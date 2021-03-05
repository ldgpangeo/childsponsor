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


$childid = getinput("childid");
if ($childid == '') {
	$errmsg = "I don't know which child's image to update.";
}
$sessionid = getinput("id");

$groupid = getinput("gid", 1);

# generate the current image
	$dest_file = sprintf('%03s',$childid). ".jpg";
	$dest = '../dbimg/'. $dest_file;

# --------------------------------------------  Process Form output  -----------------------------------------
if ($_POST['submit'] <> '') {
#	debug ("Ini max file is set to ". ini_get("upload_max_filesize") );
#	debug ("Error flag is -" . $_FILES['newfile']['error'] . "-");
	debug ("$files array contains\n".dump_array($_FILES));
	$errmsg = '';
	debug ("Saving image to $dest from {$_FILES['newfile']['tmp_name']}");
	if (! move_uploaded_file($_FILES['newfile']['tmp_name'],$dest)) {
		$errmsg .= "File upload has failed.";
		#				do_sql("rollback");
	} else {
		$sql = "update items set image = '$dest_file' where itemid = '$childid'";
		debug ($sql);
		$res = do_sql($sql);
		if ($res === false) {
			#					do_sql("rollback");
			$errmsg .= " unable to update new row with image information.";
		} else {
			logit("IMAGE_UPDATE","Child id = $childid, admin = $login");
		}
	}
	if ($errmsg == '') {
		header("location: {$url}/admin/child_list.php?gid={$groupid}&id={$sessionid}");
	}
}

#  -------------------------------------------- End form processing -------------------------------------------

if ($errmsg == '') {
	$mode = "update";
	$sql = "select * from items where itemid = '$childid'";
	debug($sql);
	$res = do_sql($sql);
	if (mysqli_num_rows($res) == 1) {
		$row= mysqli_fetch_assoc($res);
	} else {
		$errmsg = "Child not found.";
	}
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
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<title>KOIKOI Update Child Image</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Update Child Image</H2>
<?php
if ($errmsg <> '') {
	print "<h2 align=\"center\">$errmsg</h2>\n";
} else {
?>
<form action="" method="POST" enctype="multipart/form-data">
<table align="center" border="1" cellpadding="3" cellspacing="3" width="80%">
<tr bgcolor="#FFFFCC">
<th colspan="2">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
<input type="hidden" name="childid" value="<?php print $childid ?>">
<input type="hidden" name="id" value="<?php print $sessionid ?>">
</th>
</tr>
<tr>
<td>Image file:</td>
<td><input name="newfile" type="file" /></td>
</tr>
<tr bgcolor="#FFFFCC">
<td align="center" colspan="2">
<input type="submit" name="submit" value="Save Changes">
</td>
</tr>
</table>
</form>
<?php  } #  end no error is present.?>
<p align="center">Current image<br><img src="<? print $dest ?>"></p>
<p align="center">
<a href="child_list.php?gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Return to list of children</a>
</p>


 