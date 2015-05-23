<?php

if(USER_AREA)
{
	e107::css('social', 'css/fontello.css');

	$social = e107::pref('core','social_login');

	if(!empty($social) && is_array($social))
	{
		$appID = vartrue($social['Facebook']['keys']['id']);
	}

	if(!empty($appID))
	{
		e107::meta('fb:app_id', $appID);
	}

}


?>