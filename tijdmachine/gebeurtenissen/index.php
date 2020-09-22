<?php

include("../functions.php");


$year = $_GET['year'];

$sparqlQueryString = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX edm: <http://www.europeana.eu/schemas/edm/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT ?item ?label ?begin ?end ?place ?placeLabel ?cho ?imgurl WHERE {
	?item a sem:Event ;
		sem:eventType ?eventtype ;
		sem:hasPlace ?place ;
		rdfs:label ?label ;
		sem:hasEarliestBeginTimeStamp ?begin;
		sem:hasLatestEndTimeStamp ?end .
	?place wdt:P131 wd:Q2680952 .
	?place rdfs:label ?placeLabel .
	?eventtype rdfs:label ?typelabel .
	?cho dc:subject ?item .
	?cho foaf:depiction ?imgurl .
	MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
	BIND(year(xsd:dateTime(?begin)) AS ?startyear)
	BIND(year(xsd:dateTime(?end)) AS ?endyear)
	FILTER(?startyear <= " . $year . ")
	FILTER(?endyear >= " . $year . ")
} 
ORDER BY ?begin
LIMIT 100
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);




$events = array();
//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

	$monthfrom = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $monthto = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
    

	$from = date("j M",strtotime($v['begin']['value']));
	$from = str_replace($monthfrom, $monthto, $from);


	$to = date("j M",strtotime($v['end']['value']));
	$to = str_replace($monthfrom, $monthto, $to);

	if($from==$to){
		$to = "";
	}else{
		$to = " - " . $to;
	}

	if(date("Y",strtotime($v['end']['value'])) != $year){
		$to .= " '" . substr(date("Y",strtotime($v['end']['value'])),2,2);
	}

	$events[$v['item']['value']] = array(
		"title" => $v['label']['value'],
		"actorname" => $v['actorname']['value'],
		"placeLabel" => $v['placeLabel']['value'],
		"place" => str_replace("http://www.wikidata.org/entity/","",$v['place']['value']),
		"wiki" => $v['wikipedia']['value'],
		"actor" => $v['actor']['value'],
		"datum" => $from . $to
	);

}
foreach ($data['results']['bindings'] as $k => $v) {

	$events[$v['item']['value']]['imgs'][] = array(
		"cho" => $v['cho']['value'],
		"imgurl" => $v['imgurl']['value']
	);

	$count = count($events[$v['item']['value']]['imgs']);
	$events[$v['item']['value']]['featuredimg'] = $events[$v['item']['value']]['imgs'][rand(0,($count-1))];

}
//print_r($events);


$sparqlQueryString = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX edm: <http://www.europeana.eu/schemas/edm/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT ?item ?label ?begin ?end ?place ?placeLabel ?newsreel ?newsreelfile WHERE {
	?item a sem:Event ;
		sem:eventType ?eventtype ;
		sem:hasPlace ?place ;
		rdfs:label ?label ;
		sem:hasEarliestBeginTimeStamp ?begin;
		sem:hasLatestEndTimeStamp ?end .
	?place wdt:P131 wd:Q2680952 .
	?place rdfs:label ?placeLabel .
	?eventtype rdfs:label ?typelabel .
	?newsreel dc:subject ?item .
	?newsreel dc:type <http://vocab.getty.edu/aat/300263837> .
	?newsreel edm:isShownBy ?newsreelfile .
	BIND(year(xsd:dateTime(?begin)) AS ?startyear)
	BIND(year(xsd:dateTime(?end)) AS ?endyear)
	FILTER(?startyear <= " . $year . ")
	FILTER(?endyear >= " . $year . ")
} 
ORDER BY ?begin
LIMIT 100
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);




$videos = array();


foreach ($data['results']['bindings'] as $k => $v) {

	$monthfrom = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $monthto = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
    

	$from = date("j M",strtotime($v['begin']['value']));
	$from = str_replace($monthfrom, $monthto, $from);


	$to = date("j M",strtotime($v['end']['value']));
	$to = str_replace($monthfrom, $monthto, $to);

	if($from==$to){
		$to = "";
	}else{
		$to = " - " . $to;
	}

	if(date("Y",strtotime($v['end']['value'])) != $year){
		$to .= " '" . substr(date("Y",strtotime($v['end']['value'])),2,2);
	}

	$videos[$v['item']['value']] = array(
		"title" => $v['label']['value'],
		"actorname" => $v['actorname']['value'],
		"placeLabel" => $v['placeLabel']['value'],
		"place" => str_replace("http://www.wikidata.org/entity/","",$v['place']['value']),
		"newsreel" => $v['newsreel']['value'],
		"newsreelfile" => $v['newsreelfile']['value'],
		"datum" => $from . $to
	);

}
//print_r($videos);
?>

<table class="table">

	<?php foreach($videos as $k => $v){ ?>
		<div xmlns:dct="http://purl.org/dc/terms/" xmlns:cc="http://creativecommons.org/ns#" class="oip_media" about="<?= $v['newsreelfile'] ?>">
			<video width="100%" controls="controls">
				<source type="video/mp4" src="<?= $v['newsreelfile'] ?>#t=3"/>
			</video>
		</div>

		<p class="onderschrift" style="margin-top: 5px"><?= $v['title'] ?> | <a href="/plekken/plek.php?qid=<?= $v['place'] ?>"><?= $v['placeLabel'] ?></a> | <?= $v['datum'] ?></p>
	<?php } ?>

	<?php
	foreach ($events as $event) { 

	
	?>
	
	<div class="event">

		<a href="<?= $event['featuredimg']['cho'] ?>"><img src="<?= $event['featuredimg']['imgurl'] ?>" /></a>

		<p class="onderschrift"><?= $event['title'] ?> | <a href="/plekken/plek.php?qid=<?= $event['place'] ?>"><?= $event['placeLabel'] ?></a> | <?= $event['datum'] ?></p>

	</div>

	<?php 
	} 
	?>
</table>



<p class="evensmaller">
	<?php if(count($events)==0 && count($videos)==0){ ?>
		Voor dit jaar hebben we helaas nog geen gebeurtenissen paraat.
	<?php } ?>
</p>




