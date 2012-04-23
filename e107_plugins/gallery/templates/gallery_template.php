<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/

  
$GALLERY_TEMPLATE['LIST_START'] = 
	"<div class='gallery-list-start'>";

		
$GALLERY_TEMPLATE['LIST_ITEM'] = 
	"<div class='gallery-list-item'>
	<div>{GALLERY_THUMB}</div>
	<div class='gallery-list-caption'>{GALLERY_CAPTION}</div>
	</div>
	";

$GALLERY_TEMPLATE['LIST_END'] = 
	"</div>
	<div class='gallery-list-end' >
	<a href='".e_SELF."'>Back to Categories</a>
	</div>
	";
	
	
$GALLERY_TEMPLATE['CAT_START'] = 
	"<div class='gallery-cat-start'>";

		
$GALLERY_TEMPLATE['CAT_ITEM'] = 
	"<div class='gallery-cat-item'>
	<div class='gallery-cat-thumb'>{GALLERY_CAT_THUMB}</div>
	<div class='gallery-cat-title'><h3>{GALLERY_CAT_TITLE}</h3></div>
	</div>
	";

$GALLERY_TEMPLATE['CAT_END'] = 
	"</div>
	<div class='gallery-cat-end'>
	</div>
	";
		

?>