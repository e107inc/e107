<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/
if (!defined('e107_INIT')) { exit; }

e107::js('gallery', 'jslib/lightbox/js/lightbox.js','jquery');
e107::css('gallery', 'jslib/lightbox/css/lightbox.css','jquery');

e107::js('gallery', 'jslib/jquery.cycle.all.js','jquery');
e107::css('gallery', 'gallery_style.css');

$gp = e107::getPlugPref('gallery');

e107::js('inline',"
$(document).ready(function() 
{
	
	$('#gallery-slideshow-content').cycle({
		fx: 		'".varset($gp['slideshow_effect'],'scrollHorz')."',
		next:		'.gal-next',
		prev: 		'.gal-prev',
		speed:		1000,  // speed of the transition (any valid fx speed value) 
    	timeout:	4000,
		slideExpr:	'.slide', 
		
		activePagerClass: '.gallery-slide-jumper-selected',//,
		before: function(currSlideElement, nextSlideElement, options, forwardFlag)
		{
			var nx = $(nextSlideElement).attr('id').split('item-');
			var th = $(currSlideElement).attr('id').split('item-');
			$('#gallery-jumper-'+th[1]).removeClass('gallery-slide-jumper-selected');
			$('#gallery-jumper-'+nx[1]).addClass('gallery-slide-jumper-selected');						
		}
	});
	
	
	
	$('.gallery-slide-jumper').click(function() { 
		var nid = $(this).attr('id');
		var id = nid.split('-jumper-');
	
		var go = parseInt(id[1]) - 1;
    	$('#gallery-slideshow-content').cycle(go); 
    	return false; 
	}); 

});
");

	
unset($gp);


?>