<?php

/**
 * Description of Advertisement
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class Advertisement extends DataObject {
	public static $db = array(
		'Title'				=> 'Varchar',
		'TargetURL'			=> 'Varchar(255)',
	);
	
	public static $has_one = array(
		'InternalPage'		=> 'Page',
		'Campaign'			=> 'AdCampaign',
		'Image'				=> 'Image',
	);
	
	public function getCMSFields() {
		$fields = new FieldSet();
		$fields->push(new TabSet('Root', new Tab('Main', 
			new TextField('Title', 'Title'),
			new TextField('TargetURL', 'Target URL')
		)));
		
		if ($this->ID) {
			$fields->addFieldsToTab('Root.Main', array(
				new ImageField('Image'),
				new Treedropdownfield('InternalPageID', 'Internal Page Link', 'Page'),
				new HasOnePickerField($this, 'Campaign', 'Ad Campaign')
			));
		}
		
		return $fields;
	}
	
	public function forTemplate() {
		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery-packed.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('advertisements/javascript/advertisements.js');
		
		$inner = Convert::raw2xml($this->Title);
		if ($this->ImageID) {
			$inner = $this->Image()->forTemplate();
		}
		
		$link = Convert::raw2att($this->InternalPageID ? $this->InternalPage()->Link() : $this->TargetURL);
		
		$tag = '<a class="adlink" href="'.$link.'" adid="'.$this->ID.'">'.$inner.'</a>';
		
		return $tag;
	}
}
