<?php

include("functions.php");



$sparql = "
SELECT ?item ?itemLabel ?typeLabel ?bouwjaar ?sloopjaar ?starttype ?eindtype ?naamstring ?startnaam ?eindnaam ?straat ?straatLabel WHERE {
  
    VALUES ?type { 
      wd:Q57660343 #podiumkunstgebouw
      wd:Q41253 #bioscoop
      wd:Q24354 #theatergebouw
      wd:Q24699794 #museumgebouw
      wd:Q207694 #kunstmuseum
      wd:Q856584 #bibliotheekgebouw
      wd:Q57659484 #tentoonstellingsgebouw
      wd:Q1060829 #concertgebouw
      wd:Q18674739 #evenementenlocatie
      wd:Q15206070 #poppodium
      wd:Q30022 #koffiehuis
      wd:Q1228895 #discotheek
    }
    ?item wdt:P131 wd:Q2680952 .
    ?item wdt:P31 ?type .
    OPTIONAL{
      ?item wdt:P669 ?straat .
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
LIMIT 1001";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


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
   $venues[$type][$wdid]["straatlabel"] = $v['straatLabel']['value'];
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
  
<title>Rotterdams Publiek - verhalen</title>

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
<body>

  

<div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
         <h1><a href="../">Rotterdams Publiek</a> | Verhalen</h1>
      </div> 
    </div>

    binnenkort!

</div>

</div>






</body>
</html>
