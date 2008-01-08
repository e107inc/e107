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
|     $Source: /cvs_backup/e107_0.8/usersettings.php,v $
|     $Revision: 1.20 $
|     $Date: 2008-01-08 22:24:14 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+

Notes:
Uses $udata initially, later curVal to hold current user data
Admin log events:
	USET_01 - admin changed user data

*/


require_once("class2.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."user_extended_class.php");
$ue = new e107_user_extended;

//define("US_DEBUG",TRUE);
define("US_DEBUG",FALSE);


if (!USER) 
{	// Must be logged in to change settings
  header("location:".e_BASE."index.php");
  exit;
}

if (!ADMIN && e_QUERY && e_QUERY != "update") 
{
  header("location:".e_BASE."usersettings.php");
  exit;
}

require_once(e_HANDLER."ren_help.php");

if(is_readable(THEME."usersettings_template.php"))
{
  include_once(THEME."usersettings_template.php");
}
else
{
  include_once(e_THEME."templates/usersettings_template.php");
}
include_once(e_FILE."shortcode/batch/usersettings_shortcodes.php");

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
$_uid = is_numeric(e_QUERY) ? intval(e_QUERY) : "";
$sesschange = '';						// Notice removal
$photo_to_delete = '';
$avatar_to_delete = '';
$changed_user_data = array();

require_once(HEADERF);


// Given an array of user data, return a comma separated string which includes public, admin, member classes etc as appropriate.
function addCommonClasses($udata)
{
  $tmp = array();
  if ($udata['user_class'] != "") $tmp = explode(",", $udata['user_class']);
  $tmp[] = e_UC_MEMBER;
  $tmp[] = e_UC_READONLY;
  $tmp[] = e_UC_PUBLIC;
  if($udata['user_admin'] == 1)
  {
	$tmp[] = e_UC_ADMIN;
  }
  if (strpos($udata['user_perms'],'0') === 0)
  {
	$tmp[] = e_UC_MAINADMIN;
  }
  return implode(",", $tmp);
}


// Save user settings (changes only)
//-----------------------------------
$error = "";

if (isset($_POST['updatesettings']))
{
	if(!varsettrue($pref['auth_method']) || $pref['auth_method'] == '>e107')
	{
	  $pref['auth_method'] = 'e107';
	}

	if($pref['auth_method'] != 'e107')
	{
	  $_POST['password1'] = '';
	  $_POST['password2'] = '';
	}


	if ($_uid && ADMIN)
	{	// Admin logged in and editing another user's settings - so editing a different ID
	  $inp = $_uid;
	  $remflag = TRUE;
	}
	else
	{	// Current user logged in - use their ID
	  $inp = USERID;
	}


	$udata = get_user_data($inp);				// Get all the existing user data, including any extended fields
	$udata['user_classlist'] = addCommonClasses($udata);

	$peer = ($inp == USERID ? false : true);
/*
	echo "<pre>";
	var_dump($udata);
	echo "</pre>";
*/


	// Check external avatar
	if ($_POST['image'])
	{
	  $_POST['image'] = str_replace(array('\'', '"', '(', ')'), '', $_POST['image']);   // these are invalid anyway, so why allow them? (XSS Fix)
	  if ($size = getimagesize($_POST['image']))
	  {
		$avwidth = $size[0];
		$avheight = $size[1];
		$avmsg = "";

		$pref['im_width'] = varsettrue($pref['im_width'], 120);
		$pref['im_height'] = varsettrue($pref['im_height'], 100);
		if ($avwidth > $pref['im_width']) 
		{
		  $avmsg .= LAN_USET_1." ({$avwidth})<br />".LAN_USET_2.": {$pref['im_width']}<br /><br />";
		}
		if ($avheight > $pref['im_height']) 
		{
		  $avmsg .= LAN_USET_3." ({$avheight})<br />".LAN_USET_4.": {$pref['im_height']}";
		}
		if ($avmsg) 
		{
		  $_POST['image'] = "";
		  $error = $avmsg;
		}
		else
		{
		  if ($_POST['image'] != $udata['user_image'])
		  {
			$changed_user_data['user_image'] = $_POST['image'];
		  }
		}
	  }
	  else
	  {  // Invalid image file - we could just put up a message
	  }
	}




	// The 'class' option doesn't really make sense to me, but left it for now
//	$signup_option_title = array(LAN_308, LAN_120, LAN_121, LAN_122);
//	$signup_option_names = array("realname", "signature", "image", "timezone");

	$signup_option_title = array(LAN_308, LAN_120, LAN_121, LAN_122, LAN_USET_6, LAN_USET_19);
	$signup_option_names = array("realname", "signature", "image", "timezone", "class", 'signup_option_customtitle');
	foreach($signup_option_names as $key => $value)
	{  // Check required signup fields
		if ($pref['signup_option_'.$value] == 2 && !$_POST[$value] && !$_uid)
		{
			$error .= LAN_SIGNUP_6.$signup_option_title[$key].LAN_SIGNUP_7."\\n";
		}
    }



// Login Name checks - only admin can change login name
	if (isset($_POST['loginname']) && ADMIN && getperms("4"))
	{  // Only check if its been edited
	  $loginname = trim(preg_replace('/&nbsp;|\#|\=|\$/', "", strip_tags($_POST['loginname'])));
	  if ($loginname != $_POST['loginname'])
	  {
		$error .= LAN_USET_13."\\n";
	  }
	  // Check if login name exceeds maximum allowed length
	  if (strlen($loginname) > varset($pref['loginname_maxlength'],30))
	  {
	    $error .= LAN_USET_14."\\n";
	  }
	  if ($udata['user_loginname']  != $loginname) 
	  {
	    $changed_user_data['user_loginname']  = $loginname; 
	  }
	  else 
	  {
		unset($loginname);
	  }
	}
	if (isset($loginname)) $_POST['loginname'] = $loginname; else unset($_POST['loginname']);			// Make sure no chance of the $_POST value staying set inappropriately



	// Display name checks 
	// If display name == login name, it has to meet the criteria for both login name and display name
//	echo "Check_class: {$pref['displayname_class']}; {$udata['user_classlist']}; {$peer}<br />";
	if (check_class($pref['displayname_class'], $udata['user_classlist'], $peer))
	{	// Display name can be different to login name - check display name if its been entered
	  if (isset($_POST['username']))
	  {
	    $username = trim(strip_tags($_POST['username']));
		$_POST['username'] = $username;
//		echo "Found new display name: {$username}<br />";
	  }
	}
	else
	{  // Display name and login name must be the same - check only if the login name has been changed
	  if (varsettrue($loginname)) $username = $loginname;
	}



	if (varsettrue($username))
	{
	  // Impose a minimum length on display name
	  if (strlen($username) < 2)
	  {
		$error .= LAN_USET_12."\\n";
	  }
	  if (strlen($username) > varset($pref['displayname_maxlength'],15))
	  {
		$error .= LAN_USET_15."\\n";
	  }

	  if(isset($pref['signup_disallow_text']))
	  {
		$tmp = explode(",", $pref['signup_disallow_text']);
		foreach($tmp as $disallow)
		{
		  if(stristr($username, trim($disallow)))
		  {
			$error .= LAN_USET_11."\\n";
		  }
	    }
	  }

	// Display Name exists.
	  if ($sql->db_Count("user", "(*)", "WHERE `user_name`='".$username."' AND `user_id` != '".intval($inp)."' "))
	  {
		$error .= LAN_USET_17;
	  }
	  if ($username != $udata['user_name']) $changed_user_data['user_name'] = $username;
	  unset($username);
	}



// Password checks
	if ($_POST['password1'] != $_POST['password2']) 
	{
	  $error .= LAN_105."\\n";
	}
	else
	{
	  if(trim($_POST['password1']) != "")
	  {
		if (strlen(trim($_POST['password1'])) < $pref['signup_pass_len']) 
		{
		  $error .= LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5."\\n";
		}
	    $changed_user_data['user_password'] = md5(trim($_POST['password1']));
	  }
	}


// Email address checks
	if (!varsettrue($pref['disable_emailcheck']))
	{
	  if (!check_email($_POST['email']))
	  {
		$error .= LAN_106."\\n";
	  }
	}

	// Check for duplicate of email address
	if ($sql->db_Select("user", "user_name, user_email", "user_email='".$tp -> toDB($_POST['email'])."' AND user_id !='".intval($inp)."' "))
	{
	  $error .= LAN_408."\\n";
	}

		
		
// Uploaded avatar and/or photo
	if ($file_userfile['error'] != 4)
	{
	  require_once(e_HANDLER."upload_handler.php");
	  require_once(e_HANDLER."resize_handler.php");

	  if ($uploaded = file_upload(e_FILE."public/avatars/", "avatar"))
	  {
		foreach ($uploaded as $upload)
		{	// Needs the latest upload handler (with legacy and 'future' interfaces) to work
		  if ($upload['name'] && ($upload['index'] == 'avatar') && $pref['avatar_upload'])
		  {
			// avatar uploaded - give it a reference which identifies it as server-stored
			$_POST['image'] = "-upload-".$upload['name'];
			if ($_POST['image'] != $udata['user_image'])
			{
			  $avatar_to_delete = str_replace("-upload-", "", $udata['user_image']);
//			  echo "Avatar change; deleting {$avatar_to_delete}<br />";
			  $changed_user_data['user_image'] = $_POST['image'];
			}

			if (!resize_image(e_FILE."public/avatars/".$upload['name'], e_FILE."public/avatars/".$upload['name'], "avatar"))
			{
			  unset($message);
			  $error .= RESIZE_NOT_SUPPORTED."\\n";
			  @unlink(e_FILE."public/avatars/".$upload['name']);
			  $_POST['image'] = '';
			  unset($changed_user_data['user_image']);
			}
		  }

		  if ($upload['name'] && ($upload['index'] == 'photo') && $pref['photo_upload'] )
		  {
			// photograph uploaded
			if ($udata['user_sess'] != $upload['name'])
			{
			  $photo_to_delete = $udata['user_sess'];
			  $changed_user_data['user_sess'] = $upload['name'];
			}

			if (!resize_image(e_FILE."public/avatars/".$upload['name'], e_FILE."public/avatars/".$upload['name'], 180))
			{
			  unset($message);
			  $error .= RESIZE_NOT_SUPPORTED."\\n";
			  @unlink(e_FILE."public/avatars/".$upload['name']);
			  unset($changed_user_data['user_sess']);
			}
		  }
		}
	  }
	}

// See if user just wants to delete existing photo
	if (isset($_POST['user_delete_photo']))
	{
	  $photo_to_delete = $udata['user_sess'];
	  $changed_user_data['user_sess'] = '';
//	  echo "Just delete old photo: {$photo_to_delete}<br />";
	}




    // Validate Extended User Fields.
	if($_POST['ue'])
	{
	  if($sql->db_Select('user_extended_struct'))	
	  {
		while($row = $sql->db_Fetch())
		{
		  $extList["user_".$row['user_extended_struct_name']] = $row;
		}
	  }

	  $ue_fields = "";
	  foreach($_POST['ue'] as $key => $val)
	  {
			$err = false;
			$parms = explode("^,^", $extList[$key]['user_extended_struct_parms']);
			$regex = $tp->toText($parms[1]);
			$regexfail = $tp->toText($parms[2]);
    		if(defined($regexfail)) {$regexfail = constant($regexfail);}
	  		if($val == '' && $extList[$key]['user_extended_struct_required'] == 1 && !$_uid)
			{
         		$error .= LAN_SIGNUP_6.($tp->toHtml($extList[$key]['user_extended_struct_text'],FALSE,"defs"))." ".LAN_SIGNUP_7."\\n";
	    		$err = TRUE;
			}
			if($regex != "" && $val != "")
			{
				if(!preg_match($regex, $val))
				{
               		$error .= $regexfail."\\n";
         			$err = TRUE;
	         	}
			}
			if(!$err)
			{
				$val = $tp->toDB($val);
				$ue_fields .= ($ue_fields) ? ", " : "";
				$ue_fields .= $key."='".$val."'";
				}
	  }
    }



// All key fields validated here
// -----------------------------

// $inp - UID of user whose data is being changed (may not be the currently logged in user)
	if (!$error)
	{
	  unset($_POST['password1']);
	  unset($_POST['password2']);
	  
	  
      $_POST['user_id'] = intval($inp);


	  $ret = $e_event->trigger("preuserset", $_POST);

	  if ($ret == '')
	  {
		// Either delete this block, or delete user_customtitle from the later loop for non-vetted fields
		$new_customtitle = "";
		if(isset($_POST['customtitle']) && ($pref['signup_option_customtitle'] || ADMIN))
		{
		  $new_customtitle = $tp->toDB($_POST['customtitle']);
		  if ($new_customtitle != $udata['user_customtitle']) $changed_user_data['user_customtitle'] = $new_customtitle;
		}


		// Extended fields - handle any hidden fields
		if($ue_fields)
		{
		  $hidden_fields = implode("^", array_keys($_POST['hide']));
		  if($hidden_fields != "")
		  {
			$hidden_fields = "^".$hidden_fields."^";
		  }
		  $ue_fields .= ", user_hidden_fields = '".$hidden_fields."'";
		}


		// Handle fields which are just transferred without vetting (but are subject to toDB() for exploit restriction)
		$copy_list = array('user_signature' => 'signature', 
							'user_login' => 'realname', 
							'user_email' => 'email',
							'user_timezone' => 'timezone',
							'user_hideemail' =>'hideemail',
							'user_xup' => 'user_xup');
		
		// Next list identifies numerics which might take a value of 0
		$non_text_list = array(
							'user_hideemail' =>'hideemail'
							);
		foreach ($copy_list as $k => $v)
		{
		  if (isset($_POST[$v]) && (trim($_POST[$v]) || isset($non_text_list[$k])))
		  {
		    $_POST[$v] = $tp->toDB(trim($_POST[$v]));
			if ($_POST[$v] != $udata[$k]) 
			{
			  $changed_user_data[$k] = $_POST[$v];
//			  echo "Changed {$k}, {$v} from {$udata[$k]} to {$_POST[$v]}<br />";
			}
		  }
		}


		// Update Userclass - only if its the user changing their own data (admins can do it another way)
		if (!$_uid)
		{
		  if (!is_object($e_userclass)) $e_userclass = new user_class;
		  $ucList = explode(',',$e_userclass->get_editable_classes());			// List of classes which this user can edit
		  if (count($ucList))
		  {
			if (US_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Usersettings test","Read editable list. Current user classes: ".$udata['user_class'],FALSE,LOG_TO_ROLLING);

			$cur_classes = explode(",", $udata['user_class']);			// Current class membership
			$newclist = array_flip($cur_classes);						// Array keys are now the class IDs

			// Update class list - we must take care to only change those classes a user can edit themselves 
			foreach ($ucList as $cid)
			{
			  if(!in_array($cid, $_POST['class']))
			  {
				unset($newclist[$cid]);
			  }
			  else
			  {
				$newclist[$cid] = 1;
			  }
			}
			$newclist = array_keys($newclist);
			$nid = implode(',', array_diff($newclist, array('')));
//			echo "Userclass data - new: {$nid}, old: {$udata['user_class']}<br />";
			if ($nid != $udata['user_class'])
			{
			  if (US_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Usersettings test","Write back classes; old list: {$udata['user_class']}; new list: ".$nid,FALSE,LOG_TO_ROLLING);
			  $changed_user_data['user_class'] = $nid;
			}
		  }
		}



		// Only admins can update login name - do this just in case one of the event triggers has mucked it about
		if (!(ADMIN && getperms("4")))
		{
		  unset($changed_user_data['user_loginname']);
		}


		// We can update the basic user record now - can just update fields from $changed_user_data
		if (US_DEBUG) $admin_log->e_log_event(10,debug_backtrace(),"DEBUG","Usersettings test","Changed data:<br> ".var_export($changed_user_data,TRUE),FALSE,LOG_TO_ROLLING);
		$sql->db_UpdateArray("user",$changed_user_data," WHERE user_id='".intval($inp)."' ");

		// Now see if we need to log anything. First check the options and class membership
		// (Normally we would leave logging decision to the log class. But this one's a bit more complicated)
		$user_logging_opts = array_flip(explode(',',varset($pref['user_audit_opts'],'')));
		$do_log = array();
		$log_action = '';
		if ($_uid)
		{		// Its an admin changing someone elses data - make an admin log entry here
		  $admin_log->log_event('LAN_ADMIN_LOG_001',"UID: {$udata['user_id']}. UName: {$udata['user_name']}",E_LOG_INFORMATIVE,'USET_01');
		  // Check against the class of the target user, not the admin!
		  if (!check_class(varset($pref['user_audit_class'],''),$udata['user_class'])) $user_logging_opts = array();
		}
		else
		{
		  if (!check_class(varset($pref['user_audit_class'],''))) $user_logging_opts = array();
		}
		
		// Now log changes if required
		if (count($user_logging_opts))
		{
			// Start with any specific fields we're changing

			if (isset($changed_user_data['user_name']))
			{
			  if (isset($user_logging_opts[USER_AUDIT_NEW_DN]))
			  {
				$do_log['user_name'] = $changed_user_data['user_name'];
				$log_action = USER_AUDIT_NEW_DN;
			  }
			  unset($changed_user_data['user_name']);
			}

			if (isset($changed_user_data['user_password']))
			{
			  if (isset($user_logging_opts[USER_AUDIT_NEW_PW]))
			  {	// Password has already been changed to an md5(), so OK to leave the data
				$do_log['user_password'] = $changed_user_data['user_password'];
				$log_action = USER_AUDIT_NEW_PW;
			  }
			  unset($changed_user_data['user_password']);
			}

			if (isset($changed_user_data['user_email']))
			{
			  if (isset($user_logging_opts[USER_AUDIT_NEW_EML]))
			  {
				$do_log['user_email'] = $changed_user_data['user_email'];
				$log_action = USER_AUDIT_NEW_EML;
			  }
			  unset($changed_user_data['user_email']);
			}

			if (count($changed_user_data) && isset($user_logging_opts[USER_AUDIT_NEW_SET]))
			{
			  $do_log = array_merge($do_log,$changed_user_data);
			  $log_action = USER_AUDIT_NEW_SET;
			}
			if (count($do_log))
			{  // Got some changes to audit
//			echo "Adding to audit log<br />";
			  if ($_uid)
			  {
				$log_action = USER_AUDIT_ADMIN;						// If an admin did the mod, different heading
				// Embed a message saying who changed the data
				$changed_user_data['message'] = str_replace(array('--ID--','--LOGNAME--'),array(USERID,USERNAME),LAN_USET_18);
				$admin_log->user_audit($log_action,$do_log, $udata['user_id'],$udata['user_loginname']);
			  }
			  else
			  {
				if (count($do_log) > 1)  $log_action = USER_AUDIT_NEW_SET;		// Log multiple entries to one record
				$admin_log->user_audit($log_action,$do_log);
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
		if(isset($changed_user_data['user_name']) && !$_uid)
		{
		  $sql->db_Update("online", "online_user_id = '".USERID.".".$changed_user_data['user_name']."' WHERE online_user_id = '".USERID.".".USERNAME."'");
		}


		// Save extended field values
		if($ue_fields)
		{
// ***** Next line creates a record which presumably should be there anyway, so could generate an error
		  $sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id, user_hidden_fields) values ('".intval($inp)."', '')");
		  $sql->db_Update("user_extended", $ue_fields." WHERE user_extended_id = '".intval($inp)."'");
		}


		// Update XUP data if file name changed.
		if(isset($changed_user_data['user_xup']))
		{
		  require_once(e_HANDLER."login.php");
		  userlogin::update_xup($inp, $changed_user_data['user_xup']);
		}


		$e_event->trigger("postuserset", $_POST);


		if(e_QUERY == "update") 
		{
          header("Location: index.php");
		}
		$message = "<div style='text-align:center'>".LAN_150."</div>";
		$caption = LAN_151;
	  } 
	  else 
	  {	// Invalid data
		$message = "<div style='text-align:center'>".$ret."</div>";
		$caption = LAN_151;
	  }
	  unset($_POST);
	}
}

if ($error)
{
	require_once(e_HANDLER."message_handler.php");
	message_handler("P_ALERT", $error);
	$adref = $_POST['adminreturn'];
}

// --- User data has been updated here if appropriate ---

if(isset($message))
{
	$ns->tablerender($caption, $message);
}


//-----------------------------------------------------
// Re-read the user data into curVal (ready for display)
//-----------------------------------------------------

$uuid = ($_uid) ? $_uid : USERID;			// If $_uid is set, its an admin changing another user's data

$qry = "
SELECT u.*, ue.* FROM #user AS u
LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
WHERE u.user_id='".intval($uuid)."'
";

$sql->db_Select_gen($qry);
$curVal=$sql->db_Fetch();
$curVal['userclass_list'] = addCommonClasses($curVal);


if($_POST)
{     // Fix for all the values being lost when there was an error in a field - restore from the latest $_POST values
	  // (Password fields have intentionally been cleared). If no error, there's an unset($_POST) to disable this block
  foreach($_POST as $key => $val)
  {
	$curVal["user_".$key] = $val;
  }
  foreach($_POST['ue'] as $key => $val)
  {
	$curVal[$key] = $val;
  }
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$text = (e_QUERY ? $rs->form_open("post", e_SELF."?".e_QUERY, "dataform", "", " enctype='multipart/form-data'") : $rs->form_open("post", e_SELF, "dataform", "", " enctype='multipart/form-data'"));

if(e_QUERY == "update")
{
	$text .= "<div class='fborder' style='text-align:center'><br />".str_replace("*","<span style='color:red'>*</span>",LAN_USET_9)."<br />".LAN_USET_10."<br /><br /></div>";
}

$text .= $tp->parseTemplate($USERSETTINGS_EDIT, TRUE, $usersettings_shortcodes);
$text .= "<div>";

$text .= "
	<input type='hidden' name='_uid' value='{$uuid}' />
	</div>
	</form>
	";

$ns->tablerender(LAN_155, $text);
require_once(FOOTERF);


// If a field is required, returns a red asterisk
function req($field) 
{
	global $pref;
	if ($field == 2)
	{
		$ret = "<span style='text-align:right;font-size:15px; color:red'> *</span>";
	}
	else
	{
		$ret = "";
	}
	return $ret;
}



// Delete a file from the public directories. Return TRUE on success, FALSE on failure.
// Also deletes from database if appropriate.
function delete_file($fname, $dir = 'avatars/')
{
  global $sql;
  if (!$fname) return FALSE;
  
  if (preg_match("#Binary (.*?)/#", $fname, $match)) 
  {
	return $sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
  }
  elseif (file_exists(e_FILE."public/".$dir.$fname)) 
  {
	unlink(e_FILE."public/".$dir.$fname);
	return TRUE;
  }
  return FALSE;
}


function headerjs() 
{
	global $cal;
	$script = "<script type=\"text/javascript\">
		function addtext_us(sc){
		document.getElementById('dataform').image.value = sc;
		}

		</script>\n";

	$script .= $cal->load_files();
	return $script;
}
?>
