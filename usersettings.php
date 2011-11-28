<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User settings modify
 *
 * $Source: /cvs_backup/e107_0.8/usersettings.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
/*
Notes:
Uses $udata initially, later curVal to hold current user data
Admin log events:
USET_01 - admin changed user data
*/

if(is_numeric($_SERVER["QUERY_STRING"]))
{
	$eplug_admin = TRUE;
}
require_once ('class2.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

define("US_DEBUG",FALSE);
//define('US_DEBUG', false);


if (!USER)
{	// Must be logged in to change settings
	header('location:'.e_BASE.'index.php');
	exit();
}

if ((!ADMIN || !getperms("4")) && e_QUERY && e_QUERY != "update" )
{
	header('location:'.e_BASE.'usersettings.php');
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user.php');		// Generic user-related language defines
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_usersettings.php');

require_once (e_HANDLER.'ren_help.php');
require_once (e_HANDLER.'user_extended_class.php');
// require_once (e_HANDLER.'user_handler.php');
require_once(e_HANDLER.'validator_class.php');
$ue = new e107_user_extended;

$userMethods = e107::getUserSession();

require_once (e_HANDLER.'ren_help.php');
include_once (e107::coreTemplatePath('usersettings')); //correct way to load a core template.
include_once (e_CORE.'shortcodes/batch/usersettings_shortcodes.php');

require_once (e_HANDLER.'calendar/calendar_class.php');
$cal = new DHTML_Calendar(true);

// include JS, headerjs not called in header anymore
$js = e107::getJs();
$js->footerInline(headerjs());

$photo_to_delete = '';
$avatar_to_delete = '';
$ue_fields = '';
$promptPassword = false;
$error = FALSE;
$extraErrors = array();
$eufVals = array();
$savePassword = '';


$inp = USERID;			// Initially assume that user is modifying their own data.
$_uid = false;			// FALSE if user modifying their own data; otherwise ID of data being modified
$adminEdit = FALSE;		// FALSE if editing own data. TRUE if admin edit
if(is_numeric(e_QUERY))
{	// Trying to edit specific user record
	if (ADMIN)
	{	// Admin edit of specific record
		$inp = (int)e_QUERY;
		$_uid = $inp;
		$info = get_user_data($inp);
		//Only site admin is able to change setting for other admins
		if(!is_array($info) || ($info['user_admin'] == 1 && (!defined('ADMINPERMS') || ADMINPERMS !== '0')) || ((!defined('ADMINPERMS') || ADMINPERMS !== '0') && !getperms('4')))
		{
			header('location:'.e_BASE.'index.php');
			exit();
		}
		$adminEdit = TRUE;		// Flag to indicate admin edit
	}
	else
	{
		//Non admin attempting to edit another user's ID
		header('location:'.e_BASE.'index.php');
		exit();
	}

}
if($adminEdit) // try to stay in Admin when admin is editing.
{
	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
	require_once (e_ADMIN."auth.php");
}
else
{
	require_once (HEADERF);
}


// Save user settings (changes only)
//-----------------------------------

if (isset($_POST['updatesettings']) || isset($_POST['SaveValidatedInfo']))
{
	$udata = get_user_data($inp);				// Get all the existing user data, including any extended fields
	$udata['user_classlist'] = $userMethods->addCommonClasses($udata, FALSE);
}


if (isset($_POST['updatesettings']))
{
	if (!varsettrue($pref['auth_method']))
	{
		$pref['auth_method'] = 'e107';
	}

	if ($pref['auth_method'] != 'e107')
	{
		$_POST['password1'] = '';
		$_POST['password2'] = '';
	}

	// Uploaded avatar and/or photo
	if ($file_userfile['error'] != 4)
	{
		require_once (e_HANDLER.'upload_handler.php');
		require_once (e_HANDLER.'resize_handler.php');

		if ($uploaded = process_uploaded_files(e_UPLOAD.'avatars/', 'prefix+ap_'.$udata['user_id'].'_', array('overwrite' => TRUE, 'file_mask'=>'jpg,png,gif', 'max_file_count' => 2)))
		{
			foreach ($uploaded as $upload)
			{
				if ($upload['name'] && ($upload['index'] == 'avatar') && $pref['avatar_upload'])
				{
					// avatar uploaded - give it a reference which identifies it as server-stored
					// Vetting routines will do resizing and so on
					$_POST['image'] = '-upload-'.$upload['name'];
				}
				elseif ($upload['name'] && ($upload['index'] == 'photo') && $pref['photo_upload'])
				{
					// photograph uploaded
					$_POST['user_sess'] = $upload['name'];
				}
				elseif (isset($upload['error']) && isset($upload['message']))
				{
					$extraErrors[] = $upload['message'];
				}
			}
		}
	}


	// Now validate everything - just check everything that's been entered
	$allData = validatorClass::validateFields($_POST,$userMethods->userVettingInfo, TRUE);		// Do basic validation
	validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', $inp);		// Do basic DB-related checks
	$userMethods->userValidation($allData);														// Do user-specific DB checks

	$savePassword = '';

	if (($_POST['password1'] != '') || ($_POST['password2'] != ''))
	{	// Need to validate new password here
		if (!isset($allData['errors']['user_password']))
		{	// No errors in password yet - may be valid
			$savePassword = $allData['data']['user_password'];
			unset($allData['data']['user_password']);		// Delete the password value in the output array
		}
	}
	else
	{
		unset($allData['errors']['user_password']);		// Delete the error which an empty password field generates
	}

	unset($_POST['password1']);
	unset($_POST['password2']);


	$changedUserData = validatorClass::findChanges($allData['data'], $udata,FALSE);


	// Login Name checks - only admin can change login name
	if (isset($changedUserData['user_loginname']))
	{
		if (ADMIN && getperms('4'))
		{
			if (!check_class($pref['displayname_class'], $udata['user_classlist'], $adminEdit))
			{	// Display name and login name must be the same
				$changedUserData['user_name'] = $changedUserData['user_loginname'];
			}
		}
		else
		{
			unset($changedUserData['user_loginname']);		// Just doing this is probably being kind!
			$alldata['errors']['user_loginname'] = ERR_GENERIC;
			$alldata['errortext']['user_loginname'] = LAN_USER_85;
		}
	}


	// See if user just wants to delete existing photo
	if (isset($_POST['user_delete_photo']))
	{
	  $photo_to_delete = $udata['user_sess'];
	  $changedUserData['user_sess'] = '';
	}


	if ($udata['user_image'] && !isset($changedUserData['user_image']))
	{
		$changedUserData['user_image'] = '';
		$avatar_to_delete = str_replace('-upload-', '', $udata['user_image']);
	}



    // Validate Extended User Fields.
	$changedEUFData = array();
	if (isset($_POST['ue']))
	{
		$eufVals = $ue->userExtendedValidateAll($_POST['ue'], varset($_POST['hide'],TRUE));		// Validate the extended user fields
		$changedEUFData['data'] = validatorClass::findChanges($eufVals['data'], $udata,FALSE);
	}


	// Determine whether we have an error
	$error = ((isset($allData['errors']) && count($allData['errors'])) || (isset($eufVals['errors']) && count($eufVals['errors'])) || count($extraErrors));


	// Update Userclass - only if its the user changing their own data (admins can do it another way)
	if (isset($allData['data']['user_class']))
	{
		unset($changedUserData['user_class']);		// We always recalculate this
		if (FALSE === $adminEdit)
		{	// Make sure admin can't edit another's user classes
			if (!is_object($e_userclass))
			{
				$e_userclass = new user_class;
			}
			$ucList = $e_userclass->get_editable_classes(USERCLASS_LIST,TRUE);	 // List of classes which this user can edit
			if (count($ucList))
			{
				$nid = $e_userclass->mergeClassLists($udata['user_class'], $ucList, $allData['data']['user_class'], TRUE);
				$nid = $e_userclass->stripFixedClasses($nid);
				$nid = implode(',',$nid);
				//	echo "Userclass data - new: {$nid}, old: {$udata['user_baseclasslist']}, editable: ".implode(',',$ucList).", entered: {$allData['data']['user_class']}<br />";
				if ($nid != $udata['user_baseclasslist'])
				{
					if (US_DEBUG)
					{
						$admin_log->e_log_event(10, debug_backtrace(), "DEBUG", "Usersettings test", "Write back classes; old list: {$udata['user_class']}; new list: ".$nid, false, LOG_TO_ROLLING);
					}
					$changedUserData['user_class'] = $nid;
				}
			}
		}
	}


	// All key fields validated here
	// -----------------------------
	// $inp - UID of user whose data is being changed (may not be the currently logged in user)
	if (!$error && count($changedUserData) || count($changedEUFData))
	{
		$_POST['user_id'] = intval($inp);
		$ret = $e_event->trigger('preuserset', $_POST);

		if ($ret == '')
		{

		// Only admins can update login name - do this just in case one of the event triggers has mucked it about
			if (!(ADMIN && getperms('4')))
			{
				unset($changedUserData['user_loginname']);
			}
		}
		else
		{	// Invalid data - from hooked in trigger event
			$message = "<div style='text-align:center'>".$ret."</div>";
			$caption = LAN_OK;
			$error = TRUE;
		}
	}
}  // End - update setttings
elseif (isset($_POST['SaveValidatedInfo']))
{	// Next bit only valid if user editing their own data
	if (!$adminEdit && !empty($_POST['updated_data']) && !empty($_POST['currentpassword']) && !empty($_POST['updated_key']))
	{	// Got some data confirmed with password entry
		$new_data = base64_decode($_POST['updated_data']);
		if (md5($new_data) != $_POST['updated_key'])
		{  // Should only happen if someone's fooling around
			echo 'Mismatch on validation key<br />';
			exit();
		}

		if (isset($_POST['updated_extended']))
		{
			$new_extended = base64_decode($_POST['updated_extended']);
			if (md5($new_extended) != $_POST['extended_key'])
			{  // Should only happen if someone's fooling around
				echo 'Mismatch on validity key<br />';
				exit();
			}
		}

		if ($userMethods->CheckPassword($_POST['currentpassword'], $udata['user_loginname'], $udata['user_password']) === false) // Use old data to validate

		{  // Invalid password
			echo "<br />".LAN_USET_22."<br />";
			if(!$adminEdit)
			{
				require_once (FOOTERF);
			}
			else
			{
            	require_once(e_ADMIN."footer.php");
			}
			exit();
		}
		$changedUserData = unserialize($new_data);
		$savePassword = $_POST['currentpassword'];
		if (!empty($new_extended)) $changedEUFData = unserialize($new_extended);
		unset($new_data);
		unset($new_extended);
		if (isset($changedUserData['user_sess']))
		{
			$photo_to_delete = $udata['user_sess'];
		}
		if (isset($changedUserData['user_image']))
		{
			$avatar_to_delete = $udata['user_image'];
		}
	}
}
unset($_POST['updatesettings']);
unset($_POST['SaveValidatedInfo']);


// At this point we know the error status.
// $changedUserData has an array of core changed data, except password, which is in $savePassword if changed (or entered as confirmation).
// $eufData has extended user field data
// $changedEUFData has any changes in extended user field data
$dataToSave = !$error && (isset($changedUserData) && count($changedUserData)) || (isset($changedEUFData['data']) && count($changedEUFData['data'])) || $savePassword;

if ($dataToSave)
{
	// Sort out password hashes
	if ($savePassword)
	{
		$loginname = $changedUserData['user_loginname'] ? $changedUserData['user_loginname'] : $udata['user_loginname'];
		$email = (isset($changedUserData['user_email']) && $changedUserData['user_email']) ? $changedUserData['user_email'] : $udata['user_email'];
		$changedUserData['user_password'] = $sql->escape($userMethods->HashPassword($savePassword, $loginname), false);
		if (varset($pref['allowEmailLogin'], FALSE))
		{
			$user_prefs = unserialize($udata['user_prefs']);
			$user_prefs['email_password'] = $userMethods->HashPassword($savePassword, $email);
			$changedUserData['user_prefs'] = serialize($user_prefs);
		}
	}
	else
	{
		if ((isset($changedUserData['user_loginname']) && $userMethods->isPasswordRequired('user_loginname'))
			|| (isset($changedUserData['user_email']) && $userMethods->isPasswordRequired('user_email')))
		{
			if ($_uid)
			{	// Admin is changing it
				$error = LAN_USET_20;
			}
			else
			{	// User is changing their own info
				$promptPassword = true;
			}
		}
	}
}

if ($dataToSave && !$promptPassword)
{
	$message = LAN_USET_41;

	// We can update the basic user record now - can just update fields from $changedUserData
	if (US_DEBUG) { $admin_log->e_log_event(10, debug_backtrace(), "DEBUG", "Usersettings test", "Changed data:<br /> ".var_export($changedUserData, true), false, LOG_TO_ROLLING); }
	if (isset($changedUserData) && count($changedUserData))
	{
		$changedData['data'] = $changedUserData;
		$changedData['WHERE'] = 'user_id='.intval($inp);
		validatorClass::addFieldTypes($userMethods->userVettingInfo,$changedData);

		//print_a($changedData);
		if (FALSE === $sql->db_Update('user', $changedData))
		{
			$message .= '<br />Error updating user data';
		}
		else
		{
			if (isset($changedUserData['user_password']) && !$adminEdit)
			{
			//	echo "Make new cookie<br />";
				$userMethods->makeUserCookie(array('user_id' => $udata['user_id'],'user_password' => $changedUserData['user_password']), FALSE);		// Can't handle autologin ATM
			}
		}
	}


	// Save extended field values
	if (isset($changedEUFData['data']) && count($changedEUFData['data']))
	{
		$ue->addFieldTypes($changedEUFData);				// Add in the data types for storage
		$changedEUFData['WHERE'] = '`user_extended_id` = '.intval($inp);

		//print_a($changedEUFData);
		// ***** Next line creates a record which presumably should be there anyway, so could generate an error
		$sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id, user_hidden_fields) values ('".intval($inp)."', '')");
		if (!$sql->db_Update('user_extended', $changedEUFData))
		{
			$message .= '<br />Error updating EUF';
		}
	}

	// Now see if we need to log anything. First check the options and class membership
	// (Normally we would leave logging decision to the log class. But this one's a bit more complicated)
	$user_logging_opts = array_flip(explode(',', varset($pref['user_audit_opts'], '')));
	$do_log = array();
	$log_action = '';
	if ($_uid)
	{		// Its an admin changing someone elses data - make an admin log entry here
		$admin_log->log_event('USET_01', "UID: {$udata['user_id']}. UName: {$udata['user_name']}", E_LOG_INFORMATIVE);
		// Check against the class of the target user, not the admin!
		if (!check_class(varset($pref['user_audit_class'], ''), $udata['user_class'])) { $user_logging_opts = array(); }
	}
	else
	{
		if (!check_class(varset($pref['user_audit_class'], ''))) { $user_logging_opts = array(); }
	}

	$triggerData = array();
	if (count($changedUserData))
	{
		$triggerData = $changedUserData;		// Create record for changed user data trigger
		$triggerData['user_id'] = $udata['user_id'];
		$triggerData['_CHANGED_BY_UID'] = USERID;		// May be admin changing data
		$triggerData['_CHANGED_BY_UNAME'] = USERNAME;
		if (!isset($triggerData['user_name'])) { $triggerData['user_name'] = $udata['user_name']; }
	}

	// Now log changes if required
	if (count($user_logging_opts))
	{
		// Start with any specific fields we're changing
		if (isset($changedUserData['user_name']))
		{
			if (isset($user_logging_opts[USER_AUDIT_NEW_DN]))
			{
				$do_log['user_name'] = $changedUserData['user_name'];
				$log_action = USER_AUDIT_NEW_DN;
			}
			unset($changedUserData['user_name']);
		}

		if (isset($changedUserData['user_password']))
		{
			if (isset($user_logging_opts[USER_AUDIT_NEW_PW]))
			{	// Password has already been changed to a hashed value, so OK to leave the data
				$do_log['user_password'] = $changedUserData['user_password'];
				$log_action = USER_AUDIT_NEW_PW;
			}
			unset($changedUserData['user_password']);
		}

		if (isset($changedUserData['user_email']))
		{
			if (isset($user_logging_opts[USER_AUDIT_NEW_EML]))
			{
				$do_log['user_email'] = $changedUserData['user_email'];
				$log_action = USER_AUDIT_NEW_EML;
			}
			unset($changedUserData['user_email']);
		}

		if (count($changedUserData) && isset($user_logging_opts[USER_AUDIT_NEW_SET]))
		{
			$do_log = array_merge($do_log, $changedUserData);
			$log_action = USER_AUDIT_NEW_SET;
		}
		if (count($do_log))
		{  // Got some changes to audit
			//			echo "Adding to audit log<br />";
			if ($_uid)
			{
				$log_action = USER_AUDIT_ADMIN;						// If an admin did the mod, different heading
				// Embed a message saying who changed the data
				$changedUserData['message'] = str_replace(array('--ID--', '--LOGNAME--'), array(USERID, USERNAME), LAN_USET_18);
				$admin_log->user_audit($log_action, $do_log, $udata['user_id'], $udata['user_loginname']);
			}
			else
			{
				if (count($do_log) > 1) { $log_action = USER_AUDIT_NEW_SET; } // Log multiple entries to one record
				$admin_log->user_audit($log_action, $do_log);
			}
		}
	}	// End of audit logging


	// Now tidy up
	if ($photo_to_delete)
	{	// Photo may be a flat file, or in the database
		delete_file($photo_to_delete);
	}
	if ($avatar_to_delete)
	{	// Avatar may be a flat file, or in the database
		delete_file($avatar_to_delete);
	}

		// If user has changed display name, update the record in the online table
	if (isset($changedUserData['user_name']) && !$_uid)
	{
		$sql->db_Update('online', "online_user_id = '".USERID.".".$changedUserData['user_name']."' WHERE online_user_id = '".USERID.".".USERNAME."'");
	}




	$e_event->trigger('postuserset', $_POST);
	if (count($triggerData))
	{
		$e_event->trigger('userdatachanged', $triggerData);
	}

	if (e_QUERY == 'update')
	{
		header('Location: index.php');
	}
	
	if(isset($USERSETTINGS_MESSAGE))
	{
		$message = str_replace("{MESSAGE}",$message,$USERSETTINGS_MESSAGE);			
	}
	else // backwards compatible
	{
		$message = "<div style='text-align:center'>".$message.'</div>';
		
	}
	
	$caption = (isset($USERSETTINGS_MESSAGE_CAPTION)) ? $USERSETTINGS_MESSAGE_CAPTION : LAN_OK;	
	
}	// End - if (!$error)...


if (!$error && !$promptPassword) 
{
	if(isset($_POST) && $changedUserData['user_name'])
	{
		$redirect = e107::getRedirect();
		$url = e107::getUrl();
		$to = $_uid ? $url->create('user/profile/edit', array('id' => $_uid, 'name' => $changedUserData['user_name'])) : $url->create('user/myprofile/edit');
		if($message) e107::getMessage()->addSuccess($message, 'default', true);
		$redirect->redirect($to);
	}
	unset($_POST);
}


if ($error)
{
	require_once (e_HANDLER.'message_handler.php');
	$temp = array();
	if (count($extraErrors))
	{
		$temp[] = implode('<br />', $extraErrors);
	}
	if (count($allData['errors']))
	{
		$temp[] = validatorClass::makeErrorList($allData,'USER_ERR_','%n - %x - %t: %v', '<br />', $userMethods->userVettingInfo);
	}
	if (varsettrue($eufVals['errors']))
	{
		$temp[] = '<br />'.validatorClass::makeErrorList($eufVals,'USER_ERR_','%n - %x - %t: %v', '<br />', NULL);
	}
	message_handler('P_ALERT', implode('<br />', $temp));
//	$adref = $_POST['adminreturn'];
}


// --- User data has been updated here if appropriate ---
$testSessionMessage = e107::getMessage()->get(E_MESSAGE_SUCCESS, 'default', true); // only success in the session
if($testSessionMessage) $message = implode('<br />', $testSessionMessage); // we got raw message - array
if (isset($message))
{
	$ns->tablerender($caption, $message);
}


$uuid = ($_uid ? $_uid : USERID); // If $_uid is set, its an admin changing another user's data


if ($promptPassword)
{	// User has to enter password to validate data
	$updated_data = serialize($changedUserData);
	$validation_key = md5($updated_data);
	$updated_data = base64_encode($updated_data);
	$updated_extended = serialize($changedEUFData);
	$extended_key = md5($updated_extended);
	$updated_extended = base64_encode($updated_extended);
	$text = "<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : '')."'>
		<table><tr><td style='text-align:center'>";
	foreach ($_POST as $k => $v)
	{
		if (is_array($v))
		{
			foreach ($v as $sk => $sv)
			{
				$text .= "<input type='hidden' name='{$k}[{$sk}]' value='{$sv}' />\n";
			}
		}
		else
		{
			$text .= "<input type='hidden' name='{$k}' value='{$v}' />\n";
		}
	}
	$text .= LAN_USET_21."</td></tr><tr><td>&nbsp;</td></tr>
		<tr><td style='text-align:center'><input type='password' name='currentpassword' value='' size='30' />";
	$text .= "<input type='hidden' name='updated_data' value='{$updated_data}' /><input type='hidden' name='updated_key' value='{$validation_key}' />
		<input type='hidden' name='updated_extended' value='{$updated_extended}' /><input type='hidden' name='extended_key' value='{$extended_key}' />
		</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td style='text-align:center'><input type='submit' name='SaveValidatedInfo' value='".LAN_ENTER."' /></td></tr>
		</table>
		</form>";
	$ns->tablerender(LAN_USET_39, $text);
    			if(!$adminEdit)
			{
				require_once (FOOTERF);
			}
			else
			{
            	require_once(e_ADMIN."footer.php");
			}
}


//--------------------------------------------------------
// Re-read the user data into curVal (ready for display)
//--------------------------------------------------------

$qry = "
SELECT u.*, ue.* FROM #user AS u
LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
WHERE u.user_id=".intval($uuid);

$sql->db_Select_gen($qry);
$curVal=$sql->db_Fetch(MYSQL_ASSOC);
$curVal['user_class'] = varset($changedUserData['user_class'], $curVal['user_class']);
$curVal['userclass_list'] = $userMethods->addCommonClasses($curVal, FALSE);

if ($_POST)
{     // Fix for all the values being lost when there was an error in a field - restore from the latest $_POST values
	  // (Password fields have intentionally been cleared). If no error, there's an unset($_POST) to disable this block
	foreach ($_POST as $key => $val)
	{
		if ($key != 'class') { $curVal['user_'.$key] = $tp->post_toForm($val); }
	}
	foreach ($_POST['ue'] as $key => $val)
	{
		$curVal[$key] = $tp->post_toForm($val);
	}
}

require_once (e_HANDLER."form_handler.php");
$rs = new form;

$text = (e_QUERY ? $rs->form_open("post", e_SELF."?".e_QUERY, "dataform", "", " enctype='multipart/form-data'") : $rs->form_open("post", e_SELF, "dataform", "", " enctype='multipart/form-data'"));

if (e_QUERY == "update")
{
	$text .= "<div class='fborder' style='text-align:center'><br />".str_replace("*", "<span class='required'>*</span>", LAN_USET_9)."<br />".LAN_USET_10."<br /><br /></div>";
}

$text .= $tp->parseTemplate($USERSETTINGS_EDIT, TRUE, $usersettings_shortcodes);


$text .= "<div><input type='hidden' name='_uid' value='{$uuid}' /></div>
	</form>
	";

$caption = (isset($USERSETTINGS_EDIT_CAPTION)) ? $USERSETTINGS_EDIT_CAPTION : LAN_USET_39; // 'Update User Settings'	

$ns->tablerender($caption, $text);

if(!$adminEdit)
{
	require_once (FOOTERF);
}
else
{
   	require_once(e_ADMIN."footer.php");
}




// If a field is required, returns a red asterisk
function req($field)
{
	$ret = "";
	if ($field == 2)
	{
		$ret = "<span class='required'> *</span>";
	}
	return $ret;
}



// Delete a file from the public directories. Return TRUE on success, FALSE on failure.
// Also deletes from database if appropriate.
function delete_file($fname, $dir = 'avatars/')
{
	global $sql;
	$fname = trim($fname);
	if (!$fname) return false;

	if (preg_match("#Binary (.*?)/#", $fname, $match))
	{
		return $sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
	}
	elseif (file_exists(e_UPLOAD.$dir.$fname))
	{
		unlink(e_UPLOAD.$dir.$fname);
		return true;
	}
	return false;
}


function headerjs()
{
	//global $cal;
	$script = "
		function addtext_us(sc)
		{
			document.getElementById('dataform').image.value = sc;
		}
	";

	//$script .= $cal->load_files();
	return $script;
}

function usersettings_adminmenu()
{
		include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
		
		if ($action == '')
		{
			$action = 'main';
		}
		// ##### Display options
		$var = array();
		$var ['main']['text'] = LAN_USER_LIST;
		$var ['main']['link'] = e_ADMIN.'users.php';
		$var ['main']['perm'] = '4';
		$var ['create']['text'] = LAN_USER_QUICKADD;
		$var ['create']['link'] = e_ADMIN.'users.php?create';
		$var ['create']['perm'] = '4|U1';
		$var ['prune']['text'] = LAN_USER_PRUNE;
		$var ['prune']['link'] = e_ADMIN.'users.php?prune';// Will be moved to "Schedule tasks"
		$var ['prune']['perm'] = '4';
		$var ['options']['text'] = LAN_OPTIONS;
		$var ['options']['link'] = e_ADMIN.'users.php?options';
		$var ['options']['perm'] = '4|U2';	
		$var ['ranks']['text'] = LAN_USER_RANKS;
		$var ['ranks']['link'] = e_ADMIN.'users.php?ranks';
		$var ['ranks']['perm'] = '4|U3';	
		//  $var['mailing']['text']= USRLAN_121;
		//   $var['mailing']['link']="mailout.php";
		show_admin_menu(USRLAN_76,$action,$var);
}
?>
