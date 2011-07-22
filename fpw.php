<?php
/*
* e107 website system
*
* Copyright 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Interface for users who have forgotten their password
*
* $URL$
* $Id$
*
*/
require_once('class2.php');
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
$tp = e107::getParser();

if (USER)
{
	header('location:'.e_BASE.'index.php');
	exit;
}

if($pref['fpwcode'] && extension_loaded('gd'))
{
	define('USE_IMAGECODE',TRUE);
	require_once(e_HANDLER.'secure_img_handler.php');
	$sec_img = new secure_image;
}
else
{
	define('USE_IMAGECODE',FALSE);
}



if ($pref['membersonly_enabled'])
{
	$sc = array (
		'FPW_LOGIN_LOGO' => file_exists(THEME."images/login_logo.png") ? "<img src='".THEME_ABS."images/login_logo.png' alt='' />\n" : "<img src='".e_IMAGE_ABS."logo.png' alt='' />\n"
	);
	//if (!$FPW_TABLE_HEADER)
	{
		require_once (e107::coreTemplatePath('fpw')); //correct way to load a core template.
	}
	$HEADER = $tp->simpleParse($FPW_TABLE_HEADER, $sc);
	$FOOTER = $tp->simpleParse($FPW_TABLE_FOOTER, $sc);
}

$user_info = e107::getUserSession();

require_once(HEADERF);

function fpw_error($txt)
{
	global $ns;
	$ns->tablerender(LAN_03, "<div style='text-align:center'>".$txt."</div>");
	require_once(FOOTERF);
	exit;
}



//the separator character used
define('FPW_SEPARATOR', '#');
//$fpw_sep = '#';



if (e_QUERY)
{	// User has clicked on the emailed link
	define('FPW_ACTIVE','TRUE');
	$tmpinfo = preg_replace("#[\W_]#", "", e107::getParser()->toDB(e_QUERY, true));			// query part is a 'random' number
	if ($tmpinfo != e_QUERY)
	{
		die();			// Shouldn't be any characters that toDB() changes
	}
	if ($sql->db_Select('tmp', '*', "`tmp_ip`='pwreset' AND `tmp_info` LIKE '%".FPW_SEPARATOR.$tmpinfo."' "))
	{
		$row = $sql->db_Fetch();
		$sql->db_Delete('tmp', "`tmp_time` = ".$row['tmp_time']." AND `tmp_info` = '".$row['tmp_info']."' ");

		list($loginName, $md5) = explode(FPW_SEPARATOR, $row['tmp_info']);
		$loginName = $tp -> toDB($loginName, true);

		if ($md5 != $tmpinfo)
		{
			die('Random mismatch!');			// This should never happen!
		}

		$newpw = $user_info->generateRandomString(str_repeat('*', rand(8, 12)));		// Generate new temporary password
		$mdnewpw = $user_info->HashPassword($newpw,$loginName);

		// Details for admin log
		$do_log['password_action'] = LAN_FPW21;
		//$do_log['user_name'] = $tp -> toDB($username, true);
		$do_log['user_loginname'] = $loginName;
		$do_log['activation_code'] = $tmpinfo;
		$do_log['user_password'] = $mdnewpw;
		$admin_log->user_audit(USER_AUDIT_PW_RES,$do_log,0,$do_log['user_name']);

		$sql->db_Update('user', "`user_password`='{$mdnewpw}' WHERE `user_loginname`='".$loginName."' ");

		cookie($pref['cookie_name'], '', (time()-2592000));
		$_SESSION[$pref['cookie_name']] = '';

		$txt = "<div>".LAN_FPW8."<br /><br />
		<table style='width:70%'>
		<tr><td>".LAN_218."</td><td style='font-weight:bold'>{$loginName}</td></tr>
		<tr><td>".LAN_FPW9."</td><td style='font-weight:bold'>{$newpw}</td></tr>
		</table>
		<br /><br />".LAN_FPW10." <a href='".e_LOGIN."'>".LAN_FPW11."</a> ".LAN_FPW12."</div>";
		fpw_error($txt);

	}
	else
	{
		fpw_error(LAN_FPW7);		// No 'forgot password' entry found
	}
}


