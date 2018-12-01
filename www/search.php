
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
	<style>
		body {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			font-family: "fira-sans-2", Verdana, sans-serif;
			overflow-y: scroll;
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
			margin-left: auto;
			margin-right: auto;
			padding-top: 1em;s
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
		.ui-tooltip {
			border: 1px solid #0f7ade !important;
			box-shadow: 0 0 7px #0f7ade;
			background-color: whitesmoke;
		}
		p.count {
			font-size: large;
			font-weight: bold;
		}
		div.xaxis {
			width: calc(75% + 2ex);
			margin-left: auto;
			margin-right: auto;
			color: #666;
			font-size: 10pt;
			text-align: center;
			padding-bottom: 1em;
		}
		div.xaxis>div.min {
			float: left;
		}
		div.xaxis>div.max {
			float: right;
		}
		div.xaxis>span {
			font-style: italic;
		}
		input{
	        border-radius: 4px;
	        border:2px solid #ccc;
	        padding: 10px;
		}
		input[type=text] {
			width: 50em;
	    }
		div#dummy {
			font-size: large;
			font-style: italic;
			color: #666;
		}
		div.loading {
			display: none;
		}
		div.bin {
			width: 100%;
			text-align: center;
		}
		ol.bin {
			list-style-type: none;
			display: block;
			width: 75%;
			padding: 0;
			text-align: left;
			margin-left: auto;
			margin-right: auto;
		}
		ol.bin>li {
			display: block;
			width: 75%;
			margin-left: auto;
			margin-right: auto;
		}
		ol.bin>li>a {
			width: calc(100% - 10.5em);
			text-overflow: ellipsis;
			overflow: hidden;
			white-space: nowrap;
			display: inline-block;
			text-decoration: none;
			color: #0C5CA7;
		}
		ol.bin>li.lot>a {
			width: calc(100% - 20.5em);
		}
		ol.bin>li>span {
			text-align: right;
			display: inline-block;
		}
		ol.bin>li>span.price {
			width: 7em;
		}
		ol.bin>li:not(.lot)>span.price, ol.bin>li.lot>span.eachlabel {
			font-weight: bold;
		}
		ol.bin>li>span.qty {
			width: 3em;
		}
		ol.bin>li>span.each {
			width: 7em;
			font-weight: bold;
		}
		ol.bin>li>span.eachlabel {
			width: 3.5em;
		}
		ol.bin>li>span.price:before, ol.bin>li>span.each:before {
			content: '$';
		}
		ol.bin>li.lot>span.price:after {
			content: ' /';
		}
		ol.bin>li.lot>span.qty:after {
			content: ' =';
		}
		ol.bin>li:not(.lot)>span:not(.price) {
			display: none;
		}
	</style>

	<script type="text/javascript">
		var current;

		function fadeInto(bin) {

			newDiv = $('div.bin'+bin);
			current.fadeOut(250, function() {
				newDiv.fadeIn(250);
			});
			current = newDiv;

			/*
			var newDiv = $('div.bin'+bin);
			current.hide();
			current = newDiv;
			current.show();
			*/
			return false;
		}

		$(document).ready(function() {
			current = $('div#dummy');
			for(var i = 0; i < <?=$numBins?>; i++) {
				$('li.bin'+i).click(function(){
					fadeInto(parseInt($(this).attr('class').substring(14)));
				});
				$('div.bin'+i).removeClass('loading');
				$('div.bin'+i).hide();
			}
		});
	  $( function() {
	    $( document ).tooltip();
	  } );
	</script>

	<meta charset = "utf-8">
		<title>Super Hyper Advanced Search Tool VII</title>
		<div style="text-align: center;">
			<h1>Super Hyper Advanced Search Tool VII</h1>
			<div class = 'row' >
				<form action="search.php" method="get">
					<input type="text" name="q" placeholder="Search..." value="<?=isset($_GET['q'])?$_GET['q']:''?>"/>
					<input type="submit" value="Search" />
				</form>
			</div>
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
			<?php endforeach;
		endif;?>

	<div id="dummy">Click a bar in the histogram to view listings in that price bin.</div>
