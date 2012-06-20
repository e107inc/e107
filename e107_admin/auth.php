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
	
	$sec_img = e107::getSecureImg();

	$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));

	if ($_POST['authsubmit'])
	{
		$obj = new auth;

		if ($use_imagecode)
		{	
			if ($sec_img->invalidCode($_POST['rand_num'], $_POST['code_verify']))
			{
				e107::getRedirect()->redirect('admin.php?failed');
				exit;
			//	echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
			//	header("location: ../index.php");
			//	exit;
			}
		}

	//	require_once (e_HANDLER.'user_handler.php');
		$row = $authresult = $obj->authcheck($_POST['authname'], $_POST['authpass'], varset($_POST['hashchallenge'], ''));

		if ($row[0] == "authfail")
		{
			$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", LAN_ROLL_LOG_11, "U: ".$tp->toDB($_POST['authname']), FALSE, LOG_TO_ROLLING);
			echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
		//	header("location: ../index.php");
			e107::getRedirect()->redirect('admin.php?failed');
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

			$edata_li = array("user_id"=>$row['user_id'], "user_name"=>$row['user_name'], 'class_list'=>implode(',', $class_list), 'user_admin'=> $row['user_admin']);
			
			// Fix - set cookie before login trigger
			session_set(e_COOKIE, $cookieval, (time() + 3600 * 24 * 30));
			
			e107::getEvent()->trigger("login", $edata_li);
			e107::getRedirect()->redirect(e_ADMIN_ABS.'admin.php');
			//echo "<script type='text/javascript'>document.location.href='admin.php'</script>\n";
		}
	}

	$e_sub_cat = 'logout';
	if (ADMIN == FALSE)
	{
		define("e_IFRAME",TRUE);
	}	
	if (!defset('NO_HEADER'))
		require_once (e_ADMIN."header.php");

	if (ADMIN == FALSE)
	{
		// Needs help from Deso, Vesko and Stoev! :-)
		
		e107::css('inline',"
		
			body 				{ 	text-align: left; font-size:15px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; background:#134B63 url(".e_IMAGE."logo_template_large.png) no-repeat 50% 10px; }
			a					{ 	color:#F6931E; text-decoration:none; }
			a:hover				{ 	color:silver; text-decoration:none; }
			.bold				{ 	font-weight:bold; }
			.field				{ 	text-align:center;padding:5px }
			.field input		{	padding:5px; 
									border-width:1px;							
    								border-style:solid;
    								border-color:#aaa #c8c8c8 #c8c8c8 #aaa;
									background:#fff;
									font:16px arial, helvetica, sans-serif;
									-moz-border-radius: 4px;
									-webkit-border-radius: 4px;
									border-radius: 4px;
									-moz-box-shadow: 1px 1px 2px #999 inset;
									-webkit-box-shadow: 1px 1px 2px #999 inset;
									box-shadow: 1px 1px 2px #999 inset;
								}
			
			.field input:focus	{
									border:1px solid #F6931E;
								}
								
			.field input:hover	{
									border:1px solid #F6931E;
								}
					
			#login-admin 		{
									margin-left:auto;
									margin-right:auto;
									margin-top:200px;
									width:400px; 
									padding: 10px 20px 0 20px;
									-moz-border-radius:5px;
									-webkit-border-radius:5px;
									border-radius:5px;
									-moz-box-shadow:5px 5px 20px #000000;
									-webkit-box-shadow:5px 5px 20px #000000;
									box-shadow:5px 5px 20px #000000;	
									background-color: #FEFEFE;
								}
			
			#login-admin label 	{ 	display: none; text-align: right	}
				
			
			.admin-submit 		{ 	text-align: center; 	padding:20px;	}
			
			.submit				{  }
			
		
			.placeholder 		{	color: #bbb; font-style:italic	}
	
			::-webkit-input-placeholder { font-style:italic;	color: #bbb; 	}
		
			:-moz-placeholder 	{ font-style:italic;	color: #bbb; 		}
			
			h2					{ text-align: center; color: #FAAD3D; text-shadow: #000 1px 1px 1px; }
			
			#username			{background: url(".e_IMAGE."admin_images/admins_16.png) no-repeat scroll 7px 7px; padding-left:30px; }
				 
			#userpass			{background: url(".e_IMAGE."admin_images/lock_16.png) no-repeat scroll 7px 7px; padding-left:30px; }
			
			input[disabled] 	{	color: silver;	}
			button[disabled] span	{	color: silver;	}
		
		");
		
	
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
	public function authform()  // NOTE: this should NOT be a template of the admin-template, however themes may style it using css. 
	{
		global $use_imagecode,$sec_img,$pref;

		$frm = e107::getForm();

		$incChap = (vartrue($pref['password_CHAP'], 0)) ? " onsubmit='hashLoginPassword(this)'" : "";
	
	// Start Clean 
	// NOTE: this should NOT be a template of the admin-template, however themes may style it using css. 
	
		$class = (e_QUERY == 'failed') ? "class='e-shake'" : "";
			
		$text = "<form id='admin-login' method='post' action='".e_SELF."' {$incChap} >
		<div id='login-admin' >
		<div {$class}>
		<h2>".e_DOMAIN." admin area</h2>
        
		    <div class='field'>
		    	<label for='username'>".ADLAN_89."</label> 
		    	<input class='tbox e-tip' type='text' required='required' name='authname' placeholder='".ADLAN_89."' id='username' size='30' value='' maxlength='".varset($pref['loginname_maxlength'], 30)."' />
		    	<div class='field-help'>Please enter your username or email</div>
		   	</div>			
		
		    <div class='field'>
		    	<label for='userpass'>".ADLAN_90."</label>
		    	<input class='tbox e-tip' type='password' required='required' name='authpass' placeholder='".ADLAN_90."' id='userpass' size='30' value='' maxlength='30' />
		    	<div class='field-help'>Password is required</div>
		    </div>";
		
		if ($use_imagecode)
		{
			$text .= "
			<div class='field'>
				<label for='code_verify'>".ADLAN_152."</label>"
				.$sec_img->renderImage().
				$sec_img->renderInput()."	
			</div>";
		}
			    
		    $text .= "<div class='admin-submit'>"
		       	.$frm->admin_button('authsubmit',ADLAN_91);				
				
			if (e107::getSession()->is('challenge') && varset($pref['password_CHAP'], 0))
			{
				$text .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='".e107::getSession()->get('challenge')."' />\n\n";		
			}
								
		$text .= "</div>
		</div>
		</div>
		</form>";
		    
		e107::getRender()->tablerender(ADLAN_92, $text, 'admin-login');
		echo "<div class='center' style='margin-top:30%; color:silver'><span style='padding:0 40px 0 40px;'><a href='http://e107.org'>Powered by e107</a></span> <a href='".e_BASE."index.php'>Return to Website</a></div>";
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
		$user_info 	= e107::getUserSession();
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
			$session = e107::getSession();
			if (($authresponse && $session->is('challenge')) && ($authresponse != $session->get('challenge')))
			{ // Verify using CHAP (can't handle login by email address - only loginname - although with this code it does still work if the password is stored unsalted)
				if (($pass_result = $user_info->CheckCHAP($session->get('challenge'), $authresponse, $authname, $row['user_password'])) !== PASSWORD_INVALID)
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
