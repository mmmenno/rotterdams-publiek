<!doctype html>
<html lang="nl">
<head>
	<meta charset="utf-8">

	<title>Rotterdams Publiek</title>
	<meta name="description" content="Rotterdams Publiek - culturele geschiedenissen van Rotterdammers">
	<meta name="author" content="Islands of Meaning">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/styles.css">

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

  
	
</head>

<body>

	<div class="container-fluid">
		<div class="row">
			<div class="col-md black">
				<h2>Rotterdams Publiek</h2>

				<p>
					maakt het uitgaansverleden van Rotterdammers inzichtelijk.
				</p>

				<p>
					We doen dit vooral door <a href="">data</a> te verbeteren en verbinden. Het project bevindt zich in de opbouwfase.
				</p>

				<p>
					Dit project is mede mogelijk gemaakt door <a href="https://stimuleringsfonds.nl/">het Stimuleringsfonds</a>.
				</p>
			</div>
			<div class="col-md quote">
				<blockquote>&ldquo;Quote uit interview&bdquo;</blockquote>
			</div>
			<div class="col-md abt-interviews">
				<h2>Interviews</h2>

				<p>
					We vroegen Rotterdammers naar hun uitgaansverleden. Lees en bekijk hun verhalen in het <a href="">interview overzicht</a>.
			</div>
		</div>
		<div class="row">
			<div class="col-md abt-locations">
				<h2>De Zalen</h2>
				<p>
					Bioscopen, theaters, concertzalen, clubs en musea - waar staan en stonden ze. Je kan het allemaal bekijken op <a href="locaties/">de kaart met locaties</a>.
				</p>
				<p>
					Als je dat fijner vindt, er is ook <a href="locaties/lijst.php">een lijstweergave</a>.
				</p>
			</div>
			<div class="col-md abt-events">
				<h2>
					ROTTERDAM.<br />
					MADE IT<br />
					HAPPEN.
				</h2>
				<p>
					Premières, festivals, koninklijk bezoek - op beeld vastgelegde gebeurtenissen uit het culturele leven van de stad tonen we in <a href="events/index.php">ons gebeurtenissenoverzicht</a>.
				</p>
			</div>
			<div class="col-md quote">
				<blockquote>&ldquo;Werk in uitvoering! Dit is nog maar het prototype!&bdquo;</blockquote>
			</div>
		</div>
		<div class="row">
			<div class="col-md quote">
				<blockquote>&ldquo;Quote uit interview&bdquo;</blockquote>
			</div>
			<div class="col-md abt-timemachine">
				<h2>Tijdmachine</h2>
				<p>Teleporteer jezelf naar R'dam in het jaar...</p>
				<form id="timemachineform">
					<select name="millenium">
						<option>1</option>
						<option>2</option>
					</select>
					<select name="century">
						<option>9</option>
						<option>0</option>
					</select>
					<select name="decade">
						<option>0</option>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
						<option>6</option>
						<option>7</option>
						<option selected="selected">8</option>
						<option>9</option>
					</select>
					<select name="year">
						<option>0</option>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option selected="selected">4</option>
						<option>5</option>
						<option>6</option>
						<option>7</option>
						<option>8</option>
						<option>9</option>
					</select>

					<button>🚀</button>
				</form>
				<p>... en bekijk het culturele landschap van dat moment.</p>
			</div>
			<div class="col-md abt-actors">
				<h2>Wie is wie?</h2>
				<p>
					Ray Charles in De Doelen, Jayne Mansfield op de middenstip van het Kasteel en Salvador Dali in Museum Boijmans. Wie drukte zijn of haar stempel op het culturele landschap? Of was toevallig in de buurt? Check onze <a href="">wie-is-wie pagina</a>.
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col-md abt-war">
				<h2>Oorlog</h2>
				<p>
					Het bombardement verwoestte een groot deel van de stad, en daarmee ook veel uitgaansgelegenheden. Desondanks ging het culturele leven in de oorlogsjaren daarna gewoon door - mensen gingen naar de bioscoop, Boijmans stelde "het Duitsche boek van heden" tentoon, maar ook "de prentkunst rondom Rubens". Op <a href="">ons oorlogsoverzicht</a> krijg je een beeld.
				</p>
			</div>
			<div class="col-md quote">
				<blockquote>&ldquo;Quote uit interview&bdquo;</blockquote>
			</div>
			<div class="col-md quote">
				<blockquote>&ldquo;Quote uit interview&bdquo;</blockquote>
			</div>
		</div>
		
	</div>

<script type="text/javascript">
	
	$(document).ready(function() {

		$("#timemachineform button").click(function(){
			var mil = $('select[name="millenium"]').val();
			var cent = $('select[name="century"]').val();
			var dec = $('select[name="decade"]').val();
			var jaar = $('select[name="year"]').val();

			var year = mil + cent + dec + jaar;
			//console.log(year);

			window.location.href = "/timemachine/?year=" + year;

			return false;
		});
	});
</script>

</body>
</html>