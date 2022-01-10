<?php



include("../functions.php");

include("affiches.php"); 	// posters from druid

include("acts.php"); 		// acts (ds:subjects) from wikidata






?><!DOCTYPE html>
<html>
<head>
	
	<title>Rotterdams Publiek | Hal4 affiches</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<script
	src="https://code.jquery.com/jquery-3.2.1.min.js"
	integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	crossorigin="anonymous"></script>

	<link rel="stylesheet" href="../assets/css/styles.css" />
	<link rel="stylesheet" href="/specials/hal4affiches/styles.css" />

	<script async defer data-domain="rotterdamspubliek.nl" src="https://plausible.io/js/plausible.js"></script>

	
</head>
<body>



<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><a href="../index.php">Rotterdams Publiek</a> | specials | Hal4 affiches</h1>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="col-md-4" id="thumbs">


			<h2>Affiches</h2>

			<?php foreach($blocks as $poster){ 

				if($poster['class']!="poster"){
					continue;
				}
				$wdids = "";
				foreach($poster['acts'] as $act){
					$wdids .= " " . $act;
				}
				?>

				<img id="thumb-<?= $poster['id'] ?>" class="<?= trim($wdids) ?>" src="<?= str_replace("original","medium",$poster['img']) ?>" />

			<?php } ?>
			
			<div id="herkomsten">

				<h2>Herkomst</h2>

				<?php foreach($herkomsten as $k => $v){ 
					$size = 14 + ($v/3);
					?>
					<a style="font-size: <?= $size ?>px;" href=""><?= $k ?></a>
				<?php } ?>

				
			</div>

		</div>
		<div class="col-md-8">
			<div class="row">
				<div class="col-md-12" id="genres">

					<h2>Genres</h2>

					<?php foreach($genres as $k => $v){ 
						$size = 14 + (2*$v);
						?>
						<a style="font-size: <?= $size ?>px;" href=""><?= $k ?></a>
					<?php } ?>

					

				</div>
				
			</div>
			
			<div class="row" id="blocks">

				<?php foreach($blocks as $k => $v){ ?>

					<?php // wikidata images
					if($v['class'] == "img"){ 

						$classes = "img";
						if(isset($v['genres'])){
							foreach($v['genres'] as $genre){
								$classes .= " " . $genre;
							}
						}
						if(isset($v['herkomsten'])){
							foreach($v['herkomsten'] as $herkomst){
								$classes .= " " . $herkomst;
							}
						}
						?>
						<div id="<?= $v['id'] ?>" class="col-md-4 <?= $classes ?>">

							<img src="<?= $v['imgurl'] ?>?width=400px" />
							<p class="onderschrift"><?= $v['label'] ?></p>

						</div>
					<?php 
					} 
					?>


					<?php // posters
					if($v['class'] == "poster"){ 

						$classes = "poster";
						if(isset($v['genres'])){
							foreach($v['genres'] as $genre){
								$classes .= " " . $genre;
							}
						}
						if(isset($v['herkomsten'])){
							foreach($v['herkomsten'] as $herkomst){
								$classes .= " " . $herkomst;
							}
						}
						?>
						<div id="<?= $v['id'] ?>" class="col-md-4 <?= $classes ?>">

							<img src="<?= $v['img'] ?>" />

						</div>
					<?php 
					} 
					?>


					<?php // posters
					if($v['class'] == "article"){ 

						$classes = "article";
						if(isset($v['genres'])){
							foreach($v['genres'] as $genre){
								$classes .= " " . $genre;
							}
						}
						if(isset($v['herkomsten'])){
							foreach($v['herkomsten'] as $herkomst){
								$classes .= " " . $herkomst;
							}
						}
						?>
						<div id="<?= $v['id'] ?>" class="col-md-4 <?= $classes ?>">

							<h3><?= $v['title'] ?></h3>

							<p><?= $v['text'] ?></p>

							<?= $v['link'] ?>

						</div>
					<?php 
					} 
					?>

				<?php } ?>
			</div>

		</div>
	</div>
	
</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		$('#herkomsten a').click(function(event){
			event.preventDefault();

			$('#blocks div').hide();
			var herkomst = $(this).html();
			console.log(herkomst);

			$("."+herkomst).show();
		});
		

		$('#genres a').click(function(event){
			event.preventDefault();

			$('#blocks div').hide();
			var genre = $(this).html();
			console.log(genre);

			$("."+genre).show();
		});
		

		$('#thumbs img').click(function(event){
			$('#blocks div').hide();
			var posterid = $(this).attr("id").replace("thumb-","");
			console.log(posterid);

			var classList = $(this).attr('class').split(/\s+/);
			$.each(classList, function(index, wditem) {
				if(wditem.substring(0,1)=="Q"){
			    	console.log(wditem);
			    	$('#img' + wditem ).show();
			    	$('#wp' + wditem ).show();
				}
			});
			

			$("#"+posterid).show();
		});

	});

</script>