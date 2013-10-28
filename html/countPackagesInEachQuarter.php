<?php
//This is the main method that is used to count the xml records that is being passed as input. In all the computation we have to count the number of records in the response. 
//We also have to compute the results based on the quarters that we have identified and returns those counts.
function countPackages($quarter, $data,$site) {
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	$qtr0 = 0;
	
	$currentYear = date("Y");
	
	$site = str_replace(' ', '', $site);
	foreach ( $data as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		$year = substr ( $record->entryTime, 0, 4 );
		
		//If we are generating report for all sites, then exclude ecotrends, if not count only site specific entries.
		if(($site == "AllSites") && (strpos($record->resourceId, "ecotrends") !== false))
			continue;
		if(($site != "AllSites") && (strpos($record->resourceId, $site) == false))
			continue;
		
		if (in_array ( $month, $quarter ['1'] ))
			$qtr1 = $qtr1 + 1;
		else if (in_array ( $month, $quarter ['2'] ))
			$qtr2 = $qtr2 + 1;
		else if (in_array ( $month, $quarter ['3'] ))
			$qtr3 = $qtr3 + 1;
		else if ((in_array ( $month, $quarter ['4'])) && ($currentYear == $year))
			$qtr4 = $qtr4 + 1;
		else if ((in_array ( $month, $quarter ['0']))  && ($currentYear != $year))
			$qtr0 = $qtr0 + 1;
	}
	
	$totalCount ['1'] = $qtr1;
	$totalCount ['2'] = $qtr2;
	$totalCount ['3'] = $qtr3;
	$totalCount ['4'] = $qtr4;
	$totalCount ['0'] = $qtr0;
	
	return $totalCount;
}

//This method is used to get the total count irrespective of dates in it.
function countTotalPackages($data,$site){
	
	$count = 0;
	$site = str_replace(' ', '', $site);
	foreach ( $data as $record ) {
		//If we are generating report for all sites, then exclude ecotrends, if not count only site specific entries.
		if(($site == "AllSites") && (strpos($record->resourceId, "ecotrends") !== false))
			continue;
		if(($site != "AllSites") && (strpos($record->resourceId, $site) == false))
			continue;
		$count++;
	}	
	
	return $count;
}

?>