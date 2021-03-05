<?php
ini_set('display_errors', 1);
error_reporting(E_ERROR);
require("../lib/common-init.php");


#   process form submission
if ($_POST['submit'] <> '') {
#	print "<pre>";
#	print_r($_POST);
#	print "</pre>";
	$dbg = false;
#	if ($dbg) {print "Submit started.<br>\n";}
	$tmparray = explode("|", $_POST['names'] );
#	$target = array ("/ /", "/\./");
#	$replace = array ("_", "_");
	foreach ($tmparray as $name) {
#		$contact = preg_replace($target, $replace, $name);
#		$contact = addslashes($contact);
		$id = $_POST[$name];
		if ($dbg) { print "Processing $contact with id $id<br>\n";}
		if ($id <> '') {
			$sql = "update sponsorships set contactid = '$id' where id = '$name' limit 1";
			if ($dbg) { print "$sql<br>\n";}
			$res = mysqli_query($hdl,$sql);
			if ($res === false) {die ("fatal sql error");}
		}
	}
}

#  Load civicrm libraries
require_once "$wordpress_base/wp-content/uploads/civicrm/civicrm.settings.php";
require_once "$wordpress_base/wp-content/plugins/civicrm/civicrm/api/api.php";
	define('ABSPATH' , "//var/www/sites/www.thegivingcircle.org/html/wordpress/");
	define('WPINC' , "wp-includes");


# create result set of names with no match found
$sql = "select id, last_name, first_name, email, itemid from sponsorships where contactid is null and effective_end_ts is null order by last_name  limit 20 ;";
$res = mysqli_query($hdl, "$sql");
if (!$res) {die("Bad data sql.  $sql"); }

?>
<form method="post" action="">
<table width="95%" border="1" cellpadding="3" cellspacing="3" align="center">
<?php
$names = array ();
while ($row = mysqli_fetch_assoc($res)) {
	$row['contact'] = $row['last_name']. ", ".$row['first_name'];
	array_push($names, $row['id']);
	#  get corresponding civicrm entries
	debug ("Checking ".$row['contact']);
	try{
   		$contacts = civicrm_api3('contact', 'get', array(
      			'sequential' => 1,
      			'last_name'   =>  $row['last_name'],
      			'first_name'  =>  $row['first_name']
   		));
		}
		catch (CiviCRM_API3_Exception $e) {
	   		$error = $e->getMessage();
		}
		debug("civicrm found\n".dump_array($contacts));
	$count = $contacts['count'];
	if ($count == 0) {
		try{
   			$contacts = civicrm_api3('contact', 'get', array(
      				'sequential' => 1,
      				'last_name'   =>  $row['last_name']
   			));
			}
			catch (CiviCRM_API3_Exception $e) {
		   		$error = $e->getMessage();
			}
		$count = $contacts['count'];		
	}
	
	if ($count == 0) {
		$count = 1;
		$found = false;
		$contacts = array('values' => array ("sort_name" => "no match"));		
	} else { $found = true;}

	print "<tr><td rowspan=\"$count\">".$row['first_name']." ".$row['last_name']. " ({$row['email']})";
	if ($found) {
		print "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type = \"radio\" name = \"{$row['id']}\" value=\"-1\"> No Match";
	}
	$first = null;
    foreach ($contacts['values'] as $cvitem) {
    	if ($found) {
	    	print "$first <td> <input type = \"radio\" name = \"{$row['id']}\" value=\"{$cvitem['contact_id']}\" ";
	    	if ($count == 1) { print "checked ";}
	    	print "> {$cvitem['first_name']}  {$cvitem['last_name']} ({$cvitem['contact_id']})</td></tr>\n";
		} else {
	    	print "$first <td> <input type = \"radio\" name = \"{$row['id']}\" value=\"-1\" checked> Not Found </td></tr>\n";			
		}
		if($first == null) {$first = "<tr> ";}	   
    }
	print "</tr>\n";
    }
	$tmp = join("|",$names);
	print "<input type=\"hidden\" name= \"names\" value=\"$tmp\">\n";

?>
<tr>
	<th colspan="2"><input type="submit" name="submit" value="submit"></th>
</table>
</form>
