<?php


$sparqlQueryString = "
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
SELECT DISTINCT ?saobj ?img ?begin ?end ?loc  WHERE {
  ?saobj dc:type <http://vocab.getty.edu/aat/300027221> .
  ?saobj foaf:depiction ?img .
  ?saobj sem:hasEarliestBeginTimeStamp ?begin .
  ?saobj sem:hasLatestEndTimeStamp ?end .
  ?saobj dct:spatial wd:" . $qid . " .
} 
GROUP BY ?saobj ?img ?begin ?end ?loc
ORDER BY RAND()
LIMIT 100
";

//echo $sparqlQueryString;

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$posters = array();
foreach ($data['results']['bindings'] as $k => $v) {

	$startjaar = date("Y",strtotime($v['begin']['value']));
	$eindjaar = date("Y",strtotime($v['end']['value']));
	if($startjaar==$eindjaar){
		$datum = $startjaar;
	}else{
		$datum = $startjaar . " - " . $eindjaar;
	}

	$posters[] = array(
		"uri" => $v['saobj']['value'],
		"img" => $v['img']['value'],
		"begin" => $v['begin']['value'],
		"end" => $v['end']['value'],
		"datum" => $datum,
		"loc" => str_replace("http://www.wikidata.org/entity/","",$v['loc']['value'])
	);
}
//print_r($posters);


?>