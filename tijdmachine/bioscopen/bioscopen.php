<?php

include("../functions.php");


$nextyear = $_GET['year']+1;

$sparqlQueryString = "
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT 	
?venue ?venuename ?wkt ?captime ?cap ?begin ?end ?wiki
WHERE {
	?venue a schema:MovieTheater .
  	?venue schema:temporalCoverage ?existence .
  	?existence sem:hasEarliestBeginTimeStamp ?begin .
  	OPTIONAL{
  		?existence sem:hasLatestEndTimeStamp ?end .
  	}
	FILTER(?begin <= \"" . $_GET['year'] . "\"^^xsd:gYear) .
  	?venue schema:location ?place .
	?venue schema:name ?venuename .
	?place schema:address/schema:addressLocality 'Rotterdam' .
  	?place geo:hasGeometry/geo:asWKT ?wkt .
  	OPTIONAL{
	  	?venue schema:maximumAttendeeCapacity ?capnode .
	  	?capnode schema:maximumAttendeeCapacity ?cap .
	  	?capnode sem:hasLatestBeginTimeStamp ?captime .
		FILTER(?captime <= \"" . $nextyear . "\"^^xsd:gYear)	
	}
  	OPTIONAL{
    	?venue schema:containedInPlace ?wiki .
  	}
}
ORDER BY ASC(?venue) DESC(?captime)
limit 250
";

$endpoint = "https://data.create.humanities.uva.nl/sparql";

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$queryurl = "https://data.create.humanities.uva.nl/#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdata.create.humanities.uva.nl%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&contentTypeConstruct=text%2Fturtle%2C*%2F*%3Bq%3D0.9&contentTypeSelect=application%2Fsparql-results%2Bjson%2C*%2F*%3Bq%3D0.9&outputFormat=table";



$cinemas = array();
$nrs = array();

foreach ($data['results']['bindings'] as $k => $v) {

	if(isset($v['end']['value'])){
		$endyear = substr($v['end']['value'],0,4);
		if($endyear < $nextyear){
			continue;
		}
	}

	if(!isset($cinemas[$v['venue']['value']])){

		$cinemas[$v['venue']['value']] = array(
			"bioscoop" => $v['venuename']['value'],
			"capacity" => $v['cap']['value'],
			"captime" => $v['captime']['value'],
			"begin" => $v['begin']['value'],
			"link" => $v['venue']['value']
		);
		if(isset($v['wiki']['value'])){
			$cinemas[$v['venue']['value']]['link'] = "/plekken/plek.php?qid=" . str_replace("http://www.wikidata.org/entity/","",$v['wiki']['value']);
		}
		$nrs[$v['venue']['value']] = $v['cap']['value'];
	}


	
}
arsort($nrs);
//print_r($nrs);
//print_r($cinemas);

?>

<table class="table">
<?php
foreach ($nrs as $k => $v) { 

	$total += $cinemas[$k]['capacity'];

	if(strlen($cinemas[$k]['captime'])){
		$capacity = $cinemas[$k]['capacity'];
		$description = "Sinds " . substr($cinemas[$k]['begin'],0,4) . ", aantal stoelen gemeten in " . substr($cinemas[$k]['captime'],0,4);
	}else{
		$capacity = "?";
		$description = "Sinds " . substr($cinemas[$k]['begin'],0,4) . ", aantal stoelen onbekend";
	}
	?>
	
	<tr>
		<td class="nroftd">
      		<div class="nrof"><?= $capacity ?></div>
		</td>
		<td>
			<strong><a href="<?= $cinemas[$k]['link'] ?>">
				<?= $cinemas[$k]['bioscoop'] ?>
			</a></strong><br />
			<span class="evensmaller">
				<?= $description ?>
			</span><br />
	</td></tr>

	<?php 
} 
?>
</table>





<?php if(count($cinemas)){ ?>
	<p class="smaller">
		De bioscopen zijn gesorteerd op capaciteit. De cijfers zijn zeker niet altijd recent en compleet, maar het lijkt erop dat er dit jaar zeker voor <strong><?= $total ?></strong> mensen een zitplaats was.
	</p>
<?php }else{ ?> 
	<p class="smaller">
		
	</p>
<?php } ?> 

<p class="smaller">
	<a target="_blank" href="<?= $queryurl ?>">SPARQL het zelf</a> in de Cinema Context data, op de CREATE sparql endpoint.
</p>




