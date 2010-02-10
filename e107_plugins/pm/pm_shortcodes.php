<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	PM plugin - shortcodes
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


/**
 *	e107 Private messenger plugin
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */


// Note: all shortcodes now begin with 'PM', so some changes from previous versions



if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');	
include_once(e_PLUGIN.'pm/pm_func.php');
register_shortcode('pm_handler_shortcodes', true);
initShortcodeClass('pm_handler_shortcodes');


/*
PM_FORM_TOUSER	- displays user entry box and selector
PM_FORM_TOCLASS - displays user class selector (when allowed)
PM_FORM_SUBJECT	- displays subject of current PM
PM_FORM_MESSAGE - displays message of current PM
PM_EMOTES
PM_POST_BUTTON
PM_PREVIEW_BUTTON
PM_ATTACHMENT
PM_ATTACHMENT_ICON
PM_ATTACHMENTS
PM_RECEIPT
PM_INBOX_TOTAL
PM_INBOX_UNREAD
PM_INBOX_FILLED
PM_OUTBOX_TOTAL
PM_OUTBOX_UNREAD
PM_OUTBOX_FILLED
PM_DATE
PM_READ
PM_FROM_TO
PM_SUBJECT
PM_FROM
PM_SELECT
PM_READ_ICON
PM_AVATAR
PM_BLOCK_USER
PM_DELETE
PM_DELETE_SELECTED
PM_TO
PM_MESSAGE
PM_REPLY
PM_SEND_PM_LINK
PM_NEWPM_ANIMATE
PM_NEXTPREV
PM_BLOCKED_SENDERS_MANAGE
PM_BLOCKED_SELECT
PM_BLOCKED_USER
PM_BLOCKED_DATE
PM_BLOCKED_DELETE
DELETE_BLOCKED_SELECTED
*/


class pm_handler_shortcodes
{
	protected	$e107;
	public		$pmPrefs;		// PM system options
	public		$pmInfo;		// Data relating to current PM being displayed
	public		$pmBlocks = array();	// Array of blocked users.
	public		$pmBlocked = array();	// Block info when using 'display blocked' page
	public		$nextPrev = array();	// Variables used by nextprev
	public		$pmManager = NULL;		// Pointer to pmbox_manager class instance

	public function __construct()
	{
		$this->e107 = e107::getInstance();
	}


	public function sc_pm_form_touser()
	{
		if(vartrue($this->pmInfo['from_name']))
		{
			return "<input type='hidden' name='pm_to' value='{$this->pmInfo['from_name']}' />{$this->pmInfo['from_name']}";
		}
		require_once(e_HANDLER.'user_select_class.php');
		$us = new user_select;
		$type = ($this->pmPrefs['dropdown'] == TRUE ? 'list' : 'popup');
		if(check_class($this->pmPrefs['multi_class']))
		{
			$ret = $us->select_form($type, 'textarea.pm_to');
		}
		else
		{
			$ret = $us->select_form($type, 'pm_to');
		}
		return $ret;
	}

	public	function sc_pm_form_toclass()
	{
		if(vartrue($this->pmInfo['from_name']))
		{
			return '';
		}
		if(check_class($this->pmPrefs['opt_userclass']) && check_class($this->pmPrefs['multi_class']))
		{
			$ret = "<input type='checkbox' name='to_userclass' value='1' />".LAN_PM_4." ";
			require_once(e_HANDLER.'userclass_class.php');
			$args = (ADMIN ? 'admin, classes' : 'classes, matchclass');
			if(check_class($this->pmPrefs['sendall_class']))
			{
				$args = 'member, '.$args;
			}
			$ret .= e107::getUserClass()->uc_dropdown('pm_userclass', '', $args);
			if (strpos($ret,'option') === FALSE)  $ret = '';
		}
		return $ret;
	}


