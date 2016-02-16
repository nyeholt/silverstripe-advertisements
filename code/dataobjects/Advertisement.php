<?php

/**
 * Description of Advertisement
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class Advertisement extends DataObject {

	private static $use_js_tracking = false;

	private static $db = array(
		'Title'				=> 'Varchar',
		'TargetURL'			=> 'Varchar(255)',
	);

	private static $has_one = array(
		'InternalPage'		=> 'Page',
		'Campaign'			=> 'AdCampaign',
		'Image'				=> 'Image',
	);

	private static $summary_fields = array('Title');

	public function getCMSFields() {
		$fields = new FieldList();
		$fields->push(new TabSet('Root', new Tab('Main',
			new TextField('Title', 'Title'),
			new TextField('TargetURL', 'Target URL')
		)));

		if ($this->ID) {
			$impressions = $this->getImpressions();
			$clicks = $this->getClicks();

			$fields->addFieldToTab('Root.Main', new ReadonlyField('Impressions', 'Impressions', $impressions), 'Title');
			$fields->addFieldToTab('Root.Main', new ReadonlyField('Clicks', 'Clicks', $clicks), 'Title');

			$fields->addFieldsToTab('Root.Main', array(
				new UploadField('Image'),
				new Treedropdownfield('InternalPageID', 'Internal Page Link', 'Page'),
				new DropdownField('CampaignID', 'Ad Campaign', AdCampaign::get())
			));
		}

		return $fields;
	}

	protected $impressions;

	public function getImpressions() {
		if (!$this->impressions) {
			/*$query = new SQLQuery('COUNT(*) AS Impressions', 'AdImpression', '"ClassName" = \'AdImpression\' AND "AdID" = '.$this->ID);
			$res = $query->execute();
			$obj = $res->first();

			$this->impressions = 0;
			if ($obj) {
				$this->impressions = $obj['Impressions'];
			}*/

			$this->impressions = AdImpression::get()->filter(array(
				'ClassName' => 'AdImpression',
				'AdID' => $this->ID
			))->count();
		}

		return $this->impressions;
	}

	protected $clicks;

	public function getClicks() {
		if (!$this->clicks) {
			$this->clicks = 0;
			$this->clicks = AdImpression::get()->filter(array(
				'ClassName' => 'AdClick',
				'AdID' => $this->ID
			))->count();
		}
		return $this->clicks;
	}

	public function forTemplate($width = null, $height = null) {
		$inner = Convert::raw2xml($this->Title);
		if ($this->ImageID && $this->Image()->ID) {
			if ($width) {
                $converted = $this->Image()->SetRatioSize($width, $height);
                if ($converted) {
                    $inner = $converted->forTemplate();
                }

			} else {
                $inner = $this->Image()->forTemplate();
			}
		}

		$class = '';
		if (self::$use_js_tracking) {
			$class = 'class="adlink" ';
		}

		$tag = '<a '.$class.' href="'.$this->Link().'" adid="'.$this->ID.'">'.$inner.'</a>';

		return $tag;
	}

	public function SetRatioSize($width, $height) {
		return $this->forTemplate($width, $height);
	}

	public function Link() {
		if (self::$use_js_tracking) {
			Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
			Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
			Requirements::javascript('advertisements/javascript/advertisements.js');

			$link = Convert::raw2att($this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL);

		} else {
			$link = Controller::join_links(Director::baseURL(), 'adclick/go/'.$this->ID);
		}
		return $link;
	}

	public function getTarget() {
		return $this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL;
	}
}
