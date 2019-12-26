<?php

if(!isset($_GET['year'])){
	$year = 1968;
}else{
	$year = $_GET['year'];
}

$sparql = "
PREFIX dc: <http://purl.org/dc/elements/1.1/>
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
	SERVICE <https://query.wikidata.org/sparql> {
		?actorwdid wdt:P18 ?actorimg .
	}
	OPTIONAL{
	 ?actor dbo:role ?rol . 
	}
  }
  OPTIONAL{
	?cho dc:subject ?item .
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
$url = "https://rotterdamspubliek.nl/querydata/?name=" . $year . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);
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
		$otherevents[$v['item']['value']] = array(
			"label" => $v['label']['value'],
			"place" => $v['placelabel']['value'],
			"placeid" => $wdidplace,
			"from" => dutchdate($v['begin']['value']),
			"to" => dutchdate($v['end']['value'])
		);


		if($v['actorimg']['value']!=""){
			$actors[$v['actorwdid']['value']] = array(
				"img" => $v['actorimg']['value'],
				"label" => $v['actorlabel']['value'],
				"wikipedia" => $v['artikel']['value'],
				"event" => $v['label']['value']
			);
		}

	}

}

//print_r($exhibitions);
function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));
	//$dutch = $date;

	return $dutch;
}

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
			<h2><a href="../">Rotterdams Publiek</a> | <?= $year ?></h2>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 white listing">
			<h3>tentoonstellingen</h3>

			<?php 
			foreach($exhibitions as $k => $v){
			echo '<h4>' . $v['label'] . '</h4>';
			echo '<p class="small">' . $v['from'] . ' - ' . $v['to'] . ' | <a href="../locaties/locatie.php?qid=' . $v['placeid'] . '">' . $v['place'] . '</a></p>';
			}
			?>
		</div>
		<div class="col-md-3 black imgbar">
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
		<div class="col-md white listing">
			<h3>gebeurtenissen</h3>

			<?php 
			foreach($otherevents as $k => $v){
				echo '<h4>' . $v['label'] . '</h4>';
				if($v['from']==$v['to']){
					echo '<p class="small">' . $v['from'] . ' | <a href="../locaties/locatie.php?qid=' . $v['placeid'] . '">' . $v['place'] . '</a></p>';
				}else{
					echo '<p class="small">' . $v['from'] . ' - ' . $v['to'] . ' | ' . $v['place'] . '</p>';
				}
			}
			?>

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
		</div>
	</div>
</div>




<script>
	$(document).ready(function() {

		$(".imgbar img").click(function(){
			$(this).prev('.imginfo').toggle();
		});

		$(".imginfo").click(function(){
			$(this).toggle();
		});
	});
</script>



</body>
</html>