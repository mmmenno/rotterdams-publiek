<?php

if(!isset($_GET['year'])){
	$year = 1968;
}else{
	$year = $_GET['year'];
}

$sparql = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX edm: <http://www.europeana.eu/schemas/edm/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT * WHERE {
  ?item a sem:Event ;
	sem:eventType ?eventtype ;
	sem:hasPlace ?place ;
	rdfs:label ?label ;
	sem:hasEarliestBeginTimeStamp ?begin;
	sem:hasLatestEndTimeStamp ?end .
  ?place wdt:P131 wd:Q2680952 ;
	rdfs:label ?placelabel .
  ?eventtype rdfs:label ?typelabel .
  OPTIONAL{
	?item sem:hasActor ?actor .
	?actor rdf:value ?actorwdid .
	?actorwdid rdfs:label ?actorlabel .
	OPTIONAL{
	  ?actorwdid foaf:isPrimaryTopicOf ?artikel .
	}
	OPTIONAL{
	 ?actor dbo:role ?rol . 
	}
	SERVICE <https://query.wikidata.org/sparql> {
		OPTIONAL{
			?actorwdid wdt:P18 ?actorimg .
		}
	}
  }

  OPTIONAL{
	?cho dc:subject ?item .
	?cho foaf:depiction ?imgurl .
	MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
  }
  OPTIONAL{
	?newsreel dc:subject ?item .
	?newsreel dc:type <http://vocab.getty.edu/aat/300263837> .
	?newsreel edm:isShownBy ?newsreelfile .
  }
  BIND(year(xsd:dateTime(?begin)) AS ?startyear)
  BIND(year(xsd:dateTime(?end)) AS ?endyear)
  FILTER(?startyear <= " . $year . ")
  FILTER(?endyear >= " . $year . ")
} 
ORDER BY ?begin ?item
LIMIT 100
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=" . $year . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

$exhibitions = array();
$exhibitors = array();
$otherevents = array();
$actors = array();
$videos = array();

//print_r($data);
$images = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$wdidplace = str_replace("http://www.wikidata.org/entity/", "", $v['place']['value']);

	if($v['typelabel']['value']=="tentoonstelling"){
		$exhibitions[$v['item']['value']] = array(
			"label" => $v['label']['value'],
			"place" => $v['placelabel']['value'],
			"placeid" => $wdidplace,
			"from" => dutchdate($v['begin']['value']),
			"to" => dutchdate($v['end']['value'])
		);

		if($v['actorimg']['value']!="" && $v['rol']['value']!="organisator"){
			$exhibitors[$v['actorwdid']['value']] = array(
				"img" => $v['actorimg']['value'],
				"label" => $v['actorlabel']['value'],
				"wikipedia" => $v['artikel']['value'],
				"exhibition" => $v['label']['value']
			);
		}
	}else{
		if(!isset($otherevents[$v['item']['value']])){
			$otherevents[$v['item']['value']] = array(
				"label" => $v['label']['value'],
				"place" => $v['placelabel']['value'],
				"placeid" => $wdidplace,
				"from" => dutchdate($v['begin']['value']),
				"to" => dutchdate($v['end']['value'])
			);
		}


		if($v['actorlabel']['value']!=""){
			$otherevents[$v['item']['value']]['actors'][$v['actorlabel']['value']] = array(
				"label" => $v['actorlabel']['value'],
				"wikipedia" => $v['artikel']['value']
			);
		}

		if($v['cho']['value']!=""){
			$otherevents[$v['item']['value']]['images'][] = array(
				"cho" => $v['cho']['value'],
				"imgurl" => $v['imgurl']['value']
			);
		}

	}

	if($v['newsreel']['value']!=""){
		$videos[$v['newsreel']['value']] = array(
			"fileurl" => $v['newsreelfile']['value'],
			"label" => $v['newslabel']['value'],
			"event" => $v['label']['value']
		);
	}

}

//print_r($otherevents);



// CONCERTEN
$sparql = "
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX schema: <http://schema.org/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
SELECT DISTINCT(?artist) ?artistname (SAMPLE(?bandimg) AS ?bandimg) ?rating ?wikipedia (MIN(?date) AS ?date) ?location ?locationname WHERE {
  ?concert a schema:MusicEvent .
  ?concert sem:hasTimeStamp ?date .
  ?concert schema:performer [
      owl:sameAs ?artist ;
      rdfs:label ?artistname ;
      schema:ratingValue ?rating ;
  ] .
  OPTIONAL{
    ?concert schema:performer/schema:subjectOf ?wikipedia .
  }
	SERVICE <https://query.wikidata.org/sparql> {
		OPTIONAL{
			?artist wdt:P18 ?bandimg .
		}
	}
  ?concert schema:location [
     rdf:value ?location ;
     rdfs:label ?locationname ;
  ] .
  BIND(year(xsd:dateTime(?date)) AS ?year)
  FILTER(?year = " . $year . ")
} 
GROUP BY ?artist ?artistname ?rating ?wikipedia ?location ?locationname
ORDER BY DESC(?rating)
LIMIT 50
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=concerts-" . $year . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);
$data = json_decode($result,true);
$concerts = array();
$bandimgs = array();

//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

	$concerts[] = array(
		"artistname" => $v['artistname']['value'],
		"locationname" => $v['locationname']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"wiki" => $v['wikipedia']['value'],
		"artist" => $v['artist']['value'],
		"datum" => dutchdate($v['date']['value'])
	);

	if($v['bandimg']['value']==""){
		continue;
	}

	$bandimgs[] = array(
		"artistname" => $v['artistname']['value'],
		"locationname" => $v['locationname']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"wiki" => $v['wikipedia']['value'],
		"artist" => $v['artist']['value'],
		"img" => $v['bandimg']['value']
	);

}


