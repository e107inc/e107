<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User signup
 * 
 */

require_once("class2.php");

if(vartrue($_POST['email2'])) // spam-trap. 
{
	exit; 	
}

$qs = explode(".", e_QUERY);

if($qs[0] != 'activate')
{   // multi-language fix.
	e107::coreLan('signup'); 
	//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
//	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_usersettings.php");		Shouldn't need this now
}

e107::coreLan('user'); // Generic user-related language defines

define('SIGNUP_DEBUG', FALSE);

e107::js('core', 'jquery.mailcheck.min.js','jquery',2);

include_once(e_HANDLER.'user_extended_class.php');
$usere = new e107_user_extended;

require_once(e_HANDLER.'validator_class.php');
// require_once(e_HANDLER.'user_handler.php');
$userMethods = e107::getUserSession();
$userMethods->deleteExpired();				// Delete time-expired partial registrations

require_once(e107::coreTemplatePath('signup')); //correct way to load a core template.

$signup_shortcodes = e107::getScBatch('signup');
// $facebook_shortcodes = e107::getScBatch('facebook',TRUE);

$signup_imagecode = ($pref['signcode'] && extension_loaded('gd'));
$text = '';
$extraErrors = array();
$error = FALSE;

//-------------------------------
// Resend Activation Email
//-------------------------------
if((e_QUERY == 'resend') && !USER && ($pref['user_reg_veri'] == 1))
{
	require_once(HEADERF);

	$clean_email = $tp->toDB($_POST['resend_email']);
    if(!check_email($clean_email))
	{
		$clean_email = "xxx";
	}

    $new_email = $tp->toDB(varset($_POST['resend_newemail'], ''));
    if(!check_email($new_email ))
	{
    	$new_email = FALSE;
	}

	if($_POST['submit_resend'])
	{	// Action user's submitted information
		// 'resend_email' - user name or email address actually used to sign up
		// 'resend_newemail' - corrected email address
		// 'resend_password' - password (required if changing email address)

		if($_POST['resend_email'] && !$new_email && $clean_email && $sql->gen("SELECT * FROM #user WHERE user_ban=0 AND user_sess='' AND (`user_loginname`= '".$clean_email."' OR `user_name` = '".$clean_email."' OR `user_email` = '".$clean_email."' ) "))
		{	// Account already activated
			$ns->tablerender(LAN_SIGNUP_40,LAN_SIGNUP_41."<br />");
			require_once(FOOTERF);
			exit();
		}


		// Start by looking up the user
		if(!$sql->select("user", "*", "(`user_loginname` = '".$clean_email."' OR `user_name` = '".$clean_email."' OR `user_email` = '".$clean_email."' ) AND `user_ban`=".USER_REGISTERED_NOT_VALIDATED." AND `user_sess` !='' LIMIT 1"))
		{
			message_handler("ALERT",LAN_SIGNUP_64.': '.$clean_email); // email (or other info) not valid.
			require_once(FOOTERF);
			exit();
		}
		$row = $sql -> fetch();
		// We should have a user record here

		if(trim($_POST['resend_password']) !="" && $new_email)
		{  // Need to change the email address - check password to make sure
			if ($userMethods->CheckPassword($_POST['resend_password'], $row['user_loginname'], $row['user_password']) === TRUE)
			{
				if ($sql->select('user', 'user_id, user_email', "user_email='".$new_email."'"))
				{	// Email address already used by someone
					message_handler("ALERT",LAN_SIGNUP_106); 	// Duplicate email
					require_once(FOOTERF);
					exit();
				}
				if($sql->update("user", "user_email='".$new_email."' WHERE user_id = '".$row['user_id']."' LIMIT 1 "))
				{
					$row['user_email'] = $new_email;
				}
			}
			else
			{
				message_handler("ALERT",LAN_SIGNUP_52); // Incorrect Password.
				require_once(FOOTERF);
				exit();
			}
		}

		// Now send the email - got some valid info
		$row['user_password'] = 'xxxxxxx';		// Don't know the real one
		$eml = render_email($row);
		$eml['e107_header'] = $row['user_id'];
		require_once(e_HANDLER.'mail.php');
		$mailer = new e107Email();

		if(!$mailer->sendEmail(USEREMAIL, USERNAME, $eml, FALSE))

		$do_log['signup_action'] = LAN_SIGNUP_63;

		if(!sendemail($row['user_email'], $eml['subject'], $eml['message'], $row['user_name'], "", "", $eml['attachments'], $eml['cc'], $eml['bcc'], $returnpath, $returnreceipt,$eml['inline-images']))
		{
			$ns->tablerender(LAN_ERROR,LAN_SIGNUP_42);
			$do_log['signup_result'] = LAN_SIGNUP_62;
		}
		else
		{
			$ns->tablerender(LAN_SIGNUP_43,LAN_SIGNUP_44." ".$row['user_email']." - ".LAN_SIGNUP_45."<br /><br />");
			$do_log['signup_result'] = LAN_SIGNUP_61;
		}
		// Now log this (log will ignore if its disabled)
		$admin_log->user_audit(USER_AUDIT_PW_RES,$do_log,$row['user_id'],$row['user_name']);
		require_once(FOOTERF);
		exit;
	}
	elseif(!$_POST['submit_resend'])
	{	
		// Display form to get info from user
		$text .= "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?resend' id='resend_form' autocomplete='off'>
		<table style='".USER_WIDTH."' class='fborder'>
		<tr>
			<td class='forumheader3' style='text-align:right'>".LAN_SIGNUP_48."</td>
        <td class='forumheader3'>
		<input type='text' name='resend_email' class='tbox' size='50' style='max-width:80%' value='' maxlength='80' />
		</td>
		</tr>

		<tr>
			<td class='forumheader3' colspan='2'>".LAN_SIGNUP_49."</td>
		</tr>
		<tr>
			<td class='forumheader3' style='text-align:right;width:30%'>".LAN_SIGNUP_50."</td>
			<td class='forumheader3'><input type='text' name='resend_newemail' class='tbox' size='50' style='max-width:80%' value='' maxlength='80' /></td>
		</tr>
		<tr>
			<td class='forumheader3' style='text-align:right'>".LAN_SIGNUP_51."</td>
			<td class='forumheader3'><input type='text' name='resend_password' class='tbox' size='50' style='max-width:80%' value='' maxlength='80' /></td>
		</tr>

		";

		$text .="<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
		$text .= "<input class='btn btn-default button' type='submit' name='submit_resend' value=\"".LAN_SIGNUP_47."\" />";  // resend activation email.
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns->tablerender(LAN_SIGNUP_47, $text);
		require_once(FOOTERF);
		exit;
	}
    exit;
}

