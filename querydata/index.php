<?php


if(!isset($_GET['query'])){
	$err = array("error" => "parameter 'query' missing");
	die(json_encode($err));
}
if(!isset($_GET['endpoint'])){
	$err = array("error" => "parameter 'endpoint' missing");
	die(json_encode($err));
}
if(!isset($_GET['name'])){
	$err = array("error" => "parameter 'name' missing");
	die(json_encode($err));
}

$sparqlQueryString = $_GET['query'];
$endpointUrl = $_GET['endpoint'];


$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";
$jsonfile = 'queryresults/' . $_GET['name'] . ".json";
if(file_exists($jsonfile) && !isset($_GET['uncache']) ){
	$response = file_get_contents($jsonfile);
}else{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch,CURLOPT_USERAGENT,'RotterdamsPubliek');
	$headers = [
	    'Accept: application/sparql-results+json'
	];

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec ($ch);
	curl_close ($ch);

	file_put_contents($jsonfile, $response);
}

echo $response;
die;