	public	function sc_pm_form_subject()
	{
		$value = '';
		if(vartrue($this->pmInfo['pm_subject']))
		{
			$value = $this->pmInfo['pm_subject'];
			if(substr($value, 0, strlen(LAN_PM_58)) != LAN_PM_58)
			{
				$value = LAN_PM_58.$value;
			}
		}
		return "<input class='tbox' type='text' name='pm_subject' value='{$value}' size='63' maxlength='255' />";
	}


	public	function sc_pm_form_message()
	{
		$value = '';
		if(vartrue($this->pmInfo['pm_text']))
		{
			if(isset($_POST['quote']))
			{
				$t = time();
				$value = "[quote{$t}={$this->pmInfo['from_name']}]\n{$this->pmInfo['pm_text']}\n[/quote{$t}]\n\n";
			}
		}
		return "<textarea class='tbox' name='pm_message' cols='60' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>{$value}</textarea>";
	}


	public	function sc_pm_emotes()
	{
		require_once(e_HANDLER.'emote.php');
		return r_emote();
	}


	public	function sc_pm_post_button()
	{
		return "<input class='button' type='submit' name='postpm' value='".LAN_PM_1."' />";
	}


	public	function sc_pm_preview_button()
	{
		return "<input class='button' type='submit' name='postpm' value='".LAN_PM_3."' />";
	}


	public	function sc_pm_attachment()
	{
		if (check_class($this->pmPrefs['attach_class']))
		{
			$ret = "
			<div id='up_container' >
			<span id='upline' style='white-space:nowrap'>
			<input class='tbox' type='file' name='file_userfile[]' size='40' />
			</span>
			</div>
			<input type='button' class='button' value='".LAN_PM_11."' onclick=\"duplicateHTML('upline','up_container');\"  />
			";
			return $ret;
		}
		return '';
	}


	public	function sc_pm_attachment_icon()
	{
		if($this->pmInfo['pm_attachments'] != "")
		{
			return ATTACHMENT_ICON;
		}
	}


	public	function sc_pm_attachments()
	{
		if($this->pmInfo['pm_attachments'] != '')
		{
			$attachments = explode(chr(0), $this->pmInfo['pm_attachments']);
			$i = 0;
			foreach($attachments as $a)
			{
				list($timestamp, $fromid, $rand, $filename) = explode("_", $a, 4);
				$ret .= "<a href='".e_SELF."?get.{$this->pmInfo['pm_id']}.{$i}'>{$filename}</a><br />";
				$i++;
			}
			$ret = substr($ret, 0, -3);
			return $ret;
		}
	}


	public	function sc_pm_receipt()
	{
		if (check_class($this->pmPrefs['receipt_class']))
		{
			return "<input type='checkbox' name='receipt' value='1' />".LAN_PM_10;
		}
		return '';
	}


	public	function sc_pm_inbox_total()
	{
		$pm_inbox = $this->pmManager->pm_getInfo('inbox');
		return intval($pm_inbox['inbox']['total']);
	}


	public	function sc_pm_inbox_unread()
	{
		$pm_inbox = $this->pmManager->pm_getInfo('inbox');
		return intval($pm_inbox['inbox']['unread']);
	}


	public	function sc_pm_inbox_filled()
	{
		$pm_inbox = $this->pmManager->pm_getInfo('inbox');
		return (intval($pm_inbox['inbox']['filled']) > 0 ? $pm_inbox['inbox']['filled'] : '');
	}


	public	function sc_pm_outbox_total()
	{
		$pm_outbox = $this->pmManager->pm_getInfo('outbox');
		return intval($pm_outbox['outbox']['total']);
	}


	public	function sc_pm_outbox_unread()
	{
		$pm_outbox = $this->pmManager->pm_getInfo('outbox');
		return intval($pm_outbox['outbox']['unread']);
	}


	public	function sc_pm_outbox_filled()
	{
		$pm_outbox = $this->pmManager->pm_getInfo('outbox');
		return (intval($pm_outbox['outbox']['filled']) > 0 ? $pm_outbox['outbox']['filled'] : '');
	}


