<?php


$sparqlQueryString = "
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
SELECT 	
	?film ?filmtitle (SAMPLE(?imdb) AS ?imdb)
	(COUNT(?program) AS ?number) 
	(MIN(?date) AS ?mindate) (MAX(?date) AS ?maxdate)
WHERE {
	?film a schema:Movie .
	?film schema:name ?filmtitle .
	OPTIONAL{
		?film schema:sameAs ?imdb .
		FILTER (STRSTARTS(STR(?imdb),\"https://www.imdb.com\"))
	}
	?program schema:subEvent/schema:workPresented ?film .
  	FILTER (!REGEX(str(?program),\"http://www.cinemacontext.nl/id/V$\"))
	?program schema:location ?venue .
	?program schema:startDate ?date .
	?venue schema:containedInPlace wd:" . $qid . " .
}
GROUP BY ?film ?filmtitle
ORDER BY DESC(?number)
limit 25
";



$endpoint = "https://data.create.humanities.uva.nl/sparql";

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$filmsshowed = array();
$singles = array();

if(isset($data['results']['bindings'])){
	foreach ($data['results']['bindings'] as $k => $v) {
		
		if(isset($v['imdb']['value'])){
			$link = $v['imdb']['value'];
		}else{
			$link = $v['film']['value'];
		}

		$minyear = substr($v['mindate']['value'], 0,4);
		$maxyear = substr($v['maxdate']['value'], 0,4);
		if($minyear==$maxyear){
			$period = "in " . $minyear;
		}else{
			$period = "in de jaren " . $minyear . " - " . $maxyear;
		}

		$filmsshowed[] = array(
			"film" => $v['film']['value'],
			"filmtitle" => $v['filmtitle']['value'],
			"number" => $v['number']['value'],
			"link" => $link,
			"period" => $period
		);



		if(count($filmsshowed)==10){
			//break;
		}
	}
}
//print_r($filmsshowed);


?>