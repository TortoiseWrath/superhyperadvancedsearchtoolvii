<?php
require_once('ebay.php');
require_once('boxplot.php');
?>

<!DOCTYPE html>
<link rel="stylesheet" type="text/css" href="style.css">

<script type="text/javascript">
	$(document).ready(function() {
		$('.loading').hide();
		$('.loading').removeClass('loading');
		$('form').submit(function(){
			$('ul.graph').fadeOut(125);
			$('div.xaxis').fadeOut(125);
			current.fadeOut(125, function() {
				$('div#loadingDiv').fadeIn(125);
			});
		});
	});
</script>

<meta charset = "utf-8">
<title>SHAST VII Multisearch</title>

<div style="text-align: center;">
	<h1 style="font-family: 'Arial Narrow'; font-weight: lighter">Super Hyper Advanced Search Tool VII</h1>
	<form method="get">
		<textarea name="q" placeholder="Enter one search per line"><?=isset($_GET['q'])?$_GET['q']:''?></textarea><br>
		<input type="submit" value="Search">
	</form>
</div>


<?php
if(isset($_GET['q'])):
	$queries = preg_split("/\r\n|\n|\r/", $_GET['q']);
	$results = array();
	foreach($queries as $query) {
		$results[] = array($query, search($query));
	}
	$maxPrice = 0;
	foreach($results as $result) {
		$localMax = getMaxPrice($result[1]);
		if($localMax > $maxPrice) $maxPrice = $localMax;
	}
?>

	<table>
		<thead>
			<tr>
				<th>Keywords</th>
				<th>Results</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($results as $result): ?>
				<tr>
					<td><?=$result[0]?></td>
					<td><?=count($result[1])?></td>
					<td><?=boxplot($result, $maxPrice)?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>

<div class="loading" id="loadingDiv">
	<img src="searchinganim.png" class="loading-image">
</div>
