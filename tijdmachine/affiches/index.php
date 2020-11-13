<?php

include("../functions.php");


$year = $_GET['year'];

$sparqlQueryString = "
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
SELECT DISTINCT ?saobj ?img ?begin ?end ?loc WHERE {
  ?saobj dc:type <http://vocab.getty.edu/aat/300027221> .
  ?saobj foaf:depiction ?img .
  ?saobj sem:hasEarliestBeginTimeStamp ?begin .
  ?saobj sem:hasLatestEndTimeStamp ?end .
  ?saobj dct:spatial ?loc .
  FILTER(year(?begin) <= " . $year . ")
  FILTER(year(?end) >= " . $year . ")
} 
GROUP BY ?saobj ?img ?begin ?end ?loc
ORDER BY RAND()
LIMIT 10
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

$posters = array();
//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

	$posters[] = array(
		"uri" => $v['saobj']['value'],
		"img" => $v['img']['value'],
		"begin" => $v['begin']['value'],
		"end" => $v['end']['value'],
		"loc" => str_replace("http://www.wikidata.org/entity/","",$v['loc']['value'])
	);

}
?>

<table class="table">

	<?php
	foreach ($posters as $poster) { 

	
	?>
	
	<div class="poster">

		<a href="<?= $poster['uri'] ?>"><img src="<?= $poster['img'] ?>" /></a>

		<p class="onderschrift" style="text-align: right;"><?= $poster['begin'] ?> - <?= $poster['end'] ?> | <a href="/plekken/plek.php?qid=<?= $poster['loc'] ?>">meer over deze plek</a></p>

	</div>

	<?php 
	} 
	?>
</table>



<p class="evensmaller">
	<?php if(count($posters)==0){ ?>
		Geen affiches dit jaar :-(
	<?php } ?>
</p>




