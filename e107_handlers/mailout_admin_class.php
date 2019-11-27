<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Mailout - admin-related functions
 *
 */

/**
 *	Various admin-related mailout functions, mostly to do with creating and
 * handling forms.
 *
 *	@package     e107
 *	@subpackage	e107_handlers
 */

/*
 TODO:
 1. Use API to downloads plugin to get available files (when available)
 2. Fuller checking prior to send
 3. May want more control over date display format
 4. Use new date picker
 */

if(!defined('e107_INIT'))
{
	exit ;
}

define('MAIL_ADMIN_DEBUG', TRUE);

require_once (e_HANDLER . 'mail_manager_class.php');

class mailoutAdminClass extends e107MailManager
{
	public $_cal = array();
	protected $mode;
	// So we know what the current task is
	protected $mailHandlers = array();
	protected $showFrom = 0;
	protected $showCount = 10;
	protected $sortField = 'mail_source_id';
	protected $sortOrder = 'asc';
	protected $fieldPref = array();
	protected $userCache = array();

	// Definitions associated with each column which might be displayed. (Compatible
	// with forms-based display)
	// Fields are displayed in the order listed.
	// Can also have:	width
	//					type
	//					thclass
	protected $fields = array(
		'mail_recipients' => array(
			'mail_target_id' => array(
				'title' => LAN_MAILOUT_143,
				'thclass' => 'center',
				'forced' => TRUE
			),
			'mail_recipient_id' => array(
				'title' => LAN_MAILOUT_142,
				'thclass' => 'center'
			),
			'mail_recipient_name' => array(
				'title' => LAN_MAILOUT_141,
				'forced' => TRUE
			),
			'mail_recipient_email' => array(
				'title' => LAN_MAILOUT_140,
				'thclass' => 'left',
				'forced' => TRUE
			),
			'mail_status' => array(
				'title' => LAN_MAILOUT_138,
				'thclass' => 'center',
				'proc' => 'contentstatus'
			),
			'mail_detail_id' => array('title' => LAN_MAILOUT_137),
			'mail_send_date' => array(
				'title' => LAN_MAILOUT_139,
				'proc' => 'sdatetime'
			),
			'mail_target_info' => array(
				'title' => LAN_MAILOUT_148,
				'proc' => 'array'
			),
			'options' => array(
				'title' => LAN_OPTIONS,
				'forced' => TRUE
			)
		),
		'mail_content' => array(
			'mail_source_id' => array(
				'title' => LAN_MAILOUT_137,
				'thclass' => 'center',
				'forced' => TRUE
			),
			'mail_title' => array(
				'title' => LAN_TITLE,
				'forced' => TRUE
			),
			'mail_subject' => array(
				'title' => LAN_MAILOUT_06,
				'forced' => TRUE
			),
			'mail_content_status' => array(
				'title' => LAN_MAILOUT_136,
				'thclass' => 'center',
				'proc' => 'contentstatus'
			),
			'mail_togo_count' => array('title' => LAN_MAILOUT_83),
			'mail_sent_count' => array('title' => LAN_MAILOUT_82),
			'mail_fail_count' => array('title' => LAN_MAILOUT_128),
			'mail_bounce_count' => array('title' => LAN_MAILOUT_144),
			'mail_start_send' => array(
				'title' => LAN_MAILOUT_131,
				'proc' => 'sdatetime'
			),
			'mail_end_send' => array(
				'title' => LAN_MAILOUT_132,
				'proc' => 'sdatetime'
			),
			'mail_create_date' => array(
				'title' => LAN_MAILOUT_130,
				'proc' => 'sdatetime'
			),
			'mail_creator' => array(
				'title' => LAN_MAILOUT_85,
				'proc' => 'username'
			),
			'mail_create_app' => array('title' => LAN_MAILOUT_133),
			'mail_e107_priority' => array('title' => LAN_MAILOUT_134),
			'mail_notify_complete' => array(
				'title' => LAN_MAILOUT_243,
				'nolist' => 'TRUE'
			),
			'mail_last_date' => array(
				'title' => LAN_MAILOUT_129,
				'proc' => 'sdatetime'
			),
			'mail_body' => array(
				'title' => LAN_MAILOUT_100,
				'proc' => 'trunc200'
			),
			'mail_body_templated' => array(
				'title' => LAN_MAILOUT_257,
				'proc' => 'chars'
			),
			//	'mail_other' = array('title' => LAN_MAILOUT_84),
			'mail_sender_email' => array('title' => LAN_MAILOUT_149),
			'mail_sender_name' => array('title' => LAN_MAILOUT_150),
			'mail_copy_to' => array('title' => LAN_MAILOUT_151),
			'mail_bcopy_to' => array('title' => LAN_MAILOUT_152),
			'mail_attach' => array('title' => LAN_MAILOUT_153),
			'mail_send_style' => array('title' => LAN_MAILOUT_154),
			'mail_selectors' => array(
				'title' => LAN_MAILOUT_155,
				'proc' => 'selectors',
				'nolist' => 'TRUE'
			),
			'mail_include_images' => array(
				'title' => LAN_MAILOUT_224,
				'proc' => 'yesno'
			),
			'options' => array(
				'title' => LAN_OPTIONS,
				'forced' => TRUE
			)
		)
	);

	// List of fields to be hidden for each action ('nolist' attribute true)
	protected $hideFields = array(
		'orphans' => array(),
		'saved' => 'mail_content_status,mail_togo_count,mail_sent_count,mail_fail_count,mail_bounce_count,mail_start_send,mail_end_send,mail_e107_priority,mail_notify_complete,mail_last_date,mail_selectors',
		'sent' => 'mail_togo_count,mail_last_date,mail_selectors,mail_notify_complete',
		//		'pending'  =>
		// 'mail_togo_count,mail_sent_count,mail_fail_count,mail_bounce_count,mail_start_send,mail_end_send,mail_e107_priority,mail_last_date,mail_selectors',
		'pending' => 'mail_start_send,mail_end_send,mail_e107_priority,mail_notify_complete,mail_last_date,mail_selectors',
		'held' => 'mail_sent_count,mail_fail_count,mail_bounce_count,mail_start_send,mail_end_send,mail_e107_priority,mail_notify_complete,mail_last_date,mail_selectors',
		'resend' => 'mail_Selectors,mail_notify_complete',
		'recipients' => 'mail_detail_id'
	);

	// Array of info associated with each task we might do
	protected $tasks = array(
		'makemail' => array(
			'title' => LAN_MAILOUT_190,
			'defaultSort' => '',
			'defaultTable' => ''
		),
		'saved' => array(
			'title' => LAN_MAILOUT_191,
			'defaultSort' => 'mail_source_id',
			'defaultTable' => 'mail_content'
		),
		'marksend' => array(
			'title' => 'Internal: marksend',
			'defaultSort' => 'mail_source_id',
			'defaultTable' => 'mail_content'
		),
		'sent' => array(
			'title' => LAN_MAILOUT_192,
			'defaultSort' => 'mail_source_id',
			'defaultTable' => 'mail_content'
		),
		'pending' => array(
			'title' => LAN_MAILOUT_193,
			'defaultSort' => 'mail_source_id',
			'defaultTable' => 'mail_content'
		),
		'held' => array(
			'title' => LAN_MAILOUT_194,
			'defaultSort' => 'mail_source_id',
			'defaultTable' => 'mail_content'
		),
		'recipients' => array(
			'title' => LAN_MAILOUT_173,
			'defaultSort' => 'mail_recipient_email',
			'defaultTable' => 'mail_recipients'
		),
		'mailtargets' => array(
			'title' => LAN_MAILOUT_173,
			'defaultSort' => 'mail_recipient_email',
			'defaultTable' => 'mail_recipients'
		),
		'prefs' => array(
			'title' => ADLAN_40,
			'defaultSort' => '',
			'defaultTable' => ''
		),
		'maint' => array(
			'title' => ADLAN_40,
			'defaultSort' => '',
			'defaultTable' => ''
		)
	);

	// Options for mail listing dropdown - actions apertaining to a stored email
	protected $modeOptions = array(
		'saved' => array(
			'mailedit' => LAN_MAILOUT_163,
			'maildelete' => LAN_DELETE,
			'mailshowtemplate' => LAN_MAILOUT_254
		),
		'pending' => array(
			'mailsendimmediately' => "Send Immediately",
			'mailhold' => LAN_MAILOUT_159,
			'mailcancel' => LAN_MAILOUT_160,
			'mailtargets' => LAN_MAILOUT_181
		),
		'held' => array(
			'mailsendnow' => LAN_MAILOUT_158,
			'mailcancel' => LAN_MAILOUT_160,
			'mailtargets' => LAN_MAILOUT_181
		),
		'sent' => array(
			'mailcopy' => LAN_MAILOUT_251,
			'maildelete' => LAN_DELETE,
			'mailtargets' => LAN_MAILOUT_181
		),
		'recipients' => array('mailonedelete' => LAN_DELETE)
	);

