<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if(!defined('e107_INIT'))
{
	exit;
}

if(USER_AREA)
{
	// Work-around for indent issue. see: https://github.com/twitter/bootstrap/issues/4890
		e107::css('inline', "
	/* Gallery CSS */
	.thumbnails .span2:nth-child(6n+1) {
	margin-left:0;
	}", 'jquery');


	$plugPrefs = e107::getPlugConfig('gallery')->getPref();

	if(vartrue($plugPrefs['pp_global'], false))
	{
		e107_require_once(e_PLUGIN . 'gallery/includes/gallery_load.php');
		// Load prettyPhoto settings and files.
		gallery_load_prettyphoto();
	}

}
