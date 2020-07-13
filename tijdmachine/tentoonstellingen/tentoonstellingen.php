<?php

include("../functions.php");

if(!isset($_GET['year'])){
	$year = 1968;
}else{
	$year = $_GET['year'];
}

if($year<1935){
	$place = "Q2801130"; // Schielandshuis
}else{
	$place = "Q29569055"; // Hoofdgebouw Boijmans
}

$sparqlQueryString = "
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX wd: <http://www.wikidata.org/entity/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX wdt: <http://www.wikidata.org/prop/direct/>
PREFIX dbo: <http://dbpedia.org/ontology/>
SELECT DISTINCT ?exh ?label ?begin ?end ?actorarticle ?actorlabel ?actorimg WHERE {
  ?exh sem:eventType wd:Q464980 .
  ?exh sem:hasPlace  wd:" . $place . " .
  ?exh rdfs:label ?label .
  OPTIONAL{
	?exh sem:hasActor ?actor .
	?actor rdf:value ?actorwdid .
	?actorwdid rdfs:label ?actorlabel .
	OPTIONAL{
	  ?actorwdid foaf:isPrimaryTopicOf ?actorarticle .
	}
	?actor dbo:role \"tentoongestelde\" .
	SERVICE <https://query.wikidata.org/sparql> {
		OPTIONAL{
			?actorwdid wdt:P18 ?actorimg .
		}
	}
  }
  ?exh sem:hasEarliestBeginTimeStamp ?begin .
  ?exh sem:hasLatestEndTimeStamp ?end .
  BIND (year(?begin) AS ?startyear)
  FILTER(?startyear = " . $year . ")
} 
GROUP BY ?exh ?label ?begin ?end ?place ?placelabel
ORDER BY ASC(?begin)
LIMIT 100
";


//echo $sparqlQueryString;


$endpoint = 'https://api.druid.datalegend.net/datasets/menno/events/services/events/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

//print_r($data);

$exhibitions = array();

foreach ($data['results']['bindings'] as $row) { 

	if(strlen($row['actorarticle']['value'])){
		$actor = array(
			"label" => $row['actorlabel']['value'],
			"article" => $row['actorarticle']['value'],
			"img" => $row['actorimg']['value']
		);
	}

	if(isset($exhibitions[$row['exh']['value']]) && isset($actor)){
		$exhibitions[$row['exh']['value']]['actors'][] = $actor;
		unset($actor);
		continue;
	}elseif(isset($actor)){
		$exhibitions[$row['exh']['value']]['actors'][] = $actor;
		unset($actor);
	}

	


	$monthfrom = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $monthto = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");
    

	$from = date("j M",strtotime($row['begin']['value']));
	$from = str_replace($monthfrom, $monthto, $from);


	$to = date("j M",strtotime($row['end']['value']));
	$to = str_replace($monthfrom, $monthto, $to);

	if($from==$to){
		$to = "";
	}else{
		$to = " - " . $to;
	}

	$exhibitions[$row['exh']['value']]['from'] = $from;
	$exhibitions[$row['exh']['value']]['to'] = $to;
	$exhibitions[$row['exh']['value']]['label'] = $row['label']['value'];

}

//print_r($exhibitions);

?>

<table class="table">
<?php

foreach ($exhibitions as $exh) { 

	$img = false;
	$actors = array();
	if(isset($exh['actors'])){
		foreach ($exh['actors'] as $key => $value) {
			if(strlen($value['img'])){
				$img = $value['img'];
			}
			$actor = "";
			if(strlen($value['article'])){
				$actor .= '<a href="' . $value['article'] . '">';
			}
			if(strlen($value['label'])){
				$actor .= $value['label'];
			}
			if(strlen($value['article'])){
				$actor .= '</a>';
			}
			if(strlen($value['label'])){
				$actors[] = $actor;
			}
		}
	}
	
	?>
	
	<tr>
		<td style="width: 60px;">
		<?php if($img){ ?>
			
		<? }else{ ?>
			<div style="width: 60px; height: 50px; background-color: #EBEBEB;"></div>
		<? } ?>
	</td><td>
		<strong><?= $exh['label'] ?></strong>
		<br />
		<div class="evensmaller">
			<?= $from ?><?= $to ?><br />
			<?= implode(" | ",$actors) ?>
		</div>
	</td></tr>

	<?php 
} 
?>
</table>


<?php if($year<1935){ ?>
<p class="evensmaller">
Tot aan 1935 was Museum Boijmans in het Schielandshuis gevestigd.
</p>
<?php } ?>

<p class="evensmaller">
Met dank aan <a href="https://www.boijmans.nl/">Museum Boijmans van Beuningen</a> voor het beschikbaar stellen van de data.
</p>

