<?php
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     Copyright (c) e107 Inc. 2001-2009
 |     http://e107.org
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_plugins/facebook/facebook_function.php,v $
 |     $Revision: 1.5 $
 |     $Date: 2009-11-10 13:41:41 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */

if (!defined('e107_INIT'))
{
	exit;
}

require_once (e_PLUGIN.'facebook/facebook-client/facebook.php');

define("FB_DEBUG",TRUE); // Debug this script. 

/**
 * simple get the Facebook  User ID one is logged in!
 *
 */
function is_fb()
{
	try
	{
		$fbclient = &facebook_client();
		if ($fbclient)
			return $fbclient->get_loggedin_user();
	}
	catch (FacebookRestClientException $e)
	{
		//echo "Facebook connect error:".$e->getCode();
	}
	return null;
}

/**
 * Add all infos in the facebook table
 *
 * if is specified 'full' , retrieve all Facebook infos otherwise just
 * Standard infos: http://wiki.developers.facebook.com/index.php/Users.getStandardInfo
 *
 */

function Add_Facebook_Connect_User($info = '', $user_id)
{
	$sql = e107::getDb();
	
	/*   $full = array (
	 'facebook_about_me' => Get_Facebook_Info ( 'about_me' ) ,
	 'facebook_activities' => Get_Facebook_Info ( 'activities' ) ,
	 'facebook_birthday_date' => Get_Facebook_Info ( 'birthday_date' ) ,
	 'facebook_books' => Get_Facebook_Info ( 'books' ) ,
	 'facebook_education_history' => Get_Facebook_Info ( 'education_history' ) ,
	 'facebook_hometown_location' => Get_Facebook_Info ( 'hometown_location' ) ,
	 'facebook_hs_info' => Get_Facebook_Info ( 'hs_info' ) ,
	 'facebook_interests' => Get_Facebook_Info ( 'interests' ) ,
	 'facebook_is_app_user' => Get_Facebook_Info ( 'is_app_user' ) ,
	 'facebook_is_blocked' => Get_Facebook_Info ( 'is_blocked' ) ,
	 'facebook_meeting_for' => Get_Facebook_Info ( 'meeting_for' ) ,
	 'facebook_meeting_sex' => Get_Facebook_Info ( 'meeting_sex' ) ,
	 'facebook_movies' => Get_Facebook_Info ( 'movies' ) ,
	 'facebook_music' => Get_Facebook_Info ( 'music' ) ,
	 'facebook_notes_count' => Get_Facebook_Info ( 'notes_count' ) ,
	 'facebook_pic' => Get_Facebook_Info ( 'pic' ) ,
	 'facebook_pic_with_logo' => Get_Facebook_Info ( 'pic_with_logo' ) ,
	 'facebook_pic_big' => Get_Facebook_Info ( 'pic_big' ) ,
	 'facebook_pic_big_with_logo' => Get_Facebook_Info ( 'pic_big_with_logo' ) ,
	 'facebook_pic_small' => Get_Facebook_Info ( 'pic_small' ) ,
	 'facebook_pic_small_with_logo' => Get_Facebook_Info ( 'pic_small_with_logo' ) ,
	 'facebook_pic_square' => Get_Facebook_Info ( 'pic_square' ) ,
	 'facebook_pic_square_with_logo' => Get_Facebook_Info ( 'pic_square_with_logo' ) ,
	 'facebook_political' => Get_Facebook_Info ( 'political' ) ,
	 'facebook_profile_blurb' => Get_Facebook_Info ( 'profile_blurb' ) ,
	 'facebook_profile_update_time' => Get_Facebook_Info ( 'profile_update_time' ) ,
	 'facebook_quotes' => Get_Facebook_Info ( 'quotes' ) ,
	 'facebook_relationship_status' => Get_Facebook_Info ( 'relationship_status' ) ,
	 'facebook_significant_other_id' => Get_Facebook_Info ( 'significant_other_id' ) ,
	 'facebook_tv' => Get_Facebook_Info ( 'tv' ) ,
	 'facebook_wall_count' => Get_Facebook_Info ( 'wall_count' ) ,
	 'facebook_website' => Get_Facebook_Info ( 'website' ) ,
	 'facebook_religion' => Get_Facebook_Info ( 'religion' ) ,
	 'facebook_work_history' => Get_Facebook_Info ( 'work_history' )
	 ) ; */
	
	$standard = array('facebook_connected'=>'1', 'facebook_uid'=>is_fb(), 'facebook_user_id'=>$user_id, 'facebook_last_name'=>Get_Facebook_Info('last_name'), 'facebook_username'=>Get_Facebook_Info('username'), 'facebook_name'=>Get_Facebook_Info('name'),
		//'facebook_affiliations' => Get_Facebook_Info ( 'affiliations' ) ,
		'facebook_sex'=>Get_Facebook_Info('sex'), 'facebook_timezone'=>Get_Facebook_Info('timezone'), 'facebook_birthday'=>Get_Facebook_Info('birthday'), 'facebook_profile_url'=>Get_Facebook_Info('profile_url'),
		//'facebook_proxied_email'  =>  Get_Facebook_Info (  'proxied_emai' ) ,
		'facebook_email_hashes'=>Get_Facebook_Info('email_hashes'), 'facebook_first_name'=>Get_Facebook_Info('first_name'), 'facebook_current_location'=>Get_Facebook_Info('current_location'), 'facebook_locale'=>Get_Facebook_Info('locale'));
	
	$query = $standard;
	
	if (trim($info) == 'full')
	{
		
		$query = array_push($standard, $full);
	
	}
	
	$sql->db_Insert('facebook', $query);

}

