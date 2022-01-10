<?php

$file = "wikipedia.json";
$json = file_get_contents($file);
$articles = json_decode($json,true);

$valuesstring = "";
foreach($acts as $act){
	$valuesstring .= "wd:" . $act[0] . " ";
}

$sparqlQueryString = "
SELECT DISTINCT ?item ?itemLabel ?afb ?article ?genreLabel ?natLabel ?landLabel WHERE {
  VALUES ?item { " . $valuesstring . " }
  OPTIONAL{
    ?item wdt:P18 ?afb .
  }
  OPTIONAL{
    ?article schema:about ?item .
    ?article schema:isPartOf <https://nl.wikipedia.org/> .
  }
  OPTIONAL{
    ?item wdt:P136 ?genre .
  }
  OPTIONAL{
    ?item wdt:P27 ?nat .
  }
  OPTIONAL{
    ?item wdt:P495 ?land .
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
}
";

//echo $sparqlQueryString;

$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$genres = array();
$herkomsten = array();

foreach ($data['results']['bindings'] as $k => $v) {

	$wdid = str_replace("http://www.wikidata.org/entity/","",$v['item']['value']);

	//include("create-wikipedia-abstracts.php");

	$startjaar = date("Y",strtotime($v['begin']['value']));
	$eindjaar = date("Y",strtotime($v['end']['value']));
	if($startjaar==$eindjaar){
		$datum = $startjaar;
	}else{
		$datum = $startjaar . " - " . $eindjaar;
	}

	if(substr($v['natLabel']['value'],0,3) == "Bel"){
		$v['natLabel']['value'] = "Belgi&euml;";
	}
	if(substr($v['landLabel']['value'],0,3) == "Bel"){
		$v['landLabel']['value'] = "Belgi&euml;";
	}
	if($v['natLabel']['value'] == "Koninkrijk der Nederlanden"){
		$v['natLabel']['value'] = "Nederland";
	}
	if($v['landLabel']['value'] == "Koninkrijk der Nederlanden"){
		$v['landLabel']['value'] = "Nederland";
	}

	$imgs[$v['item']['value']] = array(
		"img" => $v['afb']['value'],
		"label" => $v['itemLabel']['value']
	);


	// WIKIDATA INFO
	if(!isset($wd[$wdid])){
		$wd[$wdid] = array(
			"label" => $v['itemLabel']['value']
		);
	}



	// WIKIDATA IMAGES
	if(strlen($v['afb']['value'])){
		$imgid = "img" . str_replace("http://www.wikidata.org/entity/","",$v['item']['value']);
		if(!isset($blocks[$imgid])){
			$blocks[$imgid] = array(
				"class" => "img",
				"id" => $imgid,
				"imgurl" => $v['afb']['value'],
				"label" => $v['itemLabel']['value']
			);
		}

		if(strlen($v['genreLabel']['value'])){
			$genre = strtolower(str_replace(" ","-",$v['genreLabel']['value']));
			$blocks[$imgid]['genres'][$genre] = $genre;
		}
		if(strlen($v['landLabel']['value'])){
			$herkomst = strtolower(str_replace(" ","-",$v['landLabel']['value']));
			$blocks[$imgid]['herkomsten'][$herkomst] = $herkomst;
		}
		if(strlen($v['natLabel']['value'])){
			$herkomst = strtolower(str_replace(" ","-",$v['natLabel']['value']));
			$blocks[$imgid]['herkomsten'][$herkomst] = $herkomst;
		}
	}


	// GENRES
	if(strlen($v['genreLabel']['value'])){
			$genre = strtolower(str_replace(" ","-",$v['genreLabel']['value']));
			$genres[$genre]++;

			$wd[$wdid]['genres'][$genre] = $genre;
	}


	// HERKOMST
	if(strlen($v['landLabel']['value'])){
			$herkomst = strtolower(str_replace(" ","-",$v['landLabel']['value']));
			$herkomsten[$herkomst]++;

			$wd[$wdid]['herkomsten'][$herkomst] = $herkomst;
	}
	if(strlen($v['natLabel']['value'])){
			$herkomst = strtolower(str_replace(" ","-",$v['natLabel']['value']));
			$herkomsten[$herkomst]++;

			$wd[$wdid]['herkomsten'][$herkomst] = $herkomst;
	}

	// WIKIPEDIA INFO
	if(strlen($v['article']['value'])){
		$pos = strpos($articles[$wdid]['text'],"<");
		if($pos > 50){
			$wptxt = substr($articles[$wdid]['text'],0,$pos);
		}else{
			$wptxt = $articles[$wdid]['text'];
		}
		
		$blocks["wp" . $wdid] = array(
			"class" => "article",
			"id" => "wp" . $wdid,
			"title" => $articles[$wdid]['title'],
			"text" => $wptxt,
			"link" => $articles[$wdid]['link'],
			"herkomsten" => $wd[$wdid]['herkomsten'],
			"genres" => $wd[$wdid]['genres']
		);
	}

	
}
ksort($genres);
ksort($herkomsten);

foreach($acts as $v){
	// add genres to posters
	if(isset($wd[$v[0]]['genres'])){
		if(is_array($blocks[$v[1]]['genres'])){
			$blocks[$v[1]]['genres'] = array_merge($blocks[$v[1]]['genres'], $wd[$v[0]]['genres']);
		}else{
			$blocks[$v[1]]['genres'] = $wd[$v[0]]['genres'];
		}
	}
	// add herkomsten to posters
	if(isset($wd[$v[0]]['herkomsten'])){
		if(is_array($blocks[$v[1]]['herkomsten'])){
			$blocks[$v[1]]['herkomsten'] = array_merge($blocks[$v[1]]['herkomsten'], $wd[$v[0]]['herkomsten']);
		}else{
			$blocks[$v[1]]['herkomsten'] = $wd[$v[0]]['herkomsten'];
		}
	}
}


shuffle($blocks);
//print_r($blocks);


?>