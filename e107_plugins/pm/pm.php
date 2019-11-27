<?php
	/*
	 * e107 website system
	 *
	 * Copyright (C) 2008-2013 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 *	PM plugin - main user interface
	 *
	 */


	/**
	 *    e107 Private messenger plugin
	 *
	 * @package    e107_plugins
	 * @subpackage    pm
	 */


	$retrieve_prefs[] = 'pm_prefs';
	if(!defined('e107_INIT'))
	{
		require_once("../../class2.php");
	}


	if(!e107::isInstalled('pm'))
	{
		e107::redirect();
		exit;
	}

	if(vartrue($_POST['keyword']))
	{
		pm_user_lookup();
	}

	e107::css('pm', 'pm.css');
	require_once(e_PLUGIN . 'pm/pm_class.php');
	require_once(e_PLUGIN . 'pm/pm_func.php');
	e107::includeLan(e_PLUGIN . 'pm/languages/' . e_LANGUAGE . '.php');
	e107::getScParser();
	// require_once(e_PLUGIN.'pm/shortcodes/batch/pm_shortcodes.php');

	if(!defined('ATTACHMENT_ICON'))
	{
		if(deftrue('BOOTSTRAP') && deftrue('FONTAWESOME'))
		{
			define('ATTACHMENT_ICON', $tp->toGlyph('fa-paperclip'));
		}
		else
		{

			define('ATTACHMENT_ICON', "<img src='" . e_PLUGIN . "pm/images/attach.png' alt='' />");
		}
	}

	if(deftrue('BOOTSTRAP') && deftrue('FONTAWESOME'))
	{
		define('PM_DELETE_ICON', $tp->toGlyph('fa-trash', 'fw=1'));
	}
	else
	{
		define("PM_DELETE_ICON", "<img src='" . e_PLUGIN_ABS . "pm/images/mail_delete.png'  alt='" . LAN_DELETE . "' class='icon S16' />");
	}

	$qs = explode('.', e_QUERY);
	$action = varset($qs[0], 'inbox');
	if(!$action)
	{
		$action = 'inbox';
	}

	if(!empty($_GET['mode']))
	{
		$action = $tp->filter($_GET['mode']);
	}
	/*
	if($action == 'textarea' || $action == 'input')
	{
		if($qs[1] == 'pm_to') {
			require_once(e_HANDLER.'user_select_class.php');
			$us = new user_select;
			$us->popup();
			exit;
		}
	}*/

	$pm_proc_id = intval(varset($qs[1], 0));

	//$pm_prefs = $sysprefs->getArray('pm_prefs');

	$pm_prefs = e107::getPlugPref('pm');


	$pm_prefs['perpage'] = intval($pm_prefs['perpage']);
	if($pm_prefs['perpage'] == 0)
	{
		$pm_prefs['perpage'] = 10;
	}

	if(!isset($pm_prefs['pm_class']) || !check_class($pm_prefs['pm_class']))
	{
		require_once(HEADERF);
		$ns->tablerender(LAN_PM, LAN_PM_12);
		require_once(FOOTERF);
		exit;
	}

	//setScVar('pm_handler_shortcodes','pmPrefs', $pm_prefs);
	$pmManager = new pmbox_manager($pm_prefs);


	//setScVar('pm_handler_shortcodes','pmManager', &$pmManager);


	class pm_extended extends private_message
	{

		protected $pmManager = null;

		/**
		 *    Constructor
		 *
		 * @param array $prefs - pref settings for PM plugin
		 * @return none
		 */
		public function __construct($prefs, $manager)
		{
			$this->pmManager = $manager;
			parent::__construct($prefs);
		}


		/**
		 *    Show the 'Send to' form
		 * @param array|int $to_uid - a PM block of message to reply to, or UID of user to send to
		 *
		 * @return string text for display
		 */
		function show_send($to_uid)
		{
			$pm_info = array();
			$pm_outbox = $this->pmManager->pm_getInfo('outbox');

			if(is_array($to_uid))
			{
				$pm_info = $to_uid;        // We've been passed a 'reply to' PM
				$to_uid = $pm_info['pm_from'];
			}


			if(!empty($to_uid))
			{

				if($this->canSendTo($to_uid) == false)
				{
					return "<div class='alert alert-danger'>" . LAN_PM_114 . "</div>";// sending to this user is not permitted.
				}

				$sql2 = e107::getDb('sql2');
				if($sql2->select('user', 'user_name', 'user_id = ' . intval($to_uid))) //TODO add a check for userclass.
				{
					$row = $sql2->fetch();
					$pm_info['from_name'] = $row['user_name'];
					$pm_info['pm_from'] = intval($to_uid);
				}
				else
				{
					return "<div class='alert alert-danger'>" . LAN_PM_115 . "</div>";
				}

			}

			//echo "Show_send: {$to_uid} from {$pm_info['from_name']} is happening<br />";

			if($pm_outbox['outbox']['filled'] >= 100)
			{
				return str_replace('{PERCENT}', $pm_outbox['outbox']['filled'], LAN_PM_13);
			}

			//		$tpl_file = THEME.'pm_template.php';
//		include_once(is_readable($tpl_file) ? $tpl_file : e_PLUGIN.'pm/pm_template.php');

			if(THEME_LEGACY === true)
			{
				include_once(THEME . 'pm_template.php');
				$PM_SEND_PM = $this->updateTemplate($PM_SEND_PM);
			}

			if(empty($PM_SEND_PM))
			{
				$PM_SEND_PM = e107::getTemplate('pm', 'pm', 'send');
			}

			$enc = (check_class($this->pmPrefs['attach_class']) ? "enctype='multipart/form-data'" : '');
			//	setScVar('pm_handler_shortcodes','pmInfo', $pm_info);

			$sc = e107::getScBatch('pm', true, 'pm');
			$sc->setVars($pm_info);
			$sc->wrapper('pm');

//		$PM_SEND_PM = $this->updateTemplate($PM_SEND_PM);

			$text = "<form {$enc} method='post' action='" . e_REQUEST_SELF . "' id='dataform'>
		<div><input type='hidden' name='numsent' value='{$pm_outbox['outbox']['total']}' />" .
				e107::getParser()->parseTemplate($PM_SEND_PM, true, $sc) .
				'</div></form>';

			return $text;
		}


		/**
		 *    Show inbox
		 * @param int $start - offset into list
		 *
		 * @return string text for display
		 */

		function show_inbox($start = 0)
		{
			$tp = e107::getParser();

//		$tpl_file = THEME.'pm_template.php';
//		include(is_readable($tpl_file) ? $tpl_file : e_PLUGIN.'pm/pm_template.php');
			if(THEME_LEGACY === true)
			{
				include_once(THEME . 'pm_template.php');

				// Is updateTemplate really necessary for v2.x templates?
				$PM_INBOX = array();
				$PM_INBOX['start'] = $this->updateTemplate($PM_INBOX_HEADER);
				$PM_INBOX['item'] = $this->updateTemplate($PM_INBOX_TABLE);
				$PM_INBOX['empty'] = $this->updateTemplate($PM_INBOX_EMPTY);
				$PM_INBOX['end'] = $this->updateTemplate($PM_INBOX_FOOTER);
			}



			if(empty($PM_INBOX['item']))
			{
				$PM_INBOX = e107::getTemplate('pm', 'pm', 'inbox');
			}

			$pm_blocks = $this->block_get();
			$pmlist = $this->pm_get_inbox(USERID, $start, $this->pmPrefs['perpage']);

			//	setScVar('pm_handler_shortcodes', 'pmNextPrev', array('start' => $start, 'total' => $pmlist['total_messages']));

			$sc = e107::getScBatch('pm', true, 'pm');
			$sc->pmNextPrev = array('start' => $start, 'total' => $pmlist['total_messages']);
			$sc->wrapper('pm');

// Is updateTemplate really necessary for v2.x templates?
			/*
					$PM_INBOX_HEADER = $this->updateTemplate($PM_INBOX['start']);
					$PM_INBOX_TABLE = $this->updateTemplate($PM_INBOX['item']);
					$PM_INBOX_EMPTY = $this->updateTemplate($PM_INBOX['empty']);
					$PM_INBOX_FOOTER = $this->updateTemplate($PM_INBOX['end']);
			*/
			$txt = "<form method='post' action='" . e_REQUEST_SELF . "?" . e_QUERY . "'>";
			$txt .= $tp->parseTemplate($PM_INBOX['start'], true, $sc);

			if($pmlist['total_messages'])
			{
				foreach($pmlist['messages'] as $rec)
				{
					if(trim($rec['pm_subject']) == '')
					{
						$rec['pm_subject'] = '[' . LAN_PM_61 . ']';
					}

					$sc->setVars($rec);
					$txt .= $tp->parseTemplate($PM_INBOX['item'], true, $sc);
				}
			}
			else
			{
				$txt .= $tp->parseTemplate($PM_INBOX['empty'], true, $sc);
			}

			$txt .= $tp->parseTemplate($PM_INBOX['end'], true, $sc);
			$txt .= "</form>";


			return $txt;
		}


		/**
		 *    Show outbox
		 * @param int $start - offset into list
		 *
		 * @return string text for display
		 */
		function show_outbox($start = 0)
		{
			$tp = e107::getParser();

//		$tpl_file = THEME.'pm_template.php';
//		include(is_readable($tpl_file) ? $tpl_file : e_PLUGIN.'pm/pm_template.php');
			if(THEME_LEGACY === true)
			{
				include_once(THEME . 'pm_template.php');

				// Is updateTemplate really necessary for v2.x templates?
				$PM_OUTBOX = array();
				$PM_OUTBOX['start'] = $this->updateTemplate($PM_OUTBOX_HEADER);
				$PM_OUTBOX['item'] = $this->updateTemplate($PM_OUTBOX_TABLE);
				$PM_OUTBOX['empty'] = $this->updateTemplate($PM_OUTBOX_EMPTY);
				$PM_OUTBOX['end'] = $this->updateTemplate($PM_OUTBOX_FOOTER);
			}

			if(empty($PM_OUTBOX['item']))
			{
				$PM_OUTBOX = e107::getTemplate('pm', 'pm', 'outbox');
			}

			$pmlist = $this->pm_get_outbox(USERID, $start, $this->pmPrefs['perpage']);
			//	setScVar('pm_handler_shortcodes', 'pmNextPrev', array('start' => $start, 'total' => $pmlist['total_messages']));

			$sc = e107::getScBatch('pm', true, 'pm');
			$sc->pmNextPrev = array('start' => $start, 'total' => $pmlist['total_messages']);
			$sc->wrapper('pm');

// Is updateTemplate really necessary for v2.x templates?
			/*
					$PM_OUTBOX_HEADER = $this->updateTemplate($PM_OUTBOX['start']);
					$PM_OUTBOX_TABLE = $this->updateTemplate($PM_OUTBOX['item']);
					$PM_OUTBOX_EMPTY = $this->updateTemplate($PM_OUTBOX['empty']);
					$PM_OUTBOX_FOOTER = $this->updateTemplate($PM_OUTBOX['end']);
			*/
			$txt = "<form method='post' action='" . e_REQUEST_SELF . "?" . e_QUERY . "'>";
			$txt .= $tp->parseTemplate($PM_OUTBOX['start'], true, $sc);

			if($pmlist['total_messages'])
			{
				foreach($pmlist['messages'] as $rec)
				{
					if(trim($rec['pm_subject']) == '')
					{
						$rec['pm_subject'] = '[' . LAN_PM_61 . ']';
					}
					//	setScVar('pm_handler_shortcodes','pmInfo', $rec);
					$sc->setVars($rec);
					$txt .= $tp->parseTemplate($PM_OUTBOX['item'], true, $sc);
				}
			}
			else
			{
				$txt .= $tp->parseTemplate($PM_OUTBOX['empty'], true, $sc);
			}
			$txt .= $tp->parseTemplate($PM_OUTBOX['end'], true, $sc);
			$txt .= '</form>';

			return $txt;
		}


		/**
		 *    Show details of a pm
		 * @param int $pmid - DB ID for PM
		 * @param string $comeFrom - inbox|outbox - determines whether inbox or outbox is shown after PM
		 *
		 * @return string text for display
		 */
		function show_pm($pmid, $comeFrom = '')
		{
			$ns = e107::getRender();

//		$tpl_file = THEME.'pm_template.php';
//		include_once(is_readable($tpl_file) ? $tpl_file : e_PLUGIN.'pm/pm_template.php');
			if(THEME_LEGACY === true)
			{
				$PM_SHOW = null;
				include_once(THEME . 'pm_template.php');
				$PM_SHOW = $this->updateTemplate($PM_SHOW);
			}

			if(empty($PM_SHOW))
			{
				$PM_SHOW = e107::getTemplate('pm', 'pm', 'show');
			}

			$pm_info = $this->pm_get($pmid);

			$sc = e107::getScBatch('pm', true, 'pm');
			$sc->setVars($pm_info);
			$sc->wrapper('pm');

			if($pm_info['pm_to'] != USERID && $pm_info['pm_from'] != USERID)
			{
				$ns->tablerender(LAN_PM, LAN_PM_60);
				return null;
			}

			if($pm_info['pm_read'] == 0 && $pm_info['pm_to'] == USERID)
			{    // Inbox
				$now = time();
				$pm_info['pm_read'] = $now;
				$this->pm_mark_read($pmid, $pm_info);
			}

			$sc->pmMode = $comeFrom;

//		$PM_SHOW = $this->updateTemplate($PM_SHOW);

			$txt = e107::getParser()->parseTemplate($PM_SHOW, true, $sc);

			if($comeFrom == 'outbox')
			{
				$bread = array('text' => LAN_PLUGIN_PM_OUTBOX, 'url' => e107::url('pm', 'index') . '?mode=outbox');
			}
			else
			{
				$bread = array('text' => LAN_PLUGIN_PM_INBOX, 'url' => e107::url('pm', 'index') . '?mode=inbox');
			}

			$ns->tablerender(LAN_PM, $this->breadcrumb($bread, '#' . $pmid) . $txt);

			if(!$comeFrom)
			{
				if($pm_info['pm_from'] == USERID)
				{
					$comeFrom = 'outbox';
				}
			}

			// Need to show inbox or outbox from start
			if($comeFrom == 'outbox')
			{    // Show Outbox

				$caption = '';
				if(!deftrue('BOOTSTRAP'))
				{
					$caption .= LAN_PM . " - " . LAN_PLUGIN_PM_OUTBOX;
				}

				$ns->tablerender($caption, $this->show_outbox(), 'PM');
			}
			else
			{    // Show Inbox
				$caption = '';
				if(!deftrue('BOOTSTRAP'))
				{
					$caption .= LAN_PM . " - " . LAN_PLUGIN_PM_INBOX;
				}

				$ns->tablerender($caption, $this->show_inbox(), 'PM');
			}
		}


		/**
		 *    Show list of blocked users
		 * @param int $start - not used at present; offset into list
		 *
		 * @return string text for display
		 */
		public function showBlocked($start = 0)
		{
			$tp = e107::getParser();
//		$tpl_file = THEME.'pm_template.php';
//		include(is_readable($tpl_file) ? $tpl_file : e_PLUGIN.'pm/pm_template.php');
			if(THEME_LEGACY === true)
			{
				include_once(THEME . 'pm_template.php');

				// Is updateTemplate really necessary for v2.x templates?
				$PM_BLOCKED = array();
				$PM_BLOCKED['start'] = $this->updateTemplate($PM_BLOCKED_HEADER);
				$PM_BLOCKED['item'] = $this->updateTemplate($PM_BLOCKED_TABLE);
				$PM_BLOCKED['empty'] = $this->updateTemplate($PM_BLOCKED_EMPTY);
				$PM_BLOCKED['end'] = $this->updateTemplate($PM_BLOCKED_FOOTER);
			}

			if(empty($PM_BLOCKED))
			{
				$PM_BLOCKED = e107::getTemplate('pm', 'pm', 'blocked');
			}

			$pmBlocks = $this->block_get_user();            // TODO - handle pagination, maybe (is it likely to be necessary?)

			$sc = e107::getScBatch('pm', true, 'pm');
			$sc->pmBlocks = $pmBlocks;
			$sc->wrapper('pm');

// Is updateTemplate really necessary for v2.x templates?
			/*
					$PM_BLOCKED_HEADER = $this->updateTemplate($PM_BLOCKED['start']);
					$PM_BLOCKED_TABLE  = $this->updateTemplate($PM_BLOCKED['item']);
					$PM_BLOCKED_EMPTY  = $this->updateTemplate($PM_BLOCKED['empty']);
					$PM_BLOCKED_FOOTER  = $this->updateTemplate($PM_BLOCKED['end']);
			*/

			$txt = "<form method='post' action='" . e_REQUEST_SELF . "?" . e_QUERY . "'>";
			$txt .= $tp->parseTemplate($PM_BLOCKED['start'], true, $sc);

			if($pmTotalBlocked = count($pmBlocks))
			{
				foreach($pmBlocks as $pmBlocked)
				{
					$sc->pmBlocked = $pmBlocked;
					//	setScVar('pm_handler_shortcodes','pmBlocked', $pmBlocked);
					$txt .= $tp->parseTemplate($PM_BLOCKED['item'], true, $sc);
				}
			}
			else
			{
				$txt .= $tp->parseTemplate($PM_BLOCKED['empty'], true, $sc);
			}
			$txt .= $tp->parseTemplate($PM_BLOCKED['end'], true, $sc);
			$txt .= '</form>';

			return $txt;
		}


		/**
		 *    Send a PM based on $_POST parameters
		 *
		 * @return string text for display
		 */
		function post_pm()
		{
			// print_a($_POST);

			if(!check_class($this->pmPrefs['pm_class']))
			{
				return LAN_PM_12;
			}

			$pm_info = $this->pmManager->pm_getInfo('outbox');

			if($pm_info['outbox']['total'] != $_POST['numsent'])
			{
				return LAN_PM_14;
			}

			if(isset($_POST['pm_userclass']) && ($_POST['pm_userclass'] == e_UC_NOBODY))
			{
				$_POST['pm_userclass'] = false;
			}

			if(isset($_POST['user']))
			{
				$_POST['pm_to'] = $_POST['user'];
			}

			if(isset($_POST['pm_to']))
			{
				$msg = '';
				if(!empty($_POST['pm_userclass']))
				{
					if(!check_class($this->pmPrefs['opt_userclass']))
					{
						return LAN_PM_15;
					}
					elseif((!check_class($_POST['pm_userclass']) || !check_class($this->pmPrefs['multi_class'])) && !ADMIN)
					{
						return LAN_PM_16;
					}
				}
				else
				{
					$to_array = explode(",", str_replace(" ", "", $_POST['pm_to']));
					$to_array = array_unique($to_array);

					if(count($to_array) == 1)
					{
						$_POST['pm_to'] = $to_array[0];
					}

					if(check_class($this->pmPrefs['multi_class']) && count($to_array) > 1)
					{
						foreach($to_array as $to)
						{
							if($to_info = $this->pm_getuid($to))
							{    // Check whether sender is blocked - if so, add one to count
								if(!e107::getDb()->update('private_msg_block', "pm_block_count=pm_block_count+1 WHERE pm_block_from = '" . USERID . "' AND pm_block_to = '" . e107::getParser()->toDB($to) . "'"))
								{
									$_POST['to_array'][] = $to_info;
								}
							}
						}
					}
					else
					{
						if($to_info = $this->pm_getuid($_POST['pm_to']))
						{
							$_POST['to_info'] = $to_info;
						}
						else
						{
							return LAN_PM_17;
						}

						if(e107::getDb()->update('private_msg_block', "pm_block_count=pm_block_count+1 WHERE pm_block_from = '" . USERID . "' AND pm_block_to = '{$to_info['user_id']}'"))
						{
							return LAN_PM_18 . $to_info['user_name'];
						}
					}
				}

				if(isset($_POST['receipt']))
				{
					if(!check_class($this->pmPrefs['receipt_class']))
					{
						unset($_POST['receipt']);
					}
				}

				$totalsize = strlen($_POST['pm_message']);

				$maxsize = intval($this->pmPrefs['attach_size']) * 1024;

				foreach(array_keys($_FILES['file_userfile']['size']) as $fid)
				{
					if($maxsize > 0 && $_FILES['file_userfile']['size'][$fid] > $maxsize)
					{
						$msg .= str_replace("{FILENAME}", $_FILES['file_userfile']['name'][$fid], LAN_PM_62) . "<br />";
						$_FILES['file_userfile']['size'][$fid] = 0;
					}
					$totalsize += $_FILES['file_userfile']['size'][$fid];
				}

				if(intval($this->pmPrefs['pm_limits']) > 0)
				{
					if($this->pmPrefs['pm_limits'] == '1')
					{
						if($pm_info['outbox']['total'] == $pm_info['outbox']['limit'])
						{
							return LAN_PM_19;
						}
					}
					else
					{
						if($pm_info['outbox']['size'] + $totalsize > $pm_info['outbox']['limit'])
						{
							return LAN_PM_21;
						}
					}
				}


				if(!empty($_POST['uploaded']))
				{
					if(check_class($this->pmPrefs['attach_class']))
					{
						$_POST['uploaded'] = $this->processAttachments();

						foreach($_POST['uploaded'] as $var)
						{
							if(!empty($var['message']))
							{
								$msg .= $var['message'] . "<br />";
							}

						}
					}
					else
					{
						$msg .= LAN_PM_23 . '<br />';
						unset($_POST['uploaded']);

					}
				}

				$_POST['from_id'] = USERID;

				return $msg . $this->add($_POST);
			}
		}


		function processAttachments()
		{
			$randnum = rand(1000, 9999);
			$type = 'attachment+' . $randnum . '_';

			return e107::getFile()->getUploaded("attachments", $type, array('max_file_count' => 3));
		}


		function breadcrumb($type = '', $other='')
		{
			if(!deftrue('BOOTSTRAP'))
			{
				return null;
			}

			$array = array();
			$array[0] = array('text' => LAN_PM, 'url' => e107::url('pm', 'index'));

			if(is_string($type))
			{
				$array[1] = array('text' => $type, 'url' => null);
			}
			elseif(is_array($type))
			{
				$array[1] = $type;
			}

			if(!empty($other))
			{
				$array[2] = array('text' => $other, 'url' => null);
			}

			e107::breadcrumb($array);

			return e107::getForm()->breadcrumb($array);

		}

	}


	/**
	 *    Look up users matching a keyword, output a list of those found
	 *    Direct echo
	 */
	function pm_user_lookup()
	{
		$sql = e107::getDb();

		$tp = e107::getParser();

		$query = "SELECT * FROM #user WHERE user_name REGEXP '^" . $tp->filter($_POST['keyword'], 'w') . "' ";
		if($sql->gen($query))
		{
			echo '[';
			while($row = $sql->fetch())
			{
				$u[] = "{\"caption\":\"" . $row['user_name'] . "\",\"value\":" . $row['user_id'] . "}";
			}

			echo implode(",", $u);
			echo ']';
		}
		exit;
	}


	//$pm =& new private_message;
	$pm = new pm_extended($pm_prefs, $pmManager);

	$message = '';
	$pmSource = '';
	if(isset($_POST['pm_come_from']))
	{
		$pmSource = $tp->toDB($_POST['pm_come_from']);
	}
	elseif(isset($qs[2]))
	{
		$pmSource = $tp->toDB($qs[2]);
	}


	//Auto-delete message, if timeout set in admin
	$del_qry = array();
	$read_timeout = intval($pm_prefs['read_timeout']);
	$unread_timeout = intval($pm_prefs['unread_timeout']);
	if($read_timeout > 0)
	{
		$timeout = time() - ($read_timeout * 86400);
		$del_qry[] = "(pm_sent < {$timeout} AND pm_read > 0)";
	}
	if($unread_timeout > 0)
	{
		$timeout = time() - ($unread_timeout * 86400);
		$del_qry[] = "(pm_sent < {$timeout} AND pm_read = 0)";
	}
	if(count($del_qry) > 0)
	{
		$qry = implode(' OR ', $del_qry) . ' AND (pm_from = ' . USERID . ' OR pm_to = ' . USERID . ')';
		if($sql->select('private_msg', 'pm_id', $qry))
		{
			$delList = $sql->db_getList();
			foreach($delList as $p)
			{
				$pm->del($p['pm_id'], true);
			}
		}
	}


	if('del' == $action || isset($_POST['pm_delete_selected']))
	{
		if(isset($_POST['pm_delete_selected']))
		{
			foreach(array_keys($_POST['selected_pm']) as $id)
			{
				$message .= LAN_PM_24 . ": {$id} <br />";
				$message .= $pm->del($id);
			}
		}
		if('del' == $action)
		{
			$message = $pm->del($pm_proc_id);
		}
		if($pmSource)
		{
			$action = $pmSource;
		}
		else
		{
			if(substr($_SERVER['HTTP_REFERER'], -5) == 'inbox')
			{
				$action = 'inbox';
			}
			elseif(substr($_SERVER['HTTP_REFERER'], -6) == 'outbox')
			{
				$action = 'outbox';
			}
		}
		$pm_proc_id = 0;
		unset($qs);
	}


	if('delblocked' == $action || isset($_POST['pm_delete_blocked_selected']))
	{
		if(isset($_POST['pm_delete_blocked_selected']))
		{
			foreach(array_keys($_POST['selected_pm']) as $id)
			{
				$message .= LAN_PM_70 . ": {$id} <br />";
				$message .= $pm->block_del($id) . '<br />';
			}
		}
		elseif('delblocked' == $action)
		{
			$message = $pm->block_del($pm_proc_id);
		}
		$action = 'blocked';
		$pm_proc_id = 0;
		unset($qs);
	}


	if('block' == $action)
	{
		$message = $pm->block_add($pm_proc_id);
		$action = 'inbox';
		$pm_proc_id = 0;
	}


	if('unblock' == $action)
	{
		$message = $pm->block_del($pm_proc_id);
		$action = 'inbox';
		$pm_proc_id = 0;
	}


	if('get' == $action)
	{
		$pm->send_file($pm_proc_id, intval($qs[2]));
		exit;
	}


	require_once(HEADERF);

	if(isset($_POST['postpm']))
	{
		$message = $pm->post_pm();
		$action = 'outbox';
	}

	$mes = e107::getMessage();

	if($message != '')
	{
		$mes->add($message);
//	$ns->tablerender('', "<div class='alert alert-block'>". $message."</div>");
	}


	//-----------------------------------------
	//			DISPLAY TASKS
	//-----------------------------------------
	switch($action)
	{
		case 'send' :
			$ns->tablerender(LAN_PM, $pm->breadcrumb(LAN_PLUGIN_PM_NEW) . $mes->render() . $pm->show_send($pm_proc_id));
			break;

		case 'reply' :
			$pmid = $pm_proc_id;
			
			if($pm_info = $pm->pm_get($pmid))
			{
				if($pm_info['pm_to'] != USERID)
				{
					$ns->tablerender(LAN_PM, $pm->breadcrumb(LAN_PM_55) . $mes->render() . LAN_PM_56);
				}
				else
				{
					$ns->tablerender(LAN_PM, $pm->breadcrumb(LAN_PM_55) . $mes->render() . $pm->show_send($pm_info));
				}
			}
			else
			{
				$ns->tablerender(LAN_PM, $pm->breadcrumb(LAN_PM_55) . $mes->render() . LAN_PM_57);
			}
			break;

		case 'inbox' :
			$caption = LAN_PM;

			if(THEME_LEGACY === true)
			{
				$caption .= ' - ' . LAN_PLUGIN_PM_INBOX;
			}

			$ns->tablerender($caption, $pm->breadcrumb(LAN_PLUGIN_PM_INBOX) . $mes->render() . $pm->show_inbox($pm_proc_id), 'PM');
			break;

		case 'outbox' :
			$caption = LAN_PM;

			if(THEME_LEGACY === true)
			{
				$caption .= ' - ' . LAN_PLUGIN_PM_OUTBOX;
			}

			$ns->tablerender($caption, $pm->breadcrumb(LAN_PLUGIN_PM_OUTBOX) . $mes->render() . $pm->show_outbox($pm_proc_id), 'PM');
			break;

		case 'show' :

			$pm->show_pm($pm_proc_id, $pmSource);
			break;

		case 'blocked' :
			$caption = LAN_PM;

			if(THEME_LEGACY === true)
			{
				$caption .= ' - ' . LAN_PM_66;
			}

			$ns->tablerender($caption, $pm->breadcrumb('blocked') . $mes->render() . $pm->showBlocked($pm_proc_id), 'PM');
			break;
	}


	require_once(FOOTERF);
	exit;
?>
