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

	<link rel="stylesheet" href="../assets/css/styles.css" />

	<script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>

	
</head>
<body>



<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><a href="../index.php">Rotterdams Publiek</a> | <a href="index.php">Helpen</a> | Begrippenlijst</h1>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-8">
			<p class="lead" style="margin-top: 40px;">
				Rotterdams Publiek is een website waarop het uitgaansverleden van Rotterdam wordt belicht. Tenminste, zo lijkt het. In werkelijkheid gaat de tijd binnen het project vooral op aan het maken en verbinden van data. Wat kernbegrippen:
			</p>

			<div class="row">
				<div class="col-md-6">


					<h2>Silo</h2>
					<p>
						Miljoenen foto's, krantenpagina's, documenten en andere maaksels, in honderden collecties, zijn de afgelopen twee decennia gedigitaliseerd en doorzoekbaar gemaakt. Prachtig!
					</p> 

					<p>
						Helaas zijn die collecties maar al te vaak silo's - op zichzelf staande data-eilandjes. Op die afzonderlijke eilandjes worden vaak wel trefwoordenlijsten gebruikt, maar elk eilandje heeft dan weer zijn eigen trefwoordenlijst.
					</p> 

					<p>
						Het <a href="https://stadsarchief.rotterdam.nl/">Stadsarchief</a> is in veel opzichten nog een silo, en de <a href="https://digitup.nl/">DIGITUP</a> collectie ook.
					</p> 

					<h2>Mismatch</h2>
					<p>
						Stel je zoekt foto's van Ahoy. In de ene collectie heet die evenementenlocatie 'Ahoy Rotterdam', bij een andere 'Rotterdam Ahoy' of 'Ahoy-hal' of simpelweg 'Ahoy'. Zie zo maar eens foto's uit verschillende collecties geautomatiseerd bij elkaar te krijgen.
					</p> 
					<p>
						En dan zijn er ook nog twee Ahoy's geweest, de eerste stond van 1950 tot 1966 naast wat nu het Natuurhistorisch Museum is. Verwarring alom.
					</p> 

				</div>
				<div class="col-md-6">

					<h2>Verbindingspunt</h2>
					<p>
						Om de data in die silo's beter te verbinden hebben we <em>verbindingspunten</em> nodig - gemeenschappelijk gebruikte identifiers voor bijvoorbeeld personen of plaatsen. In ons geval gaat het vooral om gebouwen: <a href="https://rotterdamspubliek.nl/plekken/">theaters, bioscopen, enzovoort</a>. 
					</p> 

					<h2>Qnummer</h2>
					<p>
						<a href="https://www.wikidata.org/">Wikidata</a> is een goede plek om die verbindingspunten te maken. Duizenden mensen werken daar samen aan het beschrijven van allerlei <em>dingen</em>, waarbij elk ding zijn eigen Qnummer krijgt.
					</p> 
					<p>
						Het <a href="https://rotterdamspubliek.nl/plekken/plek.php?qid=Q179426">huidige Ahoy</a> heeft op Wikidata het Qnummer <a href="http://www.wikidata.org/entity/Q179426">Q179426</a> gekregen, en <a href="https://rotterdamspubliek.nl/plekken/plek.php?qid=Q108773036">het eerste Ahoy</a> Qnummer <a href="http://www.wikidata.org/entity/Q108773036">Q108773036</a>. Als in de ene collectie nu wordt genoteerd dat men met 'Ahoy-hal' Q108773036 bedoelt, en in de andere collectie dat 'Ahoy' naar datzelfde Qnummer verwijst, dan is een ondubbelzinnige verbinding gelegd.
					</p> 

					<p>
						Zo'n Qnummer kan je op Wikidata trouwens van allerlei <em>statements</em> voorzien: Q108773036 <em>is een</em> evenementenlocatie, <em>heeft als bouwjaar</em> 1951 en <em>heeft als geografische locatie</em> de coördinaten zus en zo.
					</p>

				</div>
			</div>
		</div>
		<div class="col-md-4">

			<h2>Commons</h2>
			<p>
				De commons zijn hulpbronnen die gebruikt mogen worden door alle leden van een samenleving. Dat kan grond zijn of (schoon) water, maar ook kennis en cultuur.
			</p> 

			<p>
				<a href="https://commons.wikimedia.org/">Wikimedia Commons</a> bevat alle afbeeldingen (en video en audio) die je op Wikipedia tegenkomt. Het staat iedereen vrij die media te gebruiken.
			</p> 

			<h2>Crowdsourcen</h2>
			<p>
				Rijmt op outsourcen. Bij crowdsourcen vraagt men het publiek te helpen bij het invoeren, beschrijven of anderszins verbeteren van data. Vaak gaat het om zulke grote hoeveelheden - commercieel niet interessante - data dat betaald in laten voeren domweg geen optie is.
			</p> 
			<p>
				Het <a href="https://widgets.hetvolk.org/data-entry/start/678ec0d9-91a6-07cb-a7c9-d91c4fef852e">filmladderproject op hetvolk</a> is zo'n crowdsourceproject.
			</p> 

			<h2>Wereldwijd web van data</h2>
			<p>
				Als je, via die verbindingspunten, allerlei collecties en andere datasets met elkaar verbindt, ontstaat een web van linked data.
			</p> 
			<p>
				Vrijwilligers op Wikidata bouwen mee aan dat web. Datzelfde geldt voor mensen die Wikipedia artikelen schrijven of afbeeldingen op Wikimedia Commons plaatsen (en verbinden).
			</p> 
			<p>
				Ook de deelnemers aan het filmladderproject helpen mee - via de bioscoop wordt straks elke filmvoorstelling met dat web verbonden.
			</p> 

			<p>
				Rotterdams Publiek probeert eigenlijk het stukje van dat web te laten zien dat de uitgaanshistorie van Rotterdam behelst. Kijk maar eens hoe allerlei gegevens op basis van enkel het Qnummer van <a href="https://rotterdamspubliek.nl/plekken/plek.php?qid=Q179426">Ahoy</a> of bijvoorbeeld <a href="https://rotterdamspubliek.nl/plekken/plek.php?qid=Q4672497">het oude Luxor</a> worden getoond.

		</div>
	</div>
	
</div>