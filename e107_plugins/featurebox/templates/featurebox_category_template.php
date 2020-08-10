<?php
/*
* Copyright (c) e107 Inc 2013 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox core category templates
*/

// TODO - list of all available shortcodes & schortcode parameters
$FEATUREBOX_CATEGORY_TEMPLATE = array();

// Bootstrap 3


$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['list_start'] = '
<div id="carousel-example-generic" class="featurebox carousel slide" data-ride="carousel">  
{FEATUREBOX_NAVIGATION|bootstrap3_carousel=loop&uselimit=1}
  <!-- Wrapper for slides -->
  <div class="carousel-inner">
    
';

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['list_end'] = '
	  </div>

	<!-- Controls -->
  <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left"></span>
  </a>
  <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right"></span>
  </a>
                    

	</div><!-- end row -->

<!-- end carousel -->
';

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['nav_start'] = '<!-- Indicators --><ol class="carousel-indicators">';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['nav_item'] = '<li data-target="#carousel-example-generic" data-slide-to="{FEATUREBOX_COUNTER=0}" class="{FEATUREBOX_ACTIVE}"></li>';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['nav_end'] = '</ol>';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['nav_separator'] = '';


$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['col_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['item_separator'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap3_carousel']['item_empty'] = '';






// v2.x Default - Bootstrap. 

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['list_start'] = '
 <!-- starts carousel -->
	<div class="row animated fadeInDown">
		<div class="span12">
			<div id="myCarousel" class="carousel slide">
				<!-- carousel items -->
				<div class="carousel-inner">
';

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['list_end'] = '
				</div>
	 				
	 				<!-- Carousel nav -->
                    <a class="carousel-control left" href="#myCarousel" data-slide="prev">&lsaquo;</a>
                    <a class="carousel-control right" href="#myCarousel" data-slide="next">&rsaquo;</a>
                    
			</div><!-- end myCarousel -->
		</div><!-- end span12 -->
	</div><!-- end row -->

<!-- end carousel -->
';


