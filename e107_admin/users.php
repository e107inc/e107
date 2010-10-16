<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

// Experimental e-token
if(isset($_POST['useraction']) || isset($_POST['adduser']) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	unset($_POST['e-token']);
}

require_once("../class2.php");

if (!getperms("4"))
{
  header("location:".e_BASE."index.php");
  exit;
}


if (isset($_POST['useraction']) && $_POST['useraction'] == 'userinfo')
{
	header('location:'.e_ADMIN."userinfo.php?".$tp -> toDB($_POST['userip']));
	exit;
}


if (isset($_POST['useraction']) && $_POST['useraction'] == 'usersettings')
{
	header('location:'.e_BASE."usersettings.php?".$tp -> toDB($_POST['userid']));
	exit;
}


if (isset($_POST['useraction']) && $_POST['useraction'] == 'userclass')
{
	header('location:'.e_ADMIN."userclass.php?".$tp -> toDB($_POST['userid'].".".e_QUERY));
	exit;
}

$e_sub_cat = 'users';
$user = new users;
$action = '';
$id = 0;
$sub_action = '';

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0]; // must be set before auth.php is loaded. 
	$sub_action = varset($tmp[1],'');
	$id = varset($tmp[2],0);
	$from = varset($tmp[3],0);
	unset($tmp);
}

require_once("auth.php");

require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");

$rs = new form;



$from = varset($from, 0);
$amount = 30;

/*

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

*/



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
	require_once(e_HANDLER."mail_validation_class.php");
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
	$pref['avatar_upload'] = (FILE_UPLOADS ? $_POST['avatar_upload'] : 0);
	$pref['im_width'] = $_POST['im_width'];
	$pref['im_height'] = $_POST['im_height'];
	$pref['photo_upload'] = (FILE_UPLOADS ? $_POST['photo_upload'] : 0);
	$pref['del_unv'] = $_POST['del_unv'];
	$pref['profile_rate'] = $_POST['profile_rate'];
	$pref['profile_comments'] = $_POST['profile_comments'];
	$pref['track_online'] = $_POST['track_online'];
	$pref['force_userupdate'] = $_POST['force_userupdate'];
	$pref['memberlist_access'] = $_POST['memberlist_access'];
	save_prefs();
	$user->show_message(USRLAN_1);
}


// ------- Prune Users. --------------
if (isset($_POST['prune']))
{
	$e107cache->clear("online_menu_totals");
	$text = USRLAN_56." ";
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
		 	$e_event->trigger('userdel', intval($u['user_id']));
		}
	}
	$ns->tablerender(USRLAN_57, "<div style='text-align:center'><b>".$text."</b></div>");
	unset($text);
}


