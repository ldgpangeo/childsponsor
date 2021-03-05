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

$action = getinput('action',null);
if ($action <> null) {
	$rowid = getinput('rowid', null);
	$childid = getinput('itemid',null);
	$hash = getinput('hash', null);
	if ($hash == null) {
	    $frag = '';
	} else {
	    $frag = " and hash = '$hash' ";
	}
	if (($rowid == null) or ($childid == null)) {
		throw new Exception("Unknown target for action.", ERROR_MAJOR);
	}
	switch ($action) {
		case 'lock' :
			do_sql("update sponsorship_pending set is_locked = 'Y' where id = '$rowid' and itemid = '$childid' $frag");
			logit('ADMIN Pending Locked',"child ID = $childid");
			break;
		case 'unlock' :
			do_sql("update sponsorship_pending set is_locked = 'N' where id = '$rowid' and itemid = '$childid' $frag");
			logit('ADMIN Pending Unlocked',"child ID = $childid");
			break;
		case 'release' :
			do_sql("start transaction");
			do_sql("delete from sponsorship_pending where id = '$rowid' and itemid = '$childid' $frag");
			do_sql("update items set is_sponsored = 'N', hash = null where itemid='$childid'");
			do_sql("commit");
			logit('ADMIN Pending Released',"child ID = $childid");
			break;
		case 'record'  :
			logit('ADMIN Pending Recorded', "child ID = $childid");	
			header("Location: $url/finish.php?childid=$childid&web=yes&hash=$hash&id=$sessionid");
	}
	
}

if ($_POST['submit'] == 'Send email') {
	if ($_POST['sql'] == '') {throw new Exception("No SQL found.", ERROR_MAJOR);}
	do_sql($_POST['sql']);
	header("Location: $civicrm_base/activity/email/add?action=add&reset=1&cid={$_POST['cid']}&selectedChild=activity&atype=3");
}



$sortby = getinput('sortby','s.datedone');
$direction = getinput('dir','asc');
$sortcmd = " order by $sortby $direction";
$uri = "search_pending.php?Y";


$sql = "select s.*, i.title from sponsorship_pending s, items i where i.itemid = s.itemid order by $sortby $direction ";

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
<title>KOIKOI Pending Sponsorships</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Pending Sponsorships</H2>


<table align="center" border="1" cellpadding="3" cellspacing="3" width="80%">
<tr bgcolor="#FFFFCC">
<th><a href="<?php print $uri ?>&sortby=last_name&dir=<?php print $direction?>&id=<?php print $sessionid ?>">Sponsor</a>
	<?php
	if ($sortby == 'last_name') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=last_name&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=last_name&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
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
<th><a href="<?php print $uri ?>&sortby=s.datedone&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Sponsorship Date</a>
	<?php
	if ($sortby == 's.datedone') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=s.datedone&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=s.datedone&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
	
</th>
<th><a href="<?php print $uri ?>&sortby=title&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Child</a>
	<?php
	if ($sortby == 'title') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=title&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=title&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
	
</th>
<th><a href="<?php print $uri ?>&sortby=is_locked&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Locked</a>
	<?php
	if ($sortby == 'is_locked') {
		if ($direction == 'asc') {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=is_locked&dir=desc&id=$sessionid\" border=\"0\"><img src=asc.gif></a>";
		} else {
			Print "&nbsp;&nbsp;&nbsp;<a href=\"$uri&sortby=is_locked&dir=asc&id=$sessionid\" border=\"0\"><img src=desc.gif></a>";		
		}
	}
?>
	
</th>
<th>Action</th>
</tr>
<?php 

while ($row = mysqli_fetch_assoc($res)) { 
	#  compose email body
    $now = strftime('%Y-%m-%d %T');
    $sql = "insert email values (null, {$row[id]}, '{$row['child']}', {$row['itemid']},'{$row['date']}', '$now'  )";
		?>
<tr class="<?php print $style ?>">
<td><?php print "{$row['last_name']}, {$row['first_name']}" ?>&nbsp;</td>
<td>
	<?php print $row['email'] ?>&nbsp;
<!--
<form action="" method="post" target="_blank">
	<input type = "submit" name="submit" value= "Send email">
	<input type = "hidden" name="sql" value="<?php print $sql ?>"> 
	<input type = "hidden" name="cid" value="<?php print $row['id'] ?>"> 
</form>	
-->
</td>
<td>
	<?php print strftime('%m/%d/%y',strtotime($row['datedone'])) ?>&nbsp;
	
	</td>
<td align="center"><?php print $row['title'] ?>&nbsp;</td>
<td align="center"><?php print $row['is_locked'] ?></td>
<td align="center">
<?php if ($row['is_locked'] == 'N') { ?>
	<a href="search_pending.php?rowid=<?php print $row['id'] ?>&itemid=<?php print $row['itemid'] ?>&action=lock&hash=<?php print $row['hash'] ?>&sortby=<?php print $sortby ?>&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Lock</a>
<?php } else {
?>
	<a href="search_pending.php?rowid=<?php print $row['id'] ?>&itemid=<?php print $row['itemid'] ?>&action=unlock&hash=<?php print $row['hash'] ?>&sortby=<?php print $sortby ?>&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Unlock</a>

<?php
}
	?>
&nbsp;&nbsp;
<a href="search_pending.php?rowid=<?php print $row['id'] ?>&itemid=<?php print $row['itemid'] ?>&action=release&hash=<?php print $row['hash'] ?>&sortby=<?php print $sortby ?>&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Release</a> 
&nbsp;&nbsp;
<?php
$tmp = urlencode($row['title']);
$name = html_entity_decode($row['first_name']);
$name = preg_replace('/&/', 'and', $name);
#  hack by ldg on 4/20/15 to permit annual recurring.
if ($row['amt'] > 200) { $freq = 'year'; } else { $freq = 'month'; }
$body = "Dear%20{$name}:%0A%0A

Thank you for offering to sponsor {$row['title']}.%0A%0A

I am sorry you encountered difficulties in completing your sponsorship.%0A%0A  

Please go to the following link to complete your sponsorship:%0A%0A

   {$url}/sponsor1.php?itemid={$row['itemid']}%26hash={$row['hash']}%0A%0A

Sincerely,%0A
Erin Walborn%0A
Email:  erinwalborn@gmail.com%0A
Phone:  518.424.3803%0A
%0A


";
?>
<a href="mailto:<?php print $row['email'] ?>?subject=Resume%20your%20sponsorship&body=<?php print $body ?>">Resume</a> 
&nbsp;&nbsp;
<a href="search_pending.php?rowid=<?php print $row['id'] ?>&itemid=<?php print $row['itemid'] ?>&action=record&hash=<?php print $row['hash'] ?>&sortby=<?php print $sortby ?>&dir=<?php print $direction ?>&id=<?php print $sessionid ?>">Record</a> 
</td>

</tr>
<?php }  ?>
<tr bgcolor="#FFFFCC"><td colspan="6" align="center">
<a href="index.php?id=<?php print $sessionid ?>">Return to Admin Menu</a>
</td>
</tr>
</table>
  </body>
</html>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>



 