function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

$prev = $year-1;
$next = $year+1;
$prev = "/timemachine/?year=" . $prev;
$next = "/timemachine/?year=" . $next;

?><!DOCTYPE html>
<html>
<head>
  
<title>Rotterdams Publiek - timemachine</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  
  <link rel="stylesheet" href="../assets/css/styles.css" />

  
</head>
<body class="abt-timemachine">

<div class="container-fluid">
	<div class="row black locationsheader">
		<div class="col-md">
			<span style="float: right;">
				<h2><a href="<?= $prev ?>">&lt;</a> <a href="<?= $next ?>">&gt;</a></h2>
			</span>
			<h2><a href="../">Rotterdams Publiek</a> | <?= $year ?></h2>
		</div>
	</div>
	<div class="row">

		<div class="col-md black imgbar">
			
				<?php foreach($videos as $k => $v){ ?>
					<div xmlns:dct="http://purl.org/dc/terms/" xmlns:cc="http://creativecommons.org/ns#" class="oip_media" about="<?= $v['fileurl'] ?>">
						<div class="padding">
							<h4><?= $v['event'] ?></h4>
						</div>
						<video width="100%" controls="controls">
							<source type="video/mp4" src="<?= $v['fileurl'] ?>#t=2"/>
						</video>
					</div>
				<?php } ?>
			

			<div class="row">
				<div class="col-md-8 white listing">
					<h3>tentoonstellingen</h3>

					<?php 
					foreach($exhibitions as $k => $v){
					echo '<h4>' . $v['label'] . '</h4>';
					echo '<p class="small">' . $v['from'] . ' - ' . $v['to'] . ' | <a href="../locaties/locatie.php?qid=' . $v['placeid'] . '">' . $v['place'] . '</a></p>';
					}
					?>
				</div>
				<div class="col-md black imgbar">
					<?php 
					foreach($exhibitors as $k => $v){
					echo '<div class="imginfo"><h2>' . $v['label'] . '</h2>';
					echo '<p class="small">dit jaar te zien in de tentoonstelling "' . $v['exhibition'] . '"</p>';
					if(strlen($v['wikipedia'])){
					echo '<a href="' . $v['wikipedia'] . '">' . $v['label'] . ' op Wikipedia</a>';
					}
					echo '</div>';
					echo '<img src="' . $v['img'] . '?width=200" >';
					}
					?>

				</div>
			</div>

		</div>

		<div class="col-md white listing">

			<div class="row">
				<div class="col-md black imgbar">
				
				

				<?php 
				foreach($otherevents as $k => $v){

					if($v['images'][0]['imgurl']==""){
						continue;
					}
					echo '<div class="event">';
					echo '<div class="imginfo"><h2>' . $v['label'] . '</h2>';
					echo '<p class="small">' . $v['place'] . ' | ' . $v['from'];
					if($v['from'] != $v['to']){
						echo ' - ' . $v['to'];
					}
					if(isset($v['actors'])){
						foreach($v['actors'] as $actor){
							echo " | ";
							if(strlen($actor['wikipedia'])){
								echo '<a href="' . $actor['wikipedia'] . '">' . $actor['label'] . '</a>';
							}else{
								echo $actor['label'];
							}
						}
					}
					echo "</p></div>";
					echo '<img style="width:100%;" src="' . $v['images'][0]['imgurl'] . '" >';
					echo "</div>\n\n";
					//echo '<h4>' . $v['label'] . '</h4>';
					
				}
				?>

			</div>
			</div>

			
			<div class="row">
				<div class="col-md black imgbar">
					<?php 
					foreach($actors as $k => $v){
						echo '<div class="imginfo" style="display:block;"><h2 style="display: inline;">' . $v['label'] . '</h2>';
						echo '<span class="small"> | ' . $v['event'] . '</span>';
						if(strlen($v['wikipedia'])){
							echo '<br /><a href="' . $v['wikipedia'] . '">' . $v['label'] . ' op Wikipedia</a>';
						}
						echo '</div>';
						echo '<img src="' . $v['img'] . '?width=400" >';
					}
					?>
				</div>
			</div>

			<?php if(count($concerts)){ ?>
			<div class="row">
				<div class="col-md-8 white">

					<h3>Concerten</h3>

					<?php 
					for($i=0; $i<30; $i++){
						if(!isset($concerts[$i])){ break; }
						echo '<h4>' . $concerts[$i]['artistname'] . '</h4>';
						echo '<p class="small">' . $concerts[$i]['datum'] . ' | <a href="../locaties/locatie.php?qid=' . $concerts[$i]['location'] . '">' . $concerts[$i]['locationname'] . '</a></p>';
					}
					?>
				</div>
				<div class="col-md black imgbar">
					<?php 
					for($i=0; $i<8; $i++){
						if(!isset($bandimgs[$i])){ break; }
						echo '<img src="' . $bandimgs[$i]['img'] . '?width=200" >';
						//print_r($bandimgs[$i]);
					}
					?>
				</div>
			</div>
		<?php } ?>


		</div>
	</div>
</div>




<script>
	$(document).ready(function() {

		$(".imgbar img").click(function(){
			$(this).prev('.imginfo').toggle();
			//console.log($(this).prev('.imginfo'))
		});

		$(".imginfo").click(function(){
			$(this).toggle();
		});
	});
</script>



</body>
</html>