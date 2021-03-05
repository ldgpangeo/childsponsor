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

# get the child ID
$childid = getinput('childid');
if ($childid == '') {die ("I don't know which child to edit");}
$childid = clean($childid);

#  -------------  process form information  ------------------
if ($_POST['submit'] <> '') {
	debug (dump_array($_POST));
	# collect video information
	$errmsg = '';

	#  store the assignments
	#  clear current assignments
	do_sql("update items_videos set is_active = 'N' where itemid = '$childid'");
	#  if dropall chosen, skip the assignments
	if (isset($_POST['dropall']) === false) {
    	#  loop through all chldren
		$videos = explode('|', $_POST['videos']);
		foreach ($videos as $video) {
			$videoid = null;
			#  if setall then use all children
			if (isset($_POST['setall'])) {
				list($junk,$videoid) = split('_',$video);
			#  else use only those explicitly set		
			} else if (isset($_POST[$video])) {
				debug ("testing ".$_POST[$video] );
				$videoid = $_POST[$video];
			}
			#  execute only if something was found
			if ($videoid <> null) {
				do_sql("insert items_videos (itemid,videoid,is_active) values ('$childid','$videoid','Y') on duplicate key update is_active = 'Y'");
			}
		}  #  end loop through children
	} #  end drop all not set
    logit('VIDEOS_ASSIGNED',"Method=bulk_by_child, childid = $childid, admin = $login");

}
#  --------------  end form processing  ----------------------

# get child information
$res = do_sql("select c.title, s.first_name, s.last_name " . 
             " from items c, sponsorships s where c.itemid = s.itemid and c.is_sponsored = 'Y' ".
			 " and s.effective_end_ts is null and c.itemid = '$childid'");
if (mysqli_num_rows($res) <> 1) {throw new Exception("Failed to find unique sponsorship information",ERROR_MAJOR);}
$child = mysqli_fetch_assoc($res);

# get list of all videos
$res = do_sql("select v.videoid, v.label, v.url,v.datedone,v.description, i.is_active from " . 
             "videos v left join items_videos i on v.videoid = i.videoid and itemid = '$childid'" ,
			 " order by v.seq,v.label");
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
<title>Manage Videos for a Child</title>
</head>
<body>
<form method="post" action="">	
<table width="90%" align="center" border = "1" cellpadding="3" cellspacing="3">
<tr bgcolor="#FFFFCC">
	<th colspan="5">Manage Videos for a Child</th>
</tr>
<tr>
	<td>Child's Name</td>
	<td colspan="4"><?php print $child['title'] ?></td>
</tr>
<tr>
	<td>Sponsor</td>
	<td colspan="4"><?php print $child['first_name']." ". $child['last_name'] ?></td>
</tr>
    <tr>
    	<th colspan="5">Assign ALL videos to this Sponsorship: <input type="checkbox" name="setall" value="1">
    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    	Drop ALL videos from this Sponsorship: <input type="checkbox" name="dropall" value="1">
    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    	<input type="submit" name="submit" value="Go!">	
    	</th>
    </tr>


<tr bgcolor="#FFFFCC">
<th>Assign</th>
<th>Label</th>
<th>Url</th>
<th>Date Publ.</th>
<th>Description</th>	
</tr>
<?php 
$videos = array();
while ($row=mysqli_fetch_assoc($res)) { 
	$video = "video_".$row['videoid'];
	array_push ($videos, $video);
	if ($row['is_active'] == 'Y') {$checked = "checked"; } else {$checked = '';}
?>
<tr>
	<td align="center"><input type="checkbox" name="<?php print $video ?>" value="<?php print $row['videoid'] ?>" <?php print $checked ?> ></td>
	<td ><?php print $row['label'] ?> </td>
	<td ><?php print $row['url'] ?> </td>
	<td align="center"><?php print $row['datedone'] ?> </td>
	<td ><?php print $row['description'] ?> </td>

<?php } 
 $all = join("|", $videos);
?>

<input type="hidden" name="videos" value="<?php print $all ?>" >
	<tr bgcolor="#FFFFCC">
<th colspan="5"><input type="submit" name="submit" value="Save Changes"></th>		
	</td>
</tr>
<tr bgcolor="#FFFFCC">
	<th colspan="5"> <a href="index.php?id=<?php print $sessionid ?>">Return to Index</a>
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
