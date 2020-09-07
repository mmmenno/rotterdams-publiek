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
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?iseentypeLabel ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?bagid ?wkt ?next ?nextLabel ?prev ?prevLabel WHERE {
  
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
		?naam pq:P580 ?startnaam .
		?naam pq:P582 ?eindnaam .
	 }
  
  OPTIONAL{
    ?item wdt:P1398 ?prev .
  }
  OPTIONAL{
    ?item wdt:P167 ?next .
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
ORDER BY ?typeLabel ?itemLabel
LIMIT 1000";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


//print_r($data);

$types = array();
$names = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$venue = array();
	$image = $v['image']['value'];


	$venue["wdid"] = $qid;
	$venue["uri"] = $v['item']['value'];
	$venue["label"] = $v['itemLabel']['value'];
	$venue["bagid"] = $v['bagid']['value'];
	$venue["bstart"] = $v['bouwjaar']['value'];
	$venue["bend"] = $v['sloopjaar']['value'];
	$venue["next"] = $v['next']['value'];
	$venue["nextLabel"] = $v['nextLabel']['value'];
	$venue["prev"] = $v['prev']['value'];
	$venue["prevLabel"] = $v['prevLabel']['value'];

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
  
  <link rel="stylesheet" href="assets/styles.css" />

  
</head>
<body class="abt-locations">

<div class="container-fluid">
	<div class="row">
		<div class="col-md">
			<h1><a href="../">Rotterdams Publiek</a> | <?= $venue['label'] ?></h1>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">

			<h3>Op de kaart</h3>
		  	<div id="map" style="height: 300px;"></div>



		  	<h3>Info</h3>
			<h2><?= $venue['label'] ?></h2>

			Wikidata: <a href="<?= $venue['uri'] ?>"><?= $venue['wdid'] ?></a><br /><br />

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

			echo '<br />';

			if(strlen($venue['prev'])){ 
				echo 'vervangt <a href="locatie.php?qid=' . str_replace("http://www.wikidata.org/entity/","",$venue['prev']) . '">' . $venue['prevLabel'] . '</a><br />';
			}

			if(strlen($venue['next'])){ 
				echo 'vervangen door <a href="locatie.php?qid=' . str_replace("http://www.wikidata.org/entity/","",$venue['next']) . '">' . $venue['nextLabel'] . '</a><br />';
			}

			?>

			<?php if($image != ""){ ?>
				<img src="<?= $image ?>?width=800px" style="width: 100%;" />
			<?php } ?>


			<br />
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


		</div>
		<div class="col-md-4">

			
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
