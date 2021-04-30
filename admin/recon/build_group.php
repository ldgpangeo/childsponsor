<?php 
/*
 *  
 *  

To get internal id of a group
select id from civicrm_group where title = 'childsponsor-mailing'

to empty a group
delete from civicrm_group_contact where group_id = '45'   (45 is internal id of group)

to add to a group
insert civicrm_group_contact (group_id, contact_id, status) values ('45', 'nnnn', 'Added');



 */


try {

    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");

    #   get the group id
    $sql = "select id from $cv.civicrm_group where title = 'childsponsor-mailing'";
    $res = do_sql($sql);
    $groupid = mysqli_result($res, 0, 'id');
    if ($groupid <1) {throw new EXCEPTION ("Failed to find a group id", ERROR_MAJOR); }
    
    #  empty the group
    $sql = "delete from $cv.civicrm_group_contact where group_id = '$groupid'";
    $res = do_sql($sql);
    if ($res === false) {throw new EXCEPTION ("Failed to empty the group", ERROR_MAJOR); }
    
    #  create a cursor of current civicrm sponsors
    $sql = "select distinct civicrmid from r_recon where is_active = 'Y' order by civicrmid";
    $res = do_sql($sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $sql = "insert $cv.civicrm_group_contact (group_id, contact_id, status) values ('$groupid', '{$row['civicrmid']}', 'Added');";
        $res2 = do_sql($sql);
        if ($res === false) {throw new EXCEPTION ("group insertion failed for {$row['civicrmid']}", ERROR_MAJOR); }
        
    }
    
    
    $title = "Show Reconciliation";
 #   $header = "<script type='text/javascript' src='ajax.js'></script>";
    include "page_header.php";
    
?>
<?php
} catch(Exception $err) {
    $trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
    logerr($err->getMessage(),$err->getCode(),$trace);
    }
    
    ?>
