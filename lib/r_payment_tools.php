<?php 

function get_reconid ($itemid, $civicrmid, $create=false) {
    global $hdl, $dbg;
    if (($itemid == '') or ($civicrmid == '') or ($itemid == null) or ($civicrmid == null)) { return false; }
    
    $reconid = 0;
    #  test if recon already exists
    $sql = "select reconid from r_recon where itemid = '$itemid' and civicrmid = '$civicrmid' and is_active = 'Y' ";
    $res = do_sql($sql);
    if (mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $reconid = $row['reconid'];
    } elseif ($create = true) {
        $sql = "insert r_recon(itemid,civicrmid,is_active) values ('$itemid', '$civicrmid', 'Y')";
        $sql .= " on duplicate key update itemid = '$itemid' ";
        $res = do_sql($sql);
        $reconid = mysqli_insert_id($hdl);
    }
    if ($reconid > 0) {
        return $reconid;
    } else {
        return false;
    }
}

function get_names ($itemid, $name, $source, $create=false) {
    global $hdl, $dbg;
    #  clean up name
    if ($name <> '') {
        $name = trim( str_replace('\n','',$name) );
    }
    if ($source == '') {
        $create = false;
        $snippet = "";
    } else {
        $snippet = " and source = '$source' ";
    }
    $out = array();
    
    #  return all itemids
    if ($itemid == '') { 
        $create = false;
        $sql = "select itemid, source from r_names where name = '$name' $snippet";
        $res = do_sql($sql);
        while ($tmp= mysqli_fetch_array($res)) {
            array_push($out,$tmp);
        }
        return $out;
    }
    
    # return all names
    if ($name == '') {
        $create = false;
        $sql = "select name, source from r_names where itemid = '$itemid' $snippet";
        $res = do_sql($sql);
        while ($tmp= mysqli_fetch_array($res)) {
            array_push($out,$tmp);
        }
        return $out;
    }
    
    #  populate the names table
    if ($create) {
        $tmp = addslashes($name);
        #  test if row exists
        $sql = "select * from r_names where itemid = '$itemid' and name = '$tmp' and source = '$source'";
        $res = do_sql($sql);
        if (mysqli_num_rows($res) == 0) {
            $sql = "insert r_names (itemid,name,source, is_preferred) values ('$itemid','$tmp','$source','Y') ";
            $sql .= " on duplicate key update itemid = '$itemid' ";
            $res = do_sql($sql);
        }
    }
}

function get_search ($civicrmid, $name, $source, $create=false) {
    global $hdl, $dbg;
    #  clean up name
    debug ("get_search called, civicrmid = $civicrmid, name=$name, source = $source ");
    if ($name <> '') {
        $name = trim( str_replace('\n','',$name) );
    }
    if ($source == '') {
        $create = false;
        $snippet = "";
    } else {
        $snippet = " and source = '$source' ";
    }
    $out = array();
    
    #  return all civicrm ids
    if ($civicrmid == '') {
        $create = false;
        $sql = "select civicrmid, source from r_search where name = '$name' $snippet";
        $res = do_sql($sql);
        while ($tmp= mysqli_fetch_array($res)) {
            array_push($out,$tmp);
        }
        return $out;
    }
    
    # return all names
    if ($name == '') {
        $create = false;
        $sql = "select name, source from r_search where civicrmid = '$civicrmid' $snippet";
        $res = do_sql($sql);
        while ($tmp= mysqli_fetch_array($res)) {
            array_push($out,$tmp);
        }
        return $out;
    }
    
    #  populate the search table
    if ($create) {
        $tmp = addslashes($name);
        #  test if row exists
        $sql = "select * from r_search where civicrmid = '$civicrmid' and name = '$tmp'and source = '$source'";
        $res = do_sql($sql);
        if (mysqli_num_rows($res) == 0) {
            $sql = "insert r_search (civicrmid,name,source) values ('$civicrmid','$tmp','$source') ";
            $sql .= " on duplicate key update civicrmid = '$civicrmid' ";
            $res = do_sql($sql);
        }
    }
}

function next_payment_date ($last, $period) {
    $parts = date_parse($last);
    switch (strtolower($period)) {
        case "month" :
        case "monthly" :
            $parts['month'] ++;
            break;
        case "annual" :
        case "annually" :
            $parts['year'] ++ ;
            break;
        case "quarterly" :
        case "3 months"  :
            $parts['month'] = $parts['month'] + 3;
            break;
        case "semiannual" :
        case "semi-annual" :
        case "semiannually" :
        case "semi-annually" :
        case "6 months"  :
            $parts['month'] = $parts['month'] + 6;
            break;
        default:
            return false;
    }
#    debug ("$last created Make time array is \n".dump_array($parts) );
    $new =  mktime($parts['hour'], $parts['minute'], $parts['second'], $parts['month'], $parts['day'], $parts['year']);
    return strftime('%F', $new);
}

function parse_item_title($title) {
    if ($title == '') { return false; }
    
    # load in translation map
    $sql = "select pattern, account from r_account_mapping where is_active = 'Y'";
    $res = do_sql($sql);
    $patterns = array();
    while ($row = mysqli_fetch_assoc($res)) {
        $patterns[$row['pattern']] = $row['account'];
    }
    #  scan list for a match
    foreach (array_keys($patterns) as $pattern) {
        if (! (stripos($title, $pattern) === false) ) {
            return $patterns[$pattern];
        }
    }
    return false;
}

function load_a_paypal_row($reconid, $data) {
 /*
  * Input must an array mathing a row in r_paypal_incoming (r_paypal_unmatched is same format)
  * The corresponding recon definition must exist.
  * 
  * This populates the r_payments table.
  * 
  */ 
    debug ("load_a_paypal_row data is \n", dump_array($data));
    #  reconid must exist.
    $res = do_sql("select * from r_recon where reconid = '$reconid'");
    if (mysqli_num_rows($res) <> 1) {
        return false;
    }
    #  check that it is not already loaded
    #   source = paypal, transactionid = txc_id is_active = 'Y'
    $res = do_sql("select * from r_payments where binary transactionid =  binary '{$row['transaction_id']}' and source = 'paypal' and is_active = 'Y'");
    if (mysqli_num_rows($res) >0) {
        return false; 
    }
    #  do the insertion
    if ($data['type'] == "Subscription Payment") { $data['type'] = "sponsorship"; }
    $sql = "insert r_payments (reconid, type, datedone, source, amount, transactionid, is_active) values ( ";
    $sql .= "'$reconid', '{$data['type']}', '{$data['datedone']}', 'paypal', '{$data['gross']}', '{$data['transaction_id']}', 'Y' )";
    $res = do_sql($sql);
    return true;
 }
?>