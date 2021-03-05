<?php
#  set this to true to write debug messages to human-debug.log
#  warning:   remember to set url value in dictionary.


#$mode = "dev";   <-- mode now comes from common-init-dev or common-init-prod 
$version="3.0 (12/20/15)"; 

date_default_timezone_set('America/New_York');

switch ($mode) {
	case "dev" :
		ini_set('display_errors', 1);
		error_reporting(E_ERROR);
		$dbg = true;                            #  write debug messages to the database
		$webroot = "/childsponsor";         #  set to web path for the site
		$url = "http://tgc.t4pg.com".$webroot;     
		$email_url = "http://tgc.t4pg.com/childsponsor/mail-assets";  
		$dbhost = "localhost";                  #  location of mysql server
		$dbuser = "sponsoruser1";                       #  database username
		$dbpassword = "*20AA8AFA6498C6CC04DD6A14ACC7D8D4F845BDED";                       #  database password
		$dbname = "15983_sponsor";                     #  name of database
		$civicrm_base = "http://tgc.t4pg.com/wordpress";
		$civicrm_url="$civicrm_base/sponsor-a-child";
		$cv = "civicrm";
		$civicrm_form = urlencode($civicrm_url);
		$wordpress_base = "/web/wordpress";
		break;
	case "test" : 
		#  these settings are for pangeo.homedns.org
		ini_set('display_errors', 0);
		$dbg = true;
		die("Not configured for test");
		break;
	case "prod" :
		#  these settings are for hosted service  www.thegivingcircle.org
		ini_set('display_errors', 1);
                $dbg = true;
		error_reporting(E_ERROR);
		$dbg = true;                   #  force debug on
		$webroot = "/childsponsor";
		$url = "https://www.thegivingcircle.org".$webroot;
		$email_url = "https://www.thegivingcircle.org/bricks/mail-assets";      
		$dbhost = "localhost";
		$dbuser = "tgcwordpress1";
		$dbpassword = "*55FC4F6F8051CBF1B79505F867DC068204117CB5";
		$dbname = "15983_sponsor";
		if ($cms == "wordpress") {
			$civicrm_base = "https://www.thegivingcircle.org/wordpress";
			$civicrm_url="$civicrm_base/sponsor-a-child?";
		}
		$cv="15983_tgc_wordpress_civicrm";
		$civicrm_form = urlencode($civicrm_url);
		$wordpress_base = "/home/geoffrion15983/public_html/wordpress";
		$SandboxFlag = false;
		break;

	default:
		$dbg = false;
		$path = "/web";
		$dbhost = "localhost";
		$dbuser = "root"; 
		$dbpassword = "";
		$dbname = "15983_bricks";
}

include_once("$path/lib/common-debug.php");
include_once("$path/lib/common-error.php");
include_once("$path/lib/common-mysql.php");
include_once("$path/lib/common-forms.php");
include_once("$path/lib/common-security.php");
include_once("$path/lib/common.php");

# database default values

$hdl = dbopen();


# Load specific messages into an array
$contents = dictionary_as_array('content');
#debug("content is \n".dump_array($contents));


?>
