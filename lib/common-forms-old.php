<?php

function validate_datetime($test, $min, $max, $format = '%Y-%m-%d %T') {
	if ($test == '') { return null; }
	if (strtolower($test) == 'unknown') { return null; }
	$ts = strtotime($test);
	if ( ( ($min <> '') and $ts < strtotime($min) ) or ( ( $max <> '') and ($ts > strtotime($max) ) ) ) {
		return false;
	} else {
		return strftime($format,$ts);
	}
}


function getinput($name,$default=null,$source="PG",$clean=true) {
	global $dbg;
	if ($name == '') { return false; }
	$return = null;
	if (!(strpos($source,"P") === false)) {
		$return = trim($_POST[$name]);
	}
	if (($return == '') and (!(strpos($source,"G") === false))) {
		$return = html_entity_decode(trim($_GET[$name]));
	}
	if ($return == '') { 
		$return = $default; 
#		debug ("return is set to $default");
	}
	if ($clean) {return clean($return); }
	return $return;
}

function clean($msg) {
    if ($msg <> '') {
    	$back = preg_quote(chr(92));
		$tmp = htmlentities($msg, ENT_QUOTES);
		$web = array('|/|', "/$back/", '/!/', '/\[/','/\]/', '/\|/',"/'/" );
		$replace = array('&#47;', '&#92;', '&#33;', '&#91;', '&#93;', '&#124;', '&#39;');
		$tmp = preg_replace ($web,$replace, $tmp);
      	return $tmp;                                                 # return text
   	} else {                                                         # if text is empty
      return null;													 #   return null
   	}
}

function strip($in,$allowed,$case) {
	if ($case == 'upper') {
		$in = strtoupper($in);
	} else if ($case == 'lower') {
		$in = strutolower($in);
	}
	$inarray = str_split($in);
	$valid = str_split($allowed);
	$out = "";
	foreach ($inarray as $tmp) {
		if (in_array($tmp,$valid)) { $out .= $tmp; }
	}
	return $out;
}

#  block sql injection
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not a number or a numeric string
   if (!is_numeric($value)) {
       $value = "'" . mysqli_real_escape_string($hdl,$value) . "'";
   }
   return $value;
}

function validate_telephone($phone) {
	# regex from http://stackoverflow.com/questions/123559/a-comprehensive-regex-for-phone-number-validation
	$regex2='/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';
	if (preg_match($regex2,strtolower($phone),$matches) == 0) {
		return false;
	} else {
		return $matches[0];
	}
}

function form_validate($form, &$errors) {
	global $dbg;
	if ($form == '') { return null;}
	$data = array();
	# get form rules
	$res = do_sql("select * from forms where form = '$form'");
	while ($rule = mysqli_fetch_assoc($res)) {
		$ok = true;
		$tmp = $_POST[$rule['name']];
		#  clean the data
		if ($rule['clean'] == 'Y') { 
			$tmp = trim(clean($tmp));
		} else {
			$tmp = trim($tmp);
		}
		# mysql escape the data
		if ($rule['mysqli_clean'] == 'Y') {
#			$tmp = mysqli_real_escape_string($hdl,$tmp);
			$tmp = addslashes($tmp);
		}
		#  is the data required
		if ($rule['required'] == 'Y') { 
			if ($tmp == '') {
				$ok = false;
				$errors .= $rule['required_msg'] . "<br>";
			}
		}
		#  is content plausible
		if (! ($tmp == null) ) {
#			debug ("Testing -$tmp- as {$rule['content']}");
		switch (strtolower($rule['content'])) {
			case 'integer' :
			case 'int' :
				if (filter_var($tmp, FILTER_VALIDATE_INT) === false) {
#					if ($dbg) {debug ("$tmp failed integer test.");}
					$ok = false;
					$errors .= $rule['content_msg']. " (not a valid integer)<br>";					
				} else if (($tmp < $rule['minimum']) or ($tmp >$rule['maximum'] )) {
					$errors .= $rule['content_msg'].  " (must be between {$rule['minimum']} and {$rule['maximum']})<br>";
				}
				break;
			case 'float' :
			case 'real' :
				if (filter_var($tmp, FILTER_VALIDATE_FLOAT ) === false) {
#					if ($dbg) {debug ("$tmp failed float test.");}
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				} else if (($tmp < $rule['minimum']) or ($tmp >$rule['maximum'] )) {
					$errors .= $rule['content_msg'].  " (must be between {$rule['minimum']} and {$rule['maximum']})<br>";
				}				
				break;
			case 'ip' :
				if (! filter_var($tmp, FILTER_VALIDATE_IP)) {
#					if ($dbg) {debug ("$tmp failed IP test.");}
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				}				
				break;
			case 'email' :
				$tmp = filter_var($tmp, FILTER_SANITIZE_EMAIL);
				if (! filter_var($tmp, FILTER_VALIDATE_EMAIL)) {
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				}
				break;
			case 'telephone' :
				$parsed = validate_telephone($tmp);
				if ($parsed === false) {
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				} else {
					$tmp = $parsed;
				}
				break;
			case 'yesno' :
				$tmp = strtoupper(substr($tmp,0,1));
				if (!(($tmp == 'Y') or ($tmp == 'N'))) {
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				}
				break;
			case 'enum' :
			    $tmparray = explode(',', $rule['enum_list']);
			    if (! in_array($tmp, $tmparray)) {
				   $ok = false;
				   $errors .= $rule['content_msg']. "<br>";					
			    }
				break;
			case 'datetime' :
				$ts = strtotime($tmp);
				if ((($rule['minimum'] <> '') and $ts < strtotime($rule['minimum']) ) or 
				    (($rule['maximum'] <> '') and $ts > strtotime($rule['maximum']) )) {
					$ok = false;
					$errors .= $rule['content_msg']. "<br>";					
				}
				# convert datetimes to iso format
				if ($tmp <> '') {
					$tmp = strftime('%Y-%m-%d %T',$ts);
				}
				break;
			case 'string' :
			default :
				# treat as a string 
				$detail = '';
				if (strlen($tmp) < $rule['minimum']) {
					$detail = "too short";
				}
				if (strlen($tmp) > $rule['maximum']) {
					$detail = "too long";
				}
				if ($detail <> '') {
					$ok = false;
					$errors .= $rule['content_msg']. "($detail).<br>";
				}
		}
		}
#		debug("saving variable named {$rule['name']} as $tmp");
		$data[$rule['name']] = $tmp;
	}
#	if ($dbg) { debug ($errors) ; }
 	return $data;
}

?>
