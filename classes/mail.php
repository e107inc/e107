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

	$headers = "From: ".SITENAME."<".SITEADMINEMAIL.">\n";
	$headers .= "X-Sender: <mail@".SITEURL.">\n";
	$headers .= "X-Mailer: PHP\n";
	$headers .= "X-Priority: 3\n";
	$headers .= "Return-Path: <mail@".SITEURL.">\n";

	if(file_exists(e_BASE."plugins/smtp.php")){
		require_once(e_BASE."plugins/smtp.php");
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