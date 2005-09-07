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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm_class.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-09-07 13:37:06 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class private_message
{
	function pm_mark_read($pm_id, $pm_info)
	{
		$now = time();
		global $pm_prefs, $sql;
		if($pm_prefs['read_delete'])
		{
			$this->del($pm_id);
		}
		else
		{
			$sql->db_Select_gen("UPDATE #private_msg SET pm_read = {$now} WHERE pm_id={$pm_id}");
			if(strpos($pm_info['pm_option'], "+rr") !== FALSE)
			{
				$this->pm_send_receipt($pm_info);
			}
		}
	}

	function pm_get($pmid)
	{
		global $sql;
		$qry = "
		SELECT pm.*, ut.user_image AS sent_image, ut.user_name AS sent_name, uf.user_image AS from_image, uf.user_name AS from_name, uf.user_email as from_email, ut.user_email as to_email  FROM #private_msg AS pm
		LEFT JOIN #user AS ut ON ut.user_id = pm.pm_to
		LEFT JOIN #user AS uf ON uf.user_id = pm.pm_from
		WHERE pm.pm_id='{$pmid}'
		";
		if ($sql->db_Select_gen($qry))
		{
			$row = $sql->db_Fetch();
			return $row;
		}
		return FALSE;
	}

	function add($vars)
	{
		global $pm_prefs, $tp, $sql;
		$vars['options'] = "";
		$pmsize = 0;
		$attachlist = "";
		$pm_options = "";
		if(isset($vars['receipt']) && $vars['receipt']) {$pm_options .= "+rr+";	}
		if(isset($vars['uploaded']))
		{
			foreach($vars['uploaded'] as $u)
			{
				if(!isset($u['error']))
				{
					$pmsize += $u['size'];
					$a_list[] = $u['name'];
				}
			}
			$attachlist = implode(chr(0), $a_list);
		}
		$pmsize += strlen($vars['pm_message']);

		$pm_subject = $tp->toDB($vars['pm_subject']);
		$pm_message = $tp->toDB($vars['pm_message']);
		$sendtime = time();
		if(isset($vars['to_userclass']) || isset($vars['to_array']))
		{
			if(isset($vars['to_userclass']))
			{
				require_once(e_HANDLER."userclass_class.php");
				$toclass = r_userclass_name($vars['pm_userclass']);
				$tolist = $this->get_users_inclass($vars['pm_userclass']);
				$ret .= LAN_PM_38.": {$vars['to_userclass']}<br />";
				$class = TRUE;
			}
			else
			{
				$tolist = $vars['to_array'];
				$class = FALSE;
			}
			foreach($tolist as $u)
			{
				set_time_limit(30);
				if($pmid = $sql->db_Insert("private_msg", "0, '{$vars['from_id']}', '{$u['user_id']}', '{$sendtime}', '0', '{$pm_subject}', '{$pm_message}', '1', '0', '{$attachlist}', '{$pm_option}', '{$pmsize}'"))
				{
					if($class == FALSE)
					{
						$toclass .= $u['user_name'].", ";
					}
					if(check_class($pm_prefs['notify_class'], $u['user_class']))
					{
						$this->pm_send_notify($u['user_id'], $vars, $pmid, count($a_list));
					}
				}
				else
				{
					$ret .= LAN_PM_39.": {$u['user_name']} <br />";
				}
			}
			if(!$pmid = $sql->db_Insert("private_msg", "0, '{$vars['from_id']}', '{$toclass}', '{$sendtime}', '1', '{$pm_subject}', '{$pm_message}', '0', '0', '{$attachlist}', '{$pm_options}', '{$pmsize}'"))
			{
				$ret .= LAN_PM_41."<br />";
			}
			
		}
		else
		{
			if($pmid = $sql->db_Insert("private_msg", "0, '{$vars['from_id']}', '{$vars['to_info']['user_id']}', '{$sendtime}', '0', '{$pm_subject}', '{$pm_message}', '0', '0', '{$attachlist}', '{$pm_options}', '{$pmsize}'"))
			{
				if(check_class($pm_prefs['notify_class'], $vars['to_info']['user_class']))
				{
					set_time_limit(30);
					$this->pm_send_notify($vars['to_info']['user_id'], $vars, $pmid, count($a_list));
				}
				$ret .= LAN_PM_40.": {$vars['to_info']['user_name']}<br />";
			}
		}
		return $ret;
	}

	function del($pmid)
	{
		global $sql;
		$ret = "";
		$del_pm = FALSE;
		$newvals = "";
		if($sql->db_Select("private_msg", "*", "pm_id = {$pmid} AND (pm_from = ".USERID." OR pm_to = ".USERID.")"))
		{
			$row = $sql->db_Fetch();
			if($row['pm_to'] == USERID)
			{
				$newvals = "pm_read_del = 1";
				$ret .= LAN_PM_42."<br />";
				if($row['pm_sent_del'] == 1) { $del_pm = TRUE; }
			}
			if($row['pm_from'] == USERID)
			{
				if($newvals != "") { $del_pm = TRUE; }
				$newvals = "pm_sent_del = 1";
				$ret .= LAN_PM_43."<br />";
				if($row['pm_read_del'] == 1) { $del_pm = TRUE; }
			}

			if(count($newvals) == 2 || $del_pm == TRUE)
			{
				// Delete any attachments and remove PM from db
				$attachments = explode(chr(0), $row['pm_attachments']);
				foreach($attachments as $a)
				{
					$filename = getcwd()."/attachments/{$a}";
					unlink($filename);
				}
				$sql->db_Delete("private_msg", "pm_id = {$pmid}");
			}
			else
			{
				$sql->db_Update("private_msg", $newvals." WHERE pm_id = {$pmid}");
			}
			return $ret;
		}
	}

	function pm_send_notify($uid, $pminfo, $pmid, $attach_count = 0)
	{
		require_once(e_HANDLER."mail.php");
		global $PLUGINS_DIRECTORY;
		$subject = LAN_PM_100.SITENAME;
		$pmlink = SITEURL.$PLUGINS_DIRECTORY."pm/pm.php?show.{$pmid}";
		$txt = LAN_PM_101.SITENAME."\n\n";
		$txt .= LAN_PM_102.USERNAME."\n";
		$txt .= LAN_PM_103.$pminfo['pm_subject']."\n";
		if($attch_count > 0)
		{
			$txt .= LAN_PM_104.$attach_count."\n";
		}
		$txt .= LAN_PM_105."\n".$pmlink."\n";
		sendemail($pminfo['to_info']['user_email'], $subject, $txt, $pminfo['to_info']['user_name']);
	}

	function pm_send_receipt($pminfo)
	{
		require_once(e_HANDLER."mail.php");
		global $PLUGINS_DIRECTORY;
		$subject = LAN_PM_106.$pminfo['sent_name'];
		$pmlink = SITEURL.$PLUGINS_DIRECTORY."pm/pm.php?show.{$pminfo['pm_id']}";
		$txt = str_replace("{UNAME}", $pminfo['sent_name'], LAN_PM_107).date('l F dS Y h:i:s A')."\n\n";
		$txt .= LAN_PM_108.date('l F dS Y h:i:s A', $pminfo['pm_sent'])."\n";
		$txt .= LAN_PM_103.$pminfo['pm_subject']."\n";
		$txt .= LAN_PM_105."\n".$pmlink."\n";
		sendemail($pminfo['from_email'], $subject, $txt, $pminfo['from_name']);
	}

	function block_get($to = USERID)
	{
		global $sql;
		$ret = array();
		if($sql->db_Select("private_msg_block", "pm_block_from", "pm_block_to = '{$to}'"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[] = $row['pm_block_from'];
			}
		}
		return $ret;
	}

	function block_add($from, $to = USERID)
	{
		global $sql;
		if($sql->db_Select("user", "user_name", "user_id = '{$from}'"))
		{
			$uinfo = $sql->db_Fetch();
			if(!$sql->db_Count("private_msg_block", "(*)", "WHERE pm_block_from = '{$from}' AND pm_block_to = '{$to}'"))
			{
				if($sql->db_Insert("private_msg_block", "0, '{$from}', '{$to}', '".time()."', '0'"))
				{
					return str_replace('{UNAME}', $uinfo['user_name'], LAN_PM_47);
				}
				else
				{
					return LAN_PM_48;
				}
			}
			else
			{
				return str_replace('{UNAME}', $uinfo['user_name'], LAN_PM_49);
			}
		}
		else
		{
			return LAN_PM_17;
		}
	}

	function block_del($from, $to = USERID)
	{
		global $sql;
		if($sql->db_Select("user", "user_name", "user_id = '{$from}'"))
		{
			$uinfo = $sql->db_Fetch();
			if($sql->db_Select("private_msg_block", "pm_block_id", "pm_block_from = '{$from}' AND pm_block_to = '{$to}'"))
			{
				$row = $sql->db_Fetch();
				if($sql->db_Delete("private_msg_block", "pm_block_id = '{$row['pm_block_id']}'"))
				{
					return str_replace("{UNAME}", $uinfo['user_name'], LAN_PM_44);
				}
				else
				{
					return LAN_PM_45;
				}
			}
			else
			{
				return str_replace("{UNAME}", $uinfo['user_name'], LAN_PM_46);
			}
		}
		else
		{
			return LAN_PM_17;
		}
	}

	function pm_getuid($var)
	{
		global $sql;
		$var = trim($var);
		if($sql->db_Select("user", "user_id, user_name, user_class, user_email", "user_name LIKE '{$var}'"))
		{
			$row = $sql->db_Fetch();
			return $row;
		}
		return FALSE;
	}

	function get_users_inclass($class)
	{
		global $sql;
		if($class == e_UC_MEMBER)
		{
			$qry = "SELECT user_id, user_name, user_email, user_class FROM #user WHERE 1";
		}
		elseif($class == e_UC_ADMIN)
		{
			$qry = "SELECT user_id, user_name, user_email, user_class FROM #user WHERE user_admin = 1";
		}
		elseif($class)
		{
			$regex = "(^|,)(".$class.")(,|$)";
			$qry = "SELECT user_id, user_name, user_email, user_class FROM #user WHERE user_class REGEXP {$regex}";
		}
		if($sql->db_Select_gen($qry))
		{
			$ret = $sql->db_getList();
			return $ret;
		}
		return FALSE;
	}

	function pm_get_inbox($uid = USERID, $from = 0, $limit = 10)
	{
		global $sql;
		$qry = "
		SELECT SQL_CALC_FOUND_ROWS pm.*, u.user_image, u.user_name FROM #private_msg AS pm
		LEFT JOIN #user AS u ON u.user_id = pm.pm_from
		WHERE pm.pm_to='{$uid}' AND pm.pm_read_del=0
		ORDER BY pm.pm_sent DESC
		LIMIT {$from}, {$limit}
		";
		if($sql->db_Select_gen($qry))
		{
			$ret['messages'] = $sql->db_getList();
			$sql->db_Query("SELECT FOUND_ROWS() AS total");
			$ret['total_messages'] = $sql -> db_Fetch();
		}
		return $ret;
	}

	function pm_get_outbox($uid = USERID, $from = 0, $limit = 10)
	{
		global $sql;
		$qry = "
		SELECT SQL_CALC_FOUND_ROWS pm.*, u.user_image, u.user_name FROM #private_msg AS pm
		LEFT JOIN #user AS u ON u.user_id = pm.pm_to
		WHERE pm.pm_from='{$uid}' AND pm.pm_sent_del=0
		ORDER BY pm.pm_sent DESC
		LIMIT {$from}, {$limit}
		";
		if($sql->db_Select_gen($qry))
		{
			$ret['messages'] = $sql->db_getList();
			$sql->db_Query("SELECT FOUND_ROWS() AS total");
			$ret['total_messages'] = $sql -> db_Fetch();
		}
		return $ret;
	}

	function send_file($pmid, $filenum)
	{
		global $pref;
		$pm_info = $this->pm_get($pmid);
		$attachments = explode(chr(0), $pm_info['pm_attachments']);
		if(!isset($attachments[$filenum]))
		{
			return FALSE;
		}
		$fname = $attachments[$filenum];
		list($timestamp, $fromid, $rand, $file) = explode("_", $fname, 4);
		$filename = getcwd()."/attachments/{$fname}";

		if($fromid != $pm_info['pm_from'])
		{
			return FALSE;
		}
		if(!is_file($filename))
		{
			return FALSE;
		}
		@set_time_limit(10 * 60);
		@ini_set("max_execution_time", 10 * 60);
		while (@ob_end_clean()); // kill all output buffering else it eats server resources
		if (connection_status() == 0)
		{
			if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
				$file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
			}
			if (isset($_SERVER['HTTP_RANGE']))
			{
				$seek = intval(substr($_SERVER['HTTP_RANGE'] , strlen('bytes=')));
			}
			$bufsize = 2048;
			ignore_user_abort(true);
			$data_len = filesize($filename);
			if ($seek > ($data_len - 1)) $seek = 0;
			$res =& fopen($filename, 'rb');
			if ($seek)
			{
				fseek($res , $seek);
			}
			$data_len -= $seek;
			header("Expires: 0");
			header("Cache-Control: max-age=30" );
			header("Content-Type: application/force-download");
			header("Content-Disposition: attachment; filename={$file}");
			header("Content-Length: {$data_len}");
			header("Pragma: public");
			if ($seek)
			{
				header("Accept-Ranges: bytes");
				header("HTTP/1.0 206 Partial Content");
				header("status: 206 Partial Content");
				header("Content-Range: bytes {$seek}-".($data_len - 1)."/{$data_len}");
			}
			while (!connection_aborted() && $data_len > 0)
			{
				echo fread($res , $bufsize);
				$data_len -= $bufsize;
			}
			fclose($res);
		}
	}
}
?>