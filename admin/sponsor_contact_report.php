<?php 
/*
 * This report created for Erin Walborn to consolidate all information about sponsors into one location.
 * 
 */
try {
include "../lib/common-init.php";

# generate the report
$sql = "select cv.* ,i.groupid, concat(cv.contact_id,'_',cv.childid) as 'key' from cvsponsors cv, sponsorships s, items i, ".
    "( select childid, max(entity_id) as latest, contact_id from cvsponsors group by childid, contact_id) m " .
    " where m.latest = cv.entity_id and m.contact_id = s.contactid and s.effective_end_ts is null and i.itemid = s.itemid " .
    " and i.groupid < 99 and i.childid = cv.childid and i.is_active = 'Y' order by sort_name, child";

$res = do_sql($sql);
$data = array();
while ( $row = mysqli_fetch_assoc($res) ) {
    $data[$row['key']] = $row;
    $contact_id = $row['contact_id'];
    #  collect all email addresses
    $query = "select email,is_primary, is_billing, location_type_id  from civicrm_email where contact_id = '$contact_id' " .
        " and on_hold = 0 order by is_primary desc";
    $eres = do_sql($sql);
    
    $sep = null;
    while ($erow = mysqli_fetch_assoc($eres) ) {
        switch ($erow['location_type_id']) {
            case 1 :
                $erow['location_type'] = 'home';
                break;
            case 2 :
                $erow['location_type'] = 'work';
                break;
            case 3 :
                $erow['location_type'] = 'main';
                break;
            case 4 :
                $erow['location_type'] = 'other';
                break;
            case 5 :
                $erow['location_type'] = 'billing';
                break;
            default:
                $erow['location_type'] = 'unknown';       
        }
        $email .= $sep . $erow['location_type'] .": ". $erow['email'] ;
        if ($sep == null) { $sep = "\n"; }
    }
    $data[$row['key']]['emails'] = $email;
}


print $sql . "\n";
die ();

/*
 $sql = "select cv.* ,s.hash as c_hash  from cvsponsors cv, sponsorships s, items i, ".
    "( select childid, max(entity_id) as latest, contact_id from cvsponsors group by childid, contact_id) m " .
    "( select childid, min(entity_id) as first, contact_id from cvsponsors group by childid, contact_id) f " .
    " where m.latest = cv.entity_id and m.contact_id = s.contactid " .
    " and f.first = cv.entity_id and f.contact_id = s.contactid " .
    " and s.effective_end_ts is null and i.itemid = s.itemid " .
    " and i.groupid < 99 and i.childid = cv.childid and i.is_active = 'Y'";
*/
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
	<th colspan="8">Sponsor contact information </th>
</tr>


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


?>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}
?>
