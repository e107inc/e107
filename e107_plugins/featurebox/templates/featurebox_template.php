<?php
global $sc_style;

$FEATUREBOX_TEMPLATE['default'] = '
<div class="featurebox-item default">
	<h3>{FEATUREBOX_TITLE|default}</h3>
	<div class="featurebox-body">{FEATUREBOX_TEXT|default}</div>
</div>
';

$sc_style['FEATUREBOX_IMAGE|image_left']['pre'] = '<div class="f-left">';
$sc_style['FEATUREBOX_IMAGE|image_left']['post'] = '</div>';
$FEATUREBOX_TEMPLATE['image_right'] = '
<div class="featurebox-item imgleft" id="featurebox-item-{FEATUREBOX_ID}">
	<div class="featurebox-body">{FEATUREBOX_IMAGE|image_left}<h3>{FEATUREBOX_TITLE|image_left}</h3>{FEATUREBOX_TEXT|image_left}</div>
</div>
';

$sc_style['FEATUREBOX_IMAGE|image_right']['pre'] = '<div class="f-right">';
$sc_style['FEATUREBOX_IMAGE|image_right']['post'] = '</div>';
$FEATUREBOX_TEMPLATE['image_right'] = '
<div class="featurebox-item imgright" id="featurebox-item-{FEATUREBOX_ID}">
	<div class="featurebox-body">{FEATUREBOX_IMAGE|image_right}<h3>{FEATUREBOX_TITLE|image_right}</h3>{FEATUREBOX_TEXT|image_right}</div>
</div>
';

$FEATUREBOX_TEMPLATE['__INFO__'] = array(
	'default' => array('title' => 'Default - no image'),
	'image_right' => array('title' => 'Image to right'),
	'image_left' => array('title' => 'Image to left'),
);

?>