// ------- Quick Add User --------------
if (isset($_POST['adduser']))
{
	$e107cache->clear("online_menu_totals");
	if (!$_POST['ac'] == md5(ADMINPWCHANGE))
	{
		exit;
	}

	require_once(e_HANDLER."message_handler.php");
	if (strstr($_POST['name'], "#") || strstr($_POST['name'], "="))
	{
		message_handler("P_ALERT", USRLAN_92);
		$error = TRUE;
	}

	$_POST['name'] = trim(str_replace("&nbsp;", "", $_POST['name']));
	if ($_POST['name'] == "Anonymous")
	{
		message_handler("P_ALERT", USRLAN_65);
		$error = TRUE;
	}
	elseif ($sql->db_Select("user", "*", "user_name='".$_POST['name']."' "))
	{
		message_handler("P_ALERT", USRLAN_66);
		$error = TRUE;
	}

	$loginname = strip_tags($_POST['loginname']);
	$temp_name = str_replace('--', '', trim(preg_replace("/[\^\*\|\/;:#=\$'\"!#`\s\(\)%\?<>\\{}]/", '', strip_tags($_POST['loginname']))));
	if ($loginname != $temp_name)
	{
		message_handler('P_ALERT', USRLAN_157);
		$error = TRUE;
	}
	elseif ($sql->db_Select("user", "user_loginname", "user_loginname='".$_POST['loginname']."' "))
	{
		message_handler("P_ALERT", USRLAN_75 );
		$error = TRUE;
	}

	if ($_POST['password1'] != $_POST['password2'])
	{
		message_handler("P_ALERT", USRLAN_67);
		$error = TRUE;
	}

	if ($loginname == '' || $_POST['name'] == "" || $_POST['password1'] == "" || $_POST['password2'] == "")
	{
		message_handler("P_ALERT", USRLAN_68);
		$error = TRUE;
	}

	if (!varset($pref['disable_emailcheck'],FALSE))
	{
		// Email address checks
		if (!check_email($_POST['email']))
		{
			message_handler("P_ALERT", USRLAN_69);
			$error = TRUE;
		}
		elseif ($sql->db_Count("user", "(*)", "WHERE user_email='".$_POST['email']."' AND user_ban='1' "))
		{	// Specifically identify banned users
			message_handler("P_ALERT", USRLAN_147);
			$error = TRUE;
		}
		elseif ($sql->db_Count("banlist", "(*)", "WHERE banlist_ip='".$_POST['email']."'"))
		{	// ... and filter against the ban list
			message_handler("P_ALERT", USRLAN_148);
			$error = TRUE;
		}
		elseif ($sql->db_Count("user", "(*)", "WHERE user_email='".$_POST['email']."' "))
		{	// Finally, email addresses are required to be unique anyway
			message_handler("P_ALERT", USRLAN_156);
			$error = TRUE;
		}
	}

	if (!$error)
	{
		$username = trim(strip_tags($_POST['name']));
		$username = $tp->toDB(substr($username, 0, $pref['displayname_maxlength']));

		$svar = implode(",", $_POST['userclass']);
		$now = time();
		$newUserData = array(
			'user_name' => $username,
			'user_loginname' => $loginname,
			'user_password' => md5($_POST['password1']),
			//'user_sess' => $key,
			'user_email' => $_POST['email'],
			'user_signature' => '',
			'user_hideemail' => 1,
			'user_join' => $now,
			'user_lastvisit' => $now,
			'user_currentvisit' => $now,
			'user_new' => '',
			'user_viewed' => '',
			//'user_login' => $_POST['realname'],
			'user_class' => $svar,
			'user_perms' => '',
			'user_realm' => '',
			'user_pwchange' => $now
		);
		//admin_update($sql -> db_Insert("user", "0, '{$username}', '{$loginname}',  '', '".md5($_POST['password1'])."', '{$key}', '".$_POST['email']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '1', '".time()."', '".time()."', '".time()."', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '".$_POST['realname']."', '".$svar."', '', '', '".time()."', ''"), 'insert', USRLAN_70);
		admin_update($sql -> db_Insert("user", $newUserData), 'insert', USRLAN_70);
	}
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
				$sql -> db_Insert("banlist", "'".$row['user_ip']."', '".USERID."', '".$row['user_name']."' ");
				$user->show_message(str_replace("{IP}", $row['user_ip'], USRLAN_137));
			}
		}
	}
	$action = "main";
	if(!$sub_action){$sub_action = "user_id"; }
}


