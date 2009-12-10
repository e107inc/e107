<?php
global $sc_style;

$FEATUREBOX_TEMPLATE['default'] = '
<div class="featurebox-item default">
	<h3>{FEATUREBOX_TITLE|default}</h3>
	<div class="featurebox-body">{FEATUREBOX_TEXT|default}</div>
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

$FEATUREBOX_TEMPLATE['__INFO__'] = array(
	'default' => array('title' => 'Default - no image'),
	'image_right' => array('title' => 'Image to right'),
	'image_left' => array('title' => 'Image to left'),
);

?>