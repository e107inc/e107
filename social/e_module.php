<?php

if(e_ADMIN_AREA !==true)
{
	e107::css('social', 'css/fontello.css');
	e107::css('social' ,'css/social.css');

	$appID = false;

	$social = e107::pref('core','social_login');

	if(!empty($social) && is_array($social))
	{
		if(!empty($social['Facebook']['keys']['id']))
		{
			$appID = $social['Facebook']['keys']['id'];
		}

	}

	$ogImage = e107::pref('social','og_image', false);
	if(!empty($ogImage))
	{
		e107::meta('og:image',e107::getParser()->thumbUrl($ogImage,'w=500',false,true));
		unset($ogImage);
	}



	if(!empty($appID))
	{
		e107::meta('fb:app_id', $appID);

		$locale = strtolower(CORE_LC)."_".strtoupper(CORE_LC2);

		$init = "

			window.fbAsyncInit = function() {
	            FB.init({
	            appId      : '".$appID."',
	            xfbml      : true,
	            version    : 'v2.3'
	            });
			};

			(function(d, s, id){
	            var js, fjs = d.getElementsByTagName(s)[0];
	            if (d.getElementById(id)) {return;}
	            js = d.createElement(s); js.id = id;
	            js.src = '//connect.facebook.net/".$locale."/sdk.js';
	            fjs.parentNode.insertBefore(js, fjs);
	        }(document, 'script', 'facebook-jssdk'));

	        ";

		define('SOCIAL_FACEBOOK_INIT', $init);

	}
	else
	{
		define('SOCIAL_FACEBOOK_INIT', false);
	}


}


?>