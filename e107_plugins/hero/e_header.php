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

$heroVisibility = e107::pref('hero', 'visibility', e_UC_NOBODY);

if(USER_AREA && deftrue('e_FRONTPAGE') && check_class($heroVisibility))
{
	e107::library('load', 'animate.css');
	e107::css('hero', 'css/hero.css');
}

unset($heroVisibility);


