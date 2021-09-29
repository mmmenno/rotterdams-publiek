<?php



$sparql = "
SELECT ?beeld ?image WHERE {
  ?beeld wdt:P180 wd:" . $qid . " .
  ?beeld schema:contentUrl ?url .
  bind(iri(concat(\"http://commons.wikimedia.org/wiki/Special:FilePath/\", wikibase:decodeUri(substr(str(?url),53)))) AS ?image)
  OPTIONAL{
    ?beeld wdt:P7482 ?bron .
  }
  OPTIONAL{
    ?beeld wdt:P571 ?created .
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
LIMIT 20
";


$endpoint = 'https://wcqs-beta.wmflabs.org/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$wdimgstring = str_replace(" ","_",urldecode($image));
$commons = array();


if(isset($data['results']['bindings'])){
  foreach ($data['results']['bindings'] as $k => $v) {

  	if(strpos($v['image']['value'],"\"")){ // soms zit er een " in een afbeeldingsbestandsnaam
  		continue;
  	}
  	if($v['image']['value'] == $wdimgstring){ // is al P18 in wikidata
  		continue;
  	}



  	$commons[] = array(
      "beeld" => $v['beeld']['value'],
      "image" => $v['image']['value']
    );

  }

}

//print_r($commons);

?>