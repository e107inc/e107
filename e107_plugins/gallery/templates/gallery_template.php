<?php


  
$GALLERY_TEMPLATE['LIST_START'] = 
	"<div class='gallery-list-start' style='clear:both'>";

		
$GALLERY_TEMPLATE['LIST_ITEM'] = 
	"<div style='width:190px;height:180px;float:left;margin:3px;border:1px solid black;background-color:black'>
	<div>{GALLERY_THUMB}</div>
	<div style='display:block;text-align:center;color:white;'>{GALLERY_CAPTION}</div>
	</div>
	";

$GALLERY_TEMPLATE['LIST_END'] = 
	"</div>
	<div class='gallery-list-end' style='text-align:center;clear:both'>
	<a href='".e_SELF."'>Back to Categories</a>
	</div>
	";
	
	
$GALLERY_TEMPLATE['CAT_START'] = 
	"<div class='gallery-cat-start' style='clear:both'>";

		
$GALLERY_TEMPLATE['CAT_ITEM'] = 
	"<div style='width:190px;height:180px;float:left;margin:3px;border:1px solid black;background-color:black'>
	<div>{GALLERY_CAT_THUMB}</div>
	<div style='text-align:center'><h3>{GALLERY_CAT_TITLE}</h3></div>
	</div>
	";

$GALLERY_TEMPLATE['CAT_END'] = 
	"</div>
	<div class='gallery-cat-end' style='text-align:center;clear:both'>
	</div>
	";
		

?>