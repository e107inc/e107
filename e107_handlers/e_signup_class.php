<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107.org
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


if (!defined('e107_INIT')) { exit; }


/**
 * Class e_signup_class
 * @todo add all processing elements withing signup.php into this class.
 * @todo create unit tests for each of the methods.
 */
class e_signup
{

	private $testMode = false;
	private $pref;

	function __construct()
	{
		$this->pref = e107::pref('core');

		$this->pref['user_reg_veri'] = intval($this->pref['user_reg_veri']);

		if(getperms('0'))
		{
			$this->testMode = true;
		}

	}


	public function run()
	{
		$ns = e107::getRender();

		if(substr(e_QUERY,0,9)=='activate.')
		{
			$result = $this->processActivationLink(e_QUERY);

			switch($result)
			{

				case "failed":
					$ns->tablerender(LAN_SIGNUP_75, LAN_SIGNUP_101);
					break;

				case "exists":
					$text = "<div class='alert alert-success'>".LAN_SIGNUP_41."</div>";
					$ns->tablerender(LAN_SIGNUP_75, $text);
					break;

				case "success":
					$text = "<div class='alert alert-success'>".LAN_SIGNUP_74." <a href='index.php'>".LAN_SIGNUP_22."</a> ".LAN_SIGNUP_23."<br />".LAN_SIGNUP_24." ".SITENAME."</div>";
					$ns->tablerender(LAN_SIGNUP_75, $text);
					break;

				default:
				case "invalid":
					echo e107::getMessage()->addError("Invalid URL")->render();
					break;
					// code to be executed if n is different from all labels;
			}

			return null;
		}

		if((e_QUERY == 'resend') && (!USER || $this->testMode) && ($this->pref['user_reg_veri'] === 1))
		{
			if(empty($_POST['submit_resend']))
			{
				$this->renderResendForm();
			}
			else
			{
				$this->resendEmail();
			}
		}

		if($this->testMode == true)
		{
			if(e_QUERY == 'preview')
			{
				$this->renderEmailPreview();
			}

			if(e_QUERY == "preview.aftersignup")
			{
				$this->renderAfterSignupPreview();
			}

			if(e_QUERY == 'test')
			{
				$this->sendEmailPreview();
			}
		}



	}

/*
	private function renderForm()
	{




	}
*/

