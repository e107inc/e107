<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
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


e107::getDebug()->logTime('(Start auth.php)');

define('e_CAPTCHA_FONTCOLOR','#F9A533');




// Required for a clean v1.x -> v2 upgrade. 
$core = e107::getConfig('core');

if($core->get('admintheme') != 'bootstrap3')
{
	$core->update('admintheme','bootstrap3');
	$core->update('adminstyle','infopanel');
	$core->update('admincss','admin_dark.css');
	$core->set('e_jslib_core',array('prototype' => 'none', 'jquery'=> 'auto'));
	$core->save();	
	e107::getRedirect()->redirect(e_SELF);		
}

$admincss = trim($core->get('admincss'));
if(empty($admincss) || $admincss === 'style.css'|| $admincss === 'admin_dark.css' || $admincss === 'admin_light.css')
{
	$core->update('admincss','css/bootstrap-dark.min.css');
	$core->save(false,true);
	e107::getRedirect()->redirect(e_SELF);
}

// Check Admin-Perms for current language and redirect if necessary. 
if(USER && !getperms('0') && vartrue($pref['multilanguage']) && !getperms(e_LANGUAGE) && empty($_E107['no_language_perm_check']))
{
	$lng = e107::getLanguage();

	$tmp = explode(".",ADMINPERMS);
	foreach($tmp as $ln)
	{
		if(strlen($ln) < 3) // not a language perm.
		{
			continue;
		}

		if($lng->isValid($ln))
		{
			$redirect = deftrue("MULTILANG_SUBDOMAIN") ? $lng->subdomainUrl($ln) : e_SELF."?elan=".$ln;
			//		echo "redirect to: ".$redirect;
			e107::getRedirect()->go($redirect);
		//	break;
		}	
	}
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
		{  
			$asuser = e107::getSystemUser(e107::getUser()->getSessionDataAs(), false);
			
			$lanVars = array ('x' => ($asuser->getId() ? $asuser->getName().' ('.$asuser->getValue('email').')' : 'unknown')) ;
			e107::getMessage()->addInfo($tp->lanVars(ADLAN_164, $lanVars).' <a href="'.e_ADMIN_ABS.'users.php?mode=main&amp;action=logoutas">['.LAN_LOGOUT.']</a>');
			
		}
		// NEW, legacy 3rd party code fix, header called inside the footer o.O
		if(deftrue('e_ADMIN_UI'))
		{
			// boot.php already loaded
			require_once (e_ADMIN."header.php");
		} 
		else 
		{
			// boot.php is included in admin dispatcher constructor, so do it only for legacy code
			require_once(e_ADMIN.'boot.php');
		}
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
	
	require_once(e_ADMIN.'boot.php');
	
	$sec_img = e107::getSecureImg();

	$use_imagecode = (vartrue($pref['admincode']) && extension_loaded("gd"));

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
		/** @var array $row */
		$row = $authresult = $obj->authcheck($_POST['authname'], $_POST['authpass'], varset($_POST['hashchallenge'], ''));

		if ($row[0] == "authfail")
		{
			$admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", LAN_ROLL_LOG_11, "U: ".$tp->toDB($_POST['authname']), FALSE, LOG_TO_ROLLING);
			echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";

			e107::getRedirect()->redirect('admin.php?failed');
			exit;
		}
		else
		{

			$reHashedPass = e107::getUserSession()->rehashPassword($row,$_POST['authpass']);
			if($reHashedPass !==false)
			{
				e107::getLog()->add('ADMINPW_02', '', E_LOG_INFORMATIVE, '', LOG_TO_ADMIN, $row);
				$row['user_password'] = $reHashedPass;
			}

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


			if (in_array(varset($pref['user_audit_class'], ''), $class_list))
			{
				e107::getAdminLog()->user_audit(USER_AUDIT_LOGIN, 'Login via admin page', $row['user_id'], $row['user_name']);
			}

			$edata_li = array("user_id"=>$row['user_id'], "user_name"=>$row['user_name'], 'class_list'=>implode(',', $class_list), 'user_admin'=> $row['user_admin']);
			
			// Fix - set cookie before login trigger
			$sessionLife = (int) e107::getPref('session_lifetime', ( 3600 * 24 * 30)); // default 1 month.

			if($sessionLife > 0)
			{
				$sessionLife = time() + $sessionLife;
			}

			session_set(e_COOKIE, $cookieval, $sessionLife);

			unset($sessionLife,$cookieval);
		
			// ---
			
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
		
			body 				{ 	text-align: left; font-size:15px; line-height:1.5em; font-weight:normal; 
									font-family:Arial, Helvetica, sans-serif; background-attachment: scroll; 
									/* background-color: rgb(47, 47, 47); color: rgb(198, 198, 198); */
									
									background-repeat: no-repeat; background-size: auto auto 
								}
			a					{ 	color:#F6931E; text-decoration:none; }
			a:hover				{ 	color:silver; text-decoration:none; }
			.bold				{ 	font-weight:bold; }
			.field				{ 	text-align:center;padding:5px }
			.field input		{	padding:5px; 
								
								}
			
			.field input:focus	{
									
								}
								
			.field input:hover	{
									
								}
			#logo				{
									height:140px;
									max-width:310px;
									padding-right:5px;
									margin-left:auto;
									margin-right:auto;
									margin-top:2%;
									width:95%;
									
								}
			
			#login-admin 		{
									margin-left:auto;
									margin-right:auto;
									margin-top:2%;
									min-width:250px;
									width:30%;
									padding: 0px;
									max-width:100%;
								
									/*	
									
									*/
								}

			#login-admin div.panel { padding: 0 }
			
			#login-admin label 	{ 	display: none; text-align: right	}
				
			
			.admin-submit 		{ 	text-align: center; 	padding-top:20px;	}
			
			.submit				{  }


		
			.placeholder 		{ color: #646667; font-style:italic	}
	
			::-webkit-input-placeholder { font-style:italic;	color: #bbb; 	}
		
			:-moz-placeholder 	{ font-style:italic;	color: #bbb; 		}
			
			h2					{ text-align: center; color: #FAAD3D;  }
			
			#username			{background: url(".e_IMAGE."admin_images/admins_16.png) no-repeat scroll 7px 9px; padding:7px; padding-left:30px; width:80%; max-width:218px; }

			#userpass			{background: url(".e_IMAGE."admin_images/lock_16.png) no-repeat scroll 7px 9px; padding:7px;padding-left:30px; width:80%; max-width:218px; }

			#code-verify		{ width: 220px; padding: 7px; margin-left: auto; margin-right: auto; }

			input[disabled] 	{ color: silver;	}
			button[disabled] span	{	color: silver;	}
			.title_clean		{ display:none; }

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
		global $use_imagecode,$sec_img;

		$pref = e107::getPref();
		$frm = e107::getForm();

		$incChap = (vartrue($pref['password_CHAP'], 0)) ? " onsubmit='hashLoginPassword(this)'" : "";
	
	// Start Clean 
	// NOTE: this should NOT be a template of the admin-template, however themes may style it using css. 
	
		$class = (e_QUERY == 'failed') ? "class='e-shake'" : "";



		$text = "<form id='admin-login' method='post' action='".e_SELF."' {$incChap} >
		<div id='logo' ><img src='".e_IMAGE."logo_template_large.png' alt='".LAN_LOGIN."' /></div>
		<div id='login-admin' class='center'>
		<div>";

		if(e_QUERY == 'failed')
		{
			e107::lan('core', 'login');
			$text .= "<div class='alert alert-danger'>".LAN_LOGIN_21."</div>";
			$text .= "<script type='text/javascript'>
				window.setTimeout(function() {
			    $('.alert').fadeTo(500, 0).slideUp(500, function(){
			        $(this).remove();
			    });
			}, 5000);
			</script>";

		}



		$text .= "
		<div class='panel well panel-primary'>
			<div class='panel-heading'><h3 class='panel-title'>".LAN_HEADER_04."</h3></div>

        <div class='panel-body'>
		    <div class='field'>
		    	<label for='username'>".ADLAN_89."</label> 
		    	<input class='tbox e-tip' type='text' autofocus required='required' name='authname' placeholder='".ADLAN_89."' id='username' size='30' value='' maxlength='".varset($pref['loginname_maxlength'], 30)."' />
		    	<div class='field-help' data-placement='right'>".LAN_ENTER_USRNAME_EMAIL."</div>
		   	</div>			
		
		    <div class='field'>
		    	<label for='userpass'>".ADLAN_90."</label>
		    	<input class='tbox e-tip'  type='password' required='required' name='authpass' placeholder='".ADLAN_90."' id='userpass' size='30' value='' maxlength='30' />
		    	<div class='field-help' data-placement='right'>".LAN_PWD_REQUIRED."</div>
		    </div>";
		
		if ($use_imagecode)
		{
			$text .= "
			<div class='field'>
				<label for='code-verify'>".LAN_ENTER_CODE."</label>"
				.$sec_img->renderImage().
				$sec_img->renderInput()."	
			</div>";
		}
			    
		    $text .= "<div class='admin-submit'>"
		       	.$frm->admin_button('authsubmit',ADLAN_91,'login');				
				
			if (e107::getSession()->is('challenge') && varset($pref['password_CHAP'], 0))
			{
				$text .= "<input type='hidden' name='hashchallenge' id='hashchallenge' value='".e107::getSession()->get('challenge')."' />\n\n";		
			}
								
		$text .= "</div></div>
		</div>
		</div>
		 </div>
		</form>";
		    
		e107::getRender()->tablerender("", $text, 'admin-login');
		echo "<div class='row-fluid'>
						<div class='center' style='margin-top:25%; color:silver'><span style='padding:0 40px 0 0px;'><a target='_blank' href='http://e107.org'>".ADLAN_165."</a></span> <a href='".e_BASE."index.php'>".ADLAN_166."</a></div>
			</div>";
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
		$pref 		= e107::getPref();
		$tp 		= e107::getParser();
		$sql_auth 	= e107::getDb('sql_auth');
		$user_info 	= e107::getUserSession();
		$reason 	= '';

		$authname = $tp->toDB(preg_replace("/\sOR\s|\=|\#/", "", trim($authname)));
		$authpass = trim($authpass);

		if ((($authpass == '') && ($authresponse == '')) || ($authname == ''))
			$reason = 'np';
		if (strlen($authname) > varset($pref['loginname_maxlength'], 30))
			$reason = 'lu';

		if (!$reason)
		{
			if ($sql_auth->select("user", "*", "user_loginname='{$authname}' AND user_admin = 1 "))
			{
				$row = $sql_auth->fetch();
			}
			elseif ($sql_auth->select("user", "*", "(user_name='{$authname}' OR user_email='{$authname}' ) AND user_admin=1 "))
			{
				$row = $sql_auth->fetch();
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
			if (($authresponse && $session->is('prevchallenge')) && ($authresponse != $session->get('prevchallenge')))
			{ // Verify using CHAP (can't handle login by email address - only loginname - although with this code it does still work if the password is stored unsalted)
				/*
				$title = 'Login via admin';
				$extra_text = 'C: '.$session->get('challenge').' PC: '.$session->get('prevchallenge').' PPC: '.$session->get('prevprevchallenge').' R:'.$authresponse.' P:'.$row['user_password'];
				$text = 'CHAP: '.$username.' ('.$extra_text.')';
				$title = e107::getParser()->toDB($title);
				$text  = e107::getParser()->toDB($text);
				e107::getAdminLog()->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", $title, $text, FALSE, LOG_TO_ROLLING);

				$logfp = fopen(e_LOG.'authlog.txt', 'a+'); fwrite($logfp, $title.': '.$text."\n"); fclose($logfp);
				*/
				
				if (($pass_result = $user_info->CheckCHAP($session->get('prevchallenge'), $authresponse, $authname, $row['user_password'])) !== PASSWORD_INVALID)
				{
					return $row;
				}
			}
			else
			{ // Plaintext password
				/*
				$title = 'Login via admin';
				$extra_text = 'C: '.$session->get('challenge').' PC: '.$session->get('prevchallenge').' PPC: '.$session->get('prevprevchallenge').' R:'.$authresponse.' P:'.$row['user_password'];
				$text = 'STD: '.$username.' ('.$extra_text.')';
				$title = e107::getParser()->toDB($title);
				$text  = e107::getParser()->toDB($text);
				e107::getAdminLog()->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", $title, $text, FALSE, LOG_TO_ROLLING);

//				$logfp = fopen(e_LOG.'authlog.txt', 'a+'); fwrite($logfp, $title.': '.$text."\n"); fclose($logfp);
				*/

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
