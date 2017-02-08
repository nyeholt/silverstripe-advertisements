<?php

/**
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class InteractiveCampaign extends DataObject {
	private static $db = array(
		'Title'				=> 'Varchar',
        'Begins'            => 'Date',
		'Expires'			=> 'Date',

        'DisplayType'       => 'Varchar(64)',
	);

	private static $has_many = array(
		'Interactives'		=> 'Interactive',
	);

	private static $has_one = array(
		'Client'			=> 'InteractiveClient',
	);

    private static $extensions = array(
        'InteractiveLocationExtension',
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $options = array(
            'random' => 'Always Random',
            'stickyrandom'  => 'Sticky Random',
            'all' => 'All',
        );

        $fields->replaceField('DisplayType', $df = DropdownField::create('DisplayType', 'Use items as', $options));
        $df->setRightTitle("Should one random item of this list be displayed, or all of them at once? A 'Sticky' item is randomly chosen, but then always shown to the same user");

        return $fields;
    }

    /**
     * Collect a list of interactives that are relevant for the passed in URL
     * and viewed page
     *
     * @param string $url
     * @param SiteTree $page
     */
    public function relevantInteractives($url, $page = null) {
        $items = [];
        foreach ($this->Interactives() as $ad) {
            if (!$ad->viewableOn($url, $page ? $page->class : null)) {
                continue;
            }

            $items[] = $ad->forJson();
            if ($ad->ExternalCssID) {
                Requirements::css($ad->getUrl());
            }
        }
        return $items;
    }

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
