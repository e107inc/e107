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
	// public $override = true;

	function __construct()
	{
		
	}

/*
	function sc_news_summary()
	{
		$sc = e107::getScBatch('news');
		$data = $sc->getScVar('news_item');

		return "<span class='label label-danger'>".e107::getParser()->toHTML($data['news_summary'],'BODY')."</span>";
	}*/


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

		$placement = e107::pref('theme', 'usernav_placement', 'top');

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
				<p>{LM_USERNAME_INPUT: idprefix=bs3-}</p>
				<p>{LM_PASSWORD_INPUT: idprefix=bs3-}</p>


				<div class="form-group"></div>
				{LM_IMAGECODE_NUMBER}
				{LM_IMAGECODE_BOX}
				
				<div class="checkbox">
				
				<label class="string optional" for="bs3-autologin"><input style="margin-right: 10px;" type="checkbox" name="autologin" id="bs3-autologin" value="1">
				'.LAN_LOGINMENU_6.'</label>
				</div>
				<input class="btn btn-primary btn-block" type="submit" name="userlogin" id="bs3-userlogin" value="'.LAN_LOGINMENU_51.'">
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

		$userNameLabel = !empty($parm['username']) ? USERNAME : '';

		$text = '
		
		<ul class="nav navbar-nav navbar-right'.$direction.'">';
		
		if( e107::isInstalled('pm') )
		{
			$text .= '<li class="dropdown">{PM_NAV}</li>';
		}
		
		$text .= '
		<li class="dropdown dropdown-avatar"><a href="#" class="dropdown-toggle" data-toggle="dropdown">{SETIMAGE: w=30} {USER_AVATAR: shape=circle} '. $userNameLabel.' <b class="caret"></b></a>
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
	

	/*
	 * @example shortcode to render news.
	 */
	function sc_bootstrap_news_example($parm=null)
	{
		$news   = e107::getObject('e_news_tree');  // get news class.
		$sc     = e107::getScBatch('news'); // get news shortcodes.
		$tp     = e107::getParser(); // get parser.

		$newsCategory = 1; // null, number or array(1,3,4);

		$opts = array(
			'db_order'  =>'n.news_sticky DESC, n.news_datestamp DESC', //default is n.news_datestamp DESC
			'db_where'  => "FIND_IN_SET(0, n.news_render_type)", // optional
			'db_limit'  => '6', // default is 10
		);

		// load active news items. ie. the correct userclass, start/end time etc.
		$data = $news->loadJoinActive($newsCategory, false, $opts)->toArray();  // false to utilize the built-in cache.
		$TEMPLATE = "{NEWS_TITLE} : {NEWS_CATEGORY_NAME}<br />";

		$text = '';

		foreach($data as $row)
		{

			$sc->setScVar('news_item', $row); // send $row values to shortcodes.
			$text .= $tp->parseTemplate($TEMPLATE, true, $sc); // parse news shortcodes.
		}

		return $text;


	}


	/**
	 * Mega-Menu Shortcode Example.
	 * @usage Select "bootstrap_megamenu_example" in Admin > Sitelinks > Create/Edit > Function
	 * @notes Changing the method name will require changing .theme-sc-bootstrap-megamenu-example in style.css
	 * @param null $data Link data.
	 * @return string
	 */
	function sc_bootstrap_megamenu_example($data)
	{
		// include a plugin, custom code, whatever you wish.

		// return print_a($data,true);

		$parm= array();
		$parm['caption']        = '';
		$parm['titleLimit']     = 25; //    number of chars fo news title
		$parm['summaryLimit']   = 50; //   number of chars for new summary
		$parm['source']         = 'latest'; //      latest (latest news items) | sticky (news items) | template (assigned to news-grid layout)
		$parm['order']          = 'DESC'; //       n.news_datestamp DESC
		$parm['limit']          = '6'; //     10
		$parm['layout']         = 'media-list'; //    default | or any key as defined in news_grid_template.php
		$parm['featured']       = 0;


		return "<div class='container'>". e107::getObject('news')->render_newsgrid($parm) ."</div>";


	}





	
}





?>
