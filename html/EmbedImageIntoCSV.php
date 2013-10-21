<?php

function embedImageIntoCSV($imagePath,$coordinates,$fileSavePath,$objPHPExcel){
	error_reporting ( E_ALL );
	ini_set ( 'display_errors', TRUE );
	ini_set ( 'display_startup_errors', TRUE );
	
	date_default_timezone_set ( 'Europe/London' );
	require_once dirname ( __FILE__ ) . '/../PHPExcel/PHPExcel.php';

	// Add some data, we will use printing features
	$objDrawing = new PHPExcel_Worksheet_Drawing ();
	$objDrawing->setName ( 'Total Created packages' );
	$objDrawing->setDescription ( 'Total Created packages Image' );
	$objDrawing->setPath ( $imagePath ); // filesystem reference for the image file
	$objDrawing->setHeight ( 400 ); // sets the image height to 36px (overriding the actual image height);
	$objDrawing->setCoordinates ( $coordinates); // pins the top-left corner of the image to cell D24
	$objDrawing->setOffsetX ( 0 ); // pins the top left corner of the image at an offset of 10 points horizontally to the right of the top-left corner of the cell
	$objDrawing->setWorksheet ( $objPHPExcel->getActiveSheet () );
	$objWriter = PHPExcel_IOFactory::createWriter ( $objPHPExcel, 'Excel2007' );
	$objWriter->save($fileSavePath);	
}
