<?php

function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
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
SELECT DISTINCT ?item ?label ?place ?placelabel ?typelabel ?begin ?end (COUNT(?cho) AS ?images) WHERE {
?item a sem:Event ;
  sem:eventType ?eventtype ;
  sem:hasPlace ?place ;
  rdfs:label ?label ;
  sem:hasEarliestBeginTimeStamp ?begin;
  sem:hasLatestEndTimeStamp ?end .
?place wdt:P131 wd:Q2680952 ;
  rdfs:label ?placelabel .
?eventtype rdfs:label ?typelabel .
?cho dc:subject ?item .
?cho foaf:depiction ?imgurl .
MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
} 
GROUP BY ?item ?label ?place ?placelabel ?typelabel ?begin ?end
ORDER BY ?begin ?item 
LIMIT 1000";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=eventlist&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

//print_r($data);
$events = array();
$eventtypes = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$wwwid = str_replace("https://watwaarwanneer.info/event/", "www-", $v['item']['value']);
	$events[$wwwid] = array(
		"label" => $v['label']['value'],
		"place" => str_replace("http://www.wikidata.org/entity/","",$v['place']['value']),
		"placelabel" => $v['placelabel']['value'],
		"typelabel" => $v['typelabel']['value'],
		"nrimgs" => $v['images']['value'],
		"begin" => dutchdate($v['begin']['value']),
		"end" => dutchdate($v['end']['value']),
	);

  $eventtypes[mb_strtolower($v['typelabel']['value'])]++;


}

ksort($eventtypes);

?><!DOCTYPE html>
<html>
<head>
  
<title>Rotterdams Publiek - R'dam. Made it Happen.</title>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>

  <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  
  <link rel="stylesheet" href="../assets/css/styles.css" />

  
</head>
<body class="abt-events">

  <div class="container-fluid">
   <div class="row black eventsheader">
      <div class="col-md">
         <h2><a href="../">Rotterdams Publiek</a> | R'dam. Made it happen.</h2>
      </div>
      
   </div>

   <div class="row black">
    <div class="col-md small" style="padding:15px; color: #A7ADAE;">
        <?php foreach ($eventtypes as $k => $v) { ?>

          <input type="checkbox" checked="checked" disabled="disabled" name="" /> <?= $k ?> (<?= $v ?>)

        <?php } ?>
        <button class="btn btn-default" style="background-color: #A7ADAE; font-size: 10px; padding:1px;">TODO: FILTER BY TYPE</button>
    </div>
  </div>

   <?php 
   	$i = 1;
   	foreach ($events as $k => $v) {
   		$i++;
   		if($v['begin']==$v['end']){
   			$datum = $v['begin'];
   		}else{
   			$datum = $v['begin'] . " - " . $v['end'];
   		}
   ?>
    <div class="row <?php if($i%2==0){ ?>white<?php }else{ ?>black<?php } ?>">

    	<div class="col-md event" id="<?= $k ?>">

         <div class="imgcount">
          <?php for($x=0; $x<$v['nrimgs']; $x++){ ?>
            <div></div>
          <?php } ?>
         </div>

         <h3><a href="" style="font-weight: normal;"><?= $v['label'] ?></a></h3>

         <p class="small"><?= $v['typelabel'] ?> | <?= $datum ?> | <a href="../locaties/locatie.php?qid=<?= $v['place'] ?>"><?= $v['placelabel'] ?></a></p>

         <div class="eventcontent"></div>

      </div>

    </div>
  <?php } ?>
  </div>



<div id="eventimgs">
</div>


<script>
	$(document).ready(function() {

		$(".event h3 a").click(function(e){

      e.preventDefault;

      var event = $(this).parent().parent().attr("id");
      var infodiv = $(this).parent().siblings('.eventcontent');
			
      //console.log(event);
      infodiv.load("event.php?eventid=" + event);
      infodiv.toggle();
      
			return false;
		});


		$("#close").click(function(){
			$('#eventimgs').hide();
		});


	});
</script>



</body>
</html>
