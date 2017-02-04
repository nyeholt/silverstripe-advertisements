<?php

/**
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class Advertisement extends DataObject {

	private static $use_js_tracking = false;

	private static $db = array(
		'Title'				=> 'Varchar',
		'TargetURL'			=> 'Varchar(255)',

        'HTMLContent'       => 'HTMLText',

        'Element'           => 'Varchar(64)',   // within which containing element will it display?
        'Location'          => 'Varchar(64)',   // where in its container element?
        'Frequency'         => 'Int',           // how often? 1 in X number of users see this
        'Timeout'           => 'Int',            // how long until it displays?
        'Transition'        => 'Varchar(64)',        // how does it appear?
        'HideAfterInteraction'  => 'Boolean',   // should the item not appear if someone has interacted with it?
        'TrackViews'        => 'Varchar(16)',

        'SiteWide'          => 'Boolean',
        'ExcludeUrls'       => 'MultiValueField',
        'ExcludeTypes'      => 'MultiValueField',
	);

	private static $has_one = array(
		'InternalPage'		=> 'Page',
		'Campaign'			=> 'AdCampaign',
		'Image'				=> 'Image',
	);

    private static $many_many = array(
        'OnPages'       => 'SiteTree',
    );

	private static $summary_fields = array('Title');

	public function getCMSFields() {
		$fields = new FieldList();

        $locations = ['prepend' => 'Top', 'append' => 'Bottom', 'before' => 'Before', 'after' => 'After',];
        $transitions = ['show' => 'Immediate', 'fadeIn' => 'Fade In', 'slideDown' => 'Slide Down'];
        
		$fields->push(new TabSet('Root', new Tab('Main',
			new TextField('Title', 'Title'),
			TextField::create('TargetURL', 'Target URL')->setRightTitle('Or select a page below'),
            new Treedropdownfield('InternalPageID', 'Internal Page Link', 'Page'),
            TextField::create('Element', 'Relative Element')->setRightTitle('CSS selector for element to appear with'),
            DropdownField::create('Location', 'Location in / near element', $locations),
            NumericField::create('Frequency', 'Display frequency')->setRightTitle('1 in N number of people will see this'),
            NumericField::create('Timeout', 'Delay display (seconds)'),
            DropdownField::create('Transition', 'What display effect should be used?', $transitions),
            CheckboxField::create('HideAfterInteraction'),
            DropdownField::create('CampaignID', 'Campaign', AdCampaign::get())->setEmptyString('--none--')
		)));

        if (Permission::check('ADMIN')) {
            $fields->addFieldToTab(
                'Root.Main', 
                DropdownField::create('TrackViews', 'Should views be tracked?', array('' => 'Default', 'yes' => 'Yes', 'no' => 'No')),
                'CampaignID'
            );
        }

		if ($this->ID) {
			$impressions = $this->getImpressions();
			$clicks = $this->getClicks();

			$fields->addFieldToTab('Root.Main', new ReadonlyField('Impressions', 'Impressions', $impressions), 'Title');
			$fields->addFieldToTab('Root.Main', new ReadonlyField('Clicks', 'Clicks', $clicks), 'Title');

            $classes = SiteTree::page_type_classes();
            $classes = array_combine($classes,$classes);
            $fields->addFieldsToTab('Root.SiteOptions', [
                CheckboxField::create('SiteWide', 'All pages in site'),
                TreeMultiselectField::create('OnPages', 'Display on pages', 'Page'),
                ToggleCompositeField::create('ExclusionRules', 'Excluding', [
                    MultiValueTextField::create('ExcludeUrls', 'Exluding URLs that match'),
                    MultiValueDropdownField::create('ExcludeTypes', 'Excluding page types', $classes)
                ]),
            ]);

            $fields->addFieldsToTab('Root.Content', array(
                new UploadField('Image'),
                new TextareaField('HTMLContent')
            ));
		}

        Versioned::reading_stage('Stage');
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
				'Interaction' => 'View',
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
				'Interaction' => 'Click',
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
		if (self::config()->use_js_tracking) {
			$class = 'class="intlink" ';
		}

		$tag = '<a '.$class.' href="'.$this->Link().'" data-intid="'.$this->ID.'">'.$inner.'</a>';

		return $tag;
	}

    public function forJson() {
        $content = strlen($this->HTMLContent) ? $this->HTMLContent : $this->forTemplate();
        
        $data = array(
            'ID'    => $this->ID,
            'Content'   => $content,
            'Element' => $this->Element,
            'Location'  => $this->Location,
            'Transition'    => $this->Transition,
            'Frequency' => $this->Frequency,
            'HideAfterInteraction'  => $this->HideAfterInteraction,
            'TrackViews'    => strlen($this->TrackViews) ? $this->TrackViews == 'yes' : self::config()->view_tracking,
        );

        return $data;
    }

	public function SetRatioSize($width, $height) {
		return $this->forTemplate($width, $height);
	}

	public function Link() {
        $link = Convert::raw2att($this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL);
        return $link;
        
		if (self::config()->use_js_tracking) {
			Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
			Requirements::javascript('advertisements/javascript/advertisements.js');

			$link = Convert::raw2att($this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL);

		} else {
			$link = Controller::join_links(Director::baseURL(), 'interactive-action/go/'.$this->ID);
		}
		return $link;
	}

	public function getTarget() {
		return $this->InternalPageID ? $this->InternalPage()->AbsoluteLink() : $this->TargetURL;
	}
}
