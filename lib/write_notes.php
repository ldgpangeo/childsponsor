<?php
/*
 * General purpose function to write to effective-dated table r_notes
  */

function next_noteid ($now) {
    # get the next sequence number for people
    #     this becomes people.people_id and  people_info.people_id
    global $hdl;
    $res = do_sql("insert r_noteid_seq values ( null, '$now' ) ", false);
    return mysqli_insert_id($hdl);
}


 function write_notes($data) {
 	global $login, $hdl, $dbname ;
 	
/* 	#   Security check.
	if (! isset($login) ) {
		logit('UNAUTHORIZED_ACCESS',"task = write_cots, problem = no login",true);
		throw new Exception("You must be logged in to use this.",ERROR_MAJOR);
	}
	*/
 	$login = 'ldgpangeo';
	$now = strftime('%F %T',time());
	
	
	# standard variables
	$table = "r_notes"; 
	$skip_cols = array ('seqid');
	$key_col = "noteid";
	$defaults = array(
	    'changedby' => $login,
	    'effective_start_ts' => $now,
	    'effective_end_ts' => null,
	    'author' => $login,
	    'datedone' => $now, 
	    'scope' => 'U',
	    'is_alert' => 'N', 
	    'is_active' => 'Y',
	    'note' => ''
	);
	
	# get the columns within the r_notes table
	$sql = "select COLUMN_NAME from information_schema.COLUMNS where TABLE_NAME = '$table' and TABLE_SCHEMA='$dbname'";
	$res = do_sql($sql);
	$fields = array();
	while ($row = mysqli_fetch_assoc($res)) {
	    if (! in_array($row['COLUMN_NAME'], $skip_cols)) {
	       array_push($fields, $row['COLUMN_NAME']);
	    }
	}
	$fieldlist = implode(", ", $fields);
	
	#  is this an insert or an update?
	if ((! isset($data[$key_col])) or ($data[$key_col] == '')) {
	    debug("write_notes is doing an insert.");
	    $action = "insert";
	    # load default values as prior
	    $old = $defaults;
	    # get a new noteid value
	    $old['noteid'] = next_noteid($now);
	    $data['noteid'] = $old['noteid'];
	} else {
	    $action = update;
	    #  get prior information
	    $sql = "select $fieldlist from $table where noteid = '$data[$key_col]' and effective_end_ts is null";
	    $res = do_sql($sql);
	    if (mysqli_num_rows($res) <> 1) {
	        throw new Exception("Internal Error, failed to find prior cot data", ERROR_MAJOR);
	    }
	    $old = mysqli_fetch_assoc($res);
	}
	
# Merge new information in to the old information
debug ("Starting data merge\n".dump_array($data));
	foreach ($fields as $field) {
	    switch  ($field) {
	        case "noteid" :
	            # noteid never changes
	            break;
	        case "changedby" :
	            $data[$field] = "'" . $login . "'";
	            break;
	        case "effective_start_ts" :
	            $data[$field] = "'" . $now . "'";
	            break;
	        case "effective_end_ts" :
	            $data[$field] = "null";
	            break;
	        default :
	            if (! isset($data[$field])) { $data[$field] = $old[$field]; }
	            $data[$field] = "'" . addslashes($data[$field]) . "'";
	            break;
	    }
	}
	debug ("finished data merge\n".dump_array($data));
	
    #  now write the data
    
    #  create a transaction
 	$res = do_sql("Start transaction");
 	#  if update then end date the prior rows.
 	if ($action == 'update') {
 	  $sql = "update r_notes set effective_end_ts = '$now' where noteid = '{$data['noteid']}' and effective_end_ts is null";
 	  $res = do_sql($sql);
 	}
 	#  write the new row
 	$sql = "insert r_notes ( $fieldlist ) values ( ";
 	$sep = "";
 	foreach ($fields as $field) {
 	    $sql .= $sep . $data[$field];
 	    if ($sep == '') { $sep = ", "; }
 	}
 	$sql .= ") ";
 	$res = do_sql($sql);
  	#  commit the transaction
 	$res = do_sql("commit");
 	return $data['noteid'];
 }

 
 
?>