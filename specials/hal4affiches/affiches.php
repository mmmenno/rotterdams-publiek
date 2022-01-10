<?php


$sparqlQueryString = "
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
SELECT ?aff ?img ?begin ?end ?loc ?act  WHERE {
  ?aff dc:type <http://vocab.getty.edu/aat/300027221> .
  ?aff foaf:depiction ?img .
  ?aff sem:hasEarliestBeginTimeStamp ?begin .
  ?aff sem:hasLatestEndTimeStamp ?end .
  ?aff dct:spatial wd:Q81801550 .
  optional{
  	?aff dc:subject ?act .
	}
} 
ORDER BY ASC(?begin)
LIMIT 400
";

//echo $sparqlQueryString;

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$posters = array();
$blocks = array();
$acts = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$startjaar = date("Y",strtotime($v['begin']['value']));
	$eindjaar = date("Y",strtotime($v['end']['value']));
	if($startjaar==$eindjaar){
		$datum = $startjaar;
	}else{
		$datum = $startjaar . " - " . $eindjaar;
	}

	$id = "p" . str_replace("https://omeka.digitup.nl/s/DIU/item/","",$v['aff']['value']);

	if(!isset($blocks[$id])){
		$blocks[$id] = array(
			"class" => "poster",
			"id" => $id,
			"uri" => $v['aff']['value'],
			"img" => $v['img']['value'],
			"begin" => $v['begin']['value'],
			"end" => $v['end']['value'],
			"acts" => array(),
			"datum" => $datum
		);
	}

	if(strlen($v['act']['value'])){
		$wdid = str_replace("http://www.wikidata.org/entity/","",$v['act']['value']);
		$acts[] = array($wdid,$id);
		$blocks[$id]['acts'][] = $wdid;
	}
}
$posters = $blocks;
//print_r($posters);


?>