// ------- Unban User --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "unban") {
	$sql->db_Select("user", "*", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	extract($row);
	$sql->db_Update("user", "user_ban='0' WHERE user_id='".$_POST['userid']."' ");
	$sql -> db_Delete("banlist", " banlist_ip='$user_ip' ");
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
		if ($sql->db_Delete("user", "user_id='".$_POST['userid']."' AND user_perms != '0' AND user_perms != '0.'")) 
		{
		   $sql->db_Delete("user_extended", "user_extended_id='".$_POST['userid']."' ");
		   $e_event->trigger('userdel', intval($_POST['userid']));
			$user->show_message(USRLAN_10);
		}
		if(!$sub_action){ $sub_action = "user_id"; }
		if(!$id){ $id = "DESC"; }
	}
	else
	{	// Put up confirmation
		if ($sql->db_Select("user", "*", "user_id='".$_POST['userid']."' ")) 
		{
			$row = $sql->db_Fetch();
			$qry = (e_QUERY) ? "?".e_QUERY : "";
			$text .= "<form method='post' action='".e_SELF.$qry."'><div style='text-align:center'>\n";
			$text .= "<div>
				<input type='hidden' name='e-token' value='".e_TOKEN."' />
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
// ------- Make Admin.. --------------
if (isset($_POST['useraction']) && $_POST['useraction'] == "admin" && getperms('3')) {
	$sql->db_Select("user", "*", "user_id='".$_POST['userid']."'");
	$row = $sql->db_Fetch();
	 extract($row);
	$sql->db_Update("user", "user_admin='1' WHERE user_id='".$_POST['userid']."' ");
	$user->show_message($user_name." ".USRLAN_3." <a href='".e_ADMIN."administrator.php?edit.$user_id'>".USRLAN_4."</a>");
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
	if ($sql->db_Update("user", "user_ban='0' WHERE user_id='{$uid}' "))
	{
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

if (!e_QUERY || ($action == "main")) {
	$user->show_existing_users($action, $sub_action, $id, $from, $amount);
}

if (isset($action) && $action == "unverified") {
	$user->show_existing_users($action, $sub_action, $id, $from, $amount);
}

if (isset($action) && $action == "options") {
	$user->show_prefs();
}

if (isset($action) && $action == "prune") {
	$user->show_prune();
}

if (isset($action) && $action == "create") {
	$user->add_user();
}

require_once("footer.php");





class users
{

	function show_existing_users($action, $sub_action, $id, $from, $amount)
	{
		global $sql, $rs, $ns, $tp, $mySQLdefaultdb,$pref,$unverified, $currentUser;
		// save the display choices.
		if(isset($_POST['searchdisp']))
		{
			$pref['admin_user_disp'] = implode("|",$_POST['searchdisp']);
			save_prefs();
		}

		if(!$pref['admin_user_disp'])
		{
			$search_display = array("user_name","user_class");
		}
		else
		{
			$search_display = explode("|",$pref['admin_user_disp']);
		}

		if ($sql->db_Select("userclass_classes")) {
			while ($row = $sql->db_Fetch())
			{
				$class[$row['userclass_id']] = $tp->toHTML($row['userclass_name'],"","defs,emotes_off, no_make_clickable");
			}
		}

		$text = "<div style='text-align:center'>";

		if (isset($_POST['searchquery']) && $_POST['searchquery'] != "")
		{
			$_POST['searchquery'] = $tp->toDB(trim($_POST['searchquery']));
      $query = "WHERE ".
			$query .= (strpos($_POST['searchquery'], "@") !== FALSE) ? "user_email REGEXP('".$_POST['searchquery']."') OR ": "";
			$query .= (strpos($_POST['searchquery'], ".") !== FALSE) ? "user_ip REGEXP('".$_POST['searchquery']."') OR ": "";
			foreach($search_display as $disp)
			{
				$query .= "$disp REGEXP('".$_POST['searchquery']."') OR ";
			}
			$query .= "user_login REGEXP('".$_POST['searchquery']."') OR ";
			$query .= "user_name REGEXP('".$_POST['searchquery']."') ";
			if($action == 'unverified')
			{
				$query .= " AND user_ban = 2 ";
			}
			$query .= " ORDER BY user_id";
		} else {
			$query = "";
			if($action == 'unverified')
			{
				$query = "WHERE user_ban = 2 ";
			}
			$query .= "ORDER BY ".($sub_action ? $sub_action : "user_id")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
		}

// $user_total = db_Count($table, $fields = '(*)',
	$qry_insert = "SELECT u.*, ue.* FROM #user AS u	LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id ";

		if ($user_total = $sql->db_Select_gen($qry_insert. $query)) {
			$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
				<tr>
				<td style='width:5%' class='fcaption'><a href='".e_SELF."?main.user_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></td>
				<td style='width:10%' class='fcaption'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc").".$from'>".USRLAN_79."</a></td>";


// Search Display Column header.
	$display_lan = array(
	'user_id' => DUSRLAN_1,
	'user_name' => DUSRLAN_2,
	'user_loginname' => DUSRLAN_3,
	'user_customtitle' => DUSRLAN_4,
	'user_password' => DUSRLAN_5,
	'user_sess' => DUSRLAN_6,
	'user_email' => DUSRLAN_7,
	'user_homepage' => DUSRLAN_8,
	'user_icq' => DUSRLAN_9,
	'user_aim' => DUSRLAN_10,
	'user_msn' => DUSRLAN_11,
	'user_location' => DUSRLAN_12,
	'user_birthday' => DUSRLAN_13,
	'user_signature' => DUSRLAN_14,
	'user_image' => DUSRLAN_15,
	'user_timezone' => DUSRLAN_16,
	'user_hideemail' => DUSRLAN_17,
	'user_join' => DUSRLAN_18,
	'user_lastvisit' => DUSRLAN_19,
	'user_currentvisit' => DUSRLAN_20,
	'user_lastpost' => DUSRLAN_21,
	'user_chats' => DUSRLAN_22,
	'user_comments' => DUSRLAN_23,
	'user_forums' => DUSRLAN_24,
	'user_ip' => DUSRLAN_25,
	'user_ban' => DUSRLAN_26,
	'user_prefs' => DUSRLAN_27,
	'user_new' => DUSRLAN_28,
	'user_viewed' => DUSRLAN_29,
	'user_visits' => DUSRLAN_30,
	'user_admin' => DUSRLAN_31,
	'user_login' => DUSRLAN_32,
	'user_class' => DUSRLAN_33,
	'user_perms' => DUSRLAN_34,
	'user_realm' => DUSRLAN_35,
	'user_pwchange' => DUSRLAN_36,
	'user_xup' => DUSRLAN_37
	);

			foreach($search_display as $disp){
				if (isset($display_lan[$disp])) {
					$text .= "<td style='width:15%' class='fcaption'><a href='".e_SELF."?main.$disp.".($id == "desc" ? "asc" : "desc").".$from'>".$display_lan[$disp]."</a></td>";
				} else {
					$text .= "<td style='width:15%' class='fcaption'><a href='".e_SELF."?main.$disp.".($id == "desc" ? "asc" : "desc").".$from'>".ucwords(str_replace("_"," ",$disp))."</a></td>";
				}
			}

// ------------------------------

			$text .= " 	<td style='width:30%' class='fcaption'>".LAN_OPTIONS."</td>
				</tr>";

			while ($row = $sql->db_Fetch()) {
				extract($row);
				$text .= "<tr>
					<td style='width:5%; text-align:center' class='forumheader3'>$user_id</td>
					<td style='width:10%' class='forumheader3'>";

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



 // Display Chosen options -------------------------------------

	$datefields = array("user_lastpost","user_lastvisit","user_join","user_currentvisit");
	$boleanfields = array("user_admin","user_hideemail","user_ban");

	foreach($search_display as $disp)
	{
		$text .= "<td style='white-space:nowrap' class='forumheader3'>";
		if($disp == "user_class")
		{
			if ($user_class)
			{
				$tmp = explode(",", $user_class);
				while (list($key, $class_id) = each($tmp))
				{
					$text .= ($class[$class_id] ? $class[$class_id]."<br />\n" : "");
				}
			}
			else
			{
				$text .= "&nbsp;";
			}
		}
		elseif ($disp == 'user_ban')
		{
			$text .= ($row[$disp] == 1) ? ADMIN_TRUE_ICON : "";
		}
		elseif(in_array($disp,$boleanfields))
		{
        	$text .= ($row[$disp]) ? ADMIN_TRUE_ICON : "";
		}
		elseif(in_array($disp,$datefields))
		{
			$text .= ($row[$disp]) ? strftime($pref['shortdate'],$row[$disp])."&nbsp;" : "&nbsp";
		}
		elseif($disp == "user_name")
		{
			$text .= "<a href='".e_BASE."user.php?id.{$row['user_id']}'>{$row['user_name']}</a>";
		}
		else
		{
			$text .= $row[$disp]."&nbsp;";
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
					<td style='width:30%;text-align:center' class='forumheader3'>
					<form method='post' action='".e_SELF.$qry."'>
					<div>

					<input type='hidden' name='e-token' value='".e_TOKEN."' />
					<input type='hidden' name='userid' value='{$user_id}' />
					<input type='hidden' name='userip' value='{$user_ip}' />
					<select name='useraction' onchange='this.form.submit()' class='tbox' style='width:75%'>
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
							<option value='reqverify'>".USRLAN_151."</option>
							<option value='verify'>".USRLAN_152."</option>
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
				$text .= "</form></td></tr>";
			}
			$text .= "</table>";
		}

		if($action == "unverified"){
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

		if ($users > $amount && !$_POST['searchquery']) {
			$parms = "{$users},{$amount},{$from},".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.user_id.desc.")."[FROM]";
			$text .= "<br />".$tp->parseTemplate("{NEXTPREV={$parms}}");
		}

// Search - display options etc. .

		$text .= "<br /><form method='post' action='".e_SELF."?".e_QUERY."'>\n";
		$text .= "<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n
		<input class='button' type='submit' name='searchsubmit' value='".USRLAN_90."' />\n
		<br /><br /></p>\n";

		$text .= "<div style='cursor:pointer' onclick=\"expandit('sdisp')\">".LAN_DISPLAYOPT."</div>";
		$text .= "<div  id='sdisp' style='padding-top:4px;display:none;text-align:center;margin-left:auto;margin-right:auto'>
		<table class='forumheader3' style='width:95%'>";
/*
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."user");		// Returns a resource. Deprecated
		$columns = mysql_num_fields($fields);			// Bug in PHP5.3 using mysql_num_fields() with mysql_list_fields()

		for ($i = 0; $i < $columns; $i++)
		{
			$fname[] = mysql_field_name($fields, $i);
		}
*/
		$fname = $sql->db_FieldList('user', '', FALSE);		// Get list of field names

		// include extended fields in the list.
        $sql -> db_Select('user_extended_struct');


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
			$checked = (in_array($fcol,$search_display)) ? "checked='checked'" : "";
			$text .= "<td style='text-align:left; padding:0px'>";
			$text .= "<input type='checkbox' name='searchdisp[]' value='".$fcol."' $checked />".str_replace("user_","",$fcol) . "</td>\n";
			$m++;
			if($m == 5)
			{
				$text .= "</tr>";
				$m = 0;
			}
        }

		if($m != 0)
		{
			$text .= "</tr>";
		}
		$text .= "</table></div>
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
		if ($action == "")
		{
			$action = "main";
		}
		// ##### Display options
		$var['main']['text'] = USRLAN_71;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = USRLAN_72;
		$var['create']['link'] = e_SELF."?create";

		$var['prune']['text'] = USRLAN_73;
		$var['prune']['link'] = e_SELF."?prune";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";

		if($unverified)
		{
			$var['unveri']['text'] = USRLAN_138." ($unverified)";
			$var['unveri']['link'] = e_SELF."?unverified";
		}

		//  $var['mailing']['text']= USRLAN_121;
		//   $var['mailing']['link']="mailout.php";
		show_admin_menu(USRLAN_76, $action, $var);
	}

	function show_prefs() {
		global $ns, $pref;
		$pref['memberlist_access'] = varset($pref['memberlist_access'], e_UC_MEMBER);
		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_44.":</td>
			<td style='width:50%' class='forumheader3'>". ($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' />".LAN_NO : "<input name='avatar_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked='checked' />".LAN_NO). (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
			</td>
			</tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_53.":</td>
			<td style='width:50%' class='forumheader3'>". ($pref['photo_upload'] ? "<input name='photo_upload' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' />".LAN_NO : "<input name='photo_upload' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' checked='checked' />".LAN_NO). (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
			</td>
			</tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_47.":</td>
			<td style='width:50%' class='forumheader3'>
			<input class='tbox' type='text' name='im_width' size='10' value='".$pref['im_width']."' maxlength='5' /> (".USRLAN_48.")
			</td></tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_49.":</td>
			<td style='width:50%' class='forumheader3'>
			<input class='tbox' type='text' name='im_height' size='10' value='".$pref['im_height']."' maxlength='5' /> (".USRLAN_50.")
			</td></tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_126.":</td>
			<td style='width:50%;vertical-align:top' class='forumheader3'>". ($pref['profile_rate'] ? "<input name='profile_rate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' />".LAN_NO : "<input name='profile_rate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_rate' type='radio' value='0' checked='checked' />".LAN_NO)."
			</td>
			</tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_127.":</td>
			<td style='width:50%;vertical-align:top' class='forumheader3'>". ($pref['profile_comments'] ? "<input name='profile_comments' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' />".LAN_NO : "<input name='profile_comments' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='profile_comments' type='radio' value='0' checked='checked' />".LAN_NO)."
			</td>
			</tr>

			<tr>
			<td style='width:50%;vertical-align:top' class='forumheader3'>".USRLAN_133.":<br /><span class='smalltext'>".USRLAN_134."</span></td>
			<td style='width:50%;vertical-align:top' class='forumheader3'>". ($pref['force_userupdate'] ? "<input name='force_userupdate' type='radio' value='1' checked='checked' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' />".LAN_NO : "<input name='force_userupdate' type='radio' value='1' />".LAN_YES."&nbsp;&nbsp;<input name='force_userupdate' type='radio' value='0' checked='checked' />".LAN_NO)."
			</td>
			</tr>


			<tr>
			<td style='width:50%;vertical-align:top' class='forumheader3'>".USRLAN_93."<br /><span class='smalltext'>".USRLAN_94."</span></td>
			<td style='width:50%' class='forumheader3'>
			<input class='tbox' type='text' name='del_unv' size='10' value='".$pref['del_unv']."' maxlength='5' /> ".USRLAN_95."
			</td></tr>

			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_130."<br /><span class='smalltext'>".USRLAN_131."</span></td>
			<td style='width:50%' class='forumheader3'>&nbsp;
			<input type='checkbox' name='track_online' value='1'".($pref['track_online'] ? " checked='checked'" : "")." /> ".USRLAN_132."&nbsp;&nbsp;
			</td>
			</tr>


			<tr>
			<td style='width:50%' class='forumheader3'>".USRLAN_146.":</td>
			<td style='width:50%' class='forumheader3'>".r_userclass("memberlist_access",$pref['memberlist_access'], "off", "public,member,guest,admin,main,classes,nobody")."
			</td>
			</tr>

			<tr>
			<td colspan='2' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='update_options' value='".USRLAN_51."' />
			</td></tr>

			</table></form></div>";
		$ns->tablerender(USRLAN_52, $text);
	}

	function show_message($message) {
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_prune() {
		global $ns, $sql;

		$unactive = $sql->db_Select("user", "*", "user_ban=2");
		$bounced = $sql->db_Select("user", "*", "user_ban=3");
		$older30 = $sql->db_Count("user", "(*)", "WHERE user_ban=2 AND (user_join < ".strtotime("-30 days").")");

		$text = "<div style='text-align:center'><br /><br />
			<form method='post' action='".e_SELF."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='forumheader3' style='text-align:center'><br />".LAN_DELETE.":&nbsp;
			<select class='tbox' name='prune_type'>";

			$prune_type = array(2=>USRLAN_138." [".$unactive."]",'30'=>USRLAN_138." (".USRLAN_219.") [".$older30."]", 3=>USRLAN_145." [".$bounced."]");

			foreach($prune_type as $key=>$val)
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
		$ns->tablerender(USRLAN_55, $text);
	}

	function add_user() 
	{
		global $rs, $ns, $pref,$tp;
		
		$text = "<div style='text-align:center'>". $rs->form_open("post", e_SELF.'?create', "adduserform")."
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:30%' class='forumheader3'>".USRLAN_61."</td>
			<td style='width:70%' class='forumheader3'>
			".$rs->form_text("name", 40, "", varset($pref['displayname_maxlength'],15))."
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".USRLAN_128."</td>
			<td style='width:70%' class='forumheader3'>
			".$rs->form_text("loginname", 40, "", varset($pref['loginname_maxlength'],30))."
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".USRLAN_62."</td>
			<td style='width:70%' class='forumheader3'>
			".$rs->form_password("password1", 40, "", 20)."
			</td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".USRLAN_63."</td>
			<td style='width:70%' class='forumheader3'>
			".$rs->form_password("password2", 40, "", 20)."
			</td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".USRLAN_64."</td>
			<td style='width:70%' class='forumheader3'>
			".$rs->form_text("email", 60, "", 100)."
			</td>
			</tr>";


		$ucSql = new db;
		if ($ucSql->db_Select("userclass_classes"))
		{
			$text .= "<tr style='vertical-align:top'>
				<td colspan='2' style='text-align:center' class='forumheader2'>
				".USRLAN_120."
				</td>
				</tr>";
			$c = 0;
			while ($row = $ucSql->db_Fetch())
			{
				$class[$c][0] = $row['userclass_id'];
				$class[$c][1] = $row['userclass_name'];
				$class[$c][2] = $row['userclass_description'];
				$c++;
			}
			for($a = 0; $a <= (count($class)-1); $a++)
			{
				$text .= "<tr><td style='width:30%' class='forumheader3'>
					<input type='checkbox' name='userclass[]' value='".$class[$a][0]."' />".$tp->toHtml($class[$a][1],FALSE,'defs')."
					</td><td style='width:70%' class='forumheader3'> ".$tp->toHtml($class[$a][2],FALSE,'defs')."</td></tr>";
			}
		}
		$text .= "
			<tr style='vertical-align:top'>
			<td colspan='2' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='adduser' value='".USRLAN_60."' />
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
			</td>
			</tr>
			</table>
			</form>
			</div>
			";

		$ns->tablerender(USRLAN_59, $text);
	}


	function resend($id,$key,$name,$email,$lfile=''){
        global $sql,$mailheader_e107id;

        //@FIXME multilanguage
    	// Check for a Language field, and if present, send the email in the user's language.
        if($lfile == ""){
			if($sql -> db_Select("user_extended", "user_language", "user_extended_id = '$id'")){
    			$row = $sql -> db_Fetch();
				$lfile = e_LANGUAGEDIR.$row['user_language']."/lan_signup.php";
    		}
        }
   		if(is_readable($lfile)){
			require_once($lfile);
		}else{
			$row['user_language'] = e_LANGUAGE;
    		@require_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_signup.php");
		}


		$return_address = (substr(SITEURL, -1) == "/") ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key;

		$message = LAN_EMAIL_01." ".$name."\n\n".LAN_SIGNUP_24." ".SITENAME.".\n".LAN_SIGNUP_21."\n\n";
		$message .= $return_address . "\n\n".SITENAME."\n".SITEURL;

        $mailheader_e107id = $id;

		require_once(e_HANDLER."mail.php");
	  	if(sendemail($email, LAN_404." ".SITENAME, $message))
		{
	   //		echo str_replace("\n","<br>",$message);
		  $this->show_message(USRLAN_140.": <a href='mailto:".$email."?body=".$return_address."' title=\"".DUSRLAN_7."\" >".$name."</a> (".$row['user_language'].") ");
	  	}
		else
		{
    	  $this->show_message(USRLAN_141.": ".$name);
	  	}

	}


	function resend_to_all(){
        global $sql,$pref,$sql3;
		$pause_count = 1;
		$pause_amount = ($pref['mail_pause']) ? $pref['mail_pause'] : 10;
		$pause_time = ($pref['mail_pausetime']) ? $pref['mail_pausetime'] : 1;

		if($sql -> db_Select_gen("SELECT user_language FROM #user_extended LIMIT 1")){
			$query = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id WHERE u.user_ban = 2 ORDER BY u.user_id DESC";
		}else{
        	$query = "SELECT * FROM #user WHERE user_ban='2'";
		}

		if(!is_object($sql3)){
        	$sql3 = new db;
		}

        $sql3 -> db_Select_gen($query);
			while($row = $sql3-> db_Fetch()){
				  	echo $row['user_id']." ".$row['user_sess']." ".$row['user_name']." ".$row['user_email']."<br />";
                    $this->resend($row['user_id'],$row['user_sess'],$row['user_name'],$row['user_email'],$row['user_language']);
                	if($pause_count > $pause_amount){
						sleep($pause_time);
            			$pause_count = 1;
        			}
				sleep(1);
				$pause_count++;
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
      include(e_HANDLER."pop3_class.php");

	  if (!trim($bounce_act)) $bounce_act='first_check';

//	  echo "Check bounces. Action: {$bounce_act}; Entries: {$bounce_arr}<br />";

	  $obj= new receiveMail($pref['mail_bounce_user'],$pref['mail_bounce_pass'],$pref['mail_bounce_email'],$pref['mail_bounce_pop3'],'pop3','110');
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
				  if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i", $obj->getBody($i), $result)) $usr_email = trim($result[0]);
				  if ($sql->db_Select('user','user_id, user_name, user_email',"user_email='".$usr_email."' "))
				  {
				    $row = $sql->db_Fetch();
				    if ($sql->db_Update('user',"`user_email`='' WHERE `user_id` = '".$row['user_id']."' ") !== FALSE)
					{
//					  echo "Deleting user email {$row['user_email']} for user {$row['user_name']}, id={$row['user_id']}<br />";
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



	  if ($tot)
	  { // Option to delete emails - only if there are some in the list
		  $text .= "</table><table style='".ADMIN_WIDTH."'><tr>
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delnonbouncesubmit' value='".USRLAN_153."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='clearemailbouncesubmit' value='".USRLAN_154."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delcheckedsubmit' value='".USRLAN_149."' /></td>\n
			<td class='forumheader3' style='text-align: center;'><input class='button' type='submit' name='delallsubmit' value='".USRLAN_150."' /></td>\n
			</td></tr>";
	  }
	  $text .= "</table></form></div>";

		array_unique($id);
		array_unique($emails);

        $all_ids = implode(",",$id);
		$all_emails = implode(",",$emails);

		$obj->close_mailbox();					// This will actually delete emails

												// $tot has total number of emails in the mailbox
        $found = count($emails);				// $found - Number of bounce emails found
												// $del_count has number of emails deleted


		// Update bounce status for users
	  	$ed = $sql -> db_Update("user", "user_ban=3 WHERE (`user_id` IN (".$all_ids.") OR `user_email` IN (".$all_emails.")) AND user_sess !='' ");
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
?>
