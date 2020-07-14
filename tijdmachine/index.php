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

	<link rel="stylesheet" href="assets/styles.css" />



	
</head>
<body>



<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><a href="/">Rotterdams Publiek</a> | <a href="?year=<?= $prev ?>">&laquo;</a> <?= $year ?> <a href="?year=<?= $next ?>">&raquo;</a></h1>
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

			<h2>Concerten Ahoy en De Kuip</h2>
			<div class="content" id="concerten"></div>

			

		</div>
		<div class="col-md-4">

			<h2>Op de poppodia</h2>
			<div class="content" id="concerten"></div>

			<h2>Affiches</h2>
			<div class="content" id="affiches"></div>

			<h2>Tentoonstellingen in Boijmans</h2>
			<div class="content" id="boijmans"></div>

			<h2>Vrouwen in de kunst</h2>
			<div class="content" id="vrouwen"></div>

			<h2>Nieuw in de R'dam kunstscene</h2>
			<div class="content" id="kunstscene"></div>

		</div>
		<div class="col-md-4">

			<h2>Gebeurtenissen in beeld</h2>
			<div class="content" id="gebeurtenissen"></div>

			
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
					$('#boijmans').load('tentoonstellingen/tentoonstellingen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "films"){
					$('#films').load('bioscopen/filmvoorstellingen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "bioscopen"){
					$('#bioscopen').load('bioscopen/bioscopen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "filmladder"){
					$('#filmladder').load('bioscopen/filmladder.php?year=<?= $year ?>');
				}else if(div.attr('id') == "vrouwen"){
					$('#vrouwen').load('rkd/vrouwen.php?year=<?= $year ?>');
				}else if(div.attr('id') == "kunstscene"){
					$('#kunstscene').load('rkd/kunstscene.php?year=<?= $year ?>');
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
			}else if(div.attr('id') == "boijmans" && <?= $year ?> < 1927){
				$(this).addClass('faded');
			}else if(div.attr('id') == "filmladder" && ( <?= $year ?> < 1980 || <?= $year ?> > 1989 )){
				$(this).addClass('faded');
			}
		});

	});


</script>



</body>
</html>
