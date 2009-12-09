<?php

// avoid PHP warnings
$FEATUREBOX_CATEGORY_TEMPLATE = array();

/*
 * Default Template
 * Example call: {FEATUREBOX} or {FEATUREBOX|default}
 */
$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['list_start'] = '
<div class="block">
	<h1 class="caption">{FEATUREBOX_CATEGORY_ICON} {FEATUREBOX_CATEGORY_TITLE}</h1>
	<div class="block-text">
';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['list_end'] = '
	</div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['col_end'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['item_start'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['item_end'] = '';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_default']['item_separator'] = '<div class="clear"><!-- --></div>';


/*
 * Dynamic Template
 * Example call: {FEATUREBOX|dynamic}
 */
$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['list_start'] = '
<div class="block">
	<h1 class="caption">{FEATUREBOX_CATEGORY_ICON} {FEATUREBOX_CATEGORY_TITLE}</h1>
	<div class="block-text">
';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['list_end'] = '
	</div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['col_end'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['item_start'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['item_end'] = '';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['blank_dynamic']['item_separator'] = '<div class="clear"><!-- --></div>';

/**
 * Template information. 
 * Allowed keys:
 * - title: Dropdown title (language constants are accepted e.g. 'MY_LAN')
 * - [optional] description: Template description (language constants are accepted e.g. 'MY_LAN') - UNDER CONSTRUCTION
 * - [optional] image: Template image preview (path constants are accepted e.g. '{e_PLUGIN}myplug/images/mytemplate_preview.png') - UNDER CONSTRUCTION
 * 
 * @var array
 */
$FEATUREBOX_CATEGORY_TEMPLATE['__INFO__'] = array(
	'blank_default' => array('title' => 'Blank Theme Default - show by category limit'),
	'blank_dynamic' => array('title' => 'Blank Theme Dynamic (AJAX) loading'),
);
?>