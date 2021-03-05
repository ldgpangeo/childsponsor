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
<title>Admin:  Dictionary</title>
</head>
<body>

<div align="center">
<table width="600" border="0">
<tr>
<td width="500"><h3 align="center">Edit Dictionary Settings</h3></td>
</tr>
</table></div>

<?php
#   get the dictionary area involved
$area = getinput("area");

$sql = "select * from dictionary where area = '$area' order by seq,label";
$res = do_sql($sql);

?>
  <form action="" method="POST">
  <table width="70%" align="center" border="1" cellspacing="3" cellpadding="3">
  <tr bgcolor="#FFFF99"><th colspan="5">Create/Edit Dictionary for area <?php select_dictionary_area('area',$area) ?>
  <input type="submit" value="Go">
  </th>
  </tr>
  <tr>
  <th>Action</th>
  <th>Seq</th>
  <th>Label</th>
  <th>Setting</th>
  <th>Comment</th>
  </tr>
  <?php while ($row = mysqli_fetch_assoc($res)) {
  ?>
  <tr>
  <td><a href="dictionary_edit.php?dictid=<?php print $row['dictid'] ?>&area=<?php print $area ?>&id=<?php print $sessionid ?>">Edit</a></td>
  <td><?php print $row['seq'] ?></td>
  <td><?php print $row['label'] ?></td>
  <td><?php print $row['setting'] ?></td>
  <td><?php print $row['comment'] ?></td>
  </tr>
  <?php
  } ?>
  </table>
  </form>
<p>
<a href = "dictionary_edit.php?dictid=-1&area=<?php print $area ?>&id=<?php print $sessionid ?>">Add item</a>
</p>
  <hr noshade width="70%">
  <table width="70%" align="center" bgcolor="#CCCCCC" cellpadding="4" cellspacing="4">
  <tr>
  <td align="left" width="25%"><a href="index.php?id=<?php print $sessionid ?>">Return to Admin index</a></td>
  <td align="center" width="50%"><a href="../index.php">Return to Public Index</a></td>
  <td align="right" width="25%"><a href="logout.php">Logout</a></td>
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
