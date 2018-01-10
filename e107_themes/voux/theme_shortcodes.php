<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Bootstrap Theme Shortcodes. 
 *
*/


class theme_shortcodes extends e_shortcode
{
	function __construct()
	{
		
	}



	function sc_voux_newsletter_form()
	{
		$pref = e107::pref('core');

		if(empty($pref['signup_option_class']))
		{
			return false;
		}

		$frm = e107::getForm();
		$text = $frm->open('newsletter','post', e_SIGNUP, array('class'=>'form-inline'));
		$text .= "<div class='input-inline'>";
		$text .= $frm->text('email','', null, array('placeholder'=>"Enter your email"));
		$text .= $frm->button('subscribe', 1, 'primary', "Subscribe");
		$text .= "</div>";
		$text .= $frm->close();

		return $text;
	}





	function sc_bootstrap_branding()
	{
		$pref = e107::pref('theme', 'branding');

		switch($pref)
		{
			case 'logo':

				return e107::getParser()->parseTemplate('{SITELOGO: h=30}',true);

			break;

			case 'sitenamelogo':

				return "<span class='pull-left'>".e107::getParser()->parseTemplate('{SITELOGO: h=30}',true)."</span>".SITENAME;

			break;

			case 'sitename':
			default:

				return SITENAME;

			break;
		}

	}



	function sc_bootstrap_nav_align()
	{
		$pref = e107::pref('theme', 'nav_alignment');

		if($pref == 'right')
		{
			return "navbar-right";
		}
		else
		{
			return "";
		}
	}



	function sc_bootstrap_usernav($parm='')
	{

		$placement = e107::pref('theme', 'usernav_placement', 'bottom');

		if($parm['placement'] != $placement)
		{
			return '';
		}

		e107::includeLan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");
		
		$tp = e107::getParser();		   
		require(e_PLUGIN."login_menu/login_menu_shortcodes.php"); // don't use 'require_once'.

		$direction = vartrue($parm['dir']) == 'up' ? ' dropup' : '';
		
		$userReg = defset('USER_REGISTRATION');
				   
		if(!USERID) // Logged Out. 
		{		
			$text = '
			<ul class="nav navbar-nav navbar-right'.$direction.'">';
			
			if($userReg==1)
			{
				$text .= '
				<li><a href="'.e_SIGNUP.'">'.LAN_LOGINMENU_3.'</a></li>
				'; // Signup
			}


			$socialActive = e107::pref('core', 'social_login_active');

			if(!empty($userReg) || !empty($socialActive)) // e107 or social login is active.
			{
				$text .= '
				<li class="divider-vertical"></li>
				<li class="dropdown">
			
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">'.LAN_LOGINMENU_51.' <strong class="caret"></strong></a>
				<div class="dropdown-menu col-sm-12" style="min-width:250px; padding: 15px; padding-bottom: 0px;">
				
				{SOCIAL_LOGIN: size=2x&label=1}
				'; // Sign In
			}
			else
			{
				return '';
			}
			
			
			if(!empty($userReg)) // value of 1 or 2 = login okay. 
			{

			//	global $sc_style; // never use global - will impact signup/usersettings pages. 
			//	$sc_style = array(); // remove any wrappers.

				$text .='	
				
				<form method="post" onsubmit="hashLoginPassword(this);return true" action="'.e_REQUEST_HTTP.'" accept-charset="UTF-8">
				<p>{LM_USERNAME_INPUT}</p>
				<p>{LM_PASSWORD_INPUT}</p>


				<div class="form-group"></div>
				{LM_IMAGECODE_NUMBER}
				{LM_IMAGECODE_BOX}
				
				<div class="checkbox">
				
				<label class="string optional" for="autologin"><input style="margin-right: 10px;" type="checkbox" name="autologin" id="autologin" value="1">
				'.LAN_LOGINMENU_6.'</label>
				</div>
				<input class="btn btn-primary btn-block" type="submit" name="userlogin" id="userlogin" value="'.LAN_LOGINMENU_51.'">
				';
				
				$text .= '
				
				<a href="{LM_FPW_LINK=href}" class="btn btn-default btn-secondary btn-sm  btn-block">'.LAN_LOGINMENU_4.'</a>
				<a href="{LM_RESEND_LINK=href}" class="btn btn-default btn-secondary btn-sm  btn-block">'.LAN_LOGINMENU_40.'</a>
				';
				
				
				/*
				$text .= '
					<label style="text-align:center;margin-top:5px">or</label>
					<input class="btn btn-primary btn-block" type="button" id="sign-in-google" value="Sign In with Google">
					<input class="btn btn-primary btn-block" type="button" id="sign-in-twitter" value="Sign In with Twitter">
				';
				*/
				
				$text .= "<p></p>
				</form>
				</div>
				
				</li>
				";
			
			}

			$text .= "
			
			
			</ul>";	
			
			
			
			return $tp->parseTemplate($text, true, $login_menu_shortcodes);
		}  

		
		// Logged in. 
		//TODO Generic LANS. (not theme LANs) 	



		if($placement == 'bottom')
		{
			$text = '
			<ul class="nav navbar-nav navbar-right'.$direction.'">
			<li class="dropdown"><a href="#" class="voux-nav-avatar dropdown-toggle" data-toggle="dropdown">{SETIMAGE: w=30&h=30} {USER_AVATAR: shape=circle} '.USERNAME.' <b class="caret"></b></a>';
		}
		else
		{

			$text = '
			<ul class="nav navbar-nav navbar-right'.$direction.'">
			<li class="dropdown"><a href="#" class="voux-nav-avatar dropdown-toggle" data-toggle="dropdown">{SETIMAGE: w=20&h=20} {USER_AVATAR: shape=circle} <b class="caret"></b></a>';

		}


		$text .= '
		<ul class="dropdown-menu">
		<li>
			<a href="{LM_USERSETTINGS_HREF}"><span class="glyphicon glyphicon-cog"></span> '.LAN_SETTINGS.'</a>
		</li>
		<li>
			<a class="dropdown-toggle no-block" role="button" href="{LM_PROFILE_HREF}"><span class="glyphicon glyphicon-user"></span> '.LAN_LOGINMENU_13.'</a>
		</li>
		<li class="divider"></li>';
		
		if(ADMIN) 
		{
			$text .= '<li><a href="'.e_ADMIN_ABS.'"><span class="fa fa-cogs"></span> '.LAN_LOGINMENU_11.'</a></li>';	
		}
		
		$text .= '
		<li><a href="'.e_HTTP.'index.php?logout"><span class="glyphicon glyphicon-off"></span> '.LAN_LOGOUT.'</a></li>
		</ul>
		</li>
		</ul>
		
		';


		return $tp->parseTemplate($text,true,$login_menu_shortcodes);
	}	
	
	
	
}





?>
