<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Related configuration module - News
 *
 *
*/

if (!defined('e107_INIT')) { exit; }


if(USER_AREA && e107::getMenu()->isLoaded('news_carousel'))
{
	e107::css('news','news_carousel.css');
}