/**
 * Function for rendering the Facebook Profile once logged in!
 *
 */

function Render_Facebook_Profile()
{
	if (is_fb())
	{
		
		$html .= '<div class="welcome_msg">';
		$html .= 'Welcome, '.Get_Facebook_Info('name');
		$html .= '</div>';
		$html .= '<div class="user_image">';
		$html .= '<span class="fbimg">';
		$html .= getProfilePic(is_fb(), true);
		$html .= '</span>';
		$html .= '</div>';
		
		//check for User Permission
		
		if (Has_App_Permission('publish_stream') == 0)
		{
			
			$html .= '<div class="facebook_notice">';
			$html .= '<fb:prompt-permission perms="read_stream,publish_stream">';
			$html .= 'Would you like our application to read from and post to your News Feed?';
			$html .= '</fb:prompt-permission>';
			$html .= '</div>';
		
		}
		
		/*     
		 if ( ADMIN )
		 {
		 $html .= '<a href="' . e_ADMIN . 'admin.php">&rarr; Admin Area</a><br />';
		 }
		 $html .= '<br /><a href="' . e_BASE . 'usersettings.php">&rarr; Settings</a><br />';
		 $html .= '<a href="' . e_BASE . 'user.php?id . ' . USERID . '">&rarr; Profile</a><br />';
		 
		 */

		$html .= '<div class="facebook_link">';
		$html .= '<a href="#" onclick="FB.Connect.logout ( function()  { refresh_page() ; } ) "> Logout </a>';
		$html .= '</div>';
		
		$html .= '<div class="facebook_link">';
		$html .= '<a href="'.e_PLUGIN.'facebook/facebook.php"> Invite Friends</a>';
		$html .= '</div>';
		
		return $html;
	}
}

/**
 * Get the facebook client object for easy access.
 *
 */

function facebook_client()
{
	$pref = e107::getPlugConfig('facebook')->getPref();
	
	static $facebook = null;
	
	if ($facebook === null)
	{
		$facebook = new Facebook($pref['Facebook_Api-Key'], $pref['Facebook_Secret-Key']);
		
		if (!$facebook)
		{
			//  Could not create facebook client!
		}
	
	}
	return $facebook;
}

/**
 *  since 2009 User Permission Required for Post Stream
 *
 * http://wiki.developers.facebook.com/index.php/Users.hasAppPermission
 *
 * $ext_perm = email, read_stream, publish_stream, offline_access, status_update, photo_upload, create_event, rsvp_event, sms, video_upload, create_note, share_item.
 *
 * return 0 OR 1
 */
function Has_App_Permission($ext_perm)
{
	
	if (is_fb())
	{
		
		$HasAppPermission = facebook_client()->api_client->users_hasAppPermission($ext_perm, is_fb());
		
		return $HasAppPermission;
	
	}

}

/**
 * Function for retrieve Facebook info by using his API
 *
 * more info: http://wiki.developers.facebook.com/index.php/User_ ( FQL )
 *
 */
function Get_Facebook_Info($info, $friend = '')
{
	
	if (is_fb())
	{
		
		$uid = is_fb();
		
		if ($friend != '')
		{
			
			$uid = $friend;
		
		}
		
		$info_data = facebook_client()->api_client->users_getInfo($uid, $info);
		
		if (! empty($info_data))
		{
			
			$data[$info] = $info_data[0][$info];
			
			if (is_array($data[$info]))
			{
				
				$data[$info] = implode(',', $info_data[0][$info]);
			
			}
			
			$text = $data[$info] ? $data[$info] : null;
		
		}
		
		return $text;
	}

}

