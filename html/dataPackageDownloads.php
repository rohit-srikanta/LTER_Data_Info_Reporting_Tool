<?php
function createDataPackagesDownloadsInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageDownloads" );
}
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate) {
	global $pastaURL;
	$url = $pastaURL . "audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageArchiveDownloads" );
}
function createDataPackagesDownloadOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	require_once ('countPackagesInEachQuarter.php');
	
	$count = countPackages ( $quarter, $responseXML );
	
	$_SESSION ['dataPackageDownloads1'] = $count ['1'];
	$_SESSION ['dataPackageDownloads2'] = $count ['2'];
	$_SESSION ['dataPackageDownloads3'] = $count ['3'];
	$_SESSION ['dataPackageDownloads4'] = $count ['4'];
}
function createDataPackagesArchiveDownloadOutput($xmlData, $quarter) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	require_once ('countPackagesInEachQuarter.php');
	
	$count = countPackages ( $quarter, $responseXML );
	
	$_SESSION ['dataPackageArchiveDownloads1'] = $count ['1'];
	$_SESSION ['dataPackageArchiveDownloads2'] = $count ['2'];
	$_SESSION ['dataPackageArchiveDownloads3'] = $count ['3'];
	$_SESSION ['dataPackageArchiveDownloads4'] = $count ['4'];
}
?>