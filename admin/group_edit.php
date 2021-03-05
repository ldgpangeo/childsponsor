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

#   get the dictionary area involved
$groupid = getinput('gid',null);
if ($groupid == '') {
	throw new Exception("I don't know which group to edit.",ERROR_MAJOR);
}
# get the group name
$groupname = get_dictionary_label($groupid, "groups",null);
if ($groupname == null) {
	throw new Exception("Invalid group.",ERROR_MAJOR);
}

# get current group information
$res = do_sql("select * from groups where groupid = '$groupid'");
$rowcount = mysqli_num_rows($res);
if ($rowcount > 1) {
	throw new Exception("Unable to retrieve unique group information.", ERROR_MAJOR);
}
#  is this an insert or an update?
if ($rowcount == 1) {
	$insert = false;
	$group = mysqli_fetch_assoc($res);
} else {
	$insert = true;
	#  set default values here	
	$group = array(
		"is_active" => 'Y',		
		"max_sponsors" => 1,
	);
	
}
	

# ---------------------  process a prior submission   -------------------------------------
if (isset($_POST['Save'])) {
		#  test user inputs
		$errors = null;
		$new = form_validate("group_edit",$errors);
		if ($errors <> null) {
			throw new Exception($errors, ERROR_MINOR);
		}
		if ($insert ) {
			$fields = array_keys($new);
			$fieldstr = implode(", ",$fields);
			$sql = "insert dictionary ($fieldstr) values (" ;
			$sep = "";
			foreach ($fields as $field) {
				$sql .= $sep;
				if ($new[$field] == null) {
					$sql .=  "null";
				} else {
					$sql .= "'".$new[$field]."'";
				}
				if ($sep == '') {$sep = ", ";}
			}
			$sql .= " )";
			$res = do_sql($sql);
			if (!$res) {
				throw new Exception("Insertion failed.|: $sql", ERROR_MAJOR);
			}
			$groupid = mysqli_insert_id($hdl);
			if ($groupid == null) {
				throw new Exception("Unable to retrieve new group value| $sql", ERROR_MAJOR);
			}
			logit('groups_inserted',"groupid=$groupid,  name=$groupname admin=$login ");
			print "<h4>Insert done...</h4>\n";
		} else {
			$fields = array_keys($new);
			$sql = "update groups set ";
			$sep = "";
			$changes = array();
			$doit = false;
			foreach ($fields as $field) {
				if ($new[$field] <> $group[$field]) {
					$doit = true;
					array_push($changes, "$field = ".$new[$field]);
					$sql .= $sep. $field. "=" ;
					if ($new[$field] == null) {
						$sql .=  "null";
					} else {
						$sql .= "'".$new[$field]."'";
					}
					if ($sep == '') {$sep = ", ";}
				}
			}
				$sql .= " where groupid = '$groupid'";		    			
			if ($doit) {
				$res = do_sql($sql);
				if (!$res)  {
					throw new Exception("Update failed.| $sql", ERROR_MAJOR);
				}
				$msg = implode(", ",$changes);
				logit('Groups_updated',"groupid=$groupid,  name=$groupname, $msg, admin=$login ");
				print "<h4>Update done...</h4>\n";
			}
		}
		#  reload group information
		if ($insert or $doit) {
			$res = do_sql("select * from groups where groupid = '$groupid'");
			$rowcount = mysqli_num_rows($res);
			if ($rowcount > 1) {
				throw new Exception("Unable to retrieve unique group information.", ERROR_MAJOR);
			}
			$group = mysqli_fetch_assoc($res);
				
		}
}   # end processing prior submission
# ---------------------  end process prior submission  ------------------------------------



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
<title>Admin:  Group Edit</title>
</head>
<body>

<form action="" method="POST">
<input type="hidden" name="groupid" value="<?php print $groupid?>">
  <table width="70%" align="center" border="1" cellspacing="3" cellpadding="3">
  <tr bgcolor="#FFFF99"><th colspan="5">Create/Edit Sponsorship Group Settings for <?php print $area ?></th>
    <tr>
      <td>Group is Active</td>
      <td>
      	<input type="radio" name="is_active" value="Y" <?php if ($group['is_active'] == 'Y') {print " checked ";} ?> >Yes
      	&nbsp;&nbsp;&nbsp;&nbsp;
      	<input type="radio" name="is_active" value="N" <?php if ($group['is_active'] <> 'Y') {print " checked ";} ?> >No
      	</td>
    </tr>
    <tr>
      <td>Maximum sponsors per child</td>
      <td><input type="text" name="max_sponsors" value="<?php print $group['max_sponsors'] ?>"></td>
    <tr>
    <tr>
      <td>Allow price changes</td>
      <td>
      	<?php dictionary_as_radio("allow_price_changes","yesno", $group['allow_price_changes'] )  ?>
      </td>
    </tr>
    <tr>
      <td>Monthly sponsorship fee</td>
      <td><input type="text" name="monthly" value="<?php print $group['monthly'] ?>" size="10" maxlength="10"> (blank = use dictionary default)</td>
    </tr>
    <tr>
      <td>Yearly sponsorship fee</td>
      <td><input type="text" name="yearly" value="<?php print $group['yearly'] ?>" size="10" maxlength="10"> (blank = use dictionary default)</td>
    </tr>
    <tr>
      <td>Name of style sheet</td>
      <td><input type="text" name="css" value="<?php print $group['css'] ?>" size="60" maxlength="200"> (blank = use default)</td>
    </tr>
     <tr>
      <td>Custom left footer</td>
      <td>
      	Prompt: <input type="text" name="left_footer_name" value="<?php print $group['left_footer_name'] ?>" size="60" maxlength="80"> (either blank = use default)<br />
      	File:  <input type="text" name="left_footer_url" value="<?php print $group['left_footer_url'] ?>" size="60" maxlength="200">
      	</td>
    </tr>
     <tr>
      <td>Custom center footer</td>
      <td>
      	Prompt: <input type="text" name="center_footer_name" value="<?php print $group['center_footer_name'] ?>" size="60" maxlength="80"> (either blank = use default)<br />
      	File:  <input type="text" name="center_footer_url" value="<?php print $group['center_footer_url'] ?>" size="60" maxlength="200">
      	</td>
    </tr>
     <tr>
      <td>Custom right footer</td>
      <td>
      	Prompt: <input type="text" name="right_footer_name" value="<?php print $group['right_footer_name'] ?>" size="60" maxlength="80"> (either blank = use default)<br />
      	File:  <input type="text" name="right_footer_url" value="<?php print $group['right_footer_url'] ?>" size="60" maxlength="200">
      	</td>
    </tr>
    <tr bgcolor="#FFFF99">
    <td align="center" colspan="2"><input type="submit" value="Save" name="Save">
    </td>
    </tr>
  </table>
  <hr noshade width="70%">
  <table width="70%" align="center" bgcolor="#CCCCCC" cellpadding="4" cellspacing="4">
  <tr>
  <td align="left" width="25%"><a href="index.php?id=<?php print $sessionid ?>">Return to index</a></td>
  </tr>
  </table>
<p></p>
  <p></p>
  </body>
</html>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>