/**
 * Add new e107 User , by using Facebook Infos
 *
 */
function Fb_Connect_Me()
{
	$sql = e107::getDb();

	if (!$sql->db_Select("facebook", "*", "facebook_uid = '".is_fb()."' "))
	{
		$nickname = username_exists(Get_Facebook_Info('first_name'));
		$password = md5(is_fb());
		$username = "FacebookUser_".is_fb();
		
		$nid = $sql->db_Insert('user', array('user_name'=>$nickname, 'user_loginname'=>$username, 'user_password'=>$password, 'user_login'=>Get_Facebook_Info('name'), 'user_image'=>Get_Facebook_Info('pic')));
		
		Add_Facebook_Connect_User('', $nid);		
		set_cookies($nid, md5($password));

		fb_redirect(e_SELF);
	}
}

function UEID()
{
	
	$sql = e107::getDb();
	
	$sql->db_Select("facebook", "facebook_uid", "facebook_user_id = ".USERID." LIMIT 1 ");
	
	$row = $sql->db_Fetch();
	
	return $row['facebook_uid'];

}
/**
 * When logging out e107 , simulate a Log-Out from Facebook instead of expire Facebook session!
 *
 */
function Fb_LogOut()
{
	$sql = e107::getDb();
	
	//$uid = UEID() ? UEID() : is_fb();
	
	if ($sql->db_Select("facebook", "*", "facebook_connected = '1' AND facebook_user_id = '".USERID."' "))
	{
		$row = $sql->db_Fetch();
		extract($row);
		
		$sql2 = new db;
		$sql2->db_Update("facebook", "facebook_connected = '0' WHERE facebook_uid = '".$facebook_uid."' ");
		
		fb_redirect(e_SELF);
	}
}

/**
 * Re-Login in e107 without request new Facebook session!
 *
 */
function Fb_LogIn()
{
	$sql = new db;
	$sql2 = new db;
	if ($sql->db_Update("facebook", "facebook_connected = '1' WHERE facebook_uid = '".is_fb()."' "))
	{
		
		Log_In_Registered_User();
	
	}/*
	else if ($sql2->db_Select("user_extended", "*", "user_plugin_facebook_ID = '".is_fb()."' "))
	{
		$row2 = $sql2->db_Fetch();
		extract($row2);
		
		Add_Facebook_Connect_User('', $user_extended_id);
		
		Log_In_Registered_User();
	
	}*/
	else
	{
		
		Fb_Connect_Me();
	
	}
	
	fb_redirect(e_SELF);

}

/**
 * check for e107 connection status: 1 = logged In , 0 = logged Out
 *
 */

function your_facebook_is()
{
	$uid = is_fb();
	return ($uid) ? 'your facebook id is: <b>'.$uid.'</b>' : '';

}

function Get_Connection_Status()
{
	$sql = e107::getDb();
	
	if ($sql->db_Select("facebook", "facebook_connected", "facebook_uid = '".is_fb()."' ")) 
	{
		$row = $sql->db_Fetch();
		return $row['facebook_connected'] ? $row['facebook_connected'] : '0';
	
	}/*
	elseif($sql->db_Select("user_extended", "*", "user_plugin_facebook_ID = '".is_fb()."' "))
	{
		$row = $sql->db_Fetch();
		return $row['user_extended_id'] ? $row['user_extended_id'] : '0';	
	}*/
	else
	{
		return '';
	
	}
}

/**
 * Ensure e107 and Facebook Are well linked!
 *
 */
function Facebook_User_Is_Connected()
{
	$sql = e107::getDb();
	
	if ($sql->db_Select("facebook", "*", "facebook_user_id = '".get_id_from_uid(is_fb())."' AND facebook_uid = ".is_fb()." "))
	{
		return true;
	
	}
	else
	{		
		return false;	
	}
}

/**
 * Log out from Facebook by JS
 *
 */
function Facebook_LogOut()
{
	
	if (is_fb())
	{
		onloadRegister('facebook_log_out(); ');
		
		Fb_LogOut();
	}

}

/**
 *
 *
 */