// ------------------------------------------------------------------

if(!$_POST)
{
	$error = '';
	$text = ' ';
	$password1 = '';
	$password2 = '';
	$email = '';				// Used in shortcodes
	$loginname = '';
	$realname = '';
	$image = '';
	$avatar_upload = '';
	$photo_upload = '';
	$_POST['ue'] = '';
	$signature = '';
}


if(ADMIN && (e_QUERY == 'preview' || e_QUERY == 'test'  || e_QUERY == 'preview.aftersignup'))
{
	if(e_QUERY == "preview.aftersignup")
	{
		require_once(HEADERF);

        $allData['data']['user_email'] = "example@email.com";
		$allData['data']['user_loginname'] = "user_loginname";

	  	$after_signup = render_after_signup($error_message);

		$ns->tablerender($after_signup['caption'], $after_signup['text']);
		require_once(FOOTERF);
		exit;
	}

	$temp = array();
	$eml = render_email($temp, TRUE); // It ignores the data, anyway
	echo $eml['preview'];

	if(e_QUERY == 'test')
	{
		require_once(e_HANDLER.'mail.php');
		$mailer = new e107Email();

		if(!$mailer->sendEmail(USEREMAIL, USERNAME, $eml, FALSE))
		{
			echo "<br /><br /><br /><br >&nbsp;&nbsp;>> ".LAN_SIGNUP_42; // there was a problem.
		}
		else
		{
			echo "<br /><br />&nbsp;&nbsp;>> ".LAN_SIGNUP_43." [ ".USEREMAIL." ] - ".LAN_SIGNUP_45;
		}
	}
	exit;
}

// FIXME - strange HTML output in browser
if ($pref['membersonly_enabled'])
{
	$HEADER = "<div style='text-align:center; width:100%;margin-left:auto;margin-right:auto;text-align:center'><div style='width:70%;text-align:center;margin-left:auto;margin-right:auto'><br />";
	if (file_exists(THEME."images/login_logo.png"))
	{
		$HEADER .= "<img src='".THEME_ABS."images/login_logo.png' alt='' />\n";
	}
	else
	{
		$HEADER .= "<img src='".e_IMAGE_ABS."logo.png' alt='' />\n";
	}
	$HEADER .= '<br />';
	$FOOTER = '</div></div>';
}

