<?php

if (!defined('e107_INIT')) { exit; }

class facebook_shortcodes extends e_shortcode
{

	function sc_fb($parm='')
	{
		$fbPref = e107::getPlugPref('facebook');
		$perms = "email";
		
		if($parm == 'login')
		{
			$link = "https://www.facebook.com/dialog/oauth?client_id={$fbPref['appId']}
			&redirect_uri=".e_SELF."&scope={$perms}&response_type=token";

			return "<a href='{$link}'>Login with Facebook</a>";			
		}
		
		
	}

}
?>