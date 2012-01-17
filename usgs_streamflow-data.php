<?php
/*
Plugin Name: USGS River Conditions
Plugin URI: http://manifestbozeman.com
Description: Set of functions to pull realtime streamflow data from the USGS.
Version: 1.01
Author: Philip Downer
Author URI: http://philipdowner.com
License: GPL2
*/
	
/**
* An array of rivers with their corresponding USGS site numbers
*
* @param array $river The short name of the river requested.
* @return array $sites An array containing the requested river site numbers.
*/

function usgs_fetch_sites($river) {
	//CREATE AN ARRAY OF SITES
	$sites = array();
	$sites['jefferson']['Jefferson River near Twin Bridges MT'] = '06026500';
	$sites['jefferson']['Jefferson River at Parsons Bdg nr Silver Star, MT'] = '06027600';
	$sites['jefferson']['Jefferson River near Three Forks MT'] = '06036650';
	$sites['bighole']['Big Hole River bl Mudd Cr nr Wisdom MT'] = '06024540';
	$sites['bighole']['Big Hole River at Maiden Rock nr Divide MT'] = '06025250';
	$sites['beaverhead']['Beaverhead River at Twin Bridges, MT'] = '06023100';
	$sites['ruby']['Ruby River above reservoir near Alder, MT'] = '06019500';
	$sites['ruby']['Ruby River near Twin Bridges MT'] = '06023000';
	$sites['madison']['Madison River bl Hebgen Lake nr Grayling MT'] = '06038500';
	$sites['madison']['Madison River bl Ennis Lake nr McAllister MT'] = '06041000';
	
	$river_codes = array();
	
	foreach ($river as $r) {
		if ( array_key_exists($r, $sites) ) {
			foreach ( $sites[$r] as $code )
				$river_codes[] = $code;
		}
	}
	return $river_codes;
}

/**
* An array of data parameters and their corresponding USGS Values
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*
* @param array $parameters An array of plain-english parameters to fetch from USGS
* @return array $dataparameters Array of requested parameters
*/
function usgs_fetch_dataParameters($params = array('cfs','gageHeight','temperature') ) {
	$parameters = array();
	$parameters['cfs'] = '00060'; //CUBIC FEET PER SECOND
	$parameters['gageHeight'] = '00065'; //Gage height, ft
	$parameters['temperature'] = '00010'; //Temperature, degrees Celcius
	
	$dataparameters = array();
	foreach ($params as $k => $v) {
		if ( array_key_exists($v, $parameters) ) {
			$dataparameters[] = $parameters[$v];
		}
	}
	return $dataparameters;
}

/**
* Fetches USGS River Data for the requested rivers
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*
* @param array $rivers An array of rivers provided by the fetch_sites function.
* @param array $dataparameters An array of the river data parameters provided by the fetch_dataParameters function.
* @return array $streamflow An array of the returned data
*/
function usgs_fetch_riverData($rivers,$dataparameters) {

	//CONSTRUCT THE DATA REQUEST URL
	$data = 'http://waterservices.usgs.gov/nwis/iv?format=json';
	
	//ADD THE SITES
	$data .= '&sites=';
	$max = count($rivers);
	$i = 1;
	foreach ( $rivers as $s ) {
		if ($i < $max) {
			$data .= $s.',';
		} else {
			$data .= $s;
		}
		$i++;
	}
	
	//ADD THE DATA PARAMETERS
	$data .= '&parameterCd=';
	$max = count($dataparameters);
	$i = 1;
	foreach ($dataparameters as $s) {
		if ($i < $max) {
			$data .= $s.',';
		} else {
			$data .= $s;
		}
		$i++;
	}		

	//CREATE THE RIVERS ARRAY
	$river = array();

	$streamflow = wp_remote_get($data);
	$streamflow = json_decode($streamflow['body']);
	$stream_counter = count($streamflow->value->timeSeries)-1;
	
	//ASSIGN THE APPROPRIATE ITEMS
	foreach ( $streamflow->value->timeSeries as $stream ) {
		
		//FIND THE SITE CODE
		$siteCode = $stream->sourceInfo->siteCode[0]->value;
		
		//ADD THE RIVER NAME
		$river[$siteCode]['name'] = $stream->sourceInfo->siteName; //RIVER NAME
		
		//ADD THE LATITUDE AND LONGITUDE
		$river[$siteCode]['latitude'] = $stream->sourceInfo->geoLocation->geogLocation->latitude;
		$river[$siteCode]['longitude'] = $stream->sourceInfo->geoLocation->geogLocation->longitude;

			$variable = $stream->variable;
			$variableCode = $variable->variableCode[0]->value; //VARIABLE CODE
			$river[$siteCode]['variables'][$variableCode]['variableName'] = $variable->variableName; //VARIABLE NAME
			$river[$siteCode]['variables'][$variableCode]['variableDescription'] = $variable->variableDescription; //VARIABLE DESCRIPTION
			$river[$siteCode]['variables'][$variableCode]['unitAbbreviation'] = $variable->unit->unitAbbreviation;//UNIT ABBREVIATION
		
		//ADD THE VALUES TO THE RIVERS ARRAY
		$value = $stream->values;
		$river[$siteCode]['variables'][$variableCode]['value'] = $value[0]->value[0]->value;	
	}
	
	//RETURN THE RIVER ARRAY
	//do_dump($river);
	return $river;
}

