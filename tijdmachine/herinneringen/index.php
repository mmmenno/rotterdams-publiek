<?php

include("../functions.php");


$year = $_GET['year'];

$url = "https://rotterdamspubliek-api.versie1.online/herinnering/jaar/" . $year;
$json = file_get_contents($url);

$memories = json_decode($json,true);


?>

<?php foreach ($memories as $memory) { ?>
	<div class="memory">
		<h4><?= $memory['titel'] ?></h4>
		<p><?= strip_tags($memory['bericht']) ?></p>
		<p class="credits">
			<a style="color: #fff; text-decoration: underline;" href="https://rotterdamspubliek-api.versie1.online/herinnering/gebruiker/<?= $memory['gebruikersnaam'] ?>"><?= $memory['gebruikersnaam'] ?></a>, over <a style="color: #fff; text-decoration: underline;" href="/plekken/plek.php?qid=<?= $memory['wikiId'] ?>">deze plek</a>
		</p>
	</div>
<?php } ?>




<p class="smaller">
Zelf een herinnering aan dit jaar? <a href="https://rotterdamspubliek-api.versie1.online/herinnering/form/<?= $year ?>">Deel 'm hier</a> als je denkt dat de culturele geschiedenis van Rotterdam er weer wat beter door belicht wordt.
</p>




