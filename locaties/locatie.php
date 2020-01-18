<?php

if(!isset($_GET['qid'])){
  $qid = "Q179426";
}else{
  $qid = $_GET['qid'];
}

include("functions.php");


$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?iseentypeLabel ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?bagid ?wkt WHERE {
  
  VALUES ?item { wd:" . $qid . " }
  ?item wdt:P31 ?type .
  OPTIONAL{
	 ?item wdt:P625 ?coords .
  }
  OPTIONAL{
		?item wdt:P5208 ?bagid .
		BIND(uri(CONCAT('http://bag.basisregistraties.overheid.nl/bag/id/pand/',?bagid)) AS ?baguri) .
      SERVICE <https://data.pdok.nl/sparql> {
        graph ?pandVoorkomen {
          ?baguri geo:hasGeometry/geo:asWKT ?wkt .
        }
        filter not exists { ?pandVoorkomen bag:eindGeldigheid [] } 
      }
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
		?item p:P2561 ?naam .
		?naam ps:P2561 ?naamstring .
		?naam pq:P580 ?startnaam .
		?naam pq:P582 ?eindnaam .
	 }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
ORDER BY ?typeLabel ?itemLabel
LIMIT 1000";


$endpoint = 'https://query.wikidata.org/sparql';
$name = "loc-" . $qid;


$url = "http://128.199.33.115/querydata/?name=" . $name . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
	$url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

//print_r($data);

$types = array();
$names = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$venue = array();
	$image = $v['image']['value'];


	$venue["wdid"] = $qid;
	$venue["label"] = $v['itemLabel']['value'];
	$venue["bagid"] = $v['bagid']['value'];
	$venue["bstart"] = $v['bouwjaar']['value'];
	$venue["bend"] = $v['sloopjaar']['value'];

	if(strlen($v['iseentypeLabel']['value'])){
		$type = $v['iseentypeLabel']['value'];
		$types[$type]['type'] = $type;
		$types[$type]["starttype"] = $v['starttype']['value'];
		$types[$type]["eindtype"] = $v['eindtype']['value'];
	}
	if(!array_key_exists($v['typeLabel']['value'], $types)){
		$types[$v['typeLabel']['value']]['type'] = $v['typeLabel']['value'];
	}

	if(strlen($v['naamstring']['value'])){
		$names[$v['naamstring']['value']]['name'] = $v['naamstring']['value'];
		$names[$v['naamstring']['value']]['start'] = $v['startnaam']['value'];
		$names[$v['naamstring']['value']]['end'] = $v['eindnaam']['value'];
	}

	if(strlen($v['wkt']['value'])){
		$venue['geojsonfeature'] = wkt2geojson($v['wkt']['value']);
	}elseif(strlen($v['coords']['value'])){
		$venue['geojsonfeature'] = wkt2geojson(strtoupper($v['coords']['value']));
	}
	

}

//print_r($venue);


// CONCERTEN
$sparql = "
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX schema: <http://schema.org/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
SELECT ?artist ?artistname ?rating ?wikipedia ?date ?locationname WHERE {
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
  ?concert schema:location [
     rdf:value wd:" . $qid . " ;
     rdfs:label ?locationname ;
  ] .
} 
ORDER BY ASC(?date)
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=loc-concerts-" . $qid . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);
$data = json_decode($result,true);
$concerts = array();
$bandimgs = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$concerts[] = array(
		"artistname" => $v['artistname']['value'],
		"locationname" => $v['locationname']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"wiki" => $v['wikipedia']['value'],
		"artist" => $v['artist']['value'],
		"datum" => dutchdate($v['date']['value'])
	);

}


// EVENTS

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
		sem:hasPlace wd:" . $qid . " ;
		rdfs:label ?label ;
		sem:hasEarliestBeginTimeStamp ?begin;
		sem:hasLatestEndTimeStamp ?end .
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
} 
ORDER BY ?begin ?item
LIMIT 100
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=loc-events-" . $qid . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

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


