<?php
function createTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "totalDataPackages");
}
function updateTotalDataPackagesInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool( $url, $_POST ['username'], $_POST ['password'], "updateDataPackages");
}
function createTotalDataPackagesOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement( $xmlData);
	$totalRecords = $responseXML->count();
	
	require_once('countPackagesInEachQuarter.php');
	
	$count = countPackages( $quarter, $responseXML);
	
	$_SESSION ['totalDataPackages1'] = $count ['1'];
	$_SESSION ['totalDataPackages2'] = $count ['1'] + $count ['2'];
	$_SESSION ['totalDataPackages3'] = $count ['1'] + $count ['2'] + $count ['3'];
	$_SESSION ['totalDataPackages4'] = $count ['1'] + $count ['2'] + $count ['3'] + $count ['4'];
	
	$_SESSION ['totalDataPackagesCurrentQ'] = $count ['4'];
	$_SESSION ['totalDataPackagesLastQ'] = $count ['3'];
	$_SESSION ['totalDataPackagesAyear'] = 0;
	$_SESSION ['totalDataPackages12Month'] = $count ['1'] + $count ['2'] + $count ['3'] + $count ['4'];
}
function updateDataPackagesOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement( $xmlData);
	$totalRecords = $responseXML->count();
	
	require_once('countPackagesInEachQuarter.php');
	
	$count = countPackages( $quarter, $responseXML);
	
	$_SESSION ['updateDataPackages1'] = $count ['1'];
	$_SESSION ['updateDataPackages2'] = $count ['2'];
	$_SESSION ['updateDataPackages3'] = $count ['3'];
	$_SESSION ['updateDataPackages4'] = $count ['4'];
}

function countDataPackagesForYearAgo($quarter){
	countCreateDataPackagesAYearAgo();
	countUpdateDataPackagesAYearAgo();
	countCreateDataPackagesAYearAgoQuarter($quarter);
}
function countCreateDataPackagesAYearAgo(){
	$endDate = new DateTime( date( DATE_ATOM, mktime( 0, 0, 0, date( "m"), date( "d"), date( "Y") - 1)));
	$endDate = $endDate->format( "Y-m-d");
	
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$responseXML = new SimpleXMLElement( $xmlData);
	$totalRecords = $responseXML->count();
	
	$_SESSION ['totalCreateDataPackageAYearAgo'] = $totalRecords;	
}

function countUpdateDataPackagesAYearAgo(){
	$endDate = new DateTime( date( DATE_ATOM, mktime( 0, 0, 0, date( "m"), date( "d"), date( "Y") - 1)));
	$endDate = $endDate->format( "Y-m-d");

	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);
	$totalRecords = $responseXML->count();

	$_SESSION ['totalUpdateDataPackageAYearAgo'] = $totalRecords;
}

function countCreateDataPackagesAYearAgoQuarter($quarter){
	
	if($_SESSION ['quarterTitle']['4'] === "4th Quarter"){
		$endDate =(date("Y")-1)."-12-".cal_days_in_month(CAL_GREGORIAN, 12,(date("Y")-1));
		$beginDate =(date("Y")-1)."-10-01";
	}
	
	if($_SESSION ['quarterTitle']['4'] === "3rd Quarter"){
		$endDate =(date("Y")-1)."-09-".cal_days_in_month(CAL_GREGORIAN, 09,(date("Y")-1));
		$beginDate =(date("Y")-1)."-07-01";
	}
	
	if($_SESSION ['quarterTitle']['4'] === "2nd Quarter"){
		$endDate =(date("Y")-1)."-06-".cal_days_in_month(CAL_GREGORIAN, 06,(date("Y")-1));
		$beginDate =(date("Y")-1)."-04-01";
	}
	
	if($_SESSION ['quarterTitle']['4'] === "1st Quarter"){
		$endDate =(date("Y")-1)."-03-".cal_days_in_month(CAL_GREGORIAN, 03,(date("Y")-1));
		$beginDate =(date("Y")-1)."-01-01";
	}
	
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);
	$totalRecords = $responseXML->count();

	$_SESSION ['totalDataPackagesAyear'] = $totalRecords;
}
?>