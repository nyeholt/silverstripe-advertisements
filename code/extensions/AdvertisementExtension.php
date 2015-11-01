<?php

/**
 * Description of AdvertisementExtension
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class AdvertisementExtension extends DataExtension {
	private static $db = array(
		'UseRandom'			=> 'Boolean',
		'NumberOfAds'		=> 'Int',
		'InheritSettings'	=> 'Boolean',
	);
	
	private static $defaults = array(
		'InheritSettings'	=> true	
	);
	
	private static $many_many = array(
		'Advertisements'			=> 'Advertisement',
	);
	
	private static $has_one = array(
		'UseCampaign'				=> 'AdCampaign',
	);
	
	public function updateSettingsFields(FieldList $fields) {
		$fields->addFieldToTab('Root.Advertisements', new CheckboxField('InheritSettings', _t('Advertisements.INHERIT', 'Inherit parent settings')));
//		$fields->addFieldToTab('Root.Advertisements', new CheckboxField('UseRandom', _t('Advertisements.USE_RANDOM', 'Use random selection')));
		$fields->addFieldToTab('Root.Advertisements', new NumericField('NumberOfAds', _t('Advertisements.NUM_ADS', 'How many Ads should be returned?')));
		
		$gf = GridField::create('Advertisements', 'Advertisements', $this->owner->Advertisements(), GridFieldConfig_RelationEditor::create());
		
		$fields->addFieldToTab('Root.Advertisements', $gf);
//		$fields->addFieldToTab('Root.Advertisements', new ManyManyPickerField($this->owner, 'Advertisements'));
		$fields->addFieldToTab('Root.Advertisements', $df = new DropdownField('UseCampaignID', 'Use campaign', AdCampaign::get()->map()));
		$df->setEmptyString('-- OR Select campaign --');
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
		
		$ads = $toUse->Advertisements();
		if ($this->owner->NumberOfAds) {
			$ads = $ads->limit($this->owner->NumberOfAds);
		}
		
		return $ads;
	}
	
	public function findAd($name) {
		$ad = Advertisement::get()->filter('Title', $name)->first();
		return $ad;
	}
}
