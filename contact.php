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
|     $Source: /cvs_backup/e107_0.7/contact.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-24 21:42:06 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if (!$CONTACT_FORM) {
	if (file_exists(THEME."contact_template.php")) {
		require_once(THEME."contact_template.php");
	} else {
		require_once(e_THEME."templates/contact_template.php");
	}
}

if(isset($_POST['send-contactus'])){

	$sender_name = $tp->post_toHTML($_POST['author_name']);
	$sender = check_email($_POST['email_send']);
	$subject = $tp->post_toHTML($_POST['subject']);
	$body = $tp->post_toHTML($_POST['body']);

    require_once(e_HANDLER."mail.php");
 	$message =  (sendemail(SITEADMINEMAIL,"[".SITENAME."] ".$subject, $body,ADMIN,$sender,$sender_name)) ? LANCONTACT_09 : LANCONTACT_10;
    if($_POST['email_copy'] == 1){
		sendemail($sender,"[".SITENAME."] ".$subject, $body,ADMIN,$sender,$sender_name);
    }

    $ns -> tablerender('', $message);
	require_once(FOOTERF);
	exit;
}


if(SITECONTACTINFO && $CONTACT_INFO){
	$text = $tp->toHTML($CONTACT_INFO,"","parse_sc");
	$ns -> tablerender(LANCONTACT_01, $text);
}

$text = $CONTACT_FORM;

if(trim($text) != ""){
	$ns -> tablerender(LANCONTACT_02, $text);
}
require_once(FOOTERF);
exit;
?>