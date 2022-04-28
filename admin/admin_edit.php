<?php
try {
  include_once("../lib/common-init.php");
  
  # test if user belongs here
  $sessionid = getinput('id');
  $tmparray = require_login($sessionid);
 	if ($tmparray === false) {
		$redirect = "$webroot/admin/login.php";
		$referrer = $_SERVER['REQUEST_URI'];
	} else {
		$is_ok = true;
		$uid = $tmparray[0];
		$login = $tmparray[1];
		$uid = getinput('uid');
		if ($uid > 0) {
			$insert = false;
			$msg = "Edit administrator Info";
			$submit = "Save Changes";
			$res = do_sql("select * from admins where uid = '$uid' and effective_end_ts is null");
			if (mysqli_num_rows($res) <> 1) {
				throw new Exception("Failed to find unique admin for uid = $uid", ERROR_MAJOR);
			} else {
				$old = mysqli_fetch_assoc($res);
			}
		} else {
			$insert = true;
			$msg = "Add a new administrator";
			$submit = "Add Administrator";
			$old = array();
		}
		
		$recon = getinput("r", 0);
		
# ------------------------  start form processing  --------------------------
if ($_POST['submit'] <> '') {
		$errors = '';
		$in = form_validate("admin_edit.php", $errors);
		if ($insert)  {
			#  if we are inserting, then login can not be empty
			if ($in['login'] == null) {
				$errors .= "You must provide a login name when adding an administrator.";
			} else {
				#  if we are inserting, then login can not match a prior user.
				$res = do_sql("select * from admins where login = '{$in['login']}'");
				if (mysqli_num_rows($res) > 0) {
					$errors .= "The login name {$in['login']} is already in use. Choose a different login name.";
				}		
			}
		}
		if ($insert and ($in['password'] == null)) {
			$errors .=  "You must provide a password when adding an administrator.";
		}
		if ($errors <> '') {
			throw new Exception("$errors", ERROR_MINOR);
		}
	# if password is present, encrypt it.	
	if ($in['password']<> null) {
		$tmp = substr(base_convert(mt_rand(0x19A100, 0x39AA3FF), 10, 36) . base_convert(mt_rand(0x19A100, 0x39AA3FF), 10, 36),0,8); 
		$salt = '$1$'. $tmp.'$';
		$in['password'] = crypt($in['password'],$salt);
	} else {
		unset($in['password']);
	}
	#  get next available uid
	if ($insert) {
		# establish default values
		$in['level'] = 1;
		$in['start_ts'] = strftime('%Y-%m-%d %T');
		#  get next available uid
		$res = do_sql("select max(uid) next from admins");
		$in['uid'] = mysqli_result($res,0,'next') +1;
	} else {
		if (getinput("enable") == 'Y') {
			$in['status'] = 'Y';
		}
		if (getinput("disable") == 'Y') {
			$in['status'] = 'N' ;
		}
	}	
	$in['updated_by'] = $login;
	write_admins($uid,$in);
	
	if ($insert) {
		logit("ADMIN_ADD","login = {$in['login']}, name = {$in['name']}, by admin = $login");
	} else {
		logit("ADMIN_UPDATE","login = {$in['login']}, name = {$in['name']}, by admin = $login"); 
	}	
	if ($recon == 1) {
	    #  return to recon index page
	    $redirect = "recon/index.php?id=$sessionid";
	    
	} else {
	    #  return to list of admins
	    $redirect = "admin_list.php?id=$sessionid";
	}
}
#  -------------------------  end form processing  --------------------------

}


 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php 
if ($redirect <> '') {
	print "<meta http-equiv=\"refresh\" content=\"1;URL=$redirect";
	if ($referrer <> '') {
		print "?referrer=".urlencode($referrer);
	}
	print "\">";
}
?>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="style.css" rel="stylesheet" type="text/css">
<title>Admin:  Edit Administrator</title>
</head>
<body>
<H2 align="center">Administrative Modules<br>Edit Administrators</H2>
<form action="" method="post">
<table width="90%" border = "1" cellpadding="3" cellspacing="3" align="center">
	<tr bgcolor="#FFFFBB">
		<td align="center" colspan="2"><h3><?php print $msg ?></h3></td>
	</tr>
	<tr>
		<td>Login:</td>
		<td><?php if ($insert) { ?>
			<input type="text" name="login" size="16"  maxlength="32" value="<?php print $old['login'] ?>">
			<?php } else { 
					print $old['login'];
					print " <input type=\"hidden\" name=\"login\" value=\"{$old['login']}\">\n";
				} ?>
			</td>
	</tr>
	<tr>
		<td>Name:</td>
		<td><input type="text" name="name" size="40"  maxlength="80" value="<?php print $old['name'] ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="text" name="password" size="24"  maxlength="80" value="">
			<?php if (! $insert) { ?>
				Enter new password or leave blank to keep it unchanged.
			<?php } ?>
		</td>
	</tr>
	<tr>
		<td>email:</td>
		<td><input type="text" name="email" size="40"  maxlength="80" value="<?php print $old['email'] ?>"></td>
	</tr>
<?php	if (! $insert) { ?>
	<tr>
		<td><?php  
			if ($old['end_ts'] == null) {
				print "Admin is active.";
			} else {
				print "Admin is disabled.";
			}?>
		</td>
		<td><?php 
			if ($old['end_ts'] == null) {
				print "<input type=\"checkbox\" name=\"disable\" value=\"Y\"> Disable this admin";
			} else {
				print "<input type=\"checkbox\" name=\"enable\" value=\"Y\"> Enable this admin";				
			}
			?>
		</td>
	</tr>
	<?php } #  end enable/disable admin ?>
	<tr bgcolor="#FFFFBB">
		<input type="hidden" name="r" value="<?php print $recon ?>">		
		<td align="center" colspan="2"><input type="submit" name="submit" value="<?php print $submit ?>">
		<input type="hidden" name="uid" value="<?php print $old['uid'] ?>">	
		</td>
	</tr>
</table>
</form>

</p>
<?php if ($recon == 1) {?>
  <p><a href="recon/index.php?id=<?php print $sessionid ?>&object=<?php print $object_id?>">Return to Reconciliation Menu</a></p>
<?php } else { ?>
  <p><a href="index.php?id=<?php print $sessionid ?>&object=<?php print $object_id?>">Return to Admin Menu</a></p>
<?php } ?>

</body>
</html>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>


