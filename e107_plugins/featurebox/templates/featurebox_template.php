<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Featurebox core item templates
*/

global $sc_style;

$FEATUREBOX_TEMPLATE['default'] = '
<div class="featurebox-item">
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

$FEATUREBOX_INFO = array(
	'default' 		=> array('title' => 'Default (core)', 		'description' => 'Title and description - no image'),
	'image_right' 	=> array('title' => 'Right image (core)',	'description' => 'Right floated image'),
	'image_left'	=> array('title' => 'Left image (core)'	, 	'description' => 'Left floated image')
);
?>