<?php 

/*
 * This cycles through the r_paypal_incoming table to insert into r_payments table
 * 
 */
function load_paypal_from_incoming () {
    global $hdl, $dbg ;
    
    $success = 0;        #  count of successful loads
    $duplicate = 0;      #  count of duplicate rows
    $somethingelse = 0;  # count of not a sponsorship
    $fail = 0;           #  count of failed loads
    
    #  open a cursor and loop through the r_paypal_incoming table
    $eventlog = '';
 $inres = do_sql("select * from r_paypal_incoming");
 while ($row = mysqli_fetch_assoc($inres)) {
    #  check that a row is plausible
    #  datedone not 1969, txn_id not null, gross > 0, name not null
    $good = true;
    if ( ($row['datedone'] == '1969-12-31') 
        or ($row['txn_id'] == '')
        or ($row['gross']  <= 0)
        or ($row['name']   == '')
    
        ) { $good = false; }

     if ($good) {
         #  check that it is not already loaded
         #   source = paypal, transactionid = txc_id is_active = 'Y'
         $res = do_sql("select * from r_payments where binary transactionid =  binary '{$row['transaction_id']}' and source = 'paypal' and is_active = 'Y'");
         if (mysqli_num_rows($res) >0) {
             $good = false; 
             $duplicate ++;
         }
         unset( $res );
     }
     
     # check that this is a child sponsorship
     if ($good) {
        $account = parse_item_title($row['item_title']);
       # debug ("account is $account");
        if ($account <> 'TGCA Projects:Sponsorship Programs:TGCA Child Sponsorship') { 
             $good = false;
             $somethingelse ++ ;
        }
     }
     
    #  import the row
     if ($good) {
         # check CiviCRM for the sponsorship
         $sql = "select childid,contact_id,child,display_name from cvsponsors s, r_paypal_incoming where binary '{$row['transaction_id']}' = binary trxn_id";
         $res = do_sql($sql);
         if (mysqli_num_rows($res) > 0) {
             $cvrow = mysqli_fetch_assoc($res);
             #  get the reconid
             $reconid = get_reconid($cvrow['childid'], $cvrow['contact_id'], false);
             if ($reconid === false ) {
                 #  do not auto-create reconciliation records
                 $fail ++;
                 #  test if row is already in unmatched.
                 $tst = do_sql("select * from r_paypal_unmatched where transaction_id = '{$row['transaction_id']}'");
                 if (mysqli_num_rows($tst) == 0) {
                     do_sql ("insert r_paypal_unmatched select * from r_paypal_incoming where transaction_id = '{$row['transaction_id']}'");
                 }
                 $eventlog .= "No match found for transaction {$row['transaction_id']}\n";
             } else {
             #  Write the payment data
                $res = load_a_paypal_row( $reconid, $row) ;
                # update search table
                $junk = get_search ($cvrow['contact_id'], $cvrow['display_name'], 'paypal', true);
                $success ++;
             }
         } else {
             $fail ++;
             #  test if row is already in unmatched.
             $tst = do_sql("select * from r_paypal_unmatched where transaction_id = '{$row['transaction_id']}'");
             if (mysqli_num_rows($tst) == 0) {
                do_sql ("insert r_paypal_unmatched select * from r_paypal_incoming where transaction_id = '{$row['transaction_id']}'");
             }
             $eventlog .= "No match found for transaction {$row['transaction_id']}\n";
             
         }
         
     } 
     
 }
 debug ("Paypal import result\n$eventlog");
 $results = array (
     'success' => $success,        #  count of successful loads
     'duplicate' => $duplicate,    #  count of duplicate rows
     'other' => $somethingelse,    # count of not a sponsorship
     'fail' => $fail,              #  count of failed loads
     
 );
 return $results;
 
     
}

?>