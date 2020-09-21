<?php

include("functions.php");

// just to get names of cinemas

$sparql = "
SELECT ?item ?itemLabel WHERE {
  
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
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
ORDER BY ?typeLabel ?itemLabel
LIMIT 900";


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


foreach ($data['results']['bindings'] as $k => $v) {
   $wdid = str_replace("http://www.wikidata.org/entity/", "", $v['item']['value']);
   $zaallabels[$wdid] = $v['itemLabel']['value'];
}

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX oa: <http://www.w3.org/ns/oa#>
SELECT ?item ?itemLabel ?embedUrl ?givenName ?familyName ?dob ?oa ?oaSource WHERE {
  ?item a schema:VideoObject .
  ?item schema:embedUrl ?embedUrl .
  ?item schema:about ?interviewee .
  ?interviewee schema:givenName ?givenName .
  ?interviewee schema:familyName ?familyName .
  ?interviewee schema:birthDate ?dob .
  OPTIONAL{
    ?oa oa:hasTarget/oa:hasSource ?item . 
    ?oa oa:hasBody/oa:hasPurpose oa:linking .
    ?oa oa:hasBody ?oaBody .
    ?oaBody oa:hasSource ?oaSource .
    OPTIONAL{
      ?oaBody rdfs:label ?itemLabel .
    }
  }
} 
ORDER BY ASC(?item) ASC(?oa)
LIMIT 1000";


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);


$movies = array();
$beenthere = array();
foreach ($data['results']['bindings'] as $k => $v) {

    if(!isset($movies[$v['item']['value']])){
        $movies[$v['item']['value']]['embedUrl'] = $v['embedUrl']['value'];
        $movies[$v['item']['value']]['interviewee'] = array(
            "name" => $v['givenName']['value'] . " " . $v['familyName']['value'],
            "birthyear" => $v['dob']['value']
        );
        $movies[$v['item']['value']]['links'] = array();

        $moviecount++;
    }

    
    if(strlen($v['oaSource']['value'])){
        $wdid = str_replace("http://www.wikidata.org/entity/","",$v['oaSource']['value']);
        if(array_key_exists($wdid, $zaallabels)){
            $naam = $v['itemLabel']['value'];
            $link = "/plekken/plek.php?qid=" . $wdid;
            $movies[$v['item']['value']]['links'][] = '<a class="buildings" href="' . $link . '">' . $naam . '</a>';
        }else{
            $naam = $v['itemLabel']['value'];
            $link = $v['oaSource']['value'];
            $movies[$v['item']['value']]['links'][] = '<a href="' . $link . '">' . $naam . '</a>';
        }
        $movies[$v['item']['value']]['links'] = array_unique($movies[$v['item']['value']]['links']);
    }

    

}
//print_r($movies);

$quarter = round($moviecount/4);
$half = $quarter*2;
$threequarters = $moviecount-$quarter;
$breaks = array($quarter,$half,$threequarters);

$third = ceil($moviecount/3);
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
  
  <link rel="stylesheet" href="/assets/css/styles.css" />
  <link rel="stylesheet" href="assets/styles.css" />

  
</head>
<body>

  

<div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
         <h1><a href="../">Rotterdams Publiek</a> | Verhalen</h1>
      </div> 
    </div>

    <div class="row">
      <div class="col-md-12">
         <h3 style="margin-bottom: 24px;">Interviews over bioscoopbezoek in de jaren '50</h3>
         <p style="margin-bottom: 24px;">
           Deze interviews zijn afgenomen in het kader van het <a target="_blank" href="https://www.europeancinemaaudiences.org/">European Cinema Audiences</a> project.
         </p>
      </div> 
    </div>

    <div class="row">
        
            <?php 
            $i = 0;
            foreach ($movies as $movieID => $movie) { 
             
                $i++;

                ?>
                    <div class="col-md-4">
                    <iframe width="560" height="315" src="<?= $movie['embedUrl'] ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                    <p class="smaller"><?= $movie['interviewee']['name'] ?>, geboren in <?= $movie['interviewee']['birthyear'] ?></p>

                <?php

                if(count($movie['links'])){
                    echo '<p class="smaller">o.a. over ';
                    echo implode(", ", $movie['links']);
                    echo "</p>";
                }

                echo "</div>";

                if($i%3==0){
                    echo '</div><div class="row">';
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
// By Chris Coyier & tweaked by Mathias Bynens

$(function() {

    // Find all YouTube videos
    var $allVideos = $("iframe[src^='https://www.youtube.com'],iframe[src^='http://www.youtube.com']"),

        // The element that is fluid width
        $fluidEl = $(".col-md-4:first");

    // Figure out and save aspect ratio for each video
    $allVideos.each(function() {

        $(this)
            .data('aspectRatio', this.height / this.width)
            
            // and remove the hard coded width/height
            .removeAttr('height')
            .removeAttr('width');

    });

    // When the window is resized
    // (You'll probably want to debounce this)
    $(window).resize(function() {

        var newWidth = $fluidEl.width();
        
        // Resize all videos according to their own aspect ratio
        $allVideos.each(function() {

            var $el = $(this);
            $el
                .width(newWidth)
                .height(newWidth * $el.data('aspectRatio'));

        });

    // Kick off one resize to fix all videos on page load
    }).resize();

});
</script>





</body>
</html>
