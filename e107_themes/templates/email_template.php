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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/email_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-11 16:15:04 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$SIGNUPEMAIL_SUBJECT = LAN_404." {SITENAME}";
$SIGNUPEMAIL_USETHEME = TRUE;
$SIGNUPEMAIL_LINKSTYLE = "";

$SIGNUPEMAIL_TEMPLATE = "
<body>
<div style='padding:10px'>
<div class='forumheader3' style='text-align:left; width:90%'>
".LAN_EMAIL_01." {USERNAME},<br />
<br />".
LAN_403." {SITENAME}<br />
".LAN_SIGNUP_21." ...<br />
<br />
{ACTIVATION_LINK}<br />
<br />
".LAN_SIGNUP_18."...<br />
<br />
".LAN_LOGINNAME.": <b> {LOGINNAME} </b><br />
".LAN_PASSWORD.": <b> {PASSWORD} </b><br />
<br />
".LAN_EMAIL_04."<br />
".LAN_EMAIL_05."<br />
<br />
".LAN_EMAIL_06."<br />
<br />
{SITENAME}<br />
{SITEURL}
</div>
</div>
</body>
";
?>