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
$core = e107::getConfig();
$adminTheme = $core->get('admintheme');
if($adminTheme !== 'bootstrap3'/* && $adminTheme !== 'bootstrap5'*/)
{
	$core->update('admintheme','bootstrap3');
	$core->update('adminstyle','infopanel');
	$core->update('admincss','css/modern-light.css');
	$core->set('e_jslib_core',array('prototype' => 'none', 'jquery'=> 'auto'));
	$core->save();	
	e107::getRedirect()->redirect(e_SELF);		
}

$admincss = trim($core->get('admincss'));
if(empty($admincss) || $admincss === 'style.css'|| $admincss === 'admin_dark.css' || $admincss === 'admin_light.css')
{
	$core->update('admincss','css/modern-light.css');
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
			e107::getMessage()->addInfo(e107::getParser()->lanVars(ADLAN_164, $lanVars).' <a href="'.e_ADMIN_ABS.'users.php?mode=main&amp;action=logoutas">['.LAN_LOGOUT.']</a>');
			
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
		e_jshelper::sendAjaxError(403, ADLAN_86, ADLAN_87);
	}
	
	require_once(e_ADMIN.'boot.php');

	$sec_img = e107::getSecureImg();

	$use_imagecode = (vartrue($pref['admincode']) && extension_loaded("gd"));

	// login check.
	if(!empty($_POST['authsubmit']))
	{
		if(e107::getUser()->login($_POST['authname'], $_POST['authpass'], false, varset($_POST['hashchallenge'])) !== false)
		{
			e107::getRedirect()->go('admin'); // successful login.
		}
		else
		{
			e107::coreLan('log_messages', true);
			e107::getLog()->addEvent(4, __FILE__."|".__FUNCTION__."@".__LINE__, "LOGIN", LAN_ROLL_LOG_11, "U: ".e107::getParser()->toDB($_POST['authname']), FALSE, LOG_TO_ROLLING);
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
			i.s-message-icon     { display: none }
			.s-message-title    { display: none } 
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
			
			#username           { background: url(".e_IMAGE."admin_images/admins_16.png) no-repeat scroll 7px 9px; padding:7px; padding-left:30px; width:80%; max-width:218px; }

			#userpass           { background: url(".e_IMAGE."admin_images/lock_16.png) no-repeat scroll 7px 9px; padding:7px;padding-left:30px; width:80%; max-width:218px; }

			#code-verify		{ width: 220px; padding: 7px; margin-left: auto; margin-right: auto; }

			input, input:focus, 
			input:hover         { color: rgb(238, 238, 238); background-color: #222222 !important }
			
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
	
		$text = "<form id='admin-login' method='post' action='".e_SELF."' {$incChap} >
		<div id='logo' ><img src='".e_IMAGE."logo_template_large.png' alt='".LAN_LOGIN."' /></div>
		<div id='login-admin' class='center'>
		<div>";

		e107::lan('core', 'login');
		$text .= e107::getMessage()->render(); // see e107_handlers/login.php L622
		$text .= "<script>
			window.setTimeout(function() {
		    $('.alert').fadeTo(500, 0).slideUp(500, function(){
		        $(this).remove();
		    });
		}, 7000);
		</script>";

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
						<div class='center' style='margin-top:25%; color:silver'><span style='padding:0 40px 0 0px;'><a target='_blank' href='https://e107.org'>".ADLAN_165."</a></span> <a href='".e_BASE."index.php'>".ADLAN_166."</a></div>
			</div>";
	}


}


