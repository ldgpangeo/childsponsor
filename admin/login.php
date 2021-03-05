<?php
try {
include_once("../lib/common-init.php");

$referrer = urldecode(getinput("referrer",'index.php','PG',false));
debug ("redirect is set to $referrer aka {$_GET['referrer']}");


if ($_POST['submit'] <> '') {
	$success = true;
	$errors = '';
	$in = form_validate("login.php", $errors);
	if ($errors <> '') {
		$success = false;
		throw new Exception($errors,ERROR_MINOR);
	}
	$now = strftime('%Y-%m-%d %T');
	$res = do_sql("select password, level from admins where login = '{$in['login']}' and ((end_ts is null) or end_ts > '$now') and effective_end_ts is null");
	if (mysqli_num_rows($res) <> 1) {
		$success = false;
	} else {
		$goodpass = mysqli_result($res,0,'password');
		$status = mysqli_result($res,0,'level');
		# extract the salt
		$salt = substr($goodpass,0,12);
		# encrypt the test password
		$testpass = crypt($in['password'],$salt);
#		debug("comparing $testpass to $goodpass");
		if ($testpass <> $goodpass) {
			$success = false;
			$errors = "Login Failure";
		} else if ($status < 1)   {
			$success = false;
			$errors = "You are not authorized to access this site.";
			logit("UNAUTHORIZED_ADMIN_ACCESS","Login={$in['login']}");
			throw new Exception("You are not authorized to access this site.",ERROR_MINOR_NORETURN);
		}
	} 
	if (! $success) {
			$max = get_dictionary_setting("max_fails",'login');
			if ($max <= $in['login_fail']) {
				logit("LOGIN_FAIL","fail_count = {$in['login_fail']}, login = {$in['login']}",TRUE);
				throw new Exception ("Too many login failures.",ERROR_MINOR_NORETURN);
			}
			$in['login_fail'] ++;
			debug("login failure, count is {$in['login_fail']}");
		} else {
			
			debug("login succeeded, redirecting to $referrer");
			$uid = get_uid($in['login']);
			$sessionid = save_session(null,$uid);
			#  give cookie
			setcookie('childsponsor', $sessionid, time() + 3600*4);
			debug ("Login:  cookie sent to browser with value $sessionid ");
			#  allow user to cancel resuming from prior page.
			if (! isset($_POST['resume'])) { $referrer = ''; }
			# reconstruct the referrer stripping out any prior session id.
			if ($referrer <> '') {
				# break url into pieces
				$tmparray = parse_url($referrer);
				$url = $tmparray['path'];
				$tmp = $tmparray['query'];
				debug ("url is $url and arguments are $tmp");
				# break query into components
				$parts = explode("&",$tmp);
				$new = array();
				# reassemble skipping over the id= component
				foreach ($parts as $item) {
					debug ("testing $item");
					# include item if it is not null and either id= is missing or not at start of item
					if (($item <> '') and ((strpos($item,"id=") === false) or (strpos($item,"id=") > 0))) {
						array_push($new, $item);
					}
				}
				#  add in the new id component
				array_push($new, "id=$sessionid");
				debug(dump_array($new));
				#  compose new url
				$redirect = $url."?".join("&",$new);
			} else{
				$redirect = "index.php?id=$sessionid";
			}
			debug("login redirecting to $redirect");
			$referrer = null;
		}
		
		
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php 
if ($redirect <> '') {
	print "<meta http-equiv=\"refresh\" content=\"1;URL=$redirect";
	if ($referrer <> '') {
		print "?referrer=".urlencode($referrer);
	}
	print "\">";
}
?>
<title>Admin Logout</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>
<h2 align="center">Login page</h2>
<form action="" method="POST">
<input type="hidden" name="referrer" value="<?php print $referrer ?>">
<input type="hidden" name="login_fail" value="<?php print $in['login_fail'] ?>">
<?php if ($errors <> '') {print "<h3>$errors</h3>";} ?>
<table width=50% border = "1" cellpadding="3" cellspacing="3" align="center">
<tr>
<td>Login name:</td>
<td><input type="text" name="login" size="32" maxlength="32"></td>
</tr>
<tr>
<td>Password:</td>
<td><input type="password" name="password" size="32" maxlength="32"></td>
</tr>
<?php 
if (($referrer <> 'index.php') and ($referrer <> '')) {  ?>
<tr>
	<td>Resume from last page</td>
	<td><input type="checkbox" value="Y" name="resume" checked></td>
</tr>	
<?php } ?>
<tr bgcolor="#FFFFCC">
<td colspan="2" align="center"><input type="submit" name="submit" value="Login"></td>
</tr>
</table>
</form>
</body>
</html>

<?php


} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>


