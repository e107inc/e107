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
	
	function sc_bootstrap_usernav()
	{
		include_lan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");
		
		$tp = e107::getParser();		   
		   
		if(!USERID) // Logged Out. 
		{		
			$text = '
			<ul class="nav pull-right">';
			
			if(deftrue('USER_REGISTRATION'))
			{
				$text .= '
				<li><a href="'.e_SIGNUP.'">Sign Up</a></li>
				';
			}
			
			$text .= '
			<li class="divider-vertical"></li>
			<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">Sign In <strong class="caret"></strong></a>
				<div class="dropdown-menu" style="padding: 15px; padding-bottom: 0px;">
				<form method="post" onsubmit="hashLoginPassword(this)" action="'.e_REQUEST_HTTP.'" accept-charset="UTF-8">
				{LM_USERNAME_INPUT}
				{LM_PASSWORD_INPUT}
				<input style="float: left; margin-right: 10px;" type="checkbox" name="autologin" id="autologin" value="1">
				<label class="string optional" for="autologin"> Remember me</label>
				<input class="btn btn-primary btn-block" type="submit" name="userlogin" id="userlogin" value="Sign In">
			';
			
			$text .= '
			<a href="{LM_FPW_LINK=href}" class="btn btn-small btn-block">'.LOGIN_MENU_L4.'</a>
			<a href="{LM_RESEND_LINK=href}" class="btn btn-small btn-block">'.LOGIN_MENU_L40.'</a>
			';
			
			
			/*
			$text .= '
				<label style="text-align:center;margin-top:5px">or</label>
				<input class="btn btn-primary btn-block" type="button" id="sign-in-google" value="Sign In with Google">
				<input class="btn btn-primary btn-block" type="button" id="sign-in-twitter" value="Sign In with Twitter">
			';
			*/
			
			$text .= "
			</form>
			</div>
			
			</li>
			
			
			
			</ul>";	
			
			
			require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");
			return $tp->parseTemplate($text, false, $login_menu_shortcodes);
		}  

		
		// Logged in. 
		//TODO Generic LANS. (not theme LANs) 
		
		$text = '
		
		<ul class="nav pull-right">
		<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">Logged in as '.USERNAME.' <b class="caret"></b></a>
		<ul class="dropdown-menu">
		<li><a href="'.e_HTTP.'usersettings.php"><i class="icon-cog"></i> Settings</a></li>
		<li><a class="dropdown-toggle no-block" role="button" href="'.e_HTTP.'user.php?id.'.USERID.'"><i class="icon-user"></i> Profile</a></li>
		<li class="divider"></li>';
		
		if(ADMIN) 
		{
			$text .= '<li><a href="'.e_ADMIN_ABS.'"><i class="icon-cogs"></i> Admin Area</a></li>';	
		}
		
		$text .= '
		<li><a href="'.e_HTTP.'index.php?logout"><i class="icon-off"></i> Logout</a></li>
		</ul>
		</li>
		</ul>
		
		';

		return $text;
	}	
	
	
	
}





?>