<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
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
require_once("../../class2.php");
if(!getperms("W")){ header("location:".e_BASE."index.php"); exit; }
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_mailout.php");

$HEADER = "";
$FOOTER = "";
define("e_PAGETITLE",MAILAN_60);
require_once(HEADERF);
set_time_limit(18000);
session_write_close();


if($_POST['cancel_emails']){
	$sql -> db_Delete("generic", "gen_datestamp='".intval($_POST['mail_id'])."' ");

    $text = "<div style='text-align:center;width:220px'><br />".MAILAN_66;   // Cancelled Successfully;
    $text .= "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
	<br /><input type='button' class='button' name='close' value='Close' onclick=\"window.close()\" />
     </div></div>";

    $ns -> tablerender(MAILAN_59, $text);
	echo "</body></html>";

	exit;
}

    ob_implicit_flush();
/*
	if (ob_get_level() == 0) {
	   ob_start();
	 }
*/

// -------------------- Configure PHP Mailer ------------------------------>

	require(e_HANDLER."phpmailer/class.phpmailer.php");

	$mail = new PHPMailer();

	$mail->From = ($_POST['email_from_email'])? $_POST['email_from_email']:	$pref['siteadminemail'];
	$mail->FromName = ($_POST['email_from_name'])? $_POST['email_from_name']: $pref['siteadmin'];
	//  $mail->Host     = "smtp1.site.com;smtp2.site.com";
	if ($pref['mailer']== 'smtp')
	{
		$mail->Mailer = "smtp";
		$mail->SMTPKeepAlive = (isset($pref['smtp_keepalive']) && $pref['smtp_keepalive']==1)  ? TRUE : FALSE;
		if($pref['smtp_server'])
		{
			$mail->Host = $pref['smtp_server'];
		}
		if($pref['smtp_username'] && $pref['smtp_password'])
		{
			$mail->SMTPAuth = TRUE;
			$mail->Username = $pref['smtp_username'];
			$mail->Password = $pref['smtp_password'];
			$mail->PluginDir = e_HANDLER."phpmailer/";
        }
    }
	elseif ($pref['mailer']== 'sendmail')
	{
		$mail->Mailer = "sendmail";
		$mail->Sendmail = ($pref['sendmail']) ? $pref['sendmail'] : "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'];
	}
	 else
	{
        $mail->Mailer = "mail";
	}

	$mail->AddCC = ($_POST['email_cc']);
	$mail->WordWrap = 50;
	$mail->CharSet = CHARSET;
	$mail->Subject = $_POST['email_subject'];
	$mail->IsHTML(TRUE);
	$mail->SMTPDebug = (e_MENU == "debug") ? TRUE : FALSE;

	if($_POST['email_cc'])
	{
        $tmp = explode(",",$_POST['email_cc']);
		foreach($tmp as $addc)
		{
			$mail->AddCC($addc);
        }
	}

	if($_POST['email_bcc'])
	{
        $tmp = explode(",",$_POST['email_bcc']);
		foreach($tmp as $addbc)
		{
			$mail->AddBCC($addbc);
        }
	}

	if($pref['mail_bounce_email'] !='')
	{
		$mail->Sender = $pref['mail_bounce_email'];
	}

	$attach = trim($_POST['email_attachment']);

	if(is_readable(e_DOWNLOAD.$attach))
	{
		$attach_link = e_DOWNLOAD.$attach;
	}
	else
	{
		$attach_link = e_FILE.'public/'.$attach;
	}

	if (($temp = strrchr($attach,'/')) !== FALSE)
	{	// Just specify filename as attachment - no path
		$attach = substr($temp,1);
	}

	if ($attach != "" && !$mail->AddAttachment($attach_link, $attach))
	{
		$mss = MAILAN_58."<br />$attach_link";  // problem with attachment.
		$ns->tablerender("Error", $mss);
//		require_once(e_ADMIN."footer.php");
		exit;
	}



// ---------------------------- Setup the Email ----------------------------->


	$message_subject = stripslashes($tp -> toHTML($_POST['email_subject']));

	$mail_head = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
	$mail_head .= "<html xmlns='http://www.w3.org/1999/xhtml' >\n";
	$mail_head .= "<head><meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";

	if (isset($_POST['use_theme']))
	{
		$theme = $THEMES_DIRECTORY.$pref['sitetheme']."/";
   	//	$mail_head .= "<link rel=\"stylesheet\" href=\"".SITEURL.$theme."style.css\" type=\"text/css\" />\n";
        $style_css = file_get_contents(e_THEME.$pref['sitetheme']."/style.css");
      	$mail_head .= "<style>\n".$style_css."\n</style>";

		$message_body = $mail_head;
		$message_body .= "</head>\n<body>\n";
		$message_body .= "<div style='padding:10px;width:97%'><div class='forumheader3'>\n";
		$message_body .= $tp -> toEmail($_POST['email_body'])."</div></div></body></html>";
	}
	else
	{
		$message_body = $mail_head;
		$message_body .= "</head>\n<body>\n";
		$message_body .= $tp -> toEmail($_POST['email_body'])."</body></html>";
		$message_body = str_replace("&quot;", '"', $message_body);
		$message_body = str_replace('src="', 'src="'.SITEURL, $message_body);
    }

	$message_body = stripslashes($message_body);



