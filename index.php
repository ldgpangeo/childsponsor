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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<title>Sponsor a Child</title>
	
	<!-- ########## CSS Files ########## -->	

	<!-- Framework CSS -->
	<link rel="stylesheet" href="css/kriframework.css" type="text/css" media="screen" />
	<!-- lightbox CSS -->
	<link rel="stylesheet" href="js/prettyPhoto/css/prettyPhoto.css" type="text/css" media="screen" charset="utf-8" />	
	<!-- Screen CSS -->
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />	
	
	<!-- Stylesheets for each skin -->
<?php
if (isset($group['css'])) {
	?>  
	<link rel="stylesheet" href="css/<?php print trim($group['css']) ?>" type="text/css" media="screen" />
	<?php
} else {
	?>  
	<link rel="stylesheet" href="css/classic.css" type="text/css" media="screen" />
	<?php
	
}
?>
	
	
	<!-- ########## end css ########## -->	<!-- JAVASCRIPT GOES HERE -->	
	<script type='text/javascript' src='js/jquery.js'></script>
	<script type='text/javascript' src='js/cufon.js'></script>
	<script type='text/javascript' src='js/geosans.js'></script>
	<script type='text/javascript' src="js/prettyPhoto/js/jquery.prettyPhoto.js"  charset="utf-8"></script>	
	<script type='text/javascript' src='js/custom.js'></script>
	<script type='text/javascript' src='js/ajax.js'></script>
	

</head>
<body id='top' >
	
	<div class="wrap_fullwidth" id='second_header'>
	
		<div class='center'>
		
			<h1 class="logo "><a href="index.php?gid=<?php print $groupid ?>" title="The Giving Circle Africa">Sponsor a Child</a></h1>

		<!-- end center -->
		</div>
	
	<!-- end second_header -->
	</div>
	
	
	
	
	<div class="wrap_fullwidth" id='feature_background'>	
		
		<!-- ###################################################################### -->
		<div class='center' id="feature_wrap">
		<!-- ###################################################################### -->
			<div id="featured" class='accordion'>
<?php
	$rawtitle = trim($_GET['title']);
	if (strlen($rawtitle) > 0) {
		$search = true;
		$title = clean($rawtitle);
		if (strpos($title, "%") !== false) {
		    $title = '';
		    $search = false;
		    throw new Exception ("Badly formed name: $rawtitle",ERROR_MAJOR);
		}
	} else {
		$search = false;
	}
  $id = $_GET['id'];
#  if ($id <> '') {
#  	logit ($id);
#  }

  $res = do_sql("select count(*) total from items where is_active = 'Y'");
  $num_panels = mysqli_result($res,0,'total');

  $per_page = get_dictionary_setting('per_page','panels','10');
  debug ("db has $num_panels to be displayed as $per_page per page");
   
  $range = $_GET['group'];
  if ($range < 1) { $range = 1; }
  if (filter_var($range, FILTER_VALIDATE_INT) === false) {
  	throw new Exception("Invalid group range entered.| $range",ERROR_MAJOR);
  }
  if ( ($range <= 1) or ($range > 1000) ) {
  	$range = 1;
  	$fragment = "limit $per_page";
	 $more = true;
	 $next_range = $range + 1;
  } else {
  	$n = ($range -1)*$per_page;
  	$fragment = "limit $n, $per_page";
	 $less = true;
	 $prev_range = $range -1;
	 if (($range * $per_page) < $num_panels ) { 
	 	$more = true; 
	 $next_range = $range + 1;
	 }  
  };

#  substring_index function used below to fix problems with initial names.  This should be removed after
#  the data is cleansed.   Also change line 129 below to use $row['title'] instead of $row['name'].

  if ($search) {
      $sql = "select *, floor(datediff(now(), dob)/365) as age, substring_index(title,',',1) as name " .
          "from items where title like '%$title%' and is_active = 'Y' and image is not null and groupid = '$groupid'";
  } else {
      $sql = "select *, floor(datediff(now(), dob)/365) as age, substring_index(title,',',1) as name " .
          "from items where is_active = 'Y'  and is_public = 'Y' and image is not null and groupid = '$groupid' order by is_sponsored, seq,itemid $fragment";
  }
  
  
