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
|     $Revision: 1.5 $
|     $Date: 2006-07-16 19:56:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(!isset($pref['contact_emailcopy']) || !$pref['contact_emailcopy'])
{
	$CONTACT_EMAIL_COPY = " ";
}

if (!$CONTACT_FORM) {
	if (file_exists(THEME."contact_template.php")) {
		require_once(THEME."contact_template.php");
	} else {
		require_once(e_THEME."templates/contact_template.php");
	}
}

if(isset($_POST['send-contactus'])){

	$error = "";

	$sender_name = $tp->post_toHTML($_POST['author_name']);
	$sender = check_email($_POST['email_send']);
	$subject = $tp->post_toHTML($_POST['subject']);
	$body = $tp->post_toHTML($_POST['body']);

// Check message body.
	if(strlen(trim($_POST['body'])) < 15)
	{
		$error .= LANCONTACT_12."\\n";
    }

// Check subject line.
	if(strlen(trim($_POST['subject'])) < 2)
	{
		$error .= LANCONTACT_13."\\n";
    }


// Check email address on remote server (if enabled).
	if ($pref['signup_remote_emailcheck'] && $error == "")
	{
		require_once(e_HANDLER."mail_validation_class.php");
		list($adminuser,$adminhost) = split ("@", SITEADMINEMAIL);
		$validator = new email_validation_class;
		$validator->localuser= $adminuser;
		$validator->localhost= $adminhost;
		$validator->timeout=3;
		//	$validator->debug=1;
		//	$validator->html_debug=1;
		if($validator->ValidateEmailBox($sender) != 1)
		{
			$error .= LANCONTACT_11."\\n";
		}

	}

// No errors - so proceed to email the admin and the user (if selected).
    if(!$error)
	{
		$body .= "\n\nIP:\t".USERIP."\n";
		$body .= "User:\t#".USERID." ".USERNAME."\n";

    	require_once(e_HANDLER."mail.php");
 		$message =  (sendemail(SITEADMINEMAIL,"[".SITENAME."] ".$subject, $body,ADMIN,$sender,$sender_name)) ? LANCONTACT_09 : LANCONTACT_10;
    	if(isset($pref['contact_emailcopy']) && $pref['contact_emailcopy'] && $_POST['email_copy'] == 1){
			sendemail($sender,"[".SITENAME."] ".$subject, $body,ADMIN,$sender,$sender_name);
    	}
    	$ns -> tablerender('', $message);
		require_once(FOOTERF);
		exit;
    } else {
		require_once(e_HANDLER."message_handler.php");
		message_handler("ALERT", $error);
	}

}

if(SITECONTACTINFO && $CONTACT_INFO){
	$text = $tp->toHTML($CONTACT_INFO,"","parse_sc");
	$ns -> tablerender(LANCONTACT_01, $text,"contact");
}

$text = $CONTACT_FORM;

if(trim($text) != ""){
	$ns -> tablerender(LANCONTACT_02, $text,"contact");
}
require_once(FOOTERF);
exit;
?>