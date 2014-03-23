<?php
/**
 * Refiral
 *
 * @package templateSystem
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0 
 */
	global $_SESSION;
	if(!class_exists('refiral_campaign'))
		require(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/refiral_campaign.php');
	$refiral_campaign = new refiral_campaign();

	$flag = isset($_SESSION['ref']) && $_SESSION['ref']?1:0;

	if($refiral_campaign->isEnabled())
	{
		if($apiKey = $refiral_campaign->getApi())
		{
			$refiralApiCode = '<script type="text/javascript">var apiKey = \''.$apiKey.'\';</script>';

			$refiralButtonCode = '<script type="text/javascript">var showButton = true;</script>';
			$refiralAllCode    = '<script type="text/javascript" src="http://www.refiral.com/api/all.js"></script>';

			if($page_directory == 'includes/modules/pages/checkout_confirmation') 
			{
				$refiral_campaign->setRefiralSession();
			}
			else if($page_directory == 'includes/modules/pages/checkout_success') 
			{
				$refiralButtonCode = '<script type="text/javascript">var showButton = false;</script>';
				$refiralAllCode    = $refiralAllCode."\n";
				if($flag)
					$refiralAllCode .= $refiral_campaign->getRefiralHtml();
				$refiral_campaign->unsetRefiralSession();
			}
		
			?>
			<!-- Begin Refiral Campaign Code -->

			<script type="text/javascript">
			if ( (typeof jQuery === 'undefined') && !window.jQuery ) 
			{
				document.write(unescape("%3Cscript type='text/javascript' src='http://code.jquery.com/jquery-latest.min.js'%3E%3C/script%3E"));
			} 
			else 
			{
			    if((typeof jQuery === 'undefined') && window.jQuery) 
			    {
			        jQuery = window.jQuery;
			    } 
			    else if((typeof jQuery !== 'undefined') && !window.jQuery) 
			    {
			        window.jQuery = jQuery;
			    }
			}
			</script>
			<script>jQuery.noConflict();</script>
			<?php
			echo $refiralApiCode;
			echo "\n";
			echo $refiralButtonCode;
			echo "\n";
			echo $refiralAllCode;
		}
		else
			echo '<!-- Refiral API Key is not defined -->';
	}
	else
		echo '<!-- Refiral Campaign is not enabled -->';
?>

	<!-- End Refiral Campaign Code -->
  
