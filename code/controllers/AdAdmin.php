<?php

/**
 * Description of AdAdmin
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdAdmin extends ModelAdmin {
	public static $managed_models = array(
		'Advertisement',
		'AdCampaign',
		'AdClient',
	);
	
	public static $url_segment = 'advertisements';
	public static $menu_title = 'Ads';
//	public static $collection_controller_class = "AdAdmin_Controller";
}
