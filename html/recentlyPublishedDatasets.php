<?php
function recentlyPublishedDataSetsInput($endDate) {
	global $pastaURL;
	$newBeginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ) - 3, date ( "d" ), date ( "Y" ) ) ) );
	$newBeginDate = $newBeginDate->format ( "Y-m-d" );
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $newBeginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "recentlyCreatedDataPackages" );
}
function recentlyPublishedDataSets($xmlData) {
	global $pastaURL;
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$j = 0;
	foreach ( $responseXML as $record ) {
		
		if (strpos($record->resourceId, "ecotrends") !== false)
			continue;
		
		$recentDataPackages [$j++] = substr ( $record->resourceId, 38 );
	}
	
	for($i = 0; $i < 10; $i ++) {
		$randomNumbers [$i] = mt_rand ( 0, $totalRecords );
	}
	sort ( $randomNumbers );
	
	for($i = 0; $i < 10; $i ++) {
		$url = $pastaURL . "package/metadata/eml/" . $recentDataPackages [$randomNumbers [$i]];
		$returnvalue = returnAuditReportToolOutput ( $url, $_POST ['username'], $_POST ['password'] );
		
		$XML = new SimpleXMLElement ( $returnvalue );
		$authorName = "";
		$authorCount = 0;
		foreach ( $XML->dataset->creator as $name ) {
			if ($name->individualName != "") {
				if ($authorCount != 0)
					$tempName = ( string ) ", " . $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				else
					$tempName = ( string ) $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				$authorCount ++;
				$authorName = $authorName . $tempName;
			}
		}
		
		if ($authorName == null || $authorName == "" || $authorName == ",") {
			foreach ( $XML->dataset->creator as $name ) {
				if ($authorCount != 0)
					$tempName = ( string ) ", " . $name->organizationName;
				else
					$tempName = ( string ) $name->organizationName;
				$authorCount ++;
				$authorName = $authorName . $tempName;
			}
		}
		$scope = strstr ( $recentDataPackages [$randomNumbers [$i]], '/', true );
		$identifier = strstr ( $recentDataPackages [$randomNumbers [$i]], '/' );
		$identifier = strstr ( substr ( $identifier, 1 ), '/', true );
		$temp = array (
				"name" => ( string ) str_replace ( "/", ".", $recentDataPackages [$randomNumbers [$i]] ),
				"title" => ( string ) $XML->dataset->title,
				"date" => ( string ) $XML->dataset->pubDate,
				"author" => ( string ) $authorName,
				"identifierLink" => ( string ) "https://portal.lternet.edu/nis/mapbrowse?scope=" . $scope . "&identifier=" . $identifier 
		);
		$packageDetails [$i] = $temp;
		$authorName = "";
	}
	
	$_SESSION ['recentPackages'] = $packageDetails;
}
?>