function Delete_Duplicate_Facebook_User()
{
	$sql = e107::getDb();
	$id = get_id_from_uid(is_fb());
	
//	$sql->db_Update('user_extended', "user_plugin_facebook_ID = '' WHERE user_extended_id = ".$id." LIMIT 1");
//	$sql->db_Delete("user_extended", "user_extended_id='".$id."'"); 
	$sql->db_Delete("facebook", "facebook_uid='".is_fb()."'");
	$sql->db_Delete("user", "user_loginname='FacebookUser_".is_fb()."'");

}

function Switch_Facebook_User()
{
	$sql = e107::getDb();
			
	if (!$sql->db_Insert('facebook', array('facebook_user_id'=>USERID, 'user_plugin_facebook_ID'=>is_fb())))
	{
		$sql->db_Update("user_extended", "user_plugin_facebook_ID = '".is_fb()."' WHERE user_extended_id = '".USERID."' ");
	}
	
	$id = get_id_from_uid(is_fb());
	
	// $sql->db_Update("user_extended", "user_plugin_facebook_ID = '' WHERE user_extended_id = '".$id."' ");
	$sql->db_Update("facebook", "facebook_user_id = '".USERID."' WHERE facebook_uid = '".is_fb()."' ");
}

/**
 * check for Facebook presence and validation
 *
 */
function single_uid()
{	
	$sql = e107::getDb();
	
	$count = $sql->db_Count("facebook", "(*)", "WHERE facebook_uid = '".is_fb()."'");
	return $count;
}

function uid_check()
{
	$sql = e107::getDb();
	
	if (!$sql->db_Select("facebook", "*", "facebook_user_id = ".USERID."  "))
	{	
		return "<div class='facebook_notice'><a href='".e_SELF."?facebook_link' title='click here'>Click to Link your ".SITENAME." account with Facebook</a> </div>";
		//  <div class='fb_green'>".your_facebook_is()."</div>";	
	}
	else
	{
		return '<a href="#" onclick="facebook_onlogin_ready();"> 
    	<img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect" /> 
    	</a>';	
	}
	/*
	$msg = "";
	
	if ($sql->db_Select("facebook", "*", "facebook_uid = '".is_fb()."' AND facebook_user_id != ".USERID."  "))
	{
		// header ( 'Location: ' . e_SELF ) ;
		$msg .= "<div class='facebook_notice'><a href='".e_SELF."?facebook_switch' title='switch user'>would you like to use facebook with this account? press this link!</a></div>";
		$msg .= "<div class='facebook_notice'><a href='".e_SELF."?facebook_delete' title='delete user'>would you like to delete this facebook account? press this link!</a></div>";
	}
	else if($sql->db_Select("user_extended", "*", "user_plugin_facebook_ID != '".is_fb()."' AND user_plugin_facebook_ID != '' "))
	{
		$msg .= "<div class='facebook_notice'><a href='".e_SELF."?facebook_link' title='click here'>The provided Facebook ID is wrong!</a> </div> <div class='fb_green'>".your_facebook_is()."</div>";	
	}
	else if($sql->db_Select("user_extended", "*", "user_plugin_facebook_ID = '' "))
	{
		$msg .= "<div class='facebook_notice'><a href='usersettings.php' title='click here'>Specify your Facebook ID in the Profile Settings! </a></div><div class='fb_green'>".your_facebook_is()."</div>";
	}
	else
	{
		$msg .= '<a href="#" onclick="facebook_onlogin_ready();"> 
    	<img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect" /> 
    	</a>';	
	}
	
	return $msg;*/
}


function uid_exists()
{	
	$sql = e107::getDb();
	
	if ($sql->db_Select("facebook", "*", "facebook_user_id = ".USERID." AND facebook_uid = ".is_fb()." "))
	{	
		return USERID;	
		// $row = $sql->db_Fetch();		
		// return $row['user_extended_id'];	
	}
	else
	{		
		return null;	
	}
}

/**
 * simple display icon
 *
 */

function fb_icon()
{
	return " <img src=\"".e_PLUGIN."facebook/images/icon_16.png\" alt=\"\" /> ";
}

/**
 * get profile picture by requesting it to Facebook; but we can use also the infos stored in facebook table!
 *
 */

function getProfilePic($uid, $show_logo = false)
{
	return ($uid) ? ('<fb:profile-pic uid="'.$uid.'" size="square" '.($show_logo ? ' facebook-logo="true"' : '').'></fb:profile-pic>') : '<img src="http://static.ak.fbcdn.net/pics/q_default.gif" />';
}

/**
 * get USERID by knowing his Facebook ID
 *
 */

