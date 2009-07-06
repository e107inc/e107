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
|     $Source: /cvs_backup/e107_0.8/e107_themes/templates/email_template.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-07-06 07:50:44 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

global $pref;
$SIGNUPEMAIL_SUBJECT = LAN_SIGNUP_96." {SITENAME}";
$SIGNUPEMAIL_USETHEME = 1; // Use CSS STYLE from THEME: 0 = Off, 1 = external, 2 = embedded
$SIGNUPEMAIL_LINKSTYLE = ""; // css to use on links eg. color:red;
$SIGNUPEMAIL_IMAGES =  e_IMAGE.$pref['sitebutton']; // comma separated paths to image to embed. referenced below with {IMAGE1} (IMAGE2} etc.
$SIGNUPEMAIL_CC = "";  // comma separated email addresses to put in CC of the signup email.
$SIGNUPEMAIL_BCC = "";   // comma separated email addresses to put in BCC of the signup email.
$SIGNUPEMAIL_ATTACHMENTS = ""; // files-path array of attachments. eg. array(e_FILE."myfile.zip",e_FILE."myotherfile.zip");
$SIGNUPEMAIL_BACKGROUNDIMAGE = "";// relative path to a background image eg. e_IMAGE."mybackground.jpg";

// Optional admin preferences Override.
$EMAIL_METHOD = "";  // php, smtp or sendmail
$EMAIL_SMTP_SERVER = ""; // smtp.myserver.com
$EMAIL_SMTP_USER = "";
$EMAIL_SMTP_PASS = "";
$EMAIL_SENDMAIL_PATH = "";
$EMAIL_FROM = "";  // admin@mysite.com
$EMAIL_FROM_NAME = ""; // Admin



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
LAN_SIGNUP_97." {SITENAME}<br />
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