<!DOCTYPE html>
<html>
<head>
	
	<title>Rotterdams Publiek | <?= $year ?></title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<script
	src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>

	<link rel="stylesheet" href="assets/css/styles.css" />

	<script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>

	
</head>
<body>



<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1>Rotterdams Publiek</h1>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-8">
			<p class="lead buildings" style="margin-top: 40px;">Theater in <a href="plekken/plek.php?qid=Q76161141">Casino Variété</a>, gabberfeesten in de <a href="plekken/plek.php?qid=Q2336130">Energiehal</a>, naar de film in <a href="plekken/plek.php?qid=Q76161140">Kriterion</a>, dansen bij <a href="plekken/plek.php?qid=Q93182833">Pschorr</a>, Tom Waits in <a href="plekken/plek.php?qid=Q81610581">Eksit</a>, Edith Piaf in <a href="plekken/plek.php?qid=Q4672497">Luxor</a> - Rotterdam heeft ook cultureel gezien een rijk verleden.</p>

			<p class="lead">Hier willen we je eigen uitgaansverleden weer in herinnering roepen. En het vermaak van vorige generaties illustreren. Want ben je ooit uit geweest in Rotterdam, dan ben je deel van een lange geschiedenis.</p>

			<div class="row">
				<div class="col-md-6">


					<h2 class="buildings">Plekken</h2>
					<p class="buildings">
						Bioscopen, danszalen, theathers, musea, etc. - <a href="/plekken/">hier vind je het overzicht</a>.
					</p>

					<img src="assets/img/amerikaansch-danspaviljoen.jpg" />

					<p class="onderschrift buildings">
						De danszaal van het <a href="plekken/plek.php?qid=Q76161121">Amerikaansch Danspaviljoen</a> aan het Stationsplein - een gebouw dat eerder Cirque Variété en later Circus Schouwburg huisvestte.
					</p>

					<h2>De data</h2>
					<p>
						Rotterdams Publiek kan bestaan dankzij 'linked open data'. De gebouwen bijvoorbeeld kunnen we rechtstreeks uit <a href="https://www.wikidata.org/">Wikidata</a> ophalen. Samen met andere 'Wikidatianen' hebben we ook een bijdrage geleverd aan het invoeren van die data.
					</p>

					<p>
						De databestanden die we zelf gemaakt of verzameld hebben, zoals de quotes uit krantenbank Delpher, de 'culturele gebeurtenissen' en de koppelingen tussen afbeeldingen uit het archief en locaties, delen we via <a href="https://github.com/mmmenno/rotterdams-publiek-data">onze data-repository op GitHub</a>.
					</p>

					<img src="assets/img/walhalla.jpg" />

					<p class="onderschrift">
						Eén van de honderden quotes uit Delpher komt uit <a target="_blank" href="https://resolver.kb.nl/resolve?urn=ddd:010212708:mpeg21:a0183">dit bericht</a>, dat toont hoe vrolijk zwieren het is in <span class="buildings"><a href="plekken/plek.php?qid=Q35567390">danszaal Walhalla</a></span>.
					</p>
					

				</div>
				<div class="col-md-6">

					<h2 class="timemachine">Tijdmachine</h2>
					<p class="timemachine">
						Hoe zag het culturele landschap eruit in <a href="tijdmachine/?year=1995">1995</a>? Of in <a href="tijdmachine/?year=1968">1968</a>? Of in <a href="tijdmachine/?year=1932">1932</a>? Welke tentoonstellingen, concerten en films kon je dat jaar zien? Onze <a href="tijdmachine/?year=1984">tijdmachine</a> dompelt je onder in de tijden van weleer.
					</p>

					<img src="assets/img/affiche-grand.png" />

					<p class="onderschrift">
						Affiche uit <span class="timemachine"><a href="tijdmachine/?year=1937">1937</a></span>. Het <span class="buildings"><a href="plekken/plek.php?qid=Q15875871">Grand Theatre</a></span> is bij het bombardement vernietigd.
					</p>

					<?php 
					/*
					<img src="assets/img/filmladder.jpg" />

					<p class="onderschrift timemachine">
						Nachtvoorstellingen in een filmladder uit <a href="tijdmachine/?year=1988">1988</a>. Calypso bestond nog tot in 1997, Lumière tot in 2003.
					</p>
					*/ 
					?>

					<h2>Een woord van dank</h2>
					<p>
						Zonder onze partners, vooraleerst het <a href="https://stadsarchief.rotterdam.nl/">Stadsarchief Rotterdam</a>, maar zeker ook <a href="https://digitup.nl/">DIG IT UP</a> en <a href="https://www.belvedererotterdam.nl/">Verhalenhuis Belvédère</a>, hadden we het niet voor elkaar gekregen. Dank!
					</p>
					<p>
						Dank ook aan de <a href="https://www.kb.nl/">Koninklijke Bibliotheek</a>, <a href="https://www.boijmans.nl/">Boijmans</a>, <a href="https://rkd.nl/">RKD</a>, het <a href="https://www.create.humanities.uva.nl/">UvA CREATE</a> team, <a href="https://www.setlist.fm/">setlist.fm</a>, <a href="https://www.netwerkdigitaalerfgoed.nl/">NDE</a>, het <a href="https://www.nationaalarchief.nl/">Nationaal Archief</a> en andere erfgoedinstellingen voor het (open) beschikbaar maken van data. Zo kunnen dit soort projecten gemaakt worden.
					</p>
					<p>
						Dit project is mogelijk gemaakt door een subsidie van het <a href="https://stimuleringsfonds.nl/">Stimuleringsfonds Creatieve Industrie</a>.
					</p>

					<img style="width: 60%" src="assets/img/SCI.jpg" />

					<img src="assets/img/mick.jpg" />

					<p class="onderschrift">
						In de <span class="timemachine"><a href="tijdmachine/?year=1982">tijdmachine</a></span> en bij concertpodia vind je overzichten van concerten, zoals dat van de Stones in <span class="buildings"><a href="plekken/plek.php?qid=Q330298">de Kuip</a></span>, in 1982. In <span class="buildings"><a href="plekken/plek.php?qid=Q81801550">HAL4</a></span> speelde dat jaar The Birthday Party.
					</p>

					
					<h2>Over ons</h2>
					<p>
						Rotterdams Publiek is bedacht en gemaakt door Menno den Engelse (<a href="http://islandsofmeaning.nl/">Islands of Meaning</a>), Thunnis van Oort, <a href="https://bertspaan.nl/">Bert Spaan</a>, Carinda Strangio (<a href="https://bitman.nl/">Bitman</a>) en Marie-Claire Dangerfield (<a href="https://stadsarchief.rotterdam.nl/">Stadsarchief Rotterdam</a>).
					</p>

					
				</div>
			</div>
		</div>
		<div class="col-md-4">

					
			<h2>Helpen</h2>
			<p>
				De gegevens op Rotterdams Publiek zijn voor een groot deel door vrijwilligers bijeengebracht - <a href="/helpen/index.php">hier lees je hoe je kunt helpen bij het beschrijven van het uitgaansverleden van de stad</a>.
			</p>

			<img src="../assets/img/stapeltje-ladders.jpg" />

			<p class="onderschrift">
				Op het moment loopt er een project waarin we jaren '80 filmladders invoeren.
			</p>


			<h2 class="interviews">Verhalen</h2>
			<p class="interviews">
				Persoonlijke getuigenissen zijn soms vastgelegd op beeld en geluid. Het Stadsarchief verzamelt ook video en audio. We <a href="/verhalen/">laten hier een aantal van deze getuigenissen zien</a>, en proberen daarbij fragmenten aan locaties te verbinden.
			</p>

			<h2 class="events">R'dam. Made it happen.</h2>
			<p class="events">
				Uit de archieven hebben we op beeld vastgelegde <a href="/gebeurtenissen/">gebeurtenissen</a> uit het culturele leven opgediept - van bouw tot brand, van soundcheck tot première, en daarnaast een incidentele hondenshow.
			</p>

			<?php 
			/*
			<img src="assets/img/demonstratie-fassbinder.jpg" />

			<p class="onderschrift events">
				Demonstratie in Rotterdam tegen opvoering Fassbinders `het vuil, de stad en de dood` voor <a href="plekken/plek.php?qid=Q76161173">theater De Lantaren</a>
			</p>
			*/
			?>

			<img src="assets/img/bouw-scala.jpg" />

			<p class="onderschrift buildings">
				De bouw van <a href="plekken/plek.php?qid=Q38238710">bioscoop Scala</a>, het latere Cinerama.
			</p>

			<?php

			$url = "http://memorylane.rotterdamspubliek.nl/herinnering/recent/10";
			//$url = "https://rotterdamspubliek-api.versie1.online/herinnering/recent/1";
			
			$json = curl_get_contents($url);
			$memories = json_decode($json,true);
			shuffle($memories);

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

			

			?>

			<h2>Kaart</h2>

			Op de <a href="/plekken/kaart/">kaart</a> zie je waar je wanneer naar welke plekken kon, als je zin had om te dansen, of in een ander uitje.

			<a href="/plekken/kaart/"><img src="assets/img/maps<?= rand(1,4) ?>.png" /></a>
			
			<h2>Herinneringen</h2>

			Bij <a href="/plekken/">plekken</a> en in de <a href="/tijdmachine/?year=1990">tijdmachine</a> kan je als bezoeker herinneringen delen, zoals deze:

			<?php 
			if(isset($memories)){
				$i = 0;
				foreach ($memories as $memory) { 
					$i++;
					if($i==2){
						break;
					}
				?>
				<div class="memory">
					<h4><?= $memory['titel'] ?></h4>
					<p><?= strip_tags($memory['bericht']) ?></p>
					<p class="credits">
						<a target="_blank" style="color: #fff; text-decoration: underline;" href="https://memorylane.rotterdamspubliek.nl/herinnering/gebruiker/<?= $memory['gebruikersnaam'] ?>"><?= $memory['gebruikersnaam'] ?></a>, over <a style="color: #fff; text-decoration: underline;" href="/plekken/plek.php?qid=<?= $memory['wikiId'] ?>">deze plek</a>
					</p>
				</div>
				<?php 
				} 
			}
			?>

			




		</div>
	</div>
	
</div>