	private function resendEmail()
	{
		global $userMethods;

		$ns = e107::getRender();
		$tp = e107::getParser();
		$sql = e107::getDb();
		// Action user's submitted information
		// 'resend_email' - user name or email address actually used to sign up
		// 'resend_newemail' - corrected email address
		// 'resend_password' - password (required if changing email address)

		$clean_email = $tp->toDB($_POST['resend_email']); // may also be username
		/*if(!check_email($clean_email))
		{
			$clean_email = "xxx";
		}*/

		$new_email = $tp->toDB(varset($_POST['resend_newemail'], ''));
		if(!check_email($new_email ))
		{
			$new_email = FALSE;
		}

		// Account already activated
		if($_POST['resend_email'] && !$new_email && $clean_email && $sql->gen("SELECT * FROM #user WHERE user_ban=0 AND user_sess='' AND (`user_loginname`= '".$clean_email."' OR `user_name` = '".$clean_email."' OR `user_email` = '".$clean_email."' ) "))
		{
			$ns->tablerender(LAN_SIGNUP_40,LAN_SIGNUP_41."<br />");
			return false;
		}


		// Start by looking up the user
		if(!$sql->select("user", "*", "(`user_loginname` = '".$clean_email."' OR `user_name` = '".$clean_email."' OR `user_email` = '".$clean_email."' ) AND `user_ban`=".USER_REGISTERED_NOT_VALIDATED." AND `user_sess` !='' LIMIT 1"))
		{
			message_handler("ALERT",LAN_SIGNUP_64.': '.$clean_email); // email (or other info) not valid.
			return false;
		}

		$row = $sql -> fetch();
		// We should have a user record here

		if(trim($_POST['resend_password']) !="" && $new_email) // Need to change the email address - check password to make sure
		{
			if ($userMethods->CheckPassword($_POST['resend_password'], $row['user_loginname'], $row['user_password']) === TRUE)
			{
				if ($sql->select('user', 'user_id, user_email', "user_email='".$new_email."'"))
				{	// Email address already used by someone
					message_handler("ALERT",LAN_SIGNUP_106); 	// Duplicate email
					return false;
				}
				if($sql->update("user", "user_email='".$new_email."' WHERE user_id = '".$row['user_id']."' LIMIT 1 "))
				{
					$row['user_email'] = $new_email;
				}
			}
			else
			{
				message_handler("ALERT",LAN_INCORRECT_PASSWORD); // Incorrect Password.
				return false;
			}
		}

		// Now send the email - got some valid info
		$editPassword = e107::getPref('signup_option_password', 2);

		if(empty($editPassword)) // user input of password was disabled, so generate a new one.
		{
			$row['user_password'] = $userMethods->resetPassword($row['user_id']);
		}
		else
		{
			$row['user_password'] = 'xxxxxxx';		// Don't know the real one
		}

		$row['activation_url'] = SITEURL."signup.php?activate.".$row['user_id'].".".$row['user_sess'];

		$eml = $this->render_email($row);
		$eml['e107_header'] = $row['user_id'];


		if($this->testMode == true) // Test Mode.
		{
			echo e107::getEmail()->preview($eml);

			e107::getMessage()->setTitle(LAN_SIGNUP_43,E_MESSAGE_SUCCESS)->addSuccess(LAN_SIGNUP_44." ".$row['user_email']." - ".LAN_SIGNUP_45);
			$ns->tablerender(null,e107::getMessage()->render());

			e107::getMessage()->setTitle(LAN_ERROR,E_MESSAGE_ERROR)->addError(LAN_SIGNUP_42);
			$ns->tablerender(null, e107::getMessage()->render());

			return true;
		}

		$result = e107::getEmail()->sendEmail($row['user_email'], $row['user_name'], $eml, false);

		if(!$result)
		{
			e107::getMessage()->setTitle(LAN_ERROR,E_MESSAGE_ERROR)->addError(LAN_SIGNUP_42);
			$ns->tablerender(null, e107::getMessage()->render());
			$do_log['signup_result'] = LAN_SIGNUP_62;
		}
		else
		{
			e107::getMessage()->setTitle(LAN_SIGNUP_61,E_MESSAGE_SUCCESS)->addSuccess(LAN_SIGNUP_44." ".$row['user_email']." - ".LAN_SIGNUP_45);
			$ns->tablerender(null,e107::getMessage()->render());
			$do_log['signup_result'] = LAN_SIGNUP_61;
		}

		// Now log this (log will ignore if its disabled)
		$do_log['signup_action'] = LAN_SIGNUP_63;

		e107::getLog()->user_audit(USER_AUDIT_PW_RES,$do_log,$row['user_id'],$row['user_name']);


		return $result;
	}




	private function renderResendForm()
	{
		$ns = e107::getRender();
		$frm = e107::getForm();

		$text = "<div id='signup-resend-email'>
		<form method='post' class='form-horizontal' action='".e_SELF."?resend' id='resend_form' autocomplete='off'>
		<table style='".USER_WIDTH."' class='table fborder'>
		<tr>
			<td class='forumheader3' style='width:30%'>".LAN_SIGNUP_48."</td>
            <td class='forumheader3'>".$frm->text('resend_email','',80)."
            <a class='e-expandit' href='#different'>".LAN_SIGNUP_121."</a></td>
		</tr>
		</table>

		<div  id='different' class='e-hideme'>
			<table  style='".USER_WIDTH."' class='table fborder'>
				<tr>
					<td class='forumheader3' colspan='2'>".LAN_SIGNUP_49."</td>
				</tr>
				<tr>
					<td class='forumheader3' style='width:30%'>".LAN_SIGNUP_50."</td>
					<td class='forumheader3'>".$frm->text('resend_newemail', '', 50)."</td>
				</tr>
				<tr>
					<td class='forumheader3'>".LAN_SIGNUP_51."</td>
					<td class='forumheader3'>".$frm->text('resend_password', '', 50)."</td>
				</tr>
			</table>
			</div>
		";

		$text .="<div class='center'>";
		$text .= "<input class='btn btn-primary button' type='submit' name='submit_resend' value=\"".LAN_SIGNUP_47."\" />";  // resend activation email.
		$text .= "</div>

		</form>
		</div>";

		$ns->tablerender(LAN_SIGNUP_47, $text);


	}




