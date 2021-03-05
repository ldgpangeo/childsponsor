<?php
include_once("./common-init.php");

$doit = getinput('doit');
# flag for cleaning up old stuff
if ($doit == "Yes") { $clean = true; } else { $clean = false; } 

# get recipient list
$sender = get_dictionary_setting('sender','reports','ldgpangeo@gmail.com');
$recipients = get_dictionary_setting('recipients','reports','ldgpangeo@gmail.com');
# $recipients = "/home/geoffrion15983/public_html/childsponsor/lib";

# initial settings
$message = '';
$send_message = false;
$title = '';

#  Are there unfinished pending sponsorship
$res = do_sql("select s.*,i.title, s.hash from sponsorship_pending s, items i where s.itemid = i.itemid and is_locked = 'N'");
$row_count = mysqli_num_rows($res);
$pendings = array ();
if ($row_count > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $ts = date_parse($row['datedone']);
        $start = strftime('%F %T',mktime($ts['hour'] - 1, $ts['minute'], $ts['second'], $ts['month'], $ts['day'], $ts['year']) );
        $end =   strftime('%F %T',mktime($ts['hour'] + 3, $ts['minute'], $ts['second'], $ts['month'], $ts['day'], $ts['year']) );
        $sql = "select * from cvsponsors where childid = '{$row['itemid']}' ";
        $sql .= " and sort_name like '{$row['last_name']}%' ";
        $sql .= " and receive_date between '$start' and '$end' ";
        $cvres = do_sql($sql);
        if ( mysqli_num_rows($cvres) > 0 ) {
            $ch = curl_init("https://www.thegivingcircle.org/childsponsor/finish.php?childid={$row['itemid']}&hash={$row['hash']}");
            $fp = fopen("/dev/null", "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            logit("Auto-Sponsorship-Recorded","sponsor = {$row['first_name']} {$row['last_name']}, child = {$row['title']}, id = {$row['itemid']}, hash= {$row['hash']}");
        } else {
            array_push ($pendings, $row);
        }
    }
    $count = count($pendings);
    if ($count > 0 ) {
	   $send_message = true;
	   $message .= "There are $count pending sponsorships\n";
	   $message .= "Name                           child             child_id         date/time\n";
	   $message .= "---------------------------------------------------------------------------------\n";
	   foreach ( $pendings as $row ) {
		  $message .= sprintf('%-25s', $row['first_name']." ".$row['last_name']);
		  $message .= sprintf('%-20s', $row['title']);
		  $message .= sprintf('%10s      ', $row['itemid']);
		  $message .= sprintf('%15s', strftime('%D %r', strtotime($row['datedone']))) ;
		  $message .= " \n";
	   }
	   $message .= "--------------------------------------------------------------------------------\n\n";
	   if ($clean) {
		  $res = do_sql("delete from sponsorship_pending where is_Locked = 'N'");
		  $message .= "Pending sponsorships have been purged.\n\n";
	   } else {
		  $message .= "Pending sponsorships have been retained.\n\n";
	   }
    }
}

# establish yesterday's parameters
$start = strftime('%Y-%m-%d',strtotime("yesterday"));
$start_us =  strftime('%m/%d/%y',strtotime("yesterday"));
$end = strftime('%Y-%m-%d 23:59:59',strtotime("yesterday"));

#  Were there any sponsorships?
$res = do_sql("select s.*, i.title from sponsorships s, items i where i.itemid = s.itemid and s.effective_end_ts is null and effective_start_ts between '$start' and '$end'");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There were NO Sponsorships in the previous 24 hours.\n\n\n";
} else {
	$send_message = true;
                $message .= "Sponsorships in the previous 24 hours\n";
				$message .= "  Sponsor                      Child                ID           Date\n";
                $message .= "-------------------------------------------------------------------------------\n";
        While ($row = mysqli_fetch_assoc($res)) {
		$message .= sprintf('%-30s', $row['first_name']." ".$row['last_name']);
		$message .= sprintf('%-15s', $row['title']);
		$message .= sprintf('%10s     ', $row['itemid']) ;
		$message .= sprintf('%15s', strftime('%D %r', strtotime($row['effective_start_ts']))) ;
		$message .= "\n";
                        }
        $message .= "------------------------------------------------------------------------\n";
}



#  Were there any administrative events?
$res = do_sql("select * from logs where event_code like '%ADMIN_%' and datedone between '$start' and '$end' order by datedone ");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There were NO administrative actions in the previous 24 hours.\n\n\n";
} else {
	$send_message = true;
                $message .= "Administrative actions in the previous 24 hours\n";
                $message .= "------------------------------------------------------------------------\n";
        While ($row = mysqli_fetch_assoc($res)) {
                $message .= "Date/time:    ".$row['datedone']."\n";
                $message .= "Event Code:   ".$row['event_code']."\n";
                $message .= "Detail:       ".$row['detail']."\n";
                $message .= "IP:           ".$row['ip']."\n\n";
                        }
        $message .= "------------------------------------------------------------------------\n";
}



