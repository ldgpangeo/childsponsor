<?php
try {
include "../lib/common-init.php";

die ("This page not updated for multiple sponsorships.");

  # test if user belongs here
  $sessionid = getinput('id');
  $tmparray = require_login($sessionid);
 	if ($tmparray === false) {
		$redirect = "$webroot/admin/login.php";
		$referrer = $_SERVER['REQUEST_URI'];
		debug("Referrer is {$_SERVER['REQUEST_URI']}");
	} else {
		$is_ok = true;
		$uid = $tmparray[0];
		$login = $tmparray[1];
	}
	
$errmsg = '';

$groupid = getinput('gid', 1);

$childid = getinput('childid', null);
if ($childid == null) {
	throw new Exception("I don't now which child to update.",ERROR_MAJOR);
}
	$res = do_sql("select is_sponsored,title from items where itemid = '$childid'");
	$is_sponsored = mysqli_result($res,0,'is_sponsored');
	$child_name   = mysqli_result($res,0,'title');

	switch ($is_sponsored) {
		case 'N':
			$is_update = false;
			break;
		case 'P' :
			$is_update = TRUE;
			$is_pending = TRUE;
			break;
		case 'Y': 
			$is_update = TRUE;
			$is_pending = FALSE;
	}

$sponsorid = getinput('sponsorid',null);
if (($sponsorid == null) and $is_update ) {
	if ($is_sponsored == 'Y') {
		$new_table_format = true;
	} else {
		$new_table_format = false;
		$errmsg = "Sponsor information is missing.";
	}
}

# --------------------------------------------  Process Form output  -----------------------------------------
if ($_POST['submit'] <> '') {
	$errmsg = '';
	$data = form_validate('sponsorship_edit', $errmsg);
	if ($errmsg <> '') {
		throw new Exception($errmsg, ERROR_MINOR);
	}
	debug("data array is \n".dump_array($data));
	# save the data
	do_sql("start transaction");
	#  if status has changed, update the child
	if ($data['is_sponsored'] <> $data['is_sponsored_old']) {
		do_sql("update items set is_sponsored = '{$data['is_sponsored']}' where itemid = '$childid'");
		logit("Admin_Sponsor_Status_Changed",
			  "Name = $child_name, childid=$childid, was = {$data['is_sponsored_old']}, now = {$data['is_sponsored']}",
			  false );
	}
	#  if locked has changed, update the child
	if ($data['is_locked'] <> $data['is_locked_old']) {
		do_sql("update items set is_locked = '{$data['is_locked']}' where itemid = '$childid'");
		logit("Admin_Sponsor_Lock_Changed",
			  "Name = $child_name, childid=$childid, was = {$data['is_locked_old']}, now = {$data['is_locked']}",
			   false );
	}
		switch ($data['is_sponsored']) {
		case 'N' :
			# if not sponsored, there's nothing to do.
			#  not correct...   need to
			#    if pending, delete from pending table
			if ($data['is_sponsored_old'] == 'P') {
				$sql = "delete from sponsorship_pending where itemid = '$childid'";
				do_sql($sql);
				logit("Admin_Pending_Sponsor_deleted",
				      "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}, admin=$login",
				      false );
			}
			#    if sponsored, end-date the sponsorship.
			if ($data['is_sponsored_old'] == 'S') {
			    $now = strftime('%Y-%m-%d %T');
				$sql = "update sponsorships set effective_end_ts = '$now' where itemid = '$childid' and effective_end_ts is null";
				do_sql($sql);
				logit("Admin_Sponsor_deleted",
				      "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}, admin=$login",
				      false );
			}
				
			break;
		case 'P' :
			#  write to the sponsorship pending table.
			$fields = array('email','first_name','middle_initial','last_name','appear','appear_text');
			$fieldstr = join(", ",$fields);
			debug ("in pending, old sponsorship is {$data['is_sponsored_old']}");
			if (($is_update) and ($data['is_sponsored'] == $data['is_sponsored_old'])) {
				#  update an exiting pending record
			    $now = strftime('%Y-%m-%d %T');
			    $sql = "update sponsorship_pending set datedone = '$now' ";
				foreach ($fields as $field) {
					if ($data[$field] == null) {
						$sql .= ", $field = null";
					} else {
						$sql .= ", $field = '". $data[$field] . "'";
					}
				}
				$sql .= " where itemid = '$childid' ";
				do_sql($sql);
				#  log the change
				logit("Admin_Pending_Sponsor_Updated",
					  "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}, admin=$login",
					  false );					
			} else {
				# test if a pending sponsorship already exists
				$res = do_sql("select * from sponsorship_pending where itemid = '$childid'");
				if (mysqli_num_rows($res) >0) {
					throw new Exception("A pending sponsorship already exists for this child.  You must clear it before creating a new one", ERROR_MINOR);
				}
				$now = strftime('%Y-%m-%d %T');
				$sql = "insert into sponsorship_pending (datedone, itemid, $fieldstr) values ('$now', $childid ";
				foreach ($fields as $field) {
					if ($data[$field] == null) {
						$sql .= ', null';
					} else {
						$sql .= ", '". $data[$field] . "'";
					}
				}
				$sql .= ") ";
				do_sql($sql);
				$sponsorid = mysqli_insert_id($hdl);
				#  log the change
				logit( "Admin_Pending_Sponsor_Added",
					   "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}, admin=$login",
					    false );	
			}		
			break;
		case 'Y' :
			# start transaction
			do_sql("start transaction");
			
			# Clear any pending entry
			do_sql("delete from sponsorship_pending where itemid = '$childid'");
			
			# write to the items table
			if ($is_update) {
				#  we are updating a sponsorship
				write_sponsorships($childid, $data);
				#  log the change
				logit ("Admin_Sponsor_Updated",
					   "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}",
					   false );	
			} else {
				write_sponsorships($childid, $data);
				#  log the change
				logit ("Admin_Sponsor_Added",
					   "Name = $child_name, childid = $childid, sponsor = {$data['first_name']} {$data['last_name']}",
					   false );	
			}
			break;
	}
	do_sql("commit");
	header("Location: child_edit.php?childid={$childid}&sponsorid={$sponsorid}&gid={$groupid}&id={$sessionid}");

}
#  -------------------------------------------- End form processing -------------------------------------------

if (! $is_update) {
	$row = array ("is_locked" => 'N');
} else {
	if ($is_sponsored == 'Y') {
		$sql = "select *, 'N' as is_locked, date_add(start_date, interval 1 year) as end_date ".
			  " from sponsorships where itemid = '$childid' and effective_end_ts is null";
	} else {
		$sql = "select * from sponsorship_pending where itemid = '$childid'";
	}
	$res = do_sql($sql);
	if (mysqli_num_rows($res) == 1) {
		$row= mysqli_fetch_assoc($res);
	} else {
		throw new Exception("Sponsorhip data not found.| $sql", ERROR_MAJOR_NORETURN);
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
<title>KOIKOI Edit Sponsorship info</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Edit Sponsorship Info for <?php print $child_name ?></H2>
<form action="" method="post" >
<table align="center" border="1" cellpadding="3" cellspacing="3" width="80%">
<tr bgcolor="#FFFFCC">
</tr>
<tr>
	<td>Sponsorship Status:</td>
	<td><?php print dictionary_as_radio('is_sponsored','sponsor_state',$is_sponsored)  ?> 
	<input type="hidden" name="is_sponsored_old" value="<?php print $is_sponsored ?>">		
	</td>
</tr>
<tr>
	<td>Lock Pending Status:</td>
	<td><?php print dictionary_as_radio('is_locked','yesno',$row['is_locked'])  ?>
	<input type="hidden" name="is_locked_old" value="<?php print $row['is_locked'] ?>">		
	</td>
	
</tr>
<tr>
<td>Sponsor Name:</td>
<td>
First:<input type="text" name="first_name" size="15" maxlength="60" value="<?php print stripslashes($row['first_name']) ?>"/>
MI:<input type="text" name="middle_initial" size="2" maxlength="1" value="<?php print stripslashes($row['middle_initial']) ?>"/>
Last:<input type="text" name="last_name" size="15" maxlength="60" value="<?php print stripslashes($row['last_name']) ?>"/>
</td>
</tr>
<tr><td>Sponsor Email:
</td>
<td><input type="text" name="email" size="50" maxlength="80" value="<?php print $row['email'] ?>" />
</td></tr>
<tr><td colspan="2">
Sponsor's name appears on the web site?
<?php if ((! isset($row['appear'])) or ($row['appear'] == 'Y')) { ?>
&nbsp;&nbsp;<input type="radio" name="appear" value="Y" checked />Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="appear" value="N" />No
<?php } else { ?>
&nbsp;&nbsp;<input type="radio" name="appear" value="Y"  />Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="appear" value="N" checked/>No
<?php } ?>
<br />
Custom Sponsorship Message:
<input type="text" name="appear_text" size="40" maxlength="80" value="<?php print stripslashes($row['appear_text']) ?>"/>
</td></tr>

<tr bgcolor="#FFFFCC">
<td align="center" colspan="2">
<input type = "hidden" name="id" value="<?php print $sessionid ?>">
<input type = "hidden" name="sponsorid" value="<?php print $row['sponsorid'] ?>">
<input type = "hidden" name="itemid" value="<?php print $row['itemid'] ?>">
<input type = "hidden" name="gid" value="<?php print $groupid ?>">
<input type="submit" name="submit" value="Save Changes">
</td>
</tr>
</table>
</form>
<p align="center">
<a href="child_edit.php?childid=<?php print $childid ?>&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Return to Child Editor</a>
</p>
<?php

} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>


 