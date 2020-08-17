<?php

include("../functions.php");



$sparqlQueryString = "
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX schema: <http://schema.org/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX rkd: <http://data.rkd.nl/def#>

SELECT ?startyear ?endyear ?deathdateyear ?artist ?name ?rkdid WHERE {
  ?event rdf:type rkd:Place_of_Activity ;
      schema:location \"Rotterdam\"@nl ;
      schema:startDate ?start ;
      schema:endDate ?end ;
      schema:actor ?actor .
  BIND(IF(COALESCE(xsd:datetime(str(?start)), '!') != '!',
    year(xsd:dateTime(str(?start))),\"2100-01-01\"^^xsd:dateTime) AS ?startyear ) .
  BIND(IF(COALESCE(xsd:datetime(str(?end)), '!') != '!',
    year(xsd:dateTime(str(?end))),\"2100-01-01\"^^xsd:dateTime) AS ?endyear ) .
  FILTER(?startyear <= " . $_GET['year'] . ") .
  FILTER(?endyear > " . $_GET['year'] . ") .
  ?event schema:actor ?actor .
  BIND(STRAFTER(STR(?actor),\"https://data.rkd.nl/artists/\") AS ?rkdid) .
  BIND(IRI(?actor) AS ?artist) .
  ?artist rkd:Death/schema:startDate ?deathdate .
  BIND(IF(COALESCE(xsd:datetime(str(?deathdate)), '!') != '!',
    year(xsd:dateTime(str(?deathdate))),\"1100-01-01\"^^xsd:dateTime) AS ?deathdateyear ) .
  FILTER(?deathdateyear >= " . $_GET['year'] . ") .
  ?artist schema:gender schema:Female ;
  	schema:name ?name .
} 
ORDER BY ASC(?startyear)
LIMIT 200
";

$queryurl = "https://data.netwerkdigitaalerfgoed.nl/rkd/rkdartists/sparql/rkdartists#query=" . urlencode($sparqlQueryString) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";

$endpoint = "https://api.data.netwerkdigitaalerfgoed.nl/datasets/rkd/rkdartists/services/rkdartists/sparql";

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);


?>

<table class="table">
<?php
foreach ($data['results']['bindings'] as $row) { 

	$link = "https://rkd.nl/explore/artists/" . $row['rkdid']['value'];

	?>
	
	<tr>
		<td>
		<a target="_blank" href="<?= $link ?>">
			<strong><?= $row['name']['value'] ?></strong>
		</a><br />
		<span class="smaller">
			werkzaam in R'dam vanaf <?= $row['startyear']['value'] ?>
		</span><br />
	</td></tr>

	<?php 
} 
?>
</table>


<?php





echo '<p class="smaller">';
echo "De vrouwelijke kunstenaars die in " . $_GET['year'] . " het langst in Rotterdam werkten staan bovenaan.";
echo '</p>';



//echo '<p class="smaller"><a target="_blank" href="' . $queryurl . '">SPARQL het zelf</a>, op het NDE Druid endpoint.</p>';

?>

<p class="evensmaller">
Met dank aan het <a href="https://rkd.nl">RKD</a> en <a href="https://www.netwerkdigitaalerfgoed.nl/">NDE</a> voor het beschikbaar stellen van de data!
</p>


