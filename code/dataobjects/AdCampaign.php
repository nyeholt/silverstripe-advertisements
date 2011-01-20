<?php

/**
 * Description of AdCampaign
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdCampaign extends DataObject {
	public static $db = array(
		'Title'				=> 'Varchar',
		'Expires'			=> 'Date'
	);
	
	public static $has_many = array(
		'Advertisements'		=> 'Advertisement',
	);
	
	public static $has_one = array(
		'Client'			=> 'AdClient',
	);
}
