
<?php

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';


class searchResult {
	public $title;
	public $price;
	public $url;
	public $qty = 1;
}

function search($query, $offset = 0) {
	global $endpoint;

	// Create the XML request to be POSTed
	$xmlrequest  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xmlrequest .= "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">\n";
	$xmlrequest .= "<keywords>";
	$xmlrequest .= $query;
	$xmlrequest .= "</keywords>\n";
	$xmlrequest .= "<paginationInput>\n  <entriesPerPage>100</entriesPerPage>\n</paginationInput>\n";
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
	if($resp->ack !== "Success") return false;

	$results = array();
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

	return $results;
}



?>

<!DOCTYPE html>
<title>search</title>

<?php
$results = search('intel slacr');
foreach($results as $item):?>
	<a href="<?=$item->url?>"><?=$item->title?></a> ($<?=item->price?>)
	<p>
<?php endforeach; ?>
