<?php
function dbopen() {
	global $dbhost,$dbuser,$dbpassword,$dbname;
	$hdl = mysqli_connect($dbhost,$dbuser,$dbpassword, $dbname);
	if (!$hdl) {throw new Exception("Unable to connect to database."); }
	$res = mysqli_query($hdl, "set time_zone = '-5:00'");
	if (!$res) {throw new Exception("Unable to set time zone."); }
	return $hdl;
}

function do_sql($cmd, $raise = true,$d = true)
{
	global $hdl, $dbg;
	if ($dbg and $d) {
		debug($cmd);
	}
	$res = mysqli_query($hdl, $cmd);
	if (! $res)  {
		mysqli_query($hdl, "rollback");
		if ($raise) {
			throw new Exception("Internal error| SQL ERROR: $cmd",ERROR_MAJOR_NORETURN);
		} else {
			return false;
		}
	}
	return $res;
}

function us_date($date) {
	if ($date == null) { return "unknown";}
	if ($date == '0000-00-00') { return "unknown";}
	$tmp = strftime('%m/%d/%y %r',strtotime($date));
	list($day,$hour) = explode(" ",$tmp,2);
	if ($day == "12/31/69") { return null;}
	if ($hour == "12:00:00 AM") {
		return $day;
	} else {
		return $tmp;
	}
}

function dbdate($date) {
	return strftime('%Y-%m-%d %T',strtotime($date));
}


#   function to write effective_dated_table

function write_admins($uid, $data) {
	global $hdl;
	debug("write admins starting for uid = $uid");
	# start transaction
	do_sql("start transaction");
	# determine if a row exists
	$sql = "select * from admins where uid = '$uid' and effective_end_ts is null order by effective_start_ts";
	$res = do_sql($sql);
	$colct = mysqli_num_fields($res);
	$colnames = array();
	for ($i = 0; $i < $colct; $i ++) {
		$tmparray = mysqli_fetch_field_direct($res, $i);
		array_push($colnames, $tmparray->name );
	}
	debug ("colnames contains\n".dump_array($colnames));
	$tmp = mysqli_num_rows($res);
	debug("uid $uid has $tmp rows");
	if ($tmp > 0) {
		# load the active row
		$row = mysqli_fetch_assoc($res);
		# clear prior rows.
		$now = strftime('%Y-%m-%d %T');
		do_sql("update admins set effective_end_ts = '$now' where uid = '$uid' and effective_end_ts is null");
		 
	}
	# populate all fields
	debug("admins data is \n".dump_array($data));
	foreach ($colnames as $field) {
		if (! isset($data[$field])) {
			if (isset($row[$field])) { 
				$data[$field] = $row[$field]; 
			} else {
				$data[$field] = $def_admins[$field];
			}
		}
	}
	#  clear the sequence key
	$data['adminid'] = null;
	#  set the timestamps
	$data['effective_start_ts'] = strftime('%Y-%m-%d %T');
	$data['effective_end_ts']   = null;
	#  if enablins, then clear the end_ts field
	if (($data['status'] == 'Y') and ($data['end_ts'] <> null) ) {
		$data['end_ts'] = null;
	} 
	#  if disabling then set the end_ts field
	if (($data['status'] == 'N') and ($data['end_ts'] == null) ) {
		$data['end_ts'] = strftime('%Y-%m-%d %T');
	} 
	debug("starting to create sql command");
	# write new row.
	$fieldstring = implode(",",$colnames);
	debug("fieldstring is $fieldstring");
	$sql = "insert admins ( $fieldstring ) values (";
	$sep = "";
	debug ("SQL is $sql");
	foreach ($colnames as $field) {
		if (($data[$field] <> null) and (strtolower($data[$field]) <> 'null')) {
			$sql .= $sep .  "'".$data[$field]."'";
		} else {
			$sql .= $sep . "null";
		}
		if ($sep == "") { $sep = ", "; }
			debug ("SQL is $sql");
		
	}
	$sql .= " )";
	debug ("SQL is $sql");
	$res = do_sql($sql);
	$newid = mysqli_insert_id($hdl);
        # commit the changes
        do_sql("commit");
        return $newid;
}

function write_sponsorships($childid, $data, $update_only = false) {
	global $def_sponsorships, $hdl;
	# start transaction
	do_sql("start transaction");
	# determine if a row exists
	# use hash if it exists
	if ($data['hash'] <> null) {
	    $sql = "select * from sponsorships where itemid = '$childid' and hash = '{$data['hash']}' and effective_end_ts is null order by effective_start_ts";
	} else {
	   $sql = "select * from sponsorships where itemid = '$childid' and effective_end_ts is null order by effective_start_ts";
	}
	$res = do_sql($sql);
	if ((mysqli_num_rows($res) == 0) and ($update_only)) {
		return false;
	}
	$colct = mysqli_num_fields($res);
	$colnames = array();
	for ($i = 0; $i < $colct; $i ++) {
		array_push($colnames, mysqli_fetch_field($res)->name);
	}
	if (mysqli_num_rows($res) > 0) {
		# clear prior rows.
		while ($row = mysqli_fetch_assoc($res)) {
			$old = $row;
			$now = strftime('%Y-%m-%d %T');
			do_sql("update sponsorships set effective_end_ts = '$now' where id = {$row['id']}");
		}		 
	}
	# populate all fields
	$data['itemid'] = $childid;
	foreach ($colnames as $field) {
		debug("processing $field, data is {$data[$field]}, old is {$old[$field]}, default is {$def_sponsorships[$field]}");
		if (! isset($data[$field])) {
			if (isset($old[$field])) { 
				$data[$field] = $old[$field]; 
			} else {
				$data[$field] = $def_sponsorships[$field];
			}
		}
	}
	#  clear the sequence key
	$data['id'] = null;
	#  set the starting time
	$data['effective_start_ts'] = strftime('%Y-%m-%d %T');
	# write new row.
	$colstring = implode(", ",$colnames);
	$sql = "insert sponsorships ($colstring) values (";
	$sep = "";
	foreach ($colnames as $field) {
		if ($data[$field] <> null) {
			$sql .= $sep .  "'".$data[$field]."'";
		} else {
			$sql .= $sep . "null";
		}
		if ($sep == "") { $sep = ", "; }
	}
	$sql .= " )";
	$res = do_sql($sql);
	$newid = mysqli_insert_id();
        # commit the changes
        do_sql("commit");
        return $newid;
}


function mysqli_result($res, $row, $field=0) {
    mysqli_data_seek($res, $row);
    $datarow = mysqli_fetch_array($res);
    return $datarow[$field];
} 

?>