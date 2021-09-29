<?php

set_time_limit(1);

//print_r($gebied);
$wptext = "";

if(isset($venue['wikipedia'])){
	
	$wpname = str_replace("https://nl.wikipedia.org/wiki/", "", $venue['wikipedia']);


	$url = "https://nl.wikipedia.org/w/api.php?action=query&prop=extracts&exchars=500&explaintext&titles=" . $wpname . "&format=json";

	$wpjson = file_get_contents($url);
	$wpdata = json_decode($wpjson,true);

	//print_r($wpname);

	if(isset($wpdata['query']['pages'])){
		foreach ($wpdata['query']['pages'] as $key => $value) {
			$wptext = $value['extract'];
		}
	}

	$wptext = preg_replace("/===([^=]+)===/", "<h5>$1</h5>", $wptext);
	$wptext = preg_replace("/==([^=]+)==/", "<h4>$1</h4>", $wptext);

	$wptext .= ' <a target="_blank" class="wikipedialink" href="' . $venue['wikipedia'] . '">lees verder op Wikipedia</a>';
}
?>