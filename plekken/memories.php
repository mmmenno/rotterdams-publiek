<?php

/*
Met mijn opa en oma ging ik, elf jaar oud, een dagje naar Rotterdam - voor het eerst met de metro en naar de tentoonstelling Goden en Farao's. Die maakte zo'n grote indruk op me dat opa de catalogusvoor me kocht - glanzend zwart met een scarabee voorop. Die ging natuurlijk mee naar school toen ik niet lang daarna een spreekbeurt over Toetanchamon gaf.

De tentoonstelling Framing Sculptures liet zien hoe onder andere Brancusi zijn beeldhouwwerk in foto's gebruikte en helemaal helder werd dat - zowel voor mij als mijn toen tienjarige dochter - in een aparte ruimte waar je zelf kopieën van werken van Brancusi kon stapelen, schikken en belichten om vervolgens te fotograferen. Beeldhouwwerk als decorstuk, foto's als kunst.
*/

$url = "https://memorylane.rotterdamspubliek.nl/herinnering/id/" . $qid;
//$url = "https://rotterdamspubliek-api.versie1.online/herinnering/id/" . $qid;

$json = curl_get_contents($url);
$memories = json_decode($json,true);
//shuffle($memories);

function curl_get_contents($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}


//print_r($memories);

?>

<h3>Herinneringen</h3>

<?php foreach ($memories as $memory) { ?>
	<div class="memory">
		<h4><?= $memory['titel'] ?></h4>
		<p><?= strip_tags($memory['bericht']) ?></p>
		<p class="credits">
			<a target="_blank" style="color: #fff; text-decoration: underline;" href="https://memorylane.rotterdamspubliek.nl/herinnering/gebruiker/<?= $memory['gebruikersnaam'] ?>"><?= $memory['gebruikersnaam'] ?></a>, over
			<?php if(strlen($memory['datum'])){ ?>
				het jaar <a style="color: #fff; text-decoration: underline;" href="/tijdmachine/?year=<?= $memory['datum'] ?>"><?= $memory['datum'] ?></a>
			<?php }elseif(strlen($memory['periode_vanaf']) && strlen($memory['periode_tot'])){ ?>
				de periode <?= $memory['periode_vanaf'] ?> - <?= $memory['periode_tot'] ?>
			<?php } ?>
		</p>
	</div>
<?php } ?>




<p class="smaller">
Zelf een herinnering aan deze plek? <a href="https://memorylane.rotterdamspubliek.nl/herinnering/form/<?= $qid ?>">Deel je herinnering hier</a> en schrijf mee aan de culturele geschiedenis van Rotterdam.
</p>



