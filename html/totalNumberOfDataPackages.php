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
function createTotalDataPackagesOutput($xmlData, $quarter,$deleteCount) {
	$responseXML = new SimpleXMLElement( $xmlData);
	
	require_once('countPackagesInEachQuarter.php');
	$count = countPackages( $quarter, $responseXML);
	
	$finalCount['1'] = $count ['1'] - $deleteCount['1'];
	$finalCount['2'] = $count ['2'] - $deleteCount['2'];
	$finalCount['3'] = $count ['3'] - $deleteCount['3'];
	$finalCount['4'] = $count ['4'] - $deleteCount['4'];
	
	$_SESSION ['totalDataPackages1'] = $finalCount['1'];
	$_SESSION ['totalDataPackages2'] = $finalCount['1'] + $finalCount['2'];
	$_SESSION ['totalDataPackages3'] = $finalCount['1'] + $finalCount['2'] + $finalCount['3'];
	$_SESSION ['totalDataPackages4'] = $finalCount['1'] + $finalCount['2'] + $finalCount['3']+ $finalCount['4'];
	
	$_SESSION ['totalDataPackagesCurrentQ'] = $finalCount ['4'];
	$_SESSION ['totalDataPackagesLastQ'] = $finalCount ['3'];
	$_SESSION ['totalDataPackages12Month'] = $finalCount ['1'] + $finalCount ['2'] + $finalCount ['3'] + $finalCount ['4'];
}
function updateDataPackagesOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement( $xmlData);
	
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
	
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$deleteResponseXML = new SimpleXMLElement( $xmlData);
	
	$_SESSION ['totalCreateDataPackageAYearAgo'] = countTotalPackages($responseXML) - countTotalPackages($deleteResponseXML);
}

function countUpdateDataPackagesAYearAgo(){
	$endDate = new DateTime( date( DATE_ATOM, mktime( 0, 0, 0, date( "m"), date( "d"), date( "Y") - 1)));
	$endDate = $endDate->format( "Y-m-d");

	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=updateDataPackage&status=200&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);

	$responseXML = new SimpleXMLElement( $xmlData);

	$_SESSION ['totalUpdateDataPackageAYearAgo'] = countTotalPackages($responseXML);
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
	
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$deleteResponseXML = new SimpleXMLElement( $xmlData);

	$_SESSION ['totalDataPackagesAyear'] = countTotalPackages($responseXML) - countTotalPackages($deleteResponseXML);
}

function countDeletedPackages($beginDate, $endDate,$quarter){	
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=deleteDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	$xmlData = returnAuditReportToolOutput( $url, $_POST ['username'], $_POST ['password']);
	
	$responseXML = new SimpleXMLElement( $xmlData);
	
	require_once('countPackagesInEachQuarter.php');
	$deleteCount = countPackages( $quarter, $responseXML);
	
	return $deleteCount;
}
?>