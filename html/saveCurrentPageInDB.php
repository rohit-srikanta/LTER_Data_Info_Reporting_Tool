<?php
// function saveReport() {
class MyDB extends SQLite3 {
	function __construct() {
		$this->open ( '../db/LTERSavedReports.db' );
	}
}
$db = new MyDB ();

session_start ();

$uniqueRecordID = false;


$reportID = NULL;
while ( ! $uniqueRecordID ) {
	$reportID = rand ( 1000, 999999 );
	$results = $db->query ( 'SELECT ID FROM saveLTERGeneratedReports' );
	$found = false;
	while ( $row = $results->fetchArray () ) {
		if ($row ['ID'] == $reportID) {
			$found = true;
			break;
		}
	}
	if (! $found)
		$uniqueRecordID = true;
}

$value1 = $_SESSION ['quarterTitle'] ['0'];
$value2 = $_SESSION ['quarterTitle'] ['1'];
$value3 = $_SESSION ['quarterTitle'] ['2'];
$value4 = $_SESSION ['quarterTitle'] ['3'];
$value5 = $_SESSION ['quarterTitle'] ['4'];
$value6 = $_SESSION ['totalDataPackages0'];
$value7 = $_SESSION ['totalDataPackages1'];
$value8 = $_SESSION ['totalDataPackages2'];
$value9 = $_SESSION ['totalDataPackages3'];
$value10 = $_SESSION ['totalDataPackages4'];
$value11 = $_SESSION ['dataPackageDownloads0'];
$value12 = $_SESSION ['dataPackageDownloads1'];
$value13 = $_SESSION ['dataPackageDownloads2'];
$value14 = $_SESSION ['dataPackageDownloads3'];
$value15 = $_SESSION ['dataPackageDownloads4'];
$value16 = $_SESSION ['dataPackageArchiveDownloads0'];
$value17 = $_SESSION ['dataPackageArchiveDownloads1'];
$value18 = $_SESSION ['dataPackageArchiveDownloads2'];
$value19 = $_SESSION ['dataPackageArchiveDownloads3'];
$value20 = $_SESSION ['dataPackageArchiveDownloads4'];
$value21 = $_SESSION ['CurrentQuarterDate'];
$value22 = $_SESSION ['PreviousQuarterDate'];
$value23 = $_SESSION ['totalDataPackagesCurrentQ'];
$value24 = $_SESSION ['totalDataPackagesLastQ'];
$value25 = $_SESSION ['totalDataPackagesAyear'];
$value26 = $_SESSION ['totalDataPackages12Month'];
$value27 = $_SESSION ['updateDataPackages1'];
$value28 = $_SESSION ['updateDataPackages2'];
$value29 = $_SESSION ['updateDataPackages3'];
$value30 = $_SESSION ['updateDataPackages4'];
$value31 = $_SESSION ['totalUpdateDataPackageAYearAgo'];
$value32 = $_SESSION ['AsOfCurrentQuarterDate'];
$value33 = $_SESSION ['AsOfPreviousQuarterDate'];
$value34 = $_SESSION ['AsOfPreviousYearDate'];
$value35 = $_SESSION ['totalCreateDataPackageAYearAgo'];
$value36 = $_SESSION ['site'];

$comment1 = $_POST['comment1'];
$comment2 = $_POST['comment2'];
$comment3 = $_POST['comment3'];
$comment4 = $_POST['comment4'];

date_default_timezone_set ( 'America/Phoenix' );

error_reporting(E_ERROR | E_PARSE);

$value37 = date ( "D M j G:i:s T Y" );

$db->exec ( "INSERT INTO saveLTERGeneratedReports VALUES ($reportID,'$value1','$value2','$value3','$value4','$value5',$value6,$value7,$value8,$value9,$value10,$value11,$value12,$value13,$value14,$value15,
		$value16,$value17,$value18,$value19,$value20,'$value21','$value22',$value23,$value24,$value25,$value26,$value27,$value28,$value29,$value30,$value31,'$value32','$value33','$value34',$value35,'$value36','$value37')" );

if (isset ( $_SESSION ['recentPackages'] )) {
	
	$data = $_SESSION ['recentPackages'];
	
	foreach ( $data as $value ) {
		
		$identifierLink = $value ['identifierLink'];
		$name = $value ['name'];
		$author = $value ['author'];
		$date = $value ['date'];
		$title = $value ['title'];
		
		$db->exec ( "INSERT INTO saveRecentPackages(reportID,identifierLink,name,author,date,title) VALUES ($reportID,'$identifierLink','$name','$author','$date','$title')" );
	}
}

$db->exec ( "INSERT INTO saveReportComments(reportID,comment1,comment2,comment3,comment4) VALUES ($reportID,'$comment1','$comment2','$comment3','$comment4')" );

$db->close ();
unset ( $db );

echo $reportID;
// }
?>

