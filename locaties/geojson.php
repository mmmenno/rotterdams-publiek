<?php

//ini_set('memory_limit', '1024M');

$sparqlQueryString = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?baguri ?wkt WHERE {
  
    VALUES ?type { wd:Q57660343 wd:Q41253 wd:Q24354 wd:Q24699794 wd:Q207694 wd:Q856584 wd:Q57659484 wd:Q1060829 wd:Q18674739 }
    ?item wdt:P131 wd:Q2680952 .
    ?item wdt:P31 ?type .
    OPTIONAL{
      ?item wdt:P625 ?coords .
    }
    OPTIONAL{
      ?item wdt:P5208 ?bagid .
      BIND(uri(CONCAT('http://bag.basisregistraties.overheid.nl/bag/id/pand/',?bagid)) AS ?baguri) .
      SERVICE <https://data.pdok.nl/sparql> {
        graph ?pandVoorkomen {
          ?baguri geo:hasGeometry/geo:asWKT ?wkt .
        }
        filter not exists { ?pandVoorkomen bag:eindGeldigheid [] } 
      }
    }
	OPTIONAL{
      ?item wdt:P18 ?image .
    }
	OPTIONAL{
      ?item wdt:P571 ?bouwjaar .
    }
	OPTIONAL{
      ?item wdt:P576 ?sloopjaar .
    }
	OPTIONAL{
      ?item p:P31 ?iseen .
      ?iseen pq:P580 ?starttype .
      ?iseen pq:P582 ?eindtype .
    }
	OPTIONAL{
      ?item p:P2561 ?naam .
      ?naam ps:P2561 ?naamstring .
      ?naam pq:P580 ?startnaam .
      ?naam pq:P582 ?eindnaam .
    }
  	MINUS  { ?item wdt:P361 wd:Q13439237 .}
	SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
LIMIT 1000
";

$endpointUrl = 'https://query.wikidata.org/sparql';
$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch,CURLOPT_USERAGENT,'MonumentMap');
$headers = [
    'Accept: application/sparql-results+json'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec ($ch);
curl_close ($ch);

$data = json_decode($response, true);





$venues = array();
// eerst even platslaan
foreach ($data['results']['bindings'] as $k => $v) {

	$wdid = str_replace("http://www.wikidata.org/entity/", "", $v['item']['value']);

	if(isset($venues[$wdid])){

		$types = array();
		foreach ($venues[$wdid]['properties']['types'] as $type) {
			$types[] = $type['label'];
		}

		if(!in_array($v['typeLabel']['value'], $types)){
			$venues[$wdid]['properties']['types'][] = array(
				"label" => $v['typeLabel']['value'],
				"start" => $v['starttype']['value'],
				"end" => $v['eindtype']['value']
			);
		}

		if(strlen($v['naamstring']['value'])){
			$venues[$wdid]['properties']['names'][] = array(
				"name" => $v['naamstring']['value'],
				"start" => $v['startnaam']['value'],
				"end" => $v['eindnaam']['value']
			);	
		}

		continue;
	}


	$venues[$wdid]['type'] = "Feature";

	if(strlen($v['wkt']['value'])){
		$venues[$wdid]['geometry'] = wkt2geojson($v['wkt']['value']);	
	}elseif(strlen($v['coords']['value'])){
		$coords = str_replace(array("Point(",")"), "", $v['coords']['value']);
		$latlon = explode(" ", $coords);
		$venues[$wdid]['geometry'] = array("type"=>"Point","coordinates"=>array((double)$latlon[0],(double)$latlon[1]));
	}

	$props = array(
		"wdid" => $wdid,
		"label" => $v['itemLabel']['value'],
		"bagid" => $v['bagid']['value'],
		"types" => array(),
		"names" => array(),
		"bstart" => $v['bouwjaar']['value'],
		"bend" => $v['sloopjaar']['value'],
		"image" => $v['image']['value']
	);
	$props['types'][] = array(
		"label" => $v['typeLabel']['value'],
		"start" => $v['starttype']['value'],
		"end" => $v['eindtype']['value']
	);
	if(strlen($v['naamstring']['value'])){
		$props['names'][] = array(
			"name" => $v['naamstring']['value'],
			"start" => $v['startnaam']['value'],
			"end" => $v['eindnaam']['value']
		);	
	}
	

	$venues[$wdid]['properties'] = $props;
}



$fc = array("type"=>"FeatureCollection", "features"=>array());

foreach ($venues as $k => $venue) {

	$fc['features'][] = $venue;

}

$json = json_encode($fc);

file_put_contents("locaties.geojson", $json);










function wkt2geojson($wkt){
	$coordsstart = strpos($wkt,"(");
	$type = trim(substr($wkt,0,$coordsstart));
	$coordstring = substr($wkt, $coordsstart);

	switch ($type) {
	    case "LINESTRING":
	    	$geom = array("type"=>"LineString","coordinates"=>array());
			$coordstring = str_replace(array("(",")"), "", $coordstring);
	    	$pairs = explode(",", $coordstring);
	    	foreach ($pairs as $k => $v) {
	    		$coords = explode(" ", trim($v));
	    		$geom['coordinates'][] = array((double)$coords[0],(double)$coords[1]);
	    	}
	    	return $geom;
	    	break;
	    case "POLYGON":
	    	$geom = array("type"=>"Polygon","coordinates"=>array());
			preg_match_all("/\([0-9. ,]+\)/",$coordstring,$matches);
	    	//print_r($matches);
	    	foreach ($matches[0] as $linestring) {
	    		$linestring = str_replace(array("(",")"), "", $linestring);
		    	$pairs = explode(",", $linestring);
		    	$line = array();
		    	foreach ($pairs as $k => $v) {
		    		$coords = explode(" ", trim($v));
		    		$line[] = array((double)$coords[0],(double)$coords[1]);
		    	}
		    	$geom['coordinates'][] = $line;
	    	}
	    	return $geom;
	    	break;
	    case "MULTILINESTRING":
	    	$geom = array("type"=>"MultiLineString","coordinates"=>array());
	    	preg_match_all("/\([0-9. ,]+\)/",$coordstring,$matches);
	    	//print_r($matches);
	    	foreach ($matches[0] as $linestring) {
	    		$linestring = str_replace(array("(",")"), "", $linestring);
		    	$pairs = explode(",", $linestring);
		    	$line = array();
		    	foreach ($pairs as $k => $v) {
		    		$coords = explode(" ", trim($v));
		    		$line[] = array((double)$coords[0],(double)$coords[1]);
		    	}
		    	$geom['coordinates'][] = $line;
	    	}
	    	return $geom;
	    	break;
	    case "POINT":
			$coordstring = str_replace(array("(",")"), "", $coordstring);
	    	$coords = explode(" ", $coordstring);
	    	//print_r($coords);
	    	$geom = array("type"=>"Point","coordinates"=>array((double)$coords[0],(double)$coords[1]));
	    	return $geom;
	        break;
	}
}







