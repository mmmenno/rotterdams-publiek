<?php


function getSparqlResults($endpoint,$query){

	// params
	$url = $endpoint . '?query=' . urlencode($query) . "&format=json";
	$urlhash = hash("md5",$url);
	$datafile = __DIR__ . "/data/" . $urlhash . ".json";
	$maxcachetime = 60*60*24*30;

	// get cached data if recent
	if(file_exists($datafile)){
		$mtime = filemtime($datafile);
		$timediff = time() - $mtime;
		if($timediff < $maxcachetime){
			$json = file_get_contents($datafile);
			return $json;
		}
	}

	
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

	// if valid results were returned, save file
	$data = json_decode($response,true);
	if(isset($data['results'])){
		file_put_contents($datafile, $response);
	}

	
	
	return $response;
}























?>