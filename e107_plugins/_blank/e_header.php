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


if(USER_AREA) // prevents inclusion of JS/CSS/meta in the admin area.
{
	//e107::js('_blank', 'js/blank.js');      // loads e107_plugins/_blank/js/blank.js on every page.
	e107::css('_blank', 'css/blank.css');    // loads e107_plugins/_blank/css/blank.css on every page
	e107::meta('keywords', 'blank,words');   // sets meta keywords on every page.
}