function get_id_from_uid($uid)
{
	$sql = e107::getDb();
	
	$sql->db_Select("facebook", "facebook_user_id", "facebook_uid = ".$uid);
	
	$row = $sql->db_Fetch();
	
	return $row['facebook_user_id'];

}

function render_facebook_init_js($uid)
{
	
	$sql = e107::getDb();
	$pref = e107::getPlugConfig('facebook')->getPref();
	
	$text .= '<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>
     <script type="text/javascript">
       FB_RequireFeatures ( [ "XFBML" ], function() {
       FB.init ( "'.$pref['Facebook_Api-Key'].'", "'.e_PLUGIN.'facebook/xd_receiver.php" ) ;
     } ) ;
     </script>
     <script src="'.e_PLUGIN.'facebook/facebook.js" type="text/javascript"></script>';
	
	$text .= onloadRegister(sprintf("facebook_onload(%s);", ($uid) ? "true" : "false"));
	
	return $text;
}

/**
 * Render a custom button to log in via Facebook.
 * When the button is clicked, the Facebook JS library pops up a Connect dialog
 * to authenticate the user.
 * If the user authenticates the application, the handler specified by the
 * onlogin attribute will be triggered.
 *
 * @param $size   size of the button. one of ( 'small', 'medium', 'large' )
 * http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif
 */
function Render_Facebook_Connect_Button($size = 'medium')
{
	
	if (!is_fb())
	{		
		/* return '<a href="#" onclick="FB.Connect.requireSession(); return false;" ><img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect"/></a>';*/
		
		return '<fb:login-button '.'size="'.$size.'" background="light" length="long" '.'onlogin="facebook_onlogin_ready();"></fb:login-button>';
	
	}
}

/**
 * Render pseudo Facebook button when USER logOut from e107
 *
 */

function Render_Fcuk_Facebook_Connect_Button()
{	
	return '  
    <div class="welcome_msg">Welcome '.Get_Facebook_Info('name').' !<br /> Click below to Login</div>     <br />
    
<a href="'.e_SELF.'?login">
 <img id="fb_login_image" src="'.e_PLUGIN_ABS.'facebook/images/facebooklogin.gif" alt="Click to Login"/></a>
';

}

function register_feed_form_js()
{
	onloadRegister("facebook_publish_feed_story('".getStreamToPublish()."');");
}

/*
 * Prevent caching of pages. When the Javascript needs to refresh a page,
 * it wants to actually refresh it, so we want to prevent the browser from
 * caching them.
 */
