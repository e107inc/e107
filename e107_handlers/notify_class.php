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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/notify_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-29 18:19:24 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$nt = new notify;

class notify {
	
	var $notify_prefs;
	
	function notify() {
		global $sysprefs, $e_event;
		$this -> notify_prefs = $sysprefs -> getArray('notify_prefs');
		foreach ($this -> notify_prefs['event'] as $id => $status) {
			if ($status['type'] != 'off') {
				$e_event -> register($id, 'notify_'.$id);
			}
		}
	}
	
	function send($id, $subject, $message) {
		global $sql;
		e107_require_once(e_HANDLER.'mail.php');
		$subject = SITENAME.': '.$subject;
		if ($this -> notify_prefs['event'][$id]['type'] == 'main') {
			sendemail(SITEADMINEMAIL, $subject, $message);
		} else if ($this -> notify_prefs['event'][$id]['type'] == 'class') {
			if ($this -> notify_prefs['event'][$id]['class'] == '254') {
				$sql -> db_Select('user', 'user_email', "user_admin = 1");
			} else if ($this -> notify_prefs['event'][$id]['class'] == '253') {
				$sql -> db_Select('user', 'user_email');
			} else {
				$sql -> db_Select('user', 'user_email', "user_class REGEXP '(^|,)(".$this -> notify_prefs['event'][$id]['class'].")(,|$)'");
			}
			while ($email = $sql -> db_Fetch()) {
				sendemail($email['user_email'], $subject, $message);
			}
		} else if ($this -> notify_prefs['event'][$id]['type'] == 'email') {
			sendemail($this -> notify_prefs['event'][$id]['email'], $subject, $message);
		}
	}
}

function notify_usersup($data) {
	global $sql;
	$nt = new notify;
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
	$nt -> send('usersup', 'User Signup', $message);
}

function notify_userveri($data) {
	global $sql;
	$nt = new notify;
	$nt -> send('userveri', 'User Signup Verified', 'Users session string: '.$data);
}

function notify_login($data) {
	global $sql;
	$nt = new notify;
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
	$nt -> send('login', 'User Logged In', $message);
}

function notify_logout() {
	global $sql;
	$nt = new notify;
	$nt -> send('logout', 'User Logged Out');
}

function notify_flood($data) {
	global $sql;
	$nt = new notify;
	$nt -> send('flood', 'Flood Ban', 'IP address banned for flooding: '.$data);
}

function notify_subnews($data) {
	global $sql;
	$nt = new notify;
	foreach ($data as $key => $value) {
		$message .= $key.': '.$value.'<br />';
	}
	$nt -> send('subnews', 'News Item Submitted', $message);
}

function notify_newspost($data) {
	global $sql;
	$nt = new notify;
	$message = '<b>'.$data['news_title'].'</b><br /><br />'.$data['news_summary'].'<br /><br />'.$data['data'].'<br /><br />'.$data['news_extended'];
	$nt -> send('newspost', $data['news_title'], $message);
}

function notify_newsupd($data) {
	global $sql;
	$nt = new notify;
	$message = '<b>'.$data['news_title'].'</b><br /><br />'.$data['news_summary'].'<br /><br />'.$data['data'].'<br /><br />'.$data['news_extended'];
	$nt -> send('newsupd', 'Updated: '.$data['news_title'], $message);
}

function notify_newsdel($data) {
	global $sql;
	$nt = new notify;
	$nt -> send('newsdel', 'News Item Deleted', 'Deleted news item id: '.$data);
}

foreach ($nt -> notify_prefs['plugins'] as $plugin_id => $plugin_settings) {
	require_once(e_PLUGIN.$plugin_id.'/e_notify.php');
}

?>