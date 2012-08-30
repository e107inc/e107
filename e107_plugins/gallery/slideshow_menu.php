<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/
if (!defined('e107_INIT')) { exit; }

e107::plugLan('gallery', 'front');

$text = e107::getParser()->parseTemplate("{GALLERY_SLIDESHOW}");
e107::getRender()->tablerender("Gallery",$text,'gallery_slideshow');
unset($text);

?>
