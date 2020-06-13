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

	$bcLans = array(
	"LAN_7"=> "LAN_SIGNUP_89", // "Display Name: ");
	"LAN_8"=> "LAN_SIGNUP_90", // "the name that will be displayed on site");
	"LAN_9"=> "LAN_SIGNUP_81", // "Username: ");
	"LAN_10"=> "LAN_SIGNUP_82", // "the name that you use to login");
	"LAN_17"=> "LAN_SIGNUP_83", // "Password: ");
	"LAN_109"=> "LAN_SIGNUP_77", // "This site complies with The Children's Online Privacy Protection Act of 1998 (COPPA) and as such cannot accept registrations from users under the age of 13 without a written permission document from their parent or guardian. For more information you can read the legislation");
	"LAN_111"=> "LAN_SIGNUP_84", // "Re-type Password: ");
	"LAN_112"=> "LAN_USER_60",  // "Email Address: ");
	"LAN_113"=> "LAN_USER_83", // "Hide email address?: ");
	"LAN_120"=> "LAN_USER_71", // "Signature: ");
	"LAN_121"=> "LAN_SIGNUP_94", // "Avatar: ");
	"LAN_122"=> "", // "Timezone:");
	"LAN_123"=> "LAN_SIGNUP_79", // "Register");
	"LAN_308"=> "LAN_SIGNUP_91", // "Real Name: ");
	"LAN_309"=> "LAN_SIGNUP_80", // "Please enter your details below.");
	"LAN_400"=> "LAN_SIGNUP_85", // "Usernames and passwords are <b>case-sensitive</b>.");
	"LAN_410"=> "LAN_SIGNUP_95", // "Enter code visible in the image");
	);

e107::getLanguage()->bcDefs($bcLans); // Backward compatibility fix.


define('SIGNUP_DEBUG', FALSE);

e107::js('core', 'jquery.mailcheck.min.js','jquery',2);

include_once(e_HANDLER.'user_extended_class.php');
$usere = new e107_user_extended;

require_once(e_HANDLER.'validator_class.php');
// require_once(e_HANDLER.'user_handler.php');
$userMethods = e107::getUserSession();
$userMethods->deleteExpired();				// Delete time-expired partial registrations


$SIGNUP_BEGIN = null;
$SIGNUP_BODY = null;
$SIGNUP_END  = null;
$COPPA_TEMPLATE = null;
$COPPA_FAIL = null;

require_once(e107::coreTemplatePath('signup')); //correct way to load a core template.
$signup_shortcodes = e107::getScBatch('signup');
// $facebook_shortcodes = e107::getScBatch('facebook',TRUE);

$signup_imagecode = ($pref['signcode'] && extension_loaded('gd'));
$text = '';
$extraErrors = array();
$error = FALSE;

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



if (!empty($pref['membersonly_enabled']))
{
	$template = e107::getCoreTemplate('membersonly','signup');
	define('e_IFRAME',true);
	define('e_IFRAME_HEADER', $template['header'] );
	define('e_IFRAME_FOOTER', $template['footer'] );
	unset($template);

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
	e107::redirect();
	
}


//----------------------------------------
// After clicking the activation link
//----------------------------------------
require_once(e_HANDLER."e_signup_class.php");

if(e_QUERY && e_QUERY != 'stage1')
{
	require_once(HEADERF);
	$suObj = new e_signup;
	$suObj->run();
	require_once(FOOTERF);
	exit;
}





