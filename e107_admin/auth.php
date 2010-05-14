<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area Authorization
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/auth.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT'))
{
	exit;
}

/* done in class2
 @include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
 @include_once(e_LANGUAGEDIR."English/admin/lan_admin.php");
 */
if (ADMIN)
{
	define('ADMIN_PAGE', true);
	//don't include it if it'a an AJAX call or not wanted
	if (!e_AJAX_REQUEST && !defset('e_NOHEADER'))
	{
		// XXX LOGIN AS Temporary solution, we need something smarter, e.g. reserved message stack 'admin' which will be always printed
		// inside admin area
		if(e107::getUser()->getSessionDataAs())
		{ // TODO - lan
			$asuser = e107::getSystemUser(e107::getUser()->getSessionDataAs(), false);
			e107::getMessage()->addInfo('Successfully logged in as '.($asuser && $asuser->getValue('name') ? $asuser->getValue('name') : 'unknown'). ' <a href="'.e_ADMIN_ABS.'users.php?logoutas">[logout]</a>');
		}
		require_once (e_ADMIN."header.php");
	}

	/*
	 * FIXME - missing $style for tablerender
	 * The Solution: parse_admin() without sending it to the browser if it's an ajax call
	 * The Problem: doubled render time for the ajax called page!!!
	 */
}
else
{
	//login via AJAX call is not allowed
	if (e_AJAX_REQUEST)
	{
		require_once (e_HANDLER.'js_helper.php');
		e_jshelper::sendAjaxError(403, ADLAN_86, ADLAN_87, true);
	}

	$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));

	if ($use_imagecode)
	{
		require_once (e_HANDLER."secure_img_handler.php");
		$sec_img = new secure_image;
	}

	if ($_POST['authsubmit'])
	{
		$obj = new auth;

		if ($use_imagecode)
		{
			if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
			{
				echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
				header("location: ../index.php");
				exit;
			}
		}

	//	require_once (e_HANDLER.'user_handler.php');
		$row = $authresult = $obj->authcheck($_POST['authname'], $_POST['authpass'], varset($_POST['hashchallenge'], ''));

		if ($row[0] == "authfail")
		{
			$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", LAN_ROLL_LOG_11, "U: ".$tp->toDB($_POST['authname']), FALSE, LOG_TO_ROLLING);
			echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
		//	header("location: ../index.php");
			e107::getRedirect()->redirect('../index.php');
			exit;
		}
		else
		{
			$cookieval = $row['user_id'].".".md5($row['user_password']);

			//	  $sql->db_Select("user", "*", "user_name='".$tp -> toDB($_POST['authname'])."'");
			//	  list($user_id, $user_name, $userpass) = $sql->db_Fetch();

			// Calculate class membership - needed for a couple of things
			// Problem is that USERCLASS_LIST just contains 'guest' and 'everyone' at this point
			$class_list = explode(',', $row['user_class']);
			if ($row['user_admin'] && strlen($row['user_perms']))
			{
				$class_list[] = e_UC_ADMIN;
				if (strpos($row['user_perms'], '0') === 0)
				{
					$class_list[] = e_UC_MAINADMIN;
				}
			}
			$class_list[] = e_UC_MEMBER;
			$class_list[] = e_UC_PUBLIC;

			$user_logging_opts = array_flip(explode(',', varset($pref['user_audit_opts'], '')));
			if (isset($user_logging_opts[USER_AUDIT_LOGIN]) && in_array(varset($pref['user_audit_class'], ''), $class_list))
			{ // Need to note in user audit trail
				e107::getAdminLog()->user_audit(USER_AUDIT_LOGIN, '', $user_id, $user_name);
			}

			$edata_li = array("user_id"=>$row['user_id'], "user_name"=>$row['user_name'], 'class_list'=>implode(',', $class_list));

			e107::getEvent()->trigger("login", $edata_li);

			session_set(e_COOKIE, $cookieval, (time() + 3600 * 24 * 30));
			echo "<script type='text/javascript'>document.location.href='admin.php'</script>\n";
		}
	}

	$e_sub_cat = 'logout';
	if (!defset('NO_HEADER'))
		require_once (e_ADMIN."header.php");

	if (ADMIN == FALSE)
	{
		$obj = new auth;
		$obj->authform();
		if (!defset('NO_HEADER'))
			require_once (e_ADMIN."footer.php");
		exit;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class auth
{

	/**
	 * Admin auth login
	 * @return null
	 */
	public function authform() //TODO Template
	{
		global $use_imagecode,$sec_img,$pref;

		$frm = e107::getForm();

		$incChap = (vartrue($pref['password_CHAP'], 0)) ? " onsubmit='hashLoginPassword(this)'" : "";

		$text = "<div style='padding:20px;text-align:center'>
			<form method='post' action='".e_SELF."' {$incChap} >
			<table style='width:50%' class='fborder'>
			<tr>
            <td rowspan='4' style='vertical-align:middle;width:65px'>".(file_exists(THEME."images/password.png") ? "<img src='".THEME_ABS."images/password.png' alt='' />\n" : "<img src='".e_IMAGE."generic/password.png' alt='' />\n")."</td>
			<td style='width:35%' class='forumheader3'>".ADLAN_89."</td>
			<td class='forumheader3' style='text-align:center'><input class='tbox' type='text' name='authname' id='username' size='30' value='' maxlength='".varset($pref['loginname_maxlength'], 30)."' />\n</td>

			</tr>
			<tr>
			<td style='width:35%' class='forumheader3'>".ADLAN_90."</td>
			<td class='forumheader3' style='text-align:center'><input class='tbox' type='password' name='authpass' id='userpass' size='30' value='' maxlength='30' />\n";

		if (isset($_SESSION['challenge']) && varset($pref['password_CHAP'], 0))

		$text .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='{$_SESSION['challenge']}' />\n\n";
		$text .= "</td></tr>\n";

		if ($use_imagecode)
		{
			$text .= "
			<tr>
			<td style='width:35%' class='forumheader3'>".ADLAN_152."</td>
			<td style='text-align:center'>
			<input type='hidden' name='rand_num' value='".$sec_img->random_number."' />".$sec_img->r_image()."<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20' /></td>
			</tr>
			";
		}

		$text .= "
			<tr>
			<td colspan='2' class='forumheader center'>"
			.$frm->admin_button('authsubmit',ADLAN_91).
			"</td>
			</tr>
			</table>
			</form>
			</div>";

		e107::getRender()->tablerender(ADLAN_92, $text);
	}


	/**
	 * Admin auth check
	 * @param string $authname, entered name
	 * @param string $authpass, entered pass
	 * @param object $authresponse [optional]
	 * @return boolean if fail, else result array
	 */
	public function authcheck($authname, $authpass, $authresponse = '')
	{

		global $pref;

		$tp 		= e107::getParser();
		$sql_auth 	= e107::getDb('sql_auth');
		$user_info 	= e107::getSession();
		$reason = '';

		$authname = $tp->toDB(preg_replace("/\sOR\s|\=|\#/", "", trim($authname)));
		$authpass = trim($authpass);

		if (($authpass == '') || ($authname == ''))
			$reason = 'np';
		if (strlen($authname) > varset($pref['loginname_maxlength'], 30))
			$reason = 'lu';

		if (!$reason)
		{
			if ($sql_auth->db_Select("user", "*", "user_loginname='{$authname}' AND user_admin='1' "))
			{
				$row = $sql_auth->db_Fetch();
			}
			elseif ($sql_auth->db_Select("user", "*", "user_name='{$authname}' AND user_admin='1' "))
			{
				$row = $sql_auth->db_Fetch();
				$authname = $row['user_loginname'];
			}
			else
			{
				$reason = 'iu';
			}
		}
		if (!$reason && ($row['user_id'])) // Can validate password
		{
			if (($authresponse && isset($_SESSION['challenge'])) && ($authresponse != $_SESSION['challenge']))
			{ // Verify using CHAP (can't handle login by email address - only loginname - although with this code it does still work if the password is stored unsalted)
				if (($pass_result = $user_info->CheckCHAP($_SESSION['challenge'], $authresponse, $authname, $row['user_password'])) !== PASSWORD_INVALID)
				{
					return $$row;
				}
			}
			else
			{ // Plaintext password
				if (($pass_result = $user_info->CheckPassword($authpass, $authname, $row['user_password'])) !== PASSWORD_INVALID)
				{
					return $row;
				}
			}
		}
		return array("authfail", "reason"=>$reason);
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>