	// List of fields to be included in email display for various options
	protected $mailDetailDisplay = array(
		'basic' => array(
			'mail_source_id' => 1,
			'mail_title' => 1,
			'mail_subject' => 1,
			'mail_body' => 200
		),
		'send' => array(
			'mail_source_id' => 1,
			'mail_title' => 1,
			'mail_subject' => 1,
			'mail_body' => 500,
			'mail_send_style' => 1
		),
		'template' => array(
			'mail_source_id' => 1,
			'mail_title' => 1,
			'mail_subject' => 1,
			'mail_body' => 200,
			'mail_body_templated' => 'chars'
		),
	);

	/**
	 * Constructor
	 *
	 *
	 * @return void
	 */
	public function __construct($mode = '')
	{
		parent::__construct();

		$dbTable = '';
		if(isset($this->tasks[$mode]))
		{
			$dbTable = $this->tasks[$mode]['defaultTable'];
		}
		if(isset($_GET['frm']))
		{
			$temp = intval($_GET['frm']);
			if($temp < 0)
				$temp = 0;
			$this->showFrom = $temp;
		}
		if(isset($_GET['count']))
		{
			$temp = min(intval($_GET['count']), 50);
			// Limit to 50 per page
			$temp = max($temp, 5);
			// ...and minimum 5 per page
			$this->showCount = $temp;
		}
		if(isset($_GET['fld']))
		{
			$temp = e107::getParser()->toDB($_GET['fld']);
			if(is_array($this->fields[$dbTable][$temp]))
			{
				$this->sortField = $temp;
			}
		}
		if(isset($_GET['asc']))
		{
			$temp = strtolower(e107::getParser()->toDB($_GET['asc']));
			if(($temp == 'asc') || ($temp == 'desc'))
			{
				$this->sortOrder = $temp;
			}
		}
		$this->newMode($mode);
	}

	/**
	 * Set up new mode
	 *
	 * @param $mode - display mode
	 * @return none
	 */
	public function newMode($mode = '')
	{
		global $user_pref;
		$this->mode = $mode;
		$curTable = $this->tasks[$this->mode]['defaultTable'];
		if($curTable)
		{
			if(isset($user_pref['admin_mailout_columns'][$mode]) && is_array($user_pref['admin_mailout_columns'][$mode]))
			{
				// Use saved list of fields to view if it exists
				$this->fieldPref = $user_pref['admin_mailout_columns'][$mode];
			}
			else
			{
				// Default list is minimal fields only
				$this->fieldPref = array();
				foreach($this->fields[$curTable] as $f => $v)
				{
					if(vartrue($v['forced']))
					{
						$this->fieldPref[] = $f;
					}
				}
			}
		}

		// Possibly the sort field needs changing
		if(!isset($this->fields[$curTable][$this->sortField]))
		{
			$this->sortField = $this->tasks[$mode]['defaultSort'];
		}

		// Now hide any fields that need to be for this mode
		if(isset($this->hideFields[$mode]))
		{
			$hideList = array_flip(explode(',', $this->hideFields[$mode]));
			foreach($this->fields[$curTable] as $f => $v)
			{
				$this->fields[$curTable][$f]['nolist'] = isset($hideList[$f]);
			}
			foreach($this->fieldPref as $k => $v)// Remove from list of active fields
			// (shouldn't often do anything)
			{
				if(isset($hideList[$v]))
				{
					unset($this->fieldPref[$k]);
				}
			}
		}
	}

	/**
	 * Calculate the list of fields (columns) to be displayed for a given mode
	 *
	 * @param string $mode - display mode
	 * @param boolean $noOptions - set TRUE to suppress inclusion of any 'options'
	 * column. FALSE to include 'options' (default)
	 * @return array of field definitions
	 */
	protected function calcFieldSpec($mode, $noOptions = FALSE)
	{
		if(!isset($this->tasks[$mode]))
		{
			echo "CalcfieldSpec({$mode}) - programming bungle<br />";
			return FALSE;
		}
		$ret = array();
		$curTable = $this->tasks[$this->mode]['defaultTable'];
		foreach($this->fields[$curTable] as $f => $v)
		{
			if((vartrue($v['forced']) && !vartrue($v['nolist'])) || in_array($f, $this->fieldPref))
			{
				if(($f != 'options') || ($noOptions === FALSE))
				{
					$ret[] = $f;
				}
			}
		}
		return $ret;
	}

	/**
	 * Save the column visibility prefs for this mode
	 *
	 * @param $target - display mode
	 * @return none
	 */
	/*
	 public function mailbodySaveColumnPref($target)
	 {
	 global $user_pref;
	 if (!$target) return;
	 if (!isset($this->tasks[$target]))
	 {
	 echo "Invalid prefs target: {$target}<br />";
	 return;
	 }
	 if (isset ($_POST['etrigger_ecolumns']))
	 {
	 $user_pref['admin_mailout_columns'][$target] = $_POST['e-columns'];
	 save_prefs('user');
	 $this->fieldPref = $user_pref['admin_mailout_columns'][$target];
	 }
	 }
	 */
	/**
	 *	Get the user name associated with a user ID.
	 *	The result is cached in case required again
	 *
	 *	@param int $uid - User ID
	 *
	 *	@return string with user name and user login name (UID if user not found)
	 */
	protected function getUserName($uid)
	{
		if(!isset($this->userCache[$uid]))
		{
			// Look up user
			$this->checkDB(2);
			// Make sure DB object created
			if($this->db2->select('user', 'user_name, user_loginname', 'user_id=' . intval($uid)))
			{
				$row = $this->db2->fetch();
				$this->userCache[$uid] = $row['user_name'] . ' (' . $row['user_loginname'] . ')';
			}
			else
			{
				$this->userCache[$uid] = 'UID: ' . $uid;
			}
		}
		return $this->userCache[$uid];
	}

	/**
	 * Generate the HTML for displaying actions box for emails
	 *
	 * Options given depend on $mode (saved|sent|pending|held), and also values in
	 * the email data.
	 *
	 * @param array $mailData - array of email-related info
	 * @return string HTML for display
	 */
	public function makeMailOptions($mode, $mailData)
	{
		if(!is_numeric($mailData['mail_source_id']) || ($mailData['mail_source_id'] == 0))
		{
			echo "makeMailOptions ({$mode}): Programming bungle!";
			print_a($mailData);
			return 'Error';
		}
		$text = "<select name='mailaction[{$mailData['mail_source_id']}]' onchange='this.form.submit()' class='tbox' style='width:90%'>\n
				<option selected='selected' value=''>&nbsp;</option>\n";
		foreach($this->modeOptions[$mode] as $key => $val)
		{
			$text .= "<option value='{$key}'>{$val}</option>\n";
		}
		$text .= "</select>\n";
		return $text;
	}

	/**
	 * Generate the HTML for displaying actions box for emails
	 *
	 * Options given depend on $mode, and also values in the email data.
	 *
	 * @param $mailData - array of email-related info
	 * @return HTML for display
	 */
	public function makeTargetOptions($mode, $targetData)
	{
		if(!is_numeric($targetData['mail_target_id']) || ($targetData['mail_target_id'] == 0))
		{
			echo "makeTargetOptions ({$mode}): Programming bungle!";
			print_a($targetData);
			return 'Error';
		}
		$text = "<select name='targetaction[{$targetData['mail_target_id']}]' onchange='this.form.submit()' class='tbox' style='width:90%'>\n
				<option selected='selected' value=''>&nbsp;</option>\n";
		foreach($this->modeOptions[$mode] as $key => $val)
		{
			$text .= "<option value='{$key}'>{$val}</option>\n";
		}
		$text .= "</select>\n";
		return $text;
	}

	/**
	 * Generate the HTML for displaying email selection fields
	 *
	 * @param $options - comma-separate string of handlers to load
	 *	'core' - core handler
	 *	plugin name - obvious!
	 *	'all' - obvious!
	 * @return Number of handlers loaded
	 */
	public function loadMailHandlers($options = 'all')
	{
		$pref = e107::getPref();

		$pref['mailout_enabled'] = str_replace('core','user',$pref['mailout_enabled']); // BC fix.

		$ret = 0;
		$toLoad = explode(',', $options);
		
		$active_mailers = explode(',', varset($pref['mailout_enabled'], 'user'));
		
		if((in_array('core', $toLoad) || ($options == 'all')) && in_array('core', $active_mailers))
		{
		//	require_once (e_HANDLER . 'mailout_class.php');
		//	$this->mailHandlers['core'] = new core_mailout; // Start by loading the core mailout class
		//	$ret++;
		}
		
		if(empty($pref['e_mailout_list']))
		{
			return $ret;
		}
		
		
		// Load additional configured handlers e_mailout.php from plugins. 
		foreach($pref['e_mailout_list'] as $mailer => $v)
		{
			
			if(isset($pref['plug_installed'][$mailer]) && in_array($mailer, $active_mailers) && (($options == 'all') || in_array($mailer, $toLoad)))
			{
				// Could potentially use this handler - its installed and enabled
				if(!is_readable(e_PLUGIN . $mailer . '/e_mailout.php'))
				{
					echo 'Invalid mailer selected: ' . $mailer . '<br />';
					exit ;
				}
				require_once (e_PLUGIN . $mailer . '/e_mailout.php');
				if(varset($mailerIncludeWithDefault, TRUE))
				{
					// Definitely need this plugin
					$mailClass = $mailer . '_mailout';
					$temp = new $mailClass;
				//	$temp = e107::getSingleton($mailClass);
					if($temp->mailerEnabled)
					{
						$this->mailHandlers[$mailer] = $temp;
						$ret++;
						if(varset($mailerExcludeDefault, FALSE) && isset($this->mailHandlers['core']))
						{
							$this->mailHandlers['core']->mailerEnabled = FALSE;
							// Don't need default (core) handler
							$ret--;
						}
					}
					else
					{
						unset($temp);
					}
				}
			}
		}

		return $ret;
	}

