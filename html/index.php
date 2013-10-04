<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<link rel="shortcut icon" href="../assets/ico/LTER.png">

<title>LTER Network Information System Reporting Tool</title>

<!-- Bootstrap core CSS -->
<link href="../dist/css/bootstrap.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="../assets/css/index.css" rel="stylesheet">

</head>
<?php

//Global declaration of the pasta URL so that if we have to make a change, it can be done in one place.
$pastaURL = "http://pasta.lternet.edu/";
$errorStatus = "";

require_once ('curlWebServiceCalls.php');

if (isset ( $_POST ['submitReport'] )) {
	
	global $errorStatus;
	$errorStatus="";
	
	$reportGenerationStatus = generateReport();
	
	if($reportGenerationStatus == "invalidLogin"){
		global $errorStatus;
		$errorStatus="invalidLogin";
	}
	if($reportGenerationStatus != "success" && $reportGenerationStatus != "invalidLogin"){
		global $errorStatus;
		$errorStatus="reportError";
	}
}

function generateReport() {
	session_start ();
	
	$username = $_POST ['username'];
	$password = $_POST ['password'];
	
	date_default_timezone_set ( 'MST' );
	$endDate = date ( "Y-m-d" );
	$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ), date ( "d" ), date ( "Y" ) - 1 ) ) );
	$beginDate = $beginDate->format ( "Y-m-d" );

	if (!authenticateUser()) {
		unset ( $_SESSION ['submitReport'] );
		return "invalidLogin";
	}
	
	$quarter = determineFourQuarters();
	require_once ('totalNumberOfDataPackages.php');
	createTotalDataPackagesInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['totalDataPackages'] ) && $_SESSION ['totalDataPackages'] != null){
		$deleteCount = countDeletedPackages($beginDate, $endDate,$quarter);
		createTotalDataPackagesOutput ( $_SESSION ['totalDataPackages'], $quarter,$deleteCount);
	}
	sleep ( 2 );
	
	require_once ('dataPackageDownloads.php');
	createDataPackagesDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageDownloads'] ) && $_SESSION ['dataPackageDownloads'] != null)
		createDataPackagesDownloadOutput ( $_SESSION ['dataPackageDownloads'],$quarter);
	
	createDataPackagesArchiveDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageArchiveDownloads'] ) && $_SESSION ['dataPackageArchiveDownloads'] != null)
		createDataPackagesArchiveDownloadOutput ( $_SESSION ['dataPackageArchiveDownloads'], $quarter);
	
	updateTotalDataPackagesInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['updateDataPackages'] ) && $_SESSION ['updateDataPackages'] != null)
		updateDataPackagesOutput ( $_SESSION ['updateDataPackages'], $quarter );
	
	countDataPackagesForYearAgo($quarter);
	
	require_once ('recentlyPublishedDatasets.php');
	recentlyPublishedDataSetsInput ( $endDate );
	if (isset ( $_SESSION ['recentlyCreatedDataPackages'] ) && $_SESSION ['recentlyCreatedDataPackages'] != null)
		recentlyPublishedDataSets ( $_SESSION ['recentlyCreatedDataPackages'] );
	
	return "success";
}

function determineFourQuarters() {
	$month = date ( "m" );
	$monthList = array (
			'12' => '12',
			'11' => '11',
			'10' => '10',
			'9' => '9',
			'8' => '8',
			'7' => '7',
			'6' => '6',
			'5' => '5',
			'4' => '4',
			'3' => '3',
			'2' => '2',
			'1' => '1' 
	);
	$key = array_search ( $month, array_keys ( $monthList ) );
	$month1 = array_slice ( $monthList, $key );
	$month2 = array_slice ( $monthList, 0, $key );
	$newMonthArray = array_merge ( $month1, $month2 );
	
	$currentQuarter = $month % 3;
	if ($currentQuarter == 0)
		$currentQuarter = 3;
	
	$quarter ['4'] = array_slice ( $newMonthArray, 0, $currentQuarter );
	$quarter ['3'] = array_slice ( $newMonthArray, $currentQuarter, 3 );
	$quarter ['2'] = array_slice ( $newMonthArray, $currentQuarter + 3, 3 );
	$quarter ['1'] = array_slice ( $newMonthArray, $currentQuarter + 6, 3 );
	
	$quarterNames = array (
			"1st Quarter",
			"2nd Quarter",
			"3rd Quarter",
			"4th Quarter" 
	);
	
	if ($month == 12 || $month == 11 || $month == 10) {
		$quarterTitle ['4'] = $quarterNames [3];
		$quarterTitle ['3'] = $quarterNames [2];
		$quarterTitle ['2'] = $quarterNames [1];
		$quarterTitle ['1'] = $quarterNames [0];
	}
	
	if ($month == 7 || $month == 8 || $month == 9) {
		$quarterTitle ['4'] = $quarterNames [2];
		$quarterTitle ['3'] = $quarterNames [1];
		$quarterTitle ['2'] = $quarterNames [0];
		$quarterTitle ['1'] = $quarterNames [3];
	}
	
	if ($month == 5 || $month == 5 || $month == 6) {
		$quarterTitle ['4'] = $quarterNames [1];
		$quarterTitle ['3'] = $quarterNames [0];
		$quarterTitle ['2'] = $quarterNames [3];
		$quarterTitle ['1'] = $quarterNames [2];
	}
	
	if ($month == 1 || $month == 2 || $month == 3) {
		$quarterTitle ['4'] = $quarterNames [0];
		$quarterTitle ['3'] = $quarterNames [1];
		$quarterTitle ['2'] = $quarterNames [2];
		$quarterTitle ['1'] = $quarterNames [3];
	}

	$_SESSION ['quarterTitle'] =  $quarterTitle;
	
	return $quarter;
}

