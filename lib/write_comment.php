<?php
/*
 * General purpose function to write to effective-dated table r_notes
  */

function next_commentid ($now) {
    # get the next sequence number for people
    #     this becomes people.people_id and  people_info.people_id
    global $hdl;
    $res = do_sql("insert r_commentid_seq values ( null, '$now' ) ", false);
    return mysqli_insert_id($hdl);
}


 function write_comment($data) {
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
	$table = "r_spreadsheet_comments"; 
	$skip_cols = array ('commentid');
	$key_col = "commentid";
	
	$commentid = next_commentid ($now);
	$reconid = $data['reconid'];
	$author = $data['author'];
	$comment = $data['comment'];
	
    #  now write the data
    
    #  create a transaction
 	$res = do_sql("Start transaction");
 	#  if update then end date the prior rows.
    $sql = "update $table set effective_end_ts = '$now' where reconid = '$reconid' and effective_end_ts is null";
 	$res = do_sql($sql);
 
 	#  write the new row
 	
 	$sql = "insert r_spreadsheet_comments (commentid, reconid, author, effective_start_ts, effective_end_ts, comment) ";
 	$sql .= "values ('$commentid','$reconid','$author','$now',null,'$comment' )";
 	$res = do_sql($sql);
  	#  commit the transaction
 	$res = do_sql("commit");
 	return true;
 }

 
 
?>