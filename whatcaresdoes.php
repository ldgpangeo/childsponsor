<?php
try {
include_once('lib/common-init.php');
$groupid = getinput("gid", 1);

#  get the group information
$res = do_sql("select * from groups where groupid = '$groupid'");
if (mysqli_num_rows($res) <> 1 ) {
	throw new Exception("Unable to retrieve group information.",ERROR_MAJOR);
}
$group = mysqli_fetch_assoc($res);

$video = get_dictionary_setting('about','videos',null);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>What Sponsorship Provides</title>
<!-- ########## CSS Files ########## -->
<!-- Framework CSS -->
<link rel="stylesheet" href="css/kriframework.css" type="text/css" media="screen" />
<!-- lightbox CSS -->
<link rel="stylesheet" href="js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<!-- Screen CSS -->
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<!-- Stylesheets for each skin -->
<?php if (isset($group['css'])) {
	?>  
	<link rel="stylesheet" href="css/<?php print trim($group['css']) ?>" type="text/css" media="screen" />
	<?php
} else {
	?>  
	<link rel="stylesheet" href="css/classic.css" type="text/css" media="screen" />
	<?php
	
}
?>
<!--
	<link rel="stylesheet" href="css/style2.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/style3.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/style4.css" type="text/css" media="screen" />
	-->
<!-- ########## end css ########## -->
<!-- JAVASCRIPT GOES HERE -->
<script type='text/javascript' src='js/jquery.js'></script>
<script type='text/javascript' src='js/cufon.js'></script>
<script type='text/javascript' src='js/geosans.js'></script>
<script type='text/javascript' src="js/prettyPhoto/js/jquery.prettyPhoto.js"  charset="utf-8"></script>
<script type='text/javascript' src='js/custom.js'></script>
</head>
<body bgcolor="#FFFFFF" id='top' >
<div class="wrap_fullwidth" id='head'><!-- end header -->
</div>
<div class="wrap_fullwidth" id='second_header'>
  <div class='center'>
<h1 class="logo "><a href="index.php" title="About the Koi Koi House"></a></h1>
    <h2 align="center">&nbsp;<br /><strong><u>C</u></strong><u>reating </u><strong><u>O</u></strong><u>pportunities for the </u><strong><u>D</u></strong><u>eaf and Disabled through </u><strong><u>E</u></strong><u>ducation </u><br />
      Improving Access to Quality Basic Education for Deaf and Disabled Children</h2>
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
<p><strong><u>Your sponsorship provides </u></strong></p>
<ul><blockquote>
  <li>Your Child's school fees</li>
  <li>Transportation from their villages to the school and back. All other African children walk the long distances to school.  Some of these children will come from distant villages too far away to walk to school daily. </li>
  <li>2 meals each school day</li>
  <li>School uniforms</li>
  <li>All required school supplies.</li>
  <li>A saving account. All sponsorship children receive their own bank/ savings account. </li>
  <li>HOPE!</li>
</blockquote></ul>
<p align="center"><a href="http://www.thegivingcircle.org" target="_blank"><strong>Visit The Giving Circle</strong></a>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="index.php?gid=2"><strong>Go back</strong></a>         
</p>
          <!-- end entry_content -->
        </div>
        <!-- end entry -->
      </div>
      <!-- end content_wrap -->
    </div>
    <!-- end center -->
  </div>
  <!-- end footer -->
</div>
	<div class="wrap_fullwidth" id='footer_bottom'>
	
		<div class='center'>
			<span class='copyright'>Copyright &copy; 2010 The Giving Circle</span>
			<a class='scrollTop ' href='#top'>top</a>
		<!-- end center -->
		</div>
	
	<!-- end footer -->
	</div>
</body>
</html>
<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>
