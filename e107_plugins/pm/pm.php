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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/pm.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-08-31 16:45:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

$retrieve_prefs[] = 'pm_prefs';
require_once("../../class2.php");
require_once(e_PLUGIN."pm/pm_class.php");
require_once(e_PLUGIN."pm/pm_func.php");
$lan_file = e_PLUGIN."pm/languages/".e_LANGUAGE.".php";
include_once(is_readable($lan_file) ? $lan_file : e_PLUGIN."pm/languages/English.php");

define("ATTACHMENT_ICON", "<img src='".e_PLUGIN."pm/images/attach.png' alt='' />");

$qs = explode(".", e_QUERY);
$action = $qs[0];
$pm_prefs = $sysprefs->getArray("pm_prefs");

if(!check_class($pm_prefs['pm_class']))
{
	require_once(HEADERF);
	$ns->tablerender(LAN_PM, "You are not authorized to use the PM plugin");
	require_once(FOOTERF);
	exit;
}

$pm =& new private_message;
$message = "";

//Auto-delete message, if timeout set in admin
$del_qry = array();
$read_timeout = intval($pm_prefs['read_timeout']);
$unread_timeout = intval($pm_prefs['unread_timeout']);
if($read_timeout > 0)
{
	$timeout = time()-($read_timeout * 60);
	$del_qry[] = "(pm_sent < {$timeout} AND pm_read > 0)";
}
if($unread_timeout > 0)
{
	$timeout = time()-($unread_timeout * 60);
	$del_qry[] = "(pm_sent < {$timeout} AND pm_read = 0)";
}
if(count($del_qry) > 0)
{
	$qry = implode(" OR ", $del_qry);
	if($sql->db_Select("private_msg", "pm_id", $qry))
	{
		$delList = $sql->db_getList();
		foreach($delList as $p)
		{
			$pm->del($p['pm_id']);
		}
	}
}

if("del" == $action || isset($_POST['pm_delete_selected']))
{
	if(isset($_POST['pm_delete_selected']))
	{
		foreach(array_keys($_POST['selected_pm']) as $id)
		{
			$message .= "Deleting PM: $id <br />";
			$message .= $pm->del($id);
		}
	}
	if('del' == $action)
	{
		$message = $pm->del(intval($qs[1]));
	}
	if($qs[2] != "")
	{
		$action = $qs[2];
	}
	else
	{
		if(substr($_SERVER['HTTP_REFERER'], -5) == "inbox")
		{
			$action = "inbox";
		}
		if(substr($_SERVER['HTTP_REFERER'], -6) == "outbox")
		{
			$action = "outbox";
		}
	}
}

if('block' == $action)
{
	$message = $pm->block_add(intval($qs[1]));
	$action = 'inbox';
}

if('unblock' == $action)
{
	$message = $pm->block_del(intval($qs[1]));
	$action = 'inbox';
}

if("get" == $action)
{
	$pm->send_file(intval($qs[1]), intval($qs[2]));
	exit;
}

require_once(HEADERF);

if(isset($_POST['postpm']))
{
	$message = post_pm();
	$action = 'outbox';
}

if($message != "")
{
	$ns->tablerender("", $message);
}

if("send" == $action)
{
	$ns->tablerender(LAN_PM, show_send());
}

if("inbox" == $action)
{
	$ns->tablerender(LAN_PM." - inbox", show_inbox());
}

if("outbox" == $action)
{
	$ns->tablerender(LAN_PM." - outbox", show_outbox());
}

if("show" == $action)
{
	$ns->tablerender(LAN_PM, show_pm(intval($qs[1])));
}

require_once(FOOTERF);
exit;

function show_send()
{
	global $tp;
	$pm_outbox = pm_getInfo('outbox');
	if($pm_outbox['outbox']['filled'] >= 100)
	{
		return "Your outbox is currently {$pm_outbox['outbox']['filled']}% full, you are unable to send PMs until you delete some";
	}
	require_once(e_PLUGIN."pm/pm_shortcodes.php");
	$tpl_file = THEME."pm_template.php";
	include_once(is_readable($tpl_file) ? $tpl_file : e_PLUGIN."pm/pm_template.php");
	$enc = (check_class($pm_prefs['attach_class']) ? "enctype='multipart/form-data'" : "");
	$text = "<form {$enc} method='post' action='".e_SELF."' id='dataform' name='frmPMSend'>
	<input type='hidden' name='numsent' value='{$pm_outbox['outbox']['total']}'".
	$tp->parseTemplate($PM_SEND_PM, TRUE, $pm_shortcodes).
	"</form>";
	return $text;
}

function show_inbox()
{
	global $pm, $tp, $pm_shortcodes, $pm_info, $pm_blocks;
	require_once(e_PLUGIN."pm/pm_shortcodes.php");
	$tpl_file = THEME."pm_template.php";
	include(is_readable($tpl_file) ? $tpl_file : e_PLUGIN."pm/pm_template.php");
	$pm_blocks = $pm->block_get();
	$pmlist = $pm->pm_get_inbox();
	$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
	$txt .= $tp->parseTemplate($PM_INBOX_HEADER, true, $pm_shortcodes);
	if($pmlist['total_messages'])
	{
		foreach($pmlist['messages'] as $rec)
		{
			$pm_info = $rec;
			$txt .= $tp->parseTemplate($PM_INBOX_TABLE, true, $pm_shortcodes);
		}
	}
	else
	{
		$txt .= $tp->parseTemplate($PM_INBOX_EMPTY, true, $pm_shortcodes);
	}
	$txt .= $tp->parseTemplate($PM_INBOX_FOOTER, true, $pm_shortcodes);
	$txt .= "</form>";
	return $txt;
}

