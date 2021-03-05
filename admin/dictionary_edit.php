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
$area = getinput('area',null);

#  get dictionary id
$dictid = getinput ('dictid');
if ($dictid == '') {
	throw new Exception("Dictionary entry id is missing",ERROR_MAJOR);
}
#   if dictid is -1 then this is an insertion.
if ($dictid == -1) {
	$new = true;
} else {
	$new = false;
}
#  -----------------  process deletion  -------------------------
if ((clean(trim($_GET['action']))== 'del') and (!$new)) {
		if ($area == '') {
			throw new Exception("Dictionary area is missing",ERROR_MAJOR);
		}
		$res = do_sql("select * from dictionary where dictid = '$dictid' and area = '$area'");
		if (mysqli_num_rows($res) <> 1) {
			throw new Exception("Failed to find a unique dictionary entry.| dictid = $dictic and area = $area", ERROR_MAJOR);
		}
		$row = mysqli_fetch_assoc($res);
		$sql = "delete from dictionary where dictid = '$dictid' and area = '$area'";
		$res = do_sql($sql);
		logit('Dictionary_deletion',"area=$area, label={$row['label']}, setting={$row['setting']}, admin=$login ");
		if (!$res) {
			throw new Exception("Deletion failed.|: $sql",ERROR_MAJOR);
		} else { ?>
			<meta http-equiv="refresh" content="0;URL=dictionary_list.php?area=<?php print $area ?>&id=<?php print $sessionid ?>">
<?php
			$new = true;
		}
	
}

#  -----------------  end  deletion  ----------------------------




# ---------------------  process a prior submission   -------------------------------------
if (isset($_POST['Save'])) {

	if ($_POST['action'] == del) {
		$new = true;
	} else {
		if ($area == '') {
			throw new Exception("Dictionary area is missing",ERROR_MAJOR);
		}

		#  test user inputs
		$msg = "";
		$seq = clean(trim($_POST['seq']));
		if ($seq == '') {$seq = 999;}
		$label = clean(trim($_POST['label']));
		if ($label =='') {
			throw new Exception( "You must provide a label name.", ERROR_MINOR);
		}
		$setting = addslashes(trim($_POST['setting']));
		$comment = addslashes(clean(trim($_POST['comment'])));
		$fragment = addslashes($_POST['fragment']);

		if ($new ) {
			$sql = "insert dictionary (area,seq,label,setting,comment) values
				        ('$area','$seq','$label','$setting','$comment')";
			$res = do_sql($sql);
			if (!$res) {
				throw new Exception("Insertion failed.|: $sql", ERROR_MAJOR);
			}
			$dictid = mysqli_insert_id($hdl);
			logit('Dictionary_inserted',"dictid=$dictid,  area=$area, label=$label, setting=$setting, admin=$login ");
			$new = false;
			print "<h4>Insert done...</h4>\n";
			$redirect = "dictionary_list.php?area={$area}&id={$sessionid}";
		} else {
			$sql = "update dictionary set seq = '$seq', label = '$label',
				       setting = '$setting', comment = '$comment' 
				       where dictid = '$dictid' and area = '$area'";
			$res = do_sql($sql);
			if (!$res)  {
				throw new Exception("Update failed.| $sql", ERROR_MAJOR);
			}
			logit('Dictionary_updated',"dictid=$dictid,  area=$area, label=$label, setting=$setting, admin=$login ");
			
			print "<h4>Update done...</h4>\n";
		}
	}   #  end deletion or save
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
<title>Admin:  dictionary</title>
</head>
<body>
<?php
#   if not a new item, load in the previous values
if (! $new) {
	$sql = "select * from dictionary where area = '$area' and dictid = $dictid order by seq,label";
	$res = do_sql($sql);
	if ((! $res) or (mysqlI_num_rows($res) <> 1)) {
		throw new Exception("Failed to find the existing dictionary item.", ERROR_MAJOR);
	}
	$label = mysqli_result($res,0,'label');
	$setting = stripslashes(mysqli_result($res,0,'setting'));
	$comment = stripslashes(mysqli_result($res,0,'comment'));
	$seq = mysqli_result($res,0,'seq');
}
?>
<form action="" method="POST">
<input type="hidden" name="area" value="<?php print $area ?>">
<input type="hidden" name="dictid" value="<?php print $dictid?>">
  <table width="70%" align="center" border="1" cellspacing="3" cellpadding="3">
  <tr bgcolor="#FFFF99"><th colspan="5">Create/Edit Dictionary Entry for area <?php print $area ?></th>
    <tr>
      <td>Sequence</td>
      <td><input type="text" name="seq" value="<?php print $seq ?>"></td>
    <tr>
    <tr>
      <td>Label </td>
      <td><input type="text" name="label" value="<?php print $label ?>"></td>
    <tr>
    <tr>
      <td>Setting</td>
      <td><input type="text" name="setting" value="<?php print $setting ?>" size="60" maxlength="255"></td>
    <tr>
    <tr>
      <td>Comment</td>
      <td><input type="text" name="comment" value="<?php print $comment ?>" size="60"></td>
    <tr>
    <tr>
    <tr bgcolor="#FFFF99">
    <td align="center" colspan="2"><input type="submit" value="Save" name="Save">
    </td>
    </tr>
  </table>
<?php if (! $new) { ?>
  <p>
<a href="dictionary_edit.php?area=<?php print $area ?>&dictid=<?php print $dictid ?>&action=del&id=<? print $sessionid ?>">Delete this item</a>
</p>
<?php } ?>
  <hr noshade width="70%">
  <table width="70%" align="center" bgcolor="#CCCCCC" cellpadding="4" cellspacing="4">
  <tr>
  <td align="left" width="25%"><a href="index.php?id=<? print $sessionid ?>">Return to index</a></td>
  <td align="right" width="50%"><a href="dictionary_list.php?area=<?php print $area ?>&id=<? print $sessionid ?>">Show Dictionary List</a> </td>
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
