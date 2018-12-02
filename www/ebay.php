<?php

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
	$xmlrequest .= "</keywords>\n<buyerPostalCode>35401</buyerPostalCode>";
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
		if($item->listingInfo->listingType == "Auction") continue;
		$result = new searchResult;
		if(strtolower(substr($item->title, 0, 6)) == "lot of") {
			$lotOf = intval(substr($item->title, 6));
			if($lotOf === 0) $lotOf = intval(substr($item->title, 7));
			if($lotOf !== 0) $result->qty = $lotOf;
		}
		$result->title = $item->title;
		$result->price = floatval($item->shippingInfo->shippingServiceCost);
		if($item->listingInfo->buyItNowAvailable == "true") {
			$result->price += floatval($item->listingInfo->convertedBuyItNowPrice);
		}
		else {
			$result->price += floatval($item->sellingStatus->convertedCurrentPrice);
		}
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
		if($page > $maxPages) break;
		$pageResult = searchPage($query, $page);
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
		$bin = floor((floor($item->price/$item->qty * 100) / 100 - 0.01) / $bins->increment);
		if(!isset($bins->bins[$bin])) $bins->bins[$bin] = array();
		$bins->bins[$bin][] = $item;
	}
	for($i = 0; $i < $numBins; $i++) {
		if(!isset($bins->bins[$i])) $bins->bins[$i] = array();
	}
	ksort($bins->bins);
	return $bins;
}
