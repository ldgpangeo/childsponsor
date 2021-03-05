<?php
include "../lib/common-init.php";

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
<title>KOIKOI Administration</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Main Menu</H2>

<Table width="80%" border="1" cellpadding="3" cellspacing="3" align="center">
<tr>
<td width="50%"  valign="top">
<p>Manage Children</p>
<ul>
<form action="child_list.php" method="get">	
<li>List current Children in group 
<?php dictionary_as_select("gid", "groups"); ?>
<input type="hidden" name="id" value="<?php print $sessionid ?>">	
<input type="submit" name="Go" value="Go">
</form>
</li>	
<form action="child_edit.php" method="get">	
<li>Add a new child in group
<?php dictionary_as_select("gid", "groups"); ?>
<input type="hidden" name="id" value="<?php print $sessionid ?>">
<input type="hidden" name="childid" value="-1">
<input type="submit" name="Go" value="Go">
</form>
</li>
</ul>
<p>Manage Videos</p>
<ul>
<li><a href="video_list.php?id=<?php print $sessionid ?>">List current videos</a></li>	
<li><a href="edit_video_child.php?id=<?php print $sessionid ?>">Add new video</a></li>	
</ul>
<p>Manage Administrators</p>
<ul>
<li><a href="admin_list.php?id=<?php print $sessionid ?>">Manage Admins</a></li>
</ul>
<p>Manage Settings</p>
<ul>
<li><a href="dictionary_list.php?area=default&id=<?php print $sessionid ?>">Edit dictionary</a></li>
<form action="group_edit.php" method="get">	
<li>Edit group settings
<input type="hidden" name="id" value="<?php print $sessionid ?>">
<?php dictionary_as_select("gid", "groups"); ?>
<input type="submit" name="Go" value="Go">
</form>
</li>
</ul>
</td>
<td width="50%" valign="top">
<p>Review Activity</p>
<ul>
	<li><a href="search_pending.php?id=<?php print $sessionid ?>">Search Pending Sponsor</a></li>
</ul>


<form method="post" action="search_sponsors.php?id=<?php print $sessionid ?>">
	<ul>
		<li>Search Active Sponsorships
			<?php dictionary_as_select("gid", "groups"); ?> 
			<input type="submit" name="submit" value="Go">
		</li>
	</ul>
	
</form>
	<ul>
		<li>View Logs:  <a href="view-log.php?id=<?php print $sessionid ?>">Activity Log</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="view-error.php?id=<?php print $sessionid ?>">Error Log</a>
		</li>
	</ul>


</td>
</tr>
<tr bgcolor="#FFFFBB">
<td align="center" colspan="2"><a href="../index.php">Return to Public Pages</a></td>
</tr>
</Table>
