<?php

include("../functions.php");

if(!isset($_GET['year'])){
	$year = 1968;
}else{
	$year = $_GET['year'];
}

if($year<1935){
	$place = "Q2801130"; // Schielandshuis
}else{
	$place = "Q29569055"; // Hoofdgebouw Boijmans
}

$sparqlQueryString = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT DISTINCT ?exh ?label ?begin ?end ?actorarticle ?actorlabel ?actorimg WHERE {
  ?exh sem:eventType wd:Q464980 .
  ?exh sem:hasPlace  wd:" . $place . " .
  ?exh rdfs:label ?label .
  OPTIONAL{
	?exh sem:hasActor ?actor .
	?actor rdf:value ?actorwdid .
	?actorwdid rdfs:label ?actorlabel .
	OPTIONAL{
	  ?actorwdid foaf:isPrimaryTopicOf ?actorarticle .
	}
	?actor dbo:role \"tentoongestelde\" .
	SERVICE <https://query.wikidata.org/sparql> {
		OPTIONAL{
			?actorwdid wdt:P18 ?actorimg .
		}
	}
  }
  ?exh sem:hasEarliestBeginTimeStamp ?begin .
  ?exh sem:hasLatestEndTimeStamp ?end .
  BIND (year(?begin) AS ?startyear)
  FILTER(?startyear = " . $year . ")
} 
GROUP BY ?exh ?label ?begin ?end ?place ?placelabel
ORDER BY ASC(?begin)
LIMIT 100
";


echo $sparqlQueryString;


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

print_r($data);
die;
?>
