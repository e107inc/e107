<?php

$THEME_CORE_JSLIB = array(
	'jslib/core/decorate.js' => 'all',
	'jslib/core/tabs.js' => 'admin'
);

$register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;

function theme_head() {
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
    	* TODO: add 'e-list' class to all list core tables, allow theme decorate. 
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
	return $ret;
}

function tablestyle($caption, $text){
	global $style;
	switch($style) {

	case 'admin_menu' :
		echo '
			<div class="block">
				<h4>'.$caption.'</h4>
				'.$text.'
			</div>
		';
	break;

	case 'site_info' :
		echo '
			<div class="block">
				<h4>'.$caption.'</h4>
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

$HEADER = '';
$FOOTER = '';

?>