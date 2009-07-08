<?php
/*
* e107 website system
*
* Copyright (C) 2001-2008 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Administration Area - Users
*
* $Source: /cvs_backup/e107_0.8/e107_admin/users.php,v $
* $Revision: 1.38 $
* $Date: 2009-07-08 07:00:26 $
* $Author: e107coders $
*
*/
require_once('../class2.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_user.php');

if (!getperms('4'))
{
	header('location:'.$e107->url->getUrl('core:core', 'main', 'action=index'));
	exit;
}


if(varset($_POST['useraction']))
{
	foreach($_POST['useraction'] as $key=>$val)
	{
		if($val)
		{
	    	$_POST['useraction'] = $val;
			$_POST['userip'] 	= $_POST['userip'][$key];
	        $_POST['userid'] 	= $key;
			break;
		}
	}
}

if (isset($_POST['useraction']) && $_POST['useraction'] == 'userinfo')
{
	header('location:'.e_ADMIN."userinfo.php?".$e107->tp->toDB($_POST['userip']));
	exit;
}


if (isset($_POST['useraction']) && $_POST['useraction'] == 'usersettings')
{
	header('location:'.	$e107->url->getUrl('core:user', 'main', 'func=settings&id='.(int)$_POST['userid']));
	exit;
}


if (isset($_POST['useraction']) && $_POST['useraction'] == 'userclass')
{
	header('location:'.e_ADMIN.'userclass.php?'.$e107->tp->toDB($_POST['userid'].'.'.e_QUERY));
	exit;
}

function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	return "<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>";
}

require_once(e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();

if(isset($_POST['delete_rank']))
{
	foreach($_POST['delete_rank'] as $k => $v)
	{
		deleteRank($k);
	}
}

if(isset($_POST['updateRanks']))
{
	updateRanks();
}

$e_sub_cat = 'users';

require_once('auth.php');
$user = new users;
require_once(e_HANDLER.'form_handler.php');
require_once(e_HANDLER.'userclass_class.php');

include_once(e_HANDLER.'user_extended_class.php');
require_once(e_HANDLER.'validator_class.php');



	require_once(e_HANDLER.'user_handler.php');
  //      $userMethods = new UserHandler;
//    	$colList = $userMethods->getNiceNames(TRUE);

$ue = new e107_user_extended;
$userMethods = new UserHandler;
$user_data = array();

$frm = new e_form;

if (e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1],'');
	$id = varset($tmp[2],0);
	$from = varset($tmp[3],0);
	unset($tmp);
}

$from = varset($from, 0);
$amount = 30;

if($action == 'ranks')
{
	show_ranks();
}

// ------- Check for Bounces --------------
$bounce_act = '';
if (isset($_POST['check_bounces'])) $bounce_act = 'first_check';
if (isset($_POST['delnonbouncesubmit'])) $bounce_act = 'delnonbounce';
if (isset($_POST['clearemailbouncesubmit'])) $bounce_act = 'clearemailbounce';
if (isset($_POST['delcheckedsubmit'])) $bounce_act = 'delchecked';
if (isset($_POST['delallsubmit'])) $bounce_act = 'delall';
if ($bounce_act)
{
	$user->check_bounces($bounce_act, implode(',',$_POST['delete_email']));
	require_once("footer.php");
	exit;
}




// ------- Resend Email. --------------
if (isset($_POST['resend_mail']))
{
	$user->resend($_POST['resend_id'],$_POST['resend_key'],$_POST['resend_name'],$_POST['resend_email']);
}

// ------- Resend Email. --------------
if(isset($_POST['resend_to_all']))
{
	$user->resend_to_all();
}



// ------- Test Email. --------------
if (isset($_POST['test_mail']))
{
	require_once(e_HANDLER.'mail_validation_class.php');
	list($adminuser,$adminhost) = split ("@", SITEADMINEMAIL);
	$validator = new email_validation_class;
	$validator->localuser= $adminuser;
	$validator->localhost= $adminhost;
	$validator->timeout=5;
	$validator->debug=1;
	$validator->html_debug=1;
	$text = "<div style='".ADMIN_WIDTH."'>";
	ob_start();
	$email_status = $validator->ValidateEmailBox($_POST['test_email']);
	$text .= ob_get_contents();
	ob_end_clean();
	$text .= "</div>";
	$caption = $_POST['test_email']." - ";
	$caption .= ($email_status == 1)? "Valid": "Invalid";

	if($email_status == 1){
		$text .= "<form method='post' action='".e_SELF.$qry."'>
		<div style='text-align:left'>
		<input type='hidden' name='useraction' value='resend' />\n
		<input type='hidden' name='userid' value='".$_POST['test_id']."' />\n
		<input class='button' type='submit' name='resend_' value='".USRLAN_112."' />\n</div></form>\n";
		$text .= "<div>";
	}


	$ns->tablerender($caption, $text);
	unset($id, $action, $sub_cation);
}


// ------- Update Options. --------------
if (isset($_POST['update_options']))
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
	if ($admin_log->logArrayDiffs($temp, $pref, 'USET_03'))
	{
		save_prefs();		// Only save if changes
		$user->show_message(USRLAN_1);
	}
	else
	{
		$user->show_message(USRLAN_193);
	}
}


// ------- Prune Users. --------------
if (isset($_POST['prune']))
{
	$e107cache->clear('online_menu_member_total');
	$e107cache->clear('online_menu_member_newest');
	$text = USRLAN_56.' ';
	$bantype = $_POST['prune_type'];
    if($bantype == 30) // older than 30 days.
	{
    	$bantype = 2;
		$ins = " AND user_join < ".strtotime("-30 days");
	}

	if ($sql->db_Select("user", "user_id, user_name", "user_ban= {$bantype}".$ins))
	{
		$uList = $sql->db_getList();
		foreach($uList as $u)
		{
			$text .= $u['user_name']." ";
			$sql->db_Delete("user", "user_id='{$u['user_id']}' ");
			$sql->db_Delete("user_extended", "user_extended_id='{$u['user_id']}' ");
		}
		$admin_log->log_event('USET_04',str_replace(array('--COUNT--','--TYPE--'),array(count($uList),$bantype),USRLAN_160),E_LOG_INFORMATIVE);
	}
	$ns->tablerender(USRLAN_57, "<div style='text-align:center'><b>".$text."</b></div>");
	unset($text);
}