/**
* Display River Data
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*
* @param array $riverdata Array of data provided by the usgs_fetch_riverData function.
* @param boolean $showMap True or false setting to display map.
*/
function usgs_display_RiverData($riverData,$showMap=false) {
	$i =1;
	foreach ( $riverData as $data ) {
		echo '<ul class="rivers">';
			echo '<li class="riverName">'.$data['name'];
			if ($showMap) echo ' (#'.$i.')';
			echo '</li>';
			echo '<ul>';
				foreach ( $data as $variable ) {
					
					if ( is_array($variable) ) {
						foreach ($variable as $value) {
							//MAKE SURE THE VALUE IS VALID
							echo '<li>';
							echo $value['variableDescription'].': ';
							if ( $value['value'] >= 0 ) {
								echo $value['value'].' '.$value['unitAbbreviation'];
							} else {
								echo 'Not available.';
							}
							echo '</li>';
						}
					}
				}
			echo '</ul>';
		echo '</ul>';
		$i++;
	}
	if ($showMap) {
		usgs_display_map($riverData);
	}
}

/**
* Display River Map
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*
* @param array $riverData Array of River Data to construct location points for Google Map
* @param string $id String to use for the images CSS ID (eg #riverDataMap)
* @param string $class String to use for the image's CSS Class (eg .riverMap)
* @param int $width Width of the image to be returned.
* @param int $height Height of the image to be returned.
* @param string $maptype Type of map to be returned. Possible values are 'roadmap', 'satellite', 'hybrid' and 'terrain'.
* @param bool $echo Whether to output the image or return the map url.
*/
function usgs_display_map($riverData,$id='riverDataMap',$class='riverMap', $width=350,$height=220,$maptype='terrain',$echo=true) {
	$baseurl = 'http://maps.googleapis.com/maps/api/staticmap?';
	
	//CREATE THE URL PARAMETERS
	$parameters = '';
	$parameters .= 'size='.$width.'x'.$height;
	$parameters .= '&format=jpg';
	$parameters .= '&maptype='.$maptype;
	
	//DEFINE MARKERS
	$i = 1;
	foreach ($riverData as $marker) {
		$parameters .= '&markers=color:green|size:mid|label:'.$i.'|';
		$parameters .= $marker['latitude'].','.$marker['longitude'];
		//if ( $i < count($riverData) ) $parameters .= '|';
		$i++;
	}
	
	//SENSOR
	$parameters .= '&sensor=false';
	
	$mapurl = $baseurl.utf8_encode($parameters);
	
	if ($echo) {
		echo '<img src="'.$mapurl.'" width="'.$width.'" height="'.$height.'" id="'.$id.'" class="'.$class.'" />';
	} else {
		return $mapurl;
	}
	
}
?>