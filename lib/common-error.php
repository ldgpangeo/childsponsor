<?php
#  Error handling tools

define ("ERROR_MINOR" , 1);                         # Errors that do not require software support
define ("ERROR_MINOR_NORETURN" , 2);       # Errors that do not require software support
define ("ERROR_MAJOR" , 3);                        # Substational errors
define ("ERROR_MAJOR_NORETURN" , 4);      # Substantial errors
define ("ERROR_SEVERE" , 5);                       # Show stoppers
define ("ERROR_SEVERE_NORETURN" , 6);     # Show stoppers

function logerr($message,$severity,$trace, $display = true) {
	global $webroot,$uid, $hdl;
	if ($object_id == '') { $object_id = -1;}
	debug ("error message is $message and severity is $severity and trace is \n$trace");
   	list($public, $private) = explode ("|", $message,2);
   	if ($private == '') { $private = $public;}
	$path = mysqli_real_escape_string($hdl,$trace);
	$msg = trim(mysqli_real_escape_string($hdl,$private));
	$now = strftime('%Y-%m-%d %T');
	$ip = $_SERVER['REMOTE_ADDR'];
	$sql = "insert into errors (datedone,message,trace,severity,ip) values
	           ('$now','$msg','$path','$severity','$ip')";
	debug ($sql);
        mysqli_query($hdl, $sql);
        if($display){
        error_page($message,$severity);
        }
}

#  display user error message
#  If severity is even, offer a back button
function error_page($msg,$severity=1) {
	global $path,$webroot,$sessionid;
	$title = "Error Page";
 include_once "$path/template/header.php";
	?>
   
   <h1 align="center">Error page</h1>
   <h2 align="left">The following error(s) were encountered:</h2>
   <ul>
   <?php 
   $referrer = $_SERVER['PHP_SELF'];
   debug ("referrer is $referrer");
   if (strpos($referrer, '/admin/') === false) {
   		$parts = explode("|",$msg);
   		print $parts[0] ;
   } else {
   	   	print $msg;
   }
   ?>
   </ul>
   <?php
   #  if severity is even, offer the BACK button to correct the problem.
   if (($severity % 2) <> 0) {
   ?>
   <p>Please go back and correct this problem before continuing.</p>
   <form method="post" action="">
   <p>
   <input type="button" name="back" value="Back" onClick="history.back()"> &nbsp;&nbsp;&nbsp; 
    </p>
  </form>
  <?php } else { ?>
   <p><a href = "<?php print $webroot ?>/index.php?id=<?php print $sessionid ?>">Continue</a></p>
  <?php } ?> 

   <pre>
   <?php # print var_dump($_POST) ?>
   </pre>
<?php
include_once("$path/template/footer.php");
#	die ("Fatal error encountered.\n");
}
?>