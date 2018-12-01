
<?php

$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';

function search($query) {
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
	if($resp->ack == "Success") return $resp->searchResult;

	return false;
}


?>

<!DOCTYPE html>
<form action="search.php" method="post">
Search: <input type="text" name="query" />
<input type="submit" />
</form>	

<?php
$results = search($_POST['query']);
foreach($results->item as $item):
	if($item->listingInfo->listingType == "Auction") continue;
	?>
	<a href="<?=$item->viewItemURL?>"><?=$item->title?></a> <?=$item->listingInfo->listingType?> ($<?=floatval($item->sellingStatus->currentPrice) + floatval($item->shippingInfo->shippingServiceCost)?>)
	<p>
<?php endforeach; ?>
