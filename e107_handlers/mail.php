<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/mail.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function sendemail($send_to, $subject, $message){
	$tmp = parse_url(SITEURL);
	$headers = "From: ".SITENAME."<".SITEADMINEMAIL.">\n";
	$headers .= "X-Sender: <mail@".$tmp['host'].">\n";
	$headers .= "X-Mailer: PHP\n";
	$headers .= "X-Priority: 3\n";
	$headers .= "Content-Type: text/plain; charset=".CHARSET."\n";
	$headers .= "Return-Path: <mail@".$tmp['host'].">\n";
	if(file_exists(e_HANDLER."smtp.php")){
		require_once(e_HANDLER."smtp.php");
		smtpmail($send_to, $subject, $message, $headers);
	}else{
		if(@mail($send_to, $subject, $message, $headers)){
			return TRUE;
		}else{
			return FALSE;
		}
	}

}

?>