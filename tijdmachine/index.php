<?php

if(isset($_GET['year'])){
	$year = $_GET['year'];
}else{
	$year = 1984;
}
$prev = $year - 1;
$next = $year + 1;

?>
<!DOCTYPE html>
<html>
<head>
	
	<title>Rotterdams Publiek | <?= $year ?></title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<script
	src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>

	<link rel="stylesheet" href="/assets/css/styles.css" />
	<link rel="stylesheet" href="assets/styles.css" />

	<script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>

	
</head>
<body class="timemachine">



<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><a href="/">Rotterdams Publiek</a> | <a href="?year=<?= $prev ?>">&laquo;</a> <?= $year ?> <a href="?year=<?= $next ?>">&raquo;</a></h1>
		</div>
		<div class="col-md-12">
			<p class="small" style="margin-bottom: 0">klik op een kopje om gegevens te tonen of te verbergen ...</p>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-4">


			<h2>Filmladder deze week</h2>
			<div class="content" id="filmladder"></div>


			<h2>Meestgeprogrammeerde films</h2>
			<div class="content" id="films"></div>


			<h2>Alle bioscopen</h2>
			<div class="content" id="bioscopen"></div>

			<h2>Concerten in Ahoy en De Kuip</h2>
			<div class="content" id="megaconcerten"></div>

			

		</div>
		<div class="col-md-4">

			<?php 

			if($year < 1916){
				$map = 1;
			}elseif($year < 1935){
				$map = 2;
			}elseif($year < 1965){
				$map = 3;
			}else{
				$map = 4;
			}

			?>

			<h2>Op de kaart</h2>
			<a target="_blank" href="/plekken/kaart/#year=<?= $year ?>"><img src="/assets/img/maplink<?= $map ?>.png" /></a>
			<p class="small">
				Klik <a target="_blank" href="/plekken/kaart/#year=<?= $year ?>">naar de kaart</a> om te zien hoe Rotterdam er in dit jaar ongeveer uitgezien moet hebben, en waar je toen uit kon gaan.
			</p>

			<h2>Op de poppodia</h2>
			<div class="content" id="concerten"></div>

			<h2>Tentoonstellingen in Boijmans</h2>
			<div class="content" id="boijmans"></div>

			<h2>Tentoonstellingen elders</h2>
			<div class="content" id="elders"></div>

			<h2>Vrouwen in de kunst</h2>
			<div class="content" id="vrouwen"></div>

			<h2>Nieuw in de R'dam kunstscene</h2>
			<div class="content" id="kunstscene"></div>

		</div>
		<div class="col-md-4">

			<h2>Affiches</h2>
			<div class="content" id="affiches"></div>

			<h2>R'dam. Made it happen.</h2>
			<div class="content" id="gebeurtenissen"></div>

			<h2>In de pers</h2>
			<div class="content" id="pers"></div>

			<h2>Herinneringen</h2>
			<div class="content" id="memories"></div>

			
		</div>
	</div>
</div>


<script>

	

	

	$(document).ready(function(){

		$('h2').click(function(){
			var div = $(this).next('div');
			console.log(div.attr('id'));

			if(div.html()==""){
				console.log('leeg!');

				div.append('<div class="loader"></div>');

				if(div.attr('id') == "boijmans"){
					$('#boijmans').load('tentoonstellingen/tentoonstellingen-wiki.php?year=<?= $year ?>');
				}else if(div.attr('id') == "elders"){
					$('#elders').load('tentoonstellingen/tentoonstellingen-wiki-not-boijmans.php?year=<?= $year ?>');
				}else if(div.attr('id') == "films"){
					$('#films').load('bioscopen/filmvoorstellingen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "bioscopen"){
					$('#bioscopen').load('bioscopen/bioscopen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "filmladder"){
					$('#filmladder').load('bioscopen/filmladder.php?year=<?= $year ?>');
				}else if(div.attr('id') == "megaconcerten"){
					$('#megaconcerten').load('concerten/megaconcerts.php?year=<?= $year ?>');
				}else if(div.attr('id') == "concerten"){
					$('#concerten').load('concerten/poppodia.php?year=<?= $year ?>');
				}else if(div.attr('id') == "gebeurtenissen"){
					$('#gebeurtenissen').load('gebeurtenissen/index.php?year=<?= $year ?>');
				}else if(div.attr('id') == "affiches"){
					$('#affiches').load('affiches/index.php?year=<?= $year ?>');
				}else if(div.attr('id') == "pers"){
					$('#pers').load('pers/index.php?year=<?= $year ?>');
				}else if(div.attr('id') == "vrouwen"){
					$('#vrouwen').load('rkd/vrouwen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "kunstscene"){
					$('#kunstscene').load('rkd/kunstscene.php?year=<?= $year ?>');
				}else if(div.attr('id') == "memories"){
					$('#memories').load('herinneringen/index.php?year=<?= $year ?>');
				}
			}

			div.toggle();
		});


		// fade link color in contentless years
		$( "h2" ).each(function() {
  			var div = $(this).next('div');
			//console.log(div.attr('id'));

			if(div.attr('id') == "films" && (<?= $year ?> < 1895 || <?= $year ?> > 1948)){
				$(this).addClass('faded');
			}else if(div.attr('id') == "bioscopen" && <?= $year ?> < 1912){
				$(this).addClass('faded');
			}else if(div.attr('id') == "concerten" && <?= $year ?> < 1967){
				$(this).addClass('faded');
			}else if(div.attr('id') == "megaconcerten" && <?= $year ?> < 1967){
				$(this).addClass('faded');
			}else if(div.attr('id') == "boijmans" && <?= $year ?> < 1927){
				$(this).addClass('faded');
			}else if(div.attr('id') == "filmladder" && ( <?= $year ?> < 1970 || <?= $year ?> > 1989 )){
				$(this).addClass('faded');
			}else if(div.attr('id') == "affiches" && ( <?= $year ?> < 1845 || <?= $year ?> > 1943 )){
				$(this).addClass('faded');
			}else if(div.attr('id') == "elders" && ( <?= $year ?> < 1940 )){
				$(this).addClass('faded');
			}else if(div.attr('id') == "memories" && ( <?= $year ?> < 1950 )){
				$(this).addClass('faded');
			}
		});

	});


</script>



</body>
</html>
