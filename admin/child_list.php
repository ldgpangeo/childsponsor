<?php
try {
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

#  Get the group to display.
$groupid = getinput("gid", 1);
$groupname = get_dictionary_label($groupid,"groups", "unknown");

$sort = $_GET['sort'];
if ($sort == '') {
	$sort = 'title,itemid';
}
if ($sort == "is_sponsored") {
	$sort = "is_sponsored,sponsorships.last_name";
}
$dir = $_GET['dir'];
if ($dir == '') {
	 $dir = 'asc';
	$newdir = 'asc';
} else if ($dir == 'asc') {
	$newdir = 'desc';
} else {
	$newdir = 'asc';
}

  $sql = "select items.*,sponsorships.sponsorid, sponsorships.last_name, ".
  		 "floor(datediff(now(), dob)/365) as age, email ".
  		 "from items left join sponsorships on items.itemid = sponsorships.itemid and effective_end_ts is null ".
  		 " where items.groupid = '$groupid' order by $sort $fragment  ";

$res = do_sql($sql);

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
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<title>KOIKOI List Children</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>List Children in Group <?php print $groupname ?></H2>

<table align="center" border="1" cellpadding="3" cellspacing="3" width="95%">
<tr bgcolor="#FFFFCC"><td colspan="8" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
</td>
</tr>
<tr bgcolor="#FFFFCC">
<th><a href="child_list.php?sort=itemid&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">ID</a></th>
<th><a href="child_list.php?sort=title&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Title</a></th>
<th><a href="child_list.php?sort=dob&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">DOB</a></th>
<th>Summary</th>
<th width="60px"><a href="child_list.php?sort=is_sponsored&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Sponsored</a></th>
<th><a href="child_list.php?sort=email&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Email</a></th>
<th width="60px"><a href="child_list.php?sort=is_active&gid=<?php print $groupid ?>&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Active</a></th>
<th>Action
</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($res)) { ?>
<tr>
<td><?php
if ($row['childid'] == '') {
	print $row['itemid'];
} else {
	print $row['childid'];
	if ($row['childid'] <> $row['itemid']) { print "&nbsp;&nbsp;(".$row['itemid'].")";}
}
?>&nbsp;</td>
<td><?php print $row['title'] ?>&nbsp;</td>
<td><?php 
$tmp = us_date($row['dob']);
if ($tmp <> "unknown") { print $tmp;} else { print "&nbsp;";}
?></td>
<td><?php print $row['summary'] ?>&nbsp;</td>
<td align="center"><?php 
if ($row['email'] <> '') { $row['is_sponsored'] = 'Y' ; }   #  hack to handle multiple sponsorshiips.
if ($row['is_sponsored'] == 'Y') {
	print $row['last_name'] ;
} elseif ($row['is_sponsored'] == 'P') {
	print "<span class=\"stale\">Pending</span>" ;
} else {
	print "No";
}
?>&nbsp;</td>
<td>
<?php
if ($row['is_sponsored'] == 'Y') {
		print $row['email'];
	}else {
		print "&nbsp;";
	}	?>
</td>
<td align="center"><?php print $row['is_active'] ?>&nbsp;</td>
<td align="center"><a href="child_edit.php?childid=<?php print $row['childid'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Edit</a>&nbsp;&nbsp;
<a href="image_update.php?childid=<?php print $row['itemid'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Image</a>&nbsp;&nbsp;
<a href="child_sponsor.php?childid=<?php print $row['childid'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Sponsor</a>&nbsp;&nbsp;
<?php if ($row['is_sponsored'] == 'Y') { ?>
	<a href="edit_child_video.php?childid=<?php print $row['itemid'] ?>&id=<?php print $sessionid ?>">Videos</a>
<?php } ?>
</td>

</tr>
<?php } ?>
<tr bgcolor="#FFFFCC"><td colspan="8" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
</td>
</tr>
</table>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>


 
