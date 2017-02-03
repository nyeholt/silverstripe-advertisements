<?php

/**
 * @author marcus
 */
class AdvertisementControllerExtension extends Extension
{
    public function onAfterInit() {
        Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
        Requirements::javascript('advertisements/javascript/advertisements.js');

        $page = $this->owner->data();
        if ($page instanceof Page) {
            $ads = Advertisement::get()->filter(['OnPages.ID' => $page->ID]);
            $items = [];
            foreach ($ads as $ad) {
                $items[] = $ad->forJson();
                if ($ad->ExternalCssID) {
                    Requirements::css($ad->getUrl());
                }
            }

            if (count($items)) {
                $data = array(
                    'endpoint'  => '',
                    'trackviews'    => false,
                    'trackclicks'   => true,
                    'trackforward'  => true,
                    'remember'   => false,
                    'items'     => $items,
                );
                $data = json_encode($data);
                Requirements::customScript('window.SSInteractives = ' . $data . ';', 'ads');
            }
        }
    }
}