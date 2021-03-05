<?php
try {
include_once("../lib/common-init.php");


$sessionid = getinput('id');
if ($sessionid <> '') {
	do_sql("delete from sessions where sessionid = '$sessionid'");
	$redirect = "$webroot/index.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php 
if ($redirect <> '') {
	print "<meta http-equiv=\"refresh\" content=\"1;URL=$redirect";
	if ($referrer <> '') {
		print "?referrer=".urlencode($referrer);
	}
	print "\">";
}
?>
<title>Admin Logout</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<h3>You are now logged out.</h3>

</body>
</html>
<?php


} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>


