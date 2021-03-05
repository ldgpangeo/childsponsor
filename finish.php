<?php
try{ 
/*
 *   sample finish url
 *   finish.php?child=$child&childid=$childid&hash=$child_hash
 *   
 *   tgc.t4pg.com/childsponsor/finish.php?child=ALLAN+MUKISA&childid=156&hash=1065536938
 */
require_once("lib/common-init.php");
# require_once("lib/get_contactid.php");

$childid = getinput("childid",null);
$hash = getinput("hash", null);
$web = getinput("web", null);
debug ("Itemid is $childid");
if ($childid == '') { throw new Exception("null itemid encountered.",ERROR_MAJOR);}

# This only applies for TGC sponsorships  
# All others contain a letter

if (! (filter_var($childid, FILTER_VALIDATE_INT) === false) ) { 
    if ($hash <> null) { $fragment = " and hash = '$hash' "; }
	$res = do_sql("select * from sponsorship_pending where itemid = '$childid' $fragment order by itemid desc");
	if (mysqli_num_rows($res) == 0) {
		throw new Exception("Internal Error| Failed to get a pending row for $childid",ERROR_MAJOR_NORETURN);
	}
	do_sql("start transaction");
	# write to permanent tables
	$data = mysqli_fetch_assoc($res);
	#  get contact id for this transaction
	debug("Calling get_contactid\n".dump_array($data) );
	
	#  temporary fix because of broken civicrm api
	$sql = "select contact_id from cvsponsors where childid = '$childid' order by receive_date desc limit 1 ";
	#	$tmp = get_contactid($data);
	$res = do_sql($sql);
	$row = mysqli_fetch_assoc($res);
	if ( false <> $row ) {
		$data['contactid'] = $row['contact_id'];
	} else {
		$data['contactid'] = null;
	} 
	$start_date = dbdate("today");
	debug ("Writing to sponsorships\n".dump_array($data));
	write_sponsorships($childid,$data);
	# update child
	do_sql("update items set is_sponsored = 'Y' where itemid = '$childid'");
	# clean out pending tables
	do_sql("delete from sponsorship_pending where itemid = '$childid' and hash = $hash ");
	do_sql("commit");
	
	# log the purchase 
	logit ("Sponsorship_complete","itemid = $childid, name = {$data['first_name']} {$data['last_name']}", false);
}
if ($web == 'yes') {
$sessionid = getinput("id");
?>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<title>Child Sponsorship record sponsorships</title>
</head>
<body>
<h2 align="center">Sponsorship has been recorded.</h2>
<h2 align="center"><a href="<?php print $url ?>/admin/search_pending.php?id=<?php print $sessionid ?>">Return to Pending Sponsorships</a></h2>
</body>

<?php	
}

}
catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace, false);
}

?>