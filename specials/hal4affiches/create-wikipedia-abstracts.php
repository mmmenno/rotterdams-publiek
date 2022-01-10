<?php

set_time_limit(1);

$wptext = "";

if(isset($v['article']['value'])){

	$file = "wikipedia.json";

	if($json = file_get_contents($file)){
		$articles = json_decode($json,true);
	}else{
		$articles = array();
	}

	if(!array_key_exists($wdid,$articles)){
	
		$wpname = str_replace("https://nl.wikipedia.org/wiki/", "", $v['article']['value']);


		$url = "https://nl.wikipedia.org/w/api.php?action=query&prop=extracts&exchars=500&explaintext&titles=" . $wpname . "&format=json";

		$wpjson = file_get_contents($url);
		$wpdata = json_decode($wpjson,true);

		//print_r($wpname);

		if(isset($wpdata['query']['pages'])){
			foreach ($wpdata['query']['pages'] as $key => $value) {
				$wptext = $value['extract'];
				$wptitle = $value['title'];
			}
		}

		$wptext = preg_replace("/===([^=]+)===/", "<h5>$1</h5>", $wptext);
		$wptext = preg_replace("/==([^=]+)==/", "<h4>$1</h4>", $wptext);

		$wplink = ' <a target="_blank" class="wikipedialink" href="' . $v['article']['value'] . '">lees verder op Wikipedia</a>';

		$articles[$wdid] = array(
			"title" => $wptitle,
			"text" => $wptext,
			"link" => $wplink
		);

		$string = json_encode($articles);
		file_put_contents($file, $string);

		
	}
}
?>