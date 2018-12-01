
<?php

$numBins = 32;
$maxPages = 4;

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';

require_once('ebay.php');

?>



<!DOCTYPE html>
	<style>
		body {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			font-family: "fira-sans-2", Verdana, sans-serif;
		}

		.graph {
			align-items: flex-end;
			align-self: center;
			padding: 0;
			margin: 0;
			width: 75%;
			height: 20em;
			list-style: none;
			display: flex;
			justify-content: space-evenly;
			flex-wrap: nowrap;
			border-bottom: 1pt solid #ccc;
			border-left: 1pt solid #ccc;
			margin: 1em;
			margin-left: auto;
			margin-right: auto;
		}

		.graph-cell {
			background: #0f7ade;
			width: 6.25%;
			margin-left: 2px;
			margin-right: 2px;
			transition: .25s;
		}
		.graph-cell:hover {
			background: #2f9cff;
			transition: .25s;
			cursor: pointer;
		}
	</style>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

	<script type="text/javascript">
		var current = $('#dummy');

		function fadeInto(bin) {
			/*
			newDiv = $('div.bin'+bin);
			current.stop(true).fadeOut(500, function() {
				$(this).remove();
			});
			current = newDiv;
			current.fadeIn(500);
			*/
			var newDiv = $('div.bin'+bin);
			console.log('div.bin'+bin);
			current.hide();
			current = newDiv;
			current.show();
			return false;
		}

		$(document).ready(function() {
			for(var i = 0; i < <?=$numBins?>; i++) {
				$('li.bin'+i).click(function(){
					fadeInto(parseInt($(this).attr('class').substring(14)));
				});
				$('div.bin'+i).hide();
			}
		});
	</script>

	<meta charset = "utf-8">
		<title>Super Hyper Advanced Search Tool VII</title>
		<div style="text-align: center;">
			<h1>Super Hyper Advanced Search Tool VII</h1>
			<div class = 'row' >
				<form action="search.php" method="post">
					Search: <input type="text" name="query" placeholder="Search..." value="<?=isset($_POST['query'])?$_POST['query']:''?>"/>
					<input type="submit" />
				</form>
			</div>
		</div>
	<?php
		if($_POST):
			$results = search($_POST['query']);
			$maxprice = getMaxPrice($results);
			echo "Highest price = \$".number_format($maxprice, 2)."<br>";

			$bins = bin($results);
			$bincrement = $bins->increment;
			$maxBinCount = 0;
			foreach($bins->bins as $bin) {
				if(count($bin) > $maxBinCount) $maxBinCount = count($bin);
			}

			?>
			<ul class="graph">
				<?php foreach($bins->bins as $bindex=>$bin): ?>
					<li class="graph-cell bin<?=$bindex?>" style="height: <?=number_format(count($bin) / $maxBinCount * 100)?>%;"></li>
				<?php endforeach; ?>
			</ul>
			<?php
			foreach($bins->bins as $bindex=>$bin): ?>
				<strong>
					<?php
					$minPrice = $bindex * $bins->increment;
					$maxPrice = $minPrice + $bins->increment - 0.01;
					echo "<div class=\"bin$bindex\">Bin $bindex (\$".number_format($minPrice, 2)," - \$".number_format($maxPrice,2)."): " . count($bin) . " items" ?>
				</strong><br>
				<ol class="bin bin<?=$bindex?>">
					<?php foreach($bin as $item): ?>
						<li>
						<?php if($item->qty > 1): ?>
							Lot of <?=$item->qty?>:
						<?php endif; ?>
							<a href="<?=$item->url?>"><?=$item->title?></a> ($<?=number_format($item->price, 2)?>)
						</li>
					<?php endforeach; ?>
				</ol></div>
			<?php endforeach;
		endif;?>

	<div id="dummy"></div>
