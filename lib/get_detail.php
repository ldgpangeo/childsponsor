<?php

include "common-init.php";

$panel_id = $_GET['panel_id'];
debug("ajax:  panel_id is $panel_id");
if ($panel_id > 0) {
    #  Is this child available for sponsorship?
    $sql = "select itemid from sponsorship_pending where itemid = '$panel_id' 
                union all select itemid from sponsorships where itemid = '$panel_id' and effective_end_ts is null";
    $res = do_sql($sql);
    $current = mysqli_num_rows($res);
    
#	$sql = "select body_text, appear,appear_text, last_name,video_url,is_sponsored, max_sponsors from items i
#	           left join sponsorships s on s.itemid = i.itemid and effective_end_ts is null 
#	           left join items_videos v on i.itemid = v.itemid and v.is_active = 'Y'
#	            where i.itemid =  '$panel_id'";
    $sql = "select  * from items where itemid =  '$panel_id'";
	$res = do_sql($sql);
	if (mysqli_num_rows($res) > 0) {
		$row = mysqli_fetch_assoc($res);
		$out = html_entity_decode(stripslashes($row['body_text']));
		#  create a new unique hash value
		$unique = false;
		while (! $unique) {
		    $tmp = rand();
		    $restmp = do_sql("select hash from sponsorships where hash = '$tmp' union all select hash from sponsorship_pending where hash = '$tmp'");
		    if (mysqli_num_rows($restmp) == 0) {$unique = true;}
		}
		$hash = $tmp;
		if ( $row[max_sponsors] <= 1) {
		    $is_sponsored = $row['is_sponsored'];
		} elseif ($current > 0) {
		    $is_sponsored = 'Y';
		} else {
		    $is_sponsored = 'N' ;
		}
		debug ("is_sponsored is $is_sponsored with max at {$row[max_sponsors]} and current at $current");
		switch ($is_sponsored) {
			case 'N' :
				$sponsor = "<p><a href=\"sponsor1.php?itemid=$panel_id&hash=$hash\">I want to sponsor this child.</a></p>";
				break;
			default :
			    $sponsor = '';
			    if ($row['max_sponsors'] > 1) {$frag = "Shared Sponsor: "; }
			    $sql = "select * from sponsorships where itemid = '$panel_id' and effective_end_ts is null";
                $res2 = do_sql($sql);
                while ($row2 = mysqli_fetch_assoc($res2) ) {
				if ($row2['appear'] == 'Y') {
					if ($row2['appear_text'] <> '') {
						$sponsor .= "<p><span class=\"sponsored\">" . $frag . stripslashes($row2['appear_text']);
					} else {
						$sponsor .= "<p><span class=\"sponsored\">$frag Sponsored by the </span><span class=\"sponsorname\">" . stripslashes($row2['last_name']) . " Family.";
					}
				} else {
					$sponsor .= "<p><span class=\"sponsored\">$frag Sponsored by Anonymous.";
				}
				$sponsor .= "</p>";
				debug ("Sponsor text is $sponsor");
				}
				$sql = "select * from sponsorship_pending where itemid = '$panel_id'";
				$res2 = do_sql($sql);
				while ($row2 = mysqli_fetch_assoc($res2) ) {
				    if ($row2['appear'] == 'Y') {
				        if ($row2['appear_text'] <> '') {
				            $sponsor .= "<p><span class=\"sponsor\">$frag " . "(pending) " . stripslashes($row2['appear_text']);
				        } else {
				            $sponsor .= "<p><span class=\"sponsored\">$frag Pending sponsor by the </span><span class=\"sponsorname\">" . stripslashes($row2['last_name']) . " Family.";
				        }
				    } else {
				        $sponsor .= "<p><span class=\"sponsored\">$frag Pending sponsor by Anonymous.";
				    }
				    $sponsor .= "</p>";
				    debug ("Sponsor pending text is $sponsor");
				}
				if ($current < $row[max_sponsors]) {
				    if ($row[max_sponsors] == 1) {
				        $sponsor .= "<p><a href=\"sponsor1.php?itemid=$panel_id&hash=$hash\">I want to sponsor this child.</a></p>";
				    } else {
				        $sponsor .= "<p><a href=\"sponsor1.php?itemid=$panel_id&hash=$hash\">I want to cosponsor this child.</a></p>";
				    }
				}
				
		}
		$sponsor .= "</span></p>";
		debug("ajax: $out $sponsor");
	}
	if (strlen($out) > 0) {
		print "$out $sponsor\n";
	} else {
		print "No information available for this child.\n";
	}
}

?>