	/**
	 * Generate the HTML for displaying email selection fields
	 *
	 * @param $options - comma-separated string of areas to display:
	 *		plugins - selectors from any available plugins
	 *		cc - field for 'cc' options
	 *		bcc -  field for 'bcc' options
	 *		src=plugname - selector from the specified plugin
	 *		'all' - all available fields
	 * @return text for display
	 */
	public function emailSelector($options = 'all', $selectorInfo = FALSE)
	{
		$tabs = array();

		// Check for selected email address sources
		if(!$this->mailHandlers)
		{
			$text = "<span class='label label-warning'>".LAN_MAILOUT_259."</span>";
		}
	
		foreach($this->mailHandlers as $key => $m)
		{
			
			if($m->mailerEnabled)
			{
				
				$content = $m->showSelect(TRUE, varset($selectorInfo[$key], FALSE));

				if(is_array($content))
				{
					$text = "<table class='table table-bordered table-striped ' style='margin-bottom:0;margin-left:0; margin-top:10px'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					";

					foreach($content as $var)
					{
						$text .= "
						<tr>
							<td>" . $var['caption'] . "</td>
							<td class='form-inline'>" . $var['html'] . "</td>
						</tr>";
					}
					
					$text .= "</table>";

				}
				else
				{
					$text = $content;  //BC (0.8 only) but should be deprecated
				}

				$tabs[$key] = array('caption'=>$m->mailerName, 'text'=>$text);

			}
		}
		
		if(count($tabs) < 2) // no tabs if there's only 1 category. 
		{
			return $text;	
		}
		
		
		return e107::getForm()->tabs($tabs);
	}



	/**
	 * Get the selector details from each mail plugin (to add to mail data)
	 *
	 * @return array of selectors - key is the plugin name, value is the selector
	 * data (often itself an array)
	 */
	public function getAllSelectors()
	{
		$ret = array();
		foreach($this->mailHandlers as $key => $m)
		{
			if($m->mailerEnabled)
			{
				$ret[$key] = $m->returnSelectors();
			}
		}
		return $ret;
	}

	/**
	 * Creates a 'select' dropdown of userclasses, including the number of members in
	 * each class.
	 *
	 * @param string $name - name for <select>
	 * @param string $curSel - current select value
	 * @return text for display
	 *
	 *	@TODO: Doesn't give correct count for core classes where no data initialised
	 */
	public function userClassesTotals($name, $curSel)
	{
		$fixedClasses = array(
			'self' => LAN_MAILOUT_54,
			'all' => LAN_MAILOUT_12,
			'unverified' => LAN_MAILOUT_13,
			'admin' => LAN_MAILOUT_53
		);

		$ret = '';
		$this->checkDB(2);
		// Make sure DB object created
		$ret .= "<select class='tbox' name='{$name}' >
		<option value=''>&nbsp;</option>\n";

		foreach($fixedClasses as $k => $v)
		{
			$sel = ($k == $curSel)? " selected='selected'": '';
			$ret .= "<option value='{$k}'{$sel}>{$v}</option>\n";
		}
		$query = "SELECT uc.*, count(u.user_id) AS members
				FROM #userclass_classes AS uc
				LEFT JOIN #user AS u ON u.user_class REGEXP concat('(^|,)',uc.userclass_id,'(,|$)')
				WHERE NOT uc.userclass_id IN (" . e_UC_PUBLIC . ',' . e_UC_NOBODY . ',' . e_UC_READONLY . ',' . e_UC_BOTS . ")
				GROUP BY uc.userclass_id
						";

		$this->db2->gen($query);
		while($row = $this->db2->fetch())
		{
			$public = ($row['userclass_editclass'] == e_UC_PUBLIC)? "(" . LAN_MAILOUT_10 . ")": "";
			$selected = ($row['userclass_id'] == $curSel)? " selected='selected'": '';
			$ret .= "<option value='{$row['userclass_id']}'{$selected} >" . LAN_MAILOUT_55 . " - {$row['userclass_name']}  {$public} [{$row['members']}]</option>\n";
		}
		$ret .= " </select>\n";

		return $ret;
	}

	/**
	 * Creates a 'select' dropdown of non-system user fields
	 *
	 * @param string $list_name - name for <select>
	 * @param string $curval - current select value
	 * @param boolean $add_blank - add a blank line before the options if TRUE
	 * @return text for display if any extended fields defined; FALSE if none
	 * available
	 */
	public function ret_extended_field_list($list_name, $curval = '', $add_blank = FALSE)
	{
		$ue = e107::getUserExt();
		// Get the extended field handler
		if(count($ue->fieldDefinitions) == 0)
			return FALSE;
		$ret = "<select name='{$list_name}' class='tbox'>\n";
		if($add_blank)
			$ret .= "<option value=''>&nbsp;</option>\n";

		foreach($ue->fieldDefinitions as $fd)
		{
			if($fd['user_extended_struct_text'] != '_system_')
			{
				$value = 'ue.user_' . $fd['user_extended_struct_name'];
				$selected = ($value == $curval)? " selected='selected'": '';
				$ret .= "<option value='" . $value . "' {$selected}>" . ucfirst($fd['user_extended_struct_name']) . "</option>\n";
			}
		}
		$ret .= "</select>\n";
		return $ret;
	}

	/**
	 * Creates an array of data from standard $_POST fields
	 *
	 * @param $newMail - set TRUE for initial creation, FALSE when updating
	 * @return array of data
	 */
	public function parseEmailPost($newMail = TRUE)
	{
		$tp = e107::getParser();

		$ret = array(
			'mail_title' => $_POST['email_title'],
			'mail_subject' => $_POST['email_subject'],
			'mail_body' => $_POST['email_body'],
			'mail_sender_email' => $_POST['email_from_email'],
			'mail_sender_name' => $_POST['email_from_name'],
			'mail_copy_to' => $_POST['email_cc'],
			'mail_bcopy_to' => $_POST['email_bcc'],
			'mail_attach' => trim($_POST['email_attachment']),
			'mail_send_style' => varset($_POST['email_send_style'], 'textonly'),
			'mail_include_images' => (isset($_POST['email_include_images'])? 1: 0)
		);

		$ret = $tp->toDB($ret);
		// recursive

		if(isset($_POST['mail_source_id']))
		{
			$ret['mail_source_id'] = intval($_POST['mail_source_id']);
		}
		if($newMail)
		{
			$ret['mail_creator'] = USERID;
			$ret['mail_create_date'] = time();
		}
		return $ret;
	}

	/**
	 * Does some basic checking on email data.
	 *
	 * @param $email - array of data in parseEmailPost() format
	 * @param $fullCheck - TRUE to check all fields that are required (immediately
	 * prior to sending); FALSE to just check a few basics (prior to save)
	 * @return TRUE if OK. Array of error messages if any errors found
	 */
	public function checkEmailPost(&$email, $fullCheck = FALSE)
	{
		$errList = array();
		if(count($email) < 3)
		{
			$errList[] = LAN_MAILOUT_201;
			return $errList;
		}
		if(!trim($email['mail_subject']))
			$errList[] = LAN_MAILOUT_200;
		if(!trim($email['mail_body']))
			$errList[] = LAN_MAILOUT_202;
		if(!trim($email['mail_sender_name']))
			$errList[] = LAN_MAILOUT_203;
		if(!trim($email['mail_sender_email']))
			$errList[] = LAN_MAILOUT_204;
		if(strlen($email['mail_send_style']) == 0)
		{
			// Can be a template name now
			$errList[] = LAN_MAILOUT_205;
		//	break;
		}
		else
		{
			// Get template data, override email settings as appropriate
			require_once (e_HANDLER . 'mail_template_class.php');
			$ourTemplate = new e107MailTemplate();
			$templateName = $email['mail_send_style'];
			if(!$ourTemplate->setNewTemplate($templateName))
			{
				$errList[] = LAN_MAILOUT_207 . ':' . $templateName;
				print_a($ourTemplate);
				// Probably template not found if error
			}
			if(!$ourTemplate->makeEmailBody($email['mail_body'], $email['mail_include_images']))
			{
				$errList[] = LAN_MAILOUT_205 . ':' . $templateName;
				print_a($ourTemplate);
			}
			else
			{
				$email['mail_body_templated'] = $ourTemplate->mainBodyText;
				$email['mail_body_alt'] = $ourTemplate->altBodyText;
				if(count($ourTemplate->lastTemplateData['email_overrides']))
				{
					$email['mail_overrides'] = $ourTemplate->lastTemplateData['email_overrides'];
				}
			}
		}

		if(count($errList) == 0)
		{
			return TRUE;
		}
		return $errList;
	}

