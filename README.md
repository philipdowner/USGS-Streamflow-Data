# Synopsis

This is a WordPress plugin that fetched streamflow and river data from the USGS. It's intended use is for river guides and fishing outfitters to report conditions. It may also be useful for rafting companies and others who offer services related to rivers and streams.

* [Documentation on USGS API]()http://waterservices.usgs.gov/rest/IV-Service.html)
* [Data Source](http://waterservices.usgs.gov/nwis/iv?format=json,1.1&stateCd=mt&parameterCd=00060,00065,00010&siteType=ST)
* [How to Locate USGS sites](http://wdr.water.usgs.gov/nwisgmap/index.html)

## PLANNED FUTURE DEVELOPMENT

* Add temperature conversion option for Celsius to Fahrenheit
* Add plugin options page to allow easier administration
* Addition of shortcode to report on a site number(s)
* Use Google JS Maps API instead of Static Maps
* Automatic removal/hide of sites not reporting any data.
* Improved documentation

## USAGE

See each function for a list of accepted parameters.

Begin by fetching a list of USGS sites as specified in the array.
    $sites = usgs_fetch_sites(array('jefferson','madison'));

Fetch specific data parameters
    $parameters = usgs_fetch_dataParameters();

Fetch specific river data
    $riverData = usgs_fetch_riverData($sites,$parameters);

## Notes ##

Sorry for the poor documentation. For now it will have to do. I'll improve it later!