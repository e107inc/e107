<?php
include_lan(THEME."languages/".e_LANGUAGE.".php");

$THEME_CORE_JSLIB = array(
	'jslib/core/decorate.js' => 'all',
	'jslib/core/tabs.js' => 'admin'
);

$register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;

define("STANDARDS_MODE",TRUE);


function theme_head() {

	global $theme_pref;

	$ret = '';
	$ret .= '
		<link rel="stylesheet" href="'.THEME_ABS.'menu/menu.css" type="text/css" media="all" />
		<!--[if IE]>
		<link rel="stylesheet" href="'.THEME_ABS.'ie_all.css" type="text/css" media="all" />
		<![endif]-->
		<!--[if lte IE 7]>
			<script type="text/javascript" src="'.THEME_ABS.'menu/menu.js"></script>
		<![endif]-->
	';

    $ret .= "
    <script type='text/javascript'>
       /**
    	* Decorate all tables having e-list class
    	* TODO: add 'adminlist' class to all list core tables, allow theme decorate.
    	*/
        e107.runOnLoad( function() {
            \$\$('table.adminlist').each(function(element) {
            	e107Utils.Decorate.table(element, {tr_td: 'first last'});
            });
			\$\$('div.admintabs').each(function(element) {
            	new e107Widgets.Tabs(element);
            });

        }, document, true);

    </script>";

    if(THEME_LAYOUT == "alternate") // as matched by $HEADER['alternate'];
	{
        $ret .= "<!-- Include Something --> ";
	}

	if($theme_pref['_blank_example'] == 3)  // Pref from admin -> thememanager.
	{
        $ret .= "<!-- Include Something Else --> ";
	}


	return $ret;
}

function tablestyle($caption, $text, $mod) {
	global $style;
	$class = '';
	if(is_string($mod) && $mod == 'admin_help') $class = ' '.str_replace('_', '-', $mod); 
	switch($style) {

	case 'admin_menu' :
		echo '
			<div class="block">
				<h4 class="caption">'.$caption.'</h4>
				'.$text.'
			</div>
		';
	break;

	case 'site_info' :
		echo '
			<div class="block'.$class.'">
				<h4 class="caption">'.$caption.'</h4>
				<div class="block-text">
					'.$text.'
				</div>
			</div>
		';
	break;

	case 'admin_content':
		echo '
			<div class="block">
				<h2 class="caption">'.$caption.'</h2>
				<div class="block-text">
					'.$text.'
				</div>
			</div>
		';
	break;

	default:
		echo '
			<div class="block">
				<h4>'.$caption.'</h4>
				<div class="block-text">
					'.$text.'
				</div>
			</div>
		';
	break;
	}
}

$HEADER['default'] = '';
$FOOTER['default'] = '';

$HEADER['alternate'] = '';
$FOOTER['alternate'] = '';

/*

	$CUSTOMHEADER, CUSTOMFOOTER and $CUSTOMPAGES are deprecated.
	Default custom-pages can be assigned in theme.xml

 */



?>