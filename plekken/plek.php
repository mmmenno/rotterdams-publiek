<?php

if(!isset($_GET['qid'])){
  $qid = "Q179426";
}else{
  $qid = $_GET['qid'];
}

include("functions.php");

$sparql = "
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?iseentypeLabel ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?straatLabel ?coords ?bagid ?sitelink ?next ?nextLabel ?prev ?prevLabel ?beschrevenopurl WHERE {
  
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
		OPTIONAL{
			?naam pq:P580 ?startnaam .
		}
		OPTIONAL{
			?naam pq:P582 ?eindnaam .
		}
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
    ?item wdt:P973 ?beschrevenopurl .
  }
  OPTIONAL{
  	?sitelink schema:about ?item;
    schema:isPartOf <https://nl.wikipedia.org/>;
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
ORDER BY ?typeLabel ?itemLabel
LIMIT 150";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$types = array();
$names = array();
$beschrevenopurl = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$image = "";

	$venue = array();
	$venue["wdid"] = $qid;
	$venue["bagid"] = "";
	$venue["bstart"] = "";
	$venue["bend"] = "";
	$venue["next"] = "";
	$venue["nextLabel"] = "";
	$venue["prev"] = "";
	$venue["prevLabel"] = "";
	$venue['straat'] = "";

	$venue["uri"] = $v['item']['value'];
	$venue["label"] = $v['itemLabel']['value'];
	
	if(isset($v['image']['value'])){
		$image = $v['image']['value'];
	}
	if(isset($v['bagid']['value'])){
		$venue["bagid"] = $v['bagid']['value'];
	}
	if(isset($v['bouwjaar']['value'])){
		$venue["bstart"] = $v['bouwjaar']['value'];
	}
	if(isset($v['sloopjaar']['value'])){
		$venue["bend"] = $v['sloopjaar']['value'];
	}
	if(isset($v['next']['value'])){
		$venue["next"] = $v['next']['value'];
	}
	if(isset($v['nextLabel']['value'])){
		$venue["nextLabel"] = $v['nextLabel']['value'];
	}
	if(isset($v['prev']['value'])){
		$venue["prev"] = $v['prev']['value'];
	}
	if(isset($v['prevLabel']['value'])){
		$venue["prevLabel"] = $v['prevLabel']['value'];
	}
	if(isset($v['sitelink']['value'])){
		$venue['wikipedia'] =$v['sitelink']['value'];
	}
	if(isset($v['straatLabel']['value'])){
		$venue['straat'] =$v['straatLabel']['value'];
	}
	if(isset($v['beschrevenopurl']['value'])){
		$beschrevenopurl[$v['beschrevenopurl']['value']] = $v['beschrevenopurl']['value'];
	}

	if(isset($v['iseentypeLabel']['value'])){
		$type = $v['iseentypeLabel']['value'];
		$types[$type]['type'] = $type;
		$types[$type]["starttype"] = $v['starttype']['value'];
		$types[$type]["eindtype"] = $v['eindtype']['value'];
	}
	if(!array_key_exists($v['typeLabel']['value'], $types)){
		$types[$v['typeLabel']['value']]['type'] = $v['typeLabel']['value'];
	}

	if(isset($v['naamstring']['value'])){
		if(!isset($v['eindnaam']['value'])){
			$v['eindnaam']['value'] = "";
		}
		if(!isset($v['startnaam']['value'])){
			$v['startnaam']['value'] = "";
		}
		$keystring = $v['startnaam']['value'] . $v['eindnaam']['value'] . $v['naamstring']['value'];
		$names[$keystring]['name'] = $v['naamstring']['value'];
		$names[$keystring]['start'] = $v['startnaam']['value'];
		$names[$keystring]['end'] = $v['eindnaam']['value'];
	}

	if(isset($v['wkt']['value'])){
		$venue['geojsonfeature'] = wkt2geojson($v['wkt']['value']);
	}elseif(strlen($v['coords']['value'])){
		$venue['geojsonfeature'] = wkt2geojson(strtoupper($v['coords']['value']));
	}
	

}
ksort($names);


//print_r($venue);


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

