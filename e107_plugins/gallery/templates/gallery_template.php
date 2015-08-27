<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/


$GALLERY_TEMPLATE['list_caption'] = LAN_PLUGIN_GALLERY_TITLE;
  
$GALLERY_TEMPLATE['list_start'] = 
	'{GALLERY_BREADCRUMB}
	<div class="row gallery">
	';

		
$GALLERY_TEMPLATE['list_item'] =  '
 <div class="span2 col-xs-6 col-md-3">
	<div class="thumbnail">
		{GALLERY_THUMB=w=300&h=200}
		<h5>{GALLERY_CAPTION}</h5>
	</div>
</div>';

$GALLERY_TEMPLATE['list_end'] = 
	"</div>
	<div class='center' >
		<div class='gallery-list-nextprev'>{GALLERY_NEXTPREV}</div>
		<div class='gallery-list-back'><a class='btn btn-default' href='{GALLERY_BASEURL}'>".LAN_BACK."</a></div>
	</div>
";
	
// Bootstrap3 Compatible. 	

$GALLERY_TEMPLATE['cat_caption'] = LAN_PLUGIN_GALLERY_TITLE;

$GALLERY_TEMPLATE['cat_start'] = 
	'{GALLERY_BREADCRUMB}
	<div class="row gallery-cat">';
	
	    
$GALLERY_TEMPLATE['cat_item'] = '
 <div class="span3 col-xs-6 col-md-3">
	<div >
		{GALLERY_CAT_THUMB}
		<h3>{GALLERY_CAT_TITLE}</h3>
	</div>
</div>';


$GALLERY_TEMPLATE['cat_end'] = 
	"</div>
	";	
	
	

// {GALLERY_SLIDESHOW=X}  X = Gallery Category. Default: 1 (ie. 'gallery_1') Overrides preference in admin. 
// {GALLERY_SLIDES=X}  X = number of items per slide. 
// {GALLERY_JUMPER=space} will remove numbers and just leave spaces. 

$GALLERY_TEMPLATE['slideshow_wrapper'] = '
			
			<div id="gallery-slideshow-wrapper">
			    <div id="gallery-slideshow-content" >
			        {GALLERY_SLIDES=4}
			    </div>
			</div>
			
			<div class="gallery-slideshow-controls">		
            	<a href="#" class="gallery-control gal-next btn btn-xs btn-default" style="float: right">Next {GLYPH=fa-chevron-right}</a>           
                <a href="#" class="gallery-control gal-prev btn btn-xs btn-default" >{GLYPH=fa-chevron-left} Previous</a>
                <span class="gallery-slide-jumper-container">{GALLERY_JUMPER}</span>
            </div>
         
            
		';	

$GALLERY_TEMPLATE['slideshow_slide_item'] = '<span class="gallery-slide-item">{GALLERY_THUMB: w=150&h=120}</span>';

		

?>