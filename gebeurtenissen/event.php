<?php

//echo "hello";

include("functions.php");


function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

if(!isset($_GET['eventid'])){
	$eventnr = "3593";
}else{
	$eventnr = str_replace("www-", "", $_GET['eventid']);
}


$sparql = "
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX edm: <http://www.europeana.eu/schemas/edm/>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT DISTINCT ?item ?label ?place ?placelabel ?typelabel ?begin ?end ?cho ?imgurl ?title ?creator ?actor ?actorwdid ?actorlabel ?actorwiki ?actordescription WHERE {
VALUES ?item {<https://watwaarwanneer.info/event/" . $eventnr . ">}
?item a sem:Event ;
	sem:eventType ?eventtype ;
	sem:hasPlace ?place ;
	rdfs:label ?label ;
	sem:hasEarliestBeginTimeStamp ?begin;
	sem:hasLatestEndTimeStamp ?end .
OPTIONAL{
	?item sem:hasActor ?actor .
    ?actor rdf:value ?actorwdid .
    ?actor rdfs:label ?actorlabel .
    OPTIONAL{
     	?actorwdid dc:description ?actordescription .
    }
    OPTIONAL{
     	?actorwdid foaf:isPrimaryTopicOf ?actorwiki .
    }
}
?place wdt:P131 wd:Q2680952 ;
	rdfs:label ?placelabel .
?eventtype rdfs:label ?typelabel .
?cho dc:subject ?item .
?cho foaf:depiction ?imgurl .
  OPTIONAL{
    ?cho dc:title ?title
  }
  OPTIONAL{
    ?cho dc:creator ?creator
  }
} 
ORDER BY ?begin 
LIMIT 100
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

/*
$url = "http://128.199.33.115/querydata/?name=event-" . $eventnr . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);
*/

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

//print_r($data);
$imgs = array();
$beenthereimgs = array();
$actors = array();
$beenthereactors = array();

foreach ($data['results']['bindings'] as $k => $v) {

	if(!in_array($v['cho']['value'], $beenthereimgs)){
		$imgs[] = array(
			"imgurl" => $v['imgurl']['value'],
			"imgtitle" => str_replace(array("\"","'"),"`",$v['title']['value']),
			"imgcreator" => $v['creator']['value'],
			"imglink" => $v['cho']['value']
		);
		$beenthereimgs[] = $v['cho']['value'];
	}

	if(!in_array($v['actorwdid']['value'], $beenthereactors) && strlen($v['actorwdid']['value'])){
		$actors[] = array(
			"actorwdid" => $v['actorwdid']['value'],
			"actorlabel" => str_replace(array("\"","'"),"`",$v['actorlabel']['value']),
			"actordescription" => $v['actordescription']['value'],
			"actorwiki" => $v['actorwiki']['value']
		);
		$beenthereactors[] = $v['actorwdid']['value'];
	}


}

$imgsjson = json_encode($imgs);

if($imgs[0]['eventbegin'] == $imgs[0]['eventbegin']){
	$eventdatum = $imgs[0]['eventbegin'];
}else{
	$eventdatum = $imgs[0]['eventbegin'] . " - " . $eventdatum = $imgs[0]['eventend'];;
}


?>



	
<div class="row" id="event<?= $eventnr ?>">

	<div class="col-md-6">
		
		<a id="<?= $eventnr ?>-imglink" href="<?= $imgs[0]['imglink'] ?>"><img id="<?= $eventnr ?>-img" style="width: 100%; margin-bottom: 15px;" src="<?= $imgs[0]['imgurl'] ?>" /></a>


	</div>

	<div class="col-md-6 thumbs">
		

		
		<?php 
		if(count($imgs)>1){
			foreach ($imgs as $key => $img) { 

				echo '<img id="' . $eventnr . '-' . $key . '" style="height:100px; margin-right:15px; margin-bottom:15px;" src="' . $img['imgurl'] . '" />';
			}
		}
		?>

		<?php if(count($actors)>0){ ?>

			<h3>personen / organisaties</h3>
			<?php 
			foreach ($actors as $key => $actor) { 

				echo '<strong>' . $actor['actorlabel'] . '</strong> | ';
				echo $actor['actordescription'];
				if(strlen($actor['actorwiki'])){
					echo ' ... <a target="_blank" href="' . $actor['actorwiki'] . '">meer op wikipedia</a>';
				}
				echo '<br />';

			}
			?>
		<?php } ?>

		<h3>beschrijving foto</h3>
		<div id="<?= $eventnr ?>-imgtitle"><?= $imgs[0]['imgtitle'] ?></div>
		<div id="<?= $eventnr ?>-imgcreator"><?= $imgs[0]['imgcreator'] ?></div>
		<div id="<?= $eventnr ?>-imgdate"><?= $imgs[0]['imgdate'] ?></div>

		

		<br /><br />
		
	</div>
</div>



<script>
	$(document).ready(function() {


		$('#event<?= $eventnr ?> .thumbs img').click(function(){

			var allimgs = JSON.parse('<?= $imgsjson ?>');

			var splitted = $(this).attr('id').split('-');
			var eventid = splitted[0];
			var key = splitted[1];

			$('#' + eventid + '-img').attr('src',allimgs[key]['imgurl']);
			$('#' + eventid + '-imglink').attr('href',allimgs[key]['imglink']);
			$('#' + eventid + '-imgtitle').html(allimgs[key]['imgtitle']);
			$('#' + eventid + '-imgcreator').html(allimgs[key]['imgcreator']);

			console.log(eventid + '-img');

		});


	});
</script>