	public	function sc_pm_date($parm = '')
	{
		require_once(e_HANDLER.'date_handler.php');
		if('lapse' != $parm)
		{
			return convert::convert_date($this->pmInfo['pm_sent'], $parm);
		}
		else
		{
			return convert::computeLapse($this->pmInfo['pm_sent']);
		}
	}


	public	function sc_pm_read($parm = '')
	{
		if($this->pmInfo['pm_read'] == 0)
		{
			return LAN_PM_27;
		}
		if($this->pmInfo['pm_read'] == 1)
		{
			return LAN_PM_28;
		}
		require_once(e_HANDLER.'date_handler.php');
		if('lapse' != $parm)
		{
			return convert::convert_date($this->pmInfo['pm_read'], $parm);
		}
		else
		{
			return convert::computeLapse($this->pmInfo['pm_read']);
		}
	}


	public	function sc_pm_from_to()
	{
		if($this->pmInfo['pm_from'] == USERID)
		{
			$ret = LAN_PM_2.': <br />';
			$this->pmInfo['user_name'] = $this->pmInfo['sent_name'];
			$ret .= $this->e107->tp->parseTemplate("{PM_TO=link}");
		}
		else
		{
			$ret = LAN_PM_31.': <br />';
			$this->pmInfo['user_name'] = $this->pmInfo['from_name'];
			$ret .= $this->e107->tp->parseTemplate("{PM_FROM=link}");
		}
		return $ret;
	}


	public	function sc_pm_subject($parm = '')
	{
		$ret = $this->e107->tp->toHTML($this->pmInfo['pm_subject'], true, 'USER_TITLE');
		$prm = explode(',',$parm);
		if('link' == $prm[0])
		{
			$extra = '';
			if (isset($prm[1])) $extra = '.'.$prm[1];
			$ret = "<a href='".e_PLUGIN_ABS."pm/pm.php?show.{$this->pmInfo['pm_id']}{$extra}'>".$ret."</a>";
		}
		return $ret;
	}


	public	function sc_pm_from($parm = '')
	{
		if('link' == $parm)
		{
			return "<a href='".e_HTTP."user.php?id.{$this->pmInfo['pm_from']}'>{$this->pmInfo['user_name']}</a>";
		}
		else
		{
			return $this->pmInfo['user_name'];
		}
	}


	public	function sc_pm_select()
	{
		return "<input type='checkbox' name='selected_pm[{$this->pmInfo['pm_id']}]' value='1' />";
	}


	public	function sc_pm_read_icon()
	{
		if($this->pmInfo['pm_read'] > 0 )
		{
			return PM_READ_ICON;
		}
		else
		{
			return PM_UNREAD_ICON;
		}
	}


	public	function sc_pm_avatar()
	{
		return $this->e107->tp->parseTemplate("{USER_AVATAR={$this->pmInfo['user_image']}}");
	}


	public	function sc_pm_block_user()
	{
		if(in_array($this->pmInfo['pm_from'], $this->pmBlocks))
		{
			return "<a href='".e_PLUGIN_ABS."pm/pm.php?unblock.{$this->pmInfo['pm_from']}'><img src='".e_PLUGIN_ABS."pm/images/mail_unblock.png' title='".LAN_PM_51."' alt='".LAN_PM_51."' class='icon S16' /></a>";
		}
		else
		{
			return "<a href='".e_PLUGIN_ABS."pm/pm.php?block.{$this->pmInfo['pm_from']}'><img src='".e_PLUGIN_ABS."pm/images/mail_block.png' title='".LAN_PM_50."' alt='".LAN_PM_50."' class='icon S16' /></a>";
		}
	}


	public	function sc_pm_delete($parm = '')
	{
		if($parm != '')
		{
			$extra = '.'.$parm;
		}
		else
		{
			$extra = '.'.($this->pmInfo['pm_from'] == USERID ? 'outbox' : 'inbox');
		}
		return "<a href='".e_PLUGIN_ABS."pm/pm.php?del.{$this->pmInfo['pm_id']}{$extra}'><img src='".e_PLUGIN_ABS."pm/images/mail_delete.png' title='".LAN_PM_52."' alt='".LAN_PM_52."' class='icon S16' /></a>";
	}


