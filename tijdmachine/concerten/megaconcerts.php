<?php

include("../functions.php");


$year = $_GET['year'];

$sparqlQueryString = "
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX schema: <http://schema.org/>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
SELECT DISTINCT(?artist) ?artistname (SAMPLE(?bandimg) AS ?bandimg) ?rating ?wikipedia (MIN(?date) AS ?date) ?location ?locationname WHERE {
  ?concert a schema:MusicEvent .
  ?concert sem:hasTimeStamp ?date .
  ?concert schema:performer [
      owl:sameAs ?artist ;
      rdfs:label ?artistname ;
      schema:ratingValue ?rating ;
  ] .
  OPTIONAL{
    ?concert schema:performer/schema:subjectOf ?wikipedia .
  }
	SERVICE <https://query.wikidata.org/sparql> {
		OPTIONAL{
			?artist wdt:P18 ?bandimg .
		}
	}
  ?concert schema:location [
     rdf:value ?location ;
     rdfs:label ?locationname ;
  ] .
  BIND(year(xsd:dateTime(?date)) AS ?year)
  FILTER(?year = " . $year . ")
  FILTER(?locationname = \"Ahoy\" || ?locationname = \"Stadion Feĳenoord\")
} 
GROUP BY ?artist ?artistname ?rating ?wikipedia ?location ?locationname
ORDER BY ASC(?date)
LIMIT 50
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);




$concerts = array();
$bandimgs = array();

//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

	$concerts[] = array(
		"artistname" => $v['artistname']['value'],
		"locationname" => $v['locationname']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"wiki" => $v['wikipedia']['value'],
		"artist" => $v['artist']['value'],
		"img" => $v['bandimg']['value'],
		"datum" => dutchdate($v['date']['value'])
	);

	

}


function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

//print_r($concerts);
?>

<table class="table">
<?php
foreach ($concerts as $concert) { 

	
	?>
	
	<tr>
		<td style="width: 60px;">
      		<?php if(strlen($concert['img'])){ ?>
				<img style="width: 60px;" src="<?= $concert['img'] ?>?width=100px" />
			<?php }else{ ?>
				<div style="width: 60px; height: 50px; background-color: #929eda;"></div>
			<?php } ?>
		</td>
		<td>
			<strong><a target="_blank" href="<?= $concert['wiki'] ?>"><?= $concert['artistname'] ?></a></strong>
			<br />
			<span class="evensmaller">
				<?= $concert['datum'] ?> in <a href="/locaties/locatie.php?qid=<?= $concert['location'] ?>"><?= $concert['locationname'] ?></a>
			</span><br />
	</td></tr>

	<?php 
} 
?>
</table>



<p class="evensmaller">
	Deze concerten in Ahoy en De Kuip komen van <a href="https://www.setlist.fm/search?query=city:%28Rotterdam%29" target="_blank">setlist.fm</a> en het kan zijn dat ze daar actuelere informatie hebben. De afbeeldingen komen van <a href="https://commons.wikimedia.org/" target="_blank">Wikimedia Commons</a>.
</p>




