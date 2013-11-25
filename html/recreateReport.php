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
<link href="../assets/css/print.css" rel="stylesheet" media="print" />

</head>
<?php
class MyDB extends SQLite3 {
	function __construct() {
		$this->open ( '../db/LTERSavedReports.db' );
	}
}
$db = new MyDB ();

//Check if the passed report id is present in the database.
$recordID =  intval($_GET ['ID']);

$error = false;

//If not present, report an error.
if ($recordID == "" || $recordID == NULL) {
	global $error;
	$error = true;
}

//Fetch the necessary information from the database for the given record id.
$stmt = $db->prepare ( 'SELECT * FROM saveLTERGeneratedReports where id=:id' );
$stmt->bindValue ( ':id', $recordID, SQLITE3_INTEGER );
$result = $stmt->execute ();
$retrievedData = $result->fetchArray ();

if ($retrievedData ['ID'] != $recordID) {
	global $error;
	$error = true;
}

$stmt = $db->prepare ( 'SELECT * FROM saveRecentPackages where reportID=:id' );
$stmt->bindValue ( ':id', $recordID, SQLITE3_INTEGER );
$results = $stmt->execute ();
$i = 0;
while ( $row = $results->fetchArray () ) {
	$resultRecentPackages [$i ++] = $row;
}