	/**
	 * Generate a table which shows some information about an email.
	 * Intended to be part of a 2-column table - includes the row detail, but not the
	 * surrounding table definitions
	 *
	 * @param $mailSource - array of mail information
	 * @param $options - controls how much information is displayed
	 * @return text for display
	 */
	public function showMailDetail(&$mailSource, $options = 'basic')
	{
		$tp = e107::getParser();

		if(!isset($this->mailDetailDisplay[$options]))
		{
			return "<tr><td colspan='2'>Programming bungle - invalid option value: {$options}</td></tr>";
		}

		$text = '';
		foreach($this->mailDetailDisplay[$options] as $k => $v)
		{
			$text .= '<tr><td>' . $this->fields['mail_content'][$k]['title'] . '</td><td>';
			$val = $mailSource[$k];
			
			if($k == 'mail_body')
			{
			//	$text .= print_a($mailSource,true);
			//	$text .= $tp->toHTML($val,true);
				$text .= "<iframe src='".e_ADMIN."mailout.php?mode=main&action=preview&id=".$mailSource['mail_source_id']."' width='100%' height='350'>Loading...</iframe>";	
				continue;
			}
			
			if(is_numeric($v))
			{
				$text .= ($v > 1)? $tp->text_truncate($val, $v, '...'): $val;
			}
			else
			{
				switch ($v)
				{
					case 'username':
						$text .= $this->getUserName($val);
					break;
					case 'sdatetime':
						$text .= $tp->toDate($val, 'short');
					break;
					case 'trunc200':
						
						$text .= e107::getParser()->text_truncate($val, 200, '...');
					break;
					case 'chars':
						// Show generated html as is
						$text .= htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
					break;
					case 'contentstatus':
						$text .= $this->statusToText($val);
					break;
					case 'selectors':
						$text .= 'cannot display';
					break;
					case 'yesno':
						$text .= $val? LAN_YES: LAN_NO;
					break;
					case 'default':
					default:
						$text .= $val;
				}
			}
			$text .= '</td></tr>' . "\n";
		}
		return $text;
	}

	/**
	 * Generate the HTML for dropdown to select mail sending style (text/HTML/styled
	 *
	 * @param $curval - current value
	 * @param $name name of item
	 * @return text for display
	 */
	public function sendStyleSelect($curval = '', $name = 'email_send_style', $incTemplates = TRUE)
	{

		$emFormat = array(
			'textonly' => LAN_MAILOUT_125,
			'texthtml' => LAN_MAILOUT_126,
			'texttheme' => LAN_MAILOUT_127
		);

		if($incTemplates)
		{
			$tList = self::getEmailTemplateNames('user');
			foreach($tList as $key => $val)
			{
				$emFormat[$key] = LAN_TEMPLATE .": ". $val;
			}	
		}
		
		if(empty($curval))
		{
			$curval = e107::getConfig()->get('mail_sendstyle');
				
		}

		
		return e107::getForm()->select($name,$emFormat, $curval, 'required=1&size=xxlarge');

	}

	/**
	 * Generate the HTML to show the mailout form. Used for both sending and editing
	 *
	 * @param $mailSource - array of mail information
	 * @return text for display
	 */
	/*
	 function show_mailform(&$mailSource)
	 {
	 global $HANDLERS_DIRECTORY;
	 global $mailAdmin;

	 $sql 	= e107::getDb();
	 $ns 	= e107::getRender();
	 $tp 	= e107::getParser();
	 $frm 	= e107::getForm();
	 $mes 	= e107::getMessage();
	 $pref 	= e107::getPref();

	 if (!is_array($mailSource))
	 {
	 $mes->addError('Coding error - mail not array (521)');
	 //$ns->tablerender('ERROR!!', );
	 //exit;
	 }

	 $email_subject = varset($mailSource['mail_subject'], '');
	 $email_body = $tp->toForm(varset($mailSource['mail_body'],''));
	 $email_id = varset($mailSource['mail_source_id'],'');

	 $text = '';

	 if(strpos($_SERVER['SERVER_SOFTWARE'],'mod_gzip') &&
	!is_readable(e_HANDLER.'phpmailer/.htaccess'))
	 {
	 $warning = LAN_MAILOUT_40.' '.$HANDLERS_DIRECTORY.'phpmailer/ '.LAN_MAILOUT_41;
	 $ns->tablerender(LAN_MAILOUT_42, $mes->render().$warning);
	 }

	 $debug = (e_MENU == "debug") ? "?[debug]" : "";

	 $text .= "<div>
	 <form method='post' action='".e_SELF."?mode=makemail' id='mailout_form'>";

	 $text .= $this->emailSelector('all', varset($mailSource['mail_selectors'],
	FALSE));

	 $text .= "<table class='table'>
	 <colgroup>
	 <col class='col-label' />
	 <col class='col-control' />
	 </colgroup>
	 <tr>
	 <td>".LAN_MAILOUT_111.": </td>
	 <td>".$frm->text('email_title',varset($mailSource['mail_title'],''))."</td>
	 </tr>

	 <tr>
	 <td>".LAN_MAILOUT_01.": </td>
	 <td>".$frm->text('email_from_name',varset($mailSource['mail_from_name'],USERNAME))."</td>
	 </tr>

	 <tr>
	 <td>".LAN_MAILOUT_02.": </td>
	 <td
	>".$frm->text('email_from_email',varset($mailSource['mail_from_email'],USEREMAIL))."</td>
	 </tr>";

	 // Add in the core and any plugin selectors here
	 */
	/*$text .= "

	 <tr>
	 <td>".LAN_MAILOUT_03.": </td>
	 <td>".$this->emailSelector('all', varset($mailSource['mail_selectors'],
	FALSE))."</td>
	 </tr>";*/
	/*
	 $text .= "
	 <tr>
	 <td>".LAN_MAILOUT_04.": </td>
	 <td>".$frm->text('email_cc',varset($mailSource['mail_cc'],''))."</td>
	 </tr>

	 <tr>
	 <td>".LAN_MAILOUT_05.": </td>
	 <td>".$frm->text('email_bcc',varset($mailSource['mail_bcc'],''))."</td>
	 </tr>

	 <tr>
	 <td>".LAN_MAILOUT_51.": </td>
	 <td>".$frm->text('email_subject',varset($email_subject,''),255,'required=1&size=xxlarge')."</td>
	 </tr>";

	 // Attachment.
	 if (e107::isInstalled('download'))
	 {
	 // TODO - use download plugin API

	 if($sql->select("download", "download_url,download_name", "download_id !=''
	ORDER BY download_name"))
	 {
	 $text .= "<tr>
	 <td>".LAN_MAILOUT_07.": </td>
	 <td >";
	 $text .= "<select class='tbox' name='email_attachment' >
	 <option value=''>&nbsp;</option>\n";

	 while ($row = $sql->fetch())
	 {
	 $selected = ($mailSource['mail_attach'] == $row['download_url']) ?
	"selected='selected'" : '';
	 //				$text .= "<option value='".urlencode($row['download_url'])."'
	// {$selected}>".htmlspecialchars($row['download_name'])."</option>\n";
	 $text .= "<option value='".$row['download_url']."'
	{$selected}>".htmlspecialchars($row['download_name'])."</option>\n";
	 }
	 $text .= " </select>";

	 $text .= "</td>
	 </tr>";
	 }

	 }
	 // TODO File-Picker from Media-Manager.

	 $text .= "
	 <tr>
	 <td>".LAN_MAILOUT_09.": </td>
	 <td >\n";

	 global $eplug_bb;

	 $eplug_bb[] = array(
	 'name'		=> 'shortcode',
	 'onclick'	=> 'expandit',
	 'onclick_var' => 'sc_selector',
	 'icon'		=> e_IMAGE.'generic/bbcode/shortcode.png',
	 'helptext'	=> LAN_MAILOUT_11,
	 'function'	=> array($this,'sc_Select'),
	 'function_var'	=> 'sc_selector'
	 );

	 $text .= $this->sendStyleSelect(varset($mailSource['mail_send_style'], ''));
	 $checked = (isset($mailSource['mail_include_images']) &&
	$mailSource['mail_include_images']) ? " checked='checked'" : '';
	 $text .= "&nbsp;&nbsp;<input type='checkbox' name='email_include_images'
	value='1' {$checked} />".LAN_MAILOUT_225;
	 $text .="
	 </td></tr>\n
	 <tr>

	 <td
	colspan='2'>".$frm->bbarea('email_body',$email_body,'mailout','helpb')."</td>
	 </tr>";

	 $text .="
	 <tr>
	 <td colspan='2'>
	 <div>";

	 //	$text .= display_help('helpb','mailout');

	 $text .="
	 </div></td>
	 </tr>
	 </table> ";

	 $text .= "<div class='buttons-bar center'>";

	 if($email_id)
	 {
	 $text .= $frm->hidden('mail_source_id',$email_id);
	 $text .= $frm->admin_button('update_email',LAN_UPDATE);
	 //$text .= "<input type='hidden' name='mail_source_id' value='".$email_id."'
	// />";
	 //$text .= "<input  type='submit' name='update_email' value=\"".LAN_UPDATE."\"
	// />";
	 }
	 else
	 {
	 $text .= $frm->admin_button('save_email',LAN_SAVE,'other');
	 }

	 $text .= $frm->admin_button('send_email',LAN_MAILOUT_08); //

	 $text .= "</div>

	 </form>
	 </div>";

	 return $text;

	 // $ns->tablerender(ADLAN_136.SEP.LAN_MAILOUT_15, $mes->render(). $text);		//
	// Render the complete form
	 }
	 */

