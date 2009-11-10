<?php
//error_reporting(E_ALL);

if (!defined('e107_INIT'))
{
	exit;
}

include_once (e_PLUGIN.'facebook/facebook_function.php');

if (isset($_POST['fb_sig_in_canvas']))
{
	return;
}

/**
 * start the logic...
 *
 */

global $pref;

if (!vartrue($pref['user_reg']))
{
	if (ADMIN)
	{
		$ns->tablerender("Facebook", "User Registration is turned off.");
	}
	return;
}

$fb_pref = e107::getPlugConfig('facebook')->getPref();

if (vartrue($fb_pref['Facebook_Api-Key']) && vartrue($fb_pref['Facebook_Secret-Key']))
{
	
	if (USER)
	{
		
		if (USERID == get_id_from_uid(is_fb()))
		{
			
			if (Facebook_User_Is_Connected() === true)
			{
				
				$html .= Render_Facebook_Profile();
				
				$html .= Render_Connect_Invite_Friends();
			
			}
			else
			{
				
				$html .= uid_check();
			
			}
		
		}
		else
		{
			if (is_fb() && uid_exists() && (single_uid() == 1))
			{
				
				Add_Facebook_Connect_User('', USERID);
				
				header('Location:'.e_SELF);

			
			}
			else if (is_fb() && (USERID != get_id_from_uid(is_fb())))
			{
				
				//return Facebook_LogOut();
				
				$html .= uid_check();
			
			}
		
		}
		if ((Get_Connection_Status() == '') && (Facebook_User_Is_Connected() === true))
		{
			
			$html .= uid_check();
		
		}
		else
		{
			
			$html .= Render_Facebook_Connect_Button();
		
		}
	
	}
	else
	{
		
		if (is_fb())
		{
			
			if (Get_Connection_Status() == '')
			{
				
				$html .= '<a href="#" onclick="facebook_onlogin_ready();"> 
    			<img id="fb_login_image" src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif" alt="Connect" /> 
    			</a>';
				
				// Fb_Connect_Me();
			
			}
			else if (Get_Connection_Status() == 1)
			{
				//not a real error!    just some problem with Facebook ID
				
				$html .= 'Ops... Some error Occur';
			}
			else if (Get_Connection_Status() == 0)
			{
				
				$html .= Render_Fcuk_Facebook_Connect_Button();
			
			}
		
		}
		
		$html .= Render_Facebook_Connect_Button();
	
	}

}

$caption = 'Facebook';
// $text = $tp->parseTemplate($html, true, $facebook_shortcodes);

$ns->tablerender($caption, $html);

?>