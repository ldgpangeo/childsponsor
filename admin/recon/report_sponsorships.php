<?php 
/*
 *   This generates the sponsorship payment spreadsheet for the quarterly wire transfer.
 */

try {

    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");

    # get the credit card discount
    $fee = 1 - get_dictionary_setting( 'credit_card', 'fees', 0);
    debug ("Credit card fee is $fee");
    #  establish date parameters
    $today = date_parse(strftime('%F'));
    $year = $today['year'];
    $month = $today['month'] -1;
    debug ("Today is year $year and month $month");
    $start_month = strftime('%F', mktime('0','0','0',$month, '1', $year) );
    $start_annual = strftime('%F', mktime('0','0','0',$month+1, '1', ($year -1) ) );
    $start_semi     = strftime('%F', mktime('0','0','0',($month - 5), '1', $year) );
    $start_quarter  = strftime('%F', mktime('0','0','0',($month - 2), '1', $year) );
    $end_date       = strftime('%F', mktime('0','0','0',($month +1), '1', $year) );
    $start_date     = $start_month;
    
    debug ("Start date is $start_date, End Date is $end_date");
    debug ("Annual: $start_annual, Semi: $start_semi, Quarter: $start_quarter");

    $report = array();
    $cols = array('reconid','child','sponsor','type','frequency','last_payment','last_amount','total_amount','net_amount','comments');
    
    # create a data array of payments that need processing
    $sql = "select p.reconid, p.type,  period ,comment from r_payments p ";
    $sql .= " left join r_rules r on r.reconid = p.reconid and r.type = p.type ";
    $sql .= " left join r_spreadsheet_comments c on r.reconid = c.reconid and c.effective_end_ts is null "; 
    $sql .= " where is_active = 'Y' ";
    $sql .= " and datedone >= '$start_annual' and datedone < '$end_date'  group by reconid, type order by reconid,type";
    $res = do_sql($sql);
    
    
    #  cycle through these to construct payment report
    while ($items = mysqli_fetch_assoc($res) ) {
       # collect personal info for this reconid
       $sql = "select sponsor, child , amount last_amount from sponsorship_summary2 s ";
       $sql .= " left join last_payments p on s.reconid = p.reconid and p.type = '{$items['type']}' ";
       $sql .= " where s.reconid = '{$items['reconid']}'";
       $res2= do_sql($sql, true, false);
       if (mysqli_num_rows($res2) == 1) {
           $sponsor = mysqli_result($res2, 0, 'sponsor');
           $child = mysqli_result($res2, 0, 'child');
           $last_amount = mysqli_result($res2, 0, 'last_amount');
       } else {
           $sponsor = "";
           $last_amount = "";
       }
       $fragment = '';
       switch ( strtolower($items['period']) ) {
           case "annual" :
           case "annually" :
               $start = $start_annual;
               $mult = .25;
               break;
           case "month" :
           case "monthly" :
               $start = $start_month;
               $mult = 3;
               break;
               
           case "6 months" :
           case "semiannual" :
           case "semiannually" :
               $start = $start_semi;
               $mult = .5;
               break;
           case "Quarter" :
           case "quarterly" :
           case "3 months" :
           default;
               $start = $start_quarter;
               $mult = 1;
       }
       #  collect the payment amounts
       $sql = "select sum(amount) total_amount, max(datedone) as last_payment from r_payments where reconid = '{$items['reconid']}' and type = '{$items['type']}' ";
       $sql .= " and datedone >= '$start' and datedone < '$end_date' group by reconid, type ";
       $res3 = do_sql($sql, true, false);
       if (mysqli_num_rows($res3) == 1) {
           $last_payment = mysqli_result($res3, 0, 'last_payment');
           $total_payment = mysqli_result($res3, 0, 'total_amount');
       } else {
           $last_payment = "";
           $total_payment = 0;
       }
       #  save the data
       $data = array(
           "reconid" => $items['reconid'],
           "child" => $child ,
           "sponsor" => $sponsor, 
           "type" => $items['type'],
           "frequency" => $items['period'],
           "last_payment" => $last_payment,
           "last_amount" => $last_amount,
           "total_payment" => $total_payment* $mult,
           "net_amount" => round($total_payment * $mult * $fee,0) ,
           "comment" => html_entity_decode(stripslashes($items['comment'])) ,
           
       );
#       debug ("data array is\n".dump_array($data));
       
       if ( ($total_payment > 0) or ($items['type'] == 'sponsorship') ) {
            array_push($report,$data);
       }
    }
    
        
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename="payment_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $cols );     #   report the column names
    foreach ($report as $row) {
        fputcsv ($output, $row);
    }
    fclose ($output);
?>

    

<?php
} catch(Exception $err) {
    $trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
    logerr($err->getMessage(),$err->getCode(),$trace);
    }
    
    ?>
