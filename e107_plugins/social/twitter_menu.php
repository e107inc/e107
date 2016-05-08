<?php
/**
* e107 website system
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*/

//@see https://dev.twitter.com/web/embedded-timelines
e107::lan('social',false, true);

if(deftrue('XURL_TWITTER'))	
{

	e107::js('footer-inline',	'

	!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");

	');


	$pref = e107::pref('social');

	$screenName     = basename(XURL_TWITTER);
	$limit          = vartrue($pref['twitter_menu_limit'], 5);
	$height         = vartrue($pref['twitter_menu_height'], 600);
	$theme          = vartrue($pref['twitter_menu_theme'], 'light');
	$widgetId       = '585932823665647616'; //@e107



	$text = '<a class="twitter-timeline" data-theme="'.$theme.'" href="'.XURL_TWITTER.'" data-tweet-limit="'.$limit.'" data-widget-id="'.$widgetId.'" style="height:'.$height.'px" data-screen-name="'.$screenName.'" data-chrome="noheader nofooter transparent noscrollbar">'.LAN_SOCIAL_201."@".$screenName.'</a>';


	e107::getRender()->tablerender('Twitter',$text,'twitter-menu');

}elseif(ADMIN)
{
	$text = "<div class='alert alert-danger'>".LAN_SOCIAL_200."</div>";
	e107::getRender()->tablerender('Twitter',$text,'twitter-menu');

}