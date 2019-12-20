<?php



function sparql_results_as_json($endpointUrl,$name,$sparqlQueryString){

	$url = $endpointUrl . '?query=' . urlencode($sparqlQueryString) . "&format=json";
	$jsonfile = 'queryresults/' . $name . ".json";
	$jsonfile = '../querydata/queryresults/' . $name . ".json";
	if(file_exists($jsonfile) && !isset($_GET['uncache']) ){
		$json = file_get_contents($jsonfile);

		return $json;
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
		return $response;
	}

	return false;

}








