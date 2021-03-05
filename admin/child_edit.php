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

debug ("Starting child edit with params\n".dump_array($_REQUEST));
	
$childid = getinput('childid',null);
if ($childid == null) {
	throw new exception("I don't know which child to edit.", ERROR_MAJOR);
}

#  is this an insert or an update?
$mode = getinput('mode','update');

if (($mode <> 'insert') and ($childid <> '-1') ) {
$res = do_sql("select * from items where childid = '$childid'");
	if (mysqli_num_rows($res) <> 1) {
		throw new Exception("Failed to find unique child information.",ERROR_MAJOR);
	}
	$childinfo = mysqli_fetch_assoc($res);
	$itemid = $childinfo['itemid'];
} 

$groupid = getinput("gid", 1);

# --------------------------------------------  Process Form output  -----------------------------------------
if ($_POST['submit'] <> '') {
	$errmsg = '';
	#   this needs to incorporate the new forms table
	$title = clean(trim($_POST['title']));
	if ($title == '') {$errmsg .= "You must provide a title.<br>";}
	$tmp = clean(trim($_POST['dob']));
	if (($tmp == '') or ($tmp == '0000-00-00') ) { $tmp = '2000-01-02'; }
        $dob = validate_datetime($tmp, '1993-01-01','2020-12-31','%Y-%m-%d');
        if (($dob === false) or ($dob == "0000-00-00") ) {$errmsg  .= "Date of birth is not valid.<br>"; }
	$mygroupid = clean(trim($_POST['groupid']));
	if ($mygroupid == '') {$errmsg .= "You must provide a sponsorship group.<br>";}
	$mychildid = clean(trim($_POST['childid']));
	$summary = clean(trim($_POST['summary']));
	$is_active = clean(trim($_POST['is_active']));
	$seq = clean(trim($_POST['seq']));
	$body_text = clean(trim($_POST['body_text']));
	$mode = clean(trim($_POST['mode']));
	$video_url = clean(trim($_POST['video_url']));
    $monthly = clean(trim($_POST['monthly']));
    $yearly = clean(trim($_POST['yearly']));
    $max_sponsors = clean(trim($_POST['max_sponsors']));
    if ($max_sponsors == '') { $max_sponsors = 1;}
    if ($max_sponsors > 2) {$errmsg .= "A child can not have more than 2 sponsors.<br>"; }
    if ($max_sponsors < 1) {$errmsg .= "A child can not have less than 1 sponsor.<br>"; }
    $first_monthly = clean(trim($_POST['first_monthly']));
    if ( ($first_monthly < 0) or ($first_monthly > $monthly) ) {$errmsg .= "Partial monthly value is incorrect."; }
    $first_yearly = clean(trim($_POST['first_yearly']));
    if ( ($first_yearly < 0) or ($first_yearly > $yearly) ) {$errmsg .= "Partial yearly value is incorrect."; }
    
    if ($errmsg == "") {
	if  ($mode == "insert") {
		do_sql("start transaction");
		$sql = "insert items (title, summary, is_active, seq,body_text,dob,is_sponsored,groupid,childid,monthly,yearly,max_sponsors,first_monthly,first_yearly  ) " .
		       " values ('$title','$summary','$is_active','$seq','$body_text','$dob','N','$mygroupid','$mychildid'";
		if ($monthly > 0) {
			$sql .= ",'$monthly' ";
		} else {
			$sql .= ", null ";
		}      
		if ($yearly > 0) {
			$sql .= ",'$yearly' ";
		} else {
			$sql .= ", null ";
		}      
		$sql .= ",'$max_sponsors'";
		if ($first_monthly > 0) {
		    $sql .= ",'$first_monthly' ";
		} else {
		    $sql .= ", null ";
		}
		if ($first_yearly > 0) {
		    $sql .= ",'$first_yearly' ";
		} else {
		    $sql .= ", null ";
		}
		
		$sql .=" );";
		$res = do_sql($sql);
		if ($res === false) {
			$errmsg .= "Database save failed.";
		} else {
			$itemid = mysqli_insert_id($hdl);
			if ($itemid == '') {
				$errmsg .= "got null item id.";
			} else {
				if ($mychildid == '') {
					$res = do_sql("update items set childid = itemid where itemid = '$itemid'");
					if ($res === false) { $errmsg .= "Unable to set the child ID"; }
				} else {
					$res = do_sql("update items set childid = '$mychildid' where itemid = '$itemid'");
					if ($res === false) { $errmsg .= "Unable to set the child ID"; }
				}
			} 
			if (isset($_FILES['newfile']['tmp_name']) ) {
				$dest_file = sprintf('%03s',$itemid). ".jpg";
				$dest = '../dbimg/'. $dest_file;
				debug ("Saving image to $dest from {$_FILES['newfile']['tmp_name']}");
				if (! move_uploaded_file($_FILES['newfile']['tmp_name'],$dest)) {
					$errmsg .= "File upload has failed.";
				} else {
					$sql = "update items set image = '$dest_file' where itemid = '$itemid'";
					debug ($sql);
					$res = do_sql($sql);
					if ($res === false) {
						$errmsg .= " unable to update new row with image information.";
					}
				}
			}
		}
		if ($errmsg == '') {
			do_sql("commit");
				debug("Admin is $login");
			logit("Child Added","Name = $title, Child ID = $childid, Admin = {$login} ");
		} else {
			do_sql("rollback");
		}
	} else {
		$sql = "update items set title= '$title', summary = '$summary', is_active = '$is_active',seq = '$seq',
	           body_text = '$body_text', video_url = '$video_url', dob = '$dob' ";
	    if ($monthly > 0) {$sql .= ", monthly = '$monthly' ";} else {$sql .= ", monthly = null ";}       
	    if ($yearly > 0) {$sql .= ", yearly = '$yearly' ";} else {$sql .= ", yearly = null ";}
	    $sql .= ", max_sponsors = '$max_sponsors'";
	    if ($first_monthly > 0) {$sql .= ", first_monthly = '$first_monthly' ";} else {$sql .= ", first_monthly = null ";}
	    if ($first_yearly > 0) {$sql .= ", first_yearly = '$first_yearly' ";} else {$sql .= ", first_yearly = null ";}
	    
	    $sql .= " where itemid = '$itemid'";
            if ($errmsg == "" ) {   
		$res = do_sql($sql);
		if ($res === false) {
			$errmsg .= "Database save failed."; 
		} else {
			logit("Child Updated","Name = $title, Child ID = $childid, Admin = $login ");
		}
            }
	}
}  # end error message is null
}
#  -------------------------------------------- End form processing -------------------------------------------

