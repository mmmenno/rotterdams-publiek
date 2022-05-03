<?php

include("functions.php");



$sparql = "
SELECT DISTINCT ?item WHERE {
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
    wd:Q1684522 #jazzclub
    wd:Q622425 #nachtclub
  }
  ?item wdt:P131 wd:Q2680952 .
  ?item wdt:P31 ?type .
}";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$wdids = array();

foreach ($data['results']['bindings'] as $k => $v) {

   $wdids[] = "wd:" . str_replace("http://www.wikidata.org/entity/", "", $v['item']['value']);
   

}

$query = "SELECT ?beeld ?image ?theater  WHERE {
  VALUES ?theater {\n";

    $query .= implode(" ",$wdids) . "\n";

  $query .= '}
  ?beeld wdt:P180 ?theater .
  ?beeld schema:contentUrl ?url .
  bind(iri(concat("http://commons.wikimedia.org/wiki/Special:FilePath/", wikibase:decodeUri(substr(str(?url),53)))) AS ?image)
  OPTIONAL{
    ?beeld wdt:P7482 ?bron .
  }
  OPTIONAL{
    ?beeld wdt:P571 ?created .
  }
}
LIMIT 5000';

?><!DOCTYPE html>
<html>
<head>
  
<title>Rotterdams Publiek - imgquery</title>

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

  <script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>

  
</head>
<body>

  

<div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
         <h1><a href="../">Rotterdams Publiek</a> | plekken | query</h1>
      </div> 
    </div>


    <div class="row">
      <div class="col-md-12">
        <p class="lead" style="margin-top: 20px;">
        Dit is de query die alle images uit Commons haalt van afbeeldingen van R'damse uitgaanslocaties
        </p>
        <textarea style="width:100%; height: 800px;"><?= $query ?></textarea>
      </div>
    </div>


</div>






</body>
</html>