	private function sendEmailPreview()
	{
		$temp = array();
		$eml = $this->render_email($temp, TRUE); // It ignores the data, anyway
		$mailer = e107::getEmail();

		if(!$mailer->sendEmail(USEREMAIL, USERNAME, $eml, FALSE))
		{
			echo "<div class='alert alert-danger'>".LAN_SIGNUP_42."</div>"; // there was a problem.
		}
		else
		{
			echo "<div class='alert alert-success'>".LAN_SIGNUP_43." [ ".USEREMAIL." ] - ".LAN_SIGNUP_45."</div>";
		}

	}


	function renderEmailPreview()
	{
		$ns = e107::getRender();
		$tp = e107::getParser();

		$temp = array();
		$eml = $this->render_email($temp, true); // It ignores the data, anyway
		$ns->tablerender('Email Preview', $tp->replaceConstants($eml['preview'],'abs'));

	}


	private function renderAfterSignupPreview()
	{
		global $allData;
		$ns = e107::getRender();

	    $allData['data']['user_email'] = "example@email.com";
		$allData['data']['user_loginname'] = "user_loginname";

	  	$after_signup = self::render_after_signup(null);

		$ns->tablerender($after_signup['caption'], $after_signup['text']);
	}


	/**
	 * @param $queryString
	 * @return string invalid|failed|exists|success
	 */
	public function processActivationLink($queryString)
	{

		$userMethods = e107::getUserSession();
		$sql        = e107::getDb();
		$tp         = e107::getParser();
		$log        = e107::getLog();


		$qs = explode('.', $queryString); // ie.  activate.".$row['user_id'].".".$row['user_sess']

		if ($qs[0] == 'activate' && (count($qs) == 3 || count($qs) == 4) && $qs[2])
		{

			if(isset($qs[3]) && strlen($qs[3]) == 2 ) // language-code detected... return the message in the correct language.
			{
				$slng = e107::getLanguage();
				$the_language = $slng->convert($qs[3]);


				if(is_readable(e_LANGUAGEDIR.$the_language.'/lan_signup.php'))
				{
					e107::includeLan(e_LANGUAGEDIR.$the_language.'/lan_signup.php');
				}
				else
				{
					e107::coreLan('signup');
				}
			}
			else
			{
				e107::coreLan('signup');
			}

			// When user clicks twice on the email activation link or admin manually activated the account already.
			if($sql->select("user", "user_id", "user_id = ".intval($qs[1])." AND user_ban = 0 AND user_sess='' " ) ) //TODO XXX check within last 24 hours only?
			{
				return 'exists';
			}


			e107::getCache()->clear("online_menu_totals");

			if ($sql->select("user", "*", "user_sess='".$tp->toDB($qs[2], true)."' LIMIT 1"))
			{
				if ($row = $sql->fetch())
				{
					$dbData = array();
					$dbData['WHERE'] = " user_sess='".$tp->toDB($qs[2], true)."' ";
					$dbData['data'] = array('user_ban'=>'0', 'user_sess'=>'');


					// Set initial classes, and any which the user can opt to join
					if ($init_class = $userMethods->userClassUpdate($row, 'userfull'))
					{
						//print_a($init_class); exit;
						$dbData['data']['user_class'] = $init_class;
					}

					$userMethods->addNonDefaulted($dbData);
					validatorClass::addFieldTypes($userMethods->userVettingInfo,$dbData);
					$newID = $sql->update('user',$dbData);

					if($newID === false)
					{
						$log->addEvent(10,debug_backtrace(),'USER','Verification Fail', print_r($row,true),false, LOG_TO_ROLLING);
						return 'failed';
					}

					// Log to user audit log if enabled
					$log->user_audit(USER_AUDIT_EMAILACK,$row);

					e107::getEvent()->trigger('userveri', $row);			// Legacy event
					e107::getEvent()->trigger('user_signup_activated', $row);
					e107::getEvent()->trigger('userfull', $row);			// 'New' event

					if (!empty($this->pref['autologinpostsignup']) && !e107::isCli())
					{
						require_once(e_HANDLER.'login.php');
						$usr = new userlogin();
						$usr->login($row['user_loginname'], md5($row['user_name'].$row['user_password'].$row['user_join']), 'signup', '');
					}


					return 'success';
				}
			}
			else
			{
				// Invalid activation code
				$log->addEvent(10,debug_backtrace(),'USER','Invalid Verification URL', print_r($qs,true),false, LOG_TO_ROLLING);
			}
		}


		return 'invalid';

	}


