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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/mail.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-12-30 18:55:42 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

	if(is_readable(THEME."email_template.php"))
	{
    	require_once(THEME."email_template.php");
	}
	else
	{
    	require_once(e_THEME."templates/email_template.php");
	}

    if(isset($EMAIL_HEADER) && isset($EMAIL_FOOTER) && is_object($tp)){
		$EMAIL_HEADER = $tp->parseTemplate($EMAIL_HEADER);
		$EMAIL_FOOTER = $tp->parseTemplate($EMAIL_FOOTER);
	}
/*
Please note that mailed attachments have been found to be corrupted using php 4.3.3
php 4.3.6 does NOT have this problem.
*/
// Comment out the line below if you have trouble with some people not receiving emails.
// e107_ini_set(sendmail_path, "/usr/sbin/sendmail -t -f ".$pref['siteadminemail']);

function sendemail($send_to, $subject, $message, $to_name, $send_from, $from_name, $attachments='', $Cc='', $Bcc='', $returnpath='', $returnreceipt='',$inline ="") {
	global $pref,$mailheader_e107id;

	require_once(e_HANDLER."phpmailer/class.phpmailer.php");

	$mail = new PHPMailer();

    if($mailheader_e107id){
		$mail->AddCustomHeader("X-e107-id: {$mailheader_e107id}");
    }

	if ($pref['mailer']== 'smtp') {

		if(isset($pref['smtp_pop3auth']) && $pref['smtp_pop3auth']){
			// http://www.corephp.co.uk/archives/18-POP-before-SMTP-Authentication-for-PHPMailer.html
			require_once(e_HANDLER."phpmailer/class.pop3.php");
			$pop = new POP3();
			$pop->Authorise($pref['smtp_server'], 110, 30, $pref['smtp_username'], $pref['smtp_password'], 1);
        }

		$mail->Mailer = "smtp";
	 	$mail->SMTPKeepAlive = FALSE;
		$mail->Host = $pref['smtp_server'];
		if($pref['smtp_username'] && $pref['smtp_password']){
			$mail->SMTPAuth = (isset($pref['smtp_pop3auth']) && $pref['smtp_pop3auth']) ? FALSE : TRUE;
			$mail->Username = $pref['smtp_username'];
			$mail->Password = $pref['smtp_password'];
			$mail->PluginDir = e_HANDLER."phpmailer/";
		}

	} elseif ($pref['mailer']== 'sendmail'){
		$mail->Mailer = "sendmail";
		$mail->Sendmail = ($pref['sendmail']) ? $pref['sendmail'] : "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'];
	} else {
        $mail->Mailer = "mail";
	}

	$to_name = ($to_name) ? $to_name: $send_to;

	$mail->CharSet = CHARSET;
	$mail->From = ($send_from)? $send_from: $pref['siteadminemail'];
	$mail->FromName = ($from_name)? $from_name:	$pref['siteadmin'];
	$mail->Subject = $subject;
	$mail->SetLanguage("en",e_HANDLER."phpmailer/language/");

	$lb = "\n";


	// Clean up the HTML. ==

	if (preg_match('/<(font|br|a|img|b)/i', $message)) {
		$Html = $message; // Assume html if it begins with one of these tags
	} else {
		$Html = htmlspecialchars($message);
		$Html = preg_replace('%(http|ftp|https)(://\S+)%', '<a href="\1\2">\1\2</a>', $Html);
		$Html = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '\\1<a href="http://\\2">\\2</a>', $Html);
		$Html = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/i', '<a href="mailto:\\1">\\1</a>', $Html);
	    $Html = str_replace("\r","\n",$Html);		// Handle alternative newline characters
		$Html = str_replace("\n", "<br />\n", $Html);
	}
	if (strpos($message,"</style>") !== FALSE){
    	$text = strstr($message,"</style>");
	}else{
    	$text = $message;
	}
    $text = str_replace("<br />", "\n", $text);
  	$text = strip_tags(str_replace("<br>", "\n", $text));

	$mail->Body = $Html; //Main message is HTML
	$mail->IsHTML(TRUE);
 	$mail->AltBody = $text; //Include regular plaintext as well


	$tmp = explode(",",$send_to);
    foreach($tmp as $adr){
		$mail->AddAddress($adr, $to_name);
    }


		if ($attachments){
			if (is_array($attachments))	{
				foreach($attachments as $attach){
                    if(is_readable($attach)){
						$mail->AddAttachment($attach, basename($attach),"base64",mime_content_type($attach));
                    }
				}
			}else{
				if(is_readable($attachments)){
					$mail->AddAttachment($attachments, basename($attachments),"base64",mime_content_type($attachments));
                }
			}
		}

		if($inline){
			$tmp = explode(",",$inline);
			foreach($tmp as $inline_img){
				if(is_readable($inline_img) && !is_dir($inline_img)){
					$mail->AddEmbeddedImage($inline_img, md5($inline_img), basename($inline_img),"base64",mime_content_type($inline_img));
				}
			}
		}


	if($Cc){
        if($mail->Mailer == "mail"){
			$mail->AddCustomHeader("Cc: {$Cc}");
		}else{
        	$tmp = explode(",",$Cc);
			foreach($tmp as $addc){
		  		$mail->AddCC($addc);
        	}
		}
	}

	if($Bcc){
		if($mail->Mailer == "mail"){
			$mail->AddCustomHeader("Bcc: {$Bcc}");
		}else{
        	$tmp = explode(",",$Bcc);
	   		foreach($tmp as $addbc){
				$mail->AddBCC($addbc);
        	}
		}
	}

	if (isset($returnpath) && ($returnpath != ""))
	{  // Passed parameter overrides any system default
	$mail->Sender = $returnpath;
	}
	elseif($pref['mail_bounce_email'] !=''){
		$mail->Sender = $pref['mail_bounce_email'];
    }

	if (!$mail->Send()) {
		// echo "There has been a mail error sending to " . $row["email"] . "<br>";
		return FALSE;
		// Clear all addresses and attachments for next loop
		$mail->ClearAddresses();
		$mail->ClearAttachments();
	} else {
		// Clear all addresses and attachments for next loop
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		return TRUE;
	}

}

/*  Deprecated.
 Use mail_validation_class.php instead.
function validatemail($Email) {

}
*/



?>