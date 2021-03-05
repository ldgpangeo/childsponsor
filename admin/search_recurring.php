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


if ($_POST['submit'] == 'Send email') {
	if ($_POST['sql'] == '') {throw new Exception("No SQL found.", ERROR_MAJOR);}
	do_sql($_POST['sql']);
	header("Location: $civicrm_base/activity/email/add?action=add&reset=1&cid={$_POST['cid']}&selectedChild=activity&atype=3");
}

#  view  all or only TGC or AOET sponsorships
$group = getinput("type",null);

$sortby = getinput('sortby','sort_name');
$direction = getinput('dir','asc');
$sortcmd = " order by $sortby $direction";
$uri = "search_recurring.php?type=$group";

$renewal = true;
$type = "Recurring Payments";

#  first get the internal field names for the civicrm custom fields.
#  child_id
$res = do_sql("select id from $cv.civicrm_custom_field where name = 'child_id'");
if (mysqli_num_rows($res) <> 1) {throw new Exception("Unable to find custom field child_id",ERROR_MAJOR);}
$row = mysqli_fetch_assoc($res);
$childid_tmp = "child_id_".$row['id'];
#  child
$res = do_sql("select id from $cv.civicrm_custom_field where name = 'Child'");
if (mysqli_num_rows($res) <> 1) {throw new Exception("Unable to find custom field Child",ERROR_MAJOR);}
$row = mysqli_fetch_assoc($res);
$child_tmp = "Child_".$row['id'];

