<?php

/*
* e107 website system
*
* Copyright (C) 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Administration Area - Users
*
* $URL$
* $Id$
*
*/
require_once ('../class2.php');
if (!getperms('4'))
{
	header('location:'.$e107->url->getUrl('core:core','main','action=index'));
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user.php');


if (varset($_POST['useraction']))
{
	foreach ($_POST['useraction'] as $key => $val)
	{
		if ($val)
		{
			$_POST['useraction'] = $val;
			$_POST['userip'] = $_POST['userip'][$key];
			$_POST['userid'] = $key;
			break;
		}
	}
}

if (e_QUERY == 'logoutas' || varset($_POST['useraction']) == 'logoutas')
{
	$asuser = e107::getSystemUser(e107::getUser()->getSessionDataAs(), false);
  	if(e107::getUser()->logoutAs())
  	{ // TODO - lan
		e107::getMessage()->addSuccess('Successfully logged out from '.($asuser && $asuser->getValue('name') ? $asuser->getValue('name') : 'unknown').' account', 'default', true);
		e107::getEvent()->trigger('logoutas', array('user_id' => $asuser, 'admin_id' => e107::getUser()->getId()));
  	}
	header('location:'.e_ADMIN_ABS.'users.php');
	exit;
}

if (isset ($_POST['useraction']) && $_POST['useraction'] == 'usersettings')
{
	header('location:'.$e107->url->getUrl('core:user','main','func=settings&id='.(int) $_POST['userid']));
	exit;
}



function headerjs()
{
	require_once (e_HANDLER.'js_helper.php');
	return "<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>";
}


require_once (e_HANDLER.'message_handler.php');
$emessage = & eMessage :: getInstance();
if (isset ($_POST['delete_rank']))
{
	foreach ($_POST['delete_rank'] as $k => $v)
	{
		deleteRank($k);
	}
}
if (isset ($_POST['updateRanks']))
{
	updateRanks();
}
$e_sub_cat = 'users';
require_once ('auth.php');
$user = new users;
require_once (e_HANDLER.'form_handler.php');
require_once (e_HANDLER.'userclass_class.php');
include_once (e_HANDLER.'user_extended_class.php');
require_once (e_HANDLER.'validator_class.php');
// require_once (e_HANDLER.'user_handler.php');
//      $userMethods = new UserHandler;
//    	$colList = $userMethods->getNiceNames(TRUE);
$ue = new e107_user_extended;
$userMethods = e107::getUserSession();
$user_data = array();
$frm = new e_form;
$rs = new form;
if (e_QUERY)
{
	$tmp = explode('.',e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1],'');
	$id = varset($tmp[2],0);
	$from = varset($tmp[3],0);
	unset ($tmp);
}
$from = varset($from,0);
$amount = 30;
if ($action == 'ranks')
{
	showRanks();
}
// ------- Check for Bounces --------------
$bounce_act = '';
if (isset ($_POST['check_bounces']))
	$bounce_act = 'first_check';
if (isset ($_POST['delnonbouncesubmit']))
	$bounce_act = 'delnonbounce';
if (isset ($_POST['clearemailbouncesubmit']))
	$bounce_act = 'clearemailbounce';
if (isset ($_POST['delcheckedsubmit']))
	$bounce_act = 'delchecked';
if (isset ($_POST['delallsubmit']))
	$bounce_act = 'delall';
if ($bounce_act)
{
	$user->check_bounces($bounce_act,implode(',',$_POST['delete_email']));
	require_once ("footer.php");
	exit;
}

// ------- Resend Email. --------------
if (isset ($_POST['resend_mail']))
{
	$user->resend($_POST['resend_id'],$_POST['resend_key'],$_POST['resend_name'],$_POST['resend_email']);
}

// ------- Resend Email. --------------
if (isset ($_POST['resend_to_all']))
{
	$user->resend_to_all();
}

if (isset ($_POST['execute_batch']))
{
	$user->process_batch();
}




// ------- Test Email. --------------
if (isset ($_POST['test_mail']))
{
	require_once (e_HANDLER.'mail_validation_class.php');
	list($adminuser,$adminhost) = explode('@',SITEADMINEMAIL, 2);
	$validator = new email_validation_class;
	$validator->localuser = $adminuser;
	$validator->localhost = $adminhost;
	$validator->timeout = 5;
	$validator->debug = 1;
	$validator->html_debug = 1;
	$text = "<div style='".ADMIN_WIDTH."'>";
	ob_start();
	$email_status = $validator->ValidateEmailBox($_POST['test_email']);
	$text .= ob_get_contents();
	ob_end_clean();
	$text .= "</div>";
	$caption = $_POST['test_email']." - ";
	$caption .= ($email_status == 1) ? "Valid" : "Invalid";
	if ($email_status == 1)
	{
		$text .= "<form method='post' action='".e_SELF.$qry."'>
		<div style='text-align:left'>
		<input type='hidden' name='useraction' value='resend' />\n
		<input type='hidden' name='userid' value='".$_POST['test_id']."' />\n
		<input class='button' type='submit' name='resend_' value='".USRLAN_112."' />\n</div></form>\n";
		$text .= "<div>";
	}
	$ns->tablerender($caption,$text);
	unset ($id,$action,$sub_cation);
}
// ------- Update Options. --------------
if (isset ($_POST['update_options']))
{
	$temp = array();
	$temp['avatar_upload'] = (FILE_UPLOADS ? $_POST['avatar_upload'] : 0);
	$temp['im_width'] = $_POST['im_width'];
	$temp['im_height'] = $_POST['im_height'];
	$temp['photo_upload'] = (FILE_UPLOADS ? $_POST['photo_upload'] : 0);
	$temp['del_unv'] = $_POST['del_unv'];
	$temp['profile_rate'] = $_POST['profile_rate'];
	$temp['profile_comments'] = $_POST['profile_comments'];
	$temp['track_online'] = $_POST['track_online'];
	$temp['force_userupdate'] = $_POST['force_userupdate'];
	$temp['memberlist_access'] = $_POST['memberlist_access'];
	$temp['user_new_period'] = $_POST['user_new_period'];
	if ($admin_log->logArrayDiffs($temp,$pref,'USET_03'))
	{
		save_prefs();
		// Only save if changes
		$user->show_message(USRLAN_1);
	}
	else
	{
		$user->show_message(USRLAN_193);
	}
}
// ------- Prune Users. --------------
if (isset ($_POST['prune']))
{
	$e107cache->clear('online_menu_member_total');
	$e107cache->clear('online_menu_member_newest');
	$text = USRLAN_56.' ';
	$bantype = $_POST['prune_type'];
	if ($bantype == 30)
		// older than 30 days.
	{
		$bantype = 2;
		$ins = " AND user_join < ".strtotime("-30 days");
	}
	if ($sql->db_Select("user","user_id, user_name","user_ban= {$bantype}".$ins))
	{
		$uList = $sql->db_getList();
		foreach ($uList as $u)
		{
			$text .= $u['user_name']." ";
			$sql->db_Delete("user","user_id='{$u['user_id']}' ");
			$sql->db_Delete("user_extended","user_extended_id='{$u['user_id']}' ");
		}
		$admin_log->log_event('USET_04',str_replace(array('--COUNT--','--TYPE--'),array(count($uList),$bantype),USRLAN_160),E_LOG_INFORMATIVE);
	}
	$ns->tablerender(USRLAN_57,"<div style='text-align:center'><b>".$text."</b></div>");
	unset ($text);
}



