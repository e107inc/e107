<?php

// avoid PHP warnings
$FEATUREBOX_CATEGORY_TEMPLATE = array();

/*
 * Default Template
 * Example call: {FEATUREBOX} or {FEATUREBOX|default}
 */
$FEATUREBOX_CATEGORY_TEMPLATE['default']['list_start'] = '
<div class="box featurebox">
	<h2 class="title">{FEATUREBOX_CATEGORY_ICON}{FEATUREBOX_CATEGORY_TITLE}</h2>
	<div class="body">
';

$FEATUREBOX_CATEGORY_TEMPLATE['default']['list_end'] = '
	</div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['default']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['col_end'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['default']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['item_separator'] = '<div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['default']['item_empty'] = '';

// no dynamic load support
$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_item'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_separator'] = '';

// external JS, comma separated list
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['js'] = '';
// inline JS, without <script> tags
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['js_inline'] = '';

/*
 * Dynamic Template
 * Example call: {FEATUREBOX|dynamic}
 */
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['list_start'] = '
<div class="box featurebox" id="featurebox-container">
	<h2 class="title">{FEATUREBOX_CATEGORY_ICON}{FEATUREBOX_CATEGORY_TITLE}</h2>
	<div class="body">
';

$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['list_end'] = '
	</div>
	<div class="clear"><!-- --></div>
	{FEATUREBOX_NAVIGATION|dynamic=loop}
	<div class="clear"><!-- --></div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['col_end'] = '';

// ajax navigation (unobtrusive)
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['item_separator'] = '<div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['nav_start'] = '<div class="featurebox-nav show-if-js"> <a href="#" class="featurebox-nav-prev">prev</a> <a href="#" class="featurebox-nav-next">next</a> ';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['nav_item'] = '<a href="#{FEATUREBOX_CATEGORY_TEMPLATE}.{FEATUREBOX_NAV_COUNTER}.{FEATUREBOX_CATEGORY_COLS}.{FEATUREBOX_CATEGORY_EMPTYFILL}" class="featurebox-nav-link{FEATUREBOX_NAV_ACTIVE}">{FEATUREBOX_NAV_COUNTER}</a>';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['nav_end'] = '</div>';
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['nav_separator'] = '&nbsp;';

// external JS, comma separated list
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['js'] = '{e_PLUGIN}featurebox/featurebox.js';
// inline JS, without <script> tags
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['js_inline'] = 'new Featurebox(\'featurebox-container\')';
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
	'default' => array('title' => 'Default - show by category limit'),
	'dynamic' => array('title' => 'Dynamic (AJAX) loading'),
);
?>