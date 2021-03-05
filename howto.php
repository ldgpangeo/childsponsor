<?php
$groupid = $_GET["gid"];
if ($groupid == '') { $groupid = 1; }
if ( (filter_var($groupid, FILTER_VALIDATE_INT) === false) or ($groupid < 1) or ( $groupid > 200 ) ) {
    throw new Exception("Invalid group ID| $groupid",ERROR_MAJOR);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Elementia - Basic Page</title>
<!-- ########## CSS Files ########## -->
<!-- Framework CSS -->
<link rel="stylesheet" href="css/kriframework.css" type="text/css" media="screen" />
<!-- lightbox CSS -->
<link rel="stylesheet" href="js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />
<!-- Screen CSS -->
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<!-- Stylesheets for each skin -->
<link rel="stylesheet" href="css/style3.css" type="text/css" media="screen" />
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
    <h1 class="logo "><a href="index.php?gid=<?php print $groupid ?>" title="Elementia - A premium Business and Portfolio Template">Elementia</a></h1>
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
        <h1>How to Sponsor a Child</h1>
        <div class="entry_content">
          <div class='image_border'>
            <script type="text/javascript">
AC_AX_RunContent( 'width','480','height','385','src','http://www.youtube.com/v/N6URu8Oe0pE?fs=1&hl=en_US','type','application/x-shockwave-flash','allowscriptaccess','always','allowfullscreen','true','movie','http://www.youtube.com/v/N6URu8Oe0pE?fs=1&hl=en_US' ); //end AC code
</script><noscript><object width="480" height="385">
              <param name="movie" value="http://www.youtube.com/v/N6URu8Oe0pE?fs=1&amp;hl=en_US">
              </param>
              <param name="allowFullScreen" value="true">
              </param>
              <param name="allowscriptaccess" value="always">
              </param>
              <embed src="http://www.youtube.com/v/N6URu8Oe0pE?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed>
            </object></noscript>
          </div>
          <h2>There can be no greater reward in life than to know you’ve changed the life of a child forever.</h2>
          <p>You are just a click away from giving one of our children, a Ugandan girl or boy the chance of a lifetime. Food, health care and an education. With your sponsorship gifts of about a dollar a day, you will provide the tools and hope for your child that will change their lives. </p>
          <p>Hope they do not have without your loving generosity.</p>
          
 <blockqoute><blockquote><blockquote>
           <p>Most are $35 to $50 per month<br>
            	Older children are higher because of Ugandan school tuition costs.
            </p>
            <p>Payable via either monthly or yearly tax-deductible options
            </p>
          </blockquote>
          <p>Your payment is completed in two steps:</p>
          <blockquote>
          <ol>	
            <li> Complete a brief form to identify yourself and the child you wish to sponsor.</li>
            <li> Make your contribution via PayPal -- a safe, secure site for credit card payments.</li>
          </ol>
          </blockquote>
          <p><a href="index.php?gid=<?php print $groupid ?>"><strong>Go back.</strong></a>
                 
          </p>
          <p>&nbsp;</p>
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