#  Were there any security events?
$res = do_sql("select * from logs where security = 'Y' and datedone between '$start' and '$end' order by datedone ");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There were NO security events in the previous 24 hours.\n\n\n";
} else {
	$send_message = true;
    $message .= "Suspicious Security events in the previous 24 hours\n";
    $message .= "date/time                     IP        Event Code                   detail\n";
    $message .= "------------------------------------------------------------------------\n";
    While ($row = mysqli_fetch_assoc($res)) {
          $message .= sprintf('%-18s %16s %-25s %-60s',us_date($row['datedone']), $row['ip'], $row['event_code'], $row['detail'] );
          $message .= "\n";
    }
    $message .= "------------------------------------------------------------------------\n\n\n";
}


#  Were there any errors?
$res = do_sql("select * from errors where severity > 2 and datedone between '$start' and '$end' 
               and not message like '%search name was  ()%' order by severity desc, datedone");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There were NO errors in the previous 24 hours.\n\n\n";
} else {
	$send_message = true;
                $message .= "Application Errors in the previous 24 hours\n";
                $message .= "------------------------------------------------------------------------\n";
        While ($row = mysqli_fetch_assoc($res)) {
                $message .= "Severity:     ".$row['severity']."\n";
                $message .= "Date/time:    ".$row['datedone']."\n";
                $message .= "Message:      ".$row['message']."\n";
                $message .= "Trace:        ".$row['trace']."\n";
                $message .= "IP:           ".$row['ip']."\n\n";
        }
        $message .= "------------------------------------------------------------------------\n\n";
}

#  Database audit  -- checking for internal integrity.

#  incompletely ended sponsorships
$res = do_sql("select i.itemid, title from items i, sponsorships s where i.itemid = s.itemid and is_sponsored = 'N' and effective_end_ts is null");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There are NO incomplete sponsorships.\n\n\n";
} else {
	$send_message = true;
    $message .= "Children with incomplete sponsorship termination\n";
    $message .= "------------------------------------------------------------------------\n";
   	$message .= "ID               Name\n";
        While ($row = mysqli_fetch_assoc($res)) {
		$id = sprintf($row['itemid'],'%4s');
		$message .= "$id          {$row['title']}\n";
    }
    $message .= "------------------------------------------------------------------------\n\n";
}
		   	
$res = do_sql("select itemid, total, contactid from (select itemid, count(*) as total, contactid from sponsorships where effective_end_ts is null group by itemid,contactid) t where total > 1");
$rows = mysqli_num_rows($res);
if ($rows == 0) {
        $message .= "There are NO duplicate sponsorships.\n\n\n";
} else {
	$send_message = true;
    $message .= "Children with duplicate sponsorship termination\n";
    $message .= "------------------------------------------------------------------------\n";
    While ($row = mysqli_fetch_assoc($res)) {
		$message .=  $row['itemid']. "        " . $row['contactid']. "\n";
    }
    $message .= "------------------------------------------------------------------------\n\n";
}

# check for sponsorships with missing CiviCRM link.

$sql = "select i.itemid, title, count(contact_id) as total from items i " .
		"left join cvsponsors c on i.itemid = c.childid, sponsorships s " .
		"where i.itemid = s.itemid and is_sponsored = 'Y' and effective_end_ts is null " . 
		" and floor(i.childid) > 0 " .
		"group by i.itemid order by count(contact_id)";
$res = do_sql($sql);
$badcivi = array();
while ($row = mysqli_fetch_assoc($res)) {
	if ($row['total'] == 0) {
		array_push($badcivi, $row);
	}
}
if (count($badcivi) == 0) {
	$message .= "There are no bad CiviCRM sponsorship links.\n\n";
} else {
	$sendmessage = true;
	$message .= "Children marked as sponsored with no CiviCRM connection.\n";
	$message .= "------------------------------------------------------------------------\n";
	$message .= "ID               Name\n";
	foreach ($badcivi as $row) {
		$id = sprintf($row['itemid'],'%4s');
		$message .= "$id          {$row['title']}\n";
	}
	$message .= "------------------------------------------------------------------------\n\n";
}
		
/*
		select i.itemid, title, count(contact_id) as total from items i left join cvsponsors c on i.itemid = c.childid, sponsorships s where i.itemid = s.itemid and is_sponsored = 'Y' and effective_end_ts is null group by i.itemid, order by count(contact_id)
*/		


if ($send_message) {
	$subject = "Daily Child Sponsorship Report for $start";
	send_mail("Child Sponsorship", $sender, $subject, $message, $recipients);
	debug ("Sending mail to $recipients with mesage $message");
} else {
	$subject = "No activity on $start";
	send_mail("Child Sponsorship", $sender, $subject, 'Nothing significant happened. ', $recipients);
	debug ("sending mail will all is well textto $recipients");
}

?>
