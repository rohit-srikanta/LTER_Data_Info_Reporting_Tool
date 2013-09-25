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
function callAuditReportTool($url, $returnValue) {
	$curl = curl_init ();
	// Optional Authentication:
	curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt ( $curl, CURLOPT_USERPWD, "uid=CAP,o=LTER,dc=ecoinformatics,dc=org:CAP1CAP" );
	
	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
	
	curl_setopt ( $curl, CURLOPT_FAILONERROR, true );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
	
	$retValue = curl_exec ( $curl );
	curl_close ( $curl );
	$_SESSION [$returnValue] = $retValue;
}
function returnAuditReportToolOutput($url) {
	$curl = curl_init ();
	// Optional Authentication:
	curl_setopt ( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
	curl_setopt ( $curl, CURLOPT_USERPWD, "uid=CAP,o=LTER,dc=ecoinformatics,dc=org:CAP1CAP" );
	
	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
	
	curl_setopt ( $curl, CURLOPT_FAILONERROR, true );
	curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
	
	$retValue = curl_exec ( $curl );
	curl_close ( $curl );
	return $retValue;
}

if (isset ( $_POST ['submitReport'] )) {
	session_start ();
	date_default_timezone_set ( 'MST' );
	$endDate = date ( "Y-m-d" );
	$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) - 1 ) ) );
	$beginDate = $beginDate->format ( "Y-m-d" );
	
	createTotalDataPackagesInputData ( $beginDate, $endDate );
	sleep ( 5 );
	createDataPackagesDownloadsInputData ( $beginDate, $endDate );
	createDataPackagesArchiveDownloadsInputData ( $beginDate, $endDate );
	// sleep ( 5 );
	updateTotalDataPackagesInputData ( $beginDate, $endDate );
	recentlyPublishedDataSetsInput ( $endDate );
	
	if (isset ( $_SESSION ['totalDataPackages'] )) {
		createTotalDataPackagesOutput ( $_SESSION ['totalDataPackages'], $beginDate, $endDate );
		recentlyPublishedDataSets ( $_SESSION ['totalDataPackages'] );
	}
	
	if (isset ( $_SESSION ['updateDataPackages'] ))
		updateDataPackagesOutput ( $_SESSION ['updateDataPackages'], $beginDate, $endDate );
	
	if (isset ( $_SESSION ['dataPackageDownloads'] ))
		createDataPackagesDownloadOutput ( $_SESSION ['dataPackageDownloads'], $beginDate, $endDate );
	
	if (isset ( $_SESSION ['dataPackageArchiveDownloads'] ))
		createDataPackagesArchiveDownloadOutput ( $_SESSION ['dataPackageArchiveDownloads'], $beginDate, $endDate );
	
	if (isset ( $_SESSION ['recentlyCreatedDataPackages'] ))
		recentlyPublishedDataSets ( $_SESSION ['recentlyCreatedDataPackages'] );
}
function createTotalDataPackagesInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, "totalDataPackages" );
}
function createDataPackagesDownloadsInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=readDataEntity&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, "dataPackageDownloads" );
}
function createDataPackagesArchiveDownloadsInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=readDataPackageArchive&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, "dataPackageArchiveDownloads" );
}
function updateTotalDataPackagesInputData($beginDate, $endDate) {
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=updateDataPackage&status=200&fromTime=" . $beginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, "updateDataPackages" );
}
function recentlyPublishedDataSetsInput($endDate) {
	$newBeginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ) - 3, date ( "d" ), date ( "Y" ) ) ) );
	$newBeginDate = $newBeginDate->format ( "Y-m-d" );
	$url = "http://pasta.lternet.edu/audit/report/?serviceMethod=createDataPackage&status=200&fromTime=" . $newBeginDate . "&toTime=" . $endDate;
	callAuditReportTool ( $url, "recentlyCreatedDataPackages" );
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
		$returnvalue = returnAuditReportToolOutput ( $url );
		
		$XML = new SimpleXMLElement ( $returnvalue );
		$authorName = "";
		$authorCount = 0;
		foreach ( $XML->dataset->creator as $name ) {
			if ($name->individualName != null) {
				if ($authorCount != 0)
					$tempName = ( string ) ", " . $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				else
					$tempName = ( string ) $name->individualName->givenName . " " . ( string ) $name->individualName->surName;
				$authorCount ++;
				$authorName = $authorName . $tempName;
			}
		}
		
		$temp = array (
				"name" => str_replace ( "/", ".", $recentDataPackages [$randomNumbers [$i]] ),
				"title" => ( string ) $XML->dataset->title,
				"date" => ( string ) $XML->dataset->pubDate,
				"author" => $authorName 
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
				<a class="navbar-brand" href="#">LTER Network Information System
					Report</a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav">
					<li class="active"><a href="#">Home</a></li>
					<li><a href="#about">About</a></li>
					<li><a href="#contact">Contact</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="starter-template">
			<h1>Welcome to LTER Network Information System Report</h1>
			<br>
			<p class="lead">This report describes the current status of the data
				package inventory as published in the LTER network information
				system. It is produced to highlight the volume of public access data
				produced by the LTER network of research sites. This report is
				intended for the LTER Executive Board, National Science Foundation
				program officers, and other interested parties</p>
		</div>

		<div class="col-md-12">
		
		<?php if (!isset ( $_POST ['submitReport'] )) { ?>
			<form class="form-signin" method="POST" action="index.php">
				<button id="reportButton" class="btn btn-lg btn-primary btn-block"
					type="submit" name="submitReport">Generate LTER Network Information
					System Report</button>
			</form>
		<?php }?>	
			<div class="starter-template" id="afterSubmit">
				<p class="lead">Please wait while we generate your report.....</p>
			</div>
			
		<?php
		if (isset ( $_SESSION ['totalDataPackages'] )) {
			
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
		
		if (isset ( $_SESSION ['dataPackageDownloads'] )) {
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
		if ((isset ( $_SESSION ['totalDataPackages'] )) && (isset ( $_SESSION ['updateDataPackages'] ))) {
			
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
						<td><?php echo $data[$i]['name']; ?></td>
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
		$('#reportButton').click(function() {				
				$('#reportButton').hide();
				$('#afterSubmit').show();
			
		}).change();
	});
</script>
</body>
</html>
