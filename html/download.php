<?php 
$file = trim($_GET['path']);

// force user to download the image
if (file_exists($file)) {
	echo "Inside....";
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}
else {
    echo "$file not found";
}
?>