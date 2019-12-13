<?php


$sparqlQueryString = $_GET['query'];
$endpointUrl = $_GET['endpoint'];


$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";
$jsonfile = 'queryresults/' . $_GET['name'] . ".json";

if(file_exists($jsonfile)){
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