// EVENTS

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
	?cho edm:isShownAt ?isShownAt .
	?cho dc:title ?chotitle .
	MINUS { ?cho dc:type <http://vocab.getty.edu/aat/300263837> }
} 
ORDER BY ASC(?chodate)
LIMIT 100
";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';
$url = "http://128.199.33.115/querydata/?name=loc-illustrations-" . $qid . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

$illustrations = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$illustrations[$v['cho']['value']] = array(
		"label" => $v['chotitle']['value'],
		"creator" => $v['creator']['value'],
		"imgurl" => $v['imgurl']['value'],
		"date" => dutchdate($v['chodate']['value']),
		"isShownAt" => dutchdate($v['isShownAt']['value'])
	);

}

//print_r($illustrations);
function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

//print_r($concerts);

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
  
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="stylesheet" href="styles.css" />

  
</head>
<body class="abt-locations">

<div class="container-fluid">
	<div class="row black locationsheader" <?php if(strlen($image)){ echo 'style="background-image: url(' . $image . '?width=800px);"'; } ?>>
		<div class="col-md">
			<h2><a href="../">Rotterdams Publiek</a> | <?= $venue['label'] ?></h2>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 black">

		  	<div id="map" style="height: 300px; margin-left: -15px; margin-right: -15px;"></div>


			<h2><?= $venue['label'] ?></h2>

			<?php 
			if(strlen($venue['bstart'])){ 
				echo 'gebouwd in ' . date("Y",strtotime($venue['bstart'])) . '<br />';
			}

			if(strlen($venue['bend'])){ 
				echo 'verdwenen in ' . date("Y",strtotime($venue['bend'])) . '<br /><br />';
			}

			foreach ($types as $k => $v) {
				echo '<strong>' . $k . '</strong> ';
				if(strlen($v['starttype'])){
					echo 'van ' . date("Y",strtotime($v['starttype']));
				}
				if(strlen($v['eindtype'])){
					echo ' tot ' . date("Y",strtotime($v['eindtype']));
				}
				echo '<br />';
			}

			if(count($names)){
				echo '<h3>a.k.a.:</h3>';
			}

			foreach ($names as $k => $v) {
				echo '<strong>' . $k . '</strong> ';
				if(strlen($v['start'])){
					echo 'van ' . date("Y",strtotime($v['start']));
				}
				if(strlen($v['end'])){
					echo ' tot ' . date("Y",strtotime($v['end']));
				}
				echo '<br />';
			}
			?>
			<br />
		</div>
		<div class="col-md-3 white">

			<?php if(count($concerts)){ ?>
				

				<h3>Concerten</h3>

				<?php 
				for($i=0; $i<250; $i++){
					if(!isset($concerts[$i])){ break; }
					echo '<h4>' . $concerts[$i]['artistname'] . '</h4>';
					echo '<p class="small">' . $concerts[$i]['datum'] . ' | <a href="../locaties/locatie.php?qid=' . $concerts[$i]['location'] . '">' . $concerts[$i]['locationname'] . '</a></p>';
				}
				?>
					
			<?php } ?>


		</div>
		<div class="col-md-5 black imgbar">

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


			<?php 
				foreach($otherevents as $k => $v){

					if($v['images'][0]['imgurl']==""){
						continue;
					}
					echo '<div class="event">';
					echo '<div class="imginfo"><h2>' . $v['label'] . '</h2>';
					echo '<p class="small">' . $v['from'];
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

			<?php 
			//print_r($illustrations);
				foreach($illustrations as $k => $v){

					echo '<div class="event">';
					echo '<div class="imginfo"><h2>' . $v['label'] . '</h2>';
					echo '<p class="small">' . $v['date'];
					if($v['creator'] != ""){
						echo ' | ' . $v['creator'];
					}
					echo "</p></div>";
					echo '<img style="width:100%;" src="' . $v['imgurl'] . '" >';
					echo "</div>\n\n";
					//echo '<h4>' . $v['label'] . '</h4>';
					
				}
			?>
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



</body>
</html>
