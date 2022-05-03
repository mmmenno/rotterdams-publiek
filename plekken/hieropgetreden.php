<?php


$sparqlQueryString = "
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX schema: <http://schema.org/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
SELECT DISTINCT ?artist ?artistname ?rating ?wikipedia (MIN(?year) AS ?year) 
  (COUNT(?concert) AS ?nrofconcerts) 
  (GROUP_CONCAT(DISTINCT ?concert; SEPARATOR=\",\") AS ?concerts) 
  WHERE {
	?concert a schema:MusicEvent .
	?concert sem:hasTimeStamp ?date .
	?concert schema:performer [
		owl:sameAs ?artist ;
		rdfs:label ?artistname ;
		schema:ratingValue ?rating ;
	] .
	?concert schema:performer/schema:subjectOf ?wikipedia .
  	?concert schema:location/rdf:value <http://www.wikidata.org/entity/" . $qid . "> .
	BIND(year(xsd:dateTime(?date)) AS ?year)
} 
GROUP BY ?artist ?artistname ?rating ?wikipedia
ORDER BY DESC(?rating)
LIMIT 500
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$artists = array();
foreach ($data['results']['bindings'] as $k => $v) {

	$artists[] = array(
		"wikidata" => $v['artist']['value'],
		"wikipedia" => $v['wikipedia']['value'],
		"name" => $v['artistname']['value'],
		"year" => $v['year']['value'],
		"nrofconcerts" => $v['nrofconcerts']['value'],
		"concerts" => $v['concerts']['value']
	);

}


?>