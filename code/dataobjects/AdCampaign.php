<?php

/**
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

	public function getRandomAd() {
		$number = $this->Advertisements()->count();
		if ($number) {
			--$number;
			$rand = mt_rand(0, $number);
			$items = $this->Advertisements()->toArray();
			return $items[$rand];
		}
	}
}