	/**
	 * Create email to send to user who just registered.
	 *
	 * @param array $userInfo is the array of user-related DB variables
	 * @param bool  $preview
	 * @return array of data for mailer - field names directly compatible
	 */
	function render_email($userInfo, $preview = FALSE)
	{

		if($preview == TRUE)
		{
			$userInfo['user_password'] = "test-password";
			$userInfo['user_loginname'] = "test-loginname";
			$userInfo['user_name'] = "test-username";
			$userInfo['user_email'] = "test-username@email.com";
			$userInfo['user_website'] = "www.test-site.com";		// This may not be defined
			$userInfo['user_id'] = 0;
			$userInfo['user_sess'] = "1234567890ABCDEFGHIJKLMNOP";
			$userInfo['activation_url'] = 'http://whereever.to.activate.com/';
		}

		return  e107::getSystemUser($userInfo['user_id'], false)->renderEmail('signup', $userInfo);


	}



	static function render_after_signup($error_message='')
	{

		$ret = array();

		if(!empty($error_message))
		{
			$ret['text'] = "<div class='alert alert-danger'>".$error_message."</b></div>";	// Just display the error message
			$ret['caption'] = LAN_SIGNUP_99; // Problem Detected
			return $ret;
		}

		global $pref, $allData, $adviseLoginName, $tp;

		$srch = array("[sitename]","[email]","{NEWLOGINNAME}","{EMAIL}");
		$repl = array(SITENAME,"<b>".$allData['data']['user_email']."</b>",$allData['data']['user_loginname'],$allData['data']['user_email']);

		$text = "<div class='alert alert-warning'>";

		if (isset($pref['signup_text_after']) && (strlen($pref['signup_text_after']) > 2))
		{
			$text .= str_replace($srch, $repl, $tp->toHTML($pref['signup_text_after'], TRUE, 'parse_sc,defs'))."<br />";
			// keep str_replace() outside of toHTML to allow for search/replace of dynamic terms within 'defs'.
		}
		else
		{
			$text .= (intval($pref['user_reg_veri']) === 2) ?  LAN_SIGNUP_37 : str_replace($srch,$repl, LAN_SIGNUP_72);
			$text .= "<br /><br />".$adviseLoginName;
		}

		$text .= "</div>";

		$caption_arr = array();
		$caption_arr[0] = LAN_SIGNUP_73; // Thank you!  (No Approval).
		$caption_arr[1] = LAN_SIGNUP_98; // Confirm Email (Email Confirmation)
		$caption_arr[2] = LAN_SIGNUP_100; // Approval Pending (Admin Approval)

		$mode = (int) $pref['user_reg_veri'];

		$caption = $caption_arr[$mode];

		$ret['text']    = $text;
		$ret['caption'] = $caption;

		return $ret;

	}



}


