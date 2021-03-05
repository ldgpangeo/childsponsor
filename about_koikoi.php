<?php
include "lib/common-init.php";
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
    <h1 class="logo "><a href="index.php" title="About the Koi Koi House">Koi Koi House</a></h1>
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
        <h1>About the Koi Koi House Project</h1>
        <div class="entry_content">
        <?php if ($video <> null) { ?>
          <object width="480" height="385"><param name="movie" value="<?php print $video ?>?fs=1&amp;hl=en_US"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="<?php print $video ?>?fs=1&amp;hl=en_US" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>
          <?php } ?>
</p>
<p>
The Giving Circle, Inc. (TGC) is an all volunteer non-profit organization based in Saratoga Springs, NY. The organization was founded in 2005 by Jefferson Award honoree Mark Bertrand with a mission to connect communities in need with those with the resources to help. </p>
<p>
The Giving Circle, Inc. was initially founded in response to Hurricanes Katrina and Rita, and has since expanded its efforts working locally with the underserved in Saratoga County and supporting the revitalization of Saratoga Springs' Beekman Street Artists District, nationally - continuing rehabilitation efforts in the Gulf Coast, and internationally with a partnership with Ugandan sister Org The Giving Circle Africa building The Giving Circle Koi Koi House orphanage, schools, play grounds and more.</p>
<p>The Giving Circle, Inc. is a 501(c)(3) non-profit corporation. Your donations are tax-deductible.</p>

<p align="center"><a href="http://www.thegivingcircle.org" target="_blank"><strong>Visit The Giving Circle</strong></a>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="index.php"><strong>Go back</strong></a>         
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
