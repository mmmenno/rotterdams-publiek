<?php


$year = $_GET['year'];

if(substr($_SERVER['HTTP_HOST'],0,3) == "loc"){
	$json = @file_get_contents("http://rotterdamspubliek.nl/files/filmladders-per-jaar/" . $year . "/" . $year . ".json");
}else{
	$json = @file_get_contents("/var/www/html/files/filmladders-per-jaar/" . $year . "/" . $year . ".json");
}

if($json === false){

	echo '<p class="small">Geen filmladder gevonden voor dit jaar</p>';
	die;

}else{


	$data = json_decode($json,true);

	foreach ($data as $key => $value) {
		
		$now = time();
		$dateincurrentyear = str_replace("/","-",str_replace($year,date("Y",time()),$value['datum']));
		$ladder = strtotime($dateincurrentyear);
		//echo $now . " - " . $ladder . "\n";
		if($ladder > $now){
			$thisweek = $last;
			//print_r($thisweek);
			break;
		}

		$last = $value;
	}

	if(!isset($thisweek)){
		$thisweek = $data[0];
	}
}

?>

<img src="https://rotterdamspubliek.nl/files/filmladders-per-jaar/<?= $thisweek['img'] ?>" />

<p class="evensmaller">
	De filmladder komt uit <em><?= $thisweek['krant'] ?></em> van <?= $thisweek['datum'] ?>, pagina <?= $thisweek['pagina'] ?>. Deze krant kan je ook <a target="_blank" href="<?= $thisweek['uri'] ?>">in z'n geheel bekijken in Delpher</a>.
</p>