if ($errmsg == '') {
	if ($childid <> "-1") {
		$mode = "update";
		$sql = "select * from items where childid = '$childid'";
		$res = do_sql($sql);
		if (mysqli_num_rows($res) == 1) {
			$row= mysqli_fetch_assoc($res);
		} else {
			$errmsg = "Child not found.";
		}
	} else {
		$mode = "insert";
		$row = array(
			"is_active" => "Y",
			"groupid" => $groupid,
			);

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
<title>KOIKOI Edit child info</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Create or Edit Child Info</H2>
<?php
if ($errmsg <> '') {
	print "<h2 align=\"center\">$errmsg</h2>\n";
} else {
?>
<form action="" method="POST" enctype="multipart/form-data">
<table align="center" border="1" cellpadding="3" cellspacing="3" width="80%">
<tr bgcolor="#FFFFCC">
<th colspan="2">
<input type="hidden" name="MAX_FILE_SIZE" value="200000" />
<input type="hidden" name="mode" value="<?php print $mode ?>">
<?php
if ($mode == "insert") {
	print "Defining a new Child.\n";
} else {
	print "Editing Child:  {$row['title']} (id: $childid)\n";
}
?>
</th>
</tr>
<tr>
<td>Title:</td>
<td><input type="text" name="title" size="50" maxlength="80" value="<?php print $row['title'] ?>">
</td>
</tr>
<tr>
<td>DOB (YYYY-MM-DD):</td>
<td><input type="text" name="dob" size="50" maxlength="80" value="<?php print $row['dob'] ?>">
</td>
</tr>
<tr>
<td>Sponsorship Group:</td>
<td><?php dictionary_as_select("groupid","groups", $row['groupid'] ) ?>
</td>
</tr>
<?php if ($mode == "insert") {?>
	<tr>
<td>Child ID:</td>
<td><input type="text" name="childid" size="50" maxlength="16"> (Leave blank to set it automatically)
</td>
</tr>

<?php } ?>

<tr>
	<td>Monthly Sponsorship Amount:</td>
	<td><input type = "text" name="monthly" size="10" maxlength="4" value="<?php print $row['monthly'] ?>">
		(Leave blank for default amount)
	</td>
</tr>
</tr>
<tr>
	<td>Yearly Sponsorship Amount:</td>
	<td><input type = "text" name="yearly" size="10" maxlength="4" value="<?php print $row['yearly'] ?>">
		(Leave blank for default amount)
	</td>
</tr>
<tr>
	<td>Maximum Number of Sponsors:</td>
	<td><input type = "text" name="max_sponsors" size="5" maxlength="2" value="<?php print $row['max_sponsors'] ?>">
		(Leave blank for default value of 1)
	</td>
</tr>
<tr>
	<td>When multiple, first sponsor price:</td>
	<td>Monthly: <input type = "text" name="first_monthly" size="5" maxlength="5" value="<?php print $row['first_monthly'] ?>">
		&nbsp;&nbsp;&nbsp;&nbsp; Yearly: 
		<input type = "text" name="first_yearly" size="5" maxlength="5" value="<?php print $row['first_yearly'] ?>">
	</td>
</tr>
<tr>
<td>Page Summary:</td>
<td><input type="text" name="summary" size="50" maxlength="80" value="<?php print $row['summary'] ?>">
</td>
</tr>
<tr>
<td>Status:</td>
<td><input type = "radio" name="is_active" value="Y" <?php if ($row['is_active'] <> 'N') { print "checked"; } ?>>Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type = "radio" name="is_active" value="N" <?php if ($row['is_active'] == 'N') { print "checked"; } ?>>No
</td>
</tr>
<tr>
<td>Publicly Visible:</td>
<td><input type = "radio" name="is_public" value="Y" <?php if ($row['is_public'] <> 'N') { print "checked"; } ?>>Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type = "radio" name="is_public" value="N" <?php if ($row['is_public'] == 'N') { print "checked"; } ?>>No
</td>
</tr>
<tr>
<td>Presentation Sequence:</td>
<td><input type="text" name="seq" size="6" maxlength="3" value="<?php print $row['seq'] ?>"></td>
</tr>
<?php if ($mode == "insert") { ?>
<tr>
<td>Image file:</td>
<td><input name="newfile" type="file" /></td>
</tr>
<?php } ?>
<tr>
<td colspan="2">Narrative
<textarea name="body_text" id="body_text" rows="10" cols="72"><?php print $row['body_text'] ?></textarea>
               <script type="text/javascript">
               CKEDITOR.replace( 'body_text', {toolbar :
               [
               [ 'Cut','Copy','Paste','SpellChecker','-','Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','Format','Styles','-','Source' ],
               [ 'UIColor' ]
               ]
               });
				</script>

</td>
</tr>
<?php
#  Load sponsorship information
$sponsors = array ();
$appear = false;
$row['is_sponsored'] = 'N';
$sql = "select s.*, datedone as start_date from sponsorship_pending s
    	        where itemid = '$itemid'";
$res2 = do_sql($sql);
if (mysqli_num_rows($res2) > 0) {
    while (	$sponsor = mysqli_fetch_assoc($res2) ) {
        array_push($sponsors, $sponsor);
    }
    $appear = true;
    $row['is_sponsored'] = 'Y';    
}
$sql = "select s.*,  date_add(start_date, interval 1 year) as end_date from sponsorships s
	            where itemid = '$itemid' and effective_end_ts is null ";
$res2 = do_sql($sql);
if (mysqli_num_rows($res2) > 0) {
    while (	$sponsor = mysqli_fetch_assoc($res2) ) {
        array_push($sponsors, $sponsor);
    }
    $appear = true;
    $row['is_sponsored'] = 'Y';
}


/*
switch ($row['is_sponsored']) {
	case 'N' :
		$sponsor = array('appear'=>'Y');
		break;
	case 'P' :
		$sql = "select s.*, datedone as start_date from sponsorship_pending s
    	        where itemid = '$itemid'";
		$res2 = do_sql($sql);
		if (mysqli_num_rows($res2) > 0) {
			$sponsor = mysqli_fetch_assoc($res2);
		} else {
			$sponsor = array('appear'=>'Y');
		}
		break;
	case 'Y' :
		$sql = "select s.*,  date_add(start_date, interval 1 year) as end_date from sponsorships s
	            where itemid = '$itemid' and effective_end_ts is null";
		$res2 = do_sql($sql);
		if (mysqli_num_rows($res2) > 0) {
		    $sponsors = array ();
		    while (	$sponsor = mysqli_fetch_assoc($res2) ) {
		        array_push($sponsors, $sponsor);
		    }
		} else {
			throw new Exception ("sponsor data not found.", ERROR_MINOR);
			$sponsor = array('appear'=>'Y');
		}
	    break;
}
*/
?>
<tr>
<td colspan="2">Sponsorship:
	<br>
<table width="100%" border="1" cellpadding="3" cellspacing="3">
<tr>
<th>Action</th>
<th>Sponsor</th>
<th>Name Appears</th>
<th>Appearance Text</th>
</tr>
	<?php 
	foreach($sponsors as $sponsor) {
#   	if ($row['is_sponsored'] <> 'N') {
    ?>
	<tr>
    <td>
	<a href="sponsorship_delete.php?contactid=<?php print $sponsor['contactid'] ?>&childid=<?php print $childid ?>&hash=<?php print $sponsor['hash'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Delete</a>
	&nbsp;&nbsp;&nbsp;&nbsp;
<a href="sponsorship_edit.php?sponsorid=<?php print $sponsor['id'] ?>&childid=<?php print $childid ?>&hash=<?php print $sponsor['hash'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Edit</a>
<?php #	} else { ?>
<!-- 
<a href="sponsorship_edit.php?sponsorid=<?php print $sponsor['id'] ?>&childid=<?php print $childid ?>&hash=<?php print $sponsor['hash'] ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Add Sponsor</a>
 --><?php  #} ?>	
</td>
<?php $tmp = stripslashes("{$sponsor['first_name']}  {$sponsor['middle_initial']} {$sponsor['last_name']}"); ?>
<td><?php print "$tmp" ?></td>
<td align="center">&nbsp;<?php  if ($sponsor['appear'] <> '') { print $sponsor['appear'];} else {print "&nbsp;"; } ?></td>
<td><?php print stripslashes($sponsor['appear_text']) ?></td>
</tr>
<?php } #  end loop through sponsors?>
<!-- 
<tr>
<td>Sponsor Video URL:</td>
<td colspan="4">
<input type="text" name="video_url" value="<?php print $row['video_url'] ?>" size="60" maxlength="255">
</td>

</tr>
<tr>
<td colspan="5">
Supporting Videos:<br>
<?php
#  add supporting videos
# $res3 = do_sql("select v.*,i.is_active from videos v, items_videos i where v.videoid = i.videoid and itemid = '$itemid' order by i.is_active, seq");
# if (mysqli_num_rows($res3) > 0) {
	?>
	<table width="100%" border="1" cellpadding="3" cellspacing="3">
	<tr>
	<th>Action</th>
	<th>Label</th>
	<th>URL</th>
	<th>Seq</th>
	<th>Active</th>
	</tr>
	<?php
#	while ($row = mysqli_fetch_assoc($res3)) {
#		print "<tr><td><a href=\"video_edit.php?childid=$childid&videoid={$row['videoid']}&id={$sessionid}\">Edit</a></td>\n";
#		print "<td>{$row['label']}</td>\n";
#		print "<td>{$row['url']}</td>\n";
#		print "<td>{$row['seq']}</td>\n";
#		if ($row['is_active'] == 'Y') {
#			print "<td>{$row['is_active']}</td>\n";
#		} else {
#			print "<td class = \"stale\">{$row['is_active']}</td>\n";			
#		}
#		print "</tr>";	
#	}
#	print "</table><br>";
#}
#	print "<a href=\"video_edit.php?childid=$childid&videoid=-1&id={$sessionid}\">Add New Video</a>\n";
?>
</td>
</tr>
 -->
</table>
</td>
</tr>
<tr bgcolor="#FFFFCC">
<td align="center" colspan="2">
<input type="submit" name="submit" value="Save Changes">
<input type="hidden" name="mode" value = "<?php print $mode ?>">
</td>
</tr>
</table>
</form>
<?php } #  end no error is present.?>
<p align="center">
<a href="child_list.php?gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Return to list of children</a>
</p>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>

 
