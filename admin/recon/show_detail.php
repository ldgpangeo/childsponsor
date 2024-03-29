<?php
try {
    
    include_once("../../lib/common-init.php");
    $all = getinput("all", "N");
    if ($all == 'Y') {
        $fragment = '';
    } else {
        $fragment = "and r.is_active = 'Y' ";
    }
    #  create a list of all sponsors
    $sponsor_list = '';
    $sql = "select distinct(r.civicrmid) id, c.sort_name, c.email from r_recon r, cvcontacts c where c.id = r.civicrmid $fragment order by c.sort_name";
    $res = do_sql($sql);
    while ($row = mysqli_fetch_assoc($res) ) {
        $sponsor_list .= "<option value=\"{$row['sort_name']}, {$row['email']}    |{$row['id']}|{$all}\"</option>\n";
    }
    
    
    #  create a list of all children
    $child_list = '';
    $sql = "select distinct(itemid), child from sponsorship_summary order by child";
    $res = do_sql($sql);
    while ($row = mysqli_fetch_assoc($res) ) {
        $child_list .= "<option value=\"{$row['child']}    |{$row['itemid']}\"</option>\n";
    }
    
    $header = "<script type='text/javascript' src='ajax.js'></script> ";
    include "page_header.php" ;
    
    ?>

<H2 align="center">Find a Sponsorship</H2> 
<form action="show_recon.php" method = "post">
 
<Table width="80%" border="1" cellpadding="3" cellspacing="3" align="center">
<tr><th bgcolor="#ffffcc">Search by Sponsor</th></tr>
<tr><td>
Sponsor name is:  <input id="sponsor" name="civicrmid" list="sponsors" size="60" type="text" autocomplete="off" onchange="doWork(this.value,'<?php print $all ?>'); return false;" >
<datalist id="sponsors">
<?php print $sponsor_list ?>
</datalist> (Click outside input box to show children)
<br />
<div id="ajax_text">

</div>
</td>
</tr>
<tr><td align="center" bgcolor="#ffffcc">
<a href="index.php" class="myGreen">Home</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="hidden" name="allrecon" value = "<?php print $all ?>">
<input type="submit" name="submit" value="Show details" class="myBlue">
</Table>
 </form>

  <form action="show_recon.php" method = "post" name="child"> 
  <p>&nbsp;</p>
  <p>&nbsp;</p>
<Table width="80%" border="1" cellpadding="3" cellspacing="3" align="center">
<tr><th bgcolor="#ffffcc">Search by Child</th></tr>
<tr><td>
Child name is:  <input id="child" name="itemid2" list="children" size="60" type="text" autocomplete="off" onblur="doChild(this.value); return false;" >
<datalist id="children">
<?php print $child_list ?>
</datalist> (Click outside input box to show sponsors)
<br />
<div id="ajax_child">

</div>
</td>
</tr>
<tr><td align="center" bgcolor="#ffffcc">
<a href="index.php" class = "myGreen" >Home</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit" value="Show details" class="MyBlue">
</Table>
</form>

<?php
} catch(Exception $err) {
	$trace = $err->getFile()." Line:".$err->getLine().", ".$err->getTraceAsString();
	logerr($err->getMessage(),$err->getCode(),$trace);
}

?>
