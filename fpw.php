<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/fpw.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
$qs = e_QUERY;
if($qs != ""){
	$sql -> db_Select("user", "*", "user_id='$qs'");
	$row = $sql -> db_Fetch();
	extract($row);
	if($user_sess != ""){
		$newpw = md5($user_sess);
		$sql -> db_Update("user", "user_password='$newpw', user_sess='' WHERE user_id='$user_id' ");
		setcookie('userkey', '', time()+3600*24*30, '/', '', 0);
		$_SESSION["userkey"] = "";
		$set = TRUE;
	}
}

require_once(HEADERF);

if($set == TRUE){
	$ns -> tablerender($caption, "<div style='text-align:center'>".LAN_217."</div>");
	require_once(FOOTERF);
	exit;
}

if(IsSet($_POST['pwsubmit'])){
	$name = $_POST['name'];
	$email = $_POST['email'];
	if($sql -> db_Select("user", "*", "user_name='".$_POST['name']."' AND user_email='".$_POST['email']."' ")){
		$row = $sql -> db_Fetch();
		extract($row);

		$pwlen = rand(4, 9);

		for($a=0; $a<=$pwlen;$a++){
			$newpw .= chr(rand(97, 122));
		}

		$sql -> db_Update("user", "user_sess='$newpw' WHERE user_name='".$_POST['name']."' AND user_email='".$_POST['email']."' ");

		$message = LAN_215.$newpw."\n\n".LAN_216."\n\n".SITEURL."fpw.php?".$user_id;
		
		if(file_exists(e_BASE."plugins/smtp.php")){
			require_once(e_BASE."plugins/smtp.php");
			smtpmail($_POST['email'], "Password reset from ".SITENAME, $message, "From: reset@".SITENAME."\r\n"."Reply-To: -null-\r\n"."X-Mailer: PHP/" . phpversion());
		}else{
			if(@mail($_POST['email'], "Password reset from ".SITENAME, $message, "From: reset@".SITENAME."\r\n"."Reply-To: -null-\r\n"."X-Mailer: PHP/" . phpversion())){
				$text = "<div style='text-align:center'>New password sent to ".$_POST['email'].", please follow the instructions in the email to validate your password.</div>";
			}else{
				$text = "<div style='text-align:center'>Sorry - unable to send email.</div>";
			}
		}
		$ns -> tablerender("Password Reset", $text);
		require_once(FOOTERF);
		exit;
	}else{
		$text = LAN_213;
		$ns -> tablerender(LAN_214, "<div style='text-align:center'>".$text."</div>");
	}
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:70%'>
<tr>
<td style='width:20%'>".LAN_7."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='name' size='60' value='' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_112."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='email' size='60' value='' maxlength='100' />
</td>
</tr>

</tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center'>
<br />
<input class='button' type='submit' name='pwsubmit' value='".LAN_156."' />
</table>
</form>
</div>";

$ns -> tablerender("Please enter your username/email address", $text);

require_once(FOOTERF);
?>