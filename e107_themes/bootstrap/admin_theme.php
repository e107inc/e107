<?php
if ( ! defined('e107_INIT')) { exit(); }

define('STANDARDS_MODE', TRUE);

// include_lan(e_THEME."_blank/languages/".e_LANGUAGE.".php");

 e107::js('theme','js/bootstrap.js');
e107::css('theme','css/bootstrap.css');
e107::css('theme','css/bootstrap-responsive.css');
e107::css('theme','ie_all.css',null,'all',"<!--[if IE]>","<![endif]-->");
/*
$drop = "
$(function() {
	$('.navbar .dropdown').hover(function() {
	$(this).find('.dropdown-menu').first().stop(true, true).slideDown()
}, function() {
	$(this).find('.dropdown-menu').first().stop(true, true).slideUp('fast')
});
});
";

 e107::js("inline",$drop);
 */

// e107::js("inline","$('.dropdown-toggle').toggle('slow');");



if(defined('TEXTDIRECTION') && file_exists(THEME.'/menu/menu_'.strtolower(TEXTDIRECTION).'.css'))
{
	e107::css('theme','menu/menu_'.strtolower(TEXTDIRECTION).'.css');
}
else
{
	e107::css('theme','menu/menu.css');
}


$register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;


//	define("ADMIN_TRUE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/true_32.png' alt='' />");
//	define("ADMIN_TRUE_ICON_PATH", e_IMAGE."admin_images/true_32.png");

//	define("ADMIN_FALSE_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/false_32.png' alt='' />");
//	define("ADMIN_FALSE_ICON_PATH", e_IMAGE."admin_images/false_32.png");

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

	define("ADMIN_SORT_ICON", "<img class='icon action S32' src='".e_IMAGE_ABS."admin_images/sort_32.png' alt='' title='Re-Sort' />");
	define("ADMIN_SORT_ICON_PATH", e_IMAGE."admin_images/sort_32.png");


function theme_head() {

	$ret = '
		<!--[if lte IE 7]>
			<script type="text/javascript" src="'.THEME_ABS.'menu/menu.js"></script>
		<![endif]-->
	';
	return $ret;
}

function tablestyle($caption, $text, $mode) 
{
	global $style;
	
	$class = '';
	
	// echo 'mod='.$style;
	
	if(is_string($mode) && $mode == 'admin_help') $class = ' '.str_replace('_', '-', $mode);
	
	if($mode == 'core-infopanel_latest' || $mode == 'core-infopanel_status')
	{
		echo '
				<div class="well sidebar-nav span3" style="padding:10px; min-height:200px">  
				<div class="nav-header">'.$caption.'</div>
				'.$text.'
			</div>
		';
		return;
	}	
	
	if($style == 'core-infopanel')
	{
		echo '
				<div class="well sidebar-nav span6" style="padding:10px">  
				<div class="nav-header">'.$caption.'</div>
				'.$text.'
			</div>
		';
		return;
	}
	

	
	
	
	
	
	
	switch(varset($style, 'admin_content')) {

	case 'admin_menu' :
		echo '
				<div class="well sidebar-nav" style="padding:10px">  
				<div class="nav-header">'.$caption.'</div>
				'.$text.'
			</div>
		';
	break;

	case 'site_info' :
		echo '
			<div class="well sidebar-nav" style="padding:10px">  
				<div class="nav-header">'.$caption.'</div>
				<p>
					'.$text.'
				</p>
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