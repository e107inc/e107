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

if(deftrue('USER_AREA') && (defset('e_PAGE') ===  'gsitemap.php'))
{
	$canonicalurl = e107::url('gsitemap', 'index', null, array('mode' => 'full'));
	e107::link(array('rel'=>"canonical", "href" =>$canonicalurl));
}



