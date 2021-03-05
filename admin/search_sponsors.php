<?php
/*
 * This requires the following view definition in the sponsorship database.
  create view cvsponsors as 
  select child_1 as child, child_id_2 as childid, ch.entity_id, cb.contact_id,  cb.receive_date, cb.total_amount, cb.fee_amount, cb.net_amount, cb.trxn_id,  
  cb.check_number, cb.contribution_recur_id, cr.frequency_unit, sort_name, display_name, hash, email, ct.first_name, ct.last_name 
  from 15983_tgc_wordpress_civicrm.civicrm_value_child_sponsorship_1 ch
  left join 15983_tgc_wordpress_civicrm.civicrm_contribution cb on ch.entity_id = cb.id
  left join 15983_tgc_wordpress_civicrm.civicrm_contribution_recur cr on cb.contribution_recur_id = cr.id
  left join 15983_tgc_wordpress_civicrm.civicrm_contact ct on ct.id = cb.contact_id
  left join 15983_tgc_wordpress_civicrm.civicrm_email em on is_primary = 1 and em.contact_id = ct.id
 *
 */
 try {
include "../lib/common-init.php";
include "compose_renewal_letter.php";

  # test if user belongs here
  $sessionid = getinput('id');
  $tmparray = require_login($sessionid);
 	if ($tmparray === false) {
		$redirect = "$webroot/admin/login.php";
#		$referrer = $_SERVER['REQUEST_URI']."?gid=$groupid";
	} else {
		$is_ok = true;
		$uid = $tmparray[0];
		$login = $tmparray[1];
	}

debug("in search_sponsors.php...");

/*
 if ($_POST['submit'] == 'Send email') {
	if ($_POST['sql'] == '') {throw new Exception("No SQL found.", ERROR_MAJOR);}
	do_sql($_POST['sql']);
	header("Location: $civicrm_base/wp-admin/admin.php?page=CiviCRM&q=civicrm/activity/email/add&action=add&reset=1&cid={$_POST['cid']}&selectedChild=activity&atype=3");

	# header("Location: $civicrm_base/activity/email/add?action=add&reset=1&cid={$_POST['cid']}&selectedChild=activity&atype=3");
}
*/
$groupid = getinput("gid", null);
if ($groupid == null) {
	throw new Exception("I don't know which group to report.",ERROR_MINOR);
}
$groupname = get_dictionary_label($groupid, 'groups', "unknown");

$csv =  ( ($redirect == '') and ( getinput("csv") == "Y" ) );


$sortby = getinput('sortby','sort_name');
$direction = getinput('dir','asc');
$sortcmd = " order by $sortby $direction ";
$uri = "search_sponsors.php?type=$group&gid=$groupid";


$sql = "select cv.* ,s.hash as c_hash from cvsponsors cv, sponsorships s, items i, ". 
       "( select childid, max(entity_id) as latest, contact_id from cvsponsors group by childid, contact_id) m " .
       " where m.latest = cv.entity_id and m.contact_id = s.contactid and s.effective_end_ts is null and i.itemid = s.itemid " . 
       " and i.groupid = $groupid and i.childid = cv.childid and i.is_active = 'Y' $sortcmd";

$res = do_sql($sql);

if ( $csv ) {
	header('Content-Type: application/csv');
	$filename = "sponsorships-".strftime('%y-%m-%d-%H%M').".csv";
	header('Content-Disposition: attachment; filename="'.$filename.'";');
	
	$f = fopen('php://output', 'w');
	#  send the column titles
	fputcsv($f, array(
		"Sponsor",
		"Email",
		"Child",
		"Last_Pmt",
		"Pmt_info",
		"Amount",
		"Recur",
		"Fee",
		"Net Amt",
		"Child ID",
		"Sponsor ID",
		));
} else {
	
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
<title>KOIKOI List Sponsorships</title>
</head>
<body>
<H2 align="center">Administrative Modules<br><?php print $type ?></H2>


<table align="center" border="1" cellpadding="3" cellspacing="3" width="95%">
<tr bgcolor="#FFFFCC">
	<th colspan="8">Current Sponsorships in group <?php print $groupname ?></th>
</tr>
<tr bgcolor="#FFFFCC"><td colspan="8" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="search_sponsors.php?csv=Y&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Download as CSV</a>
</td>
</tr>
<tr bgcolor="#FFFFCC">
<th><a href="<?php print $uri ?>&sortby=sort_name&dir=<?php print $direction?>&id=<?php print $sessionid ?>">Sponsor</a>
	<?php
	if ($sortby == 'sort_name') {
		if ($direction == 'asc') {
			print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=sort_name&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=sort_name&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
	?>
	</th>
<th><a href="<?php print $uri ?>&sortby=cv.email&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Email</a>
<?php
	if ($sortby == 'cv.email') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=cv.email&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=cv.email&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>

	
</th>

<th><a href="<?php print $uri ?>&sortby=child&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Child</a>
	<?php
	if ($sortby == 'child') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=child&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=child&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
	
</th>

<th><a href="<?php print $uri ?>&sortby=receive_date&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Last Pmt</a>
	<?php
	if ($sortby == 'receive_date') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=receive_date&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=receive_date&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>

</th>
<th>Trxn ID</th>
<th>Amt</th>
<th>Recur</th>
<th>Action</th>
</tr>
<?php 

$subject = htmlspecialchars("Please renew your child sponsorship");

# initialize counter variables.
$allsponsorships = 0;
$childids = array ();
$legacysponsorships = 0;
}  #  end this is not a csv download

while ($row = mysqli_fetch_assoc($res)) {
	$show_row = true; 
	$allsponsorships ++;
	$childids[$row['childid']] = 1;

#  Build email message for renewal reminder.
$body = compose_renewal( $groupid, $row );

#  build array of row values either for display or download

$data = array();
# row 1  sponsor name
array_push($data, $row['sort_name']);

# row 2  sponsor email address
array_push($data, $row['email']);

# row 3  child being sponsored
array_push($data, $row['child']);

# row 4  last payment received date
array_push($data, strftime('%m/%d/%y',strtotime($row['receive_date'])));

# row 5  transaction information
$tmp = "";
if ($row['trxn_id'] <> null) {
	$tmp = $row['trxn_id'];
} elseif ( ($row['check_number'] <> "")) {
	$tmp = "check no: ".$row['check_number'];
}
if ($tmp == '') { $legacysponsorships ++; }
array_push($data, $tmp);

# row 6  amount
array_push($data, $row['total_amount']);

# row 7  recurring period
if ($row['contribution_recur_id'] <> null) {
	if ($row['frequency_unit'] <> null){
		array_push($data, $row['frequency_unit']);
	} else {
		 array_push($data, "Unknown");
	}
} else {
	array_push($data,  "No");
}

# row 8 is fee
array_push($data, $row['fee_amount']);

# row 9 is net amount
array_push($data, $row['net_amount']);

# row 10 is sponsor_id
array_push($data, $row['contact_id']);

# row 11 is childid
array_push($data, $row['childid']);

if ( $csv ) {
	fputcsv($f, $data);
} else {

#debug("loaded data row\n".dump_array($data));
?>
<tr class="<?php print $style ?>">
<td><?php print $data[0] ?>&nbsp;</td>
<td>
<!-- 
	<form action="" method="post" target="_blank">
	<?php # print $data[1] ?>&nbsp;
	<input type = "submit" name="submit" value= "Send email">
	<input type = "hidden" name="sql" value="<?php print $sql ?>"> 
	<input type = "hidden" name="cid" value="<?php print $row['id'] ?>"> 
</form>
-->
	<?php  print $data[1] ?>&nbsp;
	<?php if ($row['email'] <> '') { ?>}
	(<a href="mailto:<?php print $row['email'] ?>?subject=<?php print $subject ?>&body=<?php print $body ?>">Send renewal</a> )
	<?php } ?>
</td>


<td align="center"><?php print $data[2] ?>&nbsp;</td>

<td>
	<?php print $data[3] ?>&nbsp;	
</td>

<!--   transaction column -->
 
<td><?php print $data[4]; ?></td>

<td align="center"><?php print $data[5] ?>
<td align="center"><?php print $data[6] ?></td>
 
<td align="center">
<a href="sponsorship_delete.php?childid=<?php print $row['childid'] ?>&contactid=<?php print $row['contact_id'] ?>&gid=<?php print $groupid ?>&refer=search&id=<?php print $sessionid ?>">Terminate</a>
</td>

</tr>
<?php  
} #  end this is not a csv download
} #  end looping through the data 

if ($csv) {
	fclose($f);
} else {	
?>	
<tr bgcolor="#FFFFCC"><td colspan="8" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="search_sponsors.php?csv=Y&gid=<?php print $groupid ?>&id=<?php print $sessionid ?>">Download as CSV</a>
</td>
</tr>
<tr bgcolor="#FFFFCC"><td colspan="8">
	<table width="100%" cellpadding="3" cellspacing="3">
		<tr>
			<td width="28%" align="right">Total Sponsorships:</td>
			<td width="5%"><?php print $allsponsorships ?></td>
			<td width="28%" align="right">Total Children:</td>
			<td width="5%"><?php print count($childids) ?></td> 
			<td width="28%" align="right">Legacy Sponsorships:</td>
			<td width="6%"><?php print $legacysponsorships ?></td> 
		</tr>
	</table> 
</td></tr>
</table>
<?php } ?>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>


 
