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
SELECT ?i ?iLabel ?url ?afb ?loc ?locLabel ?mainsub ?mainsubLabel ?mainsubafb ?mainsubwp ?begin ?eind WHERE {
  ?i wdt:P31/wdt:P279 wd:Q464980 .
  ?i wdt:P276 ?loc .
  ?loc wdt:P131 wd:Q2680952 .
  MINUS { ?i wdt:P276 wd:Q679527 . }
  MINUS { ?i wdt:P664 wd:Q679527 . }
  ?i wdt:P580 ?begin .
  ?i wdt:P582 ?eind .
  OPTIONAL {
    ?i wdt:P973 ?url .
  }
  OPTIONAL {
    ?i wdt:P18 ?afb .
  }
  OPTIONAL {
    ?i wdt:P921 ?mainsub .
    OPTIONAL{
    	?mainsub wdt:P18 ?mainsubafb .
    }
    OPTIONAL{
	    ?mainsubwp schema:about ?mainsub ;
	             schema:inLanguage ?lang ;
	             schema:isPartOf [ wikibase:wikiGroup \"wikipedia\" ] .
	    FILTER(?lang in ('nl'))
    }
  }
  BIND (year(?begin) AS ?startyear)
  FILTER(?startyear = " . $year . ")
  SERVICE wikibase:label { bd:serviceParam wikibase:language \"nl,en\". }
} 
ORDER BY DESC(?begin)
";


//echo $sparqlQueryString;


$endpoint = 'https://query.wikidata.org/sparql';

$json = getSparqlResults($endpoint,$sparqlQueryString);
$data = json_decode($json,true);

//print_r($data);

$exhibitions = array();
$actors = array();

foreach ($data['results']['bindings'] as $row) { 

	if(strlen($row['mainsub']['value'])){
		$exhibitions[$row['i']['value']]['actors'][$row['mainsub']['value']] = array(
			"wd" => $row['mainsub']['value'],
			"label" => $row['mainsubLabel']['value'],
			"wp" => $row['mainsubwp']['value'],
			"afb" => $row['mainsubafb']['value']
		);
	}


	$monthfrom = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    $monthto = array("januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");

	$from = date("j M",strtotime($row['begin']['value']));
	$from = str_replace($monthfrom, $monthto, $from);

	$to = date("j M",strtotime($row['eind']['value']));
	$to = str_replace($monthfrom, $monthto, $to);

	if($from==$to){
		$to = "";
	}else{
		$to = " - " . $to;
	}

	if(date("Y",strtotime($row['eind']['value'])) != $year){
		$to .= " '" . substr(date("Y",strtotime($row['eind']['value'])),2,2);
	}


	$exhibitions[$row['i']['value']]['from'] = $from;
	$exhibitions[$row['i']['value']]['to'] = $to;
	$exhibitions[$row['i']['value']]['label'] = $row['iLabel']['value'];
	$exhibitions[$row['i']['value']]['url'] = $row['url']['value'];
	$exhibitions[$row['i']['value']]['wd'] = $row['i']['value'];
	$exhibitions[$row['i']['value']]['afb'] = $row['afb']['value'];

	if($row['loc']['value'] == "http://www.wikidata.org/entity/Q2042754"){
		$row['loc']['value'] = "http://www.wikidata.org/entity/Q80815548";
	}
	
	$where = '<a href="/plekken/plek.php?qid=' . str_replace("http://www.wikidata.org/entity/","",$row['loc']['value']) . '">' . $row['locLabel']['value'] . '</a> | ';
	
	$exhibitions[$row['i']['value']]['where'] = $where;

}

//print_r($exhibitions);


?>

<table class="table">
<?php

foreach ($exhibitions as $exh) { 

	$img = false;
	$actors = array();
	$img = "";
	if(isset($exh['actors'])){
		foreach ($exh['actors'] as $key => $value) {
			$actor = "";
			if(strlen($value['wp'])){
				$actor .= '<a target="_blank" href="' . $value['wp'] . '">';
				$actor .= '<img class="wpicon" src="/assets/img/wp.png" />';
			}else{
				$actor .= '<a target="_blank" href="' . $value['wd'] . '">';
				$actor .= '<img class="wdicon" src="/assets/img/wdicon.png" />';
			}
			if(strlen($value['label'])){
				$actor .= $value['label'];
			}
			if(strlen($value['wp'])){
				$actor .= '</a>';
			}
			if(strlen($value['label'])){
				$actors[] = $actor;
			}

			if(strlen($value['afb'])){
				$img = $value['afb'];
			}
		}
	}

	if(strlen($exh['afb'])){
		$img = $exh['afb'];
	}

	if(strlen($exh['url'])){
		$label = '<a target="_blank" href="' . $exh['url'] . '">' . $exh['label'] . '</a>';
	}else{
		$label = $exh['label'];
	}
	
	?>
	
	<tr>
		<td style="width: 60px;">
			<a target="_blank" href="<?= $exh['wd'] ?>">
				<?php if($img){ ?>
					<img style="width: 60px;" src="<?= $img ?>?width=100px" />
				<?php }else{ ?>
					<div style="width: 60px; height: 50px; background-color: #929eda;"></div>
				<?php } ?>
			</a>
	</td><td>
		<strong><?= $label ?></strong>
		<br />
		<span class="evensmaller">
			<?= $exh['where'] ?><?= $exh['from'] ?><?= $exh['to'] ?><br />
			<?= implode(" | ",$actors) ?>
		</span>
	</td></tr>

	<?php 
} 
?>
</table>




<p class="evensmaller">
De tentoonstellingen komen van Wikidata. Als er een url van de tentoonstelling bekend is klikt de titel daarheen. Als er een 'onderwerp' van de tentoonstelling bekend is linken we zo mogelijk naar Wikipedia, en anders naar het Wikidata item van het onderwerp. Het plaatje, en als dat er niet is het blauwe vlakje, linkt naar het Wikidata item van de tentoonstelling.
</p>

