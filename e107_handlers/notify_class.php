<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
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

if (!defined('e107_INIT')) { exit; }

class notify {

	var $notify_prefs;
	var $debug = FALSE;

	function notify() {
		global $sysprefs, $e_event, $eArrayStorage;
		$this -> notify_prefs = $sysprefs -> get('notify_prefs');
		$this -> notify_prefs = $eArrayStorage -> ReadArray($this -> notify_prefs);
		foreach ($this -> notify_prefs['event'] as $id => $status) {
			if ($status['type'] != 'off') {
				$e_event -> register($id, 'notify_'.$id);
			}
		}

		include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_notify.php');
	}

	function send($id, $subject, $message) 
	{
		global $sql, $tp;
				
		$this->logData("Initiate Send - ".$id. " :: Subject: ".$tp->toEmail($subject));
		
		e107_require_once(e_HANDLER.'mail.php');
		$subject = SITENAME.': '.$subject;
		if ($this -> notify_prefs['event'][$id]['type'] == 'main')
		{
			if(!sendemail(SITEADMINEMAIL, $tp->toEmail($subject), $tp->toEmail($message)))
			{
				$this->logData("Error - ".SITEADMINEMAIL. " :: Subject: ".$tp->toEmail($subject));
			}
			else
			{
				$this->logData("Success - ".SITEADMINEMAIL. " :: Subject: ".$tp->toEmail($subject));	
			}
		}
		elseif ($this -> notify_prefs['event'][$id]['type'] == 'class')
		{
			if ($this -> notify_prefs['event'][$id]['class'] == e_UC_ADMIN)
			{
				$sql -> db_Select('user', 'user_email', "user_admin = 1 AND user_ban = 0");
			}
			elseif ($this -> notify_prefs['event'][$id]['class'] == e_UC_MEMBER)
			{
				$sql -> db_Select('user', 'user_email', 'user_ban = 0');
			}
			else
			{
				$sql -> db_Select('user', 'user_email', "user_ban = 0 AND user_class REGEXP '(^|,)(".$this -> notify_prefs['event'][$id]['class'].")(,|$)'");
			}
			while ($email = $sql -> db_Fetch())
			{
				if(!sendemail($email['user_email'], $tp->toEmail($subject), $tp->toEmail($message)))
				{
					$this->logData("Error - ".$email['user_email']. " :: Subject: ".$tp->toEmail($subject));
				}
				else
				{
					$this->logData("Success - ".$email['user_email']. " :: Subject: ".$tp->toEmail($subject));	
				}
			}
		}
		elseif ($this -> notify_prefs['event'][$id]['type'] == 'email')
		{
			if(!sendemail($this -> notify_prefs['event'][$id]['email'], $tp->toEmail($subject), $tp->toEmail($message)))
			{
				$this->logData("Error - ".$this -> notify_prefs['event'][$id]['email']. " subject: ".$tp->toEmail($subject));	
			}
			else
			{
				$this->logData("Success - ".$this -> notify_prefs['event'][$id]['email']. " :: Subject: ".$tp->toEmail($subject));	
			}
		}
	}
	
	
	
	/**
	 * Log Notify Info for debugging
	 * @param object $message
	 * @return 
	 */
	function logData($message)
	{
		if($this->debug == FALSE){ return; }
		if($fp = @fopen(e_HANDLER."notify.log","a+"))
		{	
			$contents = @fwrite($fp, date('r')." :: ".$message ." \n");
			@fclose($fp);
		}			
	}
}

global $nt;
$nt = new notify;

function notify_usersup($data) {
	global $nt;
	foreach ($data as $key => $value)
	{
		if($key != "password1" && $key != "password2" && $key != "email_confirm" && $key != "register")
		{
			if(is_array($value))  // show user-extended values.
			{
            	foreach($value as $k => $v)
				{
                	$message .= str_replace("user_","",$k).': '.$v.'<br />';
				}
			}
			else
			{
				$message .=  $key.': '.$value.'<br />';
			}
		}
	}
	$nt -> send('usersup', NT_LAN_US_1, $message);
}