$sql = "select i.id, i.hash, i.first_name, i.last_name, i.display_name as sponsor, e.email, c.receive_date as date,
c.total_amount as amount,c.Child_1 as child,  c.child_id_2 as itemid, datediff(now(), c.receive_date) as days, 
r.modified_date as latest_payment, r.frequency_unit, r.frequency_interval,   datediff(now(), 
r.modified_date) as last_pay_duration, k.is_sponsored, r.processor_id, r.id as recur_id, c.id as contrib_id 
from cv_contribution c
left join items k on k.itemid = c.child_id_2 , 
$cv.civicrm_contribution_recur r,
$cv.civicrm_contact i ,
$cv.civicrm_email e
where r.id = c.contribution_recur_id
and  i.id = c.contact_id
and e.contact_id = i.id 
and e.is_primary = 1 
and c.id in (select max(entity_id) from $cv.civicrm_value_child_sponsorship_1, $cv.civicrm_contribution  ".
	   "where $cv.civicrm_contribution.id = entity_id " .
	   "and $child_tmp is not null and $childid_tmp is not null group by $childid_tmp, contact_id) ".$fragment . $sortcmd ;

/*
 * Fragment for eventually adding recurring expiration info
 * case r.installments when null then '(unknown)'
      when 0 then '(unknown)'
      else date_format(c.receive_date + interval r.installments month,'(%m/%Y)') end as end_date
 */

$res = do_sql($sql);

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
<title>KOIKOI Recurring Sponsorships</title>
</head>
<body>
<H2 align="center">Administrative Modules<br><?php print $type ?></H2>


<table align="center" border="1" cellpadding="3" cellspacing="3" width="95%">
<tr bgcolor="#FFFFCC">
<th><a href="<?php print $uri ?>&sortby=sort_name&dir=<?php print $direction?>&id=<?php print $sessionid ?>">Sponsor</a>
	<?php
	if ($sortby == 'sort_name') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=sort_name&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=sort_name&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
	?>
</th>
<th><a href="<?php print $uri ?>&sortby=email&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Email</a>
<?php
	if ($sortby == 'email') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=email&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=email&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
</th>
<th><a href="<?php print $uri ?>&sortby=receive_date&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Sponsorship Date<br>(Days old)</a>
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
<th>Pay Interval</th>
<th><a href="<?php print $uri ?>&sortby=last_pay_duration&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Latest<br>(Days old)</a>
	<?php
	if ($sortby == 'last_pay_duration') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=last_pay_duration&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=last_pay_duration&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
</th>
<th><a href="<?php print $uri ?>&sortby=itemid&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Type</a>
	<?php
	if ($sortby == 'itemid') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=itemid&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=itemid&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
</th>
<th><a href="<?php print $uri ?>&sortby=<?php print $child_tmp ?>&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Child</a>
	<?php
	if ($sortby == $child_tmp) {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby={$child_tmp}&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby={$child_tmp}&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
</th>
<th>PayPal ID</th>
<th>
<?php if ($renewal) {
	print "Action";
} else {
	print "Type";
}	
?>	
</th>
</tr>
<?php 
# initialize counter variables.
$allsponsorships = 0;
$stalesponsorships = 0;

while ($row = mysqli_fetch_assoc($res)) {
	$show_row = true; 
	if ($row['days'] >= 365) {
		$yr = floor($row['days'] - 365);
		$dy = $row['days'] - 365 * $yr;
		$tmp = "$yr y, $dy d";
	} else {
		$tmp = $row['days'] . " days" ;
	}

    # determine overdue status
    $max = get_dictionary_setting($row['frequency_unit'],'overdue',30);
	switch ($row['frequency_unit']) {
		case 'year' :
			$multiplier = 365;
			break;
		case 'month' :
			$multiplier = 30 ;
			break;
		case 'week' :
			$multiplier = 7;
			break;
		default :
			$multiplier = 1;
	}
	$max = $max + ($multiplier * ($row['frequency_interval']));
	if ($row['last_pay_duration'] > $max) {
		$style = "stale";
	} else {
		$style = "normal";
	}
	
	
	# determine whether TGC or AOET sponsorship
	if ((filter_var($row['itemid'], FILTER_VALIDATE_INT)) and ($row['itemid'] < 999)) {
		$thisgroup = 'TGC';
		#  skip to the next child, if a TGC child is explicitly not sponsored.
		if ($row['is_sponsored'] <> 'Y') {
#			debug ("Found unsponsored child {$row['itemid']}");
			$show_row = false; }
		
	} else {
		$thisgroup = 'AOET';
	}
	if (($group == '') or ($thisgroup == $group)) { 
	#  compose email body
	    $now = strftime('%Y-%m-%d %T');
	    $sql = "insert email values (null, {$row[id]}, '{$row['child']}', '{$row['itemid']}','{$row['date']}', '$now'  )";
	
	if ($show_row) {
		$allsponsorships ++;
		if ($style <> "normal") {$stalesponsorships ++ ; }
		?>
<tr class="<?php print $style ?>">
<!--  Sponsor column -->
<td><?php print $row['sponsor'] ?>&nbsp;</td>

<!--  email column -->
<td>
<form action="" method="post" target="_blank">
	<?php print $row['email'] ?>&nbsp;
	<input type = "submit" name="submit" value= "Send email">
	<input type = "hidden" name="sql" value="<?php print $sql ?>"> 
	<input type = "hidden" name="cid" value="<?php print $row['id'] ?>"> 
</td>

<!--  Sponsorship date column -->
<td>
	<?php print strftime('%m/%d/%y',strtotime($row['date']))." &nbsp;(".$tmp.")" ?>&nbsp;
</form>	
	
	</td>
<!--  Pay interval column -->
<td><?php print "{$row['frequency_interval']} {$row['frequency_unit']}" ?></td>

<!--  Latest payment column -->
<td><?php print strftime('%m/%d/%y',strtotime($row['latest_payment'])) . " ({$row['last_pay_duration']} days)" ?></td>

<!--  Type column -->
<td align="center"><?php print $thisgroup ?></td>

<!--  Chiild column -->
<td align="center"><?php print $row['child'] ?>&nbsp;</td>

<!-- Paypal id -->
<td align="center"><?php print $row['processor_id']?>&nbsp;</td>
	
<!--  Action column -->
<td align="center">
<?php if ($renewal) { ?>
	<a href="sponsorship_delete.php?id=<?php print $row['id'] ?>&childid=<?php print $row['itemid'] ?>&period=<?php print $period ?>&id=<?php print $sessionid ?>">Delete</a>
	&nbsp;
<?php } else { 
	print $row['payment_type']."&nbsp;";
}
	?>
  
</td>

</tr>
<?php } } } ?>
<tr bgcolor="#FFFFCC"><td colspan="9" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
</td>
</tr>
<tr bgcolor="#FFFFCC"><td colspan="9">
	<table width="100%" cellpadding="3" cellspacing="3">
		<tr>
			<td width="40%" align="right">Total Sponsorships:</td>
			<td width="10%"><?php print $allsponsorships ?></td>
			<td width="40%" align="right">Stale Sponsorships:</td>
			<td width="10%"><?php print $stalesponsorships ?></td> 
		</tr>
	</table> 
</td></tr>
</table>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>


 