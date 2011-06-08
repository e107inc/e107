<?php
if ( ! defined('e107_INIT')) { exit(); }

define('STANDARDS_MODE', TRUE);

include_lan(e_THEME."_blank/languages/".e_LANGUAGE.".php");

//temporary fixed - awaiting theme.xml addition
e107::getJs()->requireCoreLib(array(
	'core/decorate.js' => 2,
	'core/tabs.js' => 2
));

$register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;



	define("ADMIN_TRUE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/true_32.png' alt='' />");
	define("ADMIN_TRUE_ICON_PATH", e_IMAGE."admin_images/true_32.png");



	define("ADMIN_FALSE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/false_32.png' alt='' />");
	define("ADMIN_FALSE_ICON_PATH", e_IMAGE."admin_images/false_32.png");



	define("ADMIN_EDIT_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/edit_32.png' alt='' title='".LAN_EDIT."' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE."admin_images/edit_32.png");



	define("ADMIN_DELETE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/delete_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE."admin_images/delete_32.png");


	define("ADMIN_WARNING_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/warning_32.png' alt='' />");
	define("ADMIN_WARNING_ICON_PATH", e_IMAGE."admin_images/warning_32.png");

	define("ADMIN_ADD_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/add_32.png' alt='' />");
	define("ADMIN_ADD_ICON_PATH", e_IMAGE."admin_images/add_32.png");

	define("ADMIN_INFO_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/info_32.png' alt='' />");
	define("ADMIN_INFO_ICON_PATH", e_IMAGE."admin_images/info_32.png");

	define("ADMIN_CONFIGURE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/configure_32.png' alt='' />");
	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE."admin_images/configure_32.png");

	define("ADMIN_VIEW_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/search_32.png' alt='' />");
	define("ADMIN_VIEW_ICON_PATH", e_IMAGE."admin_images/admin_images/search_32.png");

	define("ADMIN_URL_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/forums_32.png' alt='' />");
	define("ADMIN_URL_ICON_PATH", e_IMAGE."admin_images/forums_32.png");

	define("ADMIN_INSTALLPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/plugin_install_32.png' alt='' />");
	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_install_32.png");

	define("ADMIN_UNINSTALLPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/plugin_uninstall_32.png' alt='' />");
	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE."admin_images/plugin_unstall_32.png");

	define("ADMIN_UPGRADEPLUGIN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/up_32.png' alt='' />");
	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE."admin_images/up_32.png");

	define("ADMIN_UP_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/up_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_UP_ICON_PATH", e_IMAGE."admin_images/up_32.png");

	define("ADMIN_DOWN_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/down_32.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DOWN_ICON_PATH", e_IMAGE."admin_images/down_32.png");
	
	define("ADMIN_EXECUTE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/execute_32.png' alt='' title='".LAN_EXECUTE."' />");
	define("ADMIN_EXECUTE_ICON_PATH", e_IMAGE."admin_images/execute_32.png");



function theme_head() {
	$ret = '';
	if(defined('TEXTDIRECTION') && file_exists(THEME.'/menu/menu_'.strtolower(TEXTDIRECTION).'.css'))
	{
		$ret .= '
		<link rel="stylesheet" href="'.THEME_ABS.'menu/menu_'.strtolower(TEXTDIRECTION).'.css" type="text/css" media="all" />';
	}
	else
	{
		$ret .= '
		<link rel="stylesheet" href="'.THEME_ABS.'menu/menu.css" type="text/css" media="all" />';
	}

	$ret .= '
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
        e107.runOnLoad( function(event) {
        	var element = event.memo['element'] ? $(event.memo.element) : $$('body')[0];

            element.select('table.adminlist:not(.no-decorate)').each(function(element) {
            	e107Utils.Decorate.table(element, {tr_td: 'first last'});
            });
               element.select('table.fborder:not(.no-decorate)').each(function(element) {
            	e107Utils.Decorate.table(element, {tr_td: 'first last'});
            });
			element.select('div.admintabs').each(function(element) {
				//show tab navaigation
				element.select('ul.e-tabs').each( function(el){
					el.show();
					el.removeClassName('e-hideme');//prevent hideme re-register (e.g. ajax load)
				});
				//init tabs
            	new e107Widgets.Tabs(element);
            	//hide legends if any
            	element.select('legend').invoke('hide');
            });

        }, document, true);

    </script>";
	return $ret;
}

function tablestyle($caption, $text, $mod) {
	global $style;
	$class = '';
	if(is_string($mod) && $mod == 'admin_help') $class = ' '.str_replace('_', '-', $mod);

	switch(varset($style, 'admin_content')) {

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

$HEADER = '';
$FOOTER = '';

define('ICONMAIL', 'email_16.png');
define('ICONPRINT', 'print_16.png');
define('ICONSTYLE', 'border: 0px');
define('COMMENTLINK', LAN_THEME_2);
define('COMMENTOFFSTRING', LAN_THEME_1);
define('PRE_EXTENDEDSTRING', '<br /><br />');
define('EXTENDEDSTRING', LAN_THEME_3);
define('POST_EXTENDEDSTRING', '<br />');
define('TRACKBACKSTRING', LAN_THEME_4);
define('TRACKBACKBEFORESTRING', '&nbsp;|&nbsp;');

$sc_style['NEWSIMAGE']['pre'] = '<div style="float: left; margin-right: 15px">';
$sc_style['NEWSIMAGE']['post'] = '</div>';
$sc_style['NEWSICON']['pre'] = '<div style="float: left; margin-right: 15px">';
$sc_style['NEWSICON']['post'] = '</div>';

$NEWSSTYLE = '
<div class="newsItem clear">
	<h4>{NEWSTITLE}</h4>
	<span class="newsAuthor">{NEWSAUTHOR}</span>
	<span class="newsDate">{NEWSDATE}</span>
	<div style="clear: both; margin-bottom: 5px;"><!-- --></div>
	{NEWSIMAGE}
	{NEWSBODY}
	{EXTENDED}
	{TRACKBACK}
	<div style="clear: both; margin-bottom: 5px;"><!-- --></div>
	<table class="newsComments" cellpadding="0" cellspacing="0" style="border: 0px none; width: 100%" >
		<tr>
			<td valign="middle" style="text-align: left">
				{NEWSCOMMENTS}
			</td>
			<td valign="middle" style="text-align: right">
				{ADMINOPTIONS}{EMAILICON}{PRINTICON}{PDFICON}
			</td>
		</tr>
	</table>
</div>
';
?>