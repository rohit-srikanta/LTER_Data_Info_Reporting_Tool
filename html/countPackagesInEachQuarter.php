<?php
function countPackages($quarter, $data) {
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	
	foreach ( $data as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		if (in_array ( $month, $quarter ['1'] ))
			$qtr1 = $qtr1 + 1;
		else if (in_array ( $month, $quarter ['2'] ))
			$qtr2 = $qtr2 + 1;
		else if (in_array ( $month, $quarter ['3'] ))
			$qtr3 = $qtr3 + 1;
		else if (in_array ( $month, $quarter ['4'] ))
			$qtr4 = $qtr4 + 1;
	}
	
	$totalCount ['1'] = $qtr1;
	$totalCount ['2'] = $qtr2;
	$totalCount ['3'] = $qtr3;
	$totalCount ['4'] = $qtr4;
	
	return $totalCount;
}

?>