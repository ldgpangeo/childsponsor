<?php
try{
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


$sort = $_GET['sort'];
if (($sort == '') or ($sort="label")) {
	$sort = 'label';
}
if ($sort == "description") {
	$sort = "description";
}
if ($sort == "seq") {
	$sort = "seq";
}
if ($sort == "url") {
	$sort = "url";
}
if ($sort == "datedone") {
	$sort = "datedone";
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

# get list of videos
$res = do_sql("select * from videos order by $sort $dir");


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
<title>Edit Video Assignments</title>
</head>
<body>
<table width="90%" align="center" border = "1" cellpadding="3" cellspacing="3">
	<tr bgcolor="#FFFFCC">
		<th colspan="6">Select a Video to Edit</th>
	</tr>
	<tr>
		<th width="15px">Action</th>
		<th><a href="video_list.php?sort=label&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Label</a></th>
		<th  width="10px"><a href="video_list.php?sort=seq&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Seq</a></th>
		<th><a href="video_list.php?sort=url&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">YouTube URL</a></th>
		<th><a href="video_list.php?sort=datedone&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Date Publ.</a></th>
		<th><a href="video_list.php?sort=description&dir=<?php print $newdir ?>&id=<?php print $sessionid ?>">Internal Desc.</a></th>

	</tr>
<?php
while ($row = mysqli_fetch_assoc($res)) {
?>	
	<tr>
		<td align="center"><a href="edit_video_child.php?videoid=<?php print $row['videoid'] ?>&id=<?php print $sessionid ?>">Edit</a></td>
		<td><?php print $row['label'] ?></td>
		<td align="center"><?php print $row['seq'] ?></td>
		<td><?php print $row['url'] ?></td>
		<td align="center"><?php print us_date($row['datedone']) ?></td>
		<td><?php print $row['description'] ?></td>
	</tr>
<?php } ?>
<tr bgcolor="#FFFFCC"><td colspan="7" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
</td>
</tr>
</table>
</body>
</html>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>
