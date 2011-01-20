# SilverStripe Advertisement Management module

A simple module to manage advertisements on pages. 

## Maintainer Contact

Marcus Nyeholt

<marcus (at) silverstripe (dot) com (dot) au>

## Requirements

SilverStripe 2.4.x
ItemSetField module from http://github.com/ajshort/silverstripe-itemsetfield

## Documentation

Add 

`Object::add_extension('Page', 'AdvertisementExtension');`

to your _config.php file.

In /admin, navigate to the "Ads" tab and create a new add. Navigate to the page you want the ad
to appear on and select the ad via the "Advertisements" tab. 

## TODO

Add extension method and include for doing a rotating ad banner
across all pages. You can do these manually for now via Page_Controller
if you want. Just select all Ads and iterate the collection