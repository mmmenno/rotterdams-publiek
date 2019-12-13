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
ORDER BY ?typeLabel
LIMIT 1000";

/*
$postdata = http_build_query(
    array(
        'query' => urlencode($sparql),
        'name' => 'locationlist',
        'endpoint' => 'https://query.wikidata.org/sparql'
    )
);

$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $postdata
    )
);

$context  = stream_context_create($opts);

$result = file_get_contents('http://localhost:3333/querydata/index.php');
*/


$result = file_get_contents('http://localhost:3333/querydata/?name=lijstjen&endpoint=https://query.wikidata.org/sparql&query=%0APREFIX+geo%3A+%3Chttp%3A%2F%2Fwww.opengis.net%2Font%2Fgeosparql%23%3E%0APREFIX+bag%3A+%3Chttp%3A%2F%2Fbag.basisregistraties.overheid.nl%2Fdef%2Fbag%23%3E%0ASELECT+%3Fitem+%3FitemLabel+%3FtypeLabel+%3Fbouwjaar+%3Fsloopjaar+%3Fstarttype+%3Feindtype+%3Fnaamstring+%3Fstartnaam+%3Feindnaam+%3Fimage+%3Fcoords+%3Fbagid+WHERE+%7B%0A++%0A++++VALUES+%3Ftype+%7B+wd%3AQ57660343+wd%3AQ41253+wd%3AQ24354+wd%3AQ24699794+wd%3AQ207694+wd%3AQ856584+wd%3AQ57659484+wd%3AQ1060829+wd%3AQ18674739+wd%3AQ15206070+%7D%0A++++%3Fitem+wdt%3AP131+wd%3AQ2680952+.%0A++++%3Fitem+wdt%3AP31+%3Ftype+.%0A++++OPTIONAL%7B%0A++++++%3Fitem+wdt%3AP625+%3Fcoords+.%0A++++%7D%0A++++OPTIONAL%7B%0A++++++%3Fitem+wdt%3AP5208+%3Fbagid+.%0A++++%7D%0A++OPTIONAL%7B%0A++++++%3Fitem+wdt%3AP18+%3Fimage+.%0A++++%7D%0A++OPTIONAL%7B%0A++++++%3Fitem+wdt%3AP571+%3Fbouwjaar+.%0A++++%7D%0A++OPTIONAL%7B%0A++++++%3Fitem+wdt%3AP576+%3Fsloopjaar+.%0A++++%7D%0A++OPTIONAL%7B%0A++++++%3Fitem+p%3AP31+%3Fiseen+.%0A++++++%3Fiseen+pq%3AP580+%3Fstarttype+.%0A++++++%3Fiseen+pq%3AP582+%3Feindtype+.%0A++++%7D%0A++OPTIONAL%7B%0A++++++%3Fitem+p%3AP2561+%3Fnaam+.%0A++++++%3Fnaam+ps%3AP2561+%3Fnaamstring+.%0A++++++%3Fnaam+pq%3AP580+%3Fstartnaam+.%0A++++++%3Fnaam+pq%3AP582+%3Feindnaam+.%0A++++%7D%0A++SERVICE+wikibase%3Alabel+%7B+bd%3AserviceParam+wikibase%3Alanguage+%22nl%2Cen%22.+%7D%0A%7D%0AORDER+BY+%3FtypeLabel%0ALIMIT+1000');
echo($result);

die;

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
    <div class="row">
      <div class="col-md-3">
        1
      </div>
      <div class="col-md-3">
        2
      </div>
      <div class="col-md-3">
        3
      </div>
      <div class="col-md-3">
        4
      </div>
    </div>
  </div>






<script>
  
</script>



</body>
</html>
