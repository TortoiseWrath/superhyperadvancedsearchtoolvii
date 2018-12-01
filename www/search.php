
<?php

$numBins = 16;
$maxPages = 4;

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';


class searchResult {
	public $title;
	public $price;
	public $url;
	public $qty = 1;
}

function getMaxPrice($results) {
	$maxprice = 0.00;

	foreach ($results as $item) {
		if ($item->price > $maxprice) {
			$maxprice = $item->price;
		}
	}

	return $maxprice;
}

function searchPage($query, $page = 1) {
	global $endpoint;

	// Create the XML request to be POSTed
	$xmlrequest  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xmlrequest .= "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">\n";
	$xmlrequest .= "<keywords>";
	$xmlrequest .= $query;
	$xmlrequest .= "</keywords>\n";
	$xmlrequest .= "<paginationInput>\n  <entriesPerPage>100</entriesPerPage><pageNumber>$page</pageNumber>\n</paginationInput>\n";
	$xmlrequest .= "</findItemsByKeywordsRequest>";

	$headers = array(
		'X-EBAY-SOA-OPERATION-NAME: findItemsByKeywords',
		'X-EBAY-SOA-SERVICE-VERSION: 1.3.0',
		'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML',
		'X-EBAY-SOA-GLOBAL-ID: EBAY-US',
		'X-EBAY-SOA-SECURITY-APPNAME: SeanGill-SuperHyp-PRD-760b6dcaa-f4934c3c',
		'Content-Type: text/xml;charset=utf-8'
	);

	$session  = curl_init($endpoint);                       // create a curl session
	curl_setopt($session, CURLOPT_POST, true);              // POST request type
	curl_setopt($session, CURLOPT_HTTPHEADER, $headers);    // set headers using $headers array
	curl_setopt($session, CURLOPT_POSTFIELDS, $xmlrequest); // set the body of the POST
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);    // return values as a string, not to std out

	$responsexml = curl_exec($session);                     // send the request
	curl_close($session);                                   // close the session

	$resp = simplexml_load_string($responsexml);

	$results = array();
	if($resp->ack != "Success") return $results;

	foreach($resp->searchResult->item as $item) {
		if($item->listingInfo->listingType === "Auction") continue;
		$result = new searchResult;
		if(strtolower(substr($item->title, 0, 6)) == "lot of") {
			$lotOf = intval(substr($item->title, 6));
			if($lotOf === 0) $lotOf = intval(substr($item->title, 7));
			if($lotOf !== 0) $result->qty = $lotOf;
		}
		$result->title = $item->title;
		$result->price = floatval($item->sellingStatus->currentPrice) + floatval($item->shippingInfo->shippingServiceCost);
		$result->url = $item->viewItemURL;
		$results[] = $result;
	}

	$results[] = count($resp->searchResult->item);

	return $results;
}

function search($query) {
	global $maxPages;
	$results = array();
	$page = 1;
	$pageResult = searchPage($query);
	while(count($pageResult)) {
		$results = array_merge($results, $pageResult);
		if(array_pop($results) < 100) {
			break;
		}
		$page++;
		$pageResult = searchPage($query, $page);
		if($page > $maxPages) break;
	}
	usort($results, function($a, $b) {
		return $a->price / $a->qty - $b->price / $b->qty;
	});
	return $results;
}

class histogram {
	public $bins = array();
	public $increment;
}

function bin($results) {
	global $numBins;
	$bins = new histogram;
	$bins->increment = getMaxPrice($results) / $numBins;
	foreach($results as $item) {
		$bin = floor((floor($item->price * 100) / 100 - 0.01) / $bins->increment);
		if(!isset($bins->bins[$bin])) $bins->bins[$bin] = array();
		$bins->bins[$bin][] = $item;
	}
	for($i = 0; $i < $numBins; $i++) {
		if(!isset($bins->bins[$i])) $bins->bins[$i] = array();
	}
	ksort($bins->bins);
	return $bins;
}


?>



<!DOCTYPE html>
<<<<<<< HEAD
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
	<ol>
	<head><meta charset = "utf-8">
		<title>EbAvg</title>
		<div style="text-align: center;">
			<h1>EbAvg</h1>
			<div class = 'row' >
				<form action="search.php" method="post">
					Search: <input type="text" name="query" placeholder="Search..." />
					<input type="submit" />
				</form>
			</div>
		</div>
	</head>
	<body>
		<ul class="graph">
			<li class="graph-cell" style="height: 75%;"></li>
			<li class="graph-cell" style="height: 33%;"></li>
			<li class="graph-cell" style="height: 60%;"></li>
			<li class="graph-cell" style="height: 40%;"></li>
			<li class="graph-cell" style="height: 90%;"></li>
			<li class="graph-cell" style="height: 10%;"></li>
			<li class="graph-cell" style="height: 20%;"></li>
			<li class="graph-cell" style="height: 40%;"></li>
			<li class="graph-cell" style="height: 90%;"></li>
			<li class="graph-cell" style="height: 40%;"></li>
			<li class="graph-cell" style="height: 90%;"></li>
			<li class="graph-cell" style="height: 10%;"></li>
			<li class="graph-cell" style="height: 20%;"></li>
			<li class="graph-cell" style="height: 40%;"></li>
			<li class="graph-cell" style="height: 90%;"></li>
			<li class="graph-cell" style="height: 40%;"></li>
		</ul>
	</body>
	<?php
		if($_POST):
			$results = search($_POST['query']);
			$maxprice = getMaxPrice($results);
			echo "Highest price = \$".number_format($maxprice, 2)."<br>";

			$bins = bin($results);
			$bincrement = $bins->increment;
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
<form action="search.php" method="post">
Search: <input type="text" name="query" />
<input type="submit" />
</form>
<ol>
<?php
if($_POST):
	$results = search($_POST['query']);
	$maxprice = getMaxPrice($results);
	echo "Highest price = \$".number_format($maxprice, 2)."<br>";

	$bins = bin($results);
	$bincrement = $bins->increment;
	foreach($bins->bins as $bindex=>$bin): ?>
		<strong>
			<?php
			$minPrice = $bindex * $bins->increment + 0.01;
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
