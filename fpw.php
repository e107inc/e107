<?php
/*
* e107 website system
*
* Copyright 2008-2014 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Interface for users who have forgotten their password
*
*/

require_once('class2.php');

e107::coreLan('fpw'); 

$tp = e107::getParser();

if (USER && !getperms('0'))
{
	e107::redirect();
	exit;
}

if($pref['fpwcode'] && extension_loaded('gd'))
{
	define('USE_IMAGECODE', TRUE);
	$sec_img = e107::getSecureImg();
}
else
{
	define('USE_IMAGECODE', FALSE);
}


class fpw_shortcodes extends e_shortcode
{	
	private $secImg;
	
	function __construct()
	{
		global $sec_img; 
		$this->secImg = $sec_img;	
	}

	function sc_fpw_username($parm='') // used when email login is disabled
	{
		// return "<input class='tbox' type='text' name='username' size='40' value='' maxlength='100' />";	
		return e107::getForm()->text('username'); // $frm->userpicker()?
	}

	function sc_fpw_useremail($parm='') 
	{
		// return '<input class="tbox form-control" type="text" name="email" size="40" value="" maxlength="100" placeholder="Email" required="required" type="email">';
		// return "<input class='tbox' type='text' name='email' size='40' value='' maxlength='100' />";	
		return e107::getForm()->email('email', '', 200, array('placeholder' => 'Email', 'required' => 'required')); 
	}

	function sc_fpw_submit($parm='') 
	{
		// return '<button type="submit" name="pwsubmit" class="button btn btn-primary btn-block reset">'.$label.'</button>';
		// return "<input class='button btn btn-primary btn-block' type='submit' name='pwsubmit' value='".$label."' />";	
		$label = deftrue('LAN_FPW_102', LAN_SUBMIT);
		return e107::getForm()->button('pwsubmit', $label); 
	}

	function sc_fpw_captcha_lan($parm='')
	{
		return LAN_ENTER_CODE;
	}
	
	function sc_fpw_captcha_hidden($parm='')
	{
		return; // no longer required - included in renderInput();
	}

	/**
	 * @param string $parm
	 * @return mixed|null|string
	 */
	function sc_fpw_captcha_img($parm='')
	{
		if(USE_IMAGECODE)
		{
			return $this->secImg->renderImage();
		}

		return null;
	}

	/**
	 * @param string $parm
	 * @return mixed|null|string
	 */
	function sc_fpw_captcha_input($parm='')
	{
		if(USE_IMAGECODE)
		{
			return $this->secImg->renderInput();
		}

		return null;
	}

	function sc_fpw_logo($parm='')
	{
		// Unused at the moment. 	
	}
	
	function sc_fpw_text($parm='')
	{
		return deftrue('LAN_FPW_101',"Not to worry. Just enter your email address below and we'll send you an instruction email for recovery.");	
	}
}


if ($pref['membersonly_enabled'])
{
	$sc = array (
		'FPW_LOGIN_LOGO' => file_exists(THEME."images/login_logo.png") ? "<img src='".THEME_ABS."images/login_logo.png' alt='' />\n" : "<img src='".e_IMAGE_ABS."logo.png' alt='' />\n"
	);
	
	
	if(deftrue('BOOTSTRAP'))
	{
		$FPW_TABLE_HEADER = e107::getCoreTemplate('fpw','header');	
		$FPW_TABLE_FOOTER = e107::getCoreTemplate('fpw','footer');	
	}
	else
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
	if(deftrue('BOOTSTRAP'))
	{
		e107::getMessage()->addError($txt);
		e107::getRender()->tablerender(LAN_03, e107::getMessage()->render());
		require_once(FOOTERF);
		exit;
	}

	e107::getRender()->tablerender(LAN_03, "<div class='fpw-page'>".$txt."</div>", 'fpw');
	require_once(FOOTERF);
	exit;
}

//the separator character used
define('FPW_SEPARATOR', '#');
//$fpw_sep = '#';


