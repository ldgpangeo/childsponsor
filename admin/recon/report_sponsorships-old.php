<?php 
/*
 *   This generates the sponsorship payment spreadsheet for the quarterly wire transfer.
 */

try {

    include_once("php_header.php");
#    include_once("../../lib/r_payment_tools.php");
  
    #  establish date parameters
    $today = date_parse(strftime('%F'));
    $year = $today['year'];
    $month = $today['month'] -1;
    debug ("Today is year $year and month $month");
    $start_month = strftime('%F', mktime('0','0','0',$month, '1', $year) );
    $start_annual = strftime('%F', mktime('0','0','0',$month, '1', ($year -1) ) );
    $start_semi     = strftime('%F', mktime('0','0','0',($month - 5), '1', $year) );
    $start_quarter  = strftime('%F', mktime('0','0','0',($month - 2), '1', $year) );
    $end_date       = strftime('%F', mktime('0','0','0',($month +1), '-1', $year) );
    $start_date     = $start_month;
    
    debug ("Start date is $start_date, End Date is $end_date");
    debug ("Annual: $start_annual, Semi: $start_semi, Quarter: $start_quarter");
  
    $sql = " select l.reconid, d.child, d.sponsor, d.last_name, d.email, l.type, l.datedone as last_payment, l.amount, r.period as frequency  ";
    $sql .= " from last_payments l left join r_rules r on r.reconid = l.reconid and r.type = 'sponsorship' ";
    $sql .= " left join recon_data d on d.reconid = l.reconid ";
    $sql .= " where l.is_active = 'Y' order by last_name ";
    $res = do_sql($sql);
    #  collect the column info
    $finfo = mysqli_fetch_fields($res);
    $cols = array ();
    foreach ($finfo as $col) {
        array_push($cols, $col->name);
    }
    $payments = array();
    $payment = 0;
    array_push ($cols, 'total_amount');
       while ($row = mysqli_fetch_assoc($res)) {
        switch (strtolower($row['frequency']))  {
            case "annual" :
            case "annually" :
                $start = $start_annual;
                $payment = $row['amount'] / 4;
                break;
            case "month" :
            case "monthly" :
                $start = $start_month;
                $payment = $row['amount'] * 3;
                break;
            case "6 months" :
            case "semiannual" :
            case "semiannually" :
                $start = $start_semi;
                $payment = $row['amount'] / 2;
                break;
            case "Quarter" :
            case "quarterly" :
            case "3 months" :
                $start = $start_quarter;
                $payment = $row['amount'];
                break;
            default :
                $start = $start_month;
                if ($row['type'] == 'sponsorship') {
                    $payment = $row['amount'] * 3;
                } else {
                    $payment = $row['amount'];
                }
        }
        $tmppmt = strtotime($row['last_payment']);
        if ( ($tmppmt >= strtotime($start) ) and ($tmppmt <= strtotime($end_date) ) ){
            $row['total_amount'] = $payment ;
            array_push ($payments, $row);
            
        }
    }
    
#    $fp = fopen ('php://output', 'w');
#    fputcsv($fp, $payments);
#    fclose ($fp);
    
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename="payment_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $cols);     #   report the column names
    foreach ($payments as $row) {
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
