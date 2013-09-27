<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="shortcut icon" href="../assets/ico/favicon.png">

<title>LTER Network Information System Reporting Tool</title>

<!-- Bootstrap core CSS -->
<link href="../dist/css/bootstrap.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="index.css" rel="stylesheet">

</head>
<?php
 
if (isset ( $_POST ['submitReport'] )) {
	
	if (isset ( $_SESSION ['ErrorDuringReportGeneration'] ))
		unset ( $_SESSION ['ErrorDuringReportGeneration'] );
	
	$success = generateReport();
	if($success == false)
		$_SESSION['ErrorDuringReportGeneration'] = true;
}

function callAuditReportTool($url, $username, $password, $returnValue) {
	$curl = curl_init ();
	// Optional Authentication:
	curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt ( $curl, CURLOPT_USERPWD, "uid=" . $username . ",o=LTER,dc=ecoinformatics,dc=org:" . $password );

	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );

	curl_setopt ( $curl, CURLOPT_FAILONERROR, true );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );

	$retValue = curl_exec ( $curl );
	curl_close ( $curl );
	$_SESSION [$returnValue] = $retValue;
}
function returnAuditReportToolOutput($url, $username, $password) {
	$curl = curl_init ();
	// Optional Authentication:
	curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt ( $curl, CURLOPT_USERPWD, "uid=" . $username . ",o=LTER,dc=ecoinformatics,dc=org:" . $password );

	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );

	curl_setopt ( $curl, CURLOPT_FAILONERROR, true );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );

	$retValue = curl_exec ( $curl );
	curl_close ( $curl );
	return $retValue;
}

function generateReport() {
	session_start ();
	
	$username = $_POST ['username'];
	$password = $_POST ['password'];
	
	date_default_timezone_set ( 'MST' );
	$endDate = date ( "Y-m-d" );
	$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) - 1 ) ) );
	$beginDate = $beginDate->format ( "Y-m-d" );
	
	createTotalDataPackagesInputData ( $beginDate, $endDate );
	
	if($_SESSION ['totalDataPackages'] == null)
	{		
		unset ( $_SESSION ['submitReport']);
		return false;
	}
	if (isset ( $_SESSION ['totalDataPackages'] ) && $_SESSION ['totalDataPackages'] != null)
		createTotalDataPackagesOutput ( $_SESSION ['totalDataPackages'], $beginDate, $endDate );
	
	sleep ( 5 );
	
	updateTotalDataPackagesInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['updateDataPackages'] ) && $_SESSION ['updateDataPackages'] != null)
		updateDataPackagesOutput ( $_SESSION ['updateDataPackages'], $beginDate, $endDate );
	
	createDataPackagesDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageDownloads'] ) && $_SESSION ['dataPackageDownloads'] != null)
		createDataPackagesDownloadOutput ( $_SESSION ['dataPackageDownloads'], $beginDate, $endDate );
	
	createDataPackagesArchiveDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageArchiveDownloads'] ) && $_SESSION ['dataPackageArchiveDownloads'] != null)
		createDataPackagesArchiveDownloadOutput ( $_SESSION ['dataPackageArchiveDownloads'], $beginDate, $endDate );
	
	recentlyPublishedDataSetsInput ( $endDate );
	if (isset ( $_SESSION ['recentlyCreatedDataPackages'] ) && $_SESSION ['recentlyCreatedDataPackages'] != null)
		recentlyPublishedDataSets ( $_SESSION ['recentlyCreatedDataPackages'] );
	
	return true;
}

function createTotalDataPackagesInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "totalDataPackages" );
}
function createDataPackagesDownloadsInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageDownloads" );
}
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "dataPackageArchiveDownloads" );
}
function updateTotalDataPackagesInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "updateDataPackages" );
}
function recentlyPublishedDataSetsInput($endDate) {
	$newBeginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ) - 3, date ( "d" ), date ( "Y" ) ) ) );
	$newBeginDate = $newBeginDate->format ( "Y-m-d" );
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $newBeginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, $_POST ['username'], $_POST ['password'], "recentlyCreatedDataPackages" );
}
function createTotalDataPackagesOutput($xmlData, $beginDate, $endDate) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	foreach ( $responseXML as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		if ($month <= 3)
			$qtr1 = $qtr1 + 1;
		if ($month > 3 && $month <= 6)
			$qtr2 = $qtr2 + 1;
		if ($month > 6 && $month <= 9)
			$qtr3 = $qtr3 + 1;
		if ($month > 9 && $month <= 12)
			$qtr4 = $qtr4 + 1;
	}
	
	$_SESSION ['totalDataPackages20131'] = $qtr1;
	$_SESSION ['totalDataPackages20132'] = $qtr2 + $qtr1;
	$_SESSION ['totalDataPackages20133'] = $qtr3 + $qtr1 + $qtr2;
	$_SESSION ['totalDataPackages20134'] = $qtr4 + $qtr1 + $qtr2 + $qtr3;
	
	$_SESSION ['totalDataPackagesCurrentQ'] = $qtr3;
	$_SESSION ['totalDataPackagesLastQ'] = $qtr2;
	$_SESSION ['totalDataPackagesAyear'] = 0;
	$_SESSION ['totalDataPackages12Month'] = $qtr4 + $qtr1 + $qtr2 + $qtr3;
}
function createDataPackagesDownloadOutput($xmlData, $beginDate, $endDate) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	foreach ( $responseXML as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		if ($month <= 3)
			$qtr1 = $qtr1 + 1;
		if ($month > 3 && $month <= 6)
			$qtr2 = $qtr2 + 1;
		if ($month > 6 && $month <= 9)
			$qtr3 = $qtr3 + 1;
		if ($month > 9 && $month <= 12)
			$qtr4 = $qtr4 + 1;
	}
	
	$_SESSION ['dataPackageDownloads20131'] = $qtr1;
	$_SESSION ['dataPackageDownloads20132'] = $qtr2;
	$_SESSION ['dataPackageDownloads20133'] = $qtr3;
	$_SESSION ['dataPackageDownloads20134'] = $qtr4;
}
function createDataPackagesArchiveDownloadOutput($xmlData, $beginDate, $endDate) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	foreach ( $responseXML as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		if ($month <= 3)
			$qtr1 = $qtr1 + 1;
		if ($month > 3 && $month <= 6)
			$qtr2 = $qtr2 + 1;
		if ($month > 6 && $month <= 9)
			$qtr3 = $qtr3 + 1;
		if ($month > 9 && $month <= 12)
			$qtr4 = $qtr4 + 1;
	}
	
	$_SESSION ['dataPackageArchiveDownloads20131'] = $qtr1;
	$_SESSION ['dataPackageArchiveDownloads20132'] = $qtr2;
	$_SESSION ['dataPackageArchiveDownloads20133'] = $qtr3;
	$_SESSION ['dataPackageArchiveDownloads20134'] = $qtr4;
}
function updateDataPackagesOutput($xmlData, $beginDate, $endDate) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$qtr1 = 0;
	$qtr2 = 0;
	$qtr3 = 0;
	$qtr4 = 0;
	foreach ( $responseXML as $record ) {
		$month = substr ( $record->entryTime, 5, 2 );
		if ($month <= 3)
			$qtr1 = $qtr1 + 1;
		if ($month > 3 && $month <= 6)
			$qtr2 = $qtr2 + 1;
		if ($month > 6 && $month <= 9)
			$qtr3 = $qtr3 + 1;
		if ($month > 9 && $month <= 12)
			$qtr4 = $qtr4 + 1;
	}
	
	$_SESSION ['updateDataPackages20131'] = $qtr1;
	$_SESSION ['updateDataPackages20132'] = $qtr2;
	$_SESSION ['updateDataPackages20133'] = $qtr3;
	$_SESSION ['updateDataPackages20134'] = $qtr4;
}
function recentlyPublishedDataSets($xmlData) {
	$responseXML = new SimpleXMLElement ( $xmlData );
	$totalRecords = $responseXML->count ();
	
	$j = 0;
	foreach ( $responseXML as $record ) {
		$recentDataPackages [$j ++] = substr ( $record->resourceId, 38 );
	}
	
	for($i = 0; $i < 10; $i ++) {
		$randomNumbers [$i] = mt_rand ( 0, $totalRecords );
	}
	sort ( $randomNumbers );
	
	for($i = 0; $i < 10; $i ++) {
		$url = "http://pasta.lternet.edu/package/metadata/eml/" . $recentDataPackages [$randomNumbers [$i]];
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
  <body>

	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse"
					data-target=".navbar-collapse">
					<span class="icon-bar"></span> <span class="icon-bar"></span> <span
						class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="index.php">LTER Network Information
					System Report</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="index.php">Home</a></li>
					<li><a href="aboutPage.html">About</a></li>
					<li><a href="contact.html">Contact</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="starter-template">
			<h1>Welcome to LTER Network Information System Reporting Tool</h1>
			<br>
			<p class="lead">This report describes the current status of the data
				package inventory as published in the LTER network information
				system. It is produced to highlight the volume of public access data
				produced by the LTER network of research sites. This report is
				intended for the LTER Executive Board, National Science Foundation
				program officers, and other interested parties</p><hr>
				
		</div>

		<div class="col-md-12">
		
		<?php if (isset ( $_SESSION ['ErrorDuringReportGeneration'] )) {
			echo'<script> alert("Unable to generate the report. Please verify the login credentials and try again.");
			window.location="index.php"; </script> ';
		} ?>
		<p align="center"><i>Please provide the login information to generate LTER Network System Report. <br>Please note that the report generation may take time.</i> </p>
		<?php if (!isset ( $_POST ['submitReport'] )) { ?>
			<form id="reportForm" class="form-signin" method="POST"
				action="index.php">
				<input id="username" name="username" type="text"
					class="form-control" placeholder="Username" autofocus> <input
					id="password" name="password" type="password" class="form-control"
					placeholder="Password">
				<button class="btn btn-lg btn-primary btn-block" type="submit"
					name="submitReport">Generate LTER Network Information System Report</button>
			</form>
		<?php }?>
		
			<div class="starter-template" id="afterSubmit">
				<p class="lead">Please wait while we generate the report.....</p>
			</div>
		<?php
		if (isset ( $_SESSION ['totalDataPackages20131'] )) {
			
			?>
			<div class="starter-template">
				<p class="lead">Total Number Of Data Packages In Network Information
					System</p>
				<p>This report reflects the total number of data packages published
					by LTER sites in the network information system. It includes the
					total by quarter.</p>
			</div>
			<div id="chart_div_totalDataPackages"
				style="width: 1000px; height: 400px;"></div><?php
		}
		
		if (isset ( $_SESSION ['dataPackageDownloads20131'] )) {
			?>
					<div class="starter-template">
				<p class="lead">Number of Data Package Downloads</p>
				<p>This graphic reflects the number of data package downloads from
					the LTER network information system by quarter.</p>
			</div>
			<div id="chart_div_dataPackagesDownloads"
				style="width: 1000px; height: 400px;"></div><?php
		}
		?>
		
		<?php
		if ((isset ( $_SESSION ['totalDataPackages20131'] )) && (isset ( $_SESSION ['updateDataPackages20131'] ))) {
			
			?>
		<div class="starter-template">
				<p class="lead">Network Summary Statistics</p>
				<table class="table table-striped table-bordered">
					<tr>
						<th></th>
						<th>Current Period</th>
						<th>Last Period</th>
						<th>A year Ago</th>
						<th>Last 12 Months</th>
					</tr>
					<tr>
						<td>Number of data packages published</td>
						<td><?php echo $_SESSION['totalDataPackagesCurrentQ']; ?></td>
						<td><?php echo $_SESSION['totalDataPackagesLastQ']; ?></td>
						<td><?php echo $_SESSION['totalDataPackagesAyear']; ?></td>
						<td><?php echo $_SESSION['totalDataPackages12Month']; ?></td>
					</tr>
					<tr>
						<td>Total number of published data packages
						
						</th>
						<td><?php echo $_SESSION['totalDataPackages20133']; ?></td>
						<td><?php echo $_SESSION['totalDataPackages20132']; ?></td>
						<td>0
						
						</th>
						<td><?php echo $_SESSION['totalDataPackages20134']; ?></td>
					</tr>
					<tr>
						<td>Number of data package updates/revisions</td>
						<td><?php echo $_SESSION['updateDataPackages20133']; ?></td>
						<td><?php echo $_SESSION['updateDataPackages20132']; ?></td>
						<td>0
						
						</th>
						<td><?php echo ($_SESSION['updateDataPackages20131'] + $_SESSION['updateDataPackages20132'] + $_SESSION['updateDataPackages20133'] + $_SESSION['updateDataPackages20134']); ?>
						</th>
					</tr>
				</table>
			</div>

		<?php
		}
		?>
		
		
		<?php
		if (isset ( $_SESSION ['recentlyCreatedDataPackages'] )) {
			
			?>
		<div class="starter-template">
				<p class="lead">Selection of Recently Published Datasets (Last Three
					Months)</p>
				<p>This table presents a random selection of data packages published
					during the current reporting period. It is intended to provide a
					flavor of the type of research data being made accessible through
					the LTER Network Information System.</p>
				<table class="table table-striped table-bordered">
					<tr>
						<th>Data Package Identifier</th>
						<th>Creators</th>
						<th>Publication Date</th>
						<th>Title</th>
					</tr>
					<?php
			
			$data = $_SESSION ['recentPackages'];
			for($i = 0; $i < 10; $i ++) {
				?><tr>
						<td><a href=<?php echo $data[$i]['identifierLink'];?>><?php echo $data[$i]['name']; ?></a></td>
						<td><?php echo $data[$i]['author']; ?></td>
						<td><?php echo $data[$i]['date']; ?></td>
						<td><?php echo $data[$i]['title']; ?></td>
					</tr>
					<?php } ?>
				</table>
			</div>

		<?php
		}
		
		if (isset ( $_SESSION ['totalDataPackages'] ))
			unset ( $_SESSION ['totalDataPackages'] );
		
		if (isset ( $_SESSION ['dataPackageDownloads'] ))
			unset ( $_SESSION ['dataPackageDownloads'] );
		
		if (isset ( $_SESSION ['dataPackageArchiveDownloads'] ))
			unset ( $_SESSION ['dataPackageArchiveDownloads'] );
		
		if (isset ( $_SESSION ['updateDataPackages'] ))
			unset ( $_SESSION ['updateDataPackages'] );
		
		if (isset ( $_SESSION ['recentlyCreatedDataPackages'] )) {
			unset ( $_SESSION ['recentlyCreatedDataPackages'] );
			session_destroy ();
		}
		?>
		</div>
	</div>
	<!-- /.container -->

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="../assets/js/jquery.js"></script>
	<script src="../dist/js/bootstrap.min.js"></script>

	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChartTotalDataPackages);
      google.setOnLoadCallback(drawChartDataPackageDownloads);
      
      function drawChartTotalDataPackages() {
        var data = google.visualization.arrayToDataTable([
          ['Quarter', 'Total Packages'],         
          ['2013-1st Quarter', <?php echo $_SESSION['totalDataPackages20131']; ?>],
          ['2013-2nd Quarter', <?php echo $_SESSION['totalDataPackages20132']; ?>],
          ['2013-3rd Quarter', <?php echo $_SESSION['totalDataPackages20133']; ?>],
          ['2013-4th Quarter', <?php echo $_SESSION['totalDataPackages20134']; ?>],
        ]);

        var options = {
          title: 'LTER Network Data Packages',
          hAxis: {title: 'Quarter Reporting Period'},
          vAxis: {title: "Total Data Packages"}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_totalDataPackages'));
        chart.draw(data, options);
      }

      function drawChartDataPackageDownloads() {
          var data = google.visualization.arrayToDataTable([ 
            ['Quarter', 'Number of Data Downloads', 'Number of Data Archive Downloads'],      
            ['2013-1st Quarter', <?php echo $_SESSION['dataPackageDownloads20131']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads20131']; ?>],
            ['2013-2nd Quarter', <?php echo $_SESSION['dataPackageDownloads20132']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads20132']; ?>],
            ['2013-3rd Quarter', <?php echo $_SESSION['dataPackageDownloads20133']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads20133']; ?>],
            ['2013-4th Quarter', <?php echo $_SESSION['dataPackageDownloads20134']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads20134']; ?>],
          ]);

          var options = {
            title: 'Number of Network Downloads',
            isStacked: true,
            hAxis: {title: 'Quarter Reporting Period'},
            vAxis: {title: "Number of Downloads"}
          };

          var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_dataPackagesDownloads'));
          chart.draw(data, options);
      }
    </script>

	<script language="JavaScript">
	$(document).ready(function() {
		$('#afterSubmit').hide();	
		$('#reportForm').submit(function() {	
				if(($('#username').val().length == 0) || ($('#password').val().length == 0)){
					alert("Please enter username and password for report generation");
					return false;
				}		
				$('#reportForm').hide();
				$('#afterSubmit').show();
			
		}).change();
		
	});
</script>
</body>
</html>