// ----------------  Display Progress and Send Emails. ----------------------->


    echo "<div class='fcaption'>&nbsp;".MAILAN_59."</div>";
    $qry = "SELECT g.*,u.* FROM #generic AS g LEFT JOIN #user AS u ON g.gen_user_id = u.user_id WHERE g.gen_type='sendmail' and g.gen_datestamp = '".intval($_POST['mail_id'])."' ";
    $count = $sql -> db_Select_gen($qry);

	if(!$count)
	{
		echo "<div style='text-align:center;width:200px'><br />".MAILAN_61."</div>";
		echo "</body></html>";
        echo "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
			<input type='button' class='button' name='close' value='Close' onclick=\"window.close()\" />
    		 </div>";
		exit;
	}


	$c = 0; $d=0;
	$pause_count = 1;
	$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
	$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;
	$sent = array();
	$failed = array();
	$unit = (1/$count)* 100;
	echo "<div class='blocks' style='text-align:left;width:199px'><div id='bar' class='bar' style='border:0px;;width:".$cur."%' >&nbsp;</div></div>";

	stopwatch();

    while($row = $sql-> db_Fetch())
	{


// ---------------------- Mailing Part. -------------------------------------->

		$activator = (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$row['user_id'].".".$row['user_sess'] : SITEURL."/signup.php?activate.".$row['user_id'].".".$row['user_sess']);
        $signup_link = ($row['user_sess']) ? "<a href='$activator'>$activator</a>" : "";

		$search = array("|USERNAME|","|USERID|","|SIGNUP_LINK|","|USER_LOGIN|","|USER_EMAIL|");
		$replace = array($row['user_name'],$row['user_id'],$signup_link,$row['user_loginname'],$row['user_email']);

        $mes_body = str_replace($search,$replace,$message_body);
		$alt_body = str_replace($search,$replace,stripslashes($tp->toText($_POST['email_body'])));

		$mail->Body = $mes_body;
		$mail->AltBody = $alt_body;

		$mail->AddAddress($row['user_email'], $row['user_name']);
        $mail->AddCustomHeader("X-e107-id: ".$row['user_id']);


		if ($mail->Send()) {
			$sent[] = $row['user_id'];
		} else {
			$failed[] = $row['user_id'];
		}

		$mail->ClearAddresses();
   		$mail->ClearCustomHeaders();


// --------- End of the mailing. --------------------------------------------->

		$cur = round((($c / $count) * 100) + $unit);
		echo str_pad(' ',4096)."<br />\n";

		$d = ($c==0) ? 10 : round($width + $d);

		echo "<div class='percents'>".($c+1)." / ".$count." (" . $cur . "%) &nbsp;complete</div>";

		if($cur != $prev){
			echo "<script type='text/javascript'>inc('".$cur."%');</script>\n";
		}
        $prev = $cur;
		ob_flush();
		flush();

		if($pause_count > $pause_amount){
			sleep($pause_time);
            $pause_count = 1;
        }

		// Default sleep to reduce server-load: 1 second.
		sleep(1);

		$c++;
		$pause_count++;
	}
	ob_end_flush();

	echo "<div style='position:absolute;left:10px;top:50px'><br />";
	echo MAILAN_62." ".count($sent)."<br />";
	echo MAILAN_63." ".count($failed)."<br />";
	echo MAILAN_64." ".stopwatch()." ".MAILAN_65."<br />";
	echo "</div>";

	$message = $sql -> db_Delete("generic", "gen_datestamp='".intval($_POST['mail_id'])."' ") ? "deleted" : "deleted_failed";

	$mail->ClearAttachments();
	if ($pref['mailer']== 'smtp') {
			$mail->SmtpClose();
	}

echo "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
	<br /><input type='button' class='button' name='close' value='Close' onclick=\"window.close()\" />
     </div>";
echo "</body></html>";






function headerjs(){
    $text = "
	<style type='text/css'><!--
	div.percents div.blocks img.blocks{
	margin: 1px;
	height: 20px;
	padding: 1px;
	border: 1px solid #000;
	width: 199px;
	background: #fff;
	color: #000;
	float: left;
	clear: right;
	z-index: 9;
	position:relative;
	}
	.percents {
	background: #FFF;
	border: 1px solid #CCC;
	margin: 1px;
	height: 20px;
	position:absolute;
	vertical-align:middle;
	width:199px;
	z-index:10;
	left: 10px;
	top: 38px;
	text-align: center;
	color:black;
	}
	.blocks {

	margin-top: 1px;
	height: 21px;
	position: absolute;
	z-index:11;
	left: 12px;
	top: 38px;

	}

	.bar {
	background: #EEE;
	background-color:red;
	filter: alpha(opacity=50);
	height:21px;
	-moz-opacity: 0.5;
	opacity: 0.5;
	-khtml-opacity: .5
	}
	-->
	</style>";

$text .= "
	<script type='text/javascript'>
	function inc(amount){
		document.getElementById('bar').style.width= amount;
	}
</script>";

	return $text;
}


function stopwatch(){
  static $mt_previous = 0;
  list($usec, $sec) = explode(" ",microtime());
  $mt_current = (float)$usec + (float)$sec;
  if (!$mt_previous) {
     $mt_previous = $mt_current;
     return "";
  } else {
     $mt_diff = ($mt_current - $mt_previous);
     $mt_previous = $mt_current;
     return round(sprintf('%.16f',$mt_diff),2);
  }
}
?>
