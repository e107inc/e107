<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/
if (!defined('e107_INIT')) { exit; }

e107::getJS()->headerFile("http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js",1);
e107::getJs()->headerPlugin('gallery', 'jslib/lightbox/js/lightbox.js');
e107::getJs()->pluginCSS('gallery', 'jslib/lightbox/css/lightbox.css');

e107::getJS()->headerFile("https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js",1);
e107::getJS()->headerFile("https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js?load=effects",1);
e107::getJs()->headerPlugin('gallery', 'jslib/carousel.js');
e107::getJs()->pluginCSS('gallery', 'gallery_style.css');

e107::getJs()->footerInline("		
	new Carousel('carousel-wrapper', $$('#carousel-content .slide'), $$('a.carousel-control', 'a.carousel-jumper' ),
	{
		auto: true,
		circular: true
	});
");





?>