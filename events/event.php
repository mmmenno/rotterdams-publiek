<?php

function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

if(!isset($_GET['event'])){
	$eventnr = "3593";
}else{
	$eventnr = str_replace("www-", "", $_GET['event']);
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
SELECT DISTINCT ?item ?label ?place ?placelabel ?typelabel ?begin ?end ?cho ?imgurl ?title ?creator WHERE {
VALUES ?item {<https://watwaarwanneer.info/event/" . $eventnr . ">}
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
$url = "http://128.199.33.115/querydata/?name=event-" . $eventnr . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

//print_r($data);
$imgs = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$imgs[] = array(
		"eventlabel" => $v['label']['value'],
		"eventplace" => str_replace("http://www.wikidata.org/entity/","",$v['place']['value']),
		"eventplacelabel" => $v['placelabel']['value'],
		"eventtypelabel" => $v['typelabel']['value'],
		"eventnrimgs" => $v['images']['value'],
		"eventbegin" => dutchdate($v['begin']['value']),
		"eventend" => dutchdate($v['end']['value']),
		"imgurl" => $v['imgurl']['value'],
		"imgtitle" => $v['title']['value'],
		"imgcreator" => $v['creator']['value'],
		"imglink" => $v['cho']['value']
	);


}

if($imgs[0]['eventbegin'] == $imgs[0]['eventbegin']){
	$eventdatum = $imgs[0]['eventbegin'];
}else{
	$eventdatum = $imgs[0]['eventbegin'] . " - " . $eventdatum = $imgs[0]['eventend'];;
}

?>



<div class="container-fluid">
	<div id="close">X</div>

	
   <div class="row">
  
   	<div class="col-md-6">
   		
   		<a href="<?= $imgs[0]['imglink'] ?>"><img style="width: 100%" src="<?= $imgs[0]['imgurl'] ?>" /></a>

   		<br /><br />

   		<div id="imgtitle"><?= $imgs[0]['imgtitle'] ?></div>
   		<div id="imgcreator"><?= $imgs[0]['imgcreator'] ?></div>
   		<div id="imgdate"><?= $imgs[0]['imgdate'] ?></div>

   	</div>
  
   	<div class="col-md-6">
   		

   		
			<h2><?= $imgs[0]['eventlabel'] ?></h2>

			<p class="small"><?= $imgs[0]['eventtypelabel'] ?> | <?= $eventdatum ?> | <a href="../locaties/locatie.php?qid=<?= $imgs[0]['eventplace'] ?>"><?= $imgs[0]['eventplacelabel'] ?></a></p>


			<h3>Alle foto's van deze gebeurtenis</h3>
			<?php 
			foreach ($imgs as $key => $img) { 

				echo '<img style="height:100px; margin-right:20px; margin-bottom:20px;" src="' . $img['imgurl'] . '" />';
			}
			?>


   		
   	</div>
   </div>

  <? /*
   <?php 
   	$i = 0;
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
         <h3><a style="font-weight: normal;"><?= $v['label'] ?></a></h3>
         <p class="small"><?= $datum ?> | <a href="../locaties/locatie.php?qid=<?= $v['place'] ?>"><?= $v['placelabel'] ?></a></p>
         <div class="imgcount">
         	<?php for($x=0; $x<$v['nrimgs']; $x++){ ?>
         		<div></div>
         	<?php } ?>
         </div>
      </div>
    </div>
  <?php } ?>
  </div>
  */ ?>

</div>

<script>
	$(document).ready(function() {

		$("#close").click(function(){
			$('#eventimgs').hide();
		});


	});
</script>

