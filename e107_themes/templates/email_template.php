<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $pref;
$SIGNUPEMAIL_SUBJECT = LAN_404." {SITENAME}";
$SIGNUPEMAIL_USETHEME = 1; // Use CSS STYLE from THEME: 0 = Off, 1 = external, 2 = embedded
$SIGNUPEMAIL_LINKSTYLE = ""; // css to use on links eg. color:red;
$SIGNUPEMAIL_IMAGES =  e_IMAGE.$pref['sitebutton']; // comma separated paths to image to embed. referenced below with {IMAGE1} (IMAGE2} etc.
$SIGNUPEMAIL_CC = "";  // comma separated email addresses to put in CC of the signup email.
$SIGNUPEMAIL_BCC = "";   // comma separated email addresses to put in BCC of the signup email.
$SIGNUPEMAIL_ATTACHMENTS = ""; // files-path array of attachments. eg. array(e_FILE."myfile.zip",e_FILE."myotherfile.zip");
$SIGNUPEMAIL_BACKGROUNDIMAGE = "";// relative path to a background image eg. e_IMAGE."mybackground.jpg";


$EMAIL_HEADER = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' >
<head><meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />
{STYLESHEET}
</head>
<body>
<div style='padding:10px'>
";


$EMAIL_FOOTER = "
<br /><br />
{SITENAME=link}
</div>
</body>
</html>";



$SIGNUPEMAIL_TEMPLATE = "
<div style='padding:10px'>
<div style='text-align:left; width:90%'>
".LAN_EMAIL_01." {USERNAME},<br />
<br />".
LAN_403." {SITENAME}<br />
".LAN_SIGNUP_21."<br />
<br />
{ACTIVATION_LINK}<br />
<br />
".LAN_SIGNUP_59."<br />
<br />
".LAN_SIGNUP_18."<br />
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
<br /><br />
{IMAGE1}
</div>
</div>
";
?>