// Request to reset password
//--------------------------
if (isset($_POST['pwsubmit']))
{	// Request for password reset submitted
	require_once(e_HANDLER.'mail.php');
	$email = $_POST['email'];

	if ($pref['fpwcode'] && extension_loaded('gd'))
	{
		if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
		{
			fpw_error(LAN_FPW3);
		}
	}

	$clean_email = check_email($tp -> toDB($_POST['email']));
	$clean_username = $tp -> toDB(varset($_POST['username'], ''));
 	$query = "`user_email`='{$clean_email}' ";
	// Allow admins to remove 'username' from fpw_template.php if they wish.
	$query .= (isset($_POST['username'])) ? " AND `user_loginname`='{$clean_username}'" : "";

	if ($sql->db_Select('user', '*', $query))
	{	// Found user in DB
		$row = $sql->db_Fetch();

		if (($row['user_admin'] == 1) && (($row['user_perms'] == '0')  OR ($row['user_perms'] == '0.')))
		{	// Main admin expected to be competent enough to never forget password! (And its a security check - so warn them)
			sendemail($pref['siteadminemail'], LAN_06, LAN_07.' '.$e107->getip().' '.LAN_08);
			echo "<script type='text/javascript'>document.location.href='index.php'</script>\n";
			die();
		}

		switch ($row['user_ban'])
		{	// Banned user, or not validated
			case USER_BANNED :
				die();
			case USER_VALIDATED :
				break;
			default :
				fpw_error(LAN_FPW22.':'.$row['user_ban']);		// Intentionally rather a vague message
				exit;
		}

		if ($result = $sql->db_Select('tmp', '*', "`tmp_ip` = 'pwreset' AND `tmp_info` LIKE '".$row['user_loginname'].FPW_SEPARATOR."%'"))
		{
			fpw_error(LAN_FPW4);		// Password reset already requested
			exit;
		}

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date('r');
		$rcode = md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey);

		$link = SITEURL.'fpw.php?'.$rcode;
		$message = LAN_FPW5.' '.SITENAME.' '.LAN_FPW14.' : '.$e107->getip().".\n\n".LAN_FPW15."\n\n".LAN_FPW16."\n\n".LAN_FPW17."\n\n{$link}";

		$deltime = time()+86400 * 2;			//Set timestamp two days ahead so it doesn't get auto-deleted
		$sql->db_Insert('tmp', "'pwreset',{$deltime},'".$row['user_loginname'].FPW_SEPARATOR.$rcode."'");

		$do_log['password_action'] = LAN_FPW18;
		$do_log['user_id'] = $row['user_id'];
		$do_log['user_name'] = $row['user_name'];
		$do_log['user_loginname'] = $row['user_loginname'];
		$do_log['activation_code'] = $rcode;

		if (sendemail($_POST['email'], "".LAN_09."".SITENAME, $message))
		{
		  $text = "<div style='text-align:center'>".LAN_FPW6."</div>";
		  $do_log['password_result'] = LAN_FPW20;
		}
		else
		{
		  $text = "<div style='text-align:center'>".LAN_02."</div>";
		  $do_log['password_result'] = LAN_FPW19;
		}
		$admin_log->user_audit(USER_AUDIT_PW_RES,$do_log,$row['user_id'],$row['user_name']);

		$ns->tablerender(LAN_03, $text);
		require_once(FOOTERF);
		exit;
	}
	else
	{
		$text = LAN_213;
		$ns->tablerender(LAN_214, "<div style='text-align:center'>".$text."</div>");
	}
}


$sc = array();
if (USE_IMAGECODE)
{
	$sc = array (
		'FPW_TABLE_SECIMG_LAN' => LAN_FPW2,
		'FPW_TABLE_SECIMG_HIDDEN' => "<input type='hidden' name='rand_num' value='".$sec_img->random_number."' />",
		'FPW_TABLE_SECIMG_SECIMG' => $sec_img->r_image(),
		'FPW_TABLE_SECIMG_TEXTBOC' => "<input class='tbox' type='text' name='code_verify' size='15' maxlength='20' />"
	);
}

if (!$FPW_TABLE)
{
	require_once (e107::coreTemplatePath('fpw')); //correct way to load a core template.
}
$text = $tp->simpleParse($FPW_TABLE, $sc);

$ns->tablerender(LAN_03, $text);
require_once(FOOTERF);

?>