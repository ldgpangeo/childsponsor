<?php


function get_dictionary_setting($label, $area,$default = '') {
	if (($label == '') or ($area == '')) {return $default; }
	$res = do_sql("select setting from dictionary where label = '$label' and area = '$area' ",FALSE,false);
	if (!$res) { return $false; } 
	if (mysqli_num_rows($res) <> 1) { return $default; }
	return mysqli_result($res,0,'setting');
}

function get_dictionary_label($setting, $area,$default = '' ) {
	if (($setting == '') or ($area == '')) {return $default; }
	$res = do_sql("select label from dictionary where setting = '$setting' and area = '$area'",FALSE,false);
	if (!$res) { return $false; } 
	if (mysqli_num_rows($res) <> 1) { return $default; }
	return mysqli_result($res,0,'label');
}

#  present dictionary items as a set of radio buttons
function dictionary_as_radio($name,$area,$value) {   
	$res = do_sql("SELECT * FROM dictionary WHERE area = '$area' ",FALSE,FALSE);
	if (!$res)  {throw new MyError ("Bad sql... ". $sql,ERROR_MAJOR);}
	for ($i =0; $i < mysqli_num_rows($res); $i ++) {
		$setting = mysqli_result($res,$i,'setting');
		$prompt = mysqli_result($res,$i,'label');
		print "<input type = \"radio\" name=\"$name\" value=\"$setting\"";
		if ($value == $setting) { print " checked "; }
		print "> $prompt &nbsp;&nbsp;\n";
	}
}

function dictionary_as_select($name, $area, $value = null, $object_id = -1) {
	$res = do_sql("SELECT * FROM dictionary WHERE area = '$area' order by seq ",FALSE,FALSE);
	print "<select name = \"$name\">\n";
	print "<option value=\"\">Choose...</option>\n";
	while ($row = mysqli_fetch_assoc($res)) {
		print "<option value = \"{$row['setting']}\"";
		if (($value <> null) and ($row['setting'] == $value)) {
			print " selected";
		}
		print ">{$row['label']}</option>\n";
	}
	print "</select>\n";
}

function dictionary_as_array($area) {
	$res = do_sql("SELECT * FROM dictionary WHERE area = '$area' ",FALSE,FALSE);
	$out = array();
	while ($row = mysqli_fetch_assoc($res)) {
		$out[$row['label']] = $row['setting'];
	}
	return $out;
}

function select_dictionary_area($label, $default) {
	$res = do_sql("select distinct area from dictionary order by area");
	if ($res === false) { return $default; }
	if (mysqli_num_rows($res) == 0) {return $default;}
	print "<select name=\"$label\">\n";
	while ($row = mysqli_fetch_assoc($res)) {
		if ($row['area'] == $default) {
			print "<option selected>";
		} else {
			print "<option>";
		}
		print $row['area']."</option>\n";
	}
	print "</select>\n";
}

#  send email to a user, default is ldg
function send_mail($fromname, $fromaddress, $subject, $message, $toaddress ='ldgpangeo@gmail.com') {
   // Copyright  2005 ECRIA LLC, http://www.ECRIA.com
   // Please use or modify for any purpose but leave this notice unchanged.
   $headers  = "MIME-Version: 1.0\n";
   $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
   $headers .= "X-Priority: 3\n";
   $headers .= "X-MSMail-Priority: Normal\n";
   $headers .= "X-Mailer: php\n";
   $headers .= "From: \"".$fromname."\" <".$fromaddress.">\n";
   return mail($toaddress, $subject, $message, $headers);
}

function send_html_mail($fromname, $fromaddress, $subject, $message, $toaddress ='ldgpangeo@gmail.com') {
   // Copyright  2005 ECRIA LLC, http://www.ECRIA.com
   // Please use or modify for any purpose but leave this notice unchanged.
   $headers  = "MIME-Version: 1.0\n";
   $headers .= "Content-type: text/html; charset=us-ascii\n";
   $headers .= "Content-Transfer-Encoding: quoted-printable\n";
   $headers .= "X-Priority: 3\n";
   $headers .= "X-MSMail-Priority: Normal\n";
   $headers .= "X-Mailer: php\n";
   $headers .= "From: \"".$fromname."\" <".$fromaddress.">\n";
   return mail($toaddress, $subject, $message, $headers);
}

?>