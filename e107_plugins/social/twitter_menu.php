<?php
/**
* e107 website system
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

//@see https://publish.twitter.com/
e107::lan('social',false, true);

if(deftrue('XURL_TWITTER'))	
{

	$pref = e107::pref('social');

	$screenName     = basename(XURL_TWITTER);
	$limit          = vartrue($pref['twitter_menu_limit'], 5);
	$height         = vartrue($pref['twitter_menu_height'], 600);
	$theme          = vartrue($pref['twitter_menu_theme'], 'light');


	$extras = 'data-theme="'.$theme.'" data-tweet-limit="'.$limit.'"  style="height:'.$height.'px;max-width:100%" data-screen-name="'.$screenName.'" data-chrome="noheader nofooter transparent noscrollbar"';

	$text = '<a class="twitter-timeline" href="https://twitter.com/'.$screenName.'?ref_src=twsrc%5Etfw" '.$extras.'>'.LAN_SOCIAL_201."@".$screenName.'</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';

	e107::getRender()->tablerender('Twitter',$text,'twitter-menu');

}elseif(ADMIN)
{
	$text = "<div class='alert alert-danger'>".LAN_SOCIAL_200."</div>";
	e107::getRender()->tablerender('Twitter',$text,'twitter-menu');

}