$interviews = array();
foreach ($data['results']['bindings'] as $k => $v) {
	$timesstring = str_replace("t=", "", $v['selector']['value']);
	$times = explode(",", $timesstring);
	$interviews[] = array(
		"embedUrl" => $v['embedUrl']['value'],
		"start" => $times[0],
		"end" => $times[1],
	);

}



// ILLUSTRATIONS

$sparql = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX edm: <http://www.europeana.eu/schemas/edm/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX dct: <http://purl.org/dc/terms/>
SELECT * WHERE {
	?cho dct:spatial wd:" . $qid . " .
	?cho foaf:depiction ?imgurl .
	?cho dc:date ?chodate .
	?cho dc:creator ?creator .
	?cho dc:description ?description .
	?cho edm:isShownAt ?isShownAt .
	?cho dc:title ?chotitle .
	MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
} 
ORDER BY ASC(?chodate)
LIMIT 100
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';
$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$illustrations = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$illustrations[$v['cho']['value']] = array(
		"label" => $v['chotitle']['value'],
		"description" => $v['description']['value'],
		"creator" => $v['creator']['value'],
		"imgurl" => $v['imgurl']['value'],
		"date" => dutchdate($v['chodate']['value']),
		"isShownAt" => $v['isShownAt']['value']
	);

}




// GEBEURTENISSEN

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
		sem:hasPlace wd:" . $qid . " ;
		rdfs:label ?label ;
		sem:hasEarliestBeginTimeStamp ?begin;
		sem:hasLatestEndTimeStamp ?end .
	?eventtype rdfs:label ?typelabel .
	?cho dc:subject ?item .
	?cho foaf:depiction ?imgurl .
	MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
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

	if(isset($v['end']['value'])){
		$to .= " '" . substr(date("Y",strtotime($v['end']['value'])),2,2);
	}

	if(!isset($v['actorname']['value'])){
		$v['actorname']['value'] = "";
	}

	if(!isset($v['wikipedia']['value'])){
		$v['wikipedia']['value'] = "";
	}

	if(!isset($v['actor']['value'])){
		$v['actor']['value'] = "";
	}

	$events[$v['item']['value']] = array(
		"title" => $v['label']['value'],
		"actorname" => $v['actorname']['value'],
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
		sem:hasPlace wd:" . $qid . " ;
		rdfs:label ?label ;
		sem:hasEarliestBeginTimeStamp ?begin;
		sem:hasLatestEndTimeStamp ?end .
	?eventtype rdfs:label ?typelabel .
	?newsreel dc:subject ?item .
	?newsreel dc:type <http://vocab.getty.edu/aat/300263837> .
	?newsreel edm:isShownBy ?newsreelfile .
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

	if(isset($v['end']['value'])){
		$to .= " '" . substr(date("Y",strtotime($v['end']['value'])),2,2);
	}

	if(!isset($v['actorname']['value'])){
		$v['actorname']['value'] = "";
	}

	$videos[$v['item']['value']] = array(
		"title" => $v['label']['value'],
		"actorname" => $v['actorname']['value'],
		"newsreel" => $v['newsreel']['value'],
		"newsreelfile" => $v['newsreelfile']['value'],
		"datum" => $from . $to
	);

}
//print_r($videos);




$concertzalen = array(
	"Q81801550",
	"Q81610581",
	"Q330298",
	"Q2845918",
	"Q2683762",
	"Q2237396",
	"Q179426"
);

if(in_array($qid, $concertzalen)){
	include("hieropgetreden.php");
}

if(array_key_exists("bioscoop", $types)){
	include("filmvoorstellingen.php");
}


include("affiches.php");
include("commons-static.php");
include("wikipedia.php");



?><!DOCTYPE html>
<html>
<head>
  
<title>Rotterdams Publiek - locaties</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<script
	src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>

	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>

	<script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<link rel="stylesheet" href="/assets/css/styles.css" />
	<link rel="stylesheet" href="assets/styles.css" />

  	<script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>
  	
</head>
<body class="abt-locations">

