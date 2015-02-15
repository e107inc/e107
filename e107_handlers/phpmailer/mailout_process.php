<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/phpmailer/mailout_process.php,v $
 * $Revision$
 * $Date$
 * $Author$
|
| Modifications in hand to work with most recent mailout.php

To do:
	1. Admin log entries?
	2. Option to add user name in subject line - support |...| and {...} - done; test
	3. Strip bbcode from plain text emails (ideally needs updated parser).
	4. Support phpmailer 2.0 options
	5. Log cancellation of email run
|
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("W")){ header("location:".e_BASE."index.php"); exit; }
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_mailout.php");


// Directory for log (if enabled)
//FIXME need another name
define('MAIL_LOG_PATH',e_LOG);

$HEADER = "";
$FOOTER = "";
define("e_PAGETITLE",LAN_MAILOUT_60);
require_once(HEADERF);
set_time_limit(18000);
session_write_close();

// $logenable - 0 = log disabled, 1 = 'dry run' (debug and log, no send). 2 = 'log all' (send, and log result). 3 = 'dry run' with failures
// $add_email - 1 includes email detail in log
list($logenable,$add_email) = explode(',',varset($pref['mail_log_options'],'0,0'));


if($_POST['cancel_emails'])
{
	$sql -> db_Delete("generic", "gen_datestamp='".intval($_POST['mail_id'])."' ");

    $text = "<div style='text-align:center;width:220px'><br />".LAN_MAILOUT_66;   // Cancelled Successfully;
    $text .= "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
	<br /><input type='button' class='btn btn-primary button' name='close' value='Close' onclick=\"window.close()\" />
     </div></div>";

    $ns -> tablerender(LAN_MAILOUT_59, $text);
	echo "</body></html>";

	exit;
}


ob_implicit_flush();

if (e_QUERY)
{
  $tmp = explode('.',e_QUERY);
  $mail_id = intval(varset($tmp[0],0));							// ID in 'generic' table corresponding to the recipient entries
  $mail_text_id = intval(varset($tmp[1],0));					// Record number in 'generic' table corresponding to the email data
}
else
{
  $mail_id = intval(varset($_POST['mail_id'],0));				// ID in 'generic' table corresponding to the recipient entries
  $mail_text_id = intval(varset($_POST['mail_text_id'],0));		// ID in 'generic' table corresponding to the recipient entries
}


if (($mail_id == 0) || ($mail_text_id == 0))
{
  echo "Invalid parameters: {$mail_id}, {$mail_text_id}!<br />";
  exit;
}


// Get the email itself from the 'generic' table
$qry = "SELECT * FROM #generic WHERE `gen_id` = {$mail_text_id} AND gen_type='savemail' and gen_datestamp = '".$mail_id."' ";
if (!$sql -> db_Select_gen($qry))
{
  echo "Email not found<br />";
  exit;
}


if (!$row = $sql->db_Fetch())
{
  echo "Can't read email<br />";
  exit;
}


$email_info = unserialize($row['gen_chardata']);		// Gives us sender_name, sender_email, email_body