//----------------------------------------
// 		Initial signup (registration)
// TODO - move all of this into the class above.
if (isset($_POST['register']) && intval($pref['user_reg']) === 1) 
{	
	e107::getCache()->clear("online_menu_totals");
	
	if ($signup_imagecode)
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

		// generate password if passwords are disabled and email validation is enabled.
		$noPasswordInput = e107::getPref('signup_option_password', 2); //0 = generate it.
		if(empty($noPasswordInput) && !isset($_POST['password1']) && $this->pref['user_reg_veri'] === 1)
		{
			$_POST['password1'] = $userMethods->generateRandomString("#*******#");
			$_POST['password2'] = $_POST['password1'];
		}

		// posted class subscription - check it's only from the public classes.
		if(!empty($_POST['class']))
		{
			$publicClasses = e107::getUserClass()->get_editable_classes(e_UC_PUBLIC, true);
			$_POST['class'] = array_intersect($publicClasses, $_POST['class']);
			unset($publicClasses);
		}

		// Now validate everything
		$allData = validatorClass::validateFields($_POST,$userMethods->userVettingInfo, TRUE);		// Do basic validation
		validatorClass::checkMandatory('user_name,user_loginname', $allData);						// Check for missing fields (email done in userValidation() )
		validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', 0);		// Do basic DB-related checks
		$userMethods->userValidation($allData);


		$savePassword = null;

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
		if ($allData['user_ip'] != e107::LOCALHOST_IP && $allData['user_ip'] != e107::LOCALHOST_IP2)
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
				$alldata['errortext']['user_'.$value] = str_replace('[x]',$signup_option_title[$key],LAN_USER_75);
			}
		}


		// Validate Extended User Fields.
		$eufVals = array();
		if (isset($_POST['ue']))
		{
			$eufVals = $usere->sanitizeAll($_POST['ue']);
			$eufVals = $usere->userExtendedValidateAll(varset($eufVals, array()), varset($_POST['hide'],array()), TRUE); // Validate the extended user fields
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
			e107::getMessage()->addError(implode('<br />', $extraErrors));
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
			e107::redirect();
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
			$allData['data']['user_prefs'] = e107::serialize(array('email_password' => $userMethods->HashPassword($savePassword, $allData['data']['user_email'])));
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

		if(empty($allData['data']['user_class']))
		{
			$allData['data']['user_class'] = '';
		}

		$allData['data']['user_perms'] = '';
		$allData['data']['user_prefs'] = '';
		$allData['data']['user_realm'] = '';

		if(empty($allData['data']['user_signature']))
		{
			$allData['data']['user_signature'] = ''; // as above - default required in MYsQL strict mode.
		}


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
		
	//	if (SIGNUP_DEBUG)
	//	{
		//	 $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Signup new user",array_merge($allData['data'],$eufVals) ,FALSE,LOG_TO_ROLLING);
	//	}

		// Log to user audit log if enabled
		$signup_data['user_id'] = $nid;
		$signup_data['signup_key'] = $u_key;
		$signup_data['user_realname'] = $tp->toDB($_POST['realname']);

		$admin_log->user_audit(USER_AUDIT_SIGNUP,$signup_data);

		if (!$nid)
		{
			require_once(HEADERF);
			$message = e107::getMessage()->addError(LAN_SIGNUP_36)->render();
			$ns->tablerender("", $message);

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
			if (((int) $pref['user_reg_veri'] !== 2) && $allData['data']['user_email'])		// Don't send if email address blank - means that its not compulsory
			{
				$allData['data']['user_id'] = $nid;					// User ID
				// FIXME build while rendering - user::renderEmail()
				$allData['data']['activation_url'] = SITEURL."signup.php?activate.".$allData['data']['user_id'].".".$allData['data']['user_sess'];
				// FIX missing user_name
				if(!vartrue($allData['data']['user_name'])) $allData['data']['user_name'] = $allData['data']['user_login'];
				
				// prefered way to send user emails

				if(getperms('0') && !empty($_POST['simulation']))
				{
					$simulation = true;
					$check = true; //removes error message below.
				}
				else
				{
					$simulation = false;
				}

				if($simulation !== true) // Alow logged in main-admin to test signup procedure.
				{
					$sysuser = e107::getSystemUser(false, false);
					$sysuser->setData($allData['data']);
					$sysuser->setId($nid);
					$check = $sysuser->email('signup', array(
						'user_id'       => $nid,
						'user_password' => $savePassword, // for security reasons - password passed ONLY through options
					));
				}

				if(getperms('0'))
				{
					e107::getMessage()->addDebug(print_a($allData,true));
					e107::getMessage()->addDebug("Password: <b>".$savePassword."</b>");
				}

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
			e107::getEvent()->trigger('userpartial', array_merge($allData['data'],$eufVals['data']));  // New trigger - send everything in the template, including extended fields.
			e107::getEvent()->trigger('user_signup_submitted', $_POST);


			require_once(HEADERF);

			$after_signup = e_signup::render_after_signup($error_message);
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
				$user_class_update = $sql->update("user", "user_class = '{$allData['data']['user_class']}' WHERE user_name='{$allData['data']['user_name']}' LIMIT 1");
				
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
				$text = LAN_SIGNUP_76."&nbsp;".SITENAME.", ".LAN_SIGNUP_12."<br /><br />";
				$text .= str_replace(array('[',']'), array("<a href='".e_LOGIN."'>", "</a>"), LAN_SIGNUP_13);
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
	$ns->tablerender(LAN_SIGNUP_78, $text, 'coppa');
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
		e107::redirect();
		exit;
	}
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

// e107::getCoreTemplate('signup', 'signup');

$text = $tp->parseTemplate($SIGNUP_BEGIN.$SIGNUP_BODY.$SIGNUP_END, TRUE, $signup_shortcodes);
$ns->tablerender(LAN_SIGNUP_79, e107::getMessage()->render('default', true).$text, 'signup' );

require_once(FOOTERF);
exit;



//----------------------------------
// Function returns an image if a field is required.
function req($field)
{
	return ($field == 2 ? "<span class='required'></span>" : "");
}
//----------------------------------

function headerjs()
{
	return "
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

}