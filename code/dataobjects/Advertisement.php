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
			$query = new SQLQuery('COUNT(*) AS Impressions', 'AdImpression', '"ClassName" = \'AdImpression\' AND "AdID" = '.$this->ID);
			$res = $query->execute();
			$obj = $res->first();
			
			$impressions = 0;
			if ($obj) {
				$impressions = $obj['Impressions'];
			}
			
			$query = new SQLQuery('COUNT(*) AS Clicks', 'AdImpression', '"ClassName" = \'AdClick\' AND "AdID" = '.$this->ID);
			$res = $query->execute();
			$obj = $res->first();
			
			$clicks = 0;
			if ($obj) {
				$clicks = $obj['Clicks'];
			}

			$fields->addFieldToTab('Root.Main', new ReadonlyField('Impressions', 'Impressions', $impressions), 'Title');
			$fields->addFieldToTab('Root.Main', new ReadonlyField('Clicks', 'Clicks', $clicks), 'Title');
			
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
		
//		$link = Convert::raw2att($this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL);
//		$link = Controller::join_links(Director::baseURL(), 'adclick/go?link='.urlencode($link));
		
		$tag = '<a class="adlink" href="'.$this->Link().'" adid="'.$this->ID.'">'.$inner.'</a>';

		return $tag;
	}
	
	public function Link() {
		$link = Controller::join_links(Director::baseURL(), 'adclick/go/'.$this->ID);
	}
	
	public function getTarget() {
		return $this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL;
	}
}