function notify_userveri($data) {
	global $nt, $e107;
	$msgtext = NT_LAN_UV_2.$data['user_id']."\n";
	$msgtext .= NT_LAN_UV_3.$data['user_loginname']."\n";
	$msgtext .= NT_LAN_UV_4.$e107->getip();
	$nt -> send('userveri', NT_LAN_UV_1, $msgtext);
}

function notify_login($data) {
	global $nt;
	
	$theIP = $_SERVER["REMOTE_ADDR"]." (". gethostbyaddr($_SERVER['REMOTE_ADDR']).")";
	
	$message = "<br /><br /><table style='width:70%'>
	<tr><td>User ID:</td><td>".$data['user_id']."</td></tr>
	<tr><td>Username:</td><td>".$data['user_name']."</td></tr>
	<tr><td>Admin:</td><td>".($data['user_admin'] ? "Yes" : "No")."</td></tr>
    <tr><td>IP:</td><td>".$theIP."</td></tr>
	<tr><td>URL:</td><td>".$_SERVER["SCRIPT_NAME"]."</td></tr>
	<tr><td>Referer:</td><td>".$_SERVER["HTTP_REFERER"]."</td></tr>
	<tr><td>UserAgent:</td><td>".$_SERVER["HTTP_USER_AGENT"]."</td></tr>
    <tr><td>Time:</td><td>".date("r")."</td></tr>";
	
	/*	
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
	*/
	
	$nt -> send('login', NT_LAN_LI_1, $message);
}

function notify_logout() {
	global $nt;
	$nt -> send('logout', NT_LAN_LO_1, USERID.'. '.USERNAME.' '.NT_LAN_LO_2);
}

function notify_flood($data) {
	global $nt;
	$nt -> send('flood', NT_LAN_FL_1, NT_LAN_FL_2.': '.$data);
}

function notify_subnews($data) {
	global $nt,$tp;
	foreach ($data as $key => $value)
	{
		$message .= $key.': '.$value.'<br />';
	}
	
	$message .= "<br /><img src='{e_IMAGE}newspost_images/".$data['image']."' alt='' /><br />";
	$message .= "src: {e_IMAGE}newspost_images/".$data['image'];
	
	$nt -> send('subnews', NT_LAN_SN_1." : ".$data['itemtitle'], $message);
}

function notify_newspost($data) {
	global $nt, $tp;
	
	if(varset($data['news_userclass'][255])) // No notification when set to 'Nobody'. 
	{
		return;
	}
	
	$message = $tp->toDB('<b>'.$data['news_title'].'</b><br /><br />'.$data['news_summary'].'<br /><br />'.$data['data'].'<br /><br />'.$data['news_extended']);
	$nt -> send('newspost', $data['news_title'], $message);
}

function notify_newsupd($data) {
	global $nt, $tp;

	if(varset($data['news_userclass'][255])) // No notification when set to 'Nobody'. 
	{
		return;
	}

	$message = $tp->toDB('<b>'.$data['news_title'].'</b><br /><br />'.$data['news_summary'].'<br /><br />'.$data['data'].'<br /><br />'.$data['news_extended']);
	$nt -> send('newsupd', NT_LAN_NU_1.': '.$data['news_title'], $message);
}

function notify_newsdel($data) {
	global $nt;
	$nt -> send('newsdel', NT_LAN_ND_1, NT_LAN_ND_2.': '.$data);
}


function notify_fileupload($data) {
	global $nt;
	$message = '<b>'.$data['upload_name'].'</b><br /><br />'.$data['upload_description'].'<br /><br />'.$data['upload_size'].'<br /><br />'.$data['upload_user'];
	$nt -> send('fileupload', $data['upload_name'], $message);
}



if (isset($nt -> notify_prefs['plugins'])) {
	foreach ($nt -> notify_prefs['plugins'] as $plugin_id => $plugin_settings) {
		if(is_readable(e_PLUGIN.$plugin_id.'/e_notify.php'))
		{
			require_once(e_PLUGIN.$plugin_id.'/e_notify.php');
		}
	}
}

?>