$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['col_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['col_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['item_separator'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_carousel']['item_empty'] = '';





// GENERIC template avoid PHP warnings


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
$FEATUREBOX_CATEGORY_TEMPLATE['dynamic']['js_type'] = 'prototype';


// ------------------------------------------ TABS (Legacy Prototype.js) ---------------------------
/*
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['list_start'] = '
<div class="box featurebox admintabs" id="featurebox-tab-container">
	{FEATUREBOX_NAVIGATION|tabs=loop&uselimit}
	<div class="tab-content-wrap">
';

$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['list_end'] = '
	</div>
	<div class="clear"><!-- --></div>
</div>
';

// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['col_start'] = '<div id="tab-{FEATUREBOX_COLSCOUNT}-activate"><div class="tab-content">';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['col_end'] = '</div></div>';

// ajax navigation (unobtrusive)
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['item_separator'] = '<div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['nav_start'] = '<div class="tabs"><ul class="e-tabs clear" id="front-tabs">';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['nav_item'] = '<li id="featurebox-tab-{FEATUREBOX_COUNTER}"><a href="#tab-{FEATUREBOX_COUNTER}-activate"><span>{FEATUREBOX_TITLE}</span></a></li>';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['nav_end'] = '</ul></div>';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['nav_separator'] = '';

// external JS, comma separated list

$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['js'] = '{e_WEB_JS}core/tabs.js';
// inline JS, without <script> tags
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['js_inline'] = 'new e107Widgets.Tabs("featurebox-tab-container", { bookmarkFix: false });';
$FEATUREBOX_CATEGORY_TEMPLATE['tabs-proto']['js_type'] = 'prototype';
*/

// ------------------------------------------ TABS (Bootstrap) ----------------------------------------------


$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['list_start'] = '
<div class="box featurebox tab-content">
	{FEATUREBOX_NAVIGATION|bootstrap_tabs=loop&uselimit}	
';

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['list_end'] = '
	</div>
	<div class="clear"><!-- --></div>

';
// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['col_start'] = '<div id="tab-{FEATUREBOX_COLSCOUNT}" class="tab-pane {FEATUREBOX_ACTIVE}">';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['col_end'] = '</div>';

// ajax navigation (unobtrusive)
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['item_separator'] = '<div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['nav_start'] = '<ul class="nav nav-tabs">';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['nav_item'] = '<li class="{FEATUREBOX_ACTIVE}"><a data-toggle="tab" href="#tab-{FEATUREBOX_COLSCOUNT}">{FEATUREBOX_TITLE}</a></li>';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['nav_end'] = '</ul>';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['nav_separator'] = '';
//<div class="e-bootstrap_tabs">
// external JS, comma separated list

$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['js'] = '';
// inline JS, without <script> tags
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['js_inline'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['bootstrap_tabs']['js_type'] = 'jquery';



// ------------------------------------------ ACCORDION (jquery) ----------------------------------------------


$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['list_start'] 		= "\n\n<!-- Accordion -->\n<div class='box featurebox' id='featurebox-accordion-container' style='width:100%'>";

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['list_end'] 			= "</div>\n<!-- -->\n\n";

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['col_start'] 		= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['col_end'] 			= '';

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['item_start'] 		= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['item_end'] 			= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['item_separator'] 	= '';

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['item_empty'] 		= '';

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['nav_start'] 		= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['nav_item'] 			= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['nav_end'] 			= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['nav_separator'] 	= '';

$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['js'] 				= '';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['js_inline'] 		= '$( "#featurebox-accordion-container" ).accordion({FEATUREBOX_PARMS});';
$FEATUREBOX_CATEGORY_TEMPLATE['accordion']['js_type'] 			= 'jquery';



// ------------------------------------------ CAMERA ----------------------------------------------
/*
 <div class="camera_wrap">
    <div data-src="images/image_1.jpg"></div>
    <div data-src="images/image_1.jpg"></div>
    <div data-src="images/image_2.jpg"></div>
</div>
 */
/*
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['list_start'] = '<!-- start Camera -->
<div class="box featurebox camera_wrap camera_azure_skin">
	
';
// {FEATUREBOX_NAVIGATION|camera=loop&uselimit}
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['list_end'] = '
</div>
<div class="clear"><!-- --></div>
<!-- End Camera -->
';
// no column support
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['col_start'] = '';// <div id="tab-{FEATUREBOX_COLSCOUNT}-activate"><div class="tab-content">';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['col_end'] = ''; // </div></div>';

// ajax navigation (unobtrusive)
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['item_start'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['item_end'] = '';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['item_separator'] = '';// <div class="clear"><!-- --></div>';

// empty item  - used with col templates, no shortcodes just basic markup
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['item_empty'] = '';

$FEATUREBOX_CATEGORY_TEMPLATE['camera']['nav_start'] = ''; // <ul>';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['nav_item'] = ''; // <li><a href="#tab-{FEATUREBOX_COUNTER}-activate">{FEATUREBOX_TITLE}</a></li>';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['nav_end'] = ''; // </ul>';
$FEATUREBOX_CATEGORY_TEMPLATE['camera']['nav_separator'] = '';
*/

// Depracated camera.js, need replacement
//$FEATUREBOX_CATEGORY_TEMPLATE['camera']['js'] = '{e_WEB_JS}camera/scripts/camera.min.js';
//$FEATUREBOX_CATEGORY_TEMPLATE['camera']['js_inline'] = "$('.camera_wrap').camera({FEATUREBOX_PARMS});";
//$FEATUREBOX_CATEGORY_TEMPLATE['camera']['js_type'] = 'jquery';



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
	'bootstrap_carousel' 	=> array('title' => 'Bootstrap v2 Carousel', 		'description' => "Bootstrap's Hero slider"),
	'bootstrap3_carousel' 	=> array('title' => 'Bootstrap v3 Carousel', 		'description' => "Bootstrap's Hero slider"),
	'bootstrap_tabs'		=> array('title' => 'Bootstrap Tabs'	,	 	'description' => 'Tabbed Feature box items'),
//	'camera' 				=> array('title' => 'Image-Slider (jquery)'	, 	'description' => 'Image transitions using the "Camera" jquery plugin'),
//	'accordion' 			=> array('title' => 'Accordion (jquery)'	, 	'description' => 'Accordion utilizing jQuery UI'),
	'default' 				=> array('title' => 'Default', 					'description' => 'Flat - show by category limit'),
//  DEPRECATED	'dynamic' 				=> array('title' => 'Dynamic (prototype.js)', 	'description' => 'Load items on click (AJAX)'),
// DEPRECATED	'tabs-proto' 			=> array('title' => 'Tabs (prototype.js)'	, 	'description' => 'Tabbed Feature box items')
);
