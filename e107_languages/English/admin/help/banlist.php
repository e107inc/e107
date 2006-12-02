<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/banlist.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:42 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Banning users from your site";
$text = "You can ban users from your site at this screen.<br />
Either enter their full IP address or use a wildcard to ban a range of IP addresses. You can also enter an email address to stop a user registering as a member on your site.<br /><br />
<b>Banning by IP address:</b><br />
Entering the IP address 123.123.123.123 will stop the user with that address visiting your site.<br />
Entering the IP address 123.123.123.* will stop anyone in that IP range from visiting your site.<br /><br />
<b>Banning by email address</b><br />
Entering the email address foo@bar.com will stop anyone using that email address from registering as a member on your site.<br />
Entering the email address *@bar.com will stop anyone using that email domain from registering as a member on your site.";
$ns -> tablerender($caption, $text);
?>