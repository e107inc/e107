<?php
$caption = "Banning 會員s from your site";
$text = "You can ban 會員s from your site at this screen.<br />
Either enter their full IP address or use a wildcard to ban a range of IP addresses. You can also enter an email address to stop a 會員 註冊ing as a member on your site.<br /><br />
<b>Banning by IP address:</b><br />
Entering the IP address 123.123.123.123 will stop the 會員 with that address visiting your site.<br />
Entering the IP address 123.123.123.* will stop anyone in that IP range from visiting your site.<br /><br />
<b>Banning by email address</b><br />
Entering the email address foo@bar.com will stop anyone using that email address from 註冊ing as a member on your site.<br />
Entering the email address *@bar.com will stop anyone using that email do主要的 from 註冊ing as a member on your site.";
$ns -> tablerender($caption, $text);
?>