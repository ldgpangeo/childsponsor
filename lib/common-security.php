<?php

function logit($code,$msg,$security = false) {
	global $hdl;
	if ($object_id == '') { $object_id = -1; }
	$now = date("Y-m-d H:i");
	$ip = $_SERVER['REMOTE_ADDR'];
	$msg = mysqli_real_escape_string($hdl, $msg);
	if ($security) {$tmp = 'Y'; } else { $tmp = 'N';}
	$now = strftime('%Y-%m-%d %T');
	$sql = "insert logs (datedone,event_code,detail,security,ip) values ('$now','$code','$msg','$tmp','$ip')";
	do_sql($sql);
}

function require_login(&$sessionid) {
	global $dbg ;
	if ($sessionid == '') {
		$sessionid = getinput('id');
	}
	debug ("require_login: sessionid is $sessionid");
	if ($sessionid == '') { return false;}    # user has no session
	$row = get_session($sessionid);
	#	debug ("require_login: Session array is \n".dump_array($row));
	if ($row === false) {  # user is no longer logged in
	    #  delete any cookie
	    setcookie('childsponsor', '', time() -3600);
	    return false;
	}     
	$uid = $row['uid'];
debug ("userid is now $uid");
	$sessionid = save_session($sessionid, $uid);                  # update time stamp
	setcookie("childsponsor",$sessionid);
	debug ("require_login: sessionid at end is  $sessionid");
	$login = get_login($row['uid'],true);
	if ($login === false) {return false;}     # user is not authorized
	
	return array($uid, $login);
}

function get_role($uid) {
	if ($uid == '') { return "USER"; }
	$res = do_sql("select role from users where uid = '$uid'", true,false);
	if (mysqli_num_rows($res) == 1) {
		return mysqli_result($res,0,'role');
	} else {
		return "USER";
	}
}

function get_login($uid, $error = true) {
	global $dbg;
	if ($uid <> '') {
		$res = do_sql("select login from admins where uid = '$uid' and effective_end_ts is null", true,false);
		if (mysqli_num_rows($res) <> 1) {
			if ($error) {
				throw new Exception ("User \"$uid\" not found. ", ERROR_MAJOR);
			} else {
				$owner = "unknown";
			}
		} else {
			$row = mysqli_fetch_assoc($res);
			$owner = $row['login'];
		}
	} else {
		if ($error) {
			throw new Exception ("You are not logged in.", ERROR_MINOR);
		}
	}
	return $owner;
}

function get_uid($login, $error = true) {
	if ($login <> '') {
		$now = strftime('%Y-%m-%d %T');
		$res = do_sql("select uid from admins where login = '$login' and ((end_ts is null) or end_ts > '$now') and effective_end_ts is null", true,false);
		$count = mysqli_num_rows($res);
		if ($count == 1) {
			$owner = mysqli_result($res,0,'uid');
		} else {
			$owner = '';
			if ($error) {
				throw new Exception ("User \"$login\" not found.<br>");
			}
		}
	} else {
		if ($error) {
			error_page("You are not logged in.<br>");
		}
	}
	return $owner;
}

function save_session($sessionid, $uid) {
	
#	debug ("userid is $uid");
	$ip = $_SERVER['REMOTE_ADDR'];
	if ($sessionid == '') {
		$sessionid = mt_rand() . mt_rand() . mt_rand();
		$new == true;
	}
	setcookie("childsponsor", $sessionid, time() + 3600*4);
	$now = strftime('%Y-%m-%d %T');
	do_sql("insert sessions values('$sessionid','$uid','$now','$ip') on duplicate key update datedone = '$now'", true,false);
		$res = do_sql("select sessionid from sessions where uid = '$uid' and ip = '$ip'", true,false);
		if (mysqli_num_rows($res) == 1) {
			return mysqli_result($res,0,'sessionid');
		} else return $sessionid;
}

function get_session($sessionid) {
	$res = do_sql("select sessions.*,setting*60 max_idle from sessions,dictionary where sessionid = '$sessionid'
	                      and area = 'login' and label='max_idle'", true,false);
	while ($row = mysqli_fetch_assoc($res)) {
		# test idle timeout
		if ((time() - strtotime($row['datedone'])) > $row['max_idle']) {
			#  remove the idle session
			do_sql("delete from sessions where sessionid = '$sessionid'", true,false);
			return false;
		} else {
			return $row;
		}
	} 
	return false;
}

?>