	public	function sc_pm_delete_selected()
	{
		return "<input type='submit' name='pm_delete_selected' class='button' value='".LAN_PM_53."' />";
	}


	public	function sc_pm_to($parm = '')
	{
		if(is_numeric($this->pmInfo['pm_to']))
		{
			if('link' == $parm)
			{
				return "<a href='".e_HTTP."user.php?id.{$this->pmInfo['pm_to']}'>{$this->pmInfo['user_name']}</a>";
			}
			else
			{
				return $this->pmInfo['user_name'];
			}
		}
		else
		{
			return LAN_PM_63.' '.$this->pmInfo['pm_to'];
		}
	}


	public	function sc_pm_message()
	{
		return $this->e107->tp->toHTML($this->pmInfo['pm_text'], true);
	}


	public	function sc_pm_reply()
	{
		if($this->pmInfo['pm_to'] == USERID)
		{
			$ret = "
			<form method='post' action='".e_SELF."?reply.{$this->pmInfo['pm_id']}'>
			<input type='checkbox' name='quote' /> ".LAN_PM_54." &nbsp;&nbsp;&nbsp<input class='button' type='submit' name='reply' value='".LAN_PM_55."' />
			</form>
			";
			return $ret;
		}
	}


	public	function sc_pm_send_pm_link()
	{
		$pm_outbox = $this->pmManager->pm_getInfo('outbox');
		if($pm_outbox['outbox']['filled'] < 100)
		{
			$link = $this->e107->url->getUrl('pm','main',array('f' => 'send'));
			return "<a href='{$link}'>".PM_SEND_LINK."</a>";
		}
		return '';
	}


	public	function sc_pm_newpm_animate()
	{
		if($this->pmPrefs['animate'])
		{
			$pm_inbox = $this->pmManager->pm_getInfo('inbox');
			if($pm_inbox['inbox']['new'] > 0)
			{
				return NEWPM_ANIMATION;
			}
		}
		return '';
	}


	public	function sc_pm_nextprev($parm = '')
	{
		return $this->e107->tp->parseTemplate("{NEXTPREV={$this->pmNextPrev['total']},{$this->pmPrefs['perpage']},{$this->pmNextPrev['start']},".e_SELF."?{$parm}.[FROM]}");
	}


	public	function sc_pm_blocked_senders_manage()
	{
		$count = $this->e107->sql->db_Count('private_msg_block', '(*)', 'WHERE `pm_block_to` = '.USERID);
		if (!$count) return '';
		return LAN_PM_66;
	}


	public	function sc_pm_blocked_select()
	{
		return "<input type='checkbox' name='selected_pm[{$this->pmBlocked['pm_block_from']}]' value='1' />";
	}


	public	function sc_pm_blocked_user($parm = '')
	{
		if (!$this->pmBlocked['user_name'])
		{
			return LAN_PM_72;
		}
		if('link' == $parm)
		{
			return "<a href='".e_HTTP."user.php?id.{$this->pmBlocked['pm_block_from']}'>{$this->pmBlocked['user_name']}</a>";
		}
		else
		{
			return $this->pmBlocked['user_name'];
		}
	}


	public	function sc_pm_blocked_date($parm='')
	{
		require_once(e_HANDLER.'date_handler.php');
		return convert::convert_date($this->pmBlocked['pm_block_datestamp'], $parm);
	}


	public	function sc_pm_blocked_delete()
	{
		return "<a href='".e_PLUGIN_ABS."pm/pm.php?delblocked.{$this->pmBlocked['pm_block_from']}'><img src='".e_PLUGIN_ABS."pm/images/mail_delete.png' title='".LAN_PM_52."' alt='".LAN_PM_52."' class='icon S16' /></a>";
	}


	public	function sc_pm_delete_blocked_selected()
	{
		return "<input type='submit' name='pm_delete_blocked_selected' class='button' value='".LAN_PM_53."' />";
	}
}


?>