<?php
if ( ! defined('e107_INIT')) { exit(); }

define('STANDARDS_MODE', TRUE);
// define("VIEWPORT","width=device-width, initial-scale=1.0");
define("VIEWPORT","width=1080");
define("SEP"," <i class='icon-play e-breadcrumb'></i> ");

e107::lan('theme');
e107::js('core','bootstrap/js/bootstrap.min.js');
e107::css('core','bootstrap/css/bootstrap.min.css');
e107::css('core','bootstrap/css/bootstrap-responsive.min.css');
// e107::css('core','bootstrap/css/jquery-ui.custom.css');
e107::css('theme','admin_style.css');

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
	// e107::css('theme','menu/menu_'.strtolower(TEXTDIRECTION).'.css');
}
else
{
	// e107::css('theme','menu/menu.css');
}


// $register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;



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
		//return;
		echo '	
	<!-- Start Mode: '.$mode.' -->
	<li class="span6 '.$mode.'" >
				
				<div class="well" style="padding:10px;min-height:220px;" >  
				<div class="nav-header">'.$caption.'</div>
				<!-- Content Start -->
				'.$text.'
				<!-- Content End -->
				</div>
				
	</li>
	<!-- End Mode: '.$mode.' -->
		';
		return;
	}	

	if($mode == 'personalize')
	{
		echo '
	<!-- Mode: '.$mode.' -->
	<div class="well" style="padding:10px">  
				<div class="nav-header">'.$caption.'</div>
				<!-- Content Start -->
				'.$text.'
				<!-- Content End -->
			</div>
	<!-- End Mode: '.$mode.' -->
	';
			
		return;
	}
	


	
	if($style == 'core-infopanel')
	{
		echo '	
	<!-- Start Style: '.$style.' -->
		
	<li class="span12">
		
			<div class="well" >  
				<div class="nav-header">'.$caption.'</div>
				<!-- Content Start -->
				'.$text.'
				<!-- Content End -->
			</div>
			
	</li>
	<!-- End Style: '.$style.' -->
		';
		return;
	}
	

	if(e_IFRAME === true)
	{
		echo '
			<div class="block">
				<div class="block-text">
					'.$text.'
				</div>
			</div>
		';
		
		return;
	}
	
	
	
	
	switch(varset($style, 'admin_content')) {

	case 'admin_menu' :
		echo '
				<div class="well sidebar-nav" >  
				<div class="nav-header">'.$caption.'</div>
				'.$text.'
			</div>
		';
	break;

	case 'site_info' :
		echo '
			<div class="well sidebar-nav" >  
				<div class="nav-header">'.$caption.'</div>
				<p style="padding:10px">
					'.$text.'
				</p>
			</div>
		';
	break;
/*
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
*/
	default:
		echo '
			<div class="block">
				<h4 class="caption">'.$caption.'</h4>
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