<?php

/**
 * @author marcus
 */
class InteractiveControllerExtension extends Extension
{
    public function onAfterInit() {
        Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
        Requirements::javascript('advertisements/javascript/interactives.js');

        $url = $this->owner->getRequest()->getURL();

        $siteWide = Interactive::get()->filter(['SiteWide' => 1]);
        
        $page = $this->owner->data();
        if ($page instanceof Page) {
            $pageAds = Interactive::get()->filterAny(['OnPages.ID' => $page->ID]);
        }

        $ads = array_merge($siteWide->toArray(), $pageAds->toArray());
        
        $items = [];
        foreach ($ads as $ad) {
            if (!$ad->viewableOn($url, $page ? $page->class : null)) {
                continue;
            }

            $items[] = $ad->forJson();
            if ($ad->ExternalCssID) {
                Requirements::css($ad->getUrl());
            }
        }

        $data = array(
            'endpoint'  => '',
            'trackviews'    => false,
            'trackclicks'   => true,
            'trackforward'  => true,
            'remember'   => false,
            'items'     => $items,
            'tracker'   => '',
        );
        $data = json_encode($data);
        Requirements::customScript('window.SSInteractives = {config: ' . $data . '};', 'ads');
    }
}