/*
if($signup_imagecode)
{
	// require_once(e_HANDLER."secure_img_handler.php");
	// $sec_img = new secure_image;
}
*/

if ((USER || (intval($pref['user_reg']) !== 1) || (vartrue($pref['auth_method'],'e107') != 'e107')) && !getperms('0'))
{
	 header('location: '.e_HTTP.'index.php');
	
}

if(getperms('0')) // allow main admin to view signup page for design/testing. 
{
	//$mes = e107::getMessage();
	//$mes->debug("You are currently logged in.");
	
	$adminMsg = LAN_SIGNUP_112;
	
	if(intval($pref['user_reg']) !== 1)
	{
		$adminMsg .= "<br />User registration is currently disabled";	
	}
	
	$SIGNUP_BEGIN = "<div class='alert alert-block alert-error alert-danger text-center'>".$adminMsg."</div>". $SIGNUP_BEGIN;	
	unset($adminMsg); 	
}


//----------------------------------------
// After clicking the activation link
//----------------------------------------
if (e_QUERY)
{
	$qs = explode('.', e_QUERY);
	if ($qs[0] == 'activate' && (count($qs) == 3 || count($qs) == 4) && $qs[2])
	{
		// FIXME TODO use generic multilanguage selection => e107::coreLan(); 
		// return the message in the correct language.
		if(isset($qs[3]) && strlen($qs[3]) == 2 )
		{
			require_once(e_HANDLER.'language_class.php');
			$slng = new language;
			$the_language = $slng->convert($qs[3]);
			if(is_readable(e_LANGUAGEDIR.$the_language.'/lan_'.e_PAGE))
			{
				include(e_LANGUAGEDIR.$the_language.'/lan_'.e_PAGE);
			}
			else
			{
				include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
			}
		}
		else
		{
				include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
		}


		e107::getCache()->clear("online_menu_totals");
		if ($sql->select("user", "*", "user_sess='".$tp->toDB($qs[2], true)."' "))
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
				
				if($newID === FALSE)
				{
					$admin_log->e_log_event(10,debug_backtrace(),'USER','Verification Fail',print_r($row,TRUE),FALSE,LOG_TO_ROLLING);
					require_once(HEADERF);
					$ns->tablerender(LAN_SIGNUP_75, LAN_SIGNUP_101);
					require_once(FOOTERF);
					exit;
				}

				// Log to user audit log if enabled
				$admin_log->user_audit(USER_AUDIT_EMAILACK,$row);

				e107::getEvent()->trigger('userveri', $row);			// Legacy event
				e107::getEvent()->trigger('userfull', $row);			// 'New' event
				
				if (varset($pref['autologinpostsignup']))
				{
					require_once(e_HANDLER.'login.php');
					$usr = new userlogin();
					$usr->login($row['user_loginname'], md5($row['user_name'].$row['user_password'].$row['user_join']), 'signup', '');
				}

				require_once(HEADERF);
				$text = LAN_SIGNUP_74." <a href='index.php'>".LAN_SIGNUP_22."</a> ".LAN_SIGNUP_23."<br />".LAN_SIGNUP_24." ".SITENAME;
				$ns->tablerender(LAN_SIGNUP_75, $text);
				require_once(FOOTERF);
				exit;
			}
		}
		else
		{	
			// Invalid activation code
			header("location: ".e_BASE."index.php");
			exit;
		}
	}
}


//----------------------------------------
// 		Initial signup (registration)