function prevent_cache_headers()
{
	header('Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
}

/*
 * Register a bit of javascript to be executed on page load.
 *
 * This is printed in render_footer() , so make sure to include that on all pages.
 */
function onloadRegister($js)
{
	global $onload_js;
	$onload_js .= $js;
}

/**
 * expire Facebook session = logOut
 * not used yet!
 */

function fb_expire_session()
{
	try
	{
		
		$fbclient = &facebook_client();
		if ($fbclient && $fbclient->get_loggedin_user() != "")
		{
			$fbclient->expire_session();
		}
	}
	catch (Exception $e)
	{
		//nothing, probably an expired session
	}
}

/**
 * Simple get the user message status while connected
 *
 */

function Get_Fecebook_Status($uid)
{
	return ($uid) ? '<fb:user-status uid="'.$uid.'" ></fb:user-status>' : '';
}

/**
 * Simple render invitation link
 *
 */

function Render_Invite_Friends_Link()
{
	return ' <a href="javascript;" onclick="FB.Connect.inviteConnectUsers();'.' return false;">&rarr; Invite them to Connect.</a> ';
}

function Render_Fun_Box($stream, $connections, $width)
{
	$pref = e107::getPlugConfig('facebook')->getPref();
	return '<fb:fan profile_id="'.$pref['Facebook_App-Bundle'].'" stream="'.$stream.'" connections="'.$connections.'" width="'.$width.'"></fb:fan><div style="font-size:8px; padding-left:10px"><a href="'.e_PLUGIN.'facebook">e107fbconnect</a> on Facebook</div>';
}

/**
 * Render all Facebook User Friends
 *
 */

function Render_Facebook_Friends_Table()
{
	
	$friends = facebook_client()->api_client->friends_get();
	
	$n = (count($friends) * 60 / 2);
	
	if ($n > 300)
	{
		
		$n = 300;
	
	}
	
	if (is_array($friends) && ! empty($friends))
	{
		
		$html .= '<div style="width:200px;height:'.$n.'px;overflow:auto" >';
		
		foreach ($friends as $friend)
		{
			
			if ($friend)
			{				
				$html .= '<div style="margin:0;width:50px;height:50px;padding-top:10px;padding-right:5px;float:left">'.getProfilePic($friend).'</div>';			
			}
		}
		
		$html .= '</div>';
		return $html;
	}
	else
	{
		return '';
	}
}

/**
 * Display list of Friends that are not Invited yet
 *
 */

function Render_Connect_Invite_Friends()
{
	//$friends = facebook_client()->api_client->friends_get() ;
	//$has_existing_friends = count (   $friends  ) ;
	//$more = $has_existing_friends?' more':'';
	$num = '<fb:unconnected-friends-count></fb:unconnected-friends-count>';
	
	if ($num > 0)
	{
		//$one_friend_text = 'You have one' . $more . ' Facebook friend that also join on ' . SITENAME;
		//$multiple_friends_text = 'You have ' . $num.$more . ' Facebook friends that also join on ' . SITENAME;
		//$invite_link = '<a onclick="FB.Connect.inviteConnectUsers() ; return false;">Invite them to Connect.</a>';
		
		//$html = '';
		$html .= '<fb:container class="HideUntilElementReady" condition="FB.XFBML.Operator.equals ( FB.XFBML.Context.singleton.get_unconnectedFriendsCount() , 1 ) " >';
		
		$html .= '</fb:container>';
		$html .= '<fb:container class="HideUntilElementReady" condition="FB.XFBML.Operator.greaterThan ( FB.XFBML.Context.singleton.get_unconnectedFriendsCount() , 1 ) " >';
		$html .= '<a onclick="FB.Connect.inviteConnectUsers() ; return false;">Invite them to Connect.</a>';
		$html .= '</fb:container>';
		return $html;
	
	}
}

/*
 * Make the API call to register the feed forms. This is a setup call that only
 * needs to be made once.
 *
 */
function register_feed_forms()
{
	$one_line_stories = $short_stories = $full_stories = array();
	
	$one_line_stories[] = '{*actor*} went for a {*distance*} run at {*location*} . ';
	
	$form_id = facebook_client()->api_client->feed_registerTemplateBundle($one_line_stories);
	return $form_id;
}

/**
 * Prevent duplicate user while adding new user using Facebook
 *
 */

function username_exists($user)
{
	$sql = e107::getDb();
	$sql = e107::getDb('sql2');
	if ($sql->db_Select("user", "user_loginname", "user_loginname = '$user' "))
	{
		$count = $sql2->db_Count("user", "(*)", "WHERE $name LIKE '$user%' ");
		$num = $count + 1;
		return username_exists($user.$num);
	
	}
	else
	{
		
		return $user;
	}

}

/**
 * Set Login cookies
 *
 */

function set_cookies($id, $pwd)
{
	$pref = e107::getConfig()->getPref();
	
	$cockiename = $pref['cookie_name'] ? $pref['cookie_name'] : 'e107cookie';
	$cookieval = $id.".".$pwd;
	
	if ($pref['user_tracking'] == 'session')
	{
		$_SESSION[$cockiename] = $cookieval;
	}
	else
	{
		cookie($cockiename, $cookieval, (time() + 3600 * 24 * 30));
	}
	
	return $_SESSION[$cockiename];
}

/**
 * just like get_user_info but for all e107 table
 * not used yet!
 */

function get_info($info, $uid)
{
	$sql = e107::getDb();
	
	if ($sql->db_Select("user", "*", "user_id = '$uid'"))
	{
		$row = $sql->db_Fetch();
		return $row[$info];
	}
	else
	{
		return false;
	}
}

function Log_In_Registered_User()
{
	if (!USER)
	{
		$uid = get_id_from_uid(is_fb());
		set_cookies(get_info('user_id', $uid), md5(get_info('user_password', $uid)));
		fb_redirect(e_SELF);

	}
}

/**
 * Simple get last Registered User added by Facebook
 *
 */

function last_user()
{
	$sql = new db;
	$qry = "SELECT
                user_id 
           FROM 
                #user
           WHERE   
                user_id = (SELECT MAX(user_id)   
           FROM 
                #user
                ) ";
	$sql->db_Select_gen($qry);
	$row = $sql->db_Fetch();
	return $row['user_id'];
}

function fb_redirect($loc)
{
	header('Location:'.$loc);
	// Stops endless loop issues. 
	header('Content-Length: 0');
	exit();
}
