<?php

if(!isset($_GET['qid'])){
  $qid = "Q179426";
}else{
  $qid = $_GET['qid'];
}

include("functions.php");

$sparql = "
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?iseentypeLabel ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?straatLabel ?coords ?bagid ?sitelink ?next ?nextLabel ?prev ?prevLabel WHERE {
  
  VALUES ?item { wd:" . $qid . " }
  ?item wdt:P31 ?type .
  OPTIONAL{
	 ?item wdt:P625 ?coords .
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
		?iseen ps:P31 ?iseentype .
		?iseen pq:P580 ?starttype .
		?iseen pq:P582 ?eindtype .
	 }
  OPTIONAL{
		?item p:P31 ?iseen .
		?iseen ps:P31 ?iseentype .
		?iseen pq:P580 ?starttype .
		?iseen pq:P582 ?eindtype .
	 }
  OPTIONAL{
		?item p:P2561 ?naam .
		?naam ps:P2561 ?naamstring .
		?naam pq:P580 ?startnaam .
		?naam pq:P582 ?eindnaam .
	 }
  OPTIONAL{
    ?item wdt:P1398 ?prev .
  }
  OPTIONAL{
    ?item wdt:P167 ?next .
  }
  OPTIONAL{
    ?item wdt:P669 ?straat .
  }
  OPTIONAL{
  	?sitelink schema:about ?item;
    schema:isPartOf <https://nl.wikipedia.org/>;
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
ORDER BY ?typeLabel ?itemLabel
LIMIT 1000";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$types = array();
$names = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$venue = array();
	$image = $v['image']['value'];


	$venue["wdid"] = $qid;
	$venue["uri"] = $v['item']['value'];
	$venue["label"] = $v['itemLabel']['value'];
	$venue["bagid"] = $v['bagid']['value'];
	$venue["bstart"] = $v['bouwjaar']['value'];
	$venue["bend"] = $v['sloopjaar']['value'];
	$venue["next"] = $v['next']['value'];
	$venue["nextLabel"] = $v['nextLabel']['value'];
	$venue["prev"] = $v['prev']['value'];
	$venue["prevLabel"] = $v['prevLabel']['value'];
	$venue['wikipedia'] =$v['sitelink']['value'];
	$venue['straat'] =$v['straatLabel']['value'];

	if(strlen($v['iseentypeLabel']['value'])){
		$type = $v['iseentypeLabel']['value'];
		$types[$type]['type'] = $type;
		$types[$type]["starttype"] = $v['starttype']['value'];
		$types[$type]["eindtype"] = $v['eindtype']['value'];
	}
	if(!array_key_exists($v['typeLabel']['value'], $types)){
		$types[$v['typeLabel']['value']]['type'] = $v['typeLabel']['value'];
	}

	if(strlen($v['naamstring']['value'])){
		$names[$v['naamstring']['value']]['name'] = $v['naamstring']['value'];
		$names[$v['naamstring']['value']]['start'] = $v['startnaam']['value'];
		$names[$v['naamstring']['value']]['end'] = $v['eindnaam']['value'];
	}

	if(strlen($v['wkt']['value'])){
		$venue['geojsonfeature'] = wkt2geojson($v['wkt']['value']);
	}elseif(strlen($v['coords']['value'])){
		$venue['geojsonfeature'] = wkt2geojson(strtoupper($v['coords']['value']));
	}
	

}






// QUOTATIONS

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?text ?paper ?articledate ?articleurl WHERE {
  ?i a schema:Quotation .
  ?i schema:about <http://www.wikidata.org/entity/" . $qid . "> .
  ?i schema:text ?text .
  ?i schema:isPartOf ?article .
  ?article schema:isPartOf ?paper .
  ?article rdf:value ?articleurl .
  ?article schema:datePublished ?articledate
} 
ORDER BY ?articledate
LIMIT 10
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$quotes = array();
foreach ($data['results']['bindings'] as $k => $v) {

	$quotes[] = array(
		"text" => nl2br($v['text']['value']),
		"paper" => $v['paper']['value'],
		"articleurl" => $v['articleurl']['value'],
		"articledate" => dutchdate($v['articledate']['value'])
	);

}

// VIDEOFRAGMENTEN

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX oa: <http://www.w3.org/ns/oa#>
PREFIX wd: <http://www.wikidata.org/entity/>
SELECT ?item ?movie ?embedUrl ?selector WHERE {
  ?item a oa:Annotation .
  ?item oa:hasBody/oa:hasSource wd:" . $qid . " .
  ?item oa:hasTarget/oa:hasSource ?movie .
  ?movie schema:embedUrl ?embedUrl .
  ?item oa:hasTarget/oa:hasSelector/rdf:value ?selector .
} 
LIMIT 12
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$videos = array();
foreach ($data['results']['bindings'] as $k => $v) {
	$timesstring = str_replace("t=", "", $v['selector']['value']);
	$times = explode(",", $timesstring);
	$videos[] = array(
		"embedUrl" => $v['embedUrl']['value'],
		"start" => $times[0],
		"end" => $times[1],
	);

}

print_r($videos);





?>