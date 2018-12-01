
<?php

$numBins = 32;
$maxPages = 4;

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';

require_once('ebay.php');

?>



<!DOCTYPE html>
	<link rel="stylesheet" type="text/css" href="style.css">
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
		<title>SHAST VII</title>
		<div style="text-align: center;">
			<h1 style="font-family: 'Arial Narrow'; font-weight: lighter">Super Hyper Advanced Search Tool VII</h1>
			<div class = 'row' >
				<form action="search.php" method="post">
					<input type="text" name="query" placeholder="Search..." value="<?=isset($_POST['query'])?$_POST['query']:''?>"/>
				</form>
			</div>
		</div>
	<?php
		if($_POST):
			$results = search($_POST['query']);
			$maxprice = getMaxPrice($results);

			$bins = bin($results);
			$bincrement = $bins->increment;
			$maxBinCount = 0;
			foreach($bins->bins as $bin) {
				if(count($bin) > $maxBinCount) $maxBinCount = count($bin);
			}

			?>
			<ul class="graph">
				<?php foreach($bins->bins as $bindex=>$bin): ?>
					<li class="graph-cell bin<?=$bindex?> tooltip" style="height: <?=number_format(count($bin) / $maxBinCount * 100)?>%;">
						<span class="tooltiptext">$MIN-$MAX</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<ul class = ranges>
				<li class ="range" style="text-align: left;">0</li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range"></li>
				<li class = "range" style="text-align: right;"><?=$maxprice?></li>
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
