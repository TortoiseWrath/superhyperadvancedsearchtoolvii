
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
			width: 600px;
			height: 200px;
			list-style: none;
			display: flex;
			justify-content: space-evenly;
			flex-wrap: nowrap;
			border-bottom: 1px solid #ccc;
			border-left: 1px solid #ccc;
			margin: 1em;
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
		}
	</style>
	<ol><meta charset = "utf-8">
		<title>Super Hyper Advanced Search Tool VII</title>
		<div style="text-align: center;">
			<h1>Super Hyper Advanced Search Tool VII</h1>
			<div class = 'row' >
				<form action="search.php" method="post">
					Search: <input type="text" name="query" placeholder="Search..." />
					<input type="submit" />
				</form>
			</div>
		</div>
	</body>
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
					<li class="graph-cell" style="height: <?=number_format(count($bin) / $maxBinCount * 100)?>%;"></li>
				<?php endforeach; ?>
			</ul>
			<?php
			foreach($bins->bins as $bindex=>$bin): ?>
				<strong>
					<?php
					$minPrice = $bindex * $bins->increment;
					$maxPrice = $minPrice + $bins->increment - 0.01;
					echo "Bin $bindex (\$".number_format($minPrice, 2)," - \$".number_format($maxPrice,2)."): " . count($bin) . " items" ?>
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
				</ol>
			<?php endforeach;
		endif;?>
	</ol>
