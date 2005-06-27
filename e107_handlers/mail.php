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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/mail.php,v $
|     $Revision: 1.20 $
|     $Date: 2005-06-27 14:46:13 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
/*
Please note that mailed attachments have been found to be corrupted using php 4.3.3
php 4.3.6 does NOT have this problem.
*/
// Comment out the line below if you have trouble with some people not receiving emails.
// ini_set(sendmail_path, "/usr/sbin/sendmail -t -f ".$pref['siteadminemail']);

function sendemail($send_to, $subject, $message, $to_name, $send_from, $from_name, $attachments, $Cc, $Bcc, $returnpath, $returnreceipt,$inline ="") {
	global $pref;
	require_once(e_HANDLER."phpmailer/class.phpmailer.php");

	$mail = new PHPMailer();

    if ($pref['mailer']== 'smtp' || $pref['smtp_enable']==1) {
		$mail->Mailer = "smtp";
	 	$mail->SMTPKeepAlive = FALSE;
		$mail->Host = $pref['smtp_server'];
		if($pref['smtp_username'] && $pref['smtp_password']){
			$mail->SMTPAuth = TRUE;
			$mail->Username = $pref['smtp_username'];
			$mail->Password = $pref['smtp_password'];
			$mail->$PluginDir = e_HANDLER."phpmailer/";
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
	$mail->Host = $pref['smtp_server'];
	$mail->Subject = $subject;
	$mail->SetLanguage("en",e_HANDLER."phpmailer/language/");

	$lb = "\n";
	// Clean up the HTML. ==

	if (preg_match('/<(font|br|a|img|b)/i', $message)) {
		$Html = $message; // Assume html if it begins with one of these tags
	} else {
		$Html = htmlspecialchars($message);
		$Html = preg_replace('%(http|ftp|https)(://\S+)%', '<a href="\1\2">\1\2</a>', $Html);
		$Html = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2">\\2</a>', $Html);
		$Html = eregi_replace('([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})', '<a href="mailto:\\1">\\1</a>', $Html);
		$Html = str_replace("\n", "<br>\n", $Html);
	}
	if(strstr($message,"</style>")){
    	$text = strstr($message,"</style>");
	}else{
    	$text = $message;
	}
    $text = str_replace("<br />", "\n", $text);
  	$text = strip_tags(str_replace("<br>", "\n", $text));

	$mail->Body = $Html; //Main message is HTML
	$mail->IsHTML(TRUE);
 	$mail->AltBody = $text; //Include regular plaintext as well
	$mail->AddAddress($send_to, $to_name);

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
				if(is_readable($inline_img)){
					$mail->AddEmbeddedImage($inline_img, md5($inline_img), basename($inline_img),"base64",mime_content_type($inline_img));
				}
			}
		}


	if($Cc){
        $tmp = explode(",",$Cc);
		foreach($tmp as $addc){
			$mail->AddCC($addc);
        }
	}

	if($Bcc){
        $tmp = explode(",",$Bcc);
		foreach($tmp as $addbc){
			$mail->AddBCC($addbc);
        }
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


function validatemail($Email) {
	global $HTTP_HOST;
	$result = array();
	 ;

	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $Email)) {
		$result[0] = false;
		$result[1] = "$Email is not properly formatted";
		return $result;
	}

	list ($Username, $Domain ) = split ("@", $Email);

	if (getmxrr($Domain, $MXHost)) {
		$ConnectAddress = $MXHost[0];
	} else {
		$ConnectAddress = $Domain;
	}

	$Connect = fsockopen ($ConnectAddress, 25 );

	if ($Connect) {

		if (ereg("^220", $Out = fgets($Connect, 1024))) {

			fputs ($Connect, "HELO $HTTP_HOST\r\n");
			$Out = fgets ($Connect, 1024 );
			fputs ($Connect, "MAIL FROM: <{$Email}>\r\n");
			$From = fgets ($Connect, 1024 );
			fputs ($Connect, "RCPT TO: <{$Email}>\r\n");
			$To = fgets ($Connect, 1024);
			fputs ($Connect, "QUIT\r\n");
			fclose($Connect);
			if (!ereg ("^250", $From) || !ereg ("^250", $To )) {
				$result[0] = false;
				$result[1] = "Server rejected address";
				$result[2] = $From;
				return $result;
			}
		} else {
			$result[0] = false;
			$result[1] = "No response from server";
			$result[2] = $From;
			return $result;
		}

	} else {

		$result[0] = false;
		$result[1] = "Cannot find E-Mail server.";
		$result[2] = $From;
		return $result;
	}
	$result[0] = true;
	$result[1] = "$Email appears to be valid.";
	$result[2] = $From;
	return $result;
} // end of function


if(!function_exists("mime_content_type")){
function mime_content_type($filename){

    $filename = basename($filename);

    $mime[".zip"] = "application/x-zip-compressed";
	$mime[".gif"] = "image/gif";
	$mime[".png"] = "image/x-png";
	$mime[".jpg"] = "image/jpeg";
	$mime[".jpeg"] = "image/jpeg";
	$mime[".tif"] = "image/tiff";
	$mime[".tiff"] = "image/tiff";
	$mime[".pdf"] = "application/pdf";
	$mime[".hqx"] = "application/mac-binhex40";
	$mime[".doc"] = "application/msword";
	$mime[".dot"] = "application/msword";
	$mime[".exe"] = "application/octet-stream";
	$mime[".au"] = "audio/basic";
	$mime[".snd"] = "audio/basic";
	$mime[".mid"] = "audio/mid";
	$mime[".mp3"] = "audio/mpeg";
	$mime[".aif"] = "audio/x-aiff";
	$mime[".ra"] = "audio/x-pn-realaudio";
	$mime[".ram"] = "audio/x-pn-realaudio";
	$mime[".wav"] = "audio/x-wav";
	$mime[".bmp"] = "image/bmp";
	$mime[".ra"] = "audio/x-pn-realaudio";
	$mime[".htm"] = "text/html";
	$mime[".html"] = "text/html";
	$mime[".css"] = "text/css";
	$mime[".txt"] = "text/plain";
	$mime[".mov"] = "video/quicktime";
	$mime[".mpg"] = "video/mpeg";
	$mime[".asx"] = "video/x-ms-asf";
	$mime[".avi"] = "video/x-msvideo";

    $ext = strrchr($filename, '.');
   return $mime[$ext];
}







}

?>