<?php

/**
 * Description of AdvertisementExtension
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdvertisementExtension extends DataObjectDecorator {
	public function extraStatics() {
		return array(
			'many_many' => array(
				'Advertisements'			=> 'Advertisement',
			)
		);
	}
	
	public function updateCMSFields(FieldSet &$fields) {
		parent::updateCMSFields($fields);
		
		$fields->addFieldToTab('Root.Advertisements', new ManyManyPickerField($this->owner, 'Advertisements'));
	}
}
