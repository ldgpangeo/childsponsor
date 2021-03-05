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

debug ("delete sponsorship beginning with\n".dump_array($_REQUEST));

$groupid = getinput('gid', 1);
$hash = getinput('hash', null);
$contactid = getinput('contactid');
$childid = getinput('childid');
if ( ($contactid == null) or ($childid == null) ) {
	throw new Exception("I don't know which sponsorship to delete | child is $childid and contact is $contactid",ERROR_MAJOR);
}

#  collect the child information
$res = do_sql("select * from items where childid = '$childid'");
if (mysqli_num_rows($res) <> 1) {
	throw new Exception("Unable to retrieve unique child information.",ERROR_MAJOR);
}
$childinfo = mysqli_fetch_assoc($res);
$itemid = $childinfo['itemid'];
debug("Child info is \n".dump_array($childinfo));

#  Use hash if present.
if ($hash <> null) {
    $sql = "select * from sponsorships where id in ( ".
        "select max(id) as id from sponsorships where itemid = '$itemid' and contactid = '$contactid' and hash = '$hash' and effective_end_ts is null)";
} else {
    $sql = "select * from sponsorships where id in ( ".
			"select max(id) as id from sponsorships where itemid = '$itemid' and contactid = '$contactid' and effective_end_ts is null)";
}
$res = do_sql($sql);
if (mysqli_num_rows($res) == 1) {
	$sponsorinfo = mysqli_fetch_assoc($res);
	debug("Sponsorship info is \n".dump_array($sponsorinfo));
	$id = $sponsorinfo['id'];
} else {
	throw new Exception ("Can not find sponsorship record|  child is $itemid and contact is $contactid",ERROR_MAJOR);
}



#   determine where to return the user when finished.
$groupid = getinput("gid", 1);
#   collect the group infomation
$res = do_sql("select * from groups where groupid = '$groupid'");
if (mysqli_num_rows($res) <> 1) {
	throw new Exception("Unable to find the group information", ERROR_MAJOR );
}
$groupinfo = mysqli_fetch_assoc($res);

if ( strpos(getinput("refer"),"search") === "false") {
	$refer = "child_list.php?gid=$groupid&id=$sessionid";
} else {
	$refer = "search_sponsors.php?gid=$groupid&id=$sessionid";
}

#  require confirmation
if (isset($_GET['confirm'])) {
	$confirm = $_GET['confirm'];
	$is_sponsored = $childinfo['is_sponsored'];
	do_sql("start transaction");
	#    first clear the sponsorship in either pending or final table
	if ($is_sponsored == 'P') {
		do_sql("delete from sponsorship_pending where itemid = '$itemid' and hash = '$hash'");
		logit ("Pending_Sponsorship_deleted","child_id = $childid, admin=$login");
	}
	if ($is_sponsored == 'Y') {
	    $now = strftime('%Y-%m-%d %T');
		do_sql("update sponsorships set effective_end_ts = '$now' where id = '$id'");
		logit ("Sponsorship_deleted","child_id = $childid, admin=$login");
			}
	if ($confirm == 'Y') {
		#  clear any sponsorship video
		$sql= "update items set video_url = null where itemid = '$itemid'";
		do_sql($sql);
		#  clear any supplementary videos
		$sql = "update items_videos set is_active = 'N' where itemid = '$itemid'";
		do_sql($sql);
	}
	#  reset is_sponsored for the child.
	#  Rules are...
	#     1.  if max sponsorships is 1, set this to no
	#     2.  if multipe sponsorshops allowed
	#              set to N if no remaining sponsorships.
	#              set to S if remaining < max
	#              set to Y if remaining >= max
	
	# get number of remaining sponsorships.
	$res = do_sql("select count(*) as remaining from sponsorships where effective_end_ts is null and itemid = '$itemid'");
	$remaining = mysqli_fetch_assoc($res);
	
	if ( ($groupinfo['max_sponsors'] == 1) or ($remaining == 0) ) {
		do_sql("update items set is_sponsored = 'N' where itemid = '$itemid'");
	} elseif ($remaining < $groupinfo['max_sponsors']) {
		do_sql("update items set is_sponsored = 'N' where itemid = '$itemid'");		
	}
	do_sql("commit");
	header("Location: $refer");
} else {
	?>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
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
<link href="style.css" rel="stylesheet" type="text/css">
<title>Admin:  Delete sponsorship</title>
</head>
<body>
<table align="center" border="1" width="50%" cellpadding="5" cellspacing="5">
<tr bgcolor="#FFCCCC">
<th colspan="3">You are about to delete this sponsorhip.<br>
	Sponsor <?php print $sponsorinfo['first_name'] ." ". $sponsorinfo['last_name']?> of child <?php print $childinfo['title'] ?><br> 
	Are you sure?</th>
</tr>
<tr>
<td width="33%" align="center">
<a href="sponsorship_delete.php?childid=<?php print $childid ?>&contactid=<?php print $contactid ?>&hash=<?php print $hash ?>&gid=<?php print $groupid ?>&confirm=D&id=<?php print $sessionid ?>&refer=<?php print $refer ?>">Yes, but preserve videos.</a>
</td>
<td width="34%" align="center">
<a href="sponsorship_delete.php?childid=<?php print $childid ?>&contactid=<?php print $contactid ?>&hash=<?php print $hash ?>&gid=<?php print $groupid ?>&confirm=Y&id=<?php print $sessionid ?>&refer=<?php print $refer ?>">Yes, and remove videos.</a>
</td>
<td width="33%" align="center">
<a href="index.php?id=<?php print $sessionid ?>">No, cancel this.</a>
</td>
</tr>
</table>
</body>	
</html>	
	
<?php } 
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}


?>