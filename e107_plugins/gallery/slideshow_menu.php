<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Render gallery menu.
 */

if(!defined('e107_INIT'))
{
	exit;
}

e107_require_once(e_PLUGIN . 'gallery/includes/gallery_load.php');

e107::plugLan('gallery', 'front');

$gp = e107::getPlugPref('gallery');

e107::css('gallery', 'css/gallery.css');

// Load prettyPhoto settings and files.
gallery_load_prettyphoto();

e107::library('load', 'jquery.cycle');
e107::js('gallery', 'js/gallery.cycle.js');

$settings = array(
	'fx'      => varset($gp['slideshow_effect'], 'scrollHorz'),
	'speed'   => varset($gp['slideshow_duration'], 1000),
	'timeout' => varset($gp['slideshow_freq'], 4000),
);

e107::js('settings', array('gallery' => $settings));

$text = e107::getParser()->parseTemplate("{GALLERY_SLIDESHOW}");
e107::getRender()->tablerender(LAN_PLUGIN_GALLERY_TITLE, $text, 'gallery_slideshow');
unset($text);
unset($gp);
