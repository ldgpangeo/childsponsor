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

	
	
$calendar = TRUE;
$header = "Activity Log";
print ("<h2 align=\"center\">$header</h2>");

$done = $_POST['done'];
if ($done == 1) {
	if ($allowdebug) {$dbg = TRUE;}
$datestart = trim($_POST['datestart']);
$dateend = trim($_POST['dateend']);
$ip = clean(trim($_POST['ip']));
$eventcode = clean(trim($_POST['eventcode']),1);
$security = trim($_POST['security']);
$search = "select datedone,event_code,detail,ip,security from logs where 1 = 1 ";
if ($datestart <> '') {
    $tmp = strftime("%Y-%m-%d",strtotime($datestart));
    $search = $search . "and datedone > '$tmp' ";
}

if ($dateend <> '') {
    $tmp = strftime("%Y-%m-%d",strtotime($dateend)+86400);
    $search = $search . "and datedone < '$tmp' ";
}

if ($eventcode <> '') {
    $search = $search . "and event_code = '$eventcode' ";
}

if ($ip <> '') {
    $search = $search . "and ip = '$ip' ";
}

if ($security <> '') {
    $search = $search . "and security = '$security' ";
}



debug("SQL: $search");
$res = do_sql($search);
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
<title>Admin:  Search Log</title>
</head>
<body>


<?php
if ($done ==1) {
# print "<p>$search</p>\n";
?>

<table width="95%"  border="1" cellspacing="3" cellpadding="3">
  <tr>
    <th>Date</th>
    <th>IP</th>
    <th>Event Code</th>
    <th>Detail</th>
    <th>Security</th>
  </tr>

<?php
$i = 0;
while ($i < mysqli_num_rows($res)) {
?>
  <tr>
    <td><?php print strftime('%m/%d/%y %r',strtotime(mysqli_result($res,$i,'datedone'))) ?></td>
    <td><?php print mysqli_result($res,$i,'ip') ?></td>
    <td><?php print mysqli_result($res,$i,'event_code') ?>&nbsp;</td>
    <td><?php print stripslashes(mysqli_result($res,$i,'detail')) ?>&nbsp;</td>
    <td><?php print stripslashes(mysqli_result($res,$i,'security')) ?>&nbsp;</td>
  </tr>
<?php $i = $i + 1; }  ?>
</table>
<p>&nbsp;</p>
<?php
}
?>

<form name="form1" method="post" action="">
  <table width="60%"  border="1" align="center" cellpadding="3" cellspacing="3">
    <tr>
      <th colspan="2" bgcolor="#FFFF99">Search <?php print $header ?> </th>
    </tr>
    <tr>
      <td width="35%" class="larger">Beginning on date: </td>
      <td width="65%"><input name="datestart" type="text" id="datestart"></td>
    </tr>
    <tr>
      <td class="larger">Through date : </td>
      <td><input name="dateend" type="text" id="dateend"></td>
    </tr>
    <tr>
      <td class="larger">Select event code:</td>
      <td><select name="eventcode" id="eventcode">
        <option value = "" selected>All events</option>
<?php
$sql = "select distinct event_code from logs   order by event_code";
$res = mysqli_query($hdl, $sql);
if ($res) {
   $i = 0;
   while ($i < mysqli_num_rows($res)) {
       print "<option>".mysqli_result($res,$i,'event_code')."</option>\n";
	   $i = $i + 1;
   }
}
?>
      </select></td>
    </tr>
    <tr>
      <td width="35%" class="larger">IP address: </td>
      <td width="65%"><input name="ip" type="text" id="ip"> (blank = all)</td>
    </tr>
    <tr>
      <td width="35%" class="larger">Security related: </td>
      <td width="65%"><input name="security" type="radio" id="security" value = "N"> No &nbsp;&nbsp;&nbsp;&nbsp; 
      	<input name="security" type="radio" id="security" value = "Y"> Yes &nbsp;&nbsp;&nbsp;&nbsp;
      	<input name="security" type="radio" id="security" value = "" checked> Both 
      	</td>
    </tr>
        <tr>
      <td colspan="2" bgcolor="#FFFF99"><div align="center">
        <input name="done" type="hidden" id="done" value="1">
        <input name="id" type="hidden" id="id" value="<?php print $sessionid ?>">
        <input name="submit" type="submit" id="submit" value="Start Search">
      </div></td>
    </tr>
  </table>
</form>

<p align="center"><a href="index.php?id=<?php print $sessionid . "&object=" . $object_id ?>">Return to Admin Index</a></p>
</body>
</html>


<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>
