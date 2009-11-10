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

$html = '';

$fb_pref = e107::getPlugConfig('facebook')->getPref();

if (($fb_pref['Facebook_Api-Key'] != '') && ($fb_pref['Facebook_Secret-Key'] != ''))
{
	
	$html = '';
	
	if (USER)
	{
		
		if (USERID == get_id_from_uid(is_fb()))
		{
			
			if (Facebook_User_Is_Connected() === true)
			{
				
				///$html .= Render_Facebook_Profile();
				
				//$caption = 'Welcome, ' . Get_Facebook_Info ( 'name' );
				
				$html .= Render_Facebook_Friends_Table();
				
				$html .= Render_Connect_Invite_Friends();
				
				$caption = 'Friends';
				// $text = $tp->parseTemplate($html, true, $facebook_shortcodes);
				
				$ns->tablerender($caption, $html);
			
			}
		
		}
	
	}

}


?>