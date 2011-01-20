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
			'db'			=> array(
				'UseRandom'			=> 'Boolean',
				'NumberOfAds'		=> 'Int',
				'InheritSettings'	=> 'Boolean',
			),
			'defaults'		=> array(
				'InheritSettings'	=> true
			),
			'many_many'		=> array(
				'Advertisements'			=> 'Advertisement',
			),
			'has_one'		=> array(
				'UseCampaign'				=> 'AdCampaign',
			)
		);
	}
	
	public function updateCMSFields(FieldSet &$fields) {
		parent::updateCMSFields($fields);

		$fields->addFieldToTab('Root.Advertisements', new CheckboxField('InheritSettings', _t('Advertisements.INHERIT', 'Inherit parent settings')));
//		$fields->addFieldToTab('Root.Advertisements', new CheckboxField('UseRandom', _t('Advertisements.USE_RANDOM', 'Use random selection')));
		$fields->addFieldToTab('Root.Advertisements', new NumericField('NumberOfAds', _t('Advertisements.NUM_ADS', 'How many Ads should be returned?')));
		$fields->addFieldToTab('Root.Advertisements', new ManyManyPickerField($this->owner, 'Advertisements'));
		$fields->addFieldToTab('Root.Advertisements', new HasOnePickerField($this->owner, 'UseCampaign'));
	}
	
	public function AdList() {
		$toUse = $this->owner;
		if ($this->owner->InheritSettings) {
			while($toUse->ParentID) {
				if (!$toUse->InheritSettings) {
					break;
				}
				$toUse = $toUse->Parent();
			}
		}
		
		$ads = null;
		
		// If set to use a campaign, just switch to that as our context. 
		if ($toUse->UseCampaignID) {
			$toUse = $toUse->UseCampaign();
		}
		
		if ($this->owner->NumberOfAds) {
			$ads = $toUse->getManyManyComponents('Advertisements', '', '', '', $this->owner->NumberOfAds);
		} else {
			$ads = $toUse->Advertisements();
		}
		
		return $ads;
	}
}
