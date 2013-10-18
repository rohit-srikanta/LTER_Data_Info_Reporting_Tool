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

//Including the file that has information on how to call LTER Data Portal
require_once ('curlWebServiceCalls.php');

//Checking if the PHP Post variable submitReport has been set. This variable will be set when the user clicks on Generate LTER Report in the main page.
if (isset ( $_POST ['submitReport'] )) {
	
	global $errorStatus;
	$errorStatus="";
	
	//Calling the starter method to generate report.
	$reportGenerationStatus = generateReport();
	
	//If the user credentials is not correct, exit the report generation without computing the report.
	if($reportGenerationStatus == "invalidLogin"){
		global $errorStatus;
		$errorStatus="invalidLogin";
	}
	//If there was any error during reporting, throw the error to the user.
	if($reportGenerationStatus != "success" && $reportGenerationStatus != "invalidLogin"){
		global $errorStatus;
		$errorStatus="reportError";
	}
}

//The main starter method where we process all the reports in sequence. This method controls all the methods that call PASTA to retrive the necessary information. 
function generateReport() {
	session_start ();
	
	$username = $_POST ['username'];
	$password = $_POST ['password'];
	$endDate = NULL;
	$beginDate = NULL;
	
	//Setting the start date to one year ago from current time. 
	date_default_timezone_set ( 'MST' );
	
	//If the user has choosen include current quarter, then include the data until present date
	if ($_POST ['quarter'] === 'current') {
		$endDate = date ( "Y-m-d" );
		$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, date ( "m" ) - 3, 01, date ( "Y" ) - 1 ) ) );
		$beginDate = $beginDate->format ( "Y-m-d" );
	}
	//If the report has to be generated until previous quarter, find the previous quarter date and make webs services calls with that date.
	else {		
		$currentmonth = date ( "m" );
		$endMonth = $currentmonth - ($currentmonth % 3 == 0 ? 3 : $currentmonth % 3);
		$endDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, $endMonth, cal_days_in_month(CAL_GREGORIAN, $endMonth,(date("Y"))), date ("Y") ) ) );
		$endDate =  $endDate->format ( "Y-m-d" );
		$beginDate = new DateTime ( date ( DATE_ATOM, mktime ( 0, 0, 0, $endMonth - 3, 01, date ( "Y" ) - 1 ) ) );
		$beginDate = $beginDate->format ( "Y-m-d" );
	}
	
	//If its an authenticated user, then only continue to generate the report.
	if (!authenticatedUser()) {
		unset ( $_SESSION ['submitReport'] );
		return "invalidLogin";
	}

	$quarter = determineFourQuarters(substr($endDate,5,2),$_POST ['quarter']);
	
	//First compute all the 4 quarters thats necessary to generate the report. 
	
	//Include the file that is used to compute the total number of packages and compute it
	require_once ('totalNumberOfDataPackages.php');
	createTotalDataPackagesInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['totalDataPackages'] ) && $_SESSION ['totalDataPackages'] != null){
		$deleteCount = countDeletedPackages($beginDate, $endDate,$quarter);
		createTotalDataPackagesOutput ( $_SESSION ['totalDataPackages'], $quarter,$deleteCount);
	}
	//Adding a sleep command as making numerous calls to PASTA in a short interval results in failure to get the information.
	sleep ( 2 );
	
	//Include the file that is used to compute the total number of package downloads and compute it
	require_once ('dataPackageDownloads.php');
	createDataPackagesDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageDownloads'] ) && $_SESSION ['dataPackageDownloads'] != null)
		createDataPackagesDownloadOutput ( $_SESSION ['dataPackageDownloads'],$quarter);
	
	//Include the file that is used to compute the total number of archive package downloads and compute it
	createDataPackagesArchiveDownloadsInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['dataPackageArchiveDownloads'] ) && $_SESSION ['dataPackageArchiveDownloads'] != null)
		createDataPackagesArchiveDownloadOutput ( $_SESSION ['dataPackageArchiveDownloads'], $quarter);

	//Include the file that is used to compute the total number of packages that were updated and compute it
	updateTotalDataPackagesInputData ( $beginDate, $endDate );
	if (isset ( $_SESSION ['updateDataPackages'] ) && $_SESSION ['updateDataPackages'] != null)
		updateDataPackagesOutput ( $_SESSION ['updateDataPackages'], $quarter );
	
	countDataPackagesForYearAgo($quarter,$endDate);
	
	//Include the file that is used to compute the random list to of packages created in the last three months. 
	require_once ('recentlyPublishedDatasets.php');
	recentlyPublishedDataSetsInput ( $endDate );
	if (isset ( $_SESSION ['recentlyCreatedDataPackages'] ) && $_SESSION ['recentlyCreatedDataPackages'] != null)
		recentlyPublishedDataSets ( $_SESSION ['recentlyCreatedDataPackages'] );
	return "success";
}
//Method to compute the quarter to which we generate the report. Since we are calculating the report for one year, this report will have exactly 4 quarters
function determineFourQuarters($month) {
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
	//Creating a cyclic array to pick the 4 quarters. 4th quarter is the latest quarter and we go back 3 months and assign months to that quarter. 
	//0th quarter is the 4th quarter but a year before it.
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
	
	//The 0th quarter is basically the 4th quarter along with the missing months if any.
	if($currentQuarter != 0)
	{
		$tempArray = array_slice ( $newMonthArray,$currentQuarter + 9, 3 );	
		$quarter['0'] = array_merge($tempArray,$quarter ['4']);
	}
	else{
		$quarter['0'] = $quarter ['4'];
	}

	//Quarter names as suffix
	$quarterNames = array (
			"-1",
			"-2",
			"-3",
			"-4" 
	);
	
	//Based on the value of month in the array, we create the quarter titles
	if ($month == 12 || $month == 11 || $month == 10) {
		$quarterTitle ['4'] = date("Y").$quarterNames [3];
		$quarterTitle ['3'] = date("Y").$quarterNames [2];
		$quarterTitle ['2'] = date("Y").$quarterNames [1];
		$quarterTitle ['1'] = date("Y").$quarterNames [0];
		$quarterTitle ['0'] = (date("Y")-1).$quarterNames [3];
	}
	
	if ($month == 7 || $month == 8 || $month == 9) {
		$quarterTitle ['4'] = date("Y").$quarterNames [2];
		$quarterTitle ['3'] = date("Y").$quarterNames [1];
		$quarterTitle ['2'] = date("Y").$quarterNames [0];
		$quarterTitle ['1'] = (date("Y")-1).$quarterNames [3];
		$quarterTitle ['0'] = (date("Y")-1).$quarterNames [2];
	}
	
	if ($month == 5 || $month == 5 || $month == 6) {
		$quarterTitle ['4'] = date("Y").$quarterNames [1];
		$quarterTitle ['3'] = date("Y").$quarterNames [0];
		$quarterTitle ['2'] = (date("Y")-1).$quarterNames [3];
		$quarterTitle ['1'] = (date("Y")-1).$quarterNames [2];
		$quarterTitle ['0'] = (date("Y")-1).$quarterNames [1];
	}
	
	if ($month == 1 || $month == 2 || $month == 3) {
		$quarterTitle ['4'] = date("Y").$quarterNames [0];
		$quarterTitle ['3'] = (date("Y")-1).$quarterNames [1];
		$quarterTitle ['2'] = (date("Y")-1).$quarterNames [2];
		$quarterTitle ['1'] = (date("Y")-1).$quarterNames [3];
		$quarterTitle ['0'] = (date("Y")-1).$quarterNames [0];
	}

	//Creating the custom labels which will be added to the graph and table.
	$_SESSION ['quarterTitle'] =  $quarterTitle;
	
	if ($_POST ['quarter'] === 'current') 
		$_SESSION ['CurrentQuarterDate'] = "From ".$quarter['4'][count($quarter['4'])-1]."/01/".date("Y")." to ".$quarter['4'][0]."/".(date("d"))."/".date("Y");
	else
	$_SESSION ['CurrentQuarterDate'] = "From ".$quarter['4'][2]."/01/".date("Y")." to ".$quarter['4'][0]."/".cal_days_in_month(CAL_GREGORIAN,$quarter['4'][count($quarter['4'])-1],(date("Y")))."/".date("Y");
	
	$_SESSION ['PreviousQuarterDate'] = "From ".$quarter['3'][2]."/01/".date("Y")." to ".$quarter['3'][0]."/".cal_days_in_month(CAL_GREGORIAN,$quarter['3'][0],(date("Y")))."/".date("Y");
	$_SESSION ['AsOfCurrentQuarterDate'] = "As of ".date("m")."/".date("d")."/".date("Y");
	$_SESSION ['AsOfPreviousQuarterDate'] = "As of ".$quarter['3'][0]."/".cal_days_in_month(CAL_GREGORIAN,$quarter['3'][0],(date("Y")))."/".date("Y");
	$_SESSION ['AsOfPreviousYearDate'] = "As of ".date("m")."/".date("d")."/".(date("Y")-1);
	
	return $quarter;
}

