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


$videoid = getinput('videoid');
if ($videoid == '') {die ("I don't know which video to edit");}
$videoid = clean($videoid);

$childid = getinput('childid');
if ($childid == '') {die ("I don't know which child to edit");}
$childid = clean($childid);


#  --------------------  form processing  --------------------------
if ($_POST['submit'] <> '') {
	debug("processing submit.");
	# get input type
	$insert = ($_POST['new'] == Y);
	# validate user input
	$errmsg = '';
	$label = clean($_POST['label']);
	$description = clean($_POST['description']);
	$datedone = $_POST['datedone'];
	debug ("datedone is $datedone which becomes ". dbdate($datedone));
	if ($datedone == 'unknown') {$datedone = "12/31/1969";}
	$datedone = dbdate($datedone);
	$url = trim($_POST['url']);
	$seq = clean($_POST['seq']);
	$is_active = $_POST['is_active'];
	# generate sql command
	if ($insert) {
		do_sql("start transaction");
		$sql = "insert videos (label, url,seq, description, datedone) values ('$label','$url','$seq','$description','$datedone')";
		do_sql($sql);
		# get new videoid
		$videoid = mysqli_insert_id($hdl);
		if ($videoid === null) {
			throw new Exception("Insertion failed | $sql",ERROR_MAJOR);
		}
		do_sql("insert items_videos (itemid,videoid,is_active) values ('$childid','$videoid','$is_active') on duplicate key update is_active = '$is_active'");
		do_sql("commit");
		$event = "Video Added";
		$detail = "Child id = $childid, url = $url, admin = $login";
	} else if (($url <> '') or ($is_active == 'N')) {
		do_sql('start transaction');
		$sql = "update videos set label = '$label', url = '$url', seq = '$seq', " .
		       "description = '$description', datedone = '$datedone' where videoid = '$videoid' ";
		do_sql($sql);
		do_sql("update items_videos set is_active = '$is_active' where videoid = '$videoid' and itemid = '$childid'");	    
		do_sql("commit");
		$event = "Video Updated";
		$detail = "Child id = $childid, , videoid = $videoid, url = $url, admin = $login";
	} else {
		$sql = "update items_videos set is_active = 'N' where videoid = '$videoid' and itemid = '$childid'";
		$event = "Video Deleted";
		$detail = "Child id = $childid, videoid = $videoid, admin = $login";
		do_sql($sql);
	}
	logit($event, "$detail");

}
#  ---------------------  end form processing  ---------------------

if ($videoid > 0) {
	$is_new = false;
	$sql = "select v.*, i.is_active from videos v,items_videos i where i.videoid = v.videoid and v.videoid = '$videoid'";
	$res = do_sql($sql);
	$row = mysqli_fetch_assoc($res);
} else {
	$is_new = true;
	$row = array("is_active" => 'Y');
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
<title>Video edit</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Add or Edit Supplementary Videos</H2>
<?php
if ($errmsg <> '') {
	print "<h3>$errmsg</h3>\n";
}
?>
<form action="" method="POST">
<Table width="80%" border="1" cellpadding="3" cellspacing="3" align="center">
<tr  bgcolor="#FFFFCC">
<td colspan="2" align="center">
<?php if ($is_new) {
	print "Adding a new Video";
} else {
	print "Editing an existing Video";
}
?>
</td>
</tr>
<tr>
<td>Video Label<span class="stale">*</span>:</td>
<td>
<?php

if ($is_new) {
	print "<input type=\"text\" name= \"label\" size = \"40\" maxlength = \"80\" >";
	print "<input type= \"hidden\" name=\"new\" value=\"Y\">";
} else {
	print "<input type=\"text\" name= \"label\" size = \"40\" maxlength = \"80\" value=\"{$row['label']}\">";
	print "<input type= \"hidden\" name=\"new\" value=\"N\">";
}
?>
</td>
</tr>
<tr>
	<td>Internal Description<span class="stale">*</span></td>
	<td><input type="text" name="description" maxlength="255" size="60" value="<?php print $row['description'] ?>"></td>
</tr>
<tr>
	<td>Video Date<span class="stale">*</span></td>
	<td><input type="text" name="datedone" maxlength="255" size="60" value="<?php print us_date($row['datedone']) ?>"></td>
</tr>
<tr>
<td>YouTube URL:<span class="stale">*</span></td>
<td><input type="text" name="url" maxlength="255" size="60" value="<?php print $row['url'] ?>"></td>
</tr>
<tr>
<td>Sequence:<span class="stale">*</span></td>
<td><input type="text" name="seq" maxlength="5" size="6" value="<?php print $row['seq'] ?>"><br>
</td>
</tr>
<tr>
<td>Status:</td>
<td><input type = "radio" name="is_active" value="Y" <?php if ($row['is_active'] <> 'N') { print "checked"; } ?>>Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type = "radio" name="is_active" value="N" <?php if ($row['is_active'] == 'N') { print "checked"; } ?>>No
</td>
</tr>
<tr bgcolor="#FFFFCC">
<td><a href="child_edit.php?childid=<?php print $childid ?>&id=<?php print $sessionid ?>">Return to Child Editor</a></td>
<td align="center"  >
<input type="hidden" name="id" value="<?php print $sessionid ?>">
<input type="submit" name="submit" value="Save Changes">
</td>
</tr>
<tr>
<td colspan="2">
Note:  Change status it "No" to hide this video.
</td>
</tr>
<tr>
<td colspan="2">
<span class="stale">*</span> Caution:  Changing these items can affect multiple children.
</td>
</tr>
</table>
</form>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>

