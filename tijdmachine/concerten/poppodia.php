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
SELECT ?concert ?artist ?artistname ?rating ?wikipedia ?date
	?location ?locationname WHERE {
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
	?concert schema:location [
		rdf:value ?location ;
		rdfs:label ?locationname ;
	] .
	BIND(year(xsd:dateTime(?date)) AS ?year)
	FILTER(?year = " . $year . ")
	FILTER(?locationname != \"Ahoy\")
	FILTER(?locationname != \"Stadion Feĳenoord\")
} 
#GROUP BY ?artist ?artistname ?rating ?wikipedia ?location ?locationname
ORDER BY ASC(?date)
LIMIT 150
";

$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);




$concerts = array();
$actors = array();

//print_r($data);

foreach ($data['results']['bindings'] as $k => $v) {

	$concerts[] = array(
		"artistname" => $v['artistname']['value'],
		"locationname" => $v['locationname']['value'],
		"location" => str_replace("http://www.wikidata.org/entity/","",$v['location']['value']),
		"wiki" => $v['wikipedia']['value'],
		"setlistlink" => $v['concert']['value'],
		"artist" => $v['artist']['value'],
		"datum" => dutchdate($v['date']['value'])
	);

	if(strlen($v['artist']['value'])){
		$actors[] = str_replace("http://www.wikidata.org/entity/","wd:",$v['artist']['value']);
	}

}


// now see if we can get images
$sparqlQueryString = "
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX wd: <http://www.wikidata.org/entity/>

SELECT ?item ?img WHERE {
	VALUES ?item { " . implode(" ",$actors) . " }
  	?item wdt:P18 ?img
}
";

$endpoint = 'https://query.wikidata.org/sparql';
$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

//print_r($data);
$actorimgs = array();
$actorimglinks = array();
foreach ($data['results']['bindings'] as $row) { 
	$actorimgs[$row['item']['value']] = $row['img']['value'];
	$pos = strrpos($row['img']['value'],"/") + 1;
	$filename = substr($row['img']['value'], $pos);
	$actorimglinks[$row['item']['value']] = "https://commons.wikimedia.org/wiki/File:" . $filename;
}


function dutchdate($date){

	$maanden = array("","jan","feb","maart","april","mei","juni","juli","aug","sept","okt","nov","dec");
	$dutch = date("j ",strtotime($date)) . $maanden[date("n",strtotime($date))] . date(" Y",strtotime($date));

	return $dutch;
}

//print_r($actorimgs);
?>

<table class="table">
<?php
foreach ($concerts as $concert) { 



	?>
	
	<tr>
		<td style="width: 60px;">
      		<?php if(isset($actorimgs[$concert['artist']])){ ?>
				<a target="_blank" href="<?= $actorimglinks[$concert['artist']] ?>">
					<img style="width: 60px;" src="<?= $actorimgs[$concert['artist']] ?>?width=100px" />
				</a>
			<?php }else{ ?>
				<div style="width: 60px; height: 50px; background-color: #929eda;"></div>
			<?php } ?>
		</td>
		<td>
			<?php if(strlen($concert['wiki'])){ ?>
				<strong><a target="_blank" href="<?= $concert['wiki'] ?>"><?= $concert['artistname'] ?></a></strong>
			<?php }else{ ?>
				<strong style="color:#ccc;"><?= $concert['artistname'] ?></strong>
			<?php } ?>
			<br />
			<span class="evensmaller">
				<a target="_blank" href="<?= $concert['setlistlink'] ?>"><?= $concert['datum'] ?></a> in <a href="/plekken/plek.php?qid=<?= $concert['location'] ?>"><?= $concert['locationname'] ?></a>
			</span><br />
	</td></tr>

	<?php 
} 
?>
</table>



<p class="evensmaller">
	Deze concerten komen van <a href="https://www.setlist.fm/search?query=city:%28Rotterdam%29" target="_blank">setlist.fm</a> (klik erheen via de datum). Daar vind je ook concerten buiten de zalenselectie die wij toegepast hebben. De afbeeldingen komen van <a href="https://commons.wikimedia.org/" target="_blank">Wikimedia Commons</a> en zijn allemaal CC-BY-SA gelicenseerd - klikken op de afbeelding brengt je naar grotere formaten en metadata (o.a. de maker) van de afbeelding.
</p>