<div class="container-fluid">
	<div class="row">
		<div class="col-md">
			<h1><a href="../">Rotterdams Publiek</a> | <a href="/plekken/">plekken</a> | <?= $venue['label'] ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">

			<?php if(strlen($wptext)>0){ ?>

				<h3>Op Wikipedia</h3>
				<p style="margin-top: 16px;"><?= $wptext ?></p>

			<?php } ?>



		  <h3>Info van Wikidata</h3>
		  	
			
			Wikidata: <a target="_blank" href="<?= $venue['uri'] ?>"><?= $venue['wdid'] ?></a><br />

			<?php 
			
			if(count($names)){
				echo '<br/>bekend als:</br>';
			}

			foreach ($names as $k => $v) {
				echo '<strong>' . $v['name'] . '</strong> ';
				if(strlen($v['start'])){
					echo 'vanaf ' . date("Y",strtotime($v['start']));
				}
				if(strlen($v['end'])){
					echo ' tot ' . date("Y",strtotime($v['end']));
				}
				echo '<br />';
			}

			if(strlen($venue['bstart']) || strlen($venue['bend'])){ 
				echo '<br />';
			}

			if(strlen($venue['bstart'])){ 
				echo 'gebouwd in ' . date("Y",strtotime($venue['bstart'])) . '<br />';
			}

			if(strlen($venue['bend'])){ 
				echo 'gebouw verdwenen in / rond ' . date("Y",strtotime($venue['bend'])) . '<br /><br />';
			}

			foreach ($types as $k => $v) {
				echo 'een <strong>' . $k . '</strong> ';
				if(isset($v['starttype'])){
					echo 'van ' . date("Y",strtotime($v['starttype']));
				}
				if(isset($v['eindtype'])){
					echo ' tot ' . date("Y",strtotime($v['eindtype']));
				}
				echo '<br />';
			}

			if(strlen($venue['straat'])){ 
				echo '<br />straat: ' . $venue['straat'] . '<br />';
			}

			if(strlen($venue['prev'])){ 
				echo '<br />vervangt <a href="plek.php?qid=' . str_replace("http://www.wikidata.org/entity/","",$venue['prev']) . '">' . $venue['prevLabel'] . '</a><br />';
			}

			if(!strlen($venue['prev']) && strlen($venue['next'])){ 
				echo '<br />';
			}

			if(strlen($venue['next'])){ 
				echo 'vervangen door <a href="plek.php?qid=' . str_replace("http://www.wikidata.org/entity/","",$venue['next']) . '">' . $venue['nextLabel'] . '</a><br />';
			}

			if(count($beschrevenopurl)>0){
				echo '<br />beschreven op:<br />';
			}

			foreach ($beschrevenopurl as $k => $v) {
				if(strlen($v)){ 
					$showurl = str_replace(array("http://","https://"),"",$v);
					if(strlen($showurl) > 35){
						$showurl = substr($showurl,0,35) . "...";
					}
					echo '<a target="_blank" href="' . $v . '">' . $showurl . '</a><br />';
				}
			}

			

			?>

			<?php if($image != ""){ ?>
				<br /><img src="<?= $image ?>?width=800px" style="width: 100%;" />
			<?php } ?>
			<br />

			<?php if($qid == "Q81801550"){ ?>
			<h3>HAL4 Affiche Special</h3>
					<p>
						<a href="/specials/hal4affiches/">Blader hier door HAL4 affiches</a> en bekijk ze per genre of herkomst van de acts - van Neue Deutsche Welle tot Mbalax, van Cuba tot Japan. Mogelijk gemaakt door deelnemers aan het Hal4 Affiche crowdsourceproject!
					</p>

					<a href="/specials/hal4affiches/"><img src="/assets/img/affiches-hal4.jpg" /></a>
			<?php } ?>

			<?php if(count($illustrations)>0){ ?>
				<h3>Afbeeldingen</h3>
			<?php } ?>
			<?php 
			//print_r($illustrations);
				foreach($illustrations as $k => $v){

					echo '<a target="_blank" href="' . $v['isShownAt'] . '"><img src="' . $v['imgurl'] . '" ></a>';
					
					echo '<p class="onderschrift">';
					if(strlen($v['label'])){
						echo '<strong>' . $v['label'] . "</strong> | ";
					} 
					if(strlen($v['description'])){
						echo '' . $v['description'] . " | ";
					} 
					echo $v['date'];
					echo '</p>';
					
				}
			?>

			<?php if(isset($filmsshowed) && count($filmsshowed)){ ?>
				<h3>Films te zien in dit theater</h3>
				<table class="table">
				<?php
				foreach ($filmsshowed as $row) { 

					?>
					
					<tr>
						<td class="nroftd">
				      		<div class="nrof"><?= $row['number'] ?></div>
						</td>
						<td>
							<strong><a target="_blank" href="<?= $row['link'] ?>"><?= $row['filmtitle'] ?></a></strong>
							<br />
							<span class="evensmaller">hier vertoond <?= $row['period'] ?></span>
						</td>
					</tr>

					<?php 
				} 
				?>
				</table>
				<p class="evensmaller">
					Het getal in het blokje geeft het aantal weken weer waarin de film vertoond is. Lang niet alle voostellingen zijn bekend - voorstellingen na 1950 zijn sowieso (nog) niet ingevoerd. Er worden maximaal 25 films getoond. Deze gegevens komen van <a href="http://cinemacontext.nl/" target="_blank">Cinema Context</a>, waar je veel meer informatie over films, voorstellingen en bioscopen kunt vinden.
				</p>
			<?php } ?>


		</div>
		<div class="col-md-4">
			
			
			<h3>In de pers</h3>
			<?php 
			if(count($quotes)){
				foreach ($quotes as $key => $value) {
					echo "<div class=\"quote\">";
					echo "<p><span>&ldquo;</span>" . $value['text'] . "<span>&rdquo;</span></p>";
					echo "<div class=\"smaller\"><a target=\"_blank\" href=\"" . $value['articleurl'] . "\">" . $value['articledate'] . ", " . $value['paper'] . "</a></div>";
					echo "</div>";
				}
				
					
			} 
			?>


			<?php if(count($posters)){ ?>
			<h3>Affiches</h3>
			<?php 
			
				foreach ($posters as $key => $poster) {
					echo "<div class=\"poster\">";
					echo "<a target=\"_blank\" href=\"" . $poster['uri'] . "\"><img src=" . $poster['img'] . " /></a>";
					echo "<div class=\"evensmaller\">" . $poster['datum'] . "</div>";
					echo "</div>";
				}
				
					
			} 
			?>

			<?php if(count($commons)){ ?>
			<h3>Van Wikimedia Commons</h3>
      <div class="bbimgs">
        <?php foreach ($commons as $key => $commonsimg) { ?>
          <a target="_blank" title="bekijk op commons" href="<?= $commonsimg['beeld'] ?>"><img src="<?= $commonsimg['image'] ?>?width=500"></a>
        <?php } ?>
      </div>
      <?php } ?>



			<?php if(in_array($qid, $concertzalen)){ ?>
				<h3>Hier te zien</h3>
				<div class="quote" style="background-color: #000;">
					<?php 
					foreach ($artists as $key => $value) {

						$concerts = explode(",", $value['concerts']);

						if($value['nrofconcerts']>1){
							$oa = "o.a. ";
						}else{
							$oa = "";
						}

						echo "<span class=\"smaller\"><a target=\"_blank\" href=\"" . $concerts[0] . "\">" . $oa . $value['year'] . "</a></span> ";
						
						if(strlen($value['wikipedia'])){
							echo '<strong><a target="_blank" style="color:#fff;" href="' . $value['wikipedia'] . '">' . $value['name'] . "</a></strong> ";	
						}else{
							echo '<strong style="color:#ccc;">' . $value['name'] . '</strong> ';
						}	
					} 
					?>
				</div>
				<p class="evensmaller">
					De concerten waarop de lijst hierboven gebaseerd is komen uit <a href="https://www.setlist.fm/">setlist.fm</a>. Klik op het jaar om het concert daar te zien (misschien inclusief setlist) en op de naam om de Wikipediapagina van de band of artiest te openen. De lijst is gesorteerd op de grootte, in aantal karakters, van de Nederlandstalige Wikipediapagina bij inlezen.
				</p>
			<?php } ?>

		</div>
		<div class="col-md-4">

			<h3>Op de <a href="/plekken/kaart/">kaart</a></h3>
		  	<div id="map" style="height: 300px; margin-top: 20px;"></div>

		  	<?php include("memories.php"); ?>

		  	
		  	<?php if(count($interviews)>0){ ?>
		  		<h3>Deze zaal in interviews</h3>
			  	<?php foreach ($interviews as $interview) { ?>
		  			<div class="interview">
		  				<iframe width="560" height="315" src="<?= $interview['embedUrl'] ?>?start=<?= $interview['start'] ?>&end=<?= $interview['end'] ?>" frameborder="0" allow="" allowfullscreen></iframe>
					</div>
		  		<?php } ?>
		  		<p class="evensmaller">Meer interviews, ook over andere plekken, op het <a href="/verhalen/">Verhalen overzicht</a>.</p>
		  	<?php } ?>



		  	<?php if(count($videos)>0 || count($events)>0){ ?>
		  		<h3>R'dam. Made it happen.</h3>
			<?php } ?>

			<?php if($qid == "Q80815548" || $qid == "Q29569055") { ?>
			
				<p class="smaller">In <?= $venue['label'] ?> gehouden tentoonstellingen zijn ook te vinden in <a href="/tijdmachine/?year=1968">de Tijdmachine</a></p>

			<?php } ?>


		  	<?php foreach($videos as $k => $v){ ?>
				<div xmlns:dct="http://purl.org/dc/terms/" xmlns:cc="http://creativecommons.org/ns#" class="oip_media" about="<?= $v['newsreelfile'] ?>">
					<video width="100%" controls="controls">
						<source type="video/mp4" src="<?= $v['newsreelfile'] ?>#t=3"/>
					</video>
				</div>

				<p class="onderschrift" style="margin-top: 5px"><?= $v['title'] ?> | <?= $v['datum'] ?></p>
			<?php } ?>
			<?php foreach ($events as $event) { ?>
			
				<div class="event">

					<a href="<?= $event['featuredimg']['cho'] ?>"><img src="<?= $event['featuredimg']['imgurl'] ?>" /></a>

					<p class="onderschrift"><?= $event['title'] ?> | <?= $event['datum'] ?></p>
					<p class="small"></p>

				</div>

			<?php } ?>


		  	
			
		</div>
	</div>
