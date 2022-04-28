<?php 
try {

    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");
    
    $title = "Show Missing Payments";
 #   $header = "<script type='text/javascript' src='ajax.js'></script>";
    include "page_header.php";
    $today = time();

    # collect list of alerts
    $sql = "select noteid,effective_start_ts,n.reconid,note,s.sponsor,s.child, r.civicrmid, r.itemid";
    $sql .= " from r_notes n, r_recon r, sponsorship_summary s ";
    $sql .= " where effective_end_ts is null and n.is_active='Y' and is_alert='Y' and r.is_active='Y'  ";
    $sql .= " and n.reconid = r.reconid and n.reconid = s.reconid";
    $sql .= " order by effective_start_ts desc";
    $res = do_sql($sql);
    if (mysqli_num_rows($res) == 0) {
    ?>
    <h2 align="center">There are no alerts to report</h2>
    <?php 
    } else {
    ?>
    <H2 align="center">Sponsorship Alerts</H2>
<table width="80%" border = "1" cellspacing="5" cellpadding="3" align ="center">
<tr bgcolor="#ffffcc">
<th width="15%">Sponsor</th>
<th width="15%">Child</th>
<th width="10%">Last Alert</th>
<th width="60%">message</th>
<th width="10%">Action</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($res)) {?>  
<tr>  
<td><?php print stripslashes($row['sponsor'])?> </td>
<td><?php print stripslashes($row['child'])?> </td>
<td><?php print us_date($row['effective_start_ts'],false)?> </td>
<td><?php print stripslashes($row['note'])?> </td>
<td align="center"><a href="show_recon.php?civicrmid=<?php print $row['civicrmid']?>&itemid=<?php print $row['itemid']?>" class="myBlue">Visit</a></td>
</tr>

    <?php 
    }  # end loop through alerts
    ?>
    <tr><th colspan="5" bgcolor="#FFFFCC"><a href="index.php" class="myGreen">Return to main page</a></th></tr>
    </table>
    <?php 
    }  # end alerts were found
    ?>
    
    <?php
} catch(Exception $err) {
    $trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
    logerr($err->getMessage(),$err->getCode(),$trace);
    }
    
    ?>
    