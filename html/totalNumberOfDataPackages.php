<?php 

function createTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL."audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "totalDataPackages" );
}

function updateTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL."audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "updateDataPackages" );
}

function createTotalDataPackagesOutput($xmlData,$quarter) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	require_once ('countPackagesInEachQuarter.php');
	
	$count = countPackages($quarter,$responseXML);
	
	$_SESSION ['totalDataPackages1'] = $count['1'];
	$_SESSION ['totalDataPackages2'] = $count['1'] +  $count['2'];
	$_SESSION ['totalDataPackages3'] = $count['1'] +  $count['2']+ $count['3'];
	$_SESSION ['totalDataPackages4'] = $count['1'] +  $count['2']+ $count['3']+ $count['4'];
	
	$_SESSION ['totalDataPackagesCurrentQ'] = $count['4'];
	$_SESSION ['totalDataPackagesLastQ'] = $count['3'];
	$_SESSION ['totalDataPackagesAyear'] = 0;
	$_SESSION ['totalDataPackages12Month'] = $count['1'] +  $count['2']+ $count['3']+ $count['4'];
}

function updateDataPackagesOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	require_once ('countPackagesInEachQuarter.php');
	
	$count = countPackages($quarter,$responseXML);

	$_SESSION ['updateDataPackages1'] =  $count['1'];
	$_SESSION ['updateDataPackages2'] =  $count['2'];
	$_SESSION ['updateDataPackages3'] =  $count['3'];
	$_SESSION ['updateDataPackages4'] =  $count['4'];
}
?>