</div>



<script>
  $(document).ready(function() {
    createMap();
    refreshMap();
  });

  function createMap(){
    center = [51.916857, 4.476839];
    zoomlevel = 14;
    
    map = L.map('map', {
          center: center,
          zoom: zoomlevel,
          minZoom: 1,
          maxZoom: 20,
          scrollWheelZoom: true,
          zoomControl: false
      });

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner-lite/{z}/{x}/{y}{r}.{ext}', {
      attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      subdomains: 'abcd',
      minZoom: 0,
      maxZoom: 20,
      ext: 'png'
    }).addTo(map);
  }

  function refreshMap(){
  		var geojsonFeature = <?= json_encode($venue['geojsonfeature']) ?>;
  		console.log(geojsonFeature);
  		
  		var myStyle = {
			"color": "#950305",
			"weight": 3,
			"opacity": 0.8,
			"fillOpacity": 0.3
		};

  		var myLayer = L.geoJSON(null, {
              pointToLayer: function (feature, latlng) {                    
                  return new L.CircleMarker(latlng, {
							color: "#950305",
							radius:8,
							weight: 2,
							opacity: 0.8,
							fillOpacity: 0.3
                  });
              },
              style: myStyle
              }).addTo(map);
		myLayer.addData(geojsonFeature);

  		map.fitBounds(myLayer.getBounds());

  		if(geojsonFeature['type']=="Point"){
  			map.setZoom(16);
  		}
  }


</script>

<script>
// By Chris Coyier & tweaked by Mathias Bynens

$(function() {

    // Find all YouTube videos
    var $allVideos = $("iframe[src^='https://www.youtube.com'],iframe[src^='http://www.youtube.com']"),

        // The element that is fluid width
        $fluidEl = $(".interview:first");

    // Figure out and save aspect ratio for each video
    $allVideos.each(function() {

        $(this)
            .data('aspectRatio', this.height / this.width)
            
            // and remove the hard coded width/height
            .removeAttr('height')
            .removeAttr('width');

    });

    // When the window is resized
    // (You'll probably want to debounce this)
    $(window).resize(function() {

        var newWidth = $fluidEl.width();
        
        // Resize all videos according to their own aspect ratio
        $allVideos.each(function() {

            var $el = $(this);
            $el
                .width(newWidth)
                .height(newWidth * $el.data('aspectRatio'));

        });

    // Kick off one resize to fix all videos on page load
    }).resize();

});
</script>

</body>
</html>