// ------- Quick Add User --------------
if (isset ($_POST['adduser']))
{
	if (!$_POST['ac'] == md5(ADMINPWCHANGE))
	{
		exit;
	}
	$e107cache->clear('online_menu_member_total');
	$e107cache->clear('online_menu_member_newest');
	$error = false;
	if (isset ($_POST['generateloginname']))
	{
		$_POST['loginname'] = $userMethods->generateUserLogin($pref['predefinedLoginName']);
	}
	if (isset ($_POST['generatepassword']))
	{
		$_POST['password1'] = $userMethods->generateRandomString('**********');
		// 10-char password should be enough
		$_POST['password2'] = $_POST['password1'];
	}
	// Now validate everything
	$allData = validatorClass :: validateFields($_POST,$userMethods->userVettingInfo,true);
	// Do basic validation
	validatorClass :: checkMandatory('user_name,user_loginname',$allData);
	// Check for missing fields (email done in userValidation() )
	validatorClass :: dbValidateArray($allData,$userMethods->userVettingInfo,'user',0);
	// Do basic DB-related checks
	$userMethods->userValidation($allData);
	// Do user-specific DB checks
	if (!isset ($allData['errors']['user_password']))
	{
	// No errors in password - keep it outside the main data array
		$savePassword = $allData['data']['user_password'];
		unset ($allData['data']['user_password']);
		// Delete the password value in the output array
	}
	unset ($_POST['password1']);
	// Restrict the scope of this
	unset ($_POST['password2']);
	if (!check_class($pref['displayname_class'],$allData['data']['user_class']))
	{
		if ($allData['data']['user_name'] != $allData['data']['user_loginname'])
		{
			$allData['errors']['user_name'] = ERR_FIELDS_DIFFERENT;
		}
	}
	if (count($allData['errors']))
	{
		require_once (e_HANDLER."message_handler.php");
		$temp = validatorClass :: makeErrorList($allData,'USER_ERR_','%n - %x - %t: %v','<br />',$userMethods->userVettingInfo);
		message_handler('P_ALERT',$temp);
		$error = true;
	}
	// Always save some of the entered data - then we can redisplay on error
	$user_data = & $allData['data'];
	if (!$error)
	{

		if(varset($_POST['perms']))
		{
			$allData['data']['user_admin'] = 1;
			$allData['data']['user_perms'] = implode('.',$_POST['perms']);
		}


		$message = '';
		$user_data['user_password'] = $userMethods->HashPassword($savePassword,$user_data['user_login']);
		$user_data['user_join'] = time();
		if ($userMethods->needEmailPassword())
		{
		// Save separate password encryption for use with email address
			$user_data['user_prefs'] = serialize(array('email_password' => $userMethods->HashPassword($savePassword,$user_data['user_email'])));
		}
		$userMethods->userClassUpdate($allData['data'],'userall');
		// Set any initial classes
		$userMethods->addNonDefaulted($user_data);
		validatorClass :: addFieldTypes($userMethods->userVettingInfo,$allData);
		//FIXME - (SecretR) there is a better way to fix this (missing default value, sql error in strict mode - user_realm is to be deleted from DB later)
		$allData['data']['user_realm'] = '';
		if ($sql->db_Insert('user',$allData))
		{
		// Add to admin log
			$admin_log->log_event('USET_02',"UName: {$user_data['user_name']}; Email: {$user_data['user_email']}",E_LOG_INFORMATIVE);
			// Add to user audit trail
			$admin_log->user_audit(USER_AUDIT_ADD_ADMIN,$user_data,0,$user_data['user_loginname']);
			$e_event->trigger('userfull',$user_data);
			// send everything available for user data - bit sparse compared with user-generated signup
			if (isset ($_POST['sendconfemail']))
			{
			// Send confirmation email to user
				require_once (e_HANDLER.'mail.php');
				$e_message = str_replace(array('--SITE--','--LOGIN--','--PASSWORD--'),array(SITEURL,$user_data['user_login'],$savePassword),USRLAN_185).USRLAN_186;
				if (sendemail($user_data['user_email'],USRLAN_187.SITEURL,$e_message,$user_data['user_login'],'',''))
				{
					$message = USRLAN_188.'<br /><br />';
				}
				else
				{
					$message = USRLAN_189.'<br /><br />';
				}
			}
			$message .= str_replace('--NAME--',$user_data['user_name'],USRLAN_174);
			if (isset ($_POST['generateloginname']))
				$message .= '<br /><br />'.USRLAN_173.': '.$user_data['user_login'];
			if (isset ($_POST['generatepassword']))
				$message .= '<br /><br />'.USRLAN_172.': '.$savePassword;
			unset ($user_data);
			// Don't recycle the data once the user's been accepted without error
		}
	}
	if (isset ($message))
		$user->show_message($message);
}
// ------- Bounce --> Unverified --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == "reqverify")
{
	$sql->db_Select("user","*","user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	extract($row);
	$sql->db_Update("user","user_ban='2' WHERE user_id='".$_POST['userid']."' ");
	$user->show_message("User now has to verify");
	$action = "main";
	if (!$sub_action)
	{
		$sub_action = "user_id";
	}
}
if (isset ($_POST['useraction']) && $_POST['useraction'] == "ban")
{
	$user->user_ban($_POST['userid']);
}

// ------- Unban User --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == "unban")
{
	$user->user_unban($_POST['userid']);
}

// User Info.
if ((isset ($_POST['useraction']) && $_POST['useraction'] == "userinfo") || $_GET['userinfo'])
{
	$ip = ($_POST['userip']) ? $_POST['userip'] : $_GET['userinfo'];
	$user->user_info($ip);
}

// ------- Delete User --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == 'deluser')
{
	$user->user_delete($_POST['userid'],true);
}

// ---- Update User's class --------------------
if (isset ($_POST['updateclass']))
{
	$user->user_userclass($_POST['userid'], $_POST['userclass'],'clear');
}

if (isset ($_POST['useraction']) && $_POST['useraction'] == 'userclass')
{
  //	header('location:'.e_ADMIN.'userclass.php?'.$e107->tp->toDB($_POST['userid'].'.'.e_QUERY));
  //	exit;
  	$user->show_userclass($_POST['userid']);
}

// ---- Login as another user --------------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == 'loginas')
{
	if(e107::getUser()->getSessionDataAs())
	{
		e107::getMessage()->addWarning(USRLAN_AS_3);
	}
  	elseif(e107::getUser()->loginAs($_POST['userid']))
  	{ // TODO - lan
		e107::getMessage()->addSuccess('Successfully logged in as '.e107::getSystemUser($_POST['userid'])->getValue('name').' <a href="'.e_ADMIN_ABS.'users.php?logoutas">[logout]</a>')
			->addSuccess('Please, <a href="'.SITEURL.'" rel="external">Leave Admin</a> to browse the system as this user. Use &quot;Logout&quot; option in Administration to end front-end session');

		e107::getEvent()->trigger('loginas', array('user_id' => $_POST['userid'], 'admin_id' => e107::getUser()->getId()));
  	}

}

// ------- Resend Email Confirmation. --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == 'resend')
{
	$qry = (e_QUERY) ? "?".e_QUERY : "";
	if ($sql->db_Select("user","*","user_id='".$_POST['userid']."' "))
	{
		$resend = $sql->db_Fetch();
		$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
		$text .= USRLAN_116." <b>".$resend['user_name']."</b><br /><br />

		<input type='hidden' name='resend_id' value='".$_POST['userid']."' />\n
		<input type='hidden' name='resend_name' value='".$resend['user_name']."' />\n
		<input type='hidden' name='resend_key' value='".$resend['user_sess']."' />\n
		<input type='hidden' name='resend_email' value='".$resend['user_email']."' />\n
		<input class='button' type='submit' name='resend_mail' value='".USRLAN_112."' />\n</div></form>\n";
		$caption = USRLAN_112;
		$ns->tablerender($caption,$text);
		require_once ("footer.php");
		exit;
	}
}
// ------- TEst Email confirmation. --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == 'test')
{
	$qry = (e_QUERY) ? "?".e_QUERY : "";
	if ($sql->db_Select("user","*","user_id='".$_POST['userid']."' "))
	{
		$test = $sql->db_Fetch();
		$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
		$text .= USRLAN_117." <br /><b>".$test['user_email']."</b><br /><br />
		<input type='hidden' name='test_email' value='".$test['user_email']."' />\n
		<input type='hidden' name='test_id' value='".$_POST['userid']."' />\n
		<input class='button' type='submit' name='test_mail' value='".USRLAN_118."' />\n</div></form>\n";
		$caption = USRLAN_118;
		$ns->tablerender($caption,$text);
		require_once ("footer.php");
		exit;
	}
}


$prm = e107::getUserPerms();

// ------- Make Admin --------------
if ((varset($_POST['useraction'])== "admin" || varset($_POST['useraction'])== "adminperms") && getperms('3'))
{
	$sql->db_Select("user","user_id, user_name, user_perms","user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();

	if(varset($_POST['useraction'])== "admin")
	{
		$sql->db_Update("user","user_admin='1' WHERE user_id='".$_POST['userid']."' ");
	}

	$admin_log->log_event('USET_08',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_164),E_LOG_INFORMATIVE);
	$user->show_message($row['user_name']." ".USRLAN_3." <a href='".e_ADMIN."administrator.php?edit.{$row['user_id']}'>".USRLAN_4."</a>");
	$action = "main";
	if (!$sub_action)
	{
		$sub_action = "user_id";
	}
	if (!$id)
	{
		$id = "DESC";
	}


	$prm->edit_administrator($row);
	require_once ("footer.php");
	exit;
}

if (varset($_POST['update_admin'])) // Update admin Perms.
{
	$prm->updatePerms($_POST['a_id'],$_POST['perms']);
}


// ------- Remove Admin --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == "unadmin" && getperms('3'))
{
	$sql->db_Select("user","*","user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	extract($row);
	if ($user_perms == "0")
	{
		$user->show_message(USRLAN_5);
	}
	else
	{
		$sql->db_Update("user","user_admin='0', user_perms='' WHERE user_id='".$_POST['userid']."'");
		$admin_log->log_event('USET_09',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_165),E_LOG_INFORMATIVE);
		$user->show_message($user_name." ".USRLAN_6);
		$action = "main";
		if (!$sub_action)
		{
			$sub_action = "user_id";
		}
		if (!$id)
		{
			$id = "DESC";
		}
	}
}
// ------- Approve User. --------------
if (isset ($_POST['useraction']) && $_POST['useraction'] == "verify")
{
	$user->user_activate($_POST['userid']);
}
if (isset ($action) && $action == "uset")
{
	$user->show_message(USRLAN_87);
	$action = "main";
}
if (isset ($action) && $action == "cu")
{
	$user->show_message(USRLAN_88);
	$action = "main";
	//	$sub_action = "user_id";
}

/*
echo "action= ".$action."<br />";
echo "subaction= ".$sub_action."<br />";
echo "id= ".$id."<br />";
echo "from= ".$from."<br />";
echo "amount= ".$amount."<br />";
*/
$unverified = $sql->db_Count("user","(*)","WHERE user_ban = 2");
if (!e_QUERY)
	$action = "main";
switch ($action)
{
	case "unverified" :
		$user->show_existing_users($action,$sub_action,$id,$from,$amount);
		break;
	case "options" :
		$user->show_prefs();
		break;
	case "prune" :
		$user->show_prune();
		break;
	case "create" :
		$userMethods->deleteExpired();
		// Remove time-expired users
		$user->user_add($user_data);
		break;
	default :
		$user->show_existing_users($action,$sub_action,$id,$from,$amount);
}
require_once ("footer.php");


class users
{
	var $fields = array();
	var $fieldpref = array();
	var $sortorder = "asc";
	var $sortorderrev = "desc";
	var $sortfield = "user_id";
	var $from = 0;


	function users()
	{
		global $pref,$user_pref,$sql,$tp;
		if (isset ($pref['admin_user_disp']))
		{
			$user_pref['admin_users_columns'] = ($pref['admin_user_disp']) ? explode("|",$pref['admin_user_disp']) : array('user_status','user_name','user_class');
			save_prefs('user');
			unset ($pref['admin_user_disp']);
			save_prefs();
		}
		$this->usersSaveColumnPref();
		$this->fieldpref = (!$user_pref['admin_users_columns']) ? array('user_name','user_class') : $user_pref['admin_users_columns'];

		/*        if (e_QUERY)
		{
		$tmp = explode('.', e_QUERY);
		$action = $tmp[0];    // main
		$sub_action = varset($tmp[1],'');
		$id = varset($tmp[2],0);
		$from = varset($tmp[3],0);
		unset($tmp);
		}*/
		global $sub_action,$id,$from;
		if ($from)
		{
			$this->sortfield = $sub_action;
			$this->sortorder = $id;
			$this->sortorderrev = ($this->sortorder == 'asc') ? 'desc' : 'asc';
			$this->from = $from;
		}
		$this->fields = array(
			'checkboxes' 		=> array('title' => '','width' => '3%','forced' => true,'thclass' => 'center first'),
			'user_id' 			=> array('title' => 'Id','width' => '5%','forced' => true),
			'user_status' 		=> array('title' => LAN_STATUS,'width' => 'auto', 'nosort'=>TRUE),
			'user_name' 		=> array('title' => LAN_USER_01,'type' => 'text','width' => 'auto','thclass' => 'left first'), // Display name
	 		'user_loginname' 	=> array('title' => LAN_USER_02,'type' => 'text','width' => 'auto'), // User name
	 		'user_login' 		=> array('title' => LAN_USER_03,'type' => 'text','width' => 'auto'), // Real name (no real vetting)
	 		'user_customtitle' 	=> array('title' => LAN_USER_04,'type' => 'text','width' => 'auto'), // No real vetting
	 		'user_password' 	=> array('title' => LAN_USER_05,'type' => 'text','width' => 'auto'),
			'user_sess' 		=> array('title' => LAN_USER_06,'type' => 'text','width' => 'auto'), // Photo
	 		'user_image' 		=> array('title' => LAN_USER_07,'type' => 'text','width' => 'auto'), // Avatar
	 		'user_email' 		=> array('title' => LAN_USER_08,'type' => 'text','width' => 'auto'),
			'user_signature' 	=> array('title' => LAN_USER_09,'type' => 'text','width' => 'auto'),
			'user_hideemail' 	=> array('title' => LAN_USER_10,'type' => 'boolean','width' => 'auto'),
			'user_xup' 			=> array('title' => LAN_USER_11,'type' => 'text','width' => 'auto'),
			'user_class' 		=> array('title' => LAN_USER_12,'type' => 'class'),
			'user_join' 		=> array('title' => LAN_USER_14,'type' => 'date', 'width' => 'auto'),
			'user_lastvisit' 	=> array('title' => LAN_USER_15,'type' => 'date', 'width' => 'auto'),
			'user_currentvisit' => array('title' => LAN_USER_16,'type' => 'date', 'width' => 'auto'),
			'user_comments' 	=> array('title' => LAN_USER_17,'width' => 'auto'),
			'user_lastpost' 	=> array('title' => 'Last Post','type' => 'date', 'width' => 'auto'),
			'user_ip' 			=> array('title' => LAN_USER_18,'width' => 'auto'),
			'user_ban' 			=> array('title' => LAN_USER_19,'type' => 'boolean', 'width' => 'auto'),
			'user_prefs' 		=> array('title' => LAN_USER_20,'width' => 'auto'),
			'user_visits' 		=> array('title' => LAN_USER_21,'width' => 'auto','thclass'=>'right'),
			'user_admin' 		=> array('title' => LAN_USER_22,'type' => 'boolean', 'width' => 'auto', 'thclass'=>'center'),
			'user_perms' 		=> array('title' => LAN_USER_23,'width' => 'auto'),
			'user_pwchange'		=> array('title' => LAN_USER_24,'width' => 'auto'),
		);

		if($sql->db_Select('user_extended_struct', 'user_extended_struct_name', "user_extended_struct_type > 0 AND user_extended_struct_text != '_system_' ORDER BY user_extended_struct_parent ASC"))
		{
			while ($row = $sql->db_Fetch())
			{
				$field = "user_".$row['user_extended_struct_name'];
				$title = ucfirst(str_replace("user_","",$field));
				$this->fields[$field] = array('title' => $title,'width' => 'auto');
			}
		}
		$this->fields['options'] = array('title' => LAN_OPTIONS,'width' => '10%',"thclass" => "center last",'forced' => true);
	}


	function process_batch()
	{
		list($type,$tmp,$uclass) = explode("_",$_POST['execute_batch']);
		$method = "user_".$type;

		if($method == "user_remuserclass")
		{
			$method = "user_userclass";
		}

		if (method_exists($this,$method) && isset($_POST['user_selected']) )
		{
			foreach ($_POST['user_selected'] as $userid)
			{

            	if($type=='userclass' || $type=='remuserclass')
				{
					switch($type)
					{
						case 'userclass':
							$mode = 'append';
						break;

						case 'remuserclass'	:
							$mode = ($uclass != '0') ? 'remove' : 'clear';
						break;
					}

                	$this->$method($userid,array($uclass),$mode);
				}
				else
				{
                	$this->$method($userid);
				}
			}
		}
	}


	function user_delete($userid,$confirm = false)
	{
		global $sql,$admin_log,$e_event,$ns;
		if ($_POST['confirm'] || !$confirm)
		{
			$uid = ($confirm) ? intval($_POST['userid']) : $userid;
			if ($sql->db_Delete("user","user_id=".$uid." AND user_perms != '0' AND user_perms != '0.'"))
			{
				$sql->db_Delete("user_extended","user_extended_id='".$uid."' ");
				$admin_log->log_event('USET_07',str_replace('--UID--',$uid,USRLAN_163),E_LOG_INFORMATIVE);
				$e_event->trigger('userdelete',$temp = array('user_id' => $uid));
				$this->show_message(USRLAN_10);
			}
			if (!$sub_action)
			{
				$sub_action = "user_id";
			}
			if (!$id)
			{
				$id = "DESC";
			}
		}
		else
		{
		// Put up confirmation
			if ($sql->db_Select("user","*","user_id='".$_POST['userid']."' "))
			{
				$row = $sql->db_Fetch();
				$qry = (e_QUERY) ? "?".e_QUERY : "";
				$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
				$text .= "<div>
				<input type='hidden' name='useraction' value='deluser' />
				<input type='hidden' name='userid' value='{$row['user_id']}' /></div>".USRLAN_13."
				<br /><br /><span class='indent'>#{$row['user_id']} : {$row['user_name']}</span>
				<br /><br />
				<input type='submit' class='button' name='confirm' value='".USRLAN_17."' />
				&nbsp;&nbsp;
				<input type='button' class='button' name='cancel' value='".LAN_CANCEL."' onclick=\"location.href='".e_SELF.$qry."' \" />
				</div>
				</form>
				";
				$ns->tablerender(USRLAN_16,$text);
				require_once ("footer.php");
				exit;
			}
		}
	}


	function user_unban($userid)
	{
		global $sql,$admin_log;
		$sql->db_Select("user","user_name,user_ip","user_id='".$userid."'");
		$row = $sql->db_Fetch();
		$sql->db_Update("user","user_ban='0' WHERE user_id='".$userid."' ");
		$sql->db_Delete("banlist"," banlist_ip='{$row['user_ip']}' ");
		$admin_log->log_event('USET_06',str_replace(array('--UID--','--NAME--'),array($userid,$row['user_name']),USRLAN_162),E_LOG_INFORMATIVE);
		$this->show_message(USRLAN_9." (".$userid.". ".$row['user_name'].")");
		$action = "main";
		if (!$sub_action)
		{
			$sub_action = "user_id";
		}
	}


	function user_activate($userid)
	{
		global $sql,$e_event,$admin_log,$userMethods;
		$uid = intval($userid);
		if ($sql->db_Select("user","*","user_id='".$uid."' "))
		{
			if ($row = $sql->db_Fetch())
			{
				$dbData = array();
				$dbData['WHERE'] = "user_id=".$uid;
				$dbData['data'] = array('user_ban' => '0','user_sess' => '');
				// Add in the initial classes as necessary
				if ($userMethods->userClassUpdate($row,'userall'))
				{
					$dbData['data']['user_class'] = $row['user_class'];
				}
				$userMethods->addNonDefaulted($dbData);
				validatorClass :: addFieldTypes($userMethods->userVettingInfo,$dbData);
				$sql->db_Update('user',$dbData);
				$admin_log->log_event('USET_10',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_166),E_LOG_INFORMATIVE);
				$e_event->trigger('userfull',$row);
				// 'New' event
				$this->show_message(USRLAN_86." (#".$userid." : ".$row['user_name'].")");
				if (!$action)
				{
					$action = "main";
				}
				if (!$sub_action)
				{
					$sub_action = "user_id";
				}
				if (!$id)
				{
					$id = "DESC";
				}
				if ($pref['user_reg_veri'] == 2)
				{
					if ($sql->db_Select("user","user_email, user_name","user_id = '{$uid}'"))
					{
						$row = $sql->db_Fetch();
						$message = USRLAN_114." ".$row['user_name'].",\n\n".USRLAN_122." ".SITENAME.".\n\n".USRLAN_123."\n\n";
						$message .= str_replace("{SITEURL}",SITEURL,USRLAN_139);
						require_once (e_HANDLER."mail.php");
						if (sendemail($row['user_email'],USRLAN_113." ".SITENAME,$message))
						{
						//  echo str_replace("\n","<br>",$message);
							$this->show_message("Email sent to: ".$row['user_name']);
						}
						else
						{
							$this->show_message("Failed to send to: ".$row['user_name'],'error');
						}
					}
				}
			}
		}
	}


	function usersSaveColumnPref()
	{
		global $pref,$user_pref,$admin_log;
		if (isset ($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_users_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
	}


	function user_info($ipd)
	{
		global $ns, $sql, $e107;

		if (isset($ipd))
		{
			$bullet = '';
			if(defined('BULLET'))
			{
				$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
			}
            // TODO - move to e_userinfo.php
			$obj = new convert;
			$sql->db_Select("chatbox", "*", "cb_ip='$ipd' LIMIT 0,20");
			$host = $e107->get_host_name($ipd);
			$text = USFLAN_3." <b>".$ipd."</b> [ ".USFLAN_4.": $host ]<br />
				<i><a href=\"banlist.php?".$ipd."\">".USFLAN_5."</a></i>

				<br /><br />";
			while (list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip ) = $sql->db_Fetch())
			{
				$datestamp = $obj->convert_date($cb_datestamp, "short");
				$post_author_id = substr($cb_nick, 0, strpos($cb_nick, "."));
				$post_author_name = substr($cb_nick, (strpos($cb_nick, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />
					". $cb_message."
					</div>
					<br />";
			}

			$text .= "<hr />";

			$sql->db_Select("comments", "*", "comment_ip='$ipd' LIMIT 0,20");
			while (list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql->db_Fetch())
			{
				$datestamp = $obj->convert_date($comment_datestamp, "short");
				$post_author_id = substr($comment_author, 0, strpos($comment_author, "."));
				$post_author_name = substr($comment_author, (strpos($comment_author, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />". $comment_comment."
					</div>
					<br />";
			}

		}

		$ns->tablerender(USFLAN_7, $text);



	}


	function showUserStatus($row)
	{
		if ($row['user_perms'] == "0")
		{
			$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'>".LAN_MAINADMIN."</div>";
		}
		else
			if ($row['user_admin'])
			{
				$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;;text-align:center'><a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'>".LAN_ADMIN."</a></div>";
			}
			else
				if ($row['user_ban'] == 1)
				{
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'>".LAN_BANNED."</a></div>";
				}
				else
					if ($row['user_ban'] == 2)
					{
						$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_NOTVERIFIED."</div>";
					}
					else
						if ($row['user_ban'] == 3)
						{
							$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_BOUNCED."</div>";
						}
						else
						{
							$text .= "&nbsp;";
		}
		return $text;
	}


	function showUserOptions($row)
	{
		extract($row);
		$text .= "<div>

				<input type='hidden' name='userid[{$user_id}]' value='{$user_id}' />
				<input type='hidden' name='userip[{$user_id}]' value='{$user_ip}' />
				<select name='useraction[{$user_id}]' onchange='this.form.submit()' class='tbox' style='width:75%'>
				<option selected='selected' value=''>&nbsp;</option>";
		if ($user_perms != "0")
		{
			$text .= "<option value='userinfo'>".USRLAN_80."</option>
					<option value='usersettings'>".LAN_EDIT."</option>
					";
			// login/logout As
			if(getperms('0') && !($row['user_admin'] && getperms('0', $row['user_perms'])))
			{
				if(e107::getUser()->getSessionDataAs() == $row['user_id']) $text .= "<option value='logoutas'>".sprintf(USRLAN_AS_2, $row['user_name'])."</option>";
				else $text .= "<option value='loginas'>".sprintf(USRLAN_AS_1, $row['user_name'])."</option>";
			}
			switch ($user_ban)
			{
				case 0 :
					$text .= "<option value='ban'>".USRLAN_30."</option>\n";
					break;
				case 1 :
					// Banned user
					$text .= "<option value='unban'>".USRLAN_33."</option>\n";
					break;
				case 2 :
					// Unverified
					$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='verify'>".USRLAN_32."</option>
						<option value='resend'>".USRLAN_112."</option>
						<option value='test'>".USRLAN_118."</option>";
					break;
				case 3 :
					// Bounced
					$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='reqverify'>".USRLAN_181."</option>
						<option value='verify'>".USRLAN_182."</option>
						<option value='test'>".USRLAN_118."</option>";
					break;
				default :
			}
			if (!$user_admin && !$user_ban && $user_ban != 2 && getperms('3'))
			{
				$text .= "<option value='admin'>".USRLAN_35."</option>\n";
			}
			else
				if ($user_admin && $user_perms != "0" && getperms('3'))
				{
					$text .= "<option value='adminperms'>".USRLAN_221."</option>\n";
					$text .= "<option value='unadmin'>".USRLAN_34."</option>\n";
				}
		}
		if ($user_perms == "0" && !getperms("0"))
		{
			$text .= "";
		}
		elseif ($user_id != USERID || getperms("0"))
		{
			$text .= "<option value='userclass'>".USRLAN_36."</option>\n";
		}
		if ($user_perms != "0")
		{
			$text .= "<option value='deluser'>".LAN_DELETE."</option>\n";
		}
		$text .= "</select></div>";
		return $text;
	}


	function show_search_filter()
	{
		global $frm;
		$e_userclass = new user_class;
   		// TODO - The search field (not the userclass drop-down) should be replaced with a generic ajax search-filter class element.
		$text = "<form method='get' action='".e_SELF."'>
		<table class='adminform'>\n";
		$text .= "<tr><td><input class='tbox' type='text' name='srch' size='20' value=\"".$_GET['srch']."\" maxlength='50' />\n";

        $list = $e_userclass->uc_required_class_list("public,member,admin,main,classes");
		$ulist = $list + array('unverified'=>LAN_NOTVERIFIED,'banned'=>LAN_BANNED,'bounced'=>LAN_BOUNCED);

        $text .= "<select class='tbox' name='filter' onchange='this.form.submit()' >\n";

		foreach($ulist as $key=>$val)
		{
			$sel = ($_SESSION['filter'] == $key) ? "selected='selected'" : "";
        	$text .= "<option value='$key' {$sel}>".$val."</option>\n";
		}

		$text .= "</select>";
        $text .= $frm->admin_button('searchsubmit', ADLAN_142);

	//	<input class='button' type='submit' name='searchsubmit' value='".ADLAN_142."' />\n
	//	\n";
		$text .= "</td></tr></table>

		</form>\n";
		return $text;
	}

	function get_search_query()
	{
		global $sql,$frm,$ns,$tp,$mySQLdefaultdb,$pref,$unverified,$userMethods,$sub_action,$id,$from, $amount;


        if(isset($_GET['srch'])) // We could use $_GET, if so, would need to rework the ordering to use $_GET also.
		{
        	$_SESSION['srch'] = $_GET['srch'];
		}

		if(isset($_GET['filter']))
		{
        	$_SESSION['filter'] = $_GET['filter'];
		}


	    if (isset ($_SESSION['srch']) && $_SESSION['srch'] != "")
		{
			$_SESSION['srch'] = $tp->toDB(trim($_SESSION['srch']));
			$query .= "( ";
			$query .= (strpos($_SESSION['srch'],"@") !== false) ? "user_email REGEXP('".$_SESSION['srch']."') OR " : "";
	  		$query .= (strpos($_SESSION['srch'],".") !== false) ? "user_ip REGEXP('".$_SESSION['srch']."') OR " : "";

			$fquery = array();
	   		foreach ($this->fieldpref as $field)
			{
				$fquery[] = $field." REGEXP('".$_SESSION['srch']."')";
			}

			$query .= implode(" OR ",$fquery);

			$query .= " ) ";
			$qry_order = ' ORDER BY user_id';
		}
		else
		{
			$query = '';
       /*		if ($action == 'unverified')
			{
				$query = 'user_ban = 2 ';
			}*/
			$qry_order = 'ORDER BY '.($sub_action ? $sub_action : 'user_id').' '.($id ? $id : 'DESC')."  LIMIT $from, $amount";
		}

		if(varset($_SESSION['filter']))
		{
			$uqry[e_UC_ADMIN] 		= " u.user_admin = 1 ";
			$uqry[e_UC_MEMBER]		= " u.user_ban = '0' ";
            $uqry[e_UC_MAINADMIN]	= " (u.user_perms = '0' OR u.user_perms = '0.') ";
			$uqry['unverified']		= " u.user_ban = 2 ";
			$uqry['banned']			= " u.user_ban = 1 ";
			$uqry['bounced']		= " u.user_ban = 3 ";

            if($query)
			{
             	$query .= " AND ";
			}

			if(isset($uqry[$_SESSION['filter']]))
			{
            	$query .= $uqry[$_SESSION['filter']];
			}
            else
			{
        		$query .= " FIND_IN_SET(".$_SESSION['filter'].",u.user_class) ";
			}
		}
			// $user_total = db_Count($table, $fields = '(*)',

		if($_SESSION['filter']==e_UC_ADMIN)
		{
			$this->fieldpref[] = 'user_perms';
		}


		$qry_insert = 'SELECT u.*, ue.* FROM `#user` AS u	LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id ';

        return ($query) ? $qry_insert." WHERE ".$query.$qry_order : $qry_insert.$qry_order;
	}

	function show_existing_users($action,$sub_action,$id,$from,$amount)
	{
		global $mySQLdefaultdb,$pref,$unverified,$userMethods;

		$sql = e107::getDb();
		$frm = e107::getForm();
		$ns = e107::getRender();
		$tp = e107::getParser();

		$e107 = e107 :: getInstance();
		$qry = $this->get_search_query();

		$this->fieldpref = array_unique($this->fieldpref);

		$text = "<div>".$this->show_search_filter();

		if ($user_total = $sql->db_Select_gen($qry))
		{
			$text .= "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-users-list'>
						<table cellpadding='0' cellspacing='0' class='adminlist'>".
						$frm->colGroup($this->fields,$this->fieldpref).
						$frm->thead($this->fields,$this->fieldpref,"main.[FIELD].[ASC].[FROM]").
			"<tbody>\n";

			while ($row = $sql->db_Fetch())
			{

				$text .= "
				<tr>
					<td class='center' >".$frm->checkbox('user_selected[]',$row['user_id'])."</td>
					<td class='center' style='width:5%; text-align:center' >{$row['user_id']}</td>";

					foreach ($this->fieldpref as $disp)
					{
						$class = vartrue($this->fields[$disp]['thclass']) ? "class='".$this->fields[$disp]['thclass']."'" : "";
						$text .= "<td ".$class." style='white-space:nowrap'>".$this->renderValue($disp,$row)."</td>\n";
					}

				$text .= "
				<td style='width:30%' class='center'>".$this->showUserOptions($row)."</td></tr>\n";
			}

			$text .= "</tbody>
			</table>

			<div class='buttons-bar center'>".$this->show_batch_options();
			$users = (e_QUERY != "unverified") ? $sql->db_Count("user") : $unverified;

			if ($users > $amount && !$_GET['srch'])
			{
				$parms = "{$users},{$amount},{$from},".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.user_id.desc.")."[FROM]";
				$text .= $tp->parseTemplate("{NEXTPREV={$parms}}");
			}

			if ($action == "unverified")
			{
				$qry = (e_QUERY) ? "?".e_QUERY : "";
				$text .= "
				<form method='post' action='".e_SELF.$qry."'>";
				if ($pref['mail_bounce_pop3'] != '')
				{
					$text .= "<input type='submit' class='button' name='check_bounces' value=\"".USRLAN_143."\" />\n";
				}
				$text .= "&nbsp;<input type='submit' class='button' name='resend_to_all' value=\"".USRLAN_144."\" />
				</form>";
			}
			$text .= "</div>";
		}
		$text .= "</fieldset></form>

		</div>";

		$emessage = eMessage :: getInstance();

		$total_cap = (isset ($_GET['srch'])) ? $user_total : $users;
		$caption = USRLAN_77."&nbsp;&nbsp;   (total: $total_cap)";
		$ns->tablerender($caption,$emessage->render().$text);
	}


	function renderValue($key,$row)
	{
		$frm = e107::getForm();
		$e107 = e107 :: getInstance();
		$type = $this->fields[$key]['type'];
		$pref = e107::getConfig()->getPref();
		$prm = e107::getUserPerms();

		switch($key) // switch based on field.
		{
			case 'user_class':
				if ($row['user_class'])
				{
					$tmp = explode(",",$row['user_class']);
					while (list($key,$class_id) = each($tmp))
					{
						$text .= $frm->uc_label($class_id)."<br />\n";
					}
					return $text;
				}
				else
				{
					return "&nbsp;";
				}
			break;

			case 'user_ip':
				return $e107->ipDecode($row['user_ip']);
			break;


			case 'user_status':
				return $this->showUserStatus($row);
			break;

			case 'user_name':
				return "<a href='".$e107->url->getUrl('core:user','main','func=profile&id='.$row['user_id'])."'>{$row['user_name']}</a>";
			break;

			case 'user_perms': //TODO display link to popup window with editable perms.
				// return $row[$key].'&nbsp;';
				return $prm->renderPerms($row[$key],$row['user_id']);
			break;

			case 'user_ban' :
				return ($row[$key] == 1) ? ADMIN_TRUE_ICON : '';	// We may want to show more of the status later
				break;

		}

		switch($type) // switch based on type.
		{
			case 'date':
				return ($row[$key]) ? strftime($pref['shortdate'],$row[$key]).'&nbsp;' : '&nbsp';
			break;

			case 'boolean':
				return ($row[$key] == 1) ? ADMIN_TRUE_ICON : '';
			break;


			case 'user_status':
				return $this->showUserStatus($row);
			break;

		}

		return $row[$key].'&nbsp;';

	}



	function show_batch_options()
	{
		$e107 = e107::getInstance();
		$classObj = $e107->getUserClass();
		$frm = new e_form();
		$classes = $classObj->uc_get_classlist();


		$assignClasses = array(); // Userclass list of userclasses that can be assigned
		foreach ($classes as $key => $val)
		{
			if ($classObj->isEditableClass($key))
			{
				$assignClasses[$key] = $classes[$key];
			}
		}
		unset($assignClasses[0]);


		$removeClasses = $assignClasses; // Userclass list of userclasses that can be removed
		$removeClasses[0] = array('userclass_name'=>array('userclass_id'=>0, 'userclass_name'=>USRLAN_220));


		 if(count($assignClasses))
		 {
		 	 $uclasses = array(
		         'userclass'    =>array('Assign Userclass...',$assignClasses),
		         'remuserclass' =>array('Remove Userclass..', $removeClasses)
		      );
		 }
		 else
		 {
		 	$uclasses = FALSE;
		 }


		   return $frm->batchoptions(
		      array(
		         'ban_selected'       =>USRLAN_30,
					'unban_selected'     =>USRLAN_33,
					'activate_selected'  =>USRLAN_32,
					'delete_selected'    =>LAN_DELETE
	         ),$uclasses

		   );
	}


	function show_options($action)
	{

    	// Please duplicate any changes to this function also in /usersettings.php. (at the end of the script)

		global $unverified;
		// ##### Display options
		if ($action == '')
		{
			$action = 'main';
		}
		// ##### Display options
		$var ['main']['text'] = USRLAN_71;
		$var ['main']['link'] = e_ADMIN.'users.php';
		$var ['create']['text'] = USRLAN_72;
		$var ['create']['link'] = e_ADMIN.'users.php?create';
		$var ['prune']['text'] = USRLAN_73;
		$var ['prune']['link'] = e_ADMIN.'users.php?prune';
		$var ['options']['text'] = LAN_OPTIONS;
		$var ['options']['link'] = e_ADMIN.'users.php?options';
		if ($unverified)
		{
			$var ['unveri']['text'] = USRLAN_138." ($unverified)";
			$var ['unveri']['link'] = e_ADMIN.'users.php?unverified';
		}
		$var ['ranks']['text'] = USRLAN_196;
		$var ['ranks']['link'] = e_ADMIN.'users.php?ranks';
		//  $var['mailing']['text']= USRLAN_121;
		//   $var['mailing']['link']="mailout.php";
		show_admin_menu(USRLAN_76,$action,$var);
	}


	function show_prefs()
	{
		global $ns,$pref,$e_userclass;
		if (!is_object($e_userclass))
			$e_userclass = new user_class;
		$pref['memberlist_access'] = varset($pref['memberlist_access'],e_UC_MEMBER);
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table class='adminlist'>
		<colgroup>
		<col style='width:60%' />
		<col style='width:40%' />
		</colgroup>

		<tr>
		<td>".USRLAN_44.":</td>
		<td>".($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' />".LAN_NO : "<input name='avatar_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked='checked' />".LAN_NO).(!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
		</td>
		</tr>

		<tr>
		<td>".USRLAN_53.":</td>
		<td>".($pref['photo_upload'] ? "<input name='photo_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' />".LAN_NO : "<input name='photo_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' checked='checked' />".LAN_NO).(!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
		</td>
		</tr>

		<tr>
		<td>".USRLAN_47.":</td>
		<td>
		<input class='tbox' type='text' name='im_width' size='10' value='".$pref['im_width']."' maxlength='5' /> (".USRLAN_48.")
		</td></tr>

		<tr>
		<td>".USRLAN_49.":</td>
		<td>
		<input class='tbox' type='text' name='im_height' size='10' value='".$pref['im_height']."' maxlength='5' /> (".USRLAN_50.")
		</td></tr>

		<tr>
		<td>".USRLAN_126.":</td>
		<td style='vertical-align:top'>".($pref['profile_rate'] ? "<input name='profile_rate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' />".LAN_NO : "<input name='profile_rate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' checked='checked' />".LAN_NO)."
		</td>
		</tr>

		<tr>
		<td>".USRLAN_127.":</td>
		<td style='vertical-align:top'>".($pref['profile_comments'] ? "<input name='profile_comments' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' />".LAN_NO : "<input name='profile_comments' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' checked='checked' />".LAN_NO)."
		</td>
		</tr>

		<tr>
		<td style='vertical-align:top'>".USRLAN_133.":<br /><span class='smalltext'>".USRLAN_134."</span></td>
		<td style='vertical-align:top'>".($pref['force_userupdate'] ? "<input name='force_userupdate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' />".LAN_NO : "<input name='force_userupdate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' checked='checked' />".LAN_NO)."
		</td>
		</tr>


		<tr>
		<td style='vertical-align:top'>".USRLAN_93."<br /><span class='smalltext'>".USRLAN_94."</span></td>
		<td>
		<input class='tbox' type='text' name='del_unv' size='10' value='".$pref['del_unv']."' maxlength='5' /> ".USRLAN_95."
		</td></tr>

		<tr>
		<td>".USRLAN_130."<br /><span class='smalltext'>".USRLAN_131."</span></td>
		<td>&nbsp;
		<input type='checkbox' name='track_online' value='1'".($pref['track_online'] ? " checked='checked'" : "")." /> ".USRLAN_132."&nbsp;&nbsp;
		</td>
		</tr>


		<tr>
		<td>".USRLAN_146.":</td>
		<td><select name='memberlist_access' class='tbox'>\n";
		$text .= $e_userclass->vetted_tree('memberlist_access',array($e_userclass,'select'),$pref['memberlist_access'],"public,member,guest,admin,main,classes,nobody");
		$text .= "</select>
		</td>
		</tr>


		<tr>
		<td style='vertical-align:top'>".USRLAN_190."<br /><span class='smalltext'>".USRLAN_191."</span></td>
		<td>
		<input class='tbox' type='text' name='user_new_period' size='10' value='".varset($pref['user_new_period'],0)."' maxlength='5' /> ".USRLAN_192."
		</td></tr>

		<tr>
		<td colspan='2' class='center button-bar'>
		<input class='button' type='submit' name='update_options' value='".USRLAN_51."' />
		</td></tr>

		</table></form></div>";
		$emessage = & eMessage :: getInstance();
		$ns->tablerender(USRLAN_52,$emessage->render().$text);
	}


	function show_message($message,$type = '')
	{
		$emessage = & eMessage :: getInstance();
		$emessage->add($message,E_MESSAGE_SUCCESS);
	}


	function show_prune()
	{
		global $ns,$sql;
		$unactive = $sql->db_Count("user","(*)","WHERE user_ban=2");
		$bounced = $sql->db_Count("user","(*)","WHERE user_ban=3");
		$older30 = $sql->db_Count("user","(*)","WHERE user_ban=2 AND (user_join < ".strtotime("-30 days").")");
		$text = "<div style='text-align:center'><br /><br />
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader3' style='text-align:center'><br />".LAN_DELETE.":&nbsp;
		<select class='tbox' name='prune_type'>";
		$prune_type = array(2 => USRLAN_138." [".$unactive."]",'30' => USRLAN_138." (".USRLAN_219.") [".$older30."]",3 => USRLAN_145." [".$bounced."]");
		foreach ($prune_type as $key => $val)
		{
			$text .= "<option value='$key'>{$val}</option>\n";
		}
		$text .= "</select><br /><br /></td>
		</tr>
		<tr>
		<td class='forumheader' style='text-align:center'>
		<input class='button' type='submit' name='prune' value=\"".USRLAN_55."\" />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$emessage = & eMessage :: getInstance();
		$ns->tablerender(USRLAN_55,$emessage->render().$text);
	}


	// Quick Add a new user - may be passed existing data if there was an entry error on first pass
	function user_add($user_data)
	{
		global $rs,$pref,$e_userclass;

		$prm = e107::getUserPerms();
		$list = $prm->getPermList();
		$frm = e107::getForm();
		$ns = e107::getRender();
		$mes = e107::getMessage();

		//TODO Better Password generation.
		// ie. a "Generate" button, which will place the text into the text field automatically.

		if (!is_object($e_userclass))
			$e_userclass = new user_class;
		$text = "<div>".$rs->form_open("post",e_SELF.(e_QUERY ? '?'.e_QUERY : ''),"adduserform")."
        <table cellpadding='0' cellspacing='0' class='adminform'>
		<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		<tr>
			<td>".USRLAN_61."</td>
			<td>
			".$rs->form_text('username',40,varset($user_data['user_name'],""),varset($pref['displayname_maxlength'],15))."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_128."</td>
			<td>
			".$rs->form_text('loginname',40,varset($user_data['user_loginname'],""),varset($pref['loginname_maxlength'],30))."&nbsp;&nbsp;
			".$frm->checkbox_label(USRLAN_170,'generateloginname', 1,varset($pref['predefinedLoginName'],false))."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_129."</td>
			<td>
			".$rs->form_text("realname",40,varset($user_data['user_login'],""),30)."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_62."</td>
			<td>
			".$rs->form_password("password1",40,"",20)."&nbsp;&nbsp;
			".$frm->checkbox_label(USRLAN_171,'generatepassword', 1)."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_63."</td>
			<td>
			".$rs->form_password("password2",40,"",20)."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_64."</td>
			<td>
			".$rs->form_text("email",60,varset($user_data['user_email'],""),100)."
			</td>
		</tr>

		<tr style='vertical-align:top'>
			<td>Require Confirmation</td>
			<td class='center'>".$frm->checkbox_label(USRLAN_181,'sendconfemail', 1)."</td>
		</tr>";

		//FIXME check what this is doing exactly.. is it a confirmation email (activation link) or just a notification?
		// Give drop-down option to: 1) Notify User and Activate. 2) Notify User and require activation. 3) Don't Notify

		if (!isset ($user_data['user_class']))
			$user_data['user_class'] = varset($pref['initial_user_classes'],'');
		$temp = $e_userclass->vetted_tree('class',array($e_userclass,'checkbox_desc'),$user_data['user_class'],'classes');

		if ($temp)
		{
			$text .= "<tr style='vertical-align:top'>
			<td>
			".USRLAN_120."
			</td><td>
			<a href='#set_class' class='e-expandit'>".USRLAN_120."</a>
			<div class='e-hideme' id='set_class' >
			{$temp}
			</div></td>
			</tr>\n";
		}

		// Make Admin.
		$text .= "<tr>
			<td>".USRLAN_35."</td>
			<td>
			<a href='#set_perms' class='e-expandit'>Set Permissions</a>
			<div class='e-hideme' id='set_perms' >\n";

			$groupedList = $prm->getPermList('grouped');

			foreach($groupedList as $section=>$list)
			{
				$text .= "\t\t<div class='field-section'><h4>".$prm->renderSectionDiz($section)."</h4>"; //XXX Lan - General
				foreach($list as $key=>$diz)
				{
					$text .= $prm->checkb($key, '', $diz);
				}
				$text .= "</div>";
			}

		$text .= "</div></td>
		</tr>\n";


		$text .= "

		</table>
		<div class='buttons-bar center'>
			<input class='button' type='submit' name='adduser' value='".USRLAN_60."' />
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
		</div>
		</form>
		</div>
		";
		$emessage = & eMessage :: getInstance();
		$ns->tablerender(USRLAN_59,$mes->render().$text);
	}


	function resend($id,$key,$name,$email,$lfile = '')
	{
		global $sql,$mailheader_e107id,$admin_log;
		$id = (int) $id;
		// Check for a Language field, and if present, send the email in the user's language.
		if ($lfile == "")
		{
			if ($sql->db_Select('user_extended','user_language','user_extended_id = '.$id))
			{
				$row = $sql->db_Fetch();
				$lfile = e_LANGUAGEDIR.$row['user_language'].'/lan_signup.php';
			}
		}
		if (is_readable($lfile))
		{
			require_once ($lfile);
		}
		else
		{
			$row['user_language'] = e_LANGUAGE;
			//@FIXME use array
			require_once (e_LANGUAGEDIR.e_LANGUAGE."/lan_signup.php");
		}
		$return_address = (substr(SITEURL,- 1) == "/") ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key;
		$message = LAN_EMAIL_01." ".$name."\n\n".LAN_SIGNUP_24." ".SITENAME.".\n".LAN_SIGNUP_21."\n\n";
		$message .= $return_address."\n\n".SITENAME."\n".SITEURL;
		$mailheader_e107id = $id;
		require_once (e_HANDLER."mail.php");
		if (sendemail($email,LAN_404." ".SITENAME,$message))
		{
		//		echo str_replace("\n","<br>",$message);
			$admin_log->log_event('USET_11',str_replace(array('--ID--','--NAME--','--EMAIL--'),array($id,$name,$email),USRLAN_167),E_LOG_INFORMATIVE);
			$this->show_message(USRLAN_140.": <a href='mailto:".$email."?body=".$return_address."' title=\"".LAN_USER_08."\" >".$name."</a> (".$row['user_language'].") ");
		}
		else
		{
			$this->show_message(USRLAN_141.": ".$name);
		}
	}


	// ------- Ban User. --------------
	function user_ban($user_id)
	{
		global $sql,$user,$admin_log;
		//	$sub_action = $user_id;
		$sql->db_Select("user","*","user_id='".$user_id."'");
		$row = $sql->db_Fetch();
		if (($row['user_perms'] == "0") || ($row['user_perms'] == "0."))
		{
			$this->show_message(USRLAN_7);
		}
		else
		{
			if ($sql->db_Update("user","user_ban='1' WHERE user_id='".$user_id."' "))
			{
				$admin_log->log_event('USET_05',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_161),E_LOG_INFORMATIVE);
				$this->show_message(USRLAN_8);
			}
			if (trim($row['user_ip']) == "")
			{
				$this->show_message(USRLAN_135);
			}
			else
			{
				if ($sql->db_Count("user","(*)","WHERE user_ip = '{$row['user_ip']}'") > 1)
				{
				// Multiple users have same IP address
					$this->show_message(str_replace("{IP}",$row['user_ip'],USRLAN_136));
				}
				else
				{
					if ($e107->add_ban(6,USRLAN_149.$row['user_name'].'/'.$row['user_loginname'],$row['user_ip'],USERID))
					{
					// Successful IP ban
						$this->show_message(str_replace("{IP}",$row['user_ip'],USRLAN_137));
					}
					else
					{
					// IP address on whitelist
						$this->show_message(str_replace("{IP}",$row['user_ip'],USRLAN_150));
					}
				}
			}
		}
		$action = "main";
		if (!$sub_action)
		{
			$sub_action = "user_id";
		}
	}


	function resend_to_all()
	{
		global $sql,$pref,$sql3,$admin_log;
		$count = 0;
		$pause_count = 1;
		$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
		$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;
		if ($sql->db_Select_gen('SELECT user_language FROM `#user_extended` LIMIT 1'))
		{
			$query = "SELECT u.*, ue.* FROM `#user` AS u LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id WHERE u.user_ban = 2 ORDER BY u.user_id DESC";
		}
		else
		{
			$query = 'SELECT * FROM `#user` WHERE user_ban=2';
		}

		$sql3 = e107::getDb('sql3');

		$sql3->db_Select_gen($query);
		while ($row = $sql3->db_Fetch())
		{
			echo $row['user_id']." ".$row['user_sess']." ".$row['user_name']." ".$row['user_email']."<br />";
			$this->resend($row['user_id'],$row['user_sess'],$row['user_name'],$row['user_email'],$row['user_language']);
			if ($pause_count > $pause_amount)
			{
				sleep($pause_time);
				$pause_count = 1;
			}
			sleep(1);
			$pause_count++;
			$count++;
		}
		if ($count)
		{
			$admin_log->log_event('USET_12',str_replace('--COUNT--',$count,USRLAN_168),E_LOG_INFORMATIVE);
		}
	}


	// ---------------------------------------------------------------------
	//		Bounce handling
	// ---------------------------------------------------------------------
	// $bounce_act has the task to perform:
	//	'first_check' - initial read of list of bounces
	//	'delnonbounce' - delete any emails that aren't bounces
	//  'clearemailbounce' - delete email address for any user whose emails bounced
	//	'delchecked' - delete the emails whose comma-separated IDs are in $bounce_arr
	//	'delall' - delete all bounced emails
	function check_bounces($bounce_act = 'first_check',$bounce_arr = '')
	{
		global $sql,$pref;
		include (e_HANDLER.'pop3_class.php');
		if (!trim($bounce_act))
		{
			$bounce_act = 'first_check';
		}
		//	  echo "Check bounces. Action: {$bounce_act}; Entries: {$bounce_arr}<br />";
		$obj = new receiveMail($pref['mail_bounce_user'],$pref['mail_bounce_pass'],$pref['mail_bounce_email'],$pref['mail_bounce_pop3'],varset($pref['mail_bounce_type'],'pop3'));
		$del_count = 0;
		if ($bounce_act != 'first_check')
		{
		// Must do some deleting
			$obj->connect();
			$tot = $obj->getTotalMails();
			$del_array = explode(',',$bounce_arr);
			for ($i = 1; $i <= $tot; $i++)
			{
			// Scan all emails; delete current one if meets the criteria
				$dodel = false;
				switch ($bounce_act)
				{
					case 'delnonbounce' :
						$head = $obj->getHeaders($i);
						$dodel = (!$head['bounce']);
						break;
					case 'clearemailbounce' :
						if (!in_array($i,$del_array))
							break;
						$head = $obj->getHeaders($i);
						if ($head['bounce'])
						{
							if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
							{
								$usr_email = trim($result[0]);
							}
							if ($sql->db_Select('user','user_id, user_name, user_email',"user_email='".$usr_email."' "))
							{
								$row = $sql->db_Fetch();
								if ($sql->db_Update('user',"`user_email`='' WHERE `user_id` = '".$row['user_id']."' ") !== false)
								{
								// echo "Deleting user email {$row['user_email']} for user {$row['user_name']}, id={$row['user_id']}<br />";
									$dodel = true;
								}
							}
						}
						break;
					case 'delall' :
						$dodel = true;
						break;
					case 'delchecked' :
						$dodel = in_array($i,$del_array);
						break;
				}
				if ($dodel)
				{
				//			  echo "Delete email ID {$i}<br />";
					$obj->deleteMails($i);
					$del_count++;
					// Keep track of number of emails deleted
				}
			}
			// End - Delete one email
			$obj->close_mailbox();
			// This actually deletes the emails
		}
		// End of email deletion
		// Now list the emails that are left
		$obj->connect();
		$tot = $obj->getTotalMails();
		$found = false;
		$DEL = ($pref['mail_bounce_delete']) ? true : false;
		$text = "<br /><div><form  method='post' action='".e_SELF.$qry."'><table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td class='fcaption' style='width:5%'>#</td><td class='fcaption'>e107-id</td><td class='fcaption'>email</td><td class='fcaption'>Subject</td><td class='fcaption'>Bounce</td></tr>\n";
		for ($i = 1; $i <= $tot; $i++)
		{
			$head = $obj->getHeaders($i);
			if ($head['bounce'])
			{
			// Its a 'bounce' email
				if (preg_match('/.*X-e107-id:(.*)MIME/',$obj->getBody($i),$result))
				{
					if ($result[1])
					{
						$id[$i] = intval($result[1]);
						// This should be a user ID - but not on special mailers!
						//	Try and pull out an email address from body - should be the one that failed
						if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
						{
							$emails[$i] = "'".$result[0]."'";
						}
						$found = true;
					}
				}
				elseif (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					if ($result[0] && $result[0] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[0]."'";
						$found = true;
					}
					elseif ($result[1] && $result[1] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[1]."'";
						$found = true;
					}
				}
				if ($DEL && $found)
				{
				// Auto-delete bounced emails once noticed (if option set)
					$obj->deleteMails($i);
					$del_count++;
				}
			}
			else
			{
			// Its a warning message or similar
			//			  $id[$i] = '';			// Don't worry about an ID for now
			//				Try and pull out an email address from body - should be the one that failed
				if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					$wmails[$i] = "'".$result[0]."'";
				}
			}
			$text .= "<tr><td class='forumheader3'>".$i."</td><td class='forumheader3'>".$id[$i]."</td><td class='forumheader3'>".(isset ($emails[$i]) ? $emails[$i] : $wmails[$i])."</td><td class='forumheader3'>".$head['subject']."</td><td class='forumheader3'>".($head['bounce'] ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON);
			$text .= "<input type='checkbox' name='delete_email[]' value='{$i}' /></td></tr>\n";
		}
		if ($del_count)
		{
			$admin_log->log_event('USET_13',str_replace('--COUNT--',$del_count,USRLAN_169),E_LOG_INFORMATIVE);
		}
		if ($tot)
		{
		// Option to delete emails - only if there are some in the list
			$text .= "</table><table style='".ADMIN_WIDTH."'><tr>
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delnonbouncesubmit' value='".USRLAN_183."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='clearemailbouncesubmit' value='".USRLAN_184."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delcheckedsubmit' value='".USRLAN_179."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delallsubmit' value='".USRLAN_180."' /></td>\n
			</td></tr>";
		}
		$text .= "</table></form></div>";
		array_unique($id);
		array_unique($emails);
		$all_ids = implode(',',$id);
		$all_emails = implode(',',$emails);
		$obj->close_mailbox();
		// This will actually delete emails
		// $tot has total number of emails in the mailbox
		$found = count($emails);
		// $found - Number of bounce emails found
		// $del_count has number of emails deleted
		// Update bounce status for users
		$ed = $sql->db_Update('user',"user_ban=3 WHERE (`user_id` IN (".$all_ids.") OR `user_email` IN (".$all_emails.")) AND user_sess !='' ");
		if (!$ed)
			$ed = '0';
		$this->show_message(str_replace(array('{TOTAL}','{DELCOUNT}','{DELUSER}','{FOUND}'),array($tot,$del_count,$ed,$found),USRLAN_155).$text);
	}


	function check_allowed($class_id) // check userclass change is permitted.
	{
		global $e_userclass;
		if (!isset ($e_userclass->class_tree[$class_id]))
		{
			header("location:".SITEURL);
			exit;
		}
		if (!getperms("0") && !check_class($e_userclass->class_tree[$class_id]['userclass_editclass']))
		{
			header("location:".SITEURL);
			exit;
		}
		return true;
	}


	// ------------------------------------------------------------------------
	function show_userclass($userid)
	{
		global $sql,$ns, $e_userclass;

		$sql->db_Select("user","*","user_id={$userid} ");
		$row = $sql->db_Fetch();
		$caption = UCSLAN_6." <b>".$row['user_name']."</b> (".$row['user_class'].")";
		$text = "	<div>
					<form method='post' action='".e_SELF."?".e_QUERY."'>
                    <table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tr>
						<td>";
		$text .= $e_userclass->vetted_tree('userclass',array($e_userclass,'checkbox_desc'),$row['user_class'],'classes');
		$text .= '</td></tr>
		</table>';

		$text .= "	<div class='buttons-bar center'>
 					<input type='hidden' name='userid' value='{$userid}' />
					<input type='checkbox' name='notifyuser' value='1' /> ".UCSLAN_8."&nbsp;&nbsp;
					<input class='button' type='submit' name='updateclass' value='".UCSLAN_7."' />
					</div>
					</form>
					</div>";

		$ns->tablerender($caption,$text);
	}


/*
	Appears to be unused function
	function user_remuserclass($userid,$uclass)
	{
		global $sql,$sql2;
		$emessage = &eMessage::getInstance();
		if ($uclass[0] == 0)
		{
			if($sql->db_Update("user","user_class='' WHERE user_id={$userid}")===TRUE)
			{
				$emessage->add(UCSLAN_9, E_MESSAGE_SUCCESS); // classes updated;
			}
			else
			{
				$emessage->add(UCSLAN_9, E_MESSAGE_SUCCESS); // classes updated;
			}
		}
		else
		{
			$eu = new e_userclass;
			if($sql->db_Select("user","user_id,user_class","user_id={$userid} LIMIT 1"))
			{
				$row = $sql->db_Fetch();
				$eu->class_remove($uclass[0], array($row['user_id']=>$row['user_class']));
			}
			$emessage->add(UCSLAN_9, E_MESSAGE_SUCCESS); // classes updated;
		}
	}
*/

    // Set userclass for user(s).
	function user_userclass($userid,$uclass,$mode=FALSE)
	{
		global $admin_log, $e_userclass;
		$sql = e107::getDb();

		$remuser = true;
        $emessage = &eMessage::getInstance();

		if($_POST['notifyuser'] || $mode !=='clear')
		{
    		$sql->db_Select("user","*","user_id={$userid} ");
			$row = $sql->db_Fetch();
			$curClass = varset($row['user_class']) ? explode(",",$row['user_class']) : array();
        }

    	foreach ($uclass as $a)
		{
			$a = intval($a);
			$this->check_allowed($a);
			if($a !=0) // if 0 - then do not add.
			{
				$curClass[] = $a;
			}
		}

		if($mode == "remove") // remove selected classes
		{
			$curClass = array_diff($curClass,$uclass);
		}

		if($mode == "clear") // clear all classes
		{
		//	$curClass = array();
		}

        $curClass = array_unique($curClass);

        $svar = is_array($curClass) ? implode(",",$curClass) : "";

		if($sql->db_Update("user","user_class='".$svar."' WHERE user_id={$userid} ")===TRUE)
		{
			$message = UCSLAN_9;
			if ($_POST['notifyuser'])
			{

				$message .= "<br />".UCSLAN_1.":</b> ".$row['user_name']."<br />";
				require_once (e_HANDLER."mail.php");
				$messaccess = '';
				foreach ($curClass as $a)
				{
					if (!isset ($e_userclass->fixed_classes[$a]))
					{
						$messaccess .= $e_userclass->class_tree[$a]['userclass_name']." - ".$e_userclass->class_tree[$a]['userclass_description']."\n";
					}
				}
				if ($messaccess == '')
					$messaccess = UCSLAN_12."\n";
				$send_to = $row['user_email'];
				$subject = UCSLAN_2;
				$message = UCSLAN_3." ".$row['user_name'].",\n\n".UCSLAN_4." ".SITENAME."\n( ".SITEURL." )\n\n".UCSLAN_5.": \n\n".$messaccess."\n".UCSLAN_10."\n".SITEADMIN."\n( ".SITENAME." )";
				//    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User class change",str_replace("\n","<br />",$message),FALSE,LOG_TO_ROLLING);
				sendemail($send_to,$subject,$message);
			}
			$admin_log->log_event('USET_14',str_replace(array('--UID--','--CLASSES--'),array($id,$svar),UCSLAN_11),E_LOG_INFORMATIVE);

            $emessage->add($message, E_MESSAGE_SUCCESS);
		}
		else
		{
           //	$emessage->add("Update Failed", E_MESSAGE_ERROR);
		}
	}


}


// End class users
function users_adminmenu()
{
	global $user;
	global $action;
	$user->show_options($action);
}


function deleteRank($rankId)
{
	global $emessage;
	$e107 = e107 :: getInstance();
	$rankId = (int) $rankId;
	$e107->ecache->clear_sys('nomd5_user_ranks');
	if ($e107->sql->db_Delete('generic',"gen_id='{$rankId}'"))
	{
		$emessage->add(USRLAN_218,E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(USRLAN_218,E_MESSAGE_FAIL);
	}
}


function updateRanks()
{
	global $pref,$emessage;
	$e107 = e107 :: getInstance();
	$config = array();
	$ranks_calc = '';
	$ranks_flist = '';
	foreach ($_POST['op'] as $f => $o)
	{
		$config[$f]['op'] = $o;
		$config[$f]['val'] = varset($_POST['val'][$f],'');
		if ($_POST['val'][$f])
		{
			$ranks_calc .= ($ranks_calc ? ' + ' : '').'({'.$f.'} '." $o {$_POST['val'][$f]}".' )';
			$ranks_flist .= ($ranks_flist ? ',' : '').$f;
		}
	}
	$e107->sql->db_Delete('generic',"gen_type = 'user_rank_config'");
	$tmp = array();
	$tmp['data']['gen_type'] = 'user_rank_config';
	$tmp['data']['gen_chardata'] = serialize($config);
	$tmp['_FIELD_TYPES']['gen_type'] = 'string';
	$tmp['_FIELD_TYPES']['gen_chardata'] = 'escape';
	$e107->sql->db_Insert('generic',$tmp);
	$pref['ranks_calc'] = $ranks_calc;
	$pref['ranks_flist'] = $ranks_flist;
	save_prefs();
	//Delete existing rank data
	$e107->sql->db_Delete('generic',"gen_type = 'user_rank_data'");
	//Add main site admin info
	$tmp = array();
	$tmp['_FIELD_TYPES']['gen_datestamp'] = 'int';
	$tmp['_FIELD_TYPES']['gen_ip'] = 'todb';
	$tmp['_FIELD_TYPES']['gen_user_id'] = 'int';
	$tmp['_FIELD_TYPES']['gen_chardata'] = 'todb';
	$tmp['_FIELD_TYPES']['gen_intdata'] = 'int';
	$tmp['data']['gen_datestamp'] = 1;
	$tmp['data']['gen_type'] = 'user_rank_data';
	$tmp['data']['gen_ip'] = $_POST['calc_name']['main_admin'];
	$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['main_admin'],0);
	$tmp['data']['gen_chardata'] = $_POST['calc_img']['main_admin'];
	$e107->sql->db_Insert('generic',$tmp);
	//Add site admin info
	unset ($tmp['data']);
	$tmp['data']['gen_type'] = 'user_rank_data';
	$tmp['data']['gen_datestamp'] = 2;
	$tmp['data']['gen_ip'] = $_POST['calc_name']['admin'];
	$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['admin'],0);
	$tmp['data']['gen_chardata'] = $_POST['calc_img']['admin'];
	$e107->sql->db_Insert('generic',$tmp);
	//Add all current site defined ranks
	if (isset ($_POST['field_id']))
	{
		foreach ($_POST['field_id'] as $fid => $x)
		{
			unset ($tmp['data']);
			$tmp['data']['gen_type'] = 'user_rank_data';
			$tmp['data']['gen_ip'] = varset($_POST['calc_name'][$fid],'');
			$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx'][$fid],0);
			$tmp['data']['gen_chardata'] = varset($_POST['calc_img'][$fid],'');
			$tmp['data']['gen_intdata'] = varset($_POST['calc_lower'][$fid],'_NULL_');
			$e107->sql->db_Insert('generic',$tmp);
		}
	}
	//Add new rank, if posted
	if (varset($_POST['new_calc_lower']))
	{
		unset ($tmp['data']);
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_datestamp'] = 0;
		$tmp['data']['gen_ip'] = varset($_POST['new_calc_name']);
		$tmp['data']['gen_user_id'] = varset($_POST['new_calc_pfx'],0);
		$tmp['data']['gen_chardata'] = varset($_POST['new_calc_img']);
		$tmp['data']['gen_intdata'] = varset($_POST['new_calc_lower']);
		$e107->sql->db_Insert('generic',$tmp);
	}
	$e107->ecache->clear_sys('nomd5_user_ranks');
	$emessage->add(USRLAN_217,E_MESSAGE_SUCCESS);
}


function showRanks()
{
	global $pref,$emessage;
	$frm 	= new e_form;
	$e107 = e107::getInstance();

	include_once (e_HANDLER.'file_class.php');
	require_once (e_HANDLER.'message_handler.php');

/*
	$daysregged = max(1, round((time() - $user_join) / 86400))."days";
	$level = ceil((($user_forums * 5) + ($user_comments * 5) + ($user_chats * 2) + $user_visits)/4);
*/

	$ranks = e107::getRank()->getRankData();
	$tmp = e107::getFile()->get_files(e_IMAGE.'ranks', '.*?\.(png|gif|jpg)');
	foreach($tmp as $k => $v){
		$imageList[] = $v['fname'];
	}
	unset($tmp);
	natsort($imageList);

	$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	";
/*
	$config = array();
	if ($e107->sql->db_Select('generic','gen_chardata', "gen_type='user_rank_config'", 'default'))
	{
		$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
		$config = unserialize($row['gen_chardata']);
	}
	$fieldList = array('core' => array(),'extended' => array());
	$fieldList['core'] = array('comments' => USRLAN_201,'visits' => USRLAN_202,'daysregged' => USRLAN_203);
	foreach ($e107->extended_struct as $field)
	{
		if (strpos($field['Type'],'int') !== false && $field['Field'] != 'user_extended_id')
		{
			$fieldList['extended'][] = substr($field['Field'],5);
		}
	}
	$fields = array(
		'source' => array('title' => USRLAN_197, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'fieldName' => array('title' => USRLAN_198, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'operation' => array('title' => USRLAN_199, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'value' => array('title' => USRLAN_200, 'type' => 'int', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
	);

	$opArray = array('*','+','-');
	$text .= "
	<form method='post' action='".e_SELF."?".e_QUERY."'>
   <fieldset id='core-userranks-list'>

	<table cellpadding='0' cellspacing='0' class='adminlist'>".
	$frm->colGroup($fields, array_keys($fields)).
	$frm->thead($fields, array_keys($fields));
	foreach ($fieldList['core'] as $k => $f)
	{
		$text .= "
		<tr>
		<td class='label'>".USRLAN_204."</td>
		<td class='label'>{$f}</td>
		<td class='control'>
			<select name='op[{$k}]' class='tbox'>
		";
		foreach ($opArray as $op)
		{
			$sel = (varset($config[$k]['op']) == $op ? "selected='selected'" : '');
			$text .= "<option value='{$op}' {$sel}>{$op}</option>";
		}
		$text .= "
			</select>
		</td>
		<td class='control'><input type='text' class='tbox' name='val[{$k}]' value='".varset($config[$k]['val'])."' size='3' maxlength='3' /></td>
		</tr>
		";
	}
	if (count($fieldList['extended']))
	{
		foreach ($fieldList['extended'] as $f)
		{
			$text .= "
			<tr>
				<td colspan='4'>&nbsp;</td>
			</tr>
			<tr>
			<td class='label'>".USRLAN_205."</td>
			<td class='label'>{$f}</td>
			<td class='control'>
				<select name='op[{$f}]' class='tbox'>
			";
			foreach ($opArray as $op)
			{
				$sel = (varset($config[$f]['op']) == $op ? "selected='selected'" : '');
				$text .= "<option value='{$op}' {$sel}>{$op}</option>";
			}
			$text .= "
				</select>
			</td>
			<td class='control'>
			<input type='text' class='tbox' name='val[{$f}]' value='".varset($config[$f]['val'])."' size='3' maxlength='3' value='' />
			</td>
			</tr>
			";
		}
	}
	if (isset ($pref['ranks_calc']))
	{
		$text .= "<tr>
								<td class='label' colspan='4'><br />".USRLAN_206.": {$pref['ranks_calc']}</td>
							</tr>
							";
	}
	$text .= '</table>';
*/
	$e107->ns->tablerender('',$emessage->render());
//	$e107->ns->tablerender('Rank Calculation fields',$text);

	$fields = array(
		'type' => array('title' => USRLAN_207, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'rankName' => array('title' => USRLAN_208, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'lowThresh' => array('title' => USRLAN_209, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'langPrefix' => array('title' => USRLAN_210, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		'rankImage' => array('title' => USRLAN_210, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
	);


	$text .= "
	<table cellpadding='0' cellspacing='0' class='adminlist'>".
	$frm->colGroup($fields, array_keys($fields)).
	$frm->thead($fields, array_keys($fields));

	$info = $ranks['special'][1];
	$val = $e107->tp->toForm($info['name']);
	$pfx = ($info['lan_pfx'] ? "checked='checked'" : '');
	$text .= "
	<tr>
		<td class='control'>".LAN_MAINADMIN."</td>
		<td class='control'>
			<input class='tbox' type='text' name='calc_name[main_admin]' value='{$val}' />
		</td>
		<td class='control'>N/A</td>
		<td class='control'><input type='checkbox' name='calc_pfx[main_admin]' {$pfx} value='1' /></td>
		<td class='control'>".RankImageDropdown($imageList,'calc_img[main_admin]',$info['image'])."</td>
	</tr>
	";
	$info = $ranks['special'][2];
	$val = $e107->tp->toForm($info['name']);
	$pfx = ($info['lan_pfx'] ? "checked='checked'" : '');
	$text .= "
	<tr>
		<td class='control'>".LAN_ADMIN."</td>
		<td class='control'>
			<input class='tbox' type='text' name='calc_name[admin]' value='{$val}' />
		</td>
		<td class='control'>N/A</td>
		<td class='control'><input type='checkbox' name='calc_pfx[admin]' {$pfx} value='1' /></td>
		<td class='control'>".RankImageDropdown($imageList,'calc_img[admin]',$info['image'])."</td>
	</tr>
	<tr>
		<td colspan='5'>&nbsp;</td>
	</tr>
	";
	foreach ($ranks['data'] as $k => $r)
	{
		$pfx_checked = ($r['lan_pfx'] ? "checked='checked'" : '');
		$text .= "
		<tr>
			<td class='control'>".USRLAN_212."</td>
			<td class='control'>
				<input type='hidden' name='field_id[{$k}]' value='1' />
				<input class='tbox' type='text' name='calc_name[$k]' value='{$r['name']}' />
			</td>
			<td class='control'><input class='tbox' type='text' size='5' name='calc_lower[$k]' value='{$r['thresh']}' /></td>
			<td class='control'><input type='checkbox' name='calc_pfx[$k]' value='1' {$pfx_checked} /></td>
			<td class='control'>".RankImageDropdown($imageList,"calc_img[$k]",$r['image'])."&nbsp;".$frm->submit_image("delete_rank[{$r['id']}]",LAN_DELETE,'delete',USRLAN_213.": [{$r['name']}]?")."
			</td>
		</tr>
		";
	}
	$text .= "

	<tr>
		<td class='control' colspan='5'>&nbsp;</td>
	</tr>
	<tr>
		<td class='control'>".USRLAN_214."</td>
		<td class='control'><input class='tbox' type='text' name='new_calc_name' value='' /></td>
		<td class='control'><input class='tbox' type='text' size='5' name='new_calc_lower' value='' /></td>
		<td class='control'><input type='checkbox' name='new_calc_pfx' value='1' /></td>
		<td class='control'>".RankImageDropdown($imageList,'new_calc_img')."</td>
	</tr>
	<tr>
		<td colspan='5' style='text-align:center'>
			<br />
			<input type='submit' name='updateRanks' value='".USRLAN_215."' />
		</td>
	</tr>
	";
	$text .= '</table></form>';
	$e107->ns->tablerender('Ranks',$text);
	include (e_ADMIN.'footer.php');
	exit;
}


function RankImageDropdown(&$imgList, $field, $curVal = '')
{
	$ret = "
	<select class='tbox' name='{$field}'>
	<option value=''>".USRLAN_216."</option>
	";
	foreach ($imgList as $img)
	{
		$sel = ($img == $curVal ? "selected='selected'" : '');
		$ret .= "\n<option {$sel}>{$img}</option>";
	}
	$ret .= '</select>';
	return $ret;
}


?>
