<?php

if(!isset($_GET['qid'])){
  $qid = "Q179426";
}else{
  $qid = $_GET['qid'];
}


$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?iseentypeLabel ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?bagid WHERE {
  
  VALUES ?item { wd:" . $qid . " }
  ?item wdt:P31 ?type .
  OPTIONAL{
    ?item wdt:P625 ?coords .
  }
  OPTIONAL{
    ?item wdt:P5208 ?bagid .
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
$url = "https://rotterdamspubliek.nl/querydata/?name=loc-" . $qid . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);
$url = "/querydata/?name=loc-" . $qid . "&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

$types = array();
$names = array();
// eerst even platslaan

//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

   $venue = array();
   $image = $v['image']['value'];


   $venue["wdid"] = $wdid;
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
      <div class="col-md-4">

      </div>
      <div class="col-md-4">

      </div>
   </div>
</div>




<script>
  
</script>



</body>
</html>
