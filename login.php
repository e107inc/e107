<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/login.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("class2.php");


if ((USER || e_LOGIN != e_SELF || (empty($pref['user_reg']) && empty($pref['social_login_active']))) && e_QUERY !== 'preview' && !getperms('0') ) // Disable page if user logged in, or some custom e_LOGIN value is used.
{
	$prev = e107::getRedirect()->getPreviousUrl();

	if(!empty($prev))
	{
		e107::redirect($prev);
		exit();
	}

	e107::redirect();
	exit();
}

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

if(!defined('e_IFRAME')) define('e_IFRAME',true);
require_once(HEADERF);
$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));

define("LOGIN_CAPTCHA", $use_imagecode);

//if (LOGIN_CAPTCHA)
//{
	//require_once(e_HANDLER."secure_img_handler.php");
	//$sec_img = new secure_image;
//}

if (!USER || getperms('0'))
{
	if (!defined('LOGINMESSAGE')) define('LOGINMESSAGE', '');		// LOGINMESSAGE only appears with errors
	require_once(e_HANDLER.'form_handler.php'); // required for BC
	$rs = new form; // required for BC

	if (empty($LOGIN_TABLE))
	{

		if(deftrue('BOOTSTRAP'))
		{
			$LOGIN_TEMPLATE = e107::getCoreTemplate('login');
		}
		else // BC Stuff.
		{

			if (file_exists(THEME.'templates/login_template.php')) //v2.x path
			{
				require_once(THEME.'templates/login_template.php');
			}
			elseif (file_exists(THEME.'login_template.php'))
			{
				require_once(THEME.'login_template.php');
			}
			else
			{
				$LOGIN_TEMPLATE = e107::getCoreTemplate('login');
			}
		}
	}


	$sc = e107::getScBatch('login');
	$sc->wrapper('login/page');


	if(!empty($LOGIN_TEMPLATE['page']))
	{
		$LOGIN_TABLE_HEADER = $LOGIN_TEMPLATE['page']['header'];
		$LOGIN_TABLE 		= "<form id='login-page' class='form-signin' method='post' action='".e_SELF."' onsubmit='hashLoginPassword(this)' >".$LOGIN_TEMPLATE['page']['body']."</form>";
		$LOGIN_TABLE_FOOTER = $LOGIN_TEMPLATE['page']['footer'];
	}


	$text = $tp->parseTemplate($LOGIN_TABLE,true, $sc);

	if(getperms('0'))
	{
		echo "<div class='alert alert-block alert-error alert-danger center'> You are currently logged in as the Main Admin.</div>"; //TODO LAN

		if(empty($pref['user_reg']))
		{
			echo "<div class='alert alert-block alert-error alert-danger center'>User registration and/or login is currently disabled. <a class='alert-link' target='_blank' href='".e_ADMIN_ABS."prefs.php#nav-core-prefs-registration'>Go here to enable it.</a></div>"; //TODO LAN
		}

	}


	$login_message = SITENAME; //	$login_message = LAN_LOGIN_3." | ".SITENAME;
	if(strpos($LOGIN_TABLE_HEADER,'LOGIN_TABLE_LOGINMESSAGE') === false && strpos($LOGIN_TABLE,'LOGIN_TABLE_LOGINMESSAGE') === false)
	{
		echo LOGINMESSAGE;
	}

	echo $tp->parseTemplate($LOGIN_TABLE_HEADER,true, $sc);
	$ns->tablerender($login_message, $text, 'login_page');
	echo $tp->parseTemplate($LOGIN_TABLE_FOOTER, true, $sc);

}

require_once(FOOTERF);

exit;

?>
