<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/
if (!defined('e107_INIT')) { exit; }

//e107::js('gallery', 'jslib/lightbox/js/lightbox.js','jquery');
//e107::css('gallery', 'jslib/lightbox/css/lightbox.css','jquery');

// See: http://www.no-margin-for-errors.com/projects/prettyPhoto-jquery-lightbox-clone


if(USER_AREA)
{
// Work-around for indent issue. see: https://github.com/twitter/bootstrap/issues/4890
	e107::css('inline', "
/* Gallery CSS */
.thumbnails .span2:nth-child(6n+1) {
margin-left:0;
}",'jquery');


/*
e107::js('gallery', 'jslib/prettyPhoto/js/jquery.prettyPhoto.js','jquery');

e107::css('gallery', 'jslib/prettyPhoto/css/prettyPhoto.css','jquery');


e107::css('gallery', 'gallery_style.css');





$prettyPhoto = <<<JS
$(document).ready(function(){
    $("a[data-gal^='prettyPhoto']").prettyPhoto(
	    {
	    	hook: 'data-gal',
	    	theme: 'pp_default',
	    	overlay_gallery: false,
	    	deeplinking: false
	    }
    );
  });
JS;

e107::js('footer-inline',$prettyPhoto,'jquery');





	
unset($gp);
*/
}

?>