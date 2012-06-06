<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Featurebox core item templates
*/

global $sc_style;

$FEATUREBOX_TEMPLATE['default'] = '<!-- Feature box Item -->
	<div class="featurebox-item" >
		<h3>{FEATUREBOX_TITLE|default}</h3>
		{FEATUREBOX_TEXT|default}
	</div>
';


$sc_style['FEATUREBOX_IMAGE|image_left']['pre'] = '<img class="f-left" src="';
$sc_style['FEATUREBOX_IMAGE|image_left']['post'] = '" alt="" />';
$FEATUREBOX_TEMPLATE['image_left'] = '
	<div class="featurebox-item">
		{FEATUREBOX_IMAGE|image_left=src}<h3>{FEATUREBOX_TITLE|image_left}</h3>{FEATUREBOX_TEXT|image_left}
		<div class="clear"><!-- --></div>
	</div>
';


$sc_style['FEATUREBOX_IMAGE|image_right']['pre'] = '<img class="f-right" src="';
$sc_style['FEATUREBOX_IMAGE|image_right']['post'] = '" alt="" />';
$FEATUREBOX_TEMPLATE['image_right'] = '
	<div class="featurebox-item">
		{FEATUREBOX_IMAGE|image_right=src}<h3>{FEATUREBOX_TITLE|image_right}</h3>{FEATUREBOX_TEXT|image_right}
		<div class="clear"><!-- --></div>
	</div>
';



$FEATUREBOX_TEMPLATE['camera'] = '
	<div class="featurebox-item" data-thumb="{FEATUREBOX_THUMB=src}" data-src="{FEATUREBOX_IMAGE|camera=src}" data-link="{FEATUREBOX_URL}">
		<div class="featurebox-text camera_effected" style="position:absolute">
			<div class="featurebox-title">{FEATUREBOX_TITLE|camera}</div>
			<div class="featurebox-text">{FEATUREBOX_TEXT|camera}</div>
		</div>
	</div>
';



$FEATUREBOX_TEMPLATE['camera_caption'] = '
	<div class="featurebox-item" data-thumb="{FEATUREBOX_THUMB=src}" data-src="{FEATUREBOX_IMAGE|camera=src}" data-link="{FEATUREBOX_URL}">
		<div class="camera_caption fadeFromBottom">
			<h3>{FEATUREBOX_TITLE|camera}</h3>
			{FEATUREBOX_TEXT|camera}
		</div>
	</div>
';

$FEATUREBOX_TEMPLATE['accordion'] = '
	<h3 class="featurebox-title-accordion"><a href="#">{FEATUREBOX_TITLE|accordion}</a></h3>
		<div class="featurebox-text-accordion" >
			{FEATUREBOX_IMAGE|accordion}
			{FEATUREBOX_TEXT|accordion}
			<div class="clear"><!-- --></div>
		</div>
';

$FEATUREBOX_TEMPLATE['tabs'] = '
		<div class="featurebox-text-tabs" >
			{FEATUREBOX_IMAGE|accordion}
			{FEATUREBOX_TEXT|accordion}
			<div class="clear"><!-- --></div>
		</div>
';



$FEATUREBOX_INFO = array(
	'default' 		=> array('title' => 'Default (core)', 		'description' => 'Title and description - no image'),
	'image_right' 	=> array('title' => 'Right image (core)',	'description' => 'Right floated image'),
	'image_left'	=> array('title' => 'Left image (core)'	, 	'description' => 'Left floated image'),
	'camera'		=> array('title' => 'Camera item',	'description' => 'For use with the "camera" category'),
	'camera_caption' => array('title' => 'Camera item with caption',	'description' => 'For use with the "camera" category'),
	'accordion' 	=> array('title' => 'Accordion Item',	'description' => 'For use with accordion'),
	'tabs' 			=> array('title' => 'Tab Item',	'description' => 'For use with tabs')
);
?>