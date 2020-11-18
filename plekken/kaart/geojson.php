<?php

//ini_set('memory_limit', '1024M');

include("../functions.php");

$sparqlQueryString = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?baguri ?wkt WHERE {
  
    VALUES ?type { 
      wd:Q57660343 #podiumkunstgebouw
      wd:Q41253 #bioscoop
      wd:Q24354 #theatergebouw
      wd:Q24699794 #museumgebouw
      wd:Q207694 #kunstmuseum
      wd:Q856584 #bibliotheekgebouw
      wd:Q57659484 #tentoonstellingsgebouw
      wd:Q1060829 #concertgebouw
      wd:Q18674739 #evenementenlocatie
      wd:Q15206070 #poppodium
      wd:Q30022 #koffiehuis
      wd:Q1228895 #discotheek
    }
    ?item wdt:P131 wd:Q2680952 .
    ?item wdt:P31 ?type .
    ?item wdt:P625 ?coords .
    OPTIONAL{
      ?item wdt:P5208 ?bagid .
      BIND(uri(CONCAT('http://bag.basisregistraties.overheid.nl/bag/id/pand/',?bagid)) AS ?baguri) .
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
$json = getSparqlResults($endpointUrl,$sparqlQueryString);
$data = json_decode($json,true);




$venues = array();
$bagids = array();

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

		$names = array();
		foreach ($venues[$wdid]['properties']['names'] as $name) {
			$names[] = $name['name'];
		}

		if(strlen($v['naamstring']['value']) && !in_array($v['naamstring']['value'],$names)){
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
		"bagid" => $v['baguri']['value'],
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

	// collect bagids to sparql BAG for polygons later on
	if(strlen($v['baguri']['value'])){
		$bagids[] = $v['baguri']['value'];
	}
}

//print_r($venues);
//die;
//print_r($bagids);

$bagsparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>

SELECT ?baguri ?wkt WHERE {
	VALUES ?baguri {
	";
if(is_array($bagids)){
	foreach($bagids as $bagid){
		$bagsparql .= "\t<" . $bagid . ">\n";
	}
}

$bagsparql .= "}
	graph ?pandVoorkomen {
		?baguri geo:hasGeometry/geo:asWKT ?wkt .
	}
	filter not exists { ?pandVoorkomen bag:eindGeldigheid [] } 
}
";

//echo $bagsparql;

$endpointUrl = 'https://bag.basisregistraties.overheid.nl/sparql';
//$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";

$curldata = "query=" . urlencode($bagsparql);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$endpointUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $curldata);
curl_setopt($ch,CURLOPT_USERAGENT,'MonumentMap');
$headers = [
    'Accept: application/sparql-results+json'
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec ($ch);
curl_close ($ch);

$bagdata = json_decode($response, true);
//print_r($bagdata);

$baggeometries = array();
foreach ($bagdata['results']['bindings'] as $bag) {
	$baggeometries[$bag['baguri']['value']] = $bag['wkt']['value'];
}
//die;

//print_r($baggeometries);

$fc = array("type"=>"FeatureCollection", "features"=>array());

foreach ($venues as $k => $venue) {

	// calculate startyear and endyear
	if(isset($venue['properties']['bstart'])){
		$venue['properties']['startyear'] = date("Y",strtotime($venue['properties']['bstart']));
	}

	foreach ($venue['properties']['types'] as $type) {
		if(
			(!isset($earliestbytype) || date("Y",strtotime($type['start'])) < $earliestbytype)
			&& $type['start'] !== null
		){
			$earliestbytype = date("Y",strtotime($type['start']));
		}
	}
	if(isset($earliestbytype) && $earliestbytype > $venue['properties']['startyear']){
		$venue['properties']['startyear'] = $earliestbytype;
	}
	unset($earliestbytype);


	if(isset($venue['properties']['bend'])){
		$venue['properties']['endyear'] = date("Y",strtotime($venue['properties']['bend']));
	}

	foreach ($venue['properties']['types'] as $type) {
		if(
			(!isset($latestbytype) || date("Y",strtotime($type['end'])) > $latestbytype)
			&& $type['end'] !== null
		){
			$latestbytype = date("Y",strtotime($type['end']));
		}
	}
	if(isset($latestbytype)){
		$venue['properties']['endyear'] = $latestbytype;
	}
	unset($latestbytype);

	// replace wikidata point with bag polygon if any
	if(strlen($venue['properties']['bagid'])){
		//echo $venue['properties']['bagid'] ."\n";
		$venue['geometry'] = wkt2geojson($baggeometries[$venue['properties']['bagid']]);
	}else{
		//print_r($venue);
	}


	$fc['features'][] = $venue;

}
//die;
$json = json_encode($fc);

file_put_contents("locaties.geojson", $json);

header('Content-Type: application/json');
echo $json;