// ------- Quick Add User --------------
if (isset($_POST['adduser']))
{
	if (!$_POST['ac'] == md5(ADMINPWCHANGE))
	{
		exit;
	}

	$e107cache->clear('online_menu_member_total');
	$e107cache->clear('online_menu_member_newest');

	$error = FALSE;

	if (isset($_POST['generateloginname']))
	{
		$_POST['loginname'] = $userMethods->generateUserLogin($pref['predefinedLoginName']);
	}
	if (isset($_POST['generatepassword']))
	{
		$_POST['password1'] = $userMethods->generateRandomString('**********');		// 10-char password should be enough
		$_POST['password2'] = $_POST['password1'];
	}

	// Now validate everything
	$allData = validatorClass::validateFields($_POST,$userMethods->userVettingInfo, TRUE);		// Do basic validation
	validatorClass::checkMandatory('user_name,user_loginname', $allData);						// Check for missing fields (email done in userValidation() )
	validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', 0);		// Do basic DB-related checks
	$userMethods->userValidation($allData);														// Do user-specific DB checks
	if (!isset($allData['errors']['user_password']))
	{	// No errors in password - keep it outside the main data array
		$savePassword = $allData['data']['user_password'];
		unset($allData['data']['user_password']);						// Delete the password value in the output array
	}
	unset($_POST['password1']);					// Restrict the scope of this
	unset($_POST['password2']);
	if (!check_class($pref['displayname_class'], $allData['data']['user_class']))
	{
		if ($allData['data']['user_name'] != $allData['data']['user_loginname'])
		{
			$allData['errors']['user_name'] = ERR_FIELDS_DIFFERENT;
		}
	}

	if (count($allData['errors']))
	{
		require_once(e_HANDLER."message_handler.php");
		$temp = validatorClass::makeErrorList($allData,'USER_ERR_','%n - %x - %t: %v', '<br />', $userMethods->userVettingInfo);
		message_handler('P_ALERT', $temp);
		$error = TRUE;
	}

	// Always save some of the entered data - then we can redisplay on error
	$user_data = &$allData['data'];

	if (!$error)
	{
		$message = '';
		$user_data['user_password'] = $userMethods->HashPassword($savePassword,$loginname);
		$user_data['user_join'] = time();
		if ($userMethods->needEmailPassword())
		{	// Save separate password encryption for use with email address
			$user_data['user_prefs'] = serialize(array('email_password' => $userMethods->HashPassword($savePassword, $user_data['user_email'])));
		}
		$userMethods->userClassUpdate($allData['data'], 'userall');			// Set any initial classes
		$userMethods->addNonDefaulted($user_data);
		validatorClass::addFieldTypes($userMethods->userVettingInfo,$allData);
		//FIXME - (SecretR) there is a better way to fix this (missing default value, sql error in strict mode - user_realm is to be deleted from DB later)
		$allData['data']['user_realm'] = '';
		if ($sql -> db_Insert('user', $allData))
		{
			// Add to admin log
			$admin_log->log_event('USET_02',"UName: {$user_data['user_name']}; Email: {$user_data['user_email']}",E_LOG_INFORMATIVE);
			// Add to user audit trail
			$admin_log->user_audit(USER_AUDIT_ADD_ADMIN,$user_data, 0,$user_data['user_loginname']);
			$e_event->trigger('userfull', $user_data);  // send everything available for user data - bit sparse compared with user-generated signup
			if (isset($_POST['sendconfemail']))
			{  // Send confirmation email to user
				require_once(e_HANDLER.'mail.php');
				$e_message = str_replace(array('--SITE--','--LOGIN--','--PASSWORD--'),array(SITEURL,$loginname,$savePassword),USRLAN_185).USRLAN_186;
				if (sendemail($user_data['user_email'],USRLAN_187.SITEURL,$e_message,$user_data['user_login'],'',''))
				{
					$message = USRLAN_188.'<br /><br />';
				}
				else
				{
					$message = USRLAN_189.'<br /><br />';
				}
			}
			$message .= str_replace('--NAME--',$user_data['user_name'], USRLAN_174) ;
			if (isset($_POST['generateloginname'])) $message .= '<br /><br />'.USRLAN_173.': '.$loginname;
			if (isset($_POST['generatepassword'])) $message .= '<br /><br />'.USRLAN_172.': '.$savePassword;

			unset($user_data);		// Don't recycle the data once the user's been accepted without error
		}
	}
	if (isset($message)) $user->show_message($message);
}




