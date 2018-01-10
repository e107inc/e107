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
	var $override = true;
	
	function __construct()
	{

		if($img = $this->sc_videobackground('file'))
		{
			$inlinecss = 'header {     background-image: url('.$img.') }';
			e107::css("inline", $inlinecss);
		}

	}

	function sc_aboutmodal()
	{
	    $text =  e107::getParser()->parseTemplate('{CMENU=aboutmodal}');
		return $text; 
	}

	function sc_videobackground($parm=null)
	{

		if($this->isMobile() ) //|| !empty($_GET['configure'])
		{
			return null;
		}
                         
		/* first frame */ 
		if($videoposter = e107::pref('theme', 'videoposter', false))
		{
			$videoposter = e107::getParser()->thumbURL($videoposter);
		}
		else
		{
			$videoposter = SITEURLBASE.THEME_ABS."images/background01.jpg";
		}

		if($parm == 'file')
		{
			return $videoposter;
		}


		
		/* mp4 video url */

		if(!$videourl = e107::pref('theme', 'videourl', false))
		{
			$videourl = "https://s3-us-west-2.amazonaws.com/coverr/mp4/Traffic-blurred2.mp4";
		}

	    $text = '
        <video autoplay="" loop="" class="fillWidth fadeIn wow collapse in" data-wow-delay="0.5s" poster="'.$videoposter.'" id="video-background">
            <source src="'.$videourl.'" type="video/mp4">'.LAN_LZ_THEME_03.'
        </video>';
        
		return $text;
	}


	function isMobile()
	{
        return preg_match("/\b(?:a(?:ndroid|vantgo)|b(?:lackberry|olt|o?ost)|cricket|do‌​como|hiptop|i(?:emob‌​ile|p[ao]d)|kitkat|m‌​(?:ini|obi)|palm|(?:‌​i|smart|windows )phone|symbian|up\.(?:browser|link)|tablet(?: browser| pc)|(?:hp-|rim |sony )tablet|w(?:ebos|indows ce|os))/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	function sc_landing_toggle()
	{
		if($this->isMobile() || (e_ADMIN_AREA === true))
		{
			return null;
		}


		return '<hr><a href="#video-background" id="toggleVideo" data-toggle="collapse" class="btn btn-primary btn-xl">'.LAN_LZ_THEME_02.'</a>
								&nbsp; ';


	}



  function sc_cmenutext()
  {
    $sc   = e107::getScBatch('page', null, 'cpage');
    $data = $sc->getVars();
    return vartrue($data['menu_button_text'],'');
  }
 

	function sc_sitedisclaimer($copyYear = NULL)
	{
		$default = "Proudly powered by <a href='http://e107.org'>e107</a> which is released under the terms of the GNU GPL License.";
		$sitedisclaimer = deftrue('SITEDISCLAIMER',$default);
 
    $copyYear = vartrue($copyYear,'2013');
	  $curYear = date('Y'); 
	  $text = '&copy; '. $copyYear . (($copyYear != $curYear) ? ' - ' . $curYear : '');
 
	  $text .= ' '.$sitedisclaimer;        
		return e107::getParser()->toHtml($text, true, 'SUMMARY');	
	}


	//@todo Replace with social template.
	function sc_xurl_icons()
	{
		$social = array(
			'rss'             => array('href' => (e107::isInstalled('rss_menu') ? e107::url('rss_menu', 'index', array('rss_url' => 'news')) : ''), 'title' => 'RSS/Atom Feed'),
			'facebook'        => array('href' => deftrue('XURL_FACEBOOK'), 'title' => 'Facebook'),
			'twitter'         => array('href' => deftrue('XURL_TWITTER'), 'title' => 'Twitter'),
			'google'          => array('href' => deftrue('XURL_GOOGLE'), 'title' => 'Google Plus'),
			'linkedin'        => array('href' => deftrue('XURL_LINKEDIN'), 'title' => 'LinkedIn'),
			'github'          => array('href' => deftrue('XURL_GITHUB'), 'title' => 'Github'),
			'pinterest'       => array('href' => deftrue('XURL_PINTEREST'), 'title' => 'Pinterest'),
			'flickr'          => array('href' => deftrue('XURL_FLICKR'), 'title' => 'Flickr'),
			'instagram'       => array('href' => deftrue('XURL_INSTAGRAM'), 'title' => 'Instagram'),
			'youtube'         => array('href' => deftrue('XURL_YOUTUBE'), 'title' => 'YouTube'),
			'question-circle' => array('href' => deftrue('XURL_VIMEO'), 'title' => 'e107 HELP')
		);

		//Fixme - GooglePlus not working.

		$text = '';
		$textstart = '<ul class="list-inline lz-social-icons">';
		$textend = '</ul>';
		foreach($social as $id => $data)
		{
			if($data['href'] != '')
			{
				$text .= '
             <li><a rel="nofollow" target="_blank" href="' . $data['href'] . '" title="' . $data['title'] . '"><i class="icon-lg ion-social-' . $id . '-outline"></i></a>&nbsp;</li>';
				$text .= "\n";
			}
		}
		if($text != '')
		{
			return $textstart . $text . $textend;
		}
	}


  	function sc_lz_subscribe()
	{
		$pref = e107::pref('core');
		$ns = e107::getRender();

		if(empty($pref['signup_option_class']))
		{
			return false;
		}

		$frm = e107::getForm();
		$text = $frm->open('lz-subscribe','post', e_SIGNUP);
		$text .= "<div class='form-group'>";
		$text .= $frm->text('email','', null, array('placeholder'=>LAN_LZ_THEME_15, 'size'=>'xxlarge'));
		$text .= "</div>";
		$text .= "<div class='form-group'>";
		$text .= " ".$frm->button('subscribe', 1, 'submit', LAN_LZ_THEME_16, array('class'=>'btn-primary'));
		$text .= "</div>";
		$text .= $frm->close();

		$caption = LAN_LZ_THEME_17;

		return $ns->tablerender($caption,$text,'lz-subscribe', true);
	}




  function sc_lz_contactform()  //FIXME Use contact_template.php instead ie. $CONTACT_TEMPLATE['menu']
  {

	//

	$text = '       
                <div class="col-lg-8 col-lg-offset-2 text-center">
                    <h2 class="margin-top-0 wow fadeIn">Get in Touch</h2>
                    <hr class="primary">
                    <p>We love feedback. Fill out the form below and we\'ll get back to you as soon as possible.</p>
                </div>
                <div class="col-lg-10 col-lg-offset-1 text-center">
                    
                    <form class="contact-form row">
                        <div class="col-md-4">
                            <label></label>
                            <input type="text" class="form-control" placeholder="Name">
                        </div>
                        <div class="col-md-4">
                            <label></label>
                            <input type="text" class="form-control" placeholder="Email">
                        </div>
                        <div class="col-md-4">
                            <label></label>
                            <input type="text" class="form-control" placeholder="Phone">
                        </div>
                        <div class="col-md-12">
                            <label></label>
                            <textarea class="form-control" rows="9" placeholder="Your message here.."></textarea>
                        </div>
                        <div class="col-md-4 col-md-offset-4">
                            <label></label>
                            <button type="button" data-toggle="modal" data-target="#alertModal" class="btn btn-primary btn-block btn-lg">Send <i class="ion-android-arrow-forward"></i></button>
                        </div>
                    </form>
                </div>
';
        
    return $text;
	
	}

	function sc_contact_submit_button($parm='')
	{
		return "<input type='submit' name='send-contactus' value=\"".LANCONTACT_08."\" class='btn btn-primary btn-block btn-lg' />";	
	}
	
	function sc_bootstrap_usernav($parm='')
	{

		$placement = e107::pref('theme', 'usernav_placement', 'top');

		if($parm['placement'] != $placement)
		{
			return '';
		}

		include_lan(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");
		
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
		
		$text = '
		
		<ul class="nav navbar-nav navbar-right'.$direction.'">
		<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">{SETIMAGE: w=20&h=20&crop=1} {USER_AVATAR: shape=circle} <b class="caret"></b></a>
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
