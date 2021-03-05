<?php
function dump_array($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

function show_boolean($item) {
	if ($item) {
		return "true";
	} else {
		return "false";
	}
}

function init_debug() {
	global $mysql,$uid,$dbg ;
#	print "object is $object<br>";
	if ($dbg === false) {return null;}
	$sql = "select max(dbgsession) as max from debug ";	
#    print "$sql <br>";
	$res = mysqli_query($hdl, $sql);
	$dbgsession = mysqli_result($res,0,'max');
	if ($dbgsession == '') { $dbgsession = 0; }
	$dbgsession ++;
	$now = strftime('%Y-%m-%d %T');
	$sql = "insert debug (dbgsession,datedone,message,trace) values ('$dbgsession','$now','Debug session started',null)";
#    print "$sql <br>";
	$res = mysqli_query($hdl, $sql);
	return $dbgsession;
}

function debug($msg) {
	global $uid,$dbg,$dbgsession, $hdl;
	if ($dbg === false) {return null;}
	# initialize debug
	if ($dbgsession == '') {$dbgsession = init_debug($object_id);}
	$tmp = mysqli_real_escape_string($hdl, $msg);
	$trace = debug_backtrace();
	$tracemsg = '';
	foreach ($trace as $item) {
		$tracemsg .= "from {$item['file']}:{$item['function']} args: line {$item['line']}\n";
#		$tracemsg .= "from {$item['file']}:{$item['function']} args: ". dump_array($item['args']) . ": line {$item['line']}\n";
	}
	$tracemsg = mysqli_real_escape_string($hdl, $tracemsg);
	$now = strftime('%Y-%m-%d %T');
	$sql = "insert debug (dbgsession,datedone,message,trace) values ('$dbgsession','$now','$tmp','$tracemsg')";
#    print $sql."<br>";
	$res = mysqli_query($hdl, $sql);
}

?>