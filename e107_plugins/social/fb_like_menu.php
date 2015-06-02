<?php
/**
* e107 website system
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

//@see https://developers.facebook.com/docs/plugins/like-button

if(deftrue('SOCIAL_FACEBOOK_INIT') )
{
	e107::js('footer-inline', SOCIAL_FACEBOOK_INIT); // defined in e_header.php

	$pref = e107::pref('social');

	$action = vartrue($pref['facebook_like_menu_action'],   'like'); // or 'recommend';
	$layout = vartrue($pref['facebook_like_menu_layout'],   'standard'); // standard, button_count, button or box_count.
	$width = vartrue($pref['facebook_like_menu_width'],     150);
	$theme  = vartrue($pref['facebook_like_menu_theme'],    'light');
	$ref    = deftrue('XURL_FACEBOOK', SITEURL); // vartrue($pref['facebook_like_menu_ref'], basename();
	$share  = vartrue($pref['facebook_like_menu_share'],    'false');

	$text = "<div style='overflow:hidden'>"; // prevent theme breakages.
	$text .= '<div class="fb-like" data-href="'.rtrim($ref.'/').'" data-width="'.$width.'px" data-layout="'.$layout.'" data-colorscheme="'.$theme.'" data-action="'.$action.'" data-show-faces="true" data-share="'.$share.'"></div>';
	$text .= "</div>";

	e107::getRender()->tablerender('Facebook',$text,'facebook-like-menu');

}elseif(ADMIN)
{
	$text = "<div class='alert alert-danger'>Unable to display feed. Facebook App ID has not been defined in preferences.</div>";
	e107::getRender()->tablerender('Facebook',$text,'twitter-menu');
}