function show_outbox()
{
	global $pm, $tp, $pm_shortcodes, $pm_info;
	require_once(e_PLUGIN."pm/pm_shortcodes.php");
	$tpl_file = THEME."pm_template.php";
	include(is_readable($tpl_file) ? $tpl_file : e_PLUGIN."pm/pm_template.php");
	$pmlist = $pm->pm_get_outbox();
	$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
	$txt .= $tp->parseTemplate($PM_OUTBOX_HEADER, true, $pm_shortcodes);
	if($pmlist['total_messages'])
	{
		foreach($pmlist['messages'] as $rec)
		{
			$pm_info = $rec;
			$txt .= $tp->parseTemplate($PM_OUTBOX_TABLE, true, $pm_shortcodes);
		}
	}
	else
	{
		$txt .= $tp->parseTemplate($PM_OUTBOX_EMPTY, true, $pm_shortcodes);
	}
	$txt .= $tp->parseTemplate($PM_OUTBOX_FOOTER, true, $pm_shortcodes);
	$txt .= "</form>";
	return $txt;
}

function show_pm($pmid)
{
	global $pm, $tp, $pm_shortcodes, $pm_info;
	require_once(e_PLUGIN."pm/pm_shortcodes.php");
	$tpl_file = THEME."pm_template.php";
	include_once(is_readable($tpl_file) ? $tpl_file : e_PLUGIN."pm/pm_template.php");
	$pm_info = $pm->pm_get($pmid);
	if($pm_info['pm_read'] == 0)
	{
		$now = time();
		$pm_info['pm_read'] = $now;
		$pm->pm_mark_read($pmid);
	}
	$txt .= $tp->parseTemplate($PM_SHOW, true, $pm_shortcodes);
	return $txt;
}

function post_pm()
{
	global $pm_prefs, $pm, $pref, $sql;
	if(!check_class($pm_prefs['pm_class']))
	{
		return "You are not allowed to use PM";
	}

	$pm_info = pm_getInfo('outbox');
	if($pm_info['outbox']['total'] != $_POST['numsent'])
	{
		return "ERROR: Possible duplicate post, PM not sent";
	}

	if(isset($_POST['pm_to']))
	{
		$msg = "";
		if(isset($_POST['to_userclass']) && $_POST['to_userclass'])
		{
			if(!$pm_prefs['allow_userclass'])
			{
				return "not allowed to send to userclass";
			}
			elseif(!check_class($_POST['pm_userclass']) && !ADMIN)
			{
				return "Must be member of class";
			}
		}
		else
		{
			$to_array = array_unique(explode("\n", $_POST['pm_to']));
			if(check_class($pm_prefs['multi_class']) && count($to_array) > 1)
			{
				foreach($to_array as $to)
				{
					if($to_info = $pm->pm_getuid($to))
					{
 						if(!$sql->db_Update("private_msg_block","pm_block_count=pm_block_count+1 WHERE pm_block_from = '".USERID."' AND pm_block_to = '{$to}}'"))
 						{
 							$_POST['to_array'][] = $to_info;
 						}
					}
				}
			}
			else
			{
				if($to_info = $pm->pm_getuid($_POST['pm_to']))
				{
					$_POST['to_info'] = $to_info;
				}
				else
				{
					return "User not found!";
				}

				if($sql->db_Update("private_msg_block","pm_block_count=pm_block_count+1 WHERE pm_block_from = '".USERID."' AND pm_block_to = '{$to_info['user_id']}'"))
				{
					return "You are not allowed to send PMs to: ".$to_info['user_name'];
				}
			}
		}

		if(isset($_POST['receipt']))
		{
			if(!check_class($pm_prefs['receipt_class']))
			{
				unset($_POST['receipt']);
			}
		}
		$totalsize = strlen($_POST['pm_message']);
		$maxsize = intval($pm_prefs['attach_size']);
		foreach(array_keys($_FILES['file_userfile']['size']) as $fid)
		{
			if($maxsize > 0 && $_FILES['file_userfile']['size'][$fid] > $maxsize)
			{
				$msg .= "File: {$_FILES['file_userfile']['name'][$fid]} exceeds size limit - not attached";
				$_FILES['file_userfile']['size'][$fid] = 0;
			}
			$totalsize += $_FILES['file_userfile']['size'][$fid];
		}

		if(intval($pref['pm_limit']) > 0)
		{
			if($pref['pm_limit'] == '1')
			{
				if($pm_info['outbox']['total'] == $pm_info['outbox']['limit'])
				{
					return "Your outbox is full, you are not allowed to send";
				}
			}
			else
			{
				if($pm_info['outbox']['size'] + $totalsize > $pm_info['outbox']['limit'])
				{
					return "Adding this PM will exceed your maximum outbox size, PM not posted";
				}
			}
		}

		if($_FILES['file_userfile']['name'][0])
		{
			if(check_class($pm_prefs['attach_class']))
			{
				require_once(e_HANDLER."upload_handler.php");
				$randnum = rand(1000, 9999);			
				$_POST['uploaded'] = file_upload(e_PLUGIN."pm/attachments", "attachment", $randnum."_");
				if($_POST['uploaded'] == FALSE)
				{
					unset($_POST['uploaded']);
					$msg .= "File upload failed<br />";
				}
			}
			else
			{
				$msg .= "You are not allowed to send attachments<br />";
				unset($_POST['uploaded']);
			}
		}
		$_POST['from_id'] = USERID;
		return $msg.$pm->add($_POST);
	}
}
?>