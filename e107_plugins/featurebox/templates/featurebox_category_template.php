<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: featurebox_category_template.php,v 1.5 2009-12-12 16:35:46 secretr Exp $
*
* Featurebox core category templates
*/

// TODO - list of all available shortcodes & schortcode parameters

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
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_start'] = '';
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_item'] = '';
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_end'] = '';
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['nav_separator'] = '';

// external JS, comma separated list
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['js'] = '';
// inline JS, without <script> tags
//$FEATUREBOX_CATEGORY_TEMPLATE['default']['js_inline'] = '';

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



//TODO - tabs template. 
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['list_start'] = '
<div class="box featurebox admintabs" id="featurebox-tab-container">
	{FEATUREBOX_NAVIGATION|tabs=loop&uselimit}
	<div class="tab-content-wrap">
';

$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['list_end'] = '
	</div>
	<div class="clear"><!-- --></div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['col_start'] = '<div id="tab-{FEATUREBOX_COLSCOUNT}-activate"><div class="tab-content">';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['col_end'] = '</div></div>';

// ajax navigation (unobtrusive)
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['item_separator'] = '<div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['nav_start'] = '<div class="tabs"><ul class="e-tabs clear" id="front-tabs">';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['nav_item'] = '<li id="featurebox-tab-{FEATUREBOX_NAV_COUNTER}"><a href="#tab-{FEATUREBOX_NAV_COUNTER}-activate"><span>{FEATUREBOX_TITLE}</span></a></li>';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['nav_end'] = '</ul></div>';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['nav_separator'] = '';

// external JS, comma separated list
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['js'] = '{e_FILE}jslib/core/tabs.js';
// inline JS, without <script> tags
$FEATUREBOX_CATEGORY_TEMPLATE['tabs']['js_inline'] = 'new e107Widgets.Tabs("featurebox-tab-container", { bookmarkFix: false });';

/**
 * Template information. 
 * Allowed keys:
 * - title: Dropdown title (language constants are accepted e.g. 'MY_LAN')
 * - [optional] description: Template description (language constants are accepted e.g. 'MY_LAN') - UNDER CONSTRUCTION
 * - [optional] image: Template image preview (path constants are accepted e.g. '{e_PLUGIN}myplug/images/mytemplate_preview.png') - UNDER CONSTRUCTION
 * 
 * @var array
 */
$FEATUREBOX_CATEGORY_INFO = array(
	'default' 	=> array('title' => 'Default (core)', 'description' => 'Flat - show by category limit'),
	'dynamic' 	=> array('title' => 'Dynamic (core)', 'description' => 'Load items on click (AJAX)'),
	'tabs'		=> array('title' => 'Tabs (core)'	, 'description' => 'Tabbed Feature box items')
);
?>