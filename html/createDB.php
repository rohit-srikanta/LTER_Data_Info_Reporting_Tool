<?php
class MyDB extends SQLite3
{
    function __construct()
    {
        $this->open('../db/LTERSavedReports.db');
    }
}
$db = new MyDB();

$db->exec ( 'DROP table saveLTERGeneratedReports' );
$db->exec ( 'DROP table saveRecentPackages' );

if (($db->exec ( 'CREATE TABLE saveLTERGeneratedReports (ID INT PRIMARY KEY NOT NULL,
    quarterTitle0   TEXT    NOT NULL,
    quarterTitle1   TEXT    NOT NULL,
	quarterTitle2   TEXT    NOT NULL,
	quarterTitle3   TEXT    NOT NULL,
	quarterTitle4   TEXT    NOT NULL,
	totalDataPackages0  INT NOT NULL,
	totalDataPackages1  INT NOT NULL,			
   	totalDataPackages2  INT NOT NULL,	
	totalDataPackages3  INT NOT NULL,	
	totalDataPackages4  INT NOT NULL,
	dataPackageDownloads0  INT NOT NULL,
	dataPackageDownloads1  INT NOT NULL,			
   	dataPackageDownloads2  INT NOT NULL,	
	dataPackageDownloads3  INT NOT NULL,	
	dataPackageDownloads4  INT NOT NULL,
	dataPackageArchiveDownloads0  INT NOT NULL,
	dataPackageArchiveDownloads1  INT NOT NULL,			
   	dataPackageArchiveDownloads2  INT NOT NULL,	
	dataPackageArchiveDownloads3  INT NOT NULL,	
	dataPackageArchiveDownloads4  INT NOT NULL,		
	CurrentQuarterDate   TEXT NOT NULL,	
	PreviousQuarterDate  TEXT NOT NULL,
	totalDataPackagesCurrentQ  INT NOT NULL,
	totalDataPackagesLastQ INT NOT NULL,
	totalDataPackagesAyear INT NOT NULL,
	totalDataPackages12Month INT NOT NULL,
	updateDataPackages1 INT NOT NULL,
	updateDataPackages2 INT NOT NULL,
	updateDataPackages3 INT NOT NULL,
	updateDataPackages4 INT NOT NULL,
	totalUpdateDataPackageAYearAgo INT NOT NULL,
	AsOfCurrentQuarterDate TEXT NOT NULL,	
	AsOfPreviousQuarterDate TEXT NOT NULL,	
	AsOfPreviousYearDate TEXT NOT NULL,	
	totalCreateDataPackageAYearAgo	INT NOT NULL,
	site TEXT NOT NULL,
	createdOn DATETIME NOT NULL																
	)' )) == true)
	echo "Table saveLTERGeneratedReports created successfully <br>";
else
	echo "Could not create table saveLTERGeneratedReports<br>";

if (($db->exec ( 'CREATE TABLE saveRecentPackages (ID INTEGER PRIMARY KEY AUTOINCREMENT,
	reportID INT NOT NULL,
	identifierLink  TEXT NOT NULL,
	name  TEXT NOT NULL,
	author  TEXT NOT NULL,
	date  TEXT NOT NULL,
	title  TEXT NOT NULL
	)' )) == true)
	echo "Table saveRecentPackages created successfully <br>";
else
	echo "Could not create table saveRecentPackages<br>";

$db->close();
unset($db);
?>

