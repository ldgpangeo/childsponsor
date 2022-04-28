<?php 
try {

    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");
$reconid = getinput("reconid", null);
if ($reconid == null) {
    throw NEW Exception("No recon id provided.",ERROR_MAJOR);
}
$sql = "select * from r_recon where reconid = '$reconid'";
$res = do_sql($sql);
if (mysqli_num_rows($res) <> 1) {
    throw NEW Exception("Failed to find a unique recon record.",ERROR_MAJOR);
}
$row = mysqli_fetch_assoc($res);
if ($row['is_active'] <> 'Y') {
    throw NEW Exception("This recon is already inactive.", ERROR_MINOR);
}

#  -------------------------------------------  start form processing  ----------------------

if ($_POST['submit'] <> '') {
    $errlog = '';
    $in = form_validate("cancel", $errlog);
    if ($errlog <> '') {
        throw NEW Exception("The following errors were found:<br />$errlog", ERROR_MINOR);
    }
    $sql = "update r_recon set is_active = 'N', status = '{$in['status']}' where reconid = '$reconid' limit 1";
    $res = do_sql($sql);
    $redirect = "index.php";
}

#  -------------------------------------------   End form processing   ----------------------

    $title = "Cancel a sponsorship";
 #   $header = "<script type='text/javascript' src='ajax.js'></script>";
    include "page_header.php";
    
?>
<form action="" method = "POST">
<h2 align="center">Caution!  You are about to terminate a sponsorship reconciliation.</h2>

<h3>Reason for this termination: <input type ="text" size="80" name="status"> </h3>
<p align="center">
<a href="index.php" class = "myGreen" >Cancel:  GO BACK</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="hidden" name="reconid" value = "<?php print $reconid ?>">
<input type="submit" name="submit" value="Yes TERMINATE" class="MyRed">
</p>
</form> 
<?php
} catch(Exception $err) {
    $trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
    logerr($err->getMessage(),$err->getCode(),$trace);
    }
    
    ?>