//--------------------------------------------------
// 	Configure mailout handler (PHPMailer or other)
//--------------------------------------------------

	require(e_HANDLER."phpmailer/class.phpmailer.php");

	$mail = new PHPMailer();


	$mail->From = vartrue($email_info['sender_email'],$pref['siteadminemail']);
	$mail->FromName = vartrue($email_info['sender_name'], $pref['siteadmin']);
	//  $mail->Host     = "smtp1.site.com;smtp2.site.com";
	if ($pref['mailer']== 'smtp')
	{
		$mail->Mailer = "smtp";
		$mail->SMTPKeepAlive = vartrue($pref['smtp_keepalive'])  ? TRUE : FALSE;
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

	$message_subject = stripslashes($tp -> toHTML($email_info['email_subject'],FALSE,RAWTEXT));
	$mail->WordWrap = 50;
	$mail->CharSet = CHARSET;
	$mail->IsHTML(TRUE);
	$mail->SMTPDebug = (e_MENU == "debug") ? TRUE : FALSE;


	if($email_info['copy_to'])
	{
        $tmp = explode(",",$email_info['copy_to']);
		foreach($tmp as $addc)
		{
			$mail->AddCC(trim($addc));
        }
	}

	if($email_info['bcopy_to'])
	{
        $tmp = explode(",",$email_info['bcopy_to']);
		foreach($tmp as $addc)
		{
			$mail->AddBCC(trim($addc));
        }
	}


	if($pref['mail_bounce_email'] !='')
	{
		$mail->Sender = $pref['mail_bounce_email'];
	}



	$attach = trim($email_info['attach']);

	if(is_readable(e_DOWNLOAD.$attach))
	{
		$attach_link = e_DOWNLOAD.$attach;
	}
	else
	{
		$attach_link = e_UPLOAD.$attach;
	}

	if (($temp = strrchr($attach,'/')) !== FALSE)
	{	// Just specify filename as attachment - no path
		$attach = substr($temp,1);
	}

	if ($attach != "" && !$mail->AddAttachment($attach_link, $attach))
	{
		$mss = LAN_MAILOUT_58."<br />{$attach_link}->{$attach}";  // problem with attachment.
		$ns->tablerender("Error", $mss);
		exit;
	}



// ---------------------------- Setup the Email ----------------------------->


	$mail_head = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
	$mail_head .= "<html xmlns='http://www.w3.org/1999/xhtml' >\n";
	$mail_head .= "<head><meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />\n";

	if (vartrue($email_info['use_theme']))
	{
		$theme = $THEMES_DIRECTORY.$pref['sitetheme']."/";
        $style_css = file_get_contents(e_THEME.$pref['sitetheme']."/style.css");
      	$mail_head .= "<style>\n".$style_css."\n</style>";

		$message_body = $mail_head;
		$message_body .= "</head>\n<body>\n";
		$message_body .= "<div style='padding:10px;width:97%'><div class='forumheader3'>\n";
		$message_body .= $tp -> toEmail($email_info['email_body'])."</div></div></body></html>";
	}
	else
	{
		$message_body = $mail_head;
		$message_body .= "</head>\n<body>\n";
		$message_body .= $tp -> toEmail($email_info['email_body'])."</body></html>";
		$message_body = str_replace("&quot;", '"', $message_body);
		$message_body = str_replace('src="', 'src="'.SITEURL, $message_body);
    }

	$message_body = stripslashes($message_body);



// ----------------  Display Progress and Send Emails. ----------------------->


    echo "<div class='fcaption'>&nbsp;".LAN_MAILOUT_59."</div>";
//    $qry = "SELECT g.*,u.* FROM #generic AS g LEFT JOIN #user AS u ON g.gen_user_id = u.user_id WHERE g.gen_type='sendmail' and g.gen_datestamp = '".intval($_POST['mail_id'])."' ";
// All the user info is in the generic table now - simplifies the query a bit
    $qry = "SELECT g.* FROM #generic AS g WHERE g.gen_type='sendmail' and g.gen_datestamp = '".$mail_id."' ";
    $count = $sql -> db_Select_gen($qry);
//	echo date("H:i:s d.m.y")."  Start of mail run by ".USERNAME." - {$count} emails to go. ID: {$mail_id}. Subject: ".$mail_subject."<br />";

	if(!$count)
	{
		echo "<div style='text-align:center;width:200px'><br />".LAN_MAILOUT_61."</div>";
		echo "</body></html>";
        echo "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
			<input type='button' class='btn btn-default button' name='close' value='Close' onclick=\"window.close()\" />
    		 </div>";
		exit;
	}


	$c = 0; $d=0;
	$cur = 0;
	$send_ok = 0; $send_fail = 0;
	$pause_count = 1;
	$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
	$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;
	$unit = (1/$count)* 100;		// Percentage 'weight' of each email
	echo "<div class='blocks' style='text-align:left;width:199px'><div id='bar' class='bar' style='border:0px;width:".$cur."%' >&nbsp;</div></div>";
	echo "<div class='percents'><span id='numbers'>".($c+1)." / ".$count." (" . $cur . "</span>%) &nbsp;".LAN_MAILOUT_117."</div>";

	stopwatch();

	// Debug/mailout log
	if ($logenable)
	  {
    	$logfilename = MAIL_LOG_PATH.'mailoutlog.txt';
    	$loghandle = fopen($logfilename, 'a');      // Always append to file
		fwrite($loghandle,"=====----------------------------------------------------------------------------------=====\r\n");
		fwrite($loghandle,date("H:i:s d.m.y")."  Start of mail run by ".USERNAME." - {$count} emails to go. ID: {$mail_id}. Subject: ".$mail_subject."\r\n");
		if ($add_email)
		{
		  fwrite($loghandle, "From: ".$mail->From.' ('.$mail->FromName.")\r\n");
		  fwrite($loghandle, "Subject: ".$mail->Subject."\r\n");
		  fwrite($loghandle, "CC: ".$email_info['copy_to']."\r\n");
		  fwrite($loghandle, "BCC: ".$email_info['bcopy_to']."\r\n");
		  fwrite($loghandle, "Attach: ".$attach."\r\n");
		  fwrite($loghandle, "Body: ".$email_info['email_body']."\r\n");
		  fwrite($loghandle,"-----------------------------------------------------------\r\n");
		}
      }

    while($row = $sql-> db_Fetch())
	{
//-------------------------------
//		Send one email
//-------------------------------
	  $mail_info = unserialize($row['gen_chardata']);		// Has most of the info needed

	  $activator = (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$row['gen_user_id'].".".$mail_info['user_signup'] : SITEURL."/signup.php?activate.".$row['gen_user_id'].".".$mail_info['user_signup']);
      $signup_link = ($mail_info['user_signup']) ? "<a href='{$activator}'>{$activator}</a>" : "";

	  // Allow username in subject
	  $mail_subject = str_replace(array('|USERNAME|','{USERNAME}'),$mail_info['user_name'],$message_subject);
	  $mail->Subject = $mail_subject;


	  // Allow username, userID, signup link in body
	  $search = array('|USERNAME|','|USERID|','|SIGNUP_LINK|');
	  $replace = array($mail_info['user_name'],$row['gen_user_id'],$signup_link);

      $mes_body = str_replace($search,$replace,$message_body);
	  $alt_body = str_replace($search,$replace,stripslashes($tp->toText($email_info['email_body'])));

	  $mail->Body = $mes_body;
	  $mail->AltBody = $alt_body;

		$mail->AddAddress($mail_info['user_email'], $mail_info['user_name']);
		if ($row['gen_user_id'])
		{
          $mail_custom = $row['gen_user_id'];
		}
		else
		{
          $mail_custom = md5($mail_info['user_name'].$mail_info['user_email']);
		}
		$mail_custom = "X-e107-id: ".$mail_id.'/'.$mail_custom;
		$mail->AddCustomHeader($mail_custom);


		$debug_message = '';
		if (($logenable == 0) || ($logenable == 2))
		{  // Actually send email
		  $mail_result = $mail->Send();
		}
		else
		{  // Debug mode - decide result of email here
		  $mail_result = TRUE;
		  if (($logenable == 3) && (($c % 7) == 4)) $mail_result = FALSE;			// Fail one email in 7 for testing
		  $debug_message = 'Debug';
		}
		if ($mail_result)
		{
		  $send_ok++;
		  $sql2->db_Delete('generic',"gen_id={$row['gen_id']}");		// Mail sent - delete from database
		} 
		else 
		{
		  $send_fail++;
		  $mail_info['send_result'] = 'Fail: '.$mail->ErrorInfo.$debug_message;
		  $temp = serialize($mail_info);
		  // Log any error info we can
		  $sql2->db_Update('generic',"`gen_chardata`='{$temp}' WHERE gen_id={$row['gen_id']}");
		}

		if ($logenable) 
		{ 
		  fwrite($loghandle,date("H:i:s d.m.y")."  Send to {$mail_info['user_name']} at {$mail_info['user_email']} Mail-ID={$mail_custom} - {$mail_result}\r\n");  
		}

		$mail->ClearAddresses();
   		$mail->ClearCustomHeaders();


// --------- One email sent

		$cur = round((($c / $count) * 100) + $unit);

// Do we need next line?
//		echo str_pad(' ',4096)."<br />\n";					// Put out lots of spaces and a newline - works wonders for XHTML compliance!

//		$d = ($c==0) ? 10 : round($width + $d);		// Line doesn't do anything

//		echo "<div class='percents'>".($c+1)." / ".$count." (" . $cur . "%) &nbsp;".LAN_MAILOUT_117."</div>";
		echo "<script type='text/javascript'>setnum('".($c+1)."','{$count}','{$cur}');</script>\n";

/*		if($cur != $prev)
		{  // Update 'completed' segment of progress bar
		  echo "<script type='text/javascript'>inc('".$cur."%');</script>\n";
		}
        $prev = $cur;
*/		ob_flush();
		flush();

		if($pause_count > $pause_amount)
		{
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
	echo LAN_MAILOUT_62." ".$send_ok."<br />";
	echo LAN_MAILOUT_63." ".$send_fail."<br />";
	echo LAN_MAILOUT_64." ".stopwatch()." ".LAN_MAILOUT_65."<br />";
	echo "</div>";

// Complete - need to log something against the mailshot entry, and maybe write an admin log entry.
	$log_string = date("H:i:s d.m.y")."  End of ".($logenable == 1 ? 'debug ' : '')."mail run by ".USERNAME." - {$send_ok} succeeded, {$send_fail} failed. Subject: ".$mail_subject; 
	if (!is_array($email_info['send_results'])) $email_info['send_results'] = array();
	$email_info['send_results'][] = $log_string;
	$sql->db_Update('generic',"`gen_chardata`='".serialize($email_info)."' WHERE `gen_id` = {$mail_text_id} AND `gen_type`='savemail' and `gen_datestamp` = '".$mail_id."' ");

	$mail->ClearAttachments();
	
	if ($pref['mailer']== 'smtp') 
	{
	  $mail->SmtpClose();
	}

echo "<div style='text-align:center;margin-left:auto;margin-right:auto;position:absolute;left:10px;top:110px'>
	<br /><input type='button' class='btn btn-default button' name='close' value='Close' onclick=\"window.close()\" />
     </div>";
echo "</body></html>";

if ($logenable) 
{ 
  fwrite($loghandle,$log_string."\r\n"); 
  fclose($loghandle); 
}





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
	function inc(amount)
	{
	  document.getElementById('bar').style.width= amount;
	}
	
	function setnum(v1,v2,v3)
	{
	  this_el = document.getElementById('numbers');
	  if (this_el) this_el.innerHTML = v1+' / '+v2+' ('+v3;
	  document.getElementById('bar').style.width= v3+'%';
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