if (isset($_POST['register']) && intval($pref['user_reg']) === 1) 
{	
	e107::getCache()->clear("online_menu_totals");
	
	if (isset($_POST['rand_num']) && $signup_imagecode)
	{	
		if ($badCodeMsg = e107::getSecureImg()->invalidCode($_POST['rand_num'], $_POST['code_verify'])) // better: allows class to return the error. 
		{
			//$extraErrors[] = LAN_SIGNUP_3."\\n";
			$extraErrors[] = $badCodeMsg."\\n";
			$error = TRUE;
		}
	}

	if($invalid = e107::getEvent()->trigger("usersup_veri", $_POST))
	{
    	$extraErrors[] = $invalid."\\n";
        $error = TRUE;
	}

	if (!$error)
	{
		if (vartrue($pref['predefinedLoginName']))
		{
		  $_POST['loginname'] = $userMethods->generateUserLogin($pref['predefinedLoginName']);
		}

		if(!isset($_POST['hideemail'])) // For when it is disabled - default is to hide-email.  
		{
			$_POST['hideemail'] = 1;
		}
		
		if(!isset($_POST['email_confirm']))
		{
			$_POST['email_confirm'] = $_POST['email'];	
		}
			
			
		// Use LoginName for DisplayName if restricted
		if (!check_class($pref['displayname_class'],e_UC_PUBLIC.','.e_UC_MEMBER))
		{
			$_POST['username'] = $_POST['loginname'];
		}

		// Now validate everything
		$allData = validatorClass::validateFields($_POST,$userMethods->userVettingInfo, TRUE);		// Do basic validation
		validatorClass::checkMandatory('user_name,user_loginname', $allData);						// Check for missing fields (email done in userValidation() )
		validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', 0);		// Do basic DB-related checks
		$userMethods->userValidation($allData);														// Do user-specific DB checks
		
		if (!isset($allData['errors']['user_password']))
		{	
			// No errors in password - keep it outside the main data array
			$savePassword = $allData['data']['user_password'];
			unset($allData['data']['user_password']); // Delete the password value in the output array
		}

		unset($_POST['password1']); // Restrict the scope of this
		unset($_POST['password2']);

		$allData['user_ip'] = e107::getIPHandler()->getIP(FALSE);


		// check for multiple signups from the same IP address. But ignore localhost
		if ($allData['user_ip'] != e107::LOCALHOST_IP)
		{
			if($ipcount = $sql->select('user', '*', "user_ip='".$allData['user_ip']."' and user_ban !='2' "))
			{
				if($ipcount >= $pref['signup_maxip'] && trim($pref['signup_maxip']) != "")
				{
					$allData['errors']['user_email'] = ERR_GENERIC;
					$allData['errortext']['user_email'] =  LAN_SIGNUP_71;
					e107::getLog()->add('USET_15',LAN_SIGNUP_103.e107::getIPHandler()->getIP(FALSE), 4);
				}
			}
		}

		// Email address confirmation.
		if (!isset($allData['errors']['user_email']))
		{	// Obviously nothing wrong with the email address so far (or maybe its not required)
			if ($_POST['email'] != $_POST['email_confirm'])
			{
				$allData['errors']['user_email'] = ERR_GENERIC;
				$allData['errortext']['user_email'] =  LAN_SIGNUP_38;
				unset($allData['data']['user_email']);
			}
		}


		// Verify Custom Signup options if selected - need specific loop since the need for them is configuration-dependent
		$signup_option_title = array(LAN_USER_63, LAN_USER_71, LAN_USER_72, LAN_USER_73, LAN_USER_74);
		$signup_option_names = array('realname', 'signature', 'image', 'class', 'customtitle');

		foreach($signup_option_names as $key => $value)
		{
			if ($pref['signup_option_'.$value] == 2 && !isset($alldata['data']['user_'.$value]) && !isset($alldata['errors']['user_'.$value]))
			{
				$alldata['errors']['user_'.$value] = ERR_GENERIC;
				$alldata['errortext']['user_'.$value] = str_replace('--SOMETHING--',$signup_option_title[$key],LAN_USER_75);
			}
		}


		// Validate Extended User Fields.
		$eufVals = array();
		//if (isset($_POST['ue']))
		{
			$eufVals = $usere->userExtendedValidateAll(varset($_POST['ue'], array()), varset($_POST['hide'],array()), TRUE); // Validate the extended user fields
		}


		// Determine whether we have an error
		$error = ((isset($allData['errors']) && count($allData['errors'])) || (isset($eufVals['errors']) && count($eufVals['errors'])) || count($extraErrors));
		
		// All validated here - handle any errors
		if ($error) //FIXME - this ignores the errors caused by invalid image-code. 
		{
			$temp = array();
			if (count($extraErrors))
			{
				$temp[] = implode('<br />', $extraErrors);
			}
			if (count($allData['errors']))
			{
				$temp[] = validatorClass::makeErrorList($allData,'USER_ERR_','%n - %x - %t: %v', '<br />', $userMethods->userVettingInfo);
			}
			if (vartrue($eufVals['errors']))
			{
				$temp[] = validatorClass::makeErrorList($eufVals,'USER_ERR_','%n - %t: %v', '<br />');
			}


			if(deftrue('BOOTSTRAP'))
			{
				e107::getMessage()->addError(implode('<br />', $temp)); 
			}
			else
			{
				message_handler('P_ALERT', implode('<br />', $temp));	
			}
	
		}
	}		// End of data validation
	else
	{
		if(deftrue('BOOTSTRAP'))
		{
			e107::getMessage()->addError(implode('<br />', $temp)); 
		}
		else
		{
			message_handler('P_ALERT', implode('<br />', $extraErrors));	// Workaround for image-code errors. 
		}
		
	}


	// ========== End of verification.. ==============
	// If no errors, we can enter the new member in the DB
	// At this point we have two data arrays:
	//		$allData['data'] - the 'core' user data
	//		$eufVals['data'] - any extended user fields

	if (!$error)
	{
		$error_message = '';
		$fp = new floodprotect;
		if ($fp->flood("user", "user_join") == FALSE)
		{
			header("location:".e_BASE."index.php");
			exit;
		}

		if ($_POST['email'] && $sql->select("user", "*", "user_email='".$_POST['email']."' AND user_ban='".USER_BANNED."'"))
		{
			exit;
		}


		$u_key = e_user_model::randomKey();		// Key for signup completion
		$allData['data']['user_sess'] = $u_key;	// Validation key

		$userMethods->userClassUpdate($allData['data'], 'usersup');

		if ($pref['user_reg_veri'])
		{
			$allData['data']['user_ban'] = USER_REGISTERED_NOT_VALIDATED;
		}
		else
		{
			$allData['data']['user_ban'] = USER_VALIDATED;
		}
		
		// Work out data to be written to user audit trail
		$signup_data = array('user_name', 'user_loginname', 'user_email', 'user_ip');
//		foreach (array() as $f)
		foreach ($signup_data as $f)
		{
			$signup_data[$f] = $allData['data'][$f]; // Just copy across selected fields
		}

		$allData['data']['user_password'] = $userMethods->HashPassword($savePassword,$allData['data']['user_loginname']);
		
		if (vartrue($pref['allowEmailLogin']))
		{  // Need to create separate password for email login
			//$allData['data']['user_prefs'] = serialize(array('email_password' => $userMethods->HashPassword($savePassword, $allData['data']['user_email'])));
			$allData['data']['user_prefs'] = e107::getArrayStorage()->serialize(array('email_password' => $userMethods->HashPassword($savePassword, $allData['data']['user_email'])));
		}

		$allData['data']['user_join'] = time();
		$allData['data']['user_ip'] = e107::getIPHandler()->getIP(FALSE);
		
		if(!vartrue($allData['data']['user_name']))
		{
			$allData['data']['user_name'] = $allData['data']['user_loginname'];	
			$signup_data['user_name'] = $allData['data']['user_loginname'];
		} 
		
		// The user_class, user_perms, user_prefs, user_realm fields don't have default value,
		//   so we put apropriate ones, otherwise - broken DB Insert
		$allData['data']['user_class'] = '';
		$allData['data']['user_perms'] = '';
		$allData['data']['user_prefs'] = '';
		$allData['data']['user_realm'] = '';

		// Actually write data to DB
		validatorClass::addFieldTypes($userMethods->userVettingInfo, $allData);
		
		$nid = $sql->insert('user', $allData);
		
		if (isset($eufVals['data']) && count($eufVals['data']))
		{
			$usere->addFieldTypes($eufVals);		// Add in the data types for storage
			$eufVals['WHERE'] = '`user_extended_id` = '.intval($nid);
			//$usere->addDefaultFields($eufVals);		// Add in defaults for anything not explicitly set (commented out for now - will slightly modify behaviour)
			$sql->gen("INSERT INTO `#user_extended` (user_extended_id) values ('{$nid}')");
			$sql->update('user_extended', $eufVals);
		}
		
		if (SIGNUP_DEBUG)
		{
			 $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Signup new user",array_merge($allData['data'],$eufVals) ,FALSE,LOG_TO_ROLLING);
		}

		// Log to user audit log if enabled
		$signup_data['user_id'] = $nid;
		$signup_data['signup_key'] = $u_key;
		$signup_data['user_realname'] = $tp->toDB($_POST['realname']);

		$admin_log->user_audit(USER_AUDIT_SIGNUP,$signup_data);

		if (!$nid)
		{
			require_once(HEADERF);
			$ns->tablerender("", LAN_SIGNUP_36);
			require_once(FOOTERF);
		}

		$adviseLoginName = '';
		if (vartrue($pref['predefinedLoginName']) && (integer) $pref['allowEmailLogin'] === 0)
		{
			$adviseLoginName = LAN_SIGNUP_65.': '.$allData['data']['user_loginname'].'<br />'.LAN_SIGNUP_66.'<br />';
		}

		// Verification required (may be by email or by admin)
		if ($pref['user_reg_veri'])
		{	
			// ========== Send Email =========>
			if (($pref['user_reg_veri'] != 2) && $allData['data']['user_email'])		// Don't send if email address blank - means that its not compulsory
			{
				$allData['data']['user_id'] = $nid;					// User ID
				// FIXME build while rendering - user::renderEmail()
				$allData['data']['activation_url'] = SITEURL."signup.php?activate.".$allData['data']['user_id'].".".$allData['data']['user_sess'];
				// FIX missing user_name
				if(!vartrue($allData['data']['user_name'])) $allData['data']['user_name'] = $allData['data']['user_login'];
				
				// prefered way to send user emails
				$sysuser = e107::getSystemUser(false, false);
				$sysuser->setData($allData['data']);
				$sysuser->setId($userid);
				$check = $sysuser->email('signup', array(
					'user_password' => $savePassword, // for security reasons - password passed ONLY through options
				));
				
				/*
                $eml = render_email($allData['data']);
				$eml['e107_header'] = $eml['userid'];
				require_once(e_HANDLER.'mail.php');
				$mailer = new e107Email();
				
				// FIX - sendEmail returns TRUE or error message...
				$check = $mailer->sendEmail($allData['data']['user_email'], $allData['data']['user_name'], $eml,FALSE);*/
				
				if(true !== $check)
				{
					$error_message = LAN_SIGNUP_42; // There was a problem, the registration mail was not sent, please contact the website administrator.
				}
				unset($allData['data']['user_password']);
			}

			e107::getEvent()->trigger('usersup', $_POST);  // Old trigger - send everything in the template, including extended fields.
			// FIXME - undocummented feature - userpartial trigger (better trigger name?)
			e107::getEvent()->trigger('userpartial', array_merge($allData['data'],$eufVals['data']));  // New trigger - send everything in the template, including extended fields.

			require_once(HEADERF);

			$after_signup = render_after_signup($error_message);
			$ns->tablerender($after_signup['caption'], $after_signup['text']);

			require_once(FOOTERF);
			exit;
		}
		// User can be signed up immediately
		else
		{	
			require_once(HEADERF);

			if(!$sql->select("user", "user_id", "user_loginname='".$allData['data']['user_loginname']."' AND user_password='".$allData['data']['user_password']."'"))
			{	
				// Error looking up newly created user
				$ns->tablerender("", LAN_SIGNUP_36);
				require_once(FOOTERF);
				exit;
			}

			// Set initial classes, and any which the user can opt to join
			if ($init_class = $userMethods->userClassUpdate($row, 'userpartial'))
			{
				$allData['data']['user_class'] = $init_class;
				$user_class_update = $sql->update("user", "user_class = '{$allData['data']['user_class']}' WHERE user_name='{$allData['data']['user_name']}'");
				
				if($user_class_update === FALSE)
				{
					//$admin_log->e_log_event(10,debug_backtrace(),'USER','Userclass update fail',print_r($row,TRUE),FALSE,LOG_TO_ROLLING);
					require_once(HEADERF);
					$ns->tablerender(LAN_SIGNUP_75, LAN_SIGNUP_101);
					require_once(FOOTERF);
					exit;
				}
			}	

			e107::getEvent()->trigger('usersup', $_POST);  // send everything in the template, including extended fields.
			e107::getEvent()->trigger('userfull', array_merge($allData['data'],$eufVals['data']));  // New trigger - send everything in the template, including extended fields.

			if (isset($pref['signup_text_after']) && (strlen($pref['signup_text_after']) > 2))
			{
				$text = $tp->toHTML(str_replace('{NEWLOGINNAME}', $loginname, $pref['signup_text_after']), TRUE, 'parse_sc,defs')."<br />";
			}
			else
			{
				$text = LAN_SIGNUP_76."&nbsp;".SITENAME.", ".LAN_SIGNUP_12."<br /><br />".LAN_SIGNUP_13;
			}
			
			$ns->tablerender(LAN_SIGNUP_8,$text);
			require_once(FOOTERF);
			exit;
		}
	}		// End - if (!$error)
	else
	{	// 'Recirculate' selected values so they are retained on the form when an error occurs
		foreach (array('user_class') as $a)
		{
			$signupData[$a] = $tp->toForm(varset($allData['data'][$a],''));
		}
	}
}