	/**
	 *		Helper function manages the shortcodes which can be inserted
	 */
	function sc_Select($container = 'sc_selector')
	{
		$text = "
		<!-- Start of Shortcode selector -->\n
			<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$container}'>
			<div style='position:absolute; bottom:30px; right:125px'>
			<table class='fborder' style='background-color: #fff'>
			<tr><td>
			<select class='tbox' name='sc_sel' onchange=\"addtext(this.value); this.selectedIndex= 0; expandit('{$container}')\">
			<option value=''> -- </option>\n";

		$sc = array(
			'|DISPLAYNAME|' => LAN_MAILOUT_14,
			'|USERNAME|' => LAN_MAILOUT_16,
			'|SIGNUP_LINK|' => LAN_MAILOUT_17,
			'|USERID|' => LAN_MAILOUT_18,
			'|USERLASTVISIT|' => LAN_MAILOUT_178
		);

		foreach($sc as $key => $val)
		{
			$text .= "<option value='" . $key . "'>" . $val . "</option>\n";
		}
		$text .= "
			</select></td></tr>	\n </table></div>
			</div>
		\n<!-- End of SC selector -->
		";

		return $text;
	}

	/**
	 * Return dropdown for arithmetic comparisons
	 *
	 * @param $name  string name of select structure
	 * @param $curval string current value
	 * @return text for display
	 */
	public function comparisonSelect($name, $curval = '')
	{
		$compVals = array(
			' ' => ' ',
			'<' => LAN_MAILOUT_175,
			'=' => LAN_MAILOUT_176,
			'>' => LAN_MAILOUT_177
		);
		$ret = "<select name='{$name}' class='tbox'>\n";
		foreach($compVals as $k => $v)
		{
			$selected = ($k == $curval)? " selected='selected'": '';
			$ret .= "<option value='" . $k . "' {$selected}>" . $v . "</option>\n";
		}
		$ret .= "</select>\n";
		return $ret;
	}

	/**
	 *	Show the generated template of a saved email
	 */
	public function showEmailTemplate($mailId)
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$frm = e107::getForm();


		$mailData = $this->retrieveEmail($mailId);

		if($mailData === FALSE)
		{
			$mes->addInfo(LAN_MAILOUT_79);
			$ns->tablerender(ADLAN_136 . SEP . LAN_MAILOUT_171, $mes->render() . $text);
			exit ;
		}

		$text = "
			<form action='" . e_SELF . "?mode=saved' id='email_show_template' method='post'>
			<fieldset id='email-show-template'>
			<table class='table adminlist'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			<tbody>";

		$text .= $this->showMailDetail($mailData, 'template');
		$text .= '<tr><td>' . LAN_MAILOUT_172 . '</td><td>' . $this->statusToText($mailData['mail_content_status']) . "<input type='hidden' name='mailIDConf' value='{$mailID}' /></td></tr>";

		$text .= "</tbody></table>\n</fieldset>";

		$text .= "<div class='buttons-bar center'>
					" . $frm->admin_button('email_delete', LAN_MAILOUT_256, 'other') . " 
				</div>";

		$text .= "</form>";
		$ns->tablerender(ADLAN_136 . SEP . LAN_MAILOUT_255 . $mailId, $text);
	}

	/**
	 * Show a screen to confirm deletion of an email
	 *
	 * @param $mailid - number of email
	 * @param $nextPage - 'mode' specification for page to return to following delete
	 * @return text for display
	 */
	/*
	 public function showDeleteConfirm($mailID, $nextPage = 'saved')
	 {
	 $mailData = $this->retrieveEmail($mailID);
	 $frm = e107::getForm();
	 $ns = e107::getRender();
	 $mes = e107::getMessage();

	 if ($mailData === FALSE)
	 {
	 $mes->addInfo(LAN_MAILOUT_79);
	 $ns-> tablerender(ADLAN_136.SEP.LAN_MAILOUT_171, $mes->render().$text);
	 exit;
	 }

	 $text .= "
	 <form
	action='".e_SELF.'?mode=maildeleteconfirm&amp;m='.$mailID.'&amp;savepage='.$nextPage."'
	id='email_delete' method='post'>
	 <fieldset id='email-delete'>
	 <table class='table adminlist'>
	 <colgroup>
	 <col class='col-label' />
	 <col class='col-control' />
	 </colgroup>

	 <tbody>";

	 $text .= $this->showMailDetail($mailData, 'basic');
	 $text .=
	'<tr><td>'.LAN_MAILOUT_172.'</td><td>'.$this->statusToText($mailData['mail_content_status'])."<input
	type='hidden' name='mailIDConf' value='{$mailID}' /></td></tr>";
	 if ($mailData['mail_content_status'] != MAIL_STATUS_SAVED)
	 {
	 $text .= '<tr><td>'.LAN_MAILOUT_173.'</td><td>'.($mailData['mail_togo_count'] +
	$mailData['mail_sent_count'] + $mailData['mail_fail_count']).'</td></tr>';
	 }

	 $text .= "</tbody></table>\n</fieldset>";

	 $text .= "<div class='buttons-bar center'>
	 ".$frm->admin_button('email_delete', LAN_DELETE, 'delete')."
	 ".$frm->admin_button('email_cancel', LAN_CANCEL, 'cancel')."
	 </div>
	 </form>";

	 $ns->tablerender(ADLAN_136.SEP.LAN_MAILOUT_171, $text);
	 }
	 */

	/**
	 * Generate the HTML to show a list of emails of a particular type, in tabular
	 * form
	 *
	 * @param $type - type of email to display (saved|sent|pending|held)
	 * @param $from - offset into table of candidates
	 * @param $amount - number to return
	 * @return text for display
	 */
	/*
	 public function showEmailList($type, $from = 0, $amount = 10)
	 {
	 // Need to select main email entries; count number of addresses attached to each
	 $gen = new convert;
	 $frm = e107::getForm();
	 $ns = e107::getRender();
	 $mes = e107::getMessage();
	 $tp = e107::getParser();

	 switch ($type)
	 {
	 case 'sent' :
	 $searchType = 'allcomplete';
	 break;
	 default :
	 $searchType = $type;
	 }

	 if ($from < 0) { $from = $this->showFrom; }
	 if ($amount < 0) { $amount = $this->showCount; }
	 // in $_GET, so = sort order, sf = sort field
	 $count = $this->selectEmailStatus($from, $amount, '*', $searchType,
	$this->sortField, $this->sortOrder);
	 $totalCount = $this->getEmailCount();

	 $emails_found = array();			// Log ID and count for later

	 if (!$count)
	 {
	 $mes->addInfo(LAN_MAILOUT_79);
	 $ns->tablerender($this->tasks[$type]['title'], $mes->render() . $text);
	 return;
	 }

	 $text = "
	 <form action='".e_SELF.'?'.e_QUERY."' id='email_list' method='post'>
	 <fieldset id='emails-list'>
	 <table class='table adminlist'>";

	 $fieldPrefs = $this->calcFieldSpec($type, TRUE);			// Get columns to display

	 // Must use '&' rather than '&amp;' in query pattern
	 $text .=
	$frm->colGroup($this->fields['mail_content'],$this->fieldPref).$frm->thead($this->fields['mail_content'],$this->fieldPref,'mode='.$type."&fld=[FIELD]&asc=[ASC]&frm=[FROM]")."<tbody>";

	 while ($row = $this->getNextEmailStatus(FALSE))
	 {
	 //print_a($row);
	 $text .= '<tr>';
	 foreach ($fieldPrefs as $fieldName)
	 {	// Output column data value
	 $text .= '<td>';
	 if (isset($row[$fieldName]))
	 {
	 $proctype = varset($this->fields['mail_content'][$fieldName]['proc'],
	'default');
	 switch ($proctype)
	 {
	 case 'username' :
	 $text .= $this->getUserName($row[$fieldName]);
	 break;
	 case 'sdatetime' :
	 $text .= $gen->convert_date($row[$fieldName], 'short');
	 break;
	 case 'trunc200' :
	 $text .= $tp->text_truncate($row[$fieldName], 200, '...');
	 break;
	 case 'chars' :			// Show generated html as is
	 $text .= htmlspecialchars($row[$fieldName], ENT_COMPAT, 'UTF-8');
	 break;
	 case 'contentstatus' :
	 $text .= $this->statusToText($row[$fieldName]);
	 break;
	 case 'selectors' :
	 $text .= 'cannot display';
	 break;
	 case 'yesno' :
	 $text .= $row[$fieldName] ? LAN_YES : LAN_NO;
	 break;
	 case 'default' :
	 default :
	 $text .= $row[$fieldName];
	 }
	 }
	 else
	 {	// Special stuff
	 }
	 $text .= '</td>';
	 }
	 // Add in options here
	 $text .= '<td>'.$this->makeMailOptions($type,$row).'</td>';
	 $text .= '</tr>';
	 }
	 $text .= "</tbody></table><br /><br />\n";

	 if ($totalCount > $count)
	 {
	 $parms =
	"{$totalCount},{$amount},{$from},".e_SELF."?mode={$type}&amp;count={$amount}&amp;frm=[FROM]&amp;fld={$this->sortField}&amp;asc={$this->sortOrder}";
	 $text .= $tp->parseTemplate("{NEXTPREV={$parms}}");
	 }

	 $text .= '</fieldset></form>';
	 $ns->tablerender(ADLAN_136.SEP.$this->tasks[$type]['title'], $text);
	 }
	 */

	/**
	 * Generate a list of emails to send
	 * Returns various information to display in a confirmation screen
	 *
	 * The email and its recipients are stored in the DB with a tag of
	 * 'MAIL_STATUS_TEMP' if its a new email (no change if already on hold)
	 *
	 * @param array $mailData - Details of the email, selection criteria etc
	 * @param boolean $fromHold - FALSE if this is a 'new' email to send, TRUE if its
	 * already been put on hold (selects processing path)
	 * @return text for display
	 */
	public function sendEmailCircular($mailData, $fromHold = FALSE)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$frm = e107::getForm();

		if($fromHold)
		{
			// Email data already generated
			$mailMainID = $mailData['mail_source_id'];
			if($mailMainID == 0)
				return FALSE;
			if(FALSE === ($mailData = $this->retrieveEmail($mailMainID)))// Get the new data
			{
				return FALSE;
			}
			$counters['add'] = $mailData['mail_togo_count'];
			// Set up the counters
			$counters['dups'] = 0;
		}
		else
		{
			// Start by saving the email
			/*
			$mailData['mail_content_status'] = MAIL_STATUS_TEMP;
			$mailData['mail_create_app'] = 'core';
			$result = $this->saveEmail($mailData, TRUE);
		//	$result = $this->saveEmail($mailData, false); // false = update, not insert. 
			if(is_numeric($result))
			{
				$mailMainID = $mailData['mail_source_id'] = $result;
			}
			else
			{
				e107::getMessage()->addDebug("Couldn't save email. (".__FILE__." Line: ".__LINE__.")");
			}
			*/
			
			$mailMainID = $mailData['mail_source_id'];

			$this->mailInitCounters($mailMainID); // Initialise counters for emails added		
			
			foreach($this->mailHandlers as $key => $m)
			{
				// Get email addresses from each handler in turn. Do them one at a time, so that
				// all can use the $sql data object
				if($m->mailerEnabled && isset($mailData['mail_selectors'][$key]))
				{
					
					$mailerCount = $m->selectInit($mailData['mail_selectors'][$key]); // Initialise

					if(!empty($mailerCount))
					{
						
						while($row = $m->selectAdd()) // Get email addresses - add to list, strip duplicates
						{						
							$result = $this->mailAddNoDup($mailMainID, $row, MAIL_STATUS_TEMP); // Add email addresses to the database ready for sending (the body is never saved // in the DB - it gets passed as a $_POST value)
							
							if($result === FALSE)
							{
								// Error
								e107::getMessage()->addDebug("Couldn't add receipients (".__FILE__."  Line: ".__LINE__.")");
							}
						}
					}
					else
					{
						e107::getMessage()->addWarning($key.": no matching recipients");
					}



					$m->select_close();
					// Close
					// Update the stats after each handler
					$this->mailUpdateCounters($mailMainID);
				}
			}

			$counters = $this->mailRetrieveCounters($mailMainID);
			//	$this->e107->admin_log->log_event('MAIL_02','ID: '.$mailMainID.'
			// '.$counters['add'].'[!br!]'.$_POST['email_from_name']."
			// &lt;".$_POST['email_from_email'],E_LOG_INFORMATIVE,'');
		}

		// We've got all the email addresses here - display a confirmation form
		// Include start/end dates for send
		//	$text = "<form action='".e_SELF.'?mode=marksend&amp;m='.$mailMainID."'
		// id='email_send' method='post'>";

		$text = "
			<form action='" . e_SELF . "' id='email_send' method='post'>
			<fieldset id='email-send'>
			<table class='table adminlist'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			<tbody>";

		$text .= $this->showMailDetail($mailData, 'send');

		$text .= '<tr><td>' . LAN_MAILOUT_03 . '</td><td>'; // TO 
		
		// Add in core and any plugin selectors here
		foreach($this->mailHandlers as $key => $m)
		{

			if($m->mailerEnabled && ($contentArray = $m->showSelect(FALSE, $mailData['mail_selectors'][$key])))
			{
				
				$text .= $m->mailerName.':<ul>';
				foreach($contentArray as $val)
				{
					$text .= "<li>" . $val['caption'] . " : " . $val['html'] . "</li>";
				}
				$text .= '</ul>';
			}
		}

		$text .= '</td></tr>';
		
		// Figures - number of emails to send, number of duplicates stripped

		$totalRecipients = !empty($mailData['mail_togo_count']) ? $mailData['mail_togo_count'] : $counters['add'];

		$text .= '<tr><td>' . LAN_MAILOUT_173 . '</td><td>' . $totalRecipients . "<input type='hidden' name='mailIDConf' value='{$mailMainID}' /></td></tr>";
		$text .= '<tr><td>' . LAN_MAILOUT_71 . '</td><td> ' . $counters['add'] . ' ' . LAN_MAILOUT_69 . $counters['dups'] . LAN_MAILOUT_70 . '</td></tr>';
		$text .= "</tbody></table>\n</fieldset>";

		$this->updateCounter($mailMainID,'total',$counters['add']);
		
		$text .= $this->makeAdvancedOptions(TRUE);
		// Show the table of advanced options

		$text .= "<div class='buttons-bar center'>";
		$text .= "<a href='".e_SELF."?mode=main&action=sendnow&id=".$mailMainID."' class='btn btn-primary'>".LAN_MAILOUT_158."</a>";
		 
	//	$text .= $frm->admin_button('email_sendnow', "Send Now", 'primary');
		$text .= $frm->admin_button('email_send', LAN_MAILOUT_269);

		// $text .= "<input  type='submit' name='email_send' value=\"".LAN_SEND."\" />";

		if(!$fromHold)
		{
			$text .= $frm->admin_button('email_hold', LAN_HOLD, 'warning');
			$text .= $frm->admin_button('email_cancel', LAN_CANCEL, 'delete');
			// $text .= "&nbsp;<input  type='submit' name='email_hold' value=\"".LAN_HOLD."\"
			// />";
			// $text .= "&nbsp;<input  type='submit' name='email_cancel'
			// value=\"".LAN_CANCEL."\" />";
		}

		$text .= $frm->hidden('email_id', $mailMainID);

		$text .= "</div>
		</form>
		</div>";

		return $text;
		//	e107::getRender()->tablerender(ADLAN_136.SEP.LAN_MAILOUT_179, $mes->render().
		// $text);
	}// End of previewed email

	/**
	 *
	 */
	protected function makeAdvancedOptions($initHide = FALSE)
	{
		// Separate table for advanced mailout options
		// mail_notify_complete field
		$text = "
			<legend>" . LAN_MAILOUT_242 . "</legend>
			<fieldset id='email-send-options'>
			<table class='table adminlist'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			<tbody>";

		$text .= "<tr><td>" . LAN_MAILOUT_238 . "</td><td>" . $this->makeCalendar('mail_earliest_time', '', CORE_DATE_ORDER) . "</td></tr>";
		$text .= "<tr><td>" . LAN_MAILOUT_239 . "</td><td>" . $this->makeCalendar('mail_latest_time', '', CORE_DATE_ORDER) . "</td></tr>";
		// Can comment the two lines above, uncomment two lines below, and default
		// time/date is shown. May or may not be preferable
		//		$text .=
		// "<tr><td>".LAN_MAILOUT_238."</td><td>".$this->makeCalendar('mail_earliest_time',
		// time(), CORE_DATE_ORDER)."</td></tr>";
		//		$text .=
		// "<tr><td>".LAN_MAILOUT_239."</td><td>".$this->makeCalendar('mail_latest_time',
		// time()+86400, CORE_DATE_ORDER)."</td></tr>";
		$text .= "<tr><td>" . LAN_MAILOUT_240 . "</td><td><input type='checkbox' value='1' name='mail_notify_complete' />" . LAN_MAILOUT_241 . "</td></tr>";
		$text .= "</tbody></table>\n</fieldset>";
		return $text;
	}

	/**
	 *
	 */
	public function makeCalendar($calName, $calVal = '', $dateOrder = 'dmy')
	{
		// Determine formatting strings this way, to give sensible default
		switch ($dateOrder)
		{
			case 'mdy':
				$dFormat = '%m/%d/%y';
				$tFormat = '%H:%M';
			break;
			case 'ymd':
				$dFormat = '%Y/%m/%d';
				$tFormat = ' %H:%M';
			break;
			case 'dmy':
			default:
				$dFormat = '%d/%m/%Y';
				$tFormat = ' %H:%M';
		}

		$options = array(
			'type' => 'datetime',
			'format' => $dFormat . " " . $tFormat,
			//	'timeformat' => $tFormat,
			'firstDay' => 1, // 0 = Sunday.
			'size' => 12
		);
		//		$options['dateFormat'] 	= $dformat;
		//		$options['timeFormat'] 	= $tformat;

		return e107::getForm()->datepicker($calName, $calVal, $options);
	}


	/**
	 * Show recipients of an email
	 *
	 * @param $mailid - number of email
	 * @param $nextPage - 'mode' specification for page to return to following delete
	 * @return text for display
	 */
	/*
	public function showmailRecipients($mailID, $nextPage = 'saved')
	{
		$gen = new convert;
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$ns = e107::getRender();

		$mailData = $this->retrieveEmail($mailID);

		if($mailData === FALSE)
		{
			$mes->addInfo(LAN_MAILOUT_79);
			$ns->tablerender(ADLAN_136 . SEP . LAN_MAILOUT_171, $mes->render() . $text);
			exit ;
		}

		$text .= "
			<form action='" . e_SELF . '?' . e_QUERY . "' id='email_recip_header' method='post'>
			<fieldset id='email-recip_header'>
			<table class='table adminlist'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			
			<tbody>";

		$text .= $this->showMailDetail($mailData, 'basic');
		$text .= '<tr><td>' . LAN_MAILOUT_172 . '</td><td>' . $this->statusToText($mailData['mail_content_status']) . "<input type='hidden' name='mailIDConf' value='{$mailID}' /></td></tr>";
		if($mailData['mail_content_status'] != MAIL_STATUS_SAVED)
		{
			$text .= '<tr><td>' . LAN_MAILOUT_173 . '</td><td>' . ($mailData['mail_togo_count'] + $mailData['mail_sent_count'] + $mailData['mail_fail_count']) . '</td></tr>';
		}

		$text .= "</tbody></table>\n</fieldset></form>";

		// List of recipients
		// in $_GET, asc = sort order, fld = sort field
		$count = $this->selectTargetStatus($mailID, $this->showFrom, $this->showCount, '*', FALSE, $this->sortField, $this->sortOrder);
		$totalCount = $this->getTargetCount();

		if($count == 0)
		{
			$text .= "<span class='required'>" . LAN_MAILOUT_253 . '</span>';
		}
		else
		{
			$text .= "
				<form action='" . e_SELF . "?mode=recipients&amp;m={$mailID}&amp;count={$count}&amp;frm={$this->showFrom}&amp;fld={$this->sortField}&amp;asc={$this->sortOrder}&amp;savepage={$nextPage}' id='email_recip_body' method='post'>
				<fieldset id='email-recip_body'>
				<table class='table adminlist'>";

			$fieldPrefs = $this->calcFieldSpec('recipients', TRUE);
			// Get columns to display

			// Must use '&' rather than '&amp;' in query pattern
			$text .= $frm->colGroup($this->fields['mail_recipients'], $this->fieldPref) . $frm->thead($this->fields['mail_recipients'], $this->fieldPref, 'mode=' . 'recipients&amp;m=' . $mailID . "&fld=[FIELD]&asc=[ASC]&frm=[FROM]") . "<tbody>";

			while($row = $this->getNextTargetStatus(FALSE))
			{
				//	print_a($row);
				$text .= '<tr>';
				foreach($fieldPrefs as $fieldName)
				{
					// Output column data value
					$text .= '<td>';
					if(isset($row[$fieldName]))
					{
						$proctype = varset($this->fields['mail_recipients'][$fieldName]['proc'], 'default');
						switch ($proctype)
						{
							case 'username':
								$text .= $this->getUserName($row[$fieldName]);
							break;
							case 'sdatetime':
								$text .= $gen->convert_date($row[$fieldName], 'short');
							break;
							case 'trunc200':
								$text .= $tp->text_truncate($row[$fieldName], 200, '...');
							break;
							case 'chars':
								// Show generated html as is
								$text .= htmlspecialchars($row[$fieldName], ENT_COMPAT, 'UTF-8');
							break;
							case 'contentstatus':
								$text .= $this->statusToText($row[$fieldName]);
							break;
							case 'selectors':
								$text .= 'cannot display';
							break;
							case 'array':
								if(is_array($row[$fieldName]))
								{
									$nl = '';
									foreach($row[$fieldName] as $k => $v)
									{
										if($v)
										{
											$text .= $nl . $k . ' => ' . $v;
											$nl = '<br />';
										}
									}
								}
								else
								{
									$text .= 'bad data: ';
								}
							break;
							case 'default':
							default:
								$text .= $row[$fieldName];
						}
					}
					else
					{
						// Special stuff
						$text .= 'special';
					}
					$text .= '</td>';
				}
				// Add in options here
				$text .= '<td>' . $this->makeTargetOptions('recipients', $row) . '</td>';
				$text .= '</tr>';
			}

			$text .= "</tbody></table>\n</fieldset></form><br /><br />";

			if($totalCount > $count)
			{
				$parms = "{$totalCount},{$this->showCount},{$this->showFrom}," . e_SELF . "?mode=recipients&amp;m={$mailID}&amp;count={$this->showCount}&amp;frm=[FROM]&amp;fld={$this->sortField}&amp;asc={$this->sortOrder}&amp;savepage={$nextPage}";
				$text .= $tp->parseTemplate("{NEXTPREV={$parms}}");
			}
		}

		$ns->tablerender(ADLAN_136 . SEP . LAN_MAILOUT_181, $mes->render() . $text);
	}
	*/
	
	
	/**
	 * Clean up mailout DB
	 * Dump array of results to admin log
	 *
	 * @return boolean TRUE if no errors, FALSE if errors
	 */
	public function dbTidy()
	{
		$noError = TRUE;
		$results = array();
		$this->checkDB(2);
		// Make sure DB object created

		// First thing, delete temporary records from both tables
		if(($res = $this->db2->db_Delete('mail_content', '`mail_content_status` = ' . MAIL_STATUS_TEMP)) === FALSE)
		{
			$results[] = 'Error ' . $this->db2->mySQLlastErrNum . ':' . $this->db2->mySQLlastErrText . ' deleting temporary records from mail_content';
			$noError = FALSE;
		}
		else
		{
			if($res)
				$results[] = str_replace(array(
					'[x]',
					'[y]'
				), array(
					$res,
					'mail_content'
				), LAN_MAILOUT_227);
		}
		if(($res = $this->db2->delete('mail_recipients', '`mail_status` = ' . MAIL_STATUS_TEMP)) === FALSE)
		{
			$results[] = 'Error ' . $this->db2->mySQLlastErrNum . ':' . $this->db2->mySQLlastErrText . ' deleting temporary records from mail_recipients';
			$noError = FALSE;
		}
		else
		{
			if($res)
				$results[] = str_replace(array(
					'[x]',
					'[y]'
				), array(
					$res,
					'mail_recipients'
				), LAN_MAILOUT_227);
		}

		// Now look for 'orphaned' recipient records
		if(($res = $this->db2->gen("DELETE `#mail_recipients` FROM `#mail_recipients` 
					LEFT JOIN `#mail_content` ON `#mail_recipients`.`mail_detail_id` = `#mail_content`.`mail_source_id`
					WHERE `#mail_content`.`mail_source_id` IS NULL")) === FALSE)
		{
			$results[] = 'Error ' . $this->db2->mySQLlastErrNum . ':' . $this->db2->mySQLlastErrText . ' deleting orphaned records from mail_recipients';
			$noError = FALSE;
		}
		elseif($res)
		{
			if($res)
				$results[] = str_replace('[x]', $res, LAN_MAILOUT_226);
		}

		// Scan content table for anomalies, out of time records
		if(($res = $this->db2->gen("SELECT * FROM `#mail_content` 
					WHERE (`mail_content_status` >" . MAIL_STATUS_FAILED . ") AND (`mail_content_status` <=" . MAIL_STATUS_MAX_ACTIVE . ")
					AND ((`mail_togo_count`=0) OR ( (`mail_last_date` != 0) AND (`mail_last_date` < " . time() . ")))")) === FALSE)
		{
			$results[] = 'Error ' . $this->db2->mySQLlastErrNum . ':' . $this->db2->mySQLlastErrText . ' checking bad status in mail_content';
			$noError = FALSE;
		}
		else
		{
			$items = array();
			// Store record number of any content record that needs to be changed
			while($row = $this->db2->fetch())
			{
				$items[] = $row['mail_source_id'];
				if($row['mail_source_id'])
				{
					if(FALSE == $this->cancelEmail($row['mail_source_id']))
					{
						$results[] = 'Error cancelling email ref: ' . $row['mail_source_id'];
					}
					else
					{
						$results[] = 'Email cancelled: ' . $row['mail_source_id'];
					}
				}
			}
			if(count($items))
				$results[] = str_replace(array(
					'[x]',
					'[y]'
				), array(
					count($items),
					implode(', ', $items)
				), LAN_MAILOUT_228);
		}

		//Finally - check for inconsistent recipient and content status records -
		// basically verify counts
		if(($res = $this->db2->gen("SELECT COUNT(mr.`mail_status`) AS mr_count, mr.`mail_status`,
					mc.`mail_source_id`, mc.`mail_togo_count`, mc.`mail_sent_count`, mc.`mail_fail_count`, mc.`mail_bounce_count`, mc.`mail_source_id` FROM `#mail_recipients` AS mr
					LEFT JOIN `#mail_content` AS mc ON mr.`mail_detail_id` = mc.`mail_source_id` 
					WHERE mc.`mail_content_status` <= " . MAIL_STATUS_MAX_ACTIVE . "
					GROUP BY mr.`mail_status`, mc.`mail_source_id` ORDER BY mc.`mail_source_id`
					")) === FALSE)
		{
			$results[] = 'Error ' . $this->db2->mySQLlastErrNum . ':' . $this->db2->mySQLlastErrText . ' assembling email counts';
			$noError = FALSE;
		}
		else
		{
			$lastMail = 0;
			// May get several rows per mail
			$notLast = TRUE;
			// This forces one more loop, so we can clean up for last record read
			$changeCount = 0;
			$saveRow = array();
			while(($row = $this->db2->fetch()) || $notLast)
			{
				if(($lastMail > 0 && $row === FALSE) || ($lastMail != $row['mail_source_id']))
				{
					// Change of mail ID here - handle any accumulated info
					if($lastMail > 0)
					{
						// Need to verify counts for mail just read
						$changes = array();
						foreach($counters as $k => $v)
						{
							if($saveRow[$k] != $v)
							{
								$changes[$k] = $v;
								// Assume the counters have got it right
							}
						}
						if(count($changes))
						{
							// *************** Update mail record here *********************
							$this->checkDB(1);
							$this->db->update('mail_content', array(
								'data' => $changes,
								'WHERE' => '`mail_source_id` = ' . $lastMail,
								'_FIELDS' => $this->dbTypes['mail_content']
							));
							$line = "Count update for {$saveRow['mail_source_id']} - {$saveRow['mail_togo_count']}, {$saveRow['mail_sent_count']}, {$saveRow['mail_fail_count']}, {$saveRow['mail_bounce_count']} => ";
							$line .= implode(', ', $counters);
							$results[] = $line;
							$changeCount++;
							//echo $line.'<br />';
						}
					}

					// Now reset for current mail
					$lastMail = $row['mail_source_id'];
					$counters = array(
						'mail_togo_count' => 0,
						'mail_sent_count' => 0,
						'mail_fail_count' => 0,
						'mail_bounce_count' => 0
					);
					$saveRow = $row;
				}
				if($row === FALSE)
					$notLast = FALSE;
				// We get one record for each mail_status value for a given email - use them to
				// update counts
				if($notLast)
				{
					switch ($row['mail_status'])
					{
						case MAIL_STATUS_SENT:
							// Mail sent. Email handler happy, but may have bounced (or may be yet to bounce)
							$counters['mail_sent_count'] += $row['mr_count'];
						break;
						case MAIL_STATUS_BOUNCED:
							$counters['mail_sent_count'] += $row['mr_count'];
							// It was sent, so increment that counter
							$counters['mail_bounce_count'] += $row['mr_count'];
							//...but bounced, so extra status
						break;
						case MAIL_STATUS_CANCELLED:
						// Cancelled email - treat as a failure
						case MAIL_STATUS_FAILED:
							$counters['mail_fail_count'] += $row['mr_count'];
							// Never sent at all
						break;
						case MAIL_STATUS_PARTIAL:
							// Shouldn't get this on individual emails - ignore if we do
						break;
						default:
							if(($row['mail_status'] >= MAIL_STATUS_PENDING) && ($row['mail_status'] <= MAIL_STATUS_MAX_ACTIVE))
							{
								$counters['mail_togo_count'] += $row['mr_count'];
								// Still in the queue
							}
					}
				}
			}
			if($changeCount)
				$results[] = str_replace('[x]', $changeCount, LAN_MAILOUT_237);
		}

		e107::getLog()->add('MAIL_05', implode('[!br!]', $results), E_LOG_INFORMATIVE, '');
		return $noError;
	}

	/**
	 *	Get a list of all the available email templates, by name and variable name
	 *
	 *	@param string $sel - currently (all|system|user) - selects template type
	 *
	 *	@return array - key is the variable name of the template, value is the stored
	 * template name
	 */
	public function getEmailTemplateNames($sel = 'all')
	{
		$ret = array();

		$templates = e107::getCoreTemplate('email', false, 'front', false);

		foreach($templates as $key => $layout)
		{
			if(vartrue($layout['name']))
			{
				$ret[$key] = $layout['name'];
			}

		}

		return $ret;
		/*

		 foreach (array(e_CORE.'templates/email_template.php',
		THEME.'templates/email_template.php') as $templateFileName )	// Override file
		then defaults
		 if (is_readable($templateFileName))
		 {
		 require($templateFileName);
		 $tVars = get_defined_vars();
		 if (isset($tVars['GLOBALS'])) unset($tVars['GLOBALS']);
		 foreach ($tVars as $tKey => $tData)
		 {
		 if (is_array($tData) && isset($tData['template_name']))
		 {
		 if (!isset($tData['template_type']) || ($tData['template_type'] == 'all') ||
		($tData['template_type'] == $sel))
		 {
		 $ret[$tKey] = $tData['template_name'];
		 }
		 }
		 if ($tKey != 'ret')
		 {
		 unset($tVars[$tKey]);
		 }
		 }
		 }

		 print_a($ret);
		 return $ret;

		 */
	}




	public static function mailerPrefsTable($pref, $id='mailer')
	{

		$frm = e107::getForm();

		$mailers = array('php'=>'php','smtp'=>'smtp','sendmail'=>'sendmail');

		$smtp_opts = explode(',',varset($pref['smtp_options'],''));
		$smtpdisp = ($pref[$id] != 'smtp') ? "style='display:none;'" : '';

		$text = $frm->select($id, $mailers, $pref[$id])."
		<span class='field-help'>".LAN_MAILOUT_116."</span>";

		$text .= "<div id='smtp' {$smtpdisp}>
		<table class='table table-bordered adminlist' style='margin-top:10px;width:auto;margin-right:auto;margin-left:0'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		";


		$ports = array(25=>'25 ('.LAN_DEFAULT.")",26=>'26',465=>'465 (SSL)', 587=>'587', 2465=>'2465', 2525=>'2525', 2587=>'2587');

		$text .= "
		<tr>
		<td>".LAN_MAILOUT_87.":&nbsp;&nbsp;</td>
		<td>".$frm->text('smtp_server',$pref['smtp_server'], 128, array('size'=>'xxlarge'))."</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_88.":</td>
		<td style='width:50%;' >".$frm->text('smtp_username',$pref['smtp_username'], 128, array('size'=>'xxlarge', 'placeholder'=>"(".LAN_OPTIONAL.")"))."</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_89.":</td>
		<td>".$frm->password('smtp_password',$pref['smtp_password'], 128, array('size'=>'xxlarge', 'required'=>false, 'pattern'=>'.{4,}', 'placeholder'=>"(".LAN_OPTIONAL.")", 'autocomplete'=>'new-password'))."
		</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_261."</td>
		<td>".$frm->select('smtp_port',$ports, $pref['smtp_port'])."
		</td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_90."</td><td>
		<select class='tbox' name='smtp_options'>\n

		<option value=''>".LAN_NONE."</option>\n";
		$selected = (in_array('secure=SSL',$smtp_opts) ? " selected='selected'" : '');
		$text .= "<option value='smtp_ssl'{$selected}>".LAN_MAILOUT_92."</option>\n";
		$selected = (in_array('secure=TLS',$smtp_opts) ? " selected='selected'" : '');
		$text .= "<option value='smtp_tls'{$selected}>".LAN_MAILOUT_93."</option>\n";
		$selected = (in_array('pop3auth',$smtp_opts) ? " selected='selected'" : '');
		$text .= "<option value='smtp_pop3auth'{$selected}>".LAN_MAILOUT_91."</option>\n";
		$text .= "</select></td></tr>";

		$text .= "<tr>
			<td><label for='smtp_keepalive'>".LAN_MAILOUT_57."</label></td><td>\n";

		$text .= $frm->radio_switch('smtp_keepalive', $pref['smtp_keepalive'])."
			</td>
			</tr>";


		$text .= "<tr>
			<td><label for='smtp_useVERP'>".LAN_MAILOUT_95."</label></td><td>".$frm->radio_switch('smtp_useVERP',(in_array('useVERP',$smtp_opts)))."
				</td>
			</tr>
			</table></div>";

		/* FIXME - posting SENDMAIL path triggers Mod-Security rules. use define() in e107_config.php instead.
			// Sendmail. -------------->

				$text .= "<div id='sendmail' {$senddisp}><table style='margin-right:0px;margin-left:auto;border:0px'>";
				$text .= "
				<tr>
				<td>".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
				<td>
				<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
				</td>
				</tr>

				</table></div>";
			*/

		e107::js('footer-inline', "

			$('#".$id."').on('change', function() {

				var type = $(this).val();

				if(type == 'smtp')
				{
					$('#smtp').show('slow');
					$('#sendmail').hide('slow');
					return;
				}

				if(type =='sendmail')
				{
					$('#smtp').hide('slow');
					$('#sendmail').show('slow');
					return;
				}

				$('#smtp').hide('slow');
				$('#sendmail').hide('slow');


			});


		");






		return $text;


	}


}
?>