function authenticateUser() {
	global $pastaURL;
	$url = $pastaURL . "package/eml";
	$test = returnAuditReportToolOutput ( $url, $_POST ['username'], $_POST ['password'] );
	$pos = strpos ( $test, "knb-lter-cap" );
	if ($pos === false)
		return false;
	else 
		return true;
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
			<h1><img src="../assets/ico/LTER.png">&nbsp;&nbsp;Welcome to LTER Network Information System Reporting Tool</h1>
			<br>
			<p class="lead">This report describes the current status of the data
				package inventory as published in the LTER network information
				system. It is produced to highlight the volume of public access data
				produced by the LTER network of research sites. This report is
				intended for the LTER Executive Board, National Science Foundation
				program officers, and other interested parties</p>
			<hr>
		</div>

		<div class="col-md-12">	
		
		 <?php global $errorStatus;
		 if ($errorStatus === "reportError") {
			echo'<script> alert("There was a problem during error generation. Please try again.");
			window.location="index.php"; </script> ';
		} ?> 
		
		<?php global $errorStatus;
		 if ($errorStatus === "invalidLogin") {
			echo'<script> alert("Unable to generate the report. Please verify the login credentials and try again.");
			window.location="index.php"; </script> ';
		} ?>
		
		<?php if (!isset ( $_POST ['submitReport'] )) { ?>
			<p align="center">
				<i>Please provide the login information to generate LTER Network
					System Report. <br>Please note that the report generation may take
					time.
				</i>
			</p>
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
		if (isset ( $_SESSION ['totalDataPackages4'] )) {
			
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
		
		if (isset ( $_SESSION ['dataPackageDownloads4'] )) {
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
		if ((isset ( $_SESSION ['totalDataPackages4'] )) && (isset ( $_SESSION ['updateDataPackages4'] ))) {
			
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
						<td><?php echo $_SESSION['totalDataPackages4']; ?></td>
						<td><?php echo $_SESSION['totalDataPackages3']; ?></td>
						<td><?php echo $_SESSION['totalCreateDataPackageAYearAgo']; ?></td>						
						<td><?php echo $_SESSION['totalDataPackages4']; ?></td>
					</tr>
					<tr>
						<td>Number of data package updates/revisions</td>
						<td><?php echo $_SESSION['updateDataPackages4']; ?></td>
						<td><?php echo $_SESSION['updateDataPackages3']; ?></td>
						<td><?php echo $_SESSION['totalUpdateDataPackageAYearAgo']; ?></td>						
						<td><?php echo ($_SESSION['updateDataPackages1'] + $_SESSION['updateDataPackages2'] + $_SESSION['updateDataPackages3'] + $_SESSION['updateDataPackages4']); ?>						
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
          [<?php echo "'".$_SESSION['quarterTitle']['1']."'"; ?>, <?php echo $_SESSION['totalDataPackages1']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['2']."'"; ?>, <?php echo $_SESSION['totalDataPackages2']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['3']."'"; ?>, <?php echo $_SESSION['totalDataPackages3']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['4']."'"; ?>, <?php echo $_SESSION['totalDataPackages4']; ?>],
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
            [<?php echo "'".$_SESSION['quarterTitle']['1']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads1']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads1']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['2']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads2']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads2']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['3']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads3']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads3']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['4']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads4']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads4']; ?>],
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