// ------- Bounce --> Unverified --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "reqverify")
{
	$sql->db_Select("user", "*", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	extract($row);
	$sql->db_Update("user", "user_ban='2' WHERE user_id='".$_POST['userid']."' ");
	$user->show_message("User now has to verify");
	$action = "main";
	if(!$sub_action) {$sub_action = "user_id"; }
}



// ------- Ban User. --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "ban")
{
	//	$sub_action = $_POST['userid'];
	$sql->db_Select("user", "*", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	if (($row['user_perms'] == "0") || ($row['user_perms'] == "0."))
	{
		$user->show_message(USRLAN_7);
	}
	else
	{
		if($sql->db_Update("user", "user_ban='1' WHERE user_id='".$_POST['userid']."' "))
		{
			$admin_log->log_event('USET_05',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_161),E_LOG_INFORMATIVE);
			$user->show_message(USRLAN_8);
		}
		if(trim($row['user_ip']) == "")
		{
			$user->show_message(USRLAN_135);
		}
		else
		{
			if($sql->db_Count("user", "(*)", "WHERE user_ip = '{$row['user_ip']}'") > 1)
			{	// Multiple users have same IP address
				$user->show_message(str_replace("{IP}", $row['user_ip'], USRLAN_136));
			}
			else
			{
				if ($e107->add_ban(6,USRLAN_149.$row['user_name'].'/'.$row['user_loginname'],$row['user_ip'],USERID))
				{	// Successful IP ban
					$user->show_message(str_replace("{IP}", $row['user_ip'], USRLAN_137));
				}
				else
				{	// IP address on whitelist
					$user->show_message(str_replace("{IP}", $row['user_ip'], USRLAN_150));
				}
			}
		}
	}
	$action = "main";
	if(!$sub_action){$sub_action = "user_id"; }
}


// ------- Unban User --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "unban")
{
	$sql->db_Select("user", "user_name,user_ip", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	$sql->db_Update("user", "user_ban='0' WHERE user_id='".$_POST['userid']."' ");
	$sql -> db_Delete("banlist", " banlist_ip='{$row['user_ip']}' ");
	$admin_log->log_event('USET_06',str_replace(array('--UID--','--NAME--'),array($_POST['userid'],$row['user_name']),USRLAN_162),E_LOG_INFORMATIVE);
	$user->show_message(USRLAN_9);
	$action = "main";
	if(!$sub_action){$sub_action = "user_id"; }
}

// ------- Resend Email Confirmation. --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == 'resend')
{
	$qry = (e_QUERY) ? "?".e_QUERY : "";
	if ($sql->db_Select("user", "*", "user_id='".$_POST['userid']."' ")) {
		$resend = $sql->db_Fetch();
		$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
		$text .= USRLAN_116." <b>".$resend['user_name']."</b><br /><br />

		<input type='hidden' name='resend_id' value='".$_POST['userid']."' />\n
		<input type='hidden' name='resend_name' value='".$resend['user_name']."' />\n
		<input type='hidden' name='resend_key' value='".$resend['user_sess']."' />\n
		<input type='hidden' name='resend_email' value='".$resend['user_email']."' />\n
		<input class='button' type='submit' name='resend_mail' value='".USRLAN_112."' />\n</div></form>\n";
		$caption = USRLAN_112;
		$ns->tablerender($caption, $text);
		require_once("footer.php");
		exit;
	}
}




// ------- TEst Email confirmation. --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == 'test')
{
	$qry = (e_QUERY) ? "?".e_QUERY : "";
	if ($sql->db_Select("user", "*", "user_id='".$_POST['userid']."' ")) {
		$test = $sql->db_Fetch();
		$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
		$text .= USRLAN_117." <br /><b>".$test['user_email']."</b><br /><br />
		<input type='hidden' name='test_email' value='".$test['user_email']."' />\n
		<input type='hidden' name='test_id' value='".$_POST['userid']."' />\n
		<input class='button' type='submit' name='test_mail' value='".USRLAN_118."' />\n</div></form>\n";
		$caption = USRLAN_118;
		$ns->tablerender($caption, $text);
		require_once("footer.php");
		exit;
	}
}




// ------- Delete User --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == 'deluser')
{
	if ($_POST['confirm'])
	{
		$uid = intval($_POST['userid']);
		if ($sql->db_Delete("user", "user_id=".$uid." AND user_perms != '0' AND user_perms != '0.'"))
		{
			$sql->db_Delete("user_extended", "user_extended_id='".$uid."' ");
			$admin_log->log_event('USET_07',str_replace('--UID--',$uid,USRLAN_163),E_LOG_INFORMATIVE);
			$e_event->trigger('userdelete', $temp = array('user_id' => $uid));
			$user->show_message(USRLAN_10);
		}
		if(!$sub_action){ $sub_action = "user_id"; }
		if(!$id){ $id = "DESC"; }
	}
	else
	{	// Put up confirmation
		if ($sql->db_Select("user", "*", "user_id='".$_POST['userid']."' ")) {
			$row = $sql->db_Fetch();
			$qry = (e_QUERY) ? "?".e_QUERY : "";
			$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
			$text .= "<div>
			<input type='hidden' name='useraction' value='deluser' />
			<input type='hidden' name='userid' value='{$row['user_id']}' /></div>". USRLAN_13."
			<br /><br /><span class='indent'>#{$row['user_id']} : {$row['user_name']}</span>
			<br /><br />
			<input type='submit' class='button' name='confirm' value='".USRLAN_17."' />
			&nbsp;&nbsp;
			<input type='button' class='button' name='cancel' value='".LAN_CANCEL."' onclick=\"location.href='".e_SELF.$qry."' \"/>
			</div>
			</form>
			";
			$ns->tablerender(USRLAN_16, $text);
			require_once("footer.php");
			exit;
		}
	}
}



// ------- Make Admin --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "admin" && getperms('3'))
{
	$sql->db_Select("user", "user_id, user_name", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	$sql->db_Update("user", "user_admin='1' WHERE user_id='".$_POST['userid']."' ");
	$admin_log->log_event('USET_08',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_164),E_LOG_INFORMATIVE);
	$user->show_message($row['user_name']." ".USRLAN_3." <a href='".e_ADMIN."administrator.php?edit.{$row['user_id']}'>".USRLAN_4."</a>");
	$action = "main";
	if(!$sub_action){ $sub_action = "user_id"; }
	if(!$id){ $id = "DESC"; }
}





// ------- Remove Admin --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "unadmin" && getperms('3'))
{
	$sql->db_Select("user", "*", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	extract($row);
	if ($user_perms == "0")
	{
		$user->show_message(USRLAN_5);
	}
	else
	{
		$sql->db_Update("user", "user_admin='0', user_perms='' WHERE user_id='".$_POST['userid']."'");
		$admin_log->log_event('USET_09',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_165),E_LOG_INFORMATIVE);
		$user->show_message($user_name." ".USRLAN_6);
		$action = "main";
		if(!$sub_action){ $sub_action = "user_id"; }
		if(!$id){ $id = "DESC"; }
	}
}




// ------- Approve User. --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "verify")
{
	$uid = intval($_POST['userid']);

	if ($sql->db_Select("user", "*", "user_id='".$uid."' "))
	{
		if ($row = $sql->db_Fetch())
		{
			$dbData = array();
			$dbData['WHERE'] = "user_id=".$uid;
			$dbData['data'] = array('user_ban'=>'0', 'user_sess'=>'');
			// Add in the initial classes as necessary
			if ($userMethods->userClassUpdate($row, 'userall'))
			{
				$dbData['data']['user_class'] = $row['user_class'];
			}
			$userMethods->addNonDefaulted($dbData);
			validatorClass::addFieldTypes($userMethods->userVettingInfo,$dbData);
			$sql->db_Update('user',$dbData);
			$admin_log->log_event('USET_10',str_replace(array('--UID--','--NAME--'),array($row['user_id'],$row['user_name']),USRLAN_166),E_LOG_INFORMATIVE);
			$e_event->trigger('userfull', $row);			// 'New' event

			$user->show_message(USRLAN_86);
			if(!$action){ $action = "main"; }
			if(!$sub_action){ $sub_action = "user_id"; }
			if(!$id){ $id = "DESC"; }

			if($pref['user_reg_veri'] == 2)
			{
				if($sql->db_Select("user", "user_email, user_name", "user_id = '{$uid}'"))
				{
					$row = $sql->db_Fetch();
					$message = USRLAN_114." ".$row['user_name'].",\n\n".USRLAN_122." ".SITENAME.".\n\n".USRLAN_123."\n\n";
					$message .= str_replace("{SITEURL}", SITEURL, USRLAN_139);

					require_once(e_HANDLER."mail.php");
					if(sendemail($row['user_email'], USRLAN_113." ".SITENAME, $message))
					{
						//  echo str_replace("\n","<br>",$message);
						$user->show_message("Email sent to: ".$row['user_name']);
					}
					else
					{
						$user->show_message("Failed to send to: ".$row['user_name']);
					}
				}
			}
		}
	}
}

if (isset($action) && $action == "uset")
{
	$user->show_message(USRLAN_87);
	$action = "main";
}

if (isset($action) && $action == "cu")
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


$unverified = $sql -> db_Count("user", "(*)", "WHERE user_ban = 2");

if (!e_QUERY) $action = "main";
switch ($action)
{
	case  "unverified" :
	$user->show_existing_users($action, $sub_action, $id, $from, $amount);
	break;

	case  "options" :
	$user->show_prefs();
	break;

	case "prune" :
	$user->show_prune();
	break;

	case "create" :
	$userMethods->deleteExpired();			// Remove time-expired users
	$user->add_user($user_data);
	break;

	default :
	$user->show_existing_users($action, $sub_action, $id, $from, $amount);
}


require_once("footer.php");





class users
{

	var $fields = array();
	var $fieldpref = array();


	function users()
	{

        global $pref, $user_pref, $sql, $tp;


        if(isset($pref['admin_user_disp']))
		{
        	$user_pref['admin_users_columns'] = ($pref['admin_user_disp']) ? explode("|",$pref['admin_user_disp']) : array('user_name', 'user_class');
            save_prefs('user');
			unset($pref['admin_user_disp']);
			save_prefs;
		}
        $this->usersSaveColumnPref();

		$this->fieldpref = (!$user_pref['admin_users_columns']) ? array('user_name', 'user_class') : $user_pref['admin_users_columns'];

		$this->fields = array(
			'user_id'			=> array('title'=> 'Id', 'width'=>'5%', 'forced'=> TRUE),
            'user_status'	   	=> array('title'=> ADLAN_134, 'forced'=> TRUE),
         	'user_name' 		=> array('title'=> LAN_USER_01, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left first'), // Display name
			'user_loginname' 	=> array('title'=> LAN_USER_02, 'type' => 'text', 'width' => 'auto'),	// User name
			'user_login' 		=> array('title'=> LAN_USER_03, 'type' => 'text', 'width' => 'auto'),	 // Real name (no real vetting)
			'user_customtitle' 	=> array('title'=> LAN_USER_04, 'type' => 'text', 'width' => 'auto'),	 // No real vetting
			'user_password' 	=> array('title'=> LAN_USER_05, 'type' => 'text', 'width' => 'auto'),
			'user_sess' 		=> array('title'=> LAN_USER_06, 'type' => 'text', 'width' => 'auto'),	 	// Photo
			'user_image' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar
			'user_email' 		=> array('title'=> LAN_USER_08, 'type' => 'text', 'width' => 'auto'),
			'user_signature' 	=> array('title'=> LAN_USER_09, 'type' => 'text', 'width' => 'auto'),
			'user_hideemail' 	=> array('title'=> LAN_USER_10, 'type' => 'int', 'width' => 'auto'),
			'user_xup' 			=> array('title'=> LAN_USER_11, 'type' => 'text', 'width' => 'auto'),
			'user_class' 		=> array('title'=> LAN_USER_12, 'type' => 'class'),
 			'user_join'			=> array('title' => LAN_USER_14, 'width'=> 'auto'),
			'user_lastvisit'	=> array('title' => LAN_USER_15, 'width'=> 'auto'),
			'user_currentvisit'	=> array('title' => LAN_USER_16, 'width'=> 'auto'),
			'user_comments'		=> array('title' => LAN_USER_17, 'width'=> 'auto'),
			'user_ip'			=> array('title' => LAN_USER_18, 'width'=> 'auto'),
			'user_ban'			=> array('title' => LAN_USER_19, 'width'=> 'auto'),
			'user_prefs'		=> array('title' => LAN_USER_20, 'width'=> 'auto'),
			'user_visits'		=> array('title' => LAN_USER_21, 'width'=> 'auto'),
			'user_admin'		=> array('title' => LAN_USER_22, 'width'=> 'auto'),
			'user_perms'		=> array('title' => LAN_USER_23, 'width'=> 'auto'),
			'user_pwchange'		=> array('title' => LAN_USER_24, 'width'=> 'auto')

		);


		$sql -> db_Select("user_extended_struct");
		while($row = $sql-> db_Fetch())
		{
			$field = "user_".$row['user_extended_struct_name'];
			$title = ucfirst(str_replace("user_","",$field));

			$this->fields[$field] = array('title'=>$title,'width'=>'auto');
		}

        $this->fields['options'] = array('title' => LAN_OPTIONS, 'width'=>'10%', "thclass" => "center last");

	}

    function usersSaveColumnPref()
	{
		global $pref,$user_pref, $admin_log;
		if(isset($_POST['submit-e-columns']))
		{
			$user_pref['admin_users_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
	}


	function show_existing_users($action, $sub_action, $id, $from, $amount)
	{
		global $sql, $frm, $ns, $tp, $mySQLdefaultdb,$pref,$unverified, $userMethods;
		$e107 = e107::getInstance();

		$text = "<div style='text-align:center'>";

		if (isset($_POST['searchquery']) && $_POST['searchquery'] != "")
		{
			$_POST['searchquery'] = $tp->toDB(trim($_POST['searchquery']));
			$query = 'WHERE '.
			$query .= (strpos($_POST['searchquery'], "@") !== FALSE) ? "user_email REGEXP('".$_POST['searchquery']."') OR ": "";
			$query .= (strpos($_POST['searchquery'], ".") !== FALSE) ? "user_ip REGEXP('".$_POST['searchquery']."') OR ": "";
			foreach($this->fieldpref as $disp)
			{
				$query .= $disp." REGEXP('".$_POST['searchquery']."') OR ";
			}
			$query .= "user_login REGEXP('".$_POST['searchquery']."') OR ";
			$query .= "user_name REGEXP('".$_POST['searchquery']."') ";
			if($action == 'unverified')
			{
				$query .= ' AND user_ban = 2 ';
			}
			$query .= ' ORDER BY user_id';
		}
		else
		{
			$query = '';
			if($action == 'unverified')
			{
				$query = 'WHERE user_ban = 2 ';
			}
			$query .= 'ORDER BY '.($sub_action ? $sub_action : 'user_id').' '.($id ? $id : 'DESC')."  LIMIT $from, $amount";
		}

		// $user_total = db_Count($table, $fields = '(*)',
		$qry_insert = 'SELECT u.*, ue.* FROM `#user` AS u	LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id ';

		$field_count = count($this->fieldpref);

		if ($user_total = $sql->db_Select_gen($qry_insert. $query))
		{
			$text .= "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-users-list'>
						<legend class='e-hideme'>".NWSLAN_4."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							<colgroup span='".$field_count."'>".$frm->colGroup($this->fields,$this->fieldpref)."</colgroup>
							<thead>
								<tr>".$frm->thead($this->fields,$this->fieldpref)."</tr>
							</thead>
							<tbody>";



         /*	<thead>
			<tr>
			<th style='width:5%'><a href='".e_SELF."?main.user_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></th>
			<th style='width:10%'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc").".$from'>".USRLAN_79."</a></th>";


			// Search Display Column header.
			$display_lan = $userMethods->getNiceNames(TRUE);		// List of field names and descriptive names
			foreach($this->fieldpref as $disp)
			{
				if (isset($display_lan[$disp]))
				{
					$text .= "<th style='width:15%'><a href='".e_SELF."?main.$disp.".($id == "desc" ? "asc" : "desc").".$from'>".$display_lan[$disp]."</a></th>";
				}
				else
				{
					$text .= "<th style='width:15%'><a href='".e_SELF."?main.$disp.".($id == "desc" ? "asc" : "desc").".$from'>".ucwords(str_replace("_"," ",$disp))."</a></th>";
				}
			}

			// ------------------------------

			$text .= "<th style='width:30%'>".LAN_OPTIONS."</th>
			</tr>
			</thead><tbody>";*/

			while ($row = $sql->db_Fetch())
			{
				extract($row);
				$text .= "<tr>
				<td style='width:5%; text-align:center' >{$user_id}</td>
				<td style='width:10%'>";

				if ($user_perms == "0") {
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'>".LAN_MAINADMIN."</div>";
				}
				else if($user_admin) {
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;;text-align:center'><a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'>".LAN_ADMIN."</a></div>";
				}
				else if($user_ban == 1) {
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'>".LAN_BANNED."</a></div>";
				}
				else if($user_ban == 2) {
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_NOTVERIFIED."</div>";
				}
				else if($user_ban == 3) {
					$text .= "<div class='fcaption' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_BOUNCED."</div>";
				}  else {
					$text .= "&nbsp;";
				}

				$text .= "</td>";



				// Display Chosen options

				$datefields = array("user_lastpost","user_lastvisit","user_join","user_currentvisit");
				$boleanfields = array("user_admin","user_hideemail","user_ban");

				foreach($this->fieldpref as $disp)
				{
					$text .= "<td style='white-space:nowrap'>";
					if($disp == 'user_class')
					{
						if ($user_class)
						{
							$tmp = explode(",", $user_class);
							while (list($key, $class_id) = each($tmp))
							{
								$text .= $e107->user_class->uc_get_classname($class_id)."<br />\n";
							}
						}
						else
						{
							$text .= "&nbsp;";
						}
					}
					elseif($disp == 'user_ip')
					{
						$text .= $e107->ipDecode($user_ip);
					}
					elseif (in_array($disp,$boleanfields))
					{
						$text .= ($row[$disp]) ? ADMIN_TRUE_ICON : '';
					}
					elseif(in_array($disp,$datefields))
					{
						$text .= ($row[$disp]) ? strftime($pref['shortdate'],$row[$disp]).'&nbsp;' : '&nbsp';
					}
					elseif($disp == 'user_name')
					{
						$text .= "<a href='".$e107->url->getUrl('core:user', 'main', 'func=profile&id='.$row['user_id'])."'>{$row['user_name']}</a>";
					}
					else
					{
						$text .= $row[$disp].'&nbsp;';
					}
					if(!in_array($disp,$boleanfields) && isset($prev[$disp]) && $row[$disp] == $prev[$disp] && $prev[$disp] != "")
					{ // show matches
						$text .= " <b>*</b>";
					}

					$text .= "</td>";
					$prev[$disp] = $row[$disp];
				}
				// -------------------------------------------------------------
				$qry = (e_QUERY) ?  "?".e_QUERY : "";
				$text .= "
				<td style='width:30%' class='center'>

				<div>

				<input type='hidden' name='userid[{$user_id}]' value='{$user_id}' />
				<input type='hidden' name='userip[{$user_id}]' value='{$user_ip}' />
				<select name='useraction[{$user_id}]' onchange='this.form.submit()' class='tbox' style='width:75%'>
				<option selected='selected' value=''>&nbsp;</option>";

				if ($user_perms != "0")
				{
					$text .= "<option value='userinfo'>".USRLAN_80."</option>
					<option value='usersettings'>".LAN_EDIT."</option>";
					switch ($user_ban)
					{
						case 0 :
						$text .= "<option value='ban'>".USRLAN_30."</option>\n";
						break;
						case 1 :		// Banned user
						$text .= "<option value='unban'>".USRLAN_33."</option>\n";
						break;
						case 2 :		// Unverified
						$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='verify'>".USRLAN_32."</option>
						<option value='resend'>".USRLAN_112."</option>
						<option value='test'>".USRLAN_118."</option>";
						break;
						case 3 :		// Bounced
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
					else if ($user_admin && $user_perms != "0" && getperms('3'))
					{
						$text .= "<option value='unadmin'>".USRLAN_34."</option>\n";
					}
				}
				if ($user_perms == "0" && !getperms("0"))
				{
					$text .= "";
				}
				elseif($user_id != USERID || getperms("0") )
				{
					$text .= "<option value='userclass'>".USRLAN_36."</option>\n";
				}

				if ($user_perms != "0")
				{
					$text .= "<option value='deluser'>".LAN_DELETE."</option>\n";
				}
				$text .= "</select></div>";
				$text .= "</td></tr>";
			}
			$text .= "</tbody></table></fieldset> ";



		}

		if($action == "unverified")
		{
			$text .= "
			<div style='text-align:center'>
			<br />
			<form method='post' action='".e_SELF.$qry."'>";
			if($pref['mail_bounce_pop3']!=''){
				$text .= "<input type='submit' class='button' name='check_bounces' value=\"".USRLAN_143."\" />\n";
			}
			$text .= "&nbsp;<input type='submit' class='button' name='resend_to_all' value=\"".USRLAN_144."\" />
			</form>
			</div>";
		}


		$users = (e_QUERY != "unverified") ? $sql->db_Count("user"): $unverified;

		if ($users > $amount && !$_POST['searchquery'])
		{
			$parms = "{$users},{$amount},{$from},".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.user_id.desc.")."[FROM]";
			$text .= "<br />".$tp->parseTemplate("{NEXTPREV={$parms}}");
		}

		// Search etc. .

		$text .= "</form>



		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<div>\n";
		$text .= "<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n
		<input class='button' type='submit' name='searchsubmit' value='".USRLAN_90."' />\n
		<br /><br /></p>\n";
     /*
		$text .= "<div style='cursor:pointer' onclick=\"expandit('sdisp')\">".LAN_DISPLAYOPT."</div>";
		$text .= "<div  id='sdisp' style='padding-top:4px;display:none;text-align:center;margin-left:auto;margin-right:auto'>
		<table class='forumheader3' style='width:95%'>";

		$fname = array_keys($display_lan);
		// include extended fields in the list.
		$sql -> db_Select("user_extended_struct");
		while($row = $sql-> db_Fetch())
		{
			$fname[] = "user_".$row['user_extended_struct_name'];
		}
		$m = 0;
		foreach($fname as $fcol)
		{
			if($m == 0)
			{
				$text .= "<tr>";
			}
			$checked = (in_array($fcol,$this->fieldpref)) ? "checked='checked'" : "";
			$text .= "<td style='text-align:left; padding:0px'>";
			$text .= "<input type='checkbox' name='searchdisp[]' value='".$fcol."' $checked />".str_replace("user_","",$fcol) . "</td>\n";
			$m++;
			if($m == 5)
			{
				$text .= "</tr>";
				$m = 0;
			}
		}
     */


		$text .= "</div>

		</form>\n
		</div>";



		// ======================
		$total_cap = (isset($_POST['searchquery'])) ? $user_total : $users;
		$caption = USRLAN_77 ."&nbsp;&nbsp;   (total: $total_cap)";
		$ns->tablerender($caption, $text);

	}



	function show_options($action)
	{
		global $unverified;
		// ##### Display options
		if ($action == '')
		{
			$action = 'main';
		}
		// ##### Display options
		$var['main']['text'] = USRLAN_71;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = USRLAN_72;
		$var['create']['link'] = e_SELF.'?create';

		$var['prune']['text'] = USRLAN_73;
		$var['prune']['link'] = e_SELF.'?prune';

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF.'?options';

		if($unverified)
		{
			$var['unveri']['text'] = USRLAN_138." ($unverified)";
			$var['unveri']['link'] = e_SELF.'?unverified';
		}

		$var['rank']['text'] = USRLAN_196;
		$var['rank']['link'] = e_SELF.'?ranks';

		//  $var['mailing']['text']= USRLAN_121;
		//   $var['mailing']['link']="mailout.php";
		show_admin_menu(USRLAN_76, $action, $var);
	}




	function show_prefs()
	{
		global $ns, $pref, $e_userclass;
		if (!is_object($e_userclass)) $e_userclass = new user_class;
		$pref['memberlist_access'] = varset($pref['memberlist_access'], e_UC_MEMBER);
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table class='adminlist'>
		<colgroup>
		<col style='width:60%' />
		<col style='width:40%' />
		</colgroup>

		<tr>
		<td>".USRLAN_44.":</td>
		<td>". ($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' />".LAN_NO : "<input name='avatar_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked='checked' />".LAN_NO). (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
		</td>
		</tr>

		<tr>
		<td>".USRLAN_53.":</td>
		<td>". ($pref['photo_upload'] ? "<input name='photo_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' />".LAN_NO : "<input name='photo_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' checked='checked' />".LAN_NO). (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
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
		<td style='vertical-align:top'>". ($pref['profile_rate'] ? "<input name='profile_rate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' />".LAN_NO : "<input name='profile_rate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' checked='checked' />".LAN_NO)."
		</td>
		</tr>

		<tr>
		<td>".USRLAN_127.":</td>
		<td style='vertical-align:top'>". ($pref['profile_comments'] ? "<input name='profile_comments' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' />".LAN_NO : "<input name='profile_comments' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' checked='checked' />".LAN_NO)."
		</td>
		</tr>

		<tr>
		<td style='vertical-align:top'>".USRLAN_133.":<br /><span class='smalltext'>".USRLAN_134."</span></td>
		<td style='vertical-align:top'>". ($pref['force_userupdate'] ? "<input name='force_userupdate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' />".LAN_NO : "<input name='force_userupdate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' checked='checked' />".LAN_NO)."
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
		$text .= $e_userclass->vetted_tree('memberlist_access',array($e_userclass,'select'), $pref['memberlist_access'], "public,member,guest,admin,main,classes,nobody");
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
		$ns->tablerender(USRLAN_52, $text);
	}



	function show_message($message)
	{
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}




	function show_prune()
	{
		global $ns, $sql;

		$unactive = $sql->db_Count("user", "(*)", "WHERE user_ban=2");
		$bounced = $sql->db_Count("user", "(*)", "WHERE user_ban=3");
	   	$older30 = $sql->db_Count("user", "(*)", "WHERE user_ban=2 AND (user_join < ".strtotime("-30 days").")");

		$text = "<div style='text-align:center'><br /><br />
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='forumheader3' style='text-align:center'><br />".LAN_DELETE.":&nbsp;
		<select class='tbox' name='prune_type'>";

		 $prune_type = array(2=>USRLAN_138." [".$unactive."]",'30'=>USRLAN_138." (".USRLAN_219.") [".$older30."]", 3=>USRLAN_145." [".$bounced."]");

		foreach($prune_type as $key=>$val){
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
		$ns->tablerender(USRLAN_55, $text);
	}



	// Add a new user - may be passed existing data if there was an entry error on first pass
	function add_user($user_data)
	{
		global $frm, $ns, $pref, $e_userclass;
		if (!is_object($e_userclass)) $e_userclass = new user_class;
		$text = "<div style='text-align:center'>". $frm->form_open("post", e_SELF.(e_QUERY ? '?'.e_QUERY : ''), "adduserform")."
		<table class='adminlist'>
		<tr>
		<td style='width:30%'>".USRLAN_61."</td>
		<td style='width:70%'>
		".$frm->form_text('username', 40, varset($user_data['user_name'],""), varset($pref['displayname_maxlength'],15))."
		</td>
		</tr>

		<tr>
		<td style='width:30%'>".USRLAN_128."</td>
		<td style='width:70%'>
		".$frm->form_text('loginname', 40, varset($user_data['user_loginname'],""), varset($pref['loginname_maxlength'],30))."&nbsp;&nbsp;
		".$frm->form_checkbox('generateloginname',1,varset($pref['predefinedLoginName'],FALSE)).USRLAN_170."
		</td>
		</tr>

		<tr>
		<td style='width:30%'>".USRLAN_129."</td>
		<td style='width:70%'>
		".$frm->form_text("realname", 40, varset($user_data['user_login'],""), 30)."
		</td>
		</tr>

		<tr>
		<td style='width:30%'>".USRLAN_62."</td>
		<td style='width:70%'>
		".$frm->form_password("password1", 40, "", 20)."&nbsp;&nbsp;
		".$frm->form_checkbox('generatepassword',1,FALSE).USRLAN_171."
		</td>
		</tr>
		<tr>
		<td style='width:30%'>".USRLAN_63."</td>
		<td style='width:70%'>
		".$frm->form_password("password2", 40, "", 20)."
		</td>
		</tr>
		<tr>
		<td style='width:30%'>".USRLAN_64."</td>
		<td style='width:70%'>
		".$frm->form_text("email", 60, varset($user_data['user_email'],""), 100)."
		</td>
		</tr>\n";


		if (!isset($user_data['user_class'])) $user_data['user_class'] = varset($pref['initial_user_classes'],'');
		$temp = $e_userclass->vetted_tree('class',array($e_userclass,'checkbox_desc'), $user_data['user_class'], 'classes');


		if ($temp)
		{
			$text .= "<tr style='vertical-align:top'>
			<td>
			".USRLAN_120."
			</td><td>{$temp}</td>
			</tr>\n";
		}
		$text .= "
		<tr style='vertical-align:top'>
			<td colspan='2' class='center'>
			<input class='button' type='checkbox' name='sendconfemail' value='1' />".USRLAN_181."
			</td>
		</tr>
		<tr style='vertical-align:top'>
			<td colspan='2' class='center button-bar'>
			<input class='button' type='submit' name='adduser' value='".USRLAN_60."' />
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
			</td>
		</tr>
		</table>
		</form>
		</div>
		";

		$ns->tablerender(USRLAN_59, $text);
	}


	function resend($id, $key, $name, $email, $lfile='')
	{
		global $sql,$mailheader_e107id, $admin_log;
		$id = (int)$id;

		// Check for a Language field, and if present, send the email in the user's language.
		if($lfile == "")
		{
			if($sql -> db_Select('user_extended', 'user_language', 'user_extended_id = '.$id))
			{
				$row = $sql -> db_Fetch();
				$lfile = e_LANGUAGEDIR.$row['user_language'].'/lan_signup.php';
			}
		}
		if(is_readable($lfile))
		{
			require_once($lfile);
		}
		else
		{
			$row['user_language'] = e_LANGUAGE;
			require_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_signup.php");
		}


		$return_address = (substr(SITEURL, -1) == "/") ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key;

		$message = LAN_EMAIL_01." ".$name."\n\n".LAN_SIGNUP_24." ".SITENAME.".\n".LAN_SIGNUP_21."\n\n";
		$message .= $return_address . "\n\n".SITENAME."\n".SITEURL;

		$mailheader_e107id = $id;

		require_once(e_HANDLER."mail.php");
		if(sendemail($email, LAN_404." ".SITENAME, $message))
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


	function resend_to_all()
	{
		global $sql,$pref,$sql3, $admin_log;
		$count = 0;
		$pause_count = 1;
		$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
		$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;

		if($sql -> db_Select_gen('SELECT user_language FROM `#user_extended` LIMIT 1'))
		{
			$query = "SELECT u.*, ue.* FROM `#user` AS u LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id WHERE u.user_ban = 2 ORDER BY u.user_id DESC";
		}
		else
		{
			$query = 'SELECT * FROM `#user` WHERE user_ban=2';
		}

		if(!is_object($sql3))
		{
			$sql3 = new db;
		}

		$sql3 -> db_Select_gen($query);
		while($row = $sql3-> db_Fetch())
		{
			echo $row['user_id']." ".$row['user_sess']." ".$row['user_name']." ".$row['user_email']."<br />";
			$this->resend($row['user_id'],$row['user_sess'],$row['user_name'],$row['user_email'],$row['user_language']);
			if($pause_count > $pause_amount)
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

	function check_bounces($bounce_act='first_check', $bounce_arr = '')
	{
		global $sql,$pref;
		include(e_HANDLER.'pop3_class.php');

		if (!trim($bounce_act)) { $bounce_act='first_check'; }

		//	  echo "Check bounces. Action: {$bounce_act}; Entries: {$bounce_arr}<br />";

		$obj= new receiveMail($pref['mail_bounce_user'],$pref['mail_bounce_pass'],$pref['mail_bounce_email'],$pref['mail_bounce_pop3'],varset($pref['mail_bounce_type'],'pop3'));
		$del_count = 0;
		if ($bounce_act !='first_check')
		{ // Must do some deleting
			$obj->connect();
			$tot=$obj->getTotalMails();
			$del_array = explode(',',$bounce_arr);
			for($i=1;$i<=$tot;$i++)
			{	// Scan all emails; delete current one if meets the criteria
				$dodel = FALSE;
				switch ($bounce_act)
				{
					case 'delnonbounce' :
					$head=$obj->getHeaders($i);
					$dodel = (!$head['bounce']);
					break;
					case 'clearemailbounce' :
					if (!in_array($i, $del_array)) break;
					$head=$obj->getHeaders($i);
					if($head['bounce'])
					{
						if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $obj->getBody($i), $result)) { $usr_email = trim($result[0]); }
						if ($sql->db_Select('user','user_id, user_name, user_email',"user_email='".$usr_email."' "))
						{
							$row = $sql->db_Fetch();
							if ($sql->db_Update('user',"`user_email`='' WHERE `user_id` = '".$row['user_id']."' ") !== FALSE)
							{
								// echo "Deleting user email {$row['user_email']} for user {$row['user_name']}, id={$row['user_id']}<br />";
								$dodel = TRUE;
							}
						}
					}
					break;
					case 'delall' :
					$dodel = TRUE;
					break;
					case 'delchecked' :
					$dodel = in_array($i, $del_array);
					break;
				}
				if ($dodel)
				{
					//			  echo "Delete email ID {$i}<br />";
					$obj->deleteMails($i);
					$del_count++;			// Keep track of number of emails deleted
				}
			}	// End - Delete one email
			$obj->close_mailbox();	// This actually deletes the emails

		}		// End of email deletion


		// Now list the emails that are left
		$obj->connect();
		$tot=$obj->getTotalMails();
		$found = FALSE;
		$DEL = ($pref['mail_bounce_delete']) ? TRUE : FALSE;

		$text = "<br /><div><form  method='post' action='".e_SELF.$qry."'><table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td class='fcaption' style='width:5%'>#</td><td class='fcaption'>e107-id</td><td class='fcaption'>email</td><td class='fcaption'>Subject</td><td class='fcaption'>Bounce</td></tr>\n";


		for($i=1;$i<=$tot;$i++)
		{
			$head=$obj->getHeaders($i);
			if($head['bounce'])
			{	// Its a 'bounce' email
				if (ereg('.*X-e107-id:(.*)MIME', $obj->getBody($i), $result))
				{
					if($result[1])
					{
						$id[$i] = intval($result[1]);		// This should be a user ID - but not on special mailers!
						//	Try and pull out an email address from body - should be the one that failed
						if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $obj->getBody($i), $result))
						{
							$emails[$i] = "'".$result[0]."'";
						}
						$found = TRUE;
					}
				}
				elseif (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $obj->getBody($i), $result))
				{
					if($result[0] && $result[0] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[0]."'";
						$found = TRUE;
					}
					elseif($result[1] && $result[1] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[1]."'";
						$found = TRUE;
					}
				}
				if ($DEL && $found)
				{ 	// Auto-delete bounced emails once noticed (if option set)
					$obj->deleteMails($i);
					$del_count++;
				}
			}
			else
			{  // Its a warning message or similar
				//			  $id[$i] = '';			// Don't worry about an ID for now
				//				Try and pull out an email address from body - should be the one that failed
				if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $obj->getBody($i), $result))
				{
					$wmails[$i] = "'".$result[0]."'";
				}
			}

			$text .= "<tr><td class='forumheader3'>".$i."</td><td class='forumheader3'>".$id[$i]."</td><td class='forumheader3'>".(isset($emails[$i]) ? $emails[$i] : $wmails[$i])."</td><td class='forumheader3'>".$head['subject']."</td><td class='forumheader3'>".($head['bounce'] ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON);
			$text .= "<input type='checkbox' name='delete_email[]' value='{$i}' /></td></tr>\n";
		}


		if ($del_count)
		{
			$admin_log->log_event('USET_13',str_replace('--COUNT--',$del_count,USRLAN_169),E_LOG_INFORMATIVE);
		}


		if ($tot)
		{ // Option to delete emails - only if there are some in the list
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

		$obj->close_mailbox();					// This will actually delete emails

		// $tot has total number of emails in the mailbox
		$found = count($emails);				// $found - Number of bounce emails found
		// $del_count has number of emails deleted


		// Update bounce status for users
		$ed = $sql -> db_Update('user', "user_ban=3 WHERE (`user_id` IN (".$all_ids.") OR `user_email` IN (".$all_emails.")) AND user_sess !='' ");
		if (!$ed) $ed = '0';
		$this->show_message(str_replace(array('{TOTAL}','{DELCOUNT}','{DELUSER}','{FOUND}'),
		array($tot,$del_count,$ed,$found),USRLAN_155).$text);
	}
}		// End class users



function users_adminmenu()
{
	global $user;
	global $action;
	$user->show_options($action);
}

function deleteRank($rankId)
{
	global $emessage;
	$e107 = e107::getInstance();
	
	$rankId = (int)$rankId;
	$e107->ecache->clear_sys('nomd5_user_ranks');
	if($e107->sql->db_Delete('generic', "gen_id='{$rankId}'"))
	{
		$emessage->add(USRLAN_218, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(USRLAN_218, E_MESSAGE_FAIL);
	}
}

function updateRanks()
{
	global $pref, $emessage;
	$e107 = e107::getInstance();
	$config = array();
	$ranks_calc = '';
	$ranks_flist = '';
	
	foreach($_POST['op'] as $f => $o)
	{
		$config[$f]['op'] = $o;
		$config[$f]['val'] = varset($_POST['val'][$f], '');
		
		if($_POST['val'][$f])
		{
			$ranks_calc .= ($ranks_calc ? ' + ' : '').'({'.$f.'} '." $o {$_POST['val'][$f]}".' )';
			$ranks_flist .= ($ranks_flist ? ',' : '').$f;
		}
	}

	$e107->sql->db_Delete('generic', "gen_type = 'user_rank_config'");
	$tmp = array();
	$tmp['data']['gen_type'] = 'user_rank_config';
	$tmp['data']['gen_chardata'] = serialize($config);
	$tmp['_FIELD_TYPES']['gen_type'] = 'string';
	$tmp['_FIELD_TYPES']['gen_chardata'] = 'escape';
	$e107->sql->db_Insert('generic', $tmp);

	$pref['ranks_calc'] = $ranks_calc;
	$pref['ranks_flist'] = $ranks_flist;
	save_prefs();
	
	//Delete existing rank data
	$e107->sql->db_Delete('generic', "gen_type = 'user_rank_data'");

	//Add main site admin info
	$tmp = array();
	$tmp['_FIELD_TYPES']['gen_datestamp'] = 'int';
	$tmp['_FIELD_TYPES']['gen_ip'] = 'todb';
	$tmp['_FIELD_TYPES']['gen_user_id'] = 'int';
	$tmp['_FIELD_TYPES']['gen_chardata'] = 'todb';
	$tmp['_FIELD_TYPES']['gen_intdata'] = 'int';

	$tmp['data']['gen_datestamp'] = 1;
	$tmp['data']['gen_type'] = 'user_rank_data';
	$tmp['data']['gen_ip'] = 	$_POST['calc_name']['main_admin'];
	$tmp['data']['gen_user_id'] =	varset($_POST['calc_pfx']['main_admin'], 0);
	$tmp['data']['gen_chardata'] = 	$_POST['calc_img']['main_admin'];
	$e107->sql->db_Insert('generic', $tmp);

	//Add site admin info
	unset($tmp['data']);
	$tmp['data']['gen_type'] = 'user_rank_data';
	$tmp['data']['gen_datestamp'] = 2;
	$tmp['data']['gen_ip'] = 	$_POST['calc_name']['admin'];
	$tmp['data']['gen_user_id'] =	varset($_POST['calc_pfx']['admin'], 0);
	$tmp['data']['gen_chardata'] = 	$_POST['calc_img']['admin'];
	$e107->sql->db_Insert('generic', $tmp);


	//Add all current site defined ranks
	if(isset($_POST['field_id']))
	{
		foreach($_POST['field_id'] as $fid => $x)
		{
			unset($tmp['data']);
			$tmp['data']['gen_type'] = 'user_rank_data';
			$tmp['data']['gen_ip'] = varset($_POST['calc_name'][$fid], '');
			$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx'][$fid], 0);
			$tmp['data']['gen_chardata'] = varset($_POST['calc_img'][$fid], '');
			$tmp['data']['gen_intdata'] = varset($_POST['calc_lower'][$fid], '_NULL_');
			$e107->sql->db_Insert('generic', $tmp);
		}
	}

	//Add new rank, if posted
	if(varset($_POST['new_calc_lower']))
	{
		unset($tmp['data']);
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_datestamp'] = 0;
		$tmp['data']['gen_ip'] = 	varset($_POST['new_calc_name']);
		$tmp['data']['gen_user_id'] =	varset($_POST['new_calc_pfx'], 0);
		$tmp['data']['gen_chardata'] = 	varset($_POST['new_calc_img']);
		$tmp['data']['gen_intdata'] =	varset($_POST['new_calc_lower']);
		$e107->sql->db_Insert('generic', $tmp);
	}
	$e107->ecache->clear_sys('nomd5_user_ranks');
	$emessage->add(USRLAN_217, E_MESSAGE_SUCCESS);
}

function show_ranks()
{
	global $pref, $emessage;
	$e107 = e107::getInstance();
	include_once(e_HANDLER.'file_class.php');
	include_once(e_HANDLER.'level_handler.php');
	require_once(e_HANDLER.'message_handler.php');
	require_once(e_HANDLER."form_handler.php");
	$frm = new e_form(true); //enable inner tabindex counter

	$f = new e_file;
	$ranks = new e107UserRank;

	$imageList = $f->get_files(e_IMAGE.'ranks', '.*?\.(png|gif|jpg)');
	$config = array();

	if($e107->sql->db_Select('generic', 'gen_chardata', "gen_type='user_rank_config'", 'default'))
	{
		$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
		$config = unserialize($row['gen_chardata']);
	}
	
	$fieldList = array('core' => array(), 'extended' => array());

	$fieldList['core'] = array(
	'comments' => USRLAN_201,
	'visits' => USRLAN_202,
	'daysregged' => USRLAN_203 
	);

	foreach($e107->extended_struct as $field)
	{
		if(strpos($field['Type'], 'int') !== false && $field['Field'] != 'user_extended_id')
		{
			$fieldList['extended'][] = substr($field['Field'], 5);
		}
	}
	
	$opArray = array('*', '+', '-');

	$text .= "
	<form method='post'>
	<table style='".ADMIN_WIDTH."'>
	<tr>
	<td class='label'>".USRLAN_197."</td>
	<td class='label'>".USRLAN_198."</td>
	<td class='control'>".USRLAN_199."</td>
	<td class='control'>".USRLAN_200."</td>
	</tr>
	";
	foreach($fieldList['core'] as $k => $f)
	{
		$text .= "
		<tr>
		<td class='label'>".USRLAN_204."</td>
		<td class='label'>{$f}</td>
		<td class='control'>
			<select name='op[{$k}]' class='tbox'>
		";
		foreach($opArray as $op)
		{
			$sel = (varset($config[$k]['op']) == $op ? "selected='selected'" : '');
			$text .= "<option value='{$op}' {$sel}>{$op}</option>";
		}
		$text .= "
			</select>
		</td>
		<td class='control'><input type='text' class='tbox' name='val[{$k}]' value='".varset($config[$k]['val'])."' size='3' maxlength='3'></td>
		</tr>
		";
	}
	if(count($fieldList['extended']))
	{
		foreach($fieldList['extended'] as $f)
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
			foreach($opArray as $op)
			{
				$sel = (varset($config[$f]['op']) == $op ? "selected='selected'" : '');
				$text .= "<option value='{$op}' {$sel}>{$op}</option>";
			}
			$text .= "
				</select>
			</td>
			<td class='control'><input type='text' class='tbox' name='val[{$f}]' value='".varset($config[$f]['val'])."' size='3' maxlength='3' value=''></td>
			</tr>
			";
		}
	}
	if(isset($pref['ranks_calc']))
	{
		$text .= "<tr>
								<td class='label' colspan='4'><br />".USRLAN_206.": {$pref['ranks_calc']}</td>
							</tr>
							";
	}
	$text .= '</table>';
	$e107->ns->tablerender('', $emessage->render());
	$e107->ns->tablerender('Rank Calculation fields', $text);

	$text = "
	<table style='".ADMIN_WIDTH."'>
	<tr>
		<td class='label'>".USRLAN_207."</td>
		<td class='label'>".USRLAN_208."</td>
		<td class='label'>".USRLAN_209."</td>
		<td class='label'>".USRLAN_210."</td>
		<td class='label'>".USRLAN_211."</td>
	</tr>
	";
	$info = $ranks->ranks['special'][1];
	$val = $e107->tp->toForm($info['name']);
	$pfx = ($info['lan_pfx'] ? "checked='checked'" : '');
	
	$text .= "
	<tr>
		<td class='control'>".LAN_MAINADMIN."</td>
		<td class='control'>
			<input class='tbox' type='text' name='calc_name[main_admin]' value='{$val}'>
		</td>
		<td class='control'>N/A</td>
		<td class='control'><input type='checkbox' name='calc_pfx[main_admin]' {$pfx} value='1'></td>
		<td class='control'>".RankImageDropdown($imageList, 'calc_img[main_admin]', $info['image'])."</td>
	</tr>
	";

	$info = $ranks->ranks['special'][2];
	$val = $e107->tp->toForm($info['name']);
	$pfx = ($info['lan_pfx'] ? "checked='checked'" : '');
	$text .= "	
	<tr>
		<td class='control'>".LAN_ADMIN."</td>
		<td class='control'>
			<input class='tbox' type='text' name='calc_name[admin]' value='{$val}'>
		</td>
		<td class='control'>N/A</td>
		<td class='control'><input type='checkbox' name='calc_pfx[admin]' {$pfx} value='1'></td>
		<td class='control'>".RankImageDropdown($imageList, 'calc_img[admin]', $info['image'])."</td>
	</tr>
	<tr>
		<td colspan='5'>&nbsp;</td>
	</tr>
	";
	
	foreach($ranks->ranks['data'] as $k => $r)
	{
		$pfx_checked = ($r['lan_pfx'] ? "checked='checked'" : '');
		$text .= "
		<tr>
			<td class='control'>".USRLAN_212."</td>
			<td class='control'>
				<input type='hidden' name='field_id[{$k}]' value='1' />
				<input class='tbox' type='text' name='calc_name[$k]' value='{$r['name']}'>
			</td>
			<td class='control'><input class='tbox' type='text' size='5' name='calc_lower[$k]' value='{$r['thresh']}'></td>
			<td class='control'><input type='checkbox' name='calc_pfx[$k]' value='1' {$pfx_checked}></td>
			<td class='control'>".
				RankImageDropdown($imageList, "calc_img[$k]", $r['image'])."&nbsp;".
				$frm->submit_image("delete_rank[{$r['id']}]", LAN_DELETE, 'delete', USRLAN_213.": [{$r['name']}]?")."
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
		<td class='control'><input class='tbox' type='text' name='new_calc_name' value=''></td>
		<td class='control'><input class='tbox' type='text' size='5' name='new_calc_lower' value=''></td>
		<td class='control'><input type='checkbox' name='new_calc_pfx' value='1'></td>
		<td class='control'>".RankImageDropdown($imageList, 'new_calc_img')."</td>
	</tr>
	<tr>
		<td colspan='5' style='text-align:center'>
			<br />
			<input type='submit' name='updateRanks' value='".USRLAN_215."' />
		</td>
	</tr>
	";	

	$text .= '</table></form>';
	$e107->ns->tablerender('Ranks', $text);
	include(e_ADMIN.'footer.php');
	exit;
}

function RankImageDropdown(&$imgList, $field, $curVal='')
{
	$ret = "
	<select class='tbox' name='{$field}'>
	<option value=''>".USRLAN_216."</option>
	";
	foreach($imgList as $img)
	{
		$sel = ($img['fname'] == $curVal ? "selected='selected'" : '');
		$ret .= "\n<option {$sel}>{$img['fname']}</option>";
	}
	$ret .= '</select>';
	return $ret;
}

?>
