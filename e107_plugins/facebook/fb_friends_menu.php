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
	$fb = e107::getSingleton('e_facebook',e_PLUGIN.'facebook/facebook_function.php');
		
	$html = '';
	
	if (USER)
	{
		
		if (USERID == $fb->e107_userid)
		{
			
			if ($fb->isConnected() === true)
			{
				
				///$html .= Render_Facebook_Profile();
				
				//$caption = 'Welcome, ' . Get_Facebook_Info ( 'name' );
				
				$html .= $fb->Render_Facebook_Friends_Table();
				
				$html .= $fb->Render_Connect_Invite_Friends();
				
				$caption = 'Friends on this site';
				// $text = $tp->parseTemplate($html, true, $facebook_shortcodes);
				
				$ns->tablerender($caption, $html);
			
			}
		
		}
	
	}

}


?>