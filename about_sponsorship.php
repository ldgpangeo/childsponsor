<?php
try {
include_once('lib/common-init.php');
$groupid = getinput("gid", 1);
if ( (filter_var($groupid, FILTER_VALIDATE_INT) === false) or ($groupid < 1) or ( $groupid > 200 ) ) {
    throw new Exception("Invalid group ID| $groupid",ERROR_MAJOR);
}

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
<h1 class="logo "><a href="index.php?gid=<?php print $groupid ?>" title="About the Koi Koi House"></a></h1>
    <h2 align="center">&nbsp;<br /><strong><u>C</u></strong><u>reating </u><strong><u>O</u></strong><u>pportunities for the </u><strong><u>D</u></strong><u>eaf and Disabled through </u><strong><u>E</u></strong><u>ducation </u><br />
      Improving Access to Quality Basic Education for Deaf and DisabledChildren</h2>
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
<p>Life for an African child can be very hard, hardest perhaps for a disabled child and their family. </p>
<p>The Giving Circle at its Busoga Primary School operates a one of its kind program for deaf and disabled children in Uganda.  The Giving Circle Busoga Primary School is the first school to teach sign language to all students, non-hearing and hearing. </p>
<blockquote>&quot;Kasiru a <em>Luganda</em> word meaning &quot;stupid.&quot;</blockquote>
	<img src="images/signing-web.jpg" align="right" alt="Teacher & student signing" hspace="5" vspace="3">
<p>In places like Uganda where most live in profound poverty the disabled children can be seen as a curse especially the deaf  and are treated differently in 
	their families and community at large. For many families still today long held cultural superstitions see the child's handicap as a curse and the child 
	as incapable of learning.  In some casess, the cause for everything that goes wrong in the homes. </p>
<p>In terms of education, disabled children seldom attend school due to very limited family funds and the school fees and other requirements 
	compared to &quot;normal children&quot;. Schools willing to educate them are very few in Uganda and also very expensive.  Our programs and your sponsorship 
	for your child will change this and the children's lives for ever.</p>
<p>The major objective of this program is to address the educational  needs of deaf and disabled children, bringing them into mainstream activities and thus make them active members of society with the hope of the better future education brings. </p>
<ul><blockquote>
  <li>The program models the mainstreaming of disabled children in the Jinja district offering them quality basic education alongside the local children.</li>
  <li>The proposed education program targets deaf or disabled children who have never gotten a chance to go to school or dropped out of school for a number of reasons.<strong>   </strong></li>
  <li>Training parents and care givers in basic  sign language to improve on the communication skills between them and their children.  
  	This is accomplished in adult/guardian education classess. </li>
  <li>This program will provide easy access to formal education,since the all the school requirements like school dues, transport fares, scholastic materials etc will be provided to the children on a timely basis.</li>
  <li>The program also trains parents and caregivers on the rights of deaf and disabled children within the community and sign language as an effective communication medium, leadng toward better pride and recognition of the hearing impaired as productive citizens.</li>
  <li>The approach used will enable the deaf and disabled children to develop social skills and interaction with the so called normal children in the same school hence reducing the social stigma and discrimination between children. To come to appreciate that the deaf and disabled children are equally gifted like an other so called &quot;normal children&quot;.</li>
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
