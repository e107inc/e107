<?php
/**
* e107 website system
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

class social_comment
{
	private $facebookActive;




	function __construct()
	{
		$social = e107::pref('core','social_login');

		if(!empty($social))
		{
			$this->facebookActive = vartrue($social['Facebook']['keys']['id']);
		}

	}



	public function config() // Admin Area Configuration.
	{
		$engine = e107::pref('core','comments_engine','e107');

		if($engine == 'social::facebook' && empty($this->facebookActive))
		{
			e107::getMessage()->addInfo("Facebook comments requires that you have a facebook App ID. See the 'social login' area in admin-preferences to add one.");
		}

		$config = array();
		$config[] = array('name' => "Facebook", 'function'=>'facebook');


		return $config;
	}



	function facebook($data)
	{

		if(!deftrue('SOCIAL_FACEBOOK_INIT'))
		{
			return "<div class='alert alert-important alert-danger'>Unable to render comments. Missing Facebook appID.</div>";
		}

		e107::js('footer-inline', SOCIAL_FACEBOOK_INIT);

		if(E107_DEBUG_LEVEL > 0)
		{
			$link = "http://developers.facebook.com/docs/plugins/comments/";
		}
		else
		{
			$link = e_REQUEST_URL;
		}

		$pref       = e107::pref('social');
		$limit      = vartrue($pref['facebook_comments_limit'], 10);
		$theme      = vartrue($pref['facebook_comments_theme'], 'light');
		$loading    = vartrue($pref['facebook_comments_loadingtext'], 'Loading...');

		$text = '<div class="fb-comments" data-href="'.$link.'" data-width="100%" data-numposts="'.$limit.'" data-colorscheme="'.$theme.'">'.$loading.'</div>';

		return $text;
	}



}