
<?php

$numBins = 32;
$maxPages = 5;

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';

require_once('ebay.php');

?>

<!DOCTYPE html>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="style.css">

	<script type="text/javascript">
		var current;

		function fadeInto(bin) {

			newDiv = $('div.bin'+bin);
			current.fadeOut(125, function() {
				newDiv.fadeIn(125);
			});
			current = newDiv;

			return false;
		}

		$(document).ready(function() {
			current = $('div#dummy');
			for(var i = 0; i < <?=$numBins?>; i++) {
				$('li.bin'+i).click(function(){
					fadeInto(parseInt($(this).attr('class').substring(14)));
				});
			}
			$('.loading').hide();
			$('.loading').removeClass('loading');
			$('form').submit(function(){
				$('ul.graph').hide();
				$('div.xaxis').hide();
				current.hide();
				$('div#loadingDiv').show();
			});
		});
	  $( function() {
	    $( document ).tooltip();
	  } );
	</script>

	<meta charset = "utf-8">
		<title>SHAST VII</title>
		<div style="text-align: center;">
			<h1 style="font-family: 'Arial Narrow'; font-weight: lighter">Super Hyper Advanced Search Tool VII</h1>
			<form action="search.php" method="get">
				<input type="text" name="q" placeholder="Search..." value="<?=isset($_GET['q'])?$_GET['q']:''?>"/>
			</form>
		</div>
	<?php
		if(isset($_GET['q'])):
			$results = search($_GET['q']);
			if(count($results) === 0): ?>
				<p class="err">No results found.</p>
			<?php
				die();
			endif;
			$maxprice = getMaxPrice($results);
			echo '<p class="count">'.count($results).' results';
			//echo "Highest price = \$".number_format($maxprice, 2)."<br>";

			$bins = bin($results);
			$bincrement = $bins->increment;
			$maxBinCount = 0;
			foreach($bins->bins as $bin) {
				if(count($bin) > $maxBinCount) $maxBinCount = count($bin);
			}

			?>
			<ul class="graph">
				<?php foreach($bins->bins as $bindex=>$bin):
					$minPrice = $bindex * $bins->increment;
					$maxPrice = $minPrice + $bins->increment - 0.01; ?>
					<li class="graph-cell bin<?=$bindex?>" style="height: calc(<?=number_format(count($bin) / $maxBinCount * 100, 2).(count($bin)>0?'% + 2px':'')?>);" title="$<?=number_format($minPrice, 2)." – \$".number_format($maxPrice,2)." (".count($bin)." result".(count($bin)===1?'':'s').")"?>"></li>
				<?php endforeach; ?>
			</ul>
			<div class="xaxis">
				<div class="min">$0</div>
				<div class="max">$<?=number_format(ceil($numBins * $bins->increment))?></div>
				<span>Price</span>
			</div>
			<?php
			foreach($bins->bins as $bindex=>$bin): ?>
				<strong>
					<?php
					$minPrice = $bindex * $bins->increment;
					$maxPrice = $minPrice + $bins->increment - 0.01;
					echo "<div class=\"bin bin$bindex loading\">\$".number_format($minPrice, 2)." – \$".number_format($maxPrice,2).": " . count($bin) . " results" ?>
				</strong><br>
				<ol class="bin bin<?=$bindex?>">
					<?php foreach($bin as $item): ?>
						<li<?=$item->qty>1?' class="lot"':''?>>
							<a href="<?=$item->url?>"><?=$item->title?></a><span class="price"><?=number_format($item->price, 2)?></span><span class="qty"><?=$item->qty?></span><span class="each"><?=number_format($item->price/$item->qty, 2)?></span><span class="eachlabel"> each</span>
						</li>
					<?php endforeach; ?>
				</ol></div>
			<?php endforeach; ?>
			<div id="dummy">Click a bar in the histogram to view listings in that price bin.</div>
	<?php endif;?>
<div class="loading" id="loadingDiv">
	Loading.
</div>
