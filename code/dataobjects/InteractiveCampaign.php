<?php

/**
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class InteractiveCampaign extends DataObject {
	public static $db = array(
		'Title'				=> 'Varchar',
		'Expires'			=> 'Date'
	);

	public static $has_many = array(
		'Interactives'		=> 'Interactive',
	);

	public static $has_one = array(
		'Client'			=> 'InteractiveClient',
	);

	public function getRandomAd() {
		$number = $this->Interactives()->count();
		if ($number) {
			--$number;
			$rand = mt_rand(0, $number);
			$items = $this->Interactives()->toArray();
			return $items[$rand];
		}
	}
}
