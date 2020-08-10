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
 * $URL$
 * $Id$
 */


/**
 *	e107 Private messenger plugin
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */


// Note: all shortcodes now begin with 'PM', so some changes from previous versions



//if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_PLUGIN.'pm/languages/'.e_LANGUAGE.'.php');
include_once(e_PLUGIN.'pm/pm_func.php');

// register_shortcode('pm_handler_shortcodes', true);
// initShortcodeClass('pm_handler_shortcodes');


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

if(!class_exists('plugin_pm_pm_shortcodes'))
{
	class plugin_pm_pm_shortcodes extends e_shortcode // class pm_handler_shortcodes
	{
		public		$pmPrefs;		// PM system options
		public		$pmInfo;		// Data relating to current PM being displayed - replaced by $var.
		public		$pmBlocks = array();	// Array of blocked users.
		public		$pmBlocked = array();	// Block info when using 'display blocked' page
		public		$nextPrev = array();	//XXX In USE ?? // Variables used by nextprev
		public		$pmManager = NULL;		// Pointer to pmbox_manager class instance
		public		$pmNextPrev = array();
		public      $pmMode = null;
		//public 		$var = array();

		public function __construct()
		{
			$pm_prefs = e107::getPlugPref('pm');
			$this->pmManager = new pmbox_manager($pm_prefs);
			$this->pmPrefs = $pm_prefs;
			// print_a($pm_prefs);
			require_once(e_PLUGIN."pm/pm_class.php");

			$pmClass = new private_message($pm_prefs);

			$blocks = $pmClass->block_get_user();

			foreach($blocks as $usr)
			{
				if($usr['pm_block_to'] == USERID)
				{
					$this->pmBlocks[] = $usr['pm_block_from'];
				}

			}
		}


		public function sc_pm_form_to($parm)
		{
			$userTo = $this->sc_pm_form_touser();
			$classTo = $this->sc_pm_form_toclass();

			if(!empty($this->var['pm_from']))
			{
				return e107::getForm()->hidden('pm_to', $this->var['pm_from']).$this->var['from_name'];
			}

			if(deftrue('BOOTSTRAP'))
			{

				if(check_class($this->pmPrefs['opt_userclass']) )
				{
					$tab = array();

					$userDiz = (check_class($this->pmPrefs['multi_class'])) ? LAN_PM_112 : LAN_USER;

					$tab['user'] = array('caption'=> $userDiz, 'text'=>$userTo);
					$tab['class'] = array('caption'=>LAN_PM_4, 'text'=>$classTo);

					return e107::getForm()->tabs($tab);
				}
				else
				{
					return $userTo;
				}



			}

			return $userTo."<br />".$classTo; // Legacy Layout.


		}



		// TODO  Get rid of e107_handlers/user_select_class.php
		public function sc_pm_form_touser()
		{
			if(vartrue($this->var['pm_from']))
			{
				return "<input type='hidden' name='pm_to' value='{$this->var['pm_from']}' />{$this->var['from_name']}";
			}

		//	require_once(e_HANDLER.'user_select_class.php');
		//	$us = new user_select;
		//	$type = ($this->pmPrefs['dropdown'] == TRUE ? 'list' : 'popup');


			if(check_class($this->pmPrefs['multi_class']))
			{
				$ret = e107::getForm()->userpicker('pm_to', null, array('limit'=>10));
			}
			else
			{
				$frm = e107::getForm();

				$exclude = (getperms('0') && e_DEBUG === true) ? false : true;

				$ret = $frm->userlist('pm_to',null,array('excludeSelf'=>$exclude, 'default'=>'blank', 'classes'=>varset($this->pmPrefs['send_to_class'], e_UC_MEMBER)));
			}


			return $ret;
		}



		public function sc_pm_form_toclass($parm = '')
		{


			if(vartrue($this->var['from_name']))
			{
				return '';
			}

			$ret = "";

			if(check_class($this->pmPrefs['opt_userclass']) )
			{
				//$ret = "<input type='checkbox' name='to_userclass' value='1' />".LAN_PM_4." ";

		//		$ret = "<div class='input-group'><span class='input-group-addon'>".e107::getForm()->checkbox('to_userclass',1,false, LAN_PM_4)."</span>";

				// Option show by visibility
				$filterVisible = $parm == 'visible' ? 'matchclass, filter' : 'matchclass';

				$args = (ADMIN ? 'nobody, admin, classes' : 'nobody,classes, '.$filterVisible);
				if(check_class($this->pmPrefs['sendall_class']))
				{
					$args = 'member, '.$args;
				}

				$ret .= e107::getUserClass()->uc_dropdown('pm_userclass', e_UC_NOBODY, $args)."</div>";
				if (strpos($ret,'option') === FALSE)  $ret = '';
			}
			else
			{
				return false;
			}
			return $ret;
		}


		public function sc_pm_form_subject()
		{
			$value = '';
			if(vartrue($this->var['pm_subject']))
			{
				$value = $this->var['pm_subject'];
				if(substr($value, 0, strlen(LAN_PM_58)) != LAN_PM_58)
				{
					$value = LAN_PM_58.$value;
				}
			}

			return e107::getForm()->text('pm_subject',$value,255, array('size'=>80, 'required'=>1));

			// return "<input class='tbox' type='text' name='pm_subject' value='{$value}' size='63' maxlength='255' />";
		}


		public function sc_pm_form_message()
		{
			$value = '';
			$maxlength = '';
			$placeholder = '';

			if(vartrue($this->var['pm_text']))
			{
				if(isset($_POST['quote']))
				{
					$t = time();
					$value = "\n\n\n\n\n\n\n[quote{$t}={$this->var['from_name']}]\n".trim($this->var['pm_text'])."[/quote{$t}]";
				}
			}


			if(!empty($this->pmPrefs['maxlength']))
			{
				$length = intval($this->pmPrefs['maxlength']);
				$maxlength = "maxlength=".$length;
				$placeholder = "placeholder='Max. ".$length." chars.'"; // TODO LAN
			}

			return "<textarea class='tbox form-control' ".$placeholder." name='pm_message' cols='60' rows='10' ".$maxlength." onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>{$value}</textarea>";
		}


		public function sc_pm_emotes()
		{
			$active = e107::pref('core','smiley_activate');

			if(empty($active))
			{
				return null;
			}

			return r_emote();
		}


		public function sc_pm_post_button()
		{
			return "<input class='button btn btn-primary' type='submit' name='postpm' value='".LAN_PLUGIN_PM_NEW."' />";
		}


		public function sc_pm_preview_button()
		{
			return "<input class='button btn' type='submit' name='postpm' value='".LAN_PM_3."' />";
		}


		public function sc_pm_attachment()
		{
			if (check_class($this->pmPrefs['attach_class']))
			{
				$ret = "
				<div id='up_container' >
					<span id='upline' style='white-space:nowrap'>
					".e107::getForm()->file('file_userfile[]', array('size'=>40, 'multiple'=>'multiple'))."
					</span>
				</div>";
				/*
				$ret .= "
				<input type='button' class='btn btn-default button' value='".LAN_PM_11."' onclick=\"duplicateHTML('upline','up_container');\"  />
				";*/
				return $ret;
			}
			return '';
		}






		public function sc_pm_attachment_icon()
		{
			if($this->var['pm_attachments'] != "")
			{
				return ATTACHMENT_ICON;
			}
		}


		public function sc_pm_attachments()
		{
			$tp = e107::getParser();

			if($this->var['pm_attachments'] != '')
			{
				$attachments = explode(chr(0), $this->var['pm_attachments']);
				$i = 0;
				$ret = '';
				foreach($attachments as $a)
				{
					list($timestamp, $fromid, $rand, $filename) = explode("_", $a, 4);
					$url = $this->url('action/get', array('id' => $this->var['pm_id'], 'index' => $i));
					$ret .= $tp->toGlyph('fa-paperclip')."<a href='".$url."'>{$filename}</a><br />";
					$i++;
				}
				$ret = substr($ret, 0, -3);
				return $ret;
			}
		}


		public function sc_pm_receipt()
		{
			if (check_class($this->pmPrefs['receipt_class']))
			{
				return "<input type='checkbox' name='receipt' value='1' />".LAN_PM_10;
			}
			return '';
		}


		public function sc_pm_inbox_total()
		{
			$pm_inbox = $this->pmManager->pm_getInfo('inbox');
			return intval($pm_inbox['inbox']['total']);
		}


		public function sc_pm_inbox_unread()
		{
			$pm_inbox = $this->pmManager->pm_getInfo('inbox');
			return intval($pm_inbox['inbox']['unread']);
		}


		public function sc_pm_inbox_filled()
		{
			$pm_inbox = $this->pmManager->pm_getInfo('inbox');
			return (intval($pm_inbox['inbox']['filled']) > 0 ? $pm_inbox['inbox']['filled'] : '');
		}


		public function sc_pm_outbox_total()
		{
			$pm_outbox = $this->pmManager->pm_getInfo('outbox');
			return intval($pm_outbox['outbox']['total']);
		}


		public function sc_pm_outbox_unread()
		{
			$pm_outbox = $this->pmManager->pm_getInfo('outbox');
			return intval($pm_outbox['outbox']['unread']);
		}


		public function sc_pm_outbox_filled()
		{
			$pm_outbox = $this->pmManager->pm_getInfo('outbox');
			return (intval($pm_outbox['outbox']['filled']) > 0 ? $pm_outbox['outbox']['filled'] : '');
		}


		public function sc_pm_date($parm = '')
		{
			$tp = e107::getParser();

			if($parm)
			{
				return $tp->toDate($this->var['pm_sent'], $parm);
			}
			else
			{
				return $tp->toDate($this->var['pm_sent'], 'relative');
			}
		}


		public function sc_pm_read($parm = '')
		{
			if($this->pmMode == 'inbox')
			{
				return;
			}

			if($this->var['pm_read'] == 0)
			{
				return LAN_PM_27;
			}
			if($this->var['pm_read'] == 1)
			{
				return LAN_PM_28;
			}

			if('lapse' != $parm)
			{
				return e107::getDate()->convert_date($this->var['pm_read'], $parm);
			}
			else
			{
				return e107::getDate()->computeLapse($this->var['pm_read']);
			}
		}


		public function sc_pm_from_to()
		{
			$tp = e107::getParser();
		//	$sc = e107::getScBatch('pm',TRUE);

			if($this->pmMode == 'outbox')
			{
				$ret = LAN_PM_2.': ';
				$this->var['user_name'] = $this->var['sent_name'];
				$ret .= $this->sc_pm_to('link'); // $tp->parseTemplate("{PM_TO=link}", false, $sc);
			}
			else
			{
				$ret = LAN_PM_31.': ';
				$this->var['user_name'] = $this->var['from_name'];
				$ret .= $this->sc_pm_from('link');// $tp->parseTemplate("{PM_FROM=link}", false, $sc);
			}
			return $ret;
		}


		public function sc_pm_subject($parm = '')
		{
			$tp = e107::getParser();
			$ret = $tp->toHTML($this->var['pm_subject'], true, 'USER_TITLE');

			$prm = explode(',',$parm);

			if('link' == $prm[0])
			{
				$extra = '';
				// TODO - go with only one route version - view/message ???
				if (isset($prm[1])) $extra = '.'.$prm[1];

				/* Moc: commented because the URL was not rendered correctly. Reverted back to v1.x style.
				if($extra == 'inbox') $url = $this->url('message', 'id='.$this->var['pm_id']);
				elseif($extra == 'outbox') $url = $this->url('sent', 'id='.$this->var['pm_id']);
				else $url = $this->url('show', 'id='.$this->var['pm_id']);

				$ret = "<a href='".$ret."'>".$ret."</a>";
				*/

				$url = e107::url('pm','index')."?show.{$this->var['pm_id']}{$extra}";

				$ret = "<a href='".$url."'>".$ret."</a>";
			}
			return $ret;
		}


		public function sc_pm_from($parm = '')
		{
			$url = e107::getUrl();

			if('link' == $parm)
			{
				return "<a href='".$url->create('user/profile/view', array('id' => $this->var['pm_from'], 'name' => $this->var['user_name']))."'>{$this->var['user_name']}</a>";
			}
			else
			{
				return $this->var['user_name'];
			}
		}


		public function sc_pm_select()
		{
			return "<input type='checkbox' name='selected_pm[{$this->var['pm_id']}]' value='1' />";
		}


		public function sc_pm_read_icon()
		{
			if($this->var['pm_read'] > 0 )
			{
				return PM_READ_ICON;
			}
			else
			{
				return PM_UNREAD_ICON;
			}
		}

		public function sc_pm_status_class()
		{

			if($this->var['pm_read'] > 0 )
			{
				return 'pm-read';
			}
			else
			{
				return 'pm-unread';
			}

		}


		public function sc_pm_avatar($parm)
		{
			return e107::getParser()->toAvatar($this->var, $parm);
		}


		public function sc_pm_block_user()
		{



			if(in_array($this->var['pm_from'], $this->pmBlocks))
			{
				$icon = (deftrue('FONTAWESOME')) ? e107::getParser()->toGlyph('fa-user-plus','fw=1') : "<img src='".e_PLUGIN_ABS."pm/images/mail_unblock.png'  alt='".LAN_PM_51."' class='icon S16' />";

				return "<a class='btn  btn-default btn-secondary btn-danger' href='".$this->url('action/unblock', 'id='.$this->var['pm_from'])."' title='".LAN_PM_51."'>".$icon."</a>";
			}
			else
			{
				$icon = (deftrue('FONTAWESOME')) ? e107::getParser()->toGlyph('fa-user-times','fw=1') : "<img src='".e_PLUGIN_ABS."pm/images/mail_block.png'  alt='".LAN_PM_50."' class='icon S16' />";

				return "<a class='btn  btn-default btn-secondary' href='".$this->url('action/block', 'id='.$this->var['pm_from'])."' title='".LAN_PM_50."'>".$icon."</a>";
			}
		}


		public function sc_pm_delete($parm = '')
		{
			if($this->pmMode !== 'inbox' && $this->pmMode !== 'outbox' && empty($parm))
			{
				 return '';
			}


			if(!empty($parm))
			{
				$extra = '.'.$parm;
			}
			else
			{
				$extra = '.'.($this->pmMode == 'outbox' ? 'outbox' : 'inbox');
			}


			$action = $extra == '.outbox' ? 'delete-out' : 'delete-in';
			return "<a class='btn btn-default btn-secondary' title='".LAN_DELETE."' href='".$this->url('action/'.$action, 'id='.$this->var['pm_id'])."'>".PM_DELETE_ICON."</a>";
		}


		public function sc_pm_delete_selected()
		{
			$tp = e107::getParser();
			return e107::getForm()->button('pm_delete_selected',1,'delete',$tp->toGlyph('fa-trash').LAN_PM_53);
		//	return "<input type='submit' name='pm_delete_selected' class='button btn btn-sm btn-danger' value='".LAN_PM_53."' />";
		}


		public function sc_pm_to($parm = '')
		{
			if(is_numeric($this->var['pm_to']))
			{
				if('link' == $parm)
				{
					$url = e107::getUrl();
					return "<a href='".$url->create('user/profile/view', array('id' => $this->var['pm_to'], 'name' => $this->var['user_name']))."'>{$this->var['user_name']}</a>";
				}
				else
				{
					return $this->var['user_name'];
				}
			}
			else
			{
				return LAN_PM_63.' '.$this->var['pm_to'];
			}
		}


		public function sc_pm_message()
		{
			return  e107::getParser()->toHTML($this->var['pm_text'], true);
		}


		public function sc_pm_reply()
		{
			if($this->var['pm_to'] == USERID)
			{
				// pm_id is mapped insisde the config to id key
				$ret = "
				<form method='post' action='".$this->url('action/reply', $this->var)."'>
				<input type='checkbox' name='quote' /> ".LAN_PM_54." &nbsp;&nbsp;&nbsp;<input class='btn btn-primary button' type='submit' name='reply' value='".LAN_PM_55."' />
				</form>
				";
				return $ret;
			}
		}


		public function sc_pm_send_pm_link()
		{
			$pm_outbox = $this->pmManager->pm_getInfo('outbox');
			if($pm_outbox['outbox']['filled'] < 100)
			{
//				$link = $this->url('action/new');
//				return "<a class='btn btn-mini btn-xs btn-default' href='{$link}'>".LAN_PLUGIN_PM_NEW."</a>";
				return $this->url('action/new');
			}
//			return '';
		}


		public function sc_pm_newpm_animate()
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


		public function sc_pm_nextprev($parm = '')
		{
			return e107::getParser()->parseTemplate("{NEXTPREV={$this->pmNextPrev['total']},{$this->pmPrefs['perpage']},{$this->pmNextPrev['start']},".e_SELF."?{$parm}.[FROM]}");
		}


		public function sc_pm_blocked_senders_manage()
		{
			$sql = e107::getDb();
			$count = $sql->count('private_msg_block', '(*)', 'WHERE `pm_block_to` = '.USERID);
			if (!$count) return '';
			return LAN_PM_66;
		}


		public function sc_pm_blocked_select()
		{
			return "<input type='checkbox' name='selected_pm[{$this->pmBlocked['pm_block_from']}]' value='1' />";
		}


		public function sc_pm_blocked_user($parm = '')
		{
			if (!$this->pmBlocked['user_name'])
			{
				return LAN_PM_72;
			}
			if('link' == $parm)
			{

				$url = e107::getUrl();
				return "<a href='".$url->create('user/profile/view', array('id' => $this->pmBlocked['pm_block_from'], 'name' => $this->pmBlocked['user_name']))."'>{$this->pmBlocked['user_name']}</a>";
			}
			else
			{
				return $this->pmBlocked['user_name'];
			}
		}


		public function sc_pm_blocked_date($parm='')
		{
			return e107::getDate()->convert_date($this->pmBlocked['pm_block_datestamp'], $parm);
		}


		public function sc_pm_blocked_delete()
		{
			return "<a href='".$this->url('action/delete-blocked', array('id' => $this->pmBlocked['pm_block_from']))."'><img src='".e_PLUGIN_ABS."pm/images/mail_delete.png' title='".LAN_DELETE."' alt='".LAN_DELETE."' class='icon S16' /></a>";
		}


		public function sc_pm_delete_blocked_selected()
		{
			return "<input type='submit' name='pm_delete_blocked_selected' class='btn btn-default btn-secondary button' value='".LAN_PM_53."' />";
		}


		public function sc_pm_compose()
		{
			$tp = e107::getParser();
			$urlCompose = e107::url('pm','index','', array('query'=>array('mode'=>'send')));

			$class = (!empty($parm['class'])) ? $parm['class'] : 'btn btn-sm btn-primary btn-block-level';

			return "<a class='".$class."' href='".$urlCompose."'>".$tp->toGlyph('fa-edit',' ')."".LAN_PLUGIN_PM_NEW."</a>";
		}



		private function url($route, $params = array())
		{

			if(is_string($params))
			{
				parse_str($params,$params);
			}

			if(!isset($params['id']) && isset($params['pm_id'])) $params['id'] = $params['pm_id'];

			if(is_string($route))
			{
				$route = explode('/', $route, 2);
			}

			$base = e107::url('pm','index').'?';

			switch($route[1])
			{
				case 'index':
				case 'inbox':
					return $base.'inbox';
					break;
				case 'outbox':
					return $base.'outbox';
					break;
					// we could just remove them all and let only 'message' live
				case 'show':
					return $base.'show.'.$params['id'];
					break;
				case 'message':
					return $base.'show.'.$params['id'].'.inbox';
					break;
				case 'sent':
					return $base.'show.'.$params['id'].'.outbox';
					break;
				case 'reply':
					return $base.'reply.'.$params['id'];
					break;
				case 'new':
					return $base.'send';
					break;
				case 'delete-in':
					return $base.'del.'.$params['id'].'.inbox';
					break;
				case 'delete-out':
					return $base.'del.'.$params['id'].'.outbox';
					break;
				case 'delete-blocked':
					return $base.'delblocked.'.$params['id'];
					break;
				case 'block':
					return $base.'block.'.$params['id'];
					break;
				case 'unblock':
					return $base.'unblock.'.$params['id'];
					break;
				case 'get':
					return $base.'get.'.$params['id'].'.'.$params['index'];
					break;
			}

		}

	}

}