// User has clicked on the emailed link
if(e_QUERY)
{	
	// Make sure login menu is not giving any troubles
	define('FPW_ACTIVE','TRUE');

	// Verify the password reset code syntax
	$tmpinfo = preg_replace("#[\W_]#", "", e107::getParser()->toDB(e_QUERY, true));			// query part is a 'random' number
	if ($tmpinfo != e_QUERY)
	{
		// Shouldn't be any characters that toDB() changes
		//die();			
		e107::getRedirect()->redirect(SITEURL);
	}

	// Verify the password reset code
	if ($sql->select('tmp', '*', "`tmp_ip`='pwreset' AND `tmp_info` LIKE '%".FPW_SEPARATOR.$tmpinfo."' "))
	{
		$row = $sql->fetch();

		// Delete the record 
		$sql->delete('tmp', "`tmp_time` = ".$row['tmp_time']." AND `tmp_info` = '".$row['tmp_info']."' ");

		list($loginName, $md5) = explode(FPW_SEPARATOR, $row['tmp_info']);
		$loginName = $tp->toDB($loginName, true);

		// This should never happen! 
		if($md5 != $tmpinfo)
		{
			e107::getRedirect()->redirect(SITEURL);	
		}

		// Generate new temporary password
		$newpw 		= $user_info->generateRandomString(str_repeat('*', rand(8, 12)));		
		$mdnewpw 	= $user_info->HashPassword($newpw, $loginName);

		// Details for admin log
		$do_log['password_action'] = LAN_FPW21;
		//$do_log['user_name'] = $tp -> toDB($username, true);
		$do_log['user_loginname'] = $loginName;
		$do_log['activation_code'] = $tmpinfo;
		$do_log['user_password'] = $mdnewpw;
		$admin_log->user_audit(USER_AUDIT_PW_RES,$do_log,0,$do_log['user_name']);

		// Update password in database
		$sql->update('user', "`user_password`='{$mdnewpw}' WHERE `user_loginname`='".$loginName."' ");
		
		// Prepare new information to display to user
		if((integer) e107::getPref('allowEmailLogin') > 0)
		{
			// always show email when possible
			$sql->select('user', 'user_email', "user_loginname='{$loginName}'");
			$tmp = $sql->fetch();
			$loginName = $tmp['user_email'];
			unset($tmp);
		}

		// Reset login cookie/session (?)
		cookie($pref['cookie_name'], '', (time()-2592000));
		$_SESSION[$pref['cookie_name']] = '';

		// Display success message containing new login information
		$txt = "<div class='fpw-message'>".LAN_FPW8."</div>
		<table class='fpw-info'>
		<tr><td>".LAN_218."</td><td style='font-weight:bold'>{$loginName}</td></tr>
		<tr><td>".LAN_FPW9."</td><td style='font-weight:bold'>{$newpw}</td></tr>
		</table>
		<br /><br />".LAN_FPW10." <a href='".e_LOGIN."'>".LAN_FPW11."</a> ".LAN_FPW12;
		
		e107::getMessage()->addSuccess($txt);
		e107::getRender()->tablerender(LAN_03, e107::getMessage()->render());
		require_once(FOOTERF);
		exit;
	}
	// The password reset code was not found
	else
	{
		fpw_error(LAN_FPW7);		
	}
}