// Disable the signup form - if either there was an error, or starting from scratch
require_once(HEADERF);

$qs = ($error ? "stage" : e_QUERY);
if ($pref['use_coppa'] == 1 && strpos($qs, "stage") === FALSE)
{
	$text = $tp->parseTemplate($COPPA_TEMPLATE, TRUE, $signup_shortcodes);
	$ns->tablerender(LAN_SIGNUP_78, $text);
	require_once(FOOTERF);
	exit;
}


if ($qs == 'stage1' && $pref['use_coppa'] == 1)
{
	if(isset($_POST['newver']))
	{
		if(!vartrue($_POST['coppa']))
		{
			$text = $tp->parseTemplate($COPPA_FAIL);
			$ns->tablerender(LAN_SIGNUP_78, $text);
			require_once(FOOTERF);
			exit;
		}
	}
	else
	{
  		header('Location: '.e_BASE.'signup.php');
		exit;
	}
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$text = $tp->parseTemplate($SIGNUP_BEGIN.$SIGNUP_BODY.$SIGNUP_END, TRUE, $signup_shortcodes);
$ns->tablerender(LAN_SIGNUP_79, e107::getMessage()->render('default', true).$text);
require_once(FOOTERF);
exit;



//----------------------------------
// Function returns an image if a field is required.
function req($field)
{
	return ($field == 2 ? REQUIRED_FIELD_MARKER : "");
}
//----------------------------------

function headerjs()
{
	$script_txt = "
	<script type=\"text/javascript\">
	function addtext3(sc){
		document.getElementById('signupform').image.value = sc;
	}

	function addsig(sc){
		document.getElementById('signupform').signature.value += sc;
	}
	function help(help){
		document.getElementById('signupform').helpb.value = help;
	}
	</script>\n";

	//global $cal; // XXX - can this be removed completely?
	//$script_txt .= $cal->load_files();
	return $script_txt;
}


/**
 * Create email to send to user who just registered.
 * @param array $userInfo is the array of user-related DB variables
 * @return array of data for mailer - field names directly compatible
 */
function render_email($userInfo, $preview = FALSE)
{

	if($preview == TRUE)
	{
		$userInfo['user_password'] = "test-password";
		$userInfo['user_loginname'] = "test-loginname";
		$userInfo['user_name'] = "test-username";
		$userInfo['user_email'] = "test-username@email";
		$userInfo['user_website'] = "www.test-site.com";		// This may not be defined
		$userInfo['user_id'] = 0;
		$userInfo['user_sess'] = "1234567890ABCDEFGHIJKLMNOP";
		$userInfo['user_email'] = 'cameron@teslapower.solar';
		$userInfo['activation_url'] = 'http://whereever.to.activate.com/';
	}
	
	return  e107::getSystemUser($userInfo['user_id'], false)->renderEmail('signup', $userInfo);
	
	
	
	/*
	
	global $pref,$SIGNUPEMAIL_LINKSTYLE,$SIGNUPEMAIL_SUBJECT,$SIGNUPEMAIL_TEMPLATE;
	 * 
	define('RETURNADDRESS', (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$userInfo['user_id'].".".$userInfo['user_sess'] : SITEURL."/signup.php?activate.".$userInfo['user_id'].".".$userInfo['user_sess'].".".e_LAN));
	$pass_show = ($pref['user_reg_secureveri'])? '*******' : $userInfo['user_password'];

	if (file_exists(THEME.'email_template.php'))
	{
		require_once(THEME.'email_template.php');
	}
	else
	{
		require_once(e_CORE.'templates/email_template.php');
	}

	$ret['mail_recipient_id'] = $userInfo['user_id'];
	if (vartrue($SIGNUPEMAIL_CC)) { $ret['mail_copy_to'] = $SIGNUPEMAIL_CC; }
	if (vartrue($SIGNUPEMAIL_BCC)) { $ret['mail_bcopy_to'] = $SIGNUPEMAIL_BCC; }
	if (vartrue($SIGNUPEMAIL_ATTACHMENTS)) { $ret['mail_attach'] = $SIGNUPEMAIL_ATTACHMENTS; }

	$style = ($SIGNUPEMAIL_LINKSTYLE) ? "style='{$SIGNUPEMAIL_LINKSTYLE}'" : "";

	$search[0] = '{LOGINNAME}';
	$replace[0] = intval($pref['allowEmailLogin']) === 0 ? $userInfo['user_loginname'] : $userInfo['user_email'];

	$search[1] = '{PASSWORD}';
	$replace[1] = $pass_show;

	$search[2] = '{ACTIVATION_LINK}';
	$replace[2] = "<a href='".RETURNADDRESS."' {$style}>".RETURNADDRESS."</a>";

	$search[3] = '{SITENAME}';
	$replace[3] = SITENAME;

	$search[4] = '{SITEURL}';
	$replace[4] = "<a href='".SITEURL."' {$style}>".SITEURL."</a>";

	$search[5] = '{USERNAME}';
	$replace[5] = $userInfo['user_name'];

	$search[6] = '{USERURL}';
	$replace[6] = vartrue($userInfo['user_website']) ? $userInfo['user_website'] : "";



	$subject = str_replace($search,$replace,$SIGNUPEMAIL_SUBJECT);
	$ret['mail_subject'] =  $subject;
	$ret['send_html'] = TRUE;

	$HEAD = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
	$HEAD .= "<html xmlns='http://www.w3.org/1999/xhtml' >\n";
	$HEAD .= "<head><meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
	$HEAD .= ($SIGNUPEMAIL_USETHEME == 1) ? "<link rel=\"stylesheet\" href=\"".SITEURL.THEME."style.css\" type=\"text/css\" />\n" : "";
    $HEAD .= ($preview) ? "<title>".LAN_SIGNUP_58."</title>\n" : "";
	if($SIGNUPEMAIL_USETHEME == 2)
	{
		$CSS = file_get_contents(THEME."style.css");
		$HEAD .= "<style>\n".$CSS."\n</style>";
	}

	$HEAD .= "</head>\n";
	if(vartrue($SIGNUPEMAIL_BACKGROUNDIMAGE))
	{
		$HEAD .= "<body background=\"".$SIGNUPEMAIL_BACKGROUNDIMAGE."\" >\n";
	}
	else
	{
		$HEAD .= "<body>\n";
	}
	$FOOT = "\n</body>\n</html>\n";

	$ret['mail_body'] = str_replace($search,$replace,$HEAD.$SIGNUPEMAIL_TEMPLATE.$FOOT);
	$ret['preview'] = $ret['mail_body'];												// Non-standard field

	return $ret;

	 */
}



function render_after_signup($error_message)
{
	global $pref, $allData, $adviseLoginName, $tp;

	$srch = array("[sitename]","[email]","{NEWLOGINNAME}","{EMAIL}");
	$repl = array(SITENAME,"<b>".$allData['data']['user_email']."</b>",$allData['data']['user_loginname'],$allData['data']['user_email']);

	if (isset($pref['signup_text_after']) && (strlen($pref['signup_text_after']) > 2))
	{
		$text = str_replace($srch, $repl, $tp->toHTML($pref['signup_text_after'], TRUE, 'parse_sc,defs'))."<br />";
		// keep str_replace() outside of toHTML to allow for search/replace of dynamic terms within 'defs'.
	}
	else
	{
		$text = ($pref['user_reg_veri'] == 2) ?  LAN_SIGNUP_37 : str_replace($srch,$repl, LAN_SIGNUP_72);
		$text .= "<br /><br />".$adviseLoginName;
	}

	$caption_arr = array();
	$caption_arr[0] = LAN_SIGNUP_73; // Thank you!  (No Approval).
	$caption_arr[1] = LAN_SIGNUP_98; // Confirm Email (Email Confirmation)
	$caption_arr[2] = LAN_SIGNUP_100; // Approval Pending (Admin Approval)

    $caption = $caption_arr[$pref['user_reg_veri']];

	if($error_message)
	{
		$text = "<br /><b>".$error_message."</b><br />";	// Just display the error message
        $caption = LAN_SIGNUP_99; // Problem Detected
	}

    $ret['text'] = $text;
    $ret['caption'] = $caption;
	return $ret;


}
?>
