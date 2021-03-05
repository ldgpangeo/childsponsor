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

$videoid = getinput('videoid');
if ($videoid == '') {
	$insert = true;
	$video_info = array ('datedone' => strftime('%Y-%m-%d'));
} else {
	$insert = false;
}

#  begin  form processing

if ($_POST['submit'] <> '') {
	debug (dump_array($_POST));
	# collect video information
	$errmsg = '';
	$label = clean($_POST['label']);
	if ($label == '') {$errmsg .= "Label can not be blank.";}
	$description = clean($_POST['description']);
	$datedone = $_POST['datedone'];
	debug ("datedone is $datedone which becomes ". dbdate($datedone));
	if ($datedone == 'unknown') {$datedone = "12/31/1969";}
	$datedone = dbdate($datedone);
	$url = trim($_POST['url']);
	if ($url == '') {$errmsg .= "url can not be blank.";}
	$seq = clean($_POST['seq']);
	if ($errmsg <> '') {
		throw new Exception($errmsg,'ERROR_MINOR');
	}
	# save video information to database
	if ($insert) {
		do_sql("start transaction");
		$sql = "insert videos (label, url,seq, description, datedone) values ('$label','$url','$seq','$description','$datedone')";
		do_sql($sql);
		# get new videoid
		$videoid = mysqli_insert_id($hdl);
		if ($videoid === null) {
			do_sql("rollback");
			throw new Exception("Insertion failed | $sql",ERROR_MAJOR);
		}
		$event = "Video Added";
		$detail = "medhod = bulk, url = $url, admin = $login";
		logit ($event,$detail);
		$insert = false;
	} else {
		$sql = "update videos set label = '$label', url = '$url', seq = '$seq', " .
		       "description = '$description', datedone = '$datedone' where videoid = '$videoid' ";
		do_sql($sql);
		$event = "Video updated";
		$detail = "videoid = $videoid, admin = $login";
		logit ($event,$detail);				
	}
	#  store the assignments
	#  clear current assignments
	do_sql("update items_videos set is_active = 'N' where videoid = '$videoid'");
	#  if dropall chosen, skip the assignments
	$childlist = array ();
	if (isset($_POST['dropall']) === false) {
    	#  loop through all chldren
		$children = explode('|', $_POST['children']);
		foreach ($children as $child) {
			$itemid = null;
			#  if setall then use all children
			if (isset($_POST['setall'])) {
				list($junk,$itemid) = split('_',$child);
			#  else use only those explicitly set		
			} else if (isset($_POST[$child])) {
				$itemid = $_POST[$child];
			}
			#  execute only if something was found
			if ($itemid <> null) {
				array_push($childlist, $itemid);
				do_sql("insert items_videos (itemid,videoid,is_active) values ('$itemid','$videoid','Y') on duplicate key update is_active = 'Y'");
			}
		}  #  end loop through children
	} #  end drop all not set
	#  log the activity
		$event = "Video Assigned";
		if (isset($_POST['setall'])) {
		$detail = "videoid = $videoid, Assigned to all children, admin = $login";
	} elseif (isset($_POST['dropall'])) {
		$detail = "videoid = $videoid, removed from all children, admin = $login";
	} else {
		$tmp = join (",",$childlist);
		$detail = "videoid = $videoid, Assigned to childID=$tmp, admin = $login";
	}
	logit($event,$detail);

} # end submit



#  end form processing ---------------------------------------------------------------

if ($insert === false) {
	$res = do_sql("select * from videos where videoid = '$videoid'");
	if (mysqli_num_rows($res) <> 1) {
		throw new Exception("Failed to find a unique video ($videoid)",ERROR_MAJOR);
	}
	$video_info = mysqli_fetch_assoc($res);
#	debug (dump_array($video_info));
}	
$res = do_sql("select c.itemid,i.is_active,c.title,s.first_name,s.last_name " .
       " from items c left join items_videos i on c.itemid = i.itemid and i.videoid = '$videoid' " .
	   " left join sponsorships s on s.itemid = c.itemid and effective_end_ts is null" .
	   " where c.is_sponsored = 'Y'  ".
	   " order by c.title");



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
	<form action="" method="post">
<table width="80%" align="center" border = "1" cellpadding="3" cellspacing="3">
	<tr bgcolor="#FFFFCC">
		<th colspan="2">Add or Edit Video Information</th>
	</tr>
	<tr>
		<td>Label:</td>
		<td><input type="text" size="40" maxlength="80" name="label" value="<?php print $video_info['label'] ?>"></td>
	</tr>
	<tr>
		<td>Internal Description:</td>
		<td><input type="text" size="40" maxlength="255" name="description" value="<?php print $video_info['description'] ?>"></td>
	</tr>
	<tr>
		<td>Video Date:</td>
		<td><input type="text" size="12" maxlength="12" name="datedone" value="<?php print us_date($video_info['datedone']) ?>"></td>
	</tr>
	<tr>
		<td>YouTube URL:</td>
		<td><input type="text" size="40" maxlength="255" name="url" value="<?php print $video_info['url'] ?>"></td>
	</tr>
	<tr>
		<td>Sequence:</td>
		<td><input type="text" size="10" maxlength="10" name="seq" value="<?php print $video_info['seq'] ?>"></td>
	</tr>
	<tr bgcolor="#FFFFCC">
	<th colspan="2">Sponsorship Assignments</th>
	</tr>
<tr>
	<td colspan="2">
	<table width="100%" border = "1" cellpadding="3" cellspacing="3">
    <tr>
    	<th colspan="3">Assign video to ALL sponsorships: <input type="checkbox" name="setall" value="1">
    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    	Drop video from ALL sponsorships: <input type="checkbox" name="dropall" value="1">
    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    	<input type="submit" name="submit" value="Go!">	
    	</th>
    </tr>
	<tr bgcolor="#FFFFCC">
		<th>Active</th>
		<th>Child</th>
		<th>Sponsorship</th>
	</tr>		
<?php
$children = array();
while ($row = mysqli_fetch_assoc($res)) {
	$checkid = "child_". $row['itemid'];
	array_push($children, $checkid);
	if ($row['is_active'] == 'Y') {$checked = "checked"; } else {$checked = '';}
?>
<tr>
	<td align="center"><input type="checkbox" name="<?php print $checkid ?>" value="<?php print $row['itemid'] ?>" <?php print $checked ?> ></td>
	<td align="center"><?php print $row['title'] ?> </td>
	<td align="center"><?php print $row['first_name'] . " " . $row['last_name'] ?> </td>
</tr>
<?php	}  
$all = join("|", $children);
?>
<input type="hidden" name="children" value="<?php print $all ?>" >
	<tr bgcolor="#FFFFCC">
<th colspan="3"><input type="submit" name="submit" value="Save Changes"></th>		
	</td>
</tr>
</table>
</td>		
	</tr>
<tr bgcolor="#FFFFCC">
	<th colspan="2"> <a href="index.php?id=<?php print $sessionid ?>">Return to Index</a>
	</th>
</tr>	
</table>
</form>
</body>
</html>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>
