<?php

/**
 * @author marcus
 */
class AdvertisementControllerExtension extends Extension
{
    public function onAfterInit() {
        $page = $this->owner->data();
        if ($page instanceof Page) {
            $ads = Advertisement::get()->filter(['OnPages.ID' => $page->ID]);
            $items = [];
            foreach ($ads as $ad) {
                $items[] = $ad->forJson();
            }
        }

        $data = json_encode($items);
        Requirements::customScript('window.adLinks = ' . $data . ';', 'ads');
    }
}