<?php

/**
 * Defines where in a site an interactive / campaign will appear
 *
 * @author marcus
 */
class InteractiveLocationExtension extends DataExtension
{
    private static $db = array(
        'SiteWide'          => 'Boolean',
        'ExcludeUrls'       => 'MultiValueField',
        'ExcludeTypes'      => 'MultiValueField',
    );

    private static $many_many = array(
        'OnPages'       => 'SiteTree',
    );

    public function updateCMSFields(\FieldList $fields)
    {
        $fields->removeByName('SiteWide');
        $fields->removeByName('OnPages');
        $fields->removeByName('ExcludeTypes');
        $fields->removeByName('ExcludeUrls');

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
    }


    /**
     * Can this interactive be viewed on the given URL ?
     *
     * @param string $url
     */
    public function viewableOn($url, $pageType = null) {
        $excludeUrls = $this->owner->ExcludeUrls->getValues();

        if ($excludeUrls && count($excludeUrls)) {
            foreach ($excludeUrls as $urlPattern) {
                if (preg_match("{" . $urlPattern . "}", $url)) {
                    return false;
                }
            }
        }

        $excludeTypes = $this->owner->ExcludeTypes->getValues();

        if ($pageType && $excludeTypes && count($excludeTypes) && in_array($pageType, $excludeTypes)) {
            return false;
        }

        return true;
    }
}