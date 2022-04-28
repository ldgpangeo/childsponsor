<?php 
try {

    include ("../../lib/r_payment_tools.php");
    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");
    
    $title = "Show Missing Payments";
 #   $header = "<script type='text/javascript' src='ajax.js'></script>";
    include "page_header.php";
    $today = time();
    #  collect list of missing payments
    $sql = "select d.*, p.type, p.datedone, p.source, p.amount, p.transactionid, r.period from recon_data d ";
    $sql .= " left join r_rules r on r.reconid = d.reconid and r.type = 'sponsorship'  ";
    $sql .= " , last_payments p ";
#    $sql .= " left join r_search s on d.civicrmid = s.civicrmid and s.source = p.source  ";
    $sql .= "where d.reconid = p.reconid and p.type='sponsorship' and p.is_active = 'Y'  and d.is_active = 'Y' order by p.source, sponsor";
    $res = do_sql($sql);
    $late_payments = array();
    while ($row = mysqli_fetch_assoc($res)) {
        #  collect search name
        $sql2 = "select name as search_name from r_search s, last_payments p ";
        $sql2 .= " where s.civicrmid = '{$row['civicrmid']}' and s.source = p.source  and  p.reconid = '{$row['reconid']}' and  p.is_active = 'Y' ";
        $res2 = do_sql($sql2);
        if ( ($res2 <> false) and (mysqli_num_rows($res2) == 1) ){
            $row['sponsor'] = mysqli_result($res2,0,'search_name');
        }
        if (($row['datedone'] <> '') and ($row['period'] <> '') ) {
            #  compute when next payment is due
            $next_payment = next_payment_date($row['datedone'], $row['period']);
            if ($today > (strtotime($next_payment) + 86400)) {
                $row['next_date'] = $next_payment;
                array_push ($late_payments, $row);
            }
            
        } else {
            # if no period or payment date then list it anyway
            array_push ($late_payments, $row);
        }
    }
    
    
?>
<H2 align="center">Sponsorships with LATE Payments</H2>
<table width="80%" border = "1" cellspacing="5" cellpadding="3" align ="center">
<tr bgcolor="#ffffcc">
<th width="20%">Sponsor</th>
<th width="20%">child</th>
<th width="10%">source</th>
<th width="10%">period</th>
<th width="10%">Date</th>
<th width="10%">next</th>
<th width="10%">amount</th>
<th width="10%">Action</th>
</tr>
<?php foreach ($late_payments as $row) {?>
<tr>
<td align="center"><?php if ($row['search_name'] <> '') { print $row['search_name'];} else { print $row['sponsor']; }?></td>
<td align="center"><?php print $row['child']?></td>
<td align="center"><?php print $row['source']?></td>
<td align="center"><?php print $row['period']?></td>
<td align="center"><?php print us_date($row['datedone'],false)?></td>
<td align="center"><?php print us_date($row['next_date'],false)?></td>
<td align="center"><?php print "$".$row['amount']?></td>
<td align="center"><a href="show_recon.php?civicrmid=<?php print $row['civicrmid']?>&itemid=<?php print $row['itemid']?>" class="myBlue">Visit</a></td>
</tr>
<?php } ?>
<tr><th colspan="8" bgcolor="#FFFFCC"><a href="index.php" class="myGreen">Return to main page</a></th></tr>
</table>
<?php
} catch(Exception $err) {
    $trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
    logerr($err->getMessage(),$err->getCode(),$trace);
    }
    
    ?>