// Request to reset password
if (isset($_POST['pwsubmit']))
{	
	require_once(e_HANDLER.'mail.php');
	
	if ($pref['fpwcode'] && extension_loaded('gd'))
	{
		if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
		{
			fpw_error(LAN_INVALID_CODE);
		}
	}
	
	$email 			= $_POST['email'];
	$clean_email 	= check_email($tp->toDB($_POST['email']));
	$clean_username = $tp->toDB(varset($_POST['username'], ''));
 	
 	$query = "`user_email`='{$clean_email}' ";
	// Allow admins to remove 'username' from fpw_template.php if they wish.
	$query .= (isset($_POST['username'])) ? " AND `user_loginname`='{$clean_username}'" : "";

	if($sql->select('user', '*', $query))
	{	
		// Found user in DB
		$row = $sql->fetch();

		// Main admin expected to be competent enough to never forget password! (And its a security check - so warn them)
		// Sending email to admin alerting them of attempted admin password reset, and redirect user to homepage. 
		if (($row['user_admin'] == 1) && (($row['user_perms'] == '0')  OR ($row['user_perms'] == '0.')))
		{	
			sendemail($pref['siteadminemail'], LAN_06, LAN_07.' ['.e107::getIPHandler()->getIP(FALSE).'] '.e107::getIPHandler()->getIP(TRUE).' '.LAN_08);
			e107::getRedirect()->redirect(SITEURL);
		}

		// Banned user, or not validated
		switch($row['user_ban'])
		{	
			case USER_BANNED:
				e107::getRedirect()->redirect(SITEURL);
				break;
			case USER_VALIDATED:
				break;
			default:
				fpw_error(LAN_02.':'.$row['user_ban']);		// Intentionally rather a vague message
				exit;
		}

		// Check if password reset was already requested
		if ($result = $sql->select('tmp', '*', "`tmp_ip` = 'pwreset' AND `tmp_info` LIKE '".$row['user_loginname'].FPW_SEPARATOR."%'"))
		{
			fpw_error(LAN_FPW4);		
			exit;
		}

		// Set unique reset code
		mt_srand ((double)microtime() * 1000000);
		$maxran 	= 1000000;
		$rand_num 	= mt_rand(0, $maxran);
		$datekey 	= date('r');
		$rcode 		= md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey);

		// Prepare email
		$link 		= SITEURL.'fpw.php?'.$rcode;
		$message 	= LAN_FPW5.' '.SITENAME.' '.LAN_FPW14.': '.e107::getIPHandler()->getIP(TRUE).".\n\n".LAN_FPW15."\n\n".LAN_FPW16."\n\n".LAN_FPW17."\n\n{$link}";

		// Set timestamp two days ahead so it doesn't get auto-deleted
		$deltime = time()+86400 * 2;			
		
		// Insert the password reset request into the database 
		$sql->insert('tmp', "'pwreset',{$deltime},'".$row['user_loginname'].FPW_SEPARATOR.$rcode."'");

		// Setup the information to log
		$do_log['password_action'] 	= LAN_FPW18;
		$do_log['user_id'] 			= $row['user_id'];
		$do_log['user_name'] 		= $row['user_name'];
		$do_log['user_loginname'] 	= $row['user_loginname'];
		$do_log['activation_code'] 	= $rcode;

		if(getperms('0'))
		{
			$ns->tablerender("Testing Mode", print_a($message,true));
			require_once(FOOTERF);
			exit;
		}

		// Try to send the email 
		if(sendemail($clean_email, "".LAN_09."".SITENAME, $message))
		{
			e107::getMessage()->addInfo(LAN_FPW6);
			$do_log['password_result'] = LAN_FPW20;
		}
		else
		{
			//$text = "<div style='text-align:center'>".LAN_02."</div>";
			$do_log['password_result'] = LAN_FPW19;
		  	fpw_error(LAN_02); 
		}

		// Log to user audit log
		e107::getAdminLog()->user_audit(USER_AUDIT_PW_RES, $do_log, $row['user_id'], $row['user_name']);

		$ns->tablerender(LAN_03, $text.e107::getMessage()->render());
		require_once(FOOTERF);
		exit;
	}
	else
	{
		//$text = LAN_213;
		//$ns->tablerender(LAN_214, "<div style='text-align:center'>".$text."</div>");
		e107::getMessage()->addError(LAN_213); 
		$ns->tablerender(LAN_214, e107::getMessage()->render()); 
	}
}


$sc = array(); // needed?


if(deftrue('BOOTSTRAP'))
{
	// TODO do we want the <form> element outside the template?
	$FPW_TABLE = "<form method='post' action='".SITEURL."fpw.php' autocomplete='off'>";
	$FPW_TABLE .= e107::getCoreTemplate('fpw','form');	
	$FPW_TABLE .= "</form>"; 
	$caption = deftrue('LAN_FPW_100',"Forgot your password?");	
}	
elseif(!$FPW_TABLE)
{
	require_once (e107::coreTemplatePath('fpw')); //correct way to load a core template.
	$caption = LAN_03;
}

$sc = new fpw_shortcodes;

// New Shortcode names in v2. BC Fix. 
$bcShortcodes 	= array('{FPW_TABLE_SECIMG_LAN}', '{FPW_TABLE_SECIMG_HIDDEN}', '{FPW_TABLE_SECIMG_SECIMG}', '{FPW_TABLE_SECIMG_TEXTBOC}');
$nwShortcodes 	= array('{FPW_CAPTCHA_LAN}', '{FPW_CAPTCHA_HIDDEN}', '{FPW_CAPTCHA_IMG}', '{FPW_CAPTCHA_INPUT}');
$FPW_TABLE 		= str_replace($bcShortcodes,$nwShortcodes,$FPW_TABLE);

$text = $tp->parseTemplate($FPW_TABLE, true, $sc);

// $text = $tp->simpleParse($FPW_TABLE, $sc);

$ns->tablerender($caption, $text);
require_once(FOOTERF);

?>