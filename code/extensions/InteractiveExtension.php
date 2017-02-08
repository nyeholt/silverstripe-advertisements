<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class InteractiveExtension extends DataExtension {
	private static $db = array(
		'UseRandom'			=> 'Boolean',
		'NumberOfAds'		=> 'Int',
		'InheritSettings'	=> 'Boolean',
	);

	private static $defaults = array(
		'InheritSettings'	=> true
	);

	private static $many_many = array(
		'Interactives'			=> 'Interactive',
	);

	private static $has_one = array(
		'UseCampaign'				=> 'InteractiveCampaign',
	);

	public function updateSettingsFields(FieldList $fields) {
		$fields->addFieldToTab('Root.Interactives', new CheckboxField('InheritSettings', _t('Interactives.INHERIT', 'Inherit parent settings')));
//		$fields->addFieldToTab('Root.Interactive', new CheckboxField('UseRandom', _t('Advertisements.USE_RANDOM', 'Use random selection')));
		$fields->addFieldToTab('Root.Interactive', new NumericField('NumberOfAds', _t('Interactives.NUM_ADS', 'How many Ads should be returned?')));

		$gf = GridField::create('Interactives', 'Interactives', $this->owner->Interactives(), GridFieldConfig_RelationEditor::create());

		$fields->addFieldToTab('Root.Interactive', $gf);
//		$fields->addFieldToTab('Root.Advertisements', new ManyManyPickerField($this->owner, 'Advertisements'));
		$fields->addFieldToTab('Root.Interactive', $df = new DropdownField('UseCampaignID', 'Use campaign', InteractiveCampaign::get()->map()));
		$df->setEmptyString('-- OR Select campaign --');
	}

	public function InteractiveList() {
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

		$ads = $toUse->Interactives();
		if ($this->owner->NumberOfAds) {
			$ads = $ads->limit($this->owner->NumberOfAds);
		}

		return $ads;
	}

	public function findInteractive($name) {
		$ad = Interactive::get()->filter('Title', $name)->first();
		return $ad;
	}
}
