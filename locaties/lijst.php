<?php


$sparql = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX bag: <http://bag.basisregistraties.overheid.nl/def/bag#>
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?image ?coords ?bagid WHERE {
  
    VALUES ?type { wd:Q57660343 wd:Q41253 wd:Q24354 wd:Q24699794 wd:Q207694 wd:Q856584 wd:Q57659484 wd:Q1060829 wd:Q18674739 wd:Q15206070 }
    ?item wdt:P131 wd:Q2680952 .
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
$url = "https://rotterdamspubliek.nl/querydata/?name=locationlist&endpoint=" . $endpoint . "&query=" . urlencode($sparql);

if(isset($_GET['uncache'])){
   $url .= "&uncache=1";
}

$result = file_get_contents($url);

$data = json_decode($result,true);

$venues = array();
// eerst even platslaan

//print_r($data);
$venuecount = 0;
$images = array();

foreach ($data['results']['bindings'] as $k => $v) {

   $wdid = str_replace("http://www.wikidata.org/entity/", "", $v['item']['value']);
   $images[$wdid] = $v['image']['value'];
   $type = $v['typeLabel']['value'];

   if(isset($venues[$type][$wdid])){

      if(strlen($v['naamstring']['value'])){
         $venues[$type][$wdid]['names'][] = $v['naamstring']['value']; 
      }

      continue;
   }

   $venuecount++;

   $venues[$type][$wdid]["wdid"] = $wdid;
   $venues[$type][$wdid]["label"] = $v['itemLabel']['value'];
   $venues[$type][$wdid]["bagid"] = $v['bagid']['value'];
   $venues[$type][$wdid]["bstart"] = $v['bouwjaar']['value'];
   $venues[$type][$wdid]["bend"] = $v['sloopjaar']['value'];

   if(isset($v['starttype']['value'])){
      $venues[$type][$wdid]["starttype"] = $v['starttype']['value'];
   }
   if(isset($v['eindtype']['value'])){
      $venues[$type][$wdid]["eindtype"] = $v['eindtype']['value'];
   }

   if(strlen($v['naamstring']['value'])){
      $venues[$type][$wdid]['names'][] = $v['naamstring']['value'];
   }
   

}

$quarter = round($venuecount/4);
$half = $quarter*2;
$threequarters = $venuecount-$quarter;
$breaks = array($quarter,$half,$threequarters);

$third = round($venuecount/3);
$twothirds = $third*2;
$breaks = array($third,$twothirds);

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
   <div class="row black locationsheader">
      <div class="col-md">
         <h2><a href="../">Rotterdams Publiek</a> | locaties</h2>
      </div>
      
   </div>
    <div class="row white listing">
      <div class="col-md-4">
         <?php 
         $i = 0;
         foreach ($venues as $typelabel => $venuesintype) { 
            echo "<h3>" . $typelabel . "</h3>";
            foreach ($venuesintype as $venue) { 
               $i++;

               if(in_array($i,$breaks)){
                  echo '</div><div class="col-md-4">';
                  if($typelabel == $lasttype){
                     echo "<h3>" . $typelabel . " - vervolg</h3>";
                  }
               }

               echo '<h4><a href="locatie.php?qid=' . $venue['wdid'] . '">' . $venue['label'] . '</a></h4>';

               if(isset($venue['names'])){
                  $othernames = array();

                  foreach ($venue['names'] as $name) { 
                     if($name != $venue['label'] && !in_array($name, $othernames)){
                        $othernames[] = $name;
                     }
                  }
                  if(count($othernames)){
                     $aka = implode(", ", $othernames);
                     echo '<p class="small">a.k.a. ' . $aka . '</p>';
                  }
               }

               $lasttype = $typelabel;
               
            }
         }
         ?>
         <br />
         <br />
         <br />
      </div>
    </div>
  </div>






<script>
  
</script>



</body>
</html>
