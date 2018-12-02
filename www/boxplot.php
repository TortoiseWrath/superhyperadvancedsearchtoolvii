<?php
	function boxplot($result, $maxPrice) {
		$query = $result[0];
		$prices = $result[1];
		$plot = '<a href="search.php?q='.urlencode($query).'">'.'<svg width="'.$maxPrice.'" height="10">';

		$count = count($prices);
		$q1 = $prices[$count / 4]->price;
		$q2 = $prices[$count / 2]->price;
		$q3 = $prices[$count / 4 * 3]->price;
		$iqr = $q3 - $q1;
		$maxOutlier = -1;
		while($prices[$maxOutlier + 1]->price / $prices[$maxOutlier + 1]->qty < $q1 - 3*$iqr) { $maxOutlier++; }
		$minWhisker = $prices[$maxOutlier + 1]->price / $prices[$maxOutlier + 1]->qty;
		$minOutlier = count($prices);
		while($prices[$minOutlier - 1]->price / $prices[$minOutlier - 1]->qty > $q3 + 3*$iqr) { $minOutlier--; }
		$maxWhisker = $prices[$minOutlier - 1]->price / $prices[$minOutlier - 1]->qty;

		$plot .= "$maxOutlier $minWhisker $q1 $q2 $q3 $maxWhisker $minOutlier";

		$plot .= '</svg>'.'</a>';
		return $plot;
	}
