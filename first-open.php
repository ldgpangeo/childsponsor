<?php
include "lib/common-init.php";
$video = get_dictionary_setting('welcome','videos',null);
$deaf  = get_dictionary_setting('deaf','videos',null);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-refresh="0;first.php"">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Welcome to Child Sponsorship Site</title>
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
    <h1 class="logo "><a href="index.php" title="The Giving Circle Child Sponsorship Program">The Giving Circle Child Sponsorship Program</a></h1> 
    <!-- end center -->
  </div>
  <!-- end second_header -->
</div>
<div class="wrap_fullwidth small_margin" id='main'>
  <div class='center'>
    <!-- div class='content_two_third' id='content_wrap'-->
      <!--div class='content_two_third entry' -->
                      <div class='image_border'>
                      <!-- <H1 align="center"><strong>The Giving Circle Child Sponsorship</strong></H1> -->
                      <h3 align="center"><i>You are facilitating a relationship where there wouldn't be the possibility of one. <br />It will insure both longevity and sustenance.</i></h3>
<?php if ($video <> null) { ?>
                      <p align="center">
                      	  <iframe width="400" height="225" src="<?php print $deaf ?>" frameborder="0" allowfullscreen></iframe>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      	  <iframe width="400" height="225" src="<?php print $video ?>" frameborder="0" allowfullscreen></iframe>
</p>
<?php } ?>
          <!-- end entry_content -->
        </div>
        <!-- end entry -->
      </div>
      <!-- end content_wrap -->
  		<div class='center'>
						
				<div class='content_one_third portfolio_item'>
					<div class='item_data rounded'>
					
						<h2><a href='index.php?gid=2'>Go to the new Deaf and <br />Disabled Sponsorship Pages.</a></h2>
						
					<!-- end item_data-->
					</div>

				<!-- end portfolio_item-->
				</div>
				
				<div class='content_one_third portfolio_item'>
					&nbsp;
				<!-- end portfolio_item-->
				</div>
			<!-- end  #content_fullwidth -->

				<div class='content_one_third portfolio_item'>
					<div class='item_data rounded'>
					
						<h2><a href='index.php?gid=1'>Go to the Traditional Sponsorship Pages.</a></h2>
						
					<!-- end item_data-->
					</div>
				<!-- end portfolio_item-->
				</div>
				
		</div>
    </div>
    <!-- end center -->
  </div>
	
  <!-- end footer -->
</div>
	<div class="wrap_fullwidth" id='footer_bottom'>
	
		<div class='center'>
			<span class='copyright'>Copyright &copy; 2016 The Giving Circle</span>
			<a class='scrollTop ' href='#top'>top</a>
		<!-- end center -->
		</div>
	
	<!-- end footer -->
	</div>
</body>
</html>
