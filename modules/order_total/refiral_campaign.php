<?php
/**
 * refiral_campaign order-total module
 *
 * @package orderTotal
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
  	class refiral_campaign 
  	{
		
	    function refiral_campaign() 
	    {
			$this->code = 'refiral_campaign';
			$this->title = 'Refiral Campaign';
			$this->description = 'Launch your referral campaign virally.';     
			$this->template_file = 'refiral_campaign.php';
			$this->output = array();     
	    }

		function process() {
		}
		
		function execute() {
		}
	    
	    private function getSubtotal($orders_id)
	    {
			global $db;

			$orders_total_query = "SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = :ordersID AND class = 'ot_subtotal' LIMIT 1";
			$orders_total_query = $db->bindVars($orders_total_query, ':ordersID', $orders_id, 'integer');

			$orders_total = $db->Execute($orders_total_query);
			return $orders_total->fields['value'];
	    }
	    
	    public function isEnabled()
	    {
	      return (REFIRAL_CAMPAIGN_STATUS == "Yes") && (REFIRAL_CAMPAIGN_KEY != "" ? true : false); 
	    }

	    public function getApi()
	    {
	    	if(REFIRAL_CAMPAIGN_KEY != '')
    	 		return (REFIRAL_CAMPAIGN_KEY);
    	 	else
    	 		return false;
	    }

    	public function setRefiralSession() {
    		global $_SESSION;
    		$_SESSION['ref'] = true;
    	}

    	public function unsetRefiralSession() {
    		global $_SESSION;
    		unset($_SESSION['ref']);
    	}

		public function getRefiralHtml()
		{
			global $db;
			global $_SESSION;
			if ($this->check() && $this->isEnabled())
			{
				$customer_id = $_SESSION['customer_id'];

				$orders_query = "SELECT * FROM " . TABLE_ORDERS . "
				                 WHERE customers_id = :customersID
				                 ORDER BY date_purchased DESC LIMIT 1";
				$orders_query = $db->bindVars($orders_query, ':customersID', $customer_id, 'integer');
				
				$orders = $db->Execute($orders_query);
				$orders_id = $orders->fields['orders_id'];
				
				$zv_orders_id = (isset($_SESSION['order_number_created']) && $_SESSION['order_number_created'] >= 1) ? $_SESSION['order_number_created'] : $orders_id;
				$orders_id = $zv_orders_id;
										  		
				$products_query = "SELECT * FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = :ordersID";
			
			  	$products_query = $db->bindVars($products_query, ':ordersID', $orders_id, 'integer');
			  	$products = $db->Execute($products_query);

        		$cartInfo = '';
                               
		   									
			  	while (!$products->EOF)
			  	{			                                  
		          	$cartInfo .= $products->fields['products_id'].'-';
		          	$cartInfo .= $products->fields['products_price'].'-';
		          	$cartInfo .= $products->fields['products_quantity'].', ';
				    $products->MoveNext();
			  	}

				$order = $orders->fields;

		      	$order_total = round($order['order_total']);
				$order_subtotal = round($this->getSubtotal($orders_id));
		      	$order_email = $order['customers_email_address'];
		      	$order_coupon = $order['coupon_code'];
		      	$order_name = $order['customers_name'];
		      	return "<script type=\"text/javascript\">invoiceRefiral('$order_subtotal', '$order_total', '$order_coupon', '$cartInfo', '$order_name', '$order_email');</script>";
		  	}
		}
		
		
	    function check() 
	    {
	      	global $db;
	      	if (!isset($this->_check))
	      	{
	        	$check_query = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'REFIRAL_CAMPAIGN_STATUS'");
	        	$this->_check = $check_query->RecordCount();
	      	}

	      	return $this->_check;
	    }

	    function keys() {
	      return array('REFIRAL_CAMPAIGN_STATUS', 'REFIRAL_CAMPAIGN_KEY');
	    }

	    function install() 
	    {
	      	global $db, $template_dir;
	      	$db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Enable Refiral Campaign', 'REFIRAL_CAMPAIGN_STATUS', 'True', 'Do you want to enable Refiral campaign?', '6', '0', 'zen_cfg_select_option(array(\'Yes\', \'No\'), ', now())");
	      	$db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) VALUES ('Refiral API Key', 'REFIRAL_CAMPAIGN_KEY', '', 'Your Refiral API Key.', '6', '0', now())");

	      	$layout_query = $db->Execute("SELECT layout_id FROM " . TABLE_LAYOUT_BOXES . " WHERE layout_box_name = '" . $this->template_file . "'");
	      	if ($layout_query->RecordCount() > 0) 
	      	{
	      		$db->Execute("UPDATE " . TABLE_LAYOUT_BOXES . " SET layout_template = '" . $template_dir  . "', layout_box_status = 1, layout_box_status_single = 1 WHERE layout_box_name = '" . $this->template_file . "'");
	      	}
	      	else 
	      	{
				$db->Execute("INSERT INTO " . TABLE_LAYOUT_BOXES . " (layout_template, layout_box_name, layout_box_status, layout_box_location, layout_box_sort_order, layout_box_sort_order_single, layout_box_status_single) VALUES ('" . $template_dir  . "', '" . $this->template_file . "', 1, 0, 0, 0, 1)");
			}
	    }

	    function remove()
	    {
	      	global $db;
	      	$db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key in ('" . implode("', '", $this->keys()) . "')");
	      	$db->Execute("DELETE FROM " . TABLE_LAYOUT_BOXES . " WHERE layout_box_name = '" . $this->template_file . "'");
	    }
  	}
?>
