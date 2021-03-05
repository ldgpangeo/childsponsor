<?php

include "common-init.php";

$item_id = $_GET['item_id'];
$renew = $_GET['renew'];
$is_renew = ($renew == 1) ; 
# debug("ajax:  item_id is $item_id");
if ($item_id > 0) {
	$sql = "select monthly, yearly, groupid, is_sponsored, max_sponsors, first_monthly, first_yearly from items where itemid =  '$item_id'";
	$res = do_sql($sql);
	if (mysqli_num_rows($res) == 1) {
		$monthly = mysqli_result($res,0,'monthly');
		$yearly  = mysqli_result($res,0,'yearly');
		$groupid = mysqli_result($res,0,'groupid');
		$is_sponsored = mysqli_result($res,0,'is_sponsored');
		$max_sponsors = mysqli_result($res,0,'max_sponsors');
		$first_monthly = mysqli_result($res,0,'first_monthly');
		$first_yearly = mysqli_result($res,0,'first_yearly');
	} else {
		die("unable to find child information.");
	}
	#  get the relevant sponsor information
	$res = do_sql("select monthly, yearly, max_sponsors, allow_price_changes from groups where groupid = '$groupid'");
	if (mysqli_num_rows($res) <> 1) {
		die("Unable to find group information for this child.");
	} else {
		if ($monthly == '') {$monthly = mysqli_result($res,0,'monthly');}
		if ( $yearly == '') {$yearly  = mysqli_result($res,0,'yearly'); }
		if (! isset($max_sponsors) ) {
		    $max_sponsors = mysqli_result($res,0,'max_sponsors');
		}
	}
	if ( (mysqli_num_rows($res) == 0) or (mysqli_result($res,0,'monthly') == '') or (mysqli_result($res,0,'yearly') == '') ) {
		$sql = "select monthly,yearly from groups where groupid = (select groupid from items where itemid = '$item_id')";
		$res = do_sql($sql);
	}
	if ( ($monthly > 0) and ($yearly > 0) ) {
		if ($max_sponsors == 1) {
			$out = "
			<input type=\"radio\" name=\"interval\" value = \"monthly\"> &nbsp; Recurring Monthly payments of \${$monthly} <br>
			<input type=\"radio\" name=\"interval\" value = \"yearly\"> &nbsp; Recurring Annual payment of \${$yearly}
			<input type=\"hidden\" name=\"monthly_amt\" value=\"$monthly\">
			<input type=\"hidden\" name=\"yearly_amt\" value=\"$yearly\">
			";
		} else {
		    $second_monthly = $monthly - $first_monthly;
		    $second_yearly = $yearly - $first_yearly;
		    
			# need to see if this person already has any splits
			$sql = "select i.itemid  from sponsorships s,(select itemid,is_sponsored from items) i " . 
				   " where i.itemid = s.itemid and s.effective_end_ts is null  and s.itemid = '$item_id'" . 
			       " union select itemid from sponsorship_pending p where p.itemid = '$item_id' "  ;
			$res = do_sql($sql);
			$prior = mysqli_num_rows($res);
			$first_portion = false;
			$second_portion = false;
			if ($prior >= $max_sponsors) {
			    $out = "I'm sorry but this child is already fully sponsored.";
			} else  {
			    $first_portion = true;
			    $second_portion = true;
			} 
			if ($prior == 1) {
			    #  if only one, figure out if it's the first or second sponsor.
			    $sql = "select total_amount from cvsponsors where childid = '$item_id'";
			    $res = do_sql($sql);
			    $total_amount = mysqli_result($res, 0, 'total_amount');
			    if ($total_amount == '') {
			        #  If not in sponsored then look for it in pending
			        $sql = "select amt as total_amount from sponsorship_pending where itemid = '$item_id' ";
			        $res = do_sql($sql);
			        $total_amount = mysqli_result($res, 0, 'total_amount');
			    }
			    if (($total_amount == $first_yearly) or ($total_amount == $first_monthly)) {
			        $first_portion = false;
			        $second_portion = true;
			    }
			    if (($total_amount == $second_yearly) or ($total_amount == $second_monthly)) {
			        $first_portion = true;
			        $second_portion = false;
			    }
			    
			}
#			if ($prior < $max_sponsors) {
#			    $out = '';
			    if ($first_portion) {
    			    $out .= "First portion:<br>
	           		 <input type=\"radio\" name=\"interval\" value = \"first_monthly\"> &nbsp; Recurring Monthly payments of \${$first_monthly} <br>
			         <input type=\"radio\" name=\"interval\" value = \"first_yearly\"> &nbsp; Recurring Annual payment of \${$first_yearly}
			         <input type=\"hidden\" name=\"first_monthly\" value=\"$first_monthly\">
			         <input type=\"hidden\" name=\"first_yearly\" value=\"$first_yearly\">
                     <br>&nbsp;<br>
                     ";
			    }
			    if ($second_portion) {
        			$out .= "Second portion:<br>
			         <input type=\"radio\" name=\"interval\" value = \"second_monthly\"> &nbsp; Recurring Monthly payments of \${$second_monthly} each<br>
			         <input type=\"radio\" name=\"interval\" value = \"second_yearly\"> &nbsp; Recurring Annual payment of \${$second_yearly}
			         <input type=\"hidden\" name=\"second_monthly\" value=\"$second_monthly\">
			         <input type=\"hidden\" name=\"second_yearly\" value=\"$second_yearly\">
                     <br>&nbsp;<br>
                     ";
			    }
			    if ($out == '') {
			        $out .= "I'm sorry, but there has been an internal malfunction.";
			    }
			
#		}
		debug("ajax: $out ");
	}
	if (strlen($out) > 0) {
		print "$out\n";
	} else {
		print "No information available for this child.\n";
	}
}  #  monthly and yearly are not empty.
}  # end itemid is not zero
?>