# Synopsis

This is a WordPress plugin that fetched streamflow and river data from the USGS. It's intended use is for river guides and fishing outfitters to report conditions. It may also be useful for rafting companies and others who offer services related to rivers and streams.

* [Documentation on USGS API](http://waterservices.usgs.gov/rest/IV-Service.html)
* [Data Source](http://waterservices.usgs.gov/nwis/iv?format=json,1.1&stateCd=mt&parameterCd=00060,00065,00010&siteType=ST)
* [How to Locate USGS sites](http://wdr.water.usgs.gov/nwisgmap/index.html)

## PLANNED FUTURE DEVELOPMENT

* Add temperature conversion option for Celsius to Fahrenheit
* Add plugin options page to allow easier administration
* Addition of shortcode to report on a site number(s)
* Use Google JS Maps API instead of Static Maps
* Automatic removal/hide of sites not reporting any data.
* Add Widget(s)
* Migrate to classes (OOP) rather than collection of functions
* Improved documentation
* Implement some sort of caching, perhaps via the WordPress transients API

## USAGE

See each function for a list of accepted parameters.

Begin by specifying the rivers you'd like to display. Identified as an array within the function.
	$rivers = usgs_fetch_sites(array('jefferson','madison','bighole','beaverhead','ruby'));

Choose which data parameters to include (stream discharge, temperature, etc), or leave blank to include all.
	$data_params = usgs_fetch_dataParameters();

Place a call to fetch the JSON from USGS
	$riverData = usgs_fetch_riverData($rivers,$data_params);

Display the river data as an unordered list
	usgs_display_RiverData($riverData,$showMap=false);

Show a static Google map identifying the locations
	usgs_display_map($riverData,'riverDataMap','riverMap',650,450,$maptype='terrain',$echo=true);

## Notes ##

Sorry for the poor documentation. For now it will have to do. I'll improve it later!