<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/

if(!defined('e107_INIT'))
{
	exit;
}

e107::plugLan('gallery', 'front');

$gp = e107::getPlugPref('gallery');

e107::library('load', 'jquery.cycle');
e107::library('load', 'jquery.prettyPhoto');

e107::css('gallery', 'css/gallery.css');
e107::js('gallery', 'js/gallery.js');
e107::js('gallery', 'js/gallery.cycle.js');

$settings = array(
	'fx'      => varset($gp['slideshow_effect'], 'scrollHorz'),
	'speed'   => varset($gp['slideshow_duration'], 1000),
	'timeout' => varset($gp['slideshow_freq'], 4000),
);

e107::js('settings', array('gallery' => $settings));

$text = e107::getParser()->parseTemplate("{GALLERY_SLIDESHOW}");
e107::getRender()->tablerender("Gallery", $text, 'gallery_slideshow');
unset($text);
unset($gp);
