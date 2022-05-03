<?php

// get results from manual made query (due to authentication restrictions):

$json = file_get_contents('imgdata/imgqueryresults.json');
$commonsimages = json_decode($json,true);

$wdimgstring = str_replace(" ","_",urldecode($image));
$commons = array();



foreach ($commonsimages as $k => $v) {

  if(str_replace("http://www.wikidata.org/entity/","",$v['theater']) != $qid){
    continue;
  }

	if(strpos($v['image'],"\"")){ // soms zit er een " in een afbeeldingsbestandsnaam
		continue;
	}
	if($v['image'] == $wdimgstring){ // is al P18 in wikidata
		continue;
	}



	$commons[] = array(
    "beeld" => $v['beeld'],
    "image" => $v['image']
  );

}



//print_r($commons);

?>