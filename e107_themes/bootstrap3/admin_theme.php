<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Bootstrap 3 Theme for e107 v2.x admin area.
 */

if(!defined('e107_INIT'))
{
	exit();
}

//define("SEP", " <span class='fa fa-play e-breadcrumb'></span> ");
define("SEP", " <span class='fa fa-angle-double-right e-breadcrumb'></span> ");
define("BOOTSTRAP", 3);
define('FONTAWESOME', 4);

$adminStyle = e107::pref('core', 'admincss', 'css/bootstrap-dark.min.css');
e107::css('theme', $adminStyle);
e107::css('theme', 'admin_style.css');
e107::css('theme', 'ie_all.css', null, 'all', "<!--[if IE]>", "<![endif]-->");

e107::css('inline', "
.mce-menubar .mce-caret             { border-top-color: #C6C6C6!important  }
.mce-menubar:hover .mce-caret       { border-top-color: #FFFFFF!important }
.mce-menubar .mce-btn button        { color: #C6C6C6!important; }
.mce-menubar .mce-btn button span   { color: #C6C6C6!important; }
.mce-menubar .mce-btn button:hover  { color: #FFFFFF!important; }
.mce-menubar.mce-toolbar, .mce-window-head            { background-color: #373737; !important }
.mce-tinymce[role=application]      { border-color: #373737!important; }
.mce-menubar  .mce-menubtn:hover,
.mce-menubtn:active,
.mce-menubtn:focus                  { background-color:transparent!important; color: #FFFFFF!important; border-color:transparent!important; }
.mce-menubar  .mce-btn.mce-active   { color:white!important; border-color:transparent!important; background-color: transparent!important; }

body.forceColors                { margin:0; background-color: #373737; !important}
body.forceColors a              { color: white}
body.forceColors li a              { color: silver}

div#media-manager div.mce-window-head  { background-color: #373737; !important }
div#media-manager div.mce-title        { color:white; }
/* div#media-manager, html                { color: silver; background-color: #2F2F2F; !important} */
");

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


class bootstrap3_admintheme
{

	function tablestyle($caption, $text, $mode, $data)
	{
		// global $style;

		$style = $data['setStyle'];
		
	//	echo "Style: ".$style;

		echo "\n\n<!-- UniqueID: ".$data['uniqueId']." -->\n\n";
		echo "<!-- Style: ".$style." -->\n\n";
			echo "<!-- Mode: ".(string) $mode." -->";
		$class = '';

		if(is_string($mode) && $mode == 'admin_help')
		{
			$class = ' ' . str_replace('_', '-', $mode);
		}

		if($mode == 'core-infopanel_latest' || $mode == 'core-infopanel_status')
		{
			echo '<!-- Start Mode: ' . $mode . ' -->	
				<div class="well" style="padding:10px;min-height:220px;">  
					<div class="nav-header">' . $caption . '</div>
					<!-- Content Start -->
					' . $text . '
					<!-- Content End -->
				</div>
				<!-- End Mode: ' . $mode . ' -->
			';

			return;
		}

		if($mode == 'personalize')
		{
			$style = 'admin_menu';
		}

		if(deftrue('e_IFRAME'))
		{
			echo '<!-- Start Style: ' . $style . ' Mode: ' . $mode . ' and iFrame active -->
				<div class="block">
					<div class="block-text">
						' . $text . '
					</div>
				</div>
			';

			return;
		}

		if(trim($caption) == '')
		{
			$style = 'no_caption';
		}

		$panelType = array(
			'core-infopanel' => 'panel-default',
			'admin_menu'     => 'panel-primary',
			'site_info'      => 'panel-default',
			'flexpanel'      => 'panel-default',
		);

		if($data['uniqueId'] === 'e-latest-list' || $data['uniqueId'] === 'e-status-list')
		{
			$style = 'lists';
		}



		
		switch(varset($style, 'admin_content'))
		{
			case 'flexpanel':
				echo '<div class="panel ' . $panelType[$style] . '" id="' . $data['uniqueId'] . '">
					  <div class="panel-heading">
					    <h3 class="panel-title">' . $caption . '</h3>
					  </div>
					  <div class="panel-body">
					    ' . $text . '
					  </div>
					</div>';
				break;

				case 'admin_menu':
				echo '<div class="admin-menu panel panel-default" >
					  <div class="panel-heading">
					    <h3 class="panel-title">' . $caption . '</h3>
					  </div>

					    ' . $text . '

					</div>';
				break;


				case 'warning':
				echo '<div class="panel panel-warning" id="'.$data['uniqueId'].'">
					  <div class="panel-heading">
					    <h3 class="panel-title">' . $caption . '</h3>
					  </div>
					  <div class="panel-body">
					    ' . $text . '
					  </div>
					</div>';
				break;


			case 'core-infopanel':
			case 'site_info':
				echo '<div class="panel ' . $panelType[$style] . '"';
				if(!empty($data['uniqueId']))
				{
					echo ' id="'.$data['uniqueId'].'" ';
				}
				echo ' >
					  <div class="panel-heading">
					    <h3 class="panel-title">' . $caption . '</h3>
					  </div>
					  <div class="panel-body">
					    ' . $text . '
					  </div>
					</div>';
				break;

			case 'lists':
				echo '<div class="panel panel-default" id="' . $data['uniqueId'] . '">
					  <div class="panel-heading">
					    <h3 class="panel-title">' . $caption . '</h3>
					  </div>

					    ' . $text . '

					</div>';
				break;

			case 'no_caption':
				echo '<!-- Start Style: ' . $style . ' Mode: ' . $mode . ' -->
					<div class="block">
						<div class="block-text">
							' . $text . '
						</div>
					</div>
				';
				break;


			default:
				echo '<!-- Start Style: ' . $style . ' Mode: ' . $mode . ' -->
					<div class="block">
						<h4 class="caption">' . $caption . '</h4>
						<div class="block-text">
							' . $text . '
						</div>
					</div>
				';
				break;
		}
	}
}


$HEADER = '';
$FOOTER = '';


/*

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

 */
?>