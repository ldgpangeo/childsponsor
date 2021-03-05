<?php 
include_once('lib/common-init.php');

try {

$is_renew = false;
$renew = getinput('renew');
if ($renew <> null) {
	if (filter_var($renew, FILTER_VALIDATE_INT) === false) {
		throw new Exception("Invalid renewal flag| bad renewal: $renew",ERROR_MAJOR);
	}
	
	#  we are renewing an existing sponsorship.
	$is_renew = true;
	$hash = $renew;
	#  get the info from the existing sponsorship record
	$res = do_sql("select * from sponsorships where hash = '$renew' and effective_end_ts is null");
	if (mysqli_num_rows($res) <> 1) {
		throw new Exception("Failed to find prior sponsorship information",ERROR_MAJOR);
	}	
	$data = mysqli_fetch_assoc($res);
	$childid = $data['itemid'];
} else {
	$childid=getinput(itemid) ;
	if (filter_var($childid, FILTER_VALIDATE_INT) === false) {
		throw new Exception("Invalid child ID| bad child id: $childid",ERROR_MAJOR);
	}
	if ($childid == null) { $childid = getinput("childid"); }
}

if ($childid == '') {
	#  itemid can not be null
	throw new Exception("I don't know which child you are sponsoring.",ERROR_MINOR);
} else {
	if (! $is_renew) {
		$hash = getinput("hash");
	}
	if (filter_var($hash, FILTER_VALIDATE_INT) === false) {
		throw new Exception("Invalid hash| bad hash: $hash",ERROR_MAJOR);
	}
	
	# get child's name
	$res = do_sql("select title,is_sponsored,hash, groupid, max_sponsors from items where itemid = '$childid'");
	$current = mysqli_num_rows($res);
	$row = mysqli_fetch_assoc($res);
	$child = $row['title'];
	$groupid = $row['groupid'];
	$max_sponsors = $row['max_sponsors'];
	$title = $row['title'];
	#  collect the group information
	$res = do_sql("select * from groups where groupid = '$groupid'");
	$group = mysqli_fetch_assoc($res);
	if ($child == '') {
		throw new Exception("Can not find the child's name",ERROR_MAJOR);
	}
	#  Don't sponsor the same child twice.
	if ( $is_renew == false ) {
	    #  Is this child available for sponsorship?
	    $sql = "select itemid from sponsorship_pending where itemid = '$panel_id'
                union all select itemid from sponsorships where itemid = '$panel_id' and effective_end_ts is null";
	    $res = do_sql($sql);
	    $current = mysqli_num_rows($res);
	    if ( $max_sponsors <= 1) {
	        $is_sponsored = $row['is_sponsored'];
	    } elseif ($current >= $max_sponsors) {
	        $is_sponsored = 'Y';
	    } else {
	        $is_sponsored = 'N' ;
	    }
	    
		switch ($is_sponsored) {
			case 'Y' :
			    if ($current >= $max_sponsors ) {
				    throw new Exception("Child is already sponsored.",ERROR_MINOR);
			    }
				break;
			case 'P' :
				if ($hash <> $row['hash']) {
					throw new Exception("Child sponsorship is in progress.",ERROR_MINOR);
				}
				# collect previous info
				$pres = do_sql("select * from sponsorship_pending where hash = '$hash'");
				if ( mysqli_num_rows($pres) == 1 ) {
					$data = mysqli_fetch_assoc($pres);
				} 
				break;
			default :
				break;
		}
	}
}


# -----------------------------   validate form input   -------------------------------------------------------
	debug("form values:\n".dump_array($_POST));

if ($_POST['submitted'] <> '') {
	debug("form values:\n".dump_array($_POST));
	$errmsg = '';     #  placeholder for all error messages
	$data = form_validate('sponsor1', $errmsg);
	if ($dbg) {debug("sponsor1.php: ".$errmsg); }
	if ($errmsg <> '') {
		throw new Exception($errmsg, ERROR_MINOR);
	}
	debug("parsed values:\n".dump_array($data));
	
	#  set the payment interval and amount
	switch ($data['interval']) {
	    case 'yearly' :
	        $period = 1;
	        $freq = 'year';
	        $amt = $data['yearly_amt'];
	        break;
	    case 'first_monthly' :
	        $period = 1;
	        $freq = 'month';
	        $amt = $data['first_monthly'];
	        break;
	    case 'second_monthly' :
	        $period = 1;
	        $freq = 'month';
	        $amt = $data['second_monthly'];
	        break;
	    case 'first_yearly' :
	        $period = 1;
	        $freq = 'year';
	        $amt = $data['first_yearly'];
	        break;
	    case 'second_yearly' :
	        $period = 1;
	        $freq = 'year';
	        $amt = $data['second_yearly'];
	        debug ("In second yearly amt is $amt and raw was " . $data['second_yearly'] );
	        break;
	    case 'monthly' :
	        $period = 1;
	        $freq = 'month';
	        $amt = $data['monthly_amt'];
	        break;
	    default:
	        throw new Exception ("Please select a payment option. ", ERROR_MAJOR);
	}
	$data['amt'] = $amt;
	$data['is_repeat'] = $period;
	$data['freq'] = $freq;
	$data['itemid'] = $childid;
	$data['hash'] = $hash;
	$data['groupid'] = $groupid;
	# save the data
	$fields = array('email','first_name','middle_initial','last_name','itemid','appear','appear_text','hash','amt','is_repeat','freq', 'groupid');
	$updates = array('email','first_name','middle_initial','last_name','itemid','appear','appear_text','amt','is_repeat','freq', 'groupid');
	# Are we updating or inserting?
	$res = do_sql("select id from sponsorship_pending where itemid = '$childid' and hash = '$hash'");
	if (mysqli_num_rows($res) > 0) {
		$id = mysqli_result($res, 0, 'id');
		$sql = "update sponsorship_pending set ";
		$sep = '';
		foreach ($fields as $field) {
			if ($field == null) {
				$sql .= $sep . $field . "=null";
			} else {
				$sql .= $sep . $field . "='".$data[$field]."'";
			}
			if ($sep == '') { $sep = ', ';}
		}
		$sql .= " where id = '$id' ";
	} else {
		$field_list = implode (",", $fields);
		$now = strftime('%Y-%m-%d %T');
		$sql = "insert sponsorship_pending (datedone,$field_list) values ('$now' ";
		$sep = ", ";
		foreach ($fields as $field) {
			if ($field == null) {
				$sql .= $sep . "null";
			} else {
				$sql .= $sep . "'".$data[$field]."'";
			}
		}
		$sql .= ") on duplicate key update ";
		$sep = '';
		
		foreach ($updates as $field) {
			if ($field == null) {
				$sql .= $sep . $field . "=null";
			} else {
				$sql .= $sep . $field . "='".$data[$field]."'";
			}
			if ($sep == '') { $sep = ', ';}
		}
	}
	do_sql ("start transaction");
	$res = do_sql ($sql);
	if (! do_sql("update items set is_sponsored = 'P', hash = '$hash' where itemid = $childid and is_sponsored = 'N'")) {
			throw new Exception ("Unable to update sponsorship data.");
	};
	do_sql("commit");
	logit ("Sponsorship_started","itemid = $childid, name = {$data['first_name']} {$data['last_name']}, amount = $amt, interval = {$data['interval']}", false);


	header("Location: {$civicrm_url}?Child=$child&child_id=$childid&amt=$amt&repeat=$period&freq=$freq&gid=$groupid&hash=$hash");
}
	

# -----------------------------   end validate form input   -------------------------------------------------------

$res2 = do_sql( "select title, itemid from items where is_active = 'Y' and (is_sponsored = 'N' or ". 
                " (is_sponsored = 'P' and hash = '$hash')) and groupid = '$groupid' order by seq");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<head>
<title>Sponsorship Step 1</title>
<!-- ########## CSS Files ########## -->
<!-- Framework CSS -->
<link rel="stylesheet" href="css/kriframework.css" type="text/css" media="screen" />
<!-- lightbox CSS -->
<link rel="stylesheet" href="js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<!-- Screen CSS -->
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<!-- Stylesheets for each skin -->
<?php
if (isset($group['css'])) {
	?>  
	<link rel="stylesheet" href="css/<?php print trim($group['css']) ?>" type="text/css" media="screen" />
	<?php
} else {
	?>  
	<link rel="stylesheet" href="css/classic.css" type="text/css" media="screen" />
	<?php
	
}
?>
<!--  Javascript for manipulating the sponsorship cost -->
<script type='text/javascript' src='js/ajax_cost.js'></script>

</head>

<body bgcolor="#FFFFFF" id='top' onload="doWork(<?php print $childid ?>); return false;"">
<div class="wrap_fullwidth" id='head'><!-- end header -->
</div>
<div class="wrap_fullwidth" id='second_header'>
  <div class='center'>
    <h1 class="logo "><a href="index.php" title="Elementia - A premium Business and Portfolio Template">Elementia</a></h1>
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
      <?php if ($is_renew) { ?>
        <h1>Renew your Child Sponsorship</h1>
      <?php } else { ?>
        <h1>Sponsor a Child: Tell us who you are.</h1>
      <?php } ?>
        <div class="entry_content">
          <div class='image_border'></div>
<?php 
if ($errmsg <> "") {
	?>
	<h3>Please correct the following problems:</h3>
	<blockquote>
	<p><?php print $errmsg?></p>
	</blockquote> 
	<?php 
}
?>
<form id="frm1" method="post" action = "">
	
<table align="center" cellpadding="5" cellspacing="5" border="1" width="80%">
<tr>
<?php if ( ($is_renew) or ($title <> '') ) { ?>
	<td>Name of Child you are Sponsoring:</td>
	<td>
		<input type="hidden" name="itemid" value="<?php print $childid ?>">
		<?php print $title ?>
	</td>
<?php } else { ?>	
<td>Name of Child you will Sponsor: </td>
<td><select name="itemid" id="itemid" onchange="doWork(document.getElementById('itemid').value); return false;">
<option value="">Choose...</option>
<?php
while ($row2 = mysqli_fetch_assoc($res2)) {
    print "<option value=\"{$row2['itemid']}\" ";
	if ($row2['itemid'] == $childid) { print " selected "; }
	if ($row2['itemid'] == $data['itemid']) { print " selected "; }
	print ">{$row2['title']}</option>\n";
}
?>
</select>
</td>
<?php } ?>
</tr>

<tr>
	<td>Payment Options:</td>
	<td><div id='payment_area'></div></td>
</tr>

<tr><td>Your Email Address:</td>
<td><input type="text" name="email" size="50" maxlength="80" value="<?php print $data['email'] ?>" />
</td></tr>
<tr><td>Your Name:<br /></td>
<td>
First:<input type="text" name="first_name" size="15" maxlength="60" value="<?php print $data['first_name'] ?>"/>
MI:<input type="text" name="middle_initial" size="2" maxlength="1" value="<?php print $data['middle_initial'] ?>"/>
Last:<input type="text" name="last_name" size="15" maxlength="60" value="<?php print $data['last_name'] ?>"/>
</td></tr>

<tr><td colspan="2">
Would you like your name to appear as a sponsor on the web site?
<?php if ((! isset($data['appear'])) or ($data['appear'] == 'Y')) { ?>
&nbsp;&nbsp;<input type="radio" name="appear" value="Y" checked />Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="appear" value="N" />No
<?php } else { ?>
&nbsp;&nbsp;<input type="radio" name="appear" value="Y"  />Yes
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="appear" value="N" checked/>No
<?php } ?>
<br />
If yes, how should it appear (e.g. "Sponsored by the Jones Family):
<input type="text" name="appear_text" size="40" maxlength="80" value="<?php print $data['appear_text'] ?>"/>
</td></tr>

<tr><td colspan="2" align="center">

<input type="hidden" name="submitted" value="yes">
<input type="submit" name="doit" id="junk" value="Continue to Step 2 (payment)" />
</td></tr>
</table>
</form>
          <!-- end entry_content -->
        </div>
        <!-- end entry -->
      </div>
      <!-- end content_wrap -->
    </div>
    <!-- end center -->
  </div>
  <!-- end footer -->
</div>
	<div class="wrap_fullwidth" id='footer_bottom'>
	
		<div class='center'>
			<span class='copyright'>Copyright &copy; 2013 The Giving Circle</span>
			<a class='scrollTop ' href='#top'>top</a>
		<!-- end center -->
		</div>
	
	<!-- end footer -->
	</div>
</body>
</html>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>
 
