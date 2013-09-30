<?php 

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
?>