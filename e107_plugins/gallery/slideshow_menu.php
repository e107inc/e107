<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/
if (!defined('e107_INIT')) { exit; }

e107::plugLan('gallery', 'front');

$gp = e107::getPlugPref('gallery');


	e107::js('gallery', 'jslib/prettyPhoto/js/jquery.prettyPhoto.js','jquery');

	e107::css('gallery', 'jslib/prettyPhoto/css/prettyPhoto.css','jquery');


	e107::css('gallery', 'gallery_style.css');

	// Work-around for indent issue. see: https://github.com/twitter/bootstrap/issues/4890
	e107::css('inline', "

.thumbnails .span2:nth-child(6n+1) {
margin-left:0;
}",'jquery');



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




e107::js('gallery', 'jslib/jquery.cycle.all.js','jquery');
e107::js('footer-inline',"

$(document).ready(function() 
{
	
	$('#gallery-slideshow-content').cycle({
		fx: 		'".varset($gp['slideshow_effect'],'scrollHorz')."',
		next:		'.gal-next',
		prev: 		'.gal-prev',
		speed:		".varset($gp['slideshow_duration'],1000).",  // speed of the transition (any valid fx speed value) 
    	timeout:	".varset($gp['slideshow_freq'],4000).",
		slideExpr:	'.slide', 
		pause: 		1, // pause on hover - TODO pref
		
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
	
	$('#img.lb-close').on('live', function(e) {
		$(this).attr('src','".e_PLUGIN."gallery/jslib/lightbox/images/close.png');
	}); 



});
");


$text = e107::getParser()->parseTemplate("{GALLERY_SLIDESHOW}");
e107::getRender()->tablerender("Gallery",$text,'gallery_slideshow');
unset($text);
unset($gp);

?>