//This method is used to authenticate the user credentials. We make a simple call to fetch all the eml identifier. This will fetch all the identifiers in PASTA. 
//Since knb-lter-cap has to be present as one of the eml, we check if its present in the response. If so, the user credentials is correct, if not, we throw a error message.
function authenticatedUser() {
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
					<li><a href="aboutLTER.html">About</a></li>
					<li><a href="contact.html">Contact</a></li>
				</ul>
			</div>
			<!--/.nav-collapse -->
		</div>
	</div>

	<div class="container">
		<div class="starter-template">
			<h1>
				<img src="../assets/ico/LTER.png">&nbsp;&nbsp;Welcome to LTER
				Network Information System Reporting Tool
			</h1>
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
				<p>
					Generate LTER Network Report : <br> <input type="radio"
						name="quarter" checked value="current">&nbsp;Including Current
					Quarter<br> <input type="radio" name="quarter" value="previous">&nbsp;Excluding
					Current Quarter<br>
				</p>
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
				style="width: 1000px; height: 400px;">				
				</div>				
				<button type="button" class="btn btn-primary" onclick="saveAsImg(document.getElementById('chart_div_totalDataPackages'));">Save the chart as Image File</button>
				<?php
		}
		
		if (isset ( $_SESSION ['dataPackageDownloads4'] )) {
			?>
					<div class="starter-template">
				<p class="lead">Number of Data Package Downloads</p>
				<p>This graphic reflects the number of data package downloads from
					the LTER network information system by quarter.</p>
			</div>
			<div id="chart_div_dataPackagesDownloads"
				style="width: 1000px; height: 400px;"></div>
			<button type="button" class="btn btn-primary" onclick="saveAsImg(document.getElementById('chart_div_dataPackagesDownloads'));">Save the chart as Image File</button>	
			<?php
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
						<th><?php echo $_SESSION['CurrentQuarterDate']; ?></th>
						<th><?php echo $_SESSION['PreviousQuarterDate']; ?></th>
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
						<td>Number of data package updates/revisions</td>
						<td><?php echo $_SESSION['updateDataPackages4']; ?></td>
						<td><?php echo $_SESSION['updateDataPackages3']; ?></td>
						<td><?php echo $_SESSION['totalUpdateDataPackageAYearAgo']; ?></td>
						<td><?php echo ($_SESSION['updateDataPackages1'] + $_SESSION['updateDataPackages2'] + $_SESSION['updateDataPackages3'] + $_SESSION['updateDataPackages4']); ?></td>
					</tr>
				</table>

				<table class="table table-striped table-bordered">
					<tr>
						<th></th>
						<th>Current Quarter - <?php echo $_SESSION['AsOfCurrentQuarterDate']; ?></th>
						<th>Previous Quarter - <?php echo $_SESSION['AsOfPreviousQuarterDate']; ?></th>
						<th>A year ago - <?php echo $_SESSION['AsOfPreviousYearDate']; ?></th>
					</tr>
					<tr>
						<td>Total number of published data packages</td>
						<td><?php echo $_SESSION['totalDataPackages4']; ?></td>
						<td><?php echo $_SESSION['totalDataPackages3']; ?></td>
						<td><?php echo $_SESSION['totalCreateDataPackageAYearAgo']; ?></td>
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
	<script type="text/javascript" src="http://canvg.googlecode.com/svn/trunk/canvg.js"></script> 
	<script type="text/javascript" src="http://canvg.googlecode.com/svn/trunk/rgbcolor.js"></script> 
	<script type="text/javascript" src="http://canvg.googlecode.com/svn/trunk/StackBlur.js"></script>
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChartTotalDataPackages);
      google.setOnLoadCallback(drawChartDataPackageDownloads);
      
      function drawChartTotalDataPackages() {
        var data = google.visualization.arrayToDataTable([
          ['Quarter', 'Total Packages'],         
          [<?php echo "'".$_SESSION['quarterTitle']['0']."'"; ?>, <?php echo $_SESSION['totalDataPackages0']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['1']."'"; ?>, <?php echo $_SESSION['totalDataPackages1']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['2']."'"; ?>, <?php echo $_SESSION['totalDataPackages2']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['3']."'"; ?>, <?php echo $_SESSION['totalDataPackages3']; ?>],
          [<?php echo "'".$_SESSION['quarterTitle']['4']."'"; ?>, <?php echo $_SESSION['totalDataPackages4']; ?>],
        ]);

        var options = {
          title: 'LTER Network Data Packages',
          hAxis: {title: 'Quarter Reporting Period'},
          vAxis: {title: "Total Data Packages"},
          colors: ['#F87431'],
          is3D:true
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_totalDataPackages'));
        chart.draw(data, options);
      }

      function drawChartDataPackageDownloads() {
          var data = google.visualization.arrayToDataTable([ 
            ['Quarter', 'Number of Data Downloads', 'Number of Data Archive Downloads'],      
            [<?php echo "'".$_SESSION['quarterTitle']['0']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads0']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads0']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['1']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads1']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads1']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['2']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads2']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads2']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['3']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads3']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads3']; ?>],
            [<?php echo "'".$_SESSION['quarterTitle']['4']."'"; ?>, <?php echo $_SESSION['dataPackageDownloads4']; ?>,  <?php echo $_SESSION['dataPackageArchiveDownloads4']; ?>],
          ]);

          var options = {
            title: 'Number of Network Downloads',
            isStacked: true,
            hAxis: {title: 'Quarter Reporting Period'},
            vAxis: {title: "Number of Downloads"},
            colors: ['#F87431','red']
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

	<script type="text/javascript">

    function getImgData(chartContainer) {
        var chartArea = chartContainer.getElementsByTagName('svg')[0].parentNode;
        var svg = chartArea.innerHTML;
        var doc = chartContainer.ownerDocument;
        var canvas = doc.createElement('canvas');        
        canvas.setAttribute('width', chartArea.offsetWidth);
        canvas.setAttribute('height', chartArea.offsetHeight);
        
        canvas.setAttribute(
            'style',
            'position: absolute; ' +
            'top: ' + (-chartArea.offsetHeight * 2) + 'px;' +
            'left: ' + (-chartArea.offsetWidth * 2) + 'px;');
        doc.body.appendChild(canvas);
        canvg(canvas, svg);
        var imgData = canvas.toDataURL('image/png');
        canvas.parentNode.removeChild(canvas);
        return imgData;
      }
      
      function saveAsImg(chartContainer) {
          
    	var imgData = getImgData(chartContainer);
        window.location = imgData.replace('image/png', 'image/octet-stream');
      }
	</script>
</body>
</html>
