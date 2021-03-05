<?php
/*
 *   This code adds a sponsorship that exists in CiviCRM but is not present in childsponsor.
 *   Use tis when resynchronizing the two.
 */
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

if ($is_ok) {

$childid = getinput('childid');
if ($childid == '') {
	throw new Exception("I don't know which child to sponsor.",ERROR_MINOR);
}

#   process child child sponsorship
if (getinput("submit")<> '') {
	$entity_id = getinput("entity_id");
	if ($entity_id == '') {
		throw new Exception("I don't know which sponsorship to add.",ERROR_MAJOR);
	}
	
	# collect the civicrm data
	$res = do_sql("select * from cvsponsors where entity_id = '$entity_id'");
	if (mysqli_num_rows($res)<> 1) {
		throw new Exception("Failed to find information for $entity_id", ERROR_MAJOR);
	}
	$civi = mysqli_fetch_assoc($res);

    # collect the child information
	$res = do_sql("select * from items where childid = '$childid'");
	if (mysqli_num_rows($res)<> 1) {
		throw new Exception("Failed to find a unique child.",ERROR_MAJOR);
	}
	$item = mysqli_fetch_assoc($res);
        
	# Is this sponsorship already in place and active?
	$res = do_sql("select contactid from sponsorships where itemid={$item['itemid']} and contactid = '{$civi['contact_id']}'and effective_end_ts is null");
	if (mysqli_num_rows($res) > 0) {
		throw new Exception("This sponsorship already exists $entity_id", ERROR_MAJOR);
	}
	#  we are clear to proceed.
	$now = dbdate("now");
	#  create a unique hash
	$unique = false;
	while (! $unique) {
		$hash = rand(0,99999999);
		$res = do_sql("select hash from sponsorships where hash = '$hash'");
		if (mysqli_num_rows($res) == 0) {
			$res = do_sql("select hash from sponsorship_pending where hash = '$hash'");
			if (mysqli_num_rows($res) == 0) {
				$unique = true;
			}
		}
	}
	do_sql("start transaction");
	$data = array(
		'email'       => $civi['email'],
		'first_name'  => $civi['first_name'],
		'last_name'   => $civi['last_name'],
		'contactid'   => $civi['contact_id'],
		'hash'        => $hash
	);
	$result = write_sponsorships($item['itemid'], $data);
	if ($result === false) {
		do_sql("rollback");
		throw new Exception ('Unable to update sponsorship database', ERROR_MAJOR);
	}
#	$sql = "insert sponsorships (itemid,effective_start_ts,effective_end_ts,email,first_name,last_name,contactid,hash) " ;
#    $sql .= " values( '{$item['itemid']}', '$now',null,'{$civi['email']}', '{$civi['first_name']}', '{$civi['last_name']}', "; 
#	$sql .= " '{$civi['contact_id']}', '$hash')";	
#	do_sql($sql);
	# set child as sponsored
	do_sql("update items set is_sponsored = 'Y' where itemid = '{$item['itemid']}' limit 1");
	logit("Admin_Sponsorhip", "childID = {$item['itemid']}, contactID = {$civi['last_name']}, email = {$civi['email']}");
	do_sql("commit");
}  # end processing submit action

#  get child information

$res = do_sql("select * from items where childid = '$childid'");
if (mysqli_num_rows($res)<> 1) {
	throw new Exception("Failed to find a unique child.",ERROR_MAJOR);
}
$childname = mysqli_result($res,0,'title');
$groupid   = mysqli_result($res,0,'groupid');

#  get existing sponsorships
$res = do_sql("select contactid, effective_end_ts from sponsorships s, items i where s.itemid = i.itemid and i.childid = '$childid'");
$existing = array();
while ( $row = mysqli_fetch_assoc($res) ) {
	if ($row['effective_end_ts'] == null) {
		$tmp = "Active";
	} else {
		list($tmp,$junk) = explode(" ",us_date($row['effective_end_ts']));
	}
	$existing[$row['contactid']] = $tmp;
}
#  get potential sponsorships
$res = do_sql("select * from cvsponsors where entity_id in (select max(entity_id) from cvsponsors where childid = '$childid' group by contact_id)");
if (mysqli_num_rows($res) == 0) {
	throw new Exception("No sponsorships found for $childid", ERROR_MINOR);
}


}  #  end user belongs here.
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
<H2 align="center">Administrative Modules<br />Add a Sponsorship from CiviCRM</H2>

<Table width="80%" border="1" cellpadding="3" cellspacing="3" align="center">
<tr bgcolor="#FFFFBB"><td colspan="6">Child is <?php print "$childname ($childid)" ?></td></tr>
<tr bgcolor="#FFFFBB">
	<th>Contact ID</th>
	<th>Name</th>
	<th>Email</th>
	<th>Last Paymt</th>
	<th>Status</th>
	<th>Action</th>
</tr>
<?php
while ( $row = mysqli_fetch_assoc($res) ) {
	print "<tr>";
    print "<td align='center'>".$row['contact_id']. "</td>";
	print "<td>".$row['display_name']. "</td>";
	print "<td>".$row['email']. "</td>";
	print "<td align='center'>".us_date($row['receive_date']). "</td>";
	if ( in_array( $row['contact_id'], array_keys($existing) ) ) {
		print "<td align='center'>".$existing[$row['contact_id']]. "</td>";
	} else {
		print "<td align='center'>Free</td>";
	}
	print "<form method='post' action=''>";
	print "<input type='hidden' name='entity_id' value='{$row['entity_id']}'>";
	print "<td align='center'><input type='submit' name='submit', value='Sponsor'></td>";
	print "</form>";
	print "</td></tr>\n";	
}
?>

<tr bgcolor="#FFFFBB">
<td align="center" colspan="6">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Index</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
<a href="child_list.php?gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Return to Child List</a>	
</td>
</tr>
</Table>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>

