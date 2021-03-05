<?php
try {
  include_once("../lib/common-init.php");

  # test if user belongs here
  $sessionid = getinput('id');
  $tmparray = require_login($sessionid);
 	if ($tmparray === false) {
		$redirect = "$webroot/admin/login.php";
		$referrer = $_SERVER['REQUEST_URI'];
	} else {
		$is_ok = true;
		$uid = $tmparray[0];
		$login = $tmparray[1];
	}


	$rows = array();
	$res = do_sql("select * from admins where effective_end_ts is null order by end_ts,login");
	while ($row = mysqli_fetch_assoc($res)) { array_push($rows, $row);}
	debug ("Found {count($rows)} rows of admins."); 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
if ($redirect <> '') {
	print "<meta http-equiv=\"refresh\" content=\"1;URL=$redirect";
	if ($referrer <> '') {
		print "?referrer=".urlencode($referrer);
	}
	print "\">";
}
?>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style.css" rel="stylesheet" type="text/css">
<title>KOIKOI Admins</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>List Administrators</H2>
<form action="" method="post">
<table width="90%" border = "1" cellpadding="3" cellspacing="3" align="center">
	<tr bgcolor="#FFFFBB">
		<td align="center" colspan="6"><h3>List administrators</h3></td>
	</tr>
	<tr>
		<th>Login</th>
		<th>Name</th>
		<th>Email</th>
		<th>Began</th>
		<th>Ended</th>
		<th>Action</th>
	</tr>
<?php
foreach ($rows as $row) {
	$start = strftime('%m/%d/%y %I:%M %p',strtotime($row['start_ts']));
	if ($row['end_ts'] <> null) {
		$end = strftime('%m/%d/%y %I:%M %p',strtotime($row['end_ts']));
	} else {
		$end = '';
	}
	print "<tr>";
	print "<td>{$row['login']} </td>\n";
	print "<td>{$row['name']} </td>\n";
	print "<td>{$row['email']} </td>\n";
	print "<td>{$start} </td>\n";
	print "<td>{$end} </td>\n";
	print "<td><a href=\"admin_edit.php?uid={$row['uid']}&id=$sessionid\">Edit</a></td>";
	print "</tr>\n";
}

?>	
<tr bgcolor="#FFFFBB">
	<td  colspan="6"><a href="admin_edit.php?uid=-1&id=<?php print $sessionid ?>">Add new admin </a></td>
</tr>
</table>
</form>

</p>
  <p align="center"><a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a></p>

</body>
</html>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>