/*  
if ($search) {
  $sql = "select items.*,sponsorships.sponsorid, ".
  		 "floor(datediff(now(), dob)/365) as age, ".
  		 "substring_index(title,',',1) as name " .
  		 "from items left join sponsorships on items.itemid = sponsorships.itemid and effective_end_ts is null ".
  		 "where title like '%$title%' and is_active = 'Y' and image is not null and groupid = '$groupid'";
	
} else {
  $sql = "select items.*,sponsorships.sponsorid, ".
  		 "floor(datediff(now(), dob)/365) as age, ".
  		 "substring_index(title,',',1) as name " .
  		 "from items left join sponsorships on items.itemid = sponsorships.itemid and effective_end_ts is null ".
  		 "where is_active = 'Y'  and is_public = 'Y' and image is not null and groupid = '$groupid' order by is_sponsored, seq,itemid $fragment";
	
}
*/  

  $res = do_sql($sql);
  if ( mysqli_num_rows($res) == 0 ) {
      throw new Exception ("No children found. | search name was $rawtitle ($title)", ERROR_MAJOR);
  }
  
  $first = true;
  $count = 0;
  while ($row = mysqli_fetch_assoc($res)) {
  	$count ++;
      # When age is missing, don't show it.
  	if ( ($row['age'] == null) or ($row['age'] >= 20) ) {
	  	$age = "";
	  } else {
	  	$age = ", age {$row['age']}";
	  }
?>
					<div class="featured featured<?php print $count ?>">
						<a href="index.php#" onclick="doWork(<?php print $row['itemid'] ?>); return false;">
						<img src="dbimg/<?php print $row['image'] ?>" alt="" height="240" />
							<span class='feature_excerpt'>
								<strong class='sliderheading'><?php print $row['name'].$age ?></strong>
								<span class='slidercontent'>
								<?php print $row['summary'];
								switch ($row['is_sponsored']) {
									case 'Y' :
										print " <span class=\"sponsor_tag\">(sponsored)</span>";
										break;
									case 'P' :
										print " <span class=\"sponsor_tag\">(In Process)</span>";
										break;
									default :	
								}
								 ?>
								</span>
							</span>
							</a>
					</div><!-- end .featured -->

<?php } ?>
					
					
				</div><!-- end #featured --> 
				
				<span class='bottom_right_rounded_corner '></span>
				<span class='bottom_left_rounded_corner '></span>
				<span class='top_right_rounded_corner '></span>
				<span class='top_left_rounded_corner '></span>
					
				
		<!-- ###################################################################### -->
		</div><!-- end featuredwrap -->
		<!-- ###################################################################### -->
		
	<!-- end feature_area -->
	</div>
	
	<div class="wrap_fullwidth small_margin" id='main'>
		<div class='center'>
<?php
if ($less) {
   print "<a href=\"index.php?gid=$groupid&group=$prev_range\" class='latest_work'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; More Children</a> ";
}
print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
if ($more) {
   print "<a href=\"index.php?gid=$groupid&group=$next_range\" class=\"show_portfolio\"> More Children </a>";

}
?>
<div id='ajax_text'>
		<h2>&nbsp;<br>Click on a child for more information</h2>
		<form action="" method="get">
		<p>Or search for child named like: <input type="text" size="20" maxlength="32" name="title"> &nbsp;
			<input type="submit" name="submit" value="Go">
			<input type="hidden" name="gid" value="<?php print $groupid ?>"></p>			
		</form>
</div>		
	
		<!-- end center -->
		</div>
		<div class='center'>
						
				<div class='content_one_third portfolio_item'>
					<div class='item_data rounded'>
					<?php if ( isset($group['left_footer_name']) and isset($group['left_footer_url']) ) { ?>
						<h2><a href='<?php print trim($group['left_footer_url']) ?>'><?php print trim($group['left_footer_name']) ?></a></h2>

					<?php } else { ?>
						<h2><a href='howto.php?gid=<?php print $groupid ?>'>How to sponsor a child.</a></h2>						
					<?php } ?>
						
					<!-- end item_data-->
					</div>
				<!-- end portfolio_item-->
				</div>
				
				<div class='content_one_third portfolio_item'>
					<div class='item_data rounded'>
					<?php if ( isset($group['center_footer_name']) and isset($group['center_footer_url']) ) { ?>
						<h2><a href='<?php print trim($group['center_footer_url']) ?>'><?php print trim($group['center_footer_name']) ?></a></h2>

					<?php } else { ?>
						<h2><a href='whatitdoes.html'>What your sponsorship provides.</a></h2>
					<?php } ?>
					
						
					<!-- end item_data-->
					</div>
				<!-- end portfolio_item-->
				</div>
				
				<div class='content_one_third portfolio_item'>
					<div class='item_data rounded'>
					<?php if ( isset($group['right_footer_name']) and isset($group['right_footer_url']) ) { ?>
						<h2><a href='<?php print trim($group['right_footer_url']) ?>'><?php print trim($group['right_footer_name']) ?></a></h2>

					<?php } else { ?>
						<h2><a href='about_koikoi.php'>About the Koi Koi House Project</a></h2>
					<?php } ?>
					
						
					<!-- end item_data--> 
					</div>
				<!-- end portfolio_item-->
				</div>
			<!-- end  #content_fullwidth -->
		</div>
	
	<!-- end footer -->
	</div>
	<div class="wrap_fullwidth" id='footer_bottom'>
	
		<div class='center'>
			<span class='copyright'>Copyright &copy; 2013 The Giving Circle</span>
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
