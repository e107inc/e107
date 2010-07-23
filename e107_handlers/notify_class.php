<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/notify_class.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class notify {

	var $notify_prefs;

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
		e107_require_once(e_HANDLER.'mail.php');
		$subject = SITENAME.': '.$subject;
		if ($this -> notify_prefs['event'][$id]['type'] == 'main')
		{
			sendemail(SITEADMINEMAIL, $tp->toEmail($subject), $tp->toEmail($message));
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
				sendemail($email['user_email'], $tp->toEmail($subject), $tp->toEmail($message));
			}
		}
		elseif ($this -> notify_prefs['event'][$id]['type'] == 'email')
		{
			sendemail($this -> notify_prefs['event'][$id]['email'], $tp->toEmail($subject), $tp->toEmail($message));
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
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
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
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
	$nt -> send('subnews', NT_LAN_SN_1, $message);
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
