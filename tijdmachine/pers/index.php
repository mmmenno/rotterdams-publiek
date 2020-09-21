<?php

include("../functions.php");


$year = $_GET['year'];

$sparql = "
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
SELECT ?text ?paper ?articledate ?articleurl ?location WHERE {
  ?i a schema:Quotation .
  ?i schema:about ?location .
  ?i schema:text ?text .
  ?i schema:isPartOf ?article .
  ?article schema:isPartOf ?paper .
  ?article rdf:value ?articleurl .
  ?article schema:datePublished ?articledate .
  BIND(year(xsd:dateTime(?articledate)) AS ?year)
  FILTER(?year = " . $year . ")
} 
ORDER BY ?articledate
LIMIT 10
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/rotterdamspubliek/services/rotterdamspubliek/sparql';

$json = getSparqlResults($endpoint,$sparql);
$data = json_decode($json,true);

$quotes = array();
foreach ($data['results']['bindings'] as $k => $v) {

	$quotes[] = array(
		"text" => nl2br($v['text']['value']),
		"paper" => $v['paper']['value'],
		"articleurl" => $v['articleurl']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"articledate" => dutchdate($v['articledate']['value'])
	);

}

function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}


?>

<?php 
if(count($quotes)){
	foreach ($quotes as $key => $value) {
		echo "<div class=\"quote\">";
		echo "<p><span>&ldquo;</span>" . $value['text'] . "<span>&rdquo;</span></p>";
		echo "<div class=\"smaller\"><a target=\"_blank\" href=\"" . $value['articleurl'] . "\">" . $value['articledate'] . ", " . $value['paper'] . "</a></div>";
		echo "<div class=\"evensmaller\">gaat over <a href=\"/plekken/plek.php?qid=" . $value['location'] . "\">deze plek</a></div>";
		echo "</div>";
	}
	
		
} 
?>



<p class="evensmaller">
	<?php if(count($quotes)){ ?>
		Deze quotes komen van <a href="https://www.delpher.nl/" target="_blank">Delpher</a>. Daar vind je zelf heel veel meer.
	<?php }else{ ?>
		Geen quotes gevonden uit dit jaar. Op <a href="https://www.delpher.nl/" target="_blank">Delpher</a> blader je zelf door tal van Rotterdamse kranten.
	<?php } ?>
</p>




