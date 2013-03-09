<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/

  
$GALLERY_TEMPLATE['LIST_START'] = 
	'<ul class="thumbnails gallery">';

		
$GALLERY_TEMPLATE['LIST_ITEM'] =  '
 <li class="span2">
	<div class="thumbnail">
		{GALLERY_THUMB}
		<h5>{GALLERY_CAPTION}</h5>
	</div>
</li>';

$GALLERY_TEMPLATE['LIST_END'] = 
	"</ul>
	<div class='center' >
		<div class='gallery-list-nextprev'>{GALLERY_NEXTPREV}</div>
		<div class='gallery-list-back'><a class='btn' href='{GALLERY_BASEURL}'>Back to Categories</a></div>
	</div>
";
	
	
$GALLERY_TEMPLATE['CAT_START'] = 
	'<ul class="thumbnails gallery-cat">';
	
	    
$GALLERY_TEMPLATE['CAT_ITEM'] = '
 <li class="span3">
	<div class="thumbnail">
		{GALLERY_CAT_THUMB}
		<h3>{GALLERY_CAT_TITLE}</h3>
	</div>
</li>';

/*		
$GALLERY_TEMPLATE['CAT_ITEM'] = 
	"<div class='gallery-cat-item'>
	<div class='gallery-cat-thumb'>{GALLERY_CAT_THUMB}</div>
	<div class='gallery-cat-title'><h3>{GALLERY_CAT_TITLE}</h3></div>
	</div>
	";
*/
$GALLERY_TEMPLATE['CAT_END'] = 
	"</ul>
	";

// {GALLERY_SLIDESHOW=X}  X = Gallery Category. Default: 1 (ie. 'gallery_1') Overrides preference in admin. 
// {GALLERY_SLIDES=X}  X = number of items per slide. 
// {GALLERY_JUMPER=space} will remove numbers and just leave spaces. 

$GALLERY_TEMPLATE['SLIDESHOW_WRAPPER'] = '
			
			<div id="gallery-slideshow-wrapper">
			    <div id="gallery-slideshow-content">
			        {GALLERY_SLIDES=4}
			    </div>
			</div>
			
			<div class="gallery-slideshow-controls">		
            	<a href="#" class="carousel-control gal-next" style="float: right">Next &rsaquo;</a>           
                <a href="#" class="carousel-control gal-prev" >&lsaquo; Previous</a>
                <span class="gallery-slide-jumper-container">{GALLERY_JUMPER}</span>
            </div>
         
            
		';	

$GALLERY_TEMPLATE['SLIDESHOW_SLIDE_ITEM'] = '<span class="gallery-slide-item">{GALLERY_THUMB=w=150&h=120}</span>';

		

?>