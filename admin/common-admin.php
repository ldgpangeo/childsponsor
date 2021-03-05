<?php

if (isset($_COOKIE['koikoi'])) {
	$login = $_COOKIE['koikoi'];
	if ($login == '') {
		$redirect = "<Meta http-equiv=\"refresh\" content=\"0;url=login.php\">";
		die();
	}
} else {
	$redirect = "<Meta http-equiv=\"refresh\" content=\"0;url=login.php\">";
}



include_once("../inc/mysql.php");
?>