# SilverStripe Advertisement Management module

A simple module to manage advertisements on pages. 

## Maintainer Contact

Marcus Nyeholt

<marcus (at) silverstripe (dot) com (dot) au>

## Requirements

SilverStripe 3.1.x

## Documentation

Add 

```
Page:
  extensions:
    - AdvertisementExtension
SiteConfig:
  extensions:
    - AdvertisementExtension
```

to your project's configuration yml file.

Note that ads are inherited hierarchically, so setting ads on the Site Config 
will mean those ads are used across all pages unless specified for a content
tree otherwise. 


* Navigate to the "Ads" section
* Create some Advertisements
* If you want to group the ads in a collection, create an Ad Campaign. These in turn can be associated with a client. 
* On the Advertisements tab of a page (or Site Config), you can select the individual ads (or campaign) to be displayed. 
* In your page template, use the AdList collection to actually list out the Ads to be displayed. Use the "Me" or "SetRatioSize" helpers to output an image linked as needed for proper click tracking. 

	<% loop SiteConfig.AdList %>
	<div class="ad">
		$Me
		<!-- Or, to scale it appropriately -->
		$SetRatioSize(120,80)
	
	</div>
	<% end_loop %>

* You can have complete control over how things are output by referring to the ad's Image and Link accessors. Be aware that if you're going to manually output the link, to include a special attribute used if tracking ad views (eg Advertisement::$use\_js\_tracking = true). So, output something like

```
<a href="$Link" class="adlink" adid="$ID"><img src="$Image.Link" /></a>
```

* Reference an ad directly from a template via

```
$findAd(Title)
```


Check the Advertisement class for more. 

## TODO

Add extension method and include for doing a rotating ad banner
across all pages. You can do these manually for now via Page\_Controller
if you want. Just select all Ads and iterate the collection
