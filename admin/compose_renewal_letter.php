<?php
function compose_renewal($groupid, $row) {
global $url;

if ($groupid == 100) {
$body = "Dear%20{$row['display_name']}:%0A%0A

Your sponsorship of {$row['child']} will soon expire.%0A%0A

Your past generosity has helped provide {$row['child']} with:%0A%0A


&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A school uniform, books, and supplies%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;School tuition (Ugandan schools are not free)%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Food for the past year%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Medical care %0A%0A


Would you please continue your support by renewing this sponsorship.%0A%0A

You can renew at {$url}/sponsor1.php?itemid={$row['childid']}%26renew={$row['c_hash']}%0A%0A

or if you prefer to pay by check,%0A%0A
Make your check payable to The Giving Circle%0A%0A

and mail it to:%0A%0A

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Laurie Murphy%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;18 Victoria Lane%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saratoga Springs NY 12866%0A%0A

We look forward to your continued support of our children.%0A%0A

AOET and The Giving Circle have partnered to provide continued support for these children.%0A%0A

";	
} else {

$body = "Dear%20{$row['display_name']}:%0A%0A

Your sponsorship of {$row['child']} has expired.%0A%0A

You past generosity has helped provide {$row['child']} with:%0A%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;A school uniform, books, and supplies%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;School tuition (Ugandan schools are not free)%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Food for the past year%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Medical care%0A%0A
      
Would you please continue your support by renewing this sponsorhip.%0A%0A

You can renew online at: {$url}/sponsor1.php?itemid={$row['childid']}%26renew={$row['c_hash']}%0A%0A 

or you can mail your check to:%0A%0A

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;The Giving Circle%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;P.O. Box 3162%0A
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saratoga Springs, NY  12866%0A%0A

We look forward to your continued support of our children.%0A%0A

Erin Walborn%0A
Email:  erinwalborn@gmail.com%0A
Phone:  518.424.3803%0A

";
}

return $body;
}
?>