$stmt = $db->prepare ( 'SELECT * FROM saveReportComments where reportID=:id' );
$stmt->bindValue ( ':id', $recordID, SQLITE3_INTEGER );
$result = $stmt->execute ();
$retrievedComments = $result->fetchArray ();

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
					<li><a href="submitReportID.php">Retrieve Old Reports</a></li>
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
				intended for the LTER Personnel, National Science Foundation program
				officers, and other interested parties</p>
		</div>
		  <?php
				global $error;
				if ($error === false) {
					?>
		<div class="col-md-12">

			<div class="starter-template">
				<hr>
				<p class="lead">Report for Site : <?php echo $retrievedData['site'] ;?> </p>
				&nbsp;
				<p class="lead">Report was created on : <?php echo $retrievedData['createdOn'] ;?> </p>
				&nbsp;
				<p class="lead">Total Number Of Data Packages In Network Information
					System</p>
				<p>This report reflects the total number of data packages published
					by LTER sites in the network information system. It includes the
					total by quarter.</p>
			</div>
			<div style="text-align: center"><?php echo $retrievedComments['comment1'] ;?> </div><br><br>
			<div id="chart_div_totalDataPackages"
				style="width: 1000px; height: 400px;"></div>

			<div class="page-break"></div>
			<div class="starter-template">
				<p class="lead">Number of Data Package Downloads</p>
				<p>This graphic reflects the number of data package downloads from
					the LTER network information system by quarter.</p>
			</div>
			<div style="text-align: center"><?php echo $retrievedComments['comment2'] ;?> </div><br><br>
			<div id="chart_div_dataPackagesDownloads"
				style="width: 1000px; height: 400px;"></div>


			<div class="starter-template">
				<p class="lead">Network Summary Statistics</p>
				<div style="text-align: center"><?php echo $retrievedComments['comment3'] ;?> </div><br><br>
				<table class="table table-striped table-bordered">
					<tr>
						<th></th>
						<th><?php global $retrievedData; echo $retrievedData['CurrentQuarterDate']; ?></th>
						<th><?php echo $retrievedData['PreviousQuarterDate']; ?></th>
						<th>A year Ago</th>
						<th>Last 12 Months</th>
					</tr>
					<tr>
						<td>Number of data packages published</td>
						<td><?php echo $retrievedData['totalDataPackagesCurrentQ']; ?></td>
						<td><?php echo $retrievedData['totalDataPackagesLastQ']; ?></td>
						<td><?php echo $retrievedData['totalDataPackagesAyear']; ?></td>
						<td><?php echo $retrievedData['totalDataPackages12Month']; ?></td>
					</tr>
					<tr>
						<td>Number of data package updates/revisions</td>
						<td><?php echo $retrievedData['updateDataPackages4']; ?></td>
						<td><?php echo $retrievedData['updateDataPackages3']; ?></td>
						<td><?php echo $retrievedData['totalUpdateDataPackageAYearAgo']; ?></td>
						<td><?php echo ($retrievedData['updateDataPackages1'] + $retrievedData['updateDataPackages2'] + $retrievedData['updateDataPackages3'] + $retrievedData['updateDataPackages4']); ?></td>
					</tr>
				</table>

				<table class="table table-striped table-bordered">
				
					<tr>
						<th></th>
						<th>Current Quarter - <?php echo $retrievedData['AsOfCurrentQuarterDate']; ?></th>
						<th>Previous Quarter - <?php echo $retrievedData['AsOfPreviousQuarterDate']; ?></th>
						<th>A year ago - <?php echo $retrievedData['AsOfPreviousYearDate']; ?></th>
					</tr>
					<tr>
						<td>Total number of published data packages</td>
						<td><?php echo $retrievedData['totalDataPackages4']; ?></td>
						<td><?php echo $retrievedData['totalDataPackages3']; ?></td>
						<td><?php echo $retrievedData['totalCreateDataPackageAYearAgo']; ?></td>
					</tr>
				</table>
			</div>

				
			<div class="page-break"></div>
			<?php
					global $resultRecentPackages;
					if(count($resultRecentPackages) > 1){
				?>
			<div class="starter-template">
				<p class="lead">Selection of Recently Published Datasets (Last Three
					Months)</p>
				<p>This table presents a random selection of data packages published
					during the current reporting period. It is intended to provide a
					flavor of the type of research data being made accessible through
					the LTER Network Information System.</p>
					<div style="text-align: center"><?php echo $retrievedComments['comment4'] ;?> </div><br><br>
				<table class="table table-striped table-bordered">
					<tr>
						<th style="text-align: center">Data Package Identifier</th>
						<th style="text-align: center">Creators</th>
						<th style="text-align: center">Publication Date</th>
						<th style="text-align: center">Title</th>
					</tr>
					<?php
					global $resultRecentPackages;
					$data = $resultRecentPackages;
					$size = (count ( $data ) > 10 ? 10 : count ( $data ));
					for($i = 0; $i < $size; $i ++) {
						?><tr>
						<td><a href=<?php echo $data[$i]['identifierLink'];?>><?php echo $data[$i]['name']; ?></a></td>
						<td><?php echo $data[$i]['author']; ?></td>
						<td><?php echo $data[$i]['date']; ?></td>
						<td><?php echo $data[$i]['title']; ?></td>
					</tr>
					<?php } ?>
				</table>
			</div>
			<?php } ?>
		</div>
		<?php
				
} else {
					?>
		<div class="span3" style="text-align: center">
			<p class="lead">There are no reports with the given report ID. Please
				verify that the report ID exists before retrying.</p> &nbsp;
		<?php }?>
		</div>
	</div>
	<!-- /.container -->

	<!-- Bootstrap core JavaScript
    ================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script type="text/javascript" src="../assets/js/jquery.js"></script>
	<script type="text/javascript" src="../dist/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../dist/js/jquery.jeditable.js"></script>
	<script type="text/javascript" src="//www.google.com/jsapi"></script>
	
	<script type="text/javascript" src="//canvg.googlecode.com/svn/trunk/canvg.js"></script>
	<script type="text/javascript"src="//canvg.googlecode.com/svn/trunk/rgbcolor.js"></script>
	<script type="text/javascript"src="//canvg.googlecode.com/svn/trunk/StackBlur.js"></script>
	
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChartTotalDataPackages);
      google.setOnLoadCallback(drawChartDataPackageDownloads);
      
      function drawChartTotalDataPackages() {
        var data = google.visualization.arrayToDataTable([
          ['Quarter', 'Total Packages'],         
          [<?php echo "'".$retrievedData['quarterTitle0']."'"; ?>, <?php echo $retrievedData['totalDataPackages0']; ?>],
          [<?php echo "'".$retrievedData['quarterTitle1']."'"; ?>, <?php echo $retrievedData['totalDataPackages1']; ?>],
          [<?php echo "'".$retrievedData['quarterTitle2']."'"; ?>, <?php echo $retrievedData['totalDataPackages2']; ?>],
          [<?php echo "'".$retrievedData['quarterTitle3']."'"; ?>, <?php echo $retrievedData['totalDataPackages3']; ?>],
          [<?php echo "'".$retrievedData['quarterTitle4']."'"; ?>, <?php echo $retrievedData['totalDataPackages4']; ?>],
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
            [<?php echo "'".$retrievedData['quarterTitle0']."'"; ?>, <?php echo $retrievedData['dataPackageDownloads0']; ?>,  <?php echo $retrievedData['dataPackageArchiveDownloads0']; ?>],
            [<?php echo "'".$retrievedData['quarterTitle1']."'"; ?>, <?php echo $retrievedData['dataPackageDownloads1']; ?>,  <?php echo $retrievedData['dataPackageArchiveDownloads1']; ?>],
            [<?php echo "'".$retrievedData['quarterTitle2']."'"; ?>, <?php echo $retrievedData['dataPackageDownloads2']; ?>,  <?php echo $retrievedData['dataPackageArchiveDownloads2']; ?>],
            [<?php echo "'".$retrievedData['quarterTitle3']."'"; ?>, <?php echo $retrievedData['dataPackageDownloads3']; ?>,  <?php echo $retrievedData['dataPackageArchiveDownloads3']; ?>],
            [<?php echo "'".$retrievedData['quarterTitle4']."'"; ?>, <?php echo $retrievedData['dataPackageDownloads4']; ?>,  <?php echo $retrievedData['dataPackageArchiveDownloads4']; ?>],
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
</body>
</html>
