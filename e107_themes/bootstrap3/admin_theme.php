<?php
if ( ! defined('e107_INIT')) { exit(); }

define("SEP"," <span class='fa fa-play e-breadcrumb'></span> ");
define("BOOTSTRAP", 	3);
define('FONTAWESOME',	4);

e107::js("url", 		"http://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js",'jquery', 2);
// e107::css('url', 		'http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css');
e107::css('url', 		"http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css");

// Too slow.
// e107::css('url', "http://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css");
// e107::js('url',  "http://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js");

e107::css('core', 	'bootstrap3-editable/css/bootstrap-editable.css', 'jquery');
e107::js('core', 	'bootstrap3-editable/js/bootstrap-editable.min.js', 'jquery', 4);

// e107::css('url', 'http://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/slate/bootstrap.min.css');
// e107::css('url', 'http://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/cyborg/bootstrap.min.css');
// e107::css('url', "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/cosmo/bootstrap.min.css");
// e107::css('url', "https://maxcdn.bootstrapcdn.com/bootswatch/3.3.5/darkly/bootstrap.min.css");

e107::css('theme','css/bootstrap-dark.min.css');
e107::css('theme','admin_style.css');
e107::css('theme','admin_dark.css');
e107::css('theme','ie_all.css',null,'all',"<!--[if IE]>","<![endif]-->");

e107::css('inline', "
/* TinyMce */

.mce-menubar .mce-caret             { border-top-color: #C6C6C6!important  }
.mce-menubar:hover .mce-caret       { border-top-color: #FFFFFF!important }
.mce-menubar .mce-btn button        { color: #C6C6C6!important; } /* v4.1+ */
.mce-menubar .mce-btn button span   { color: #C6C6C6!important; } /* v4.0 */
.mce-menubar .mce-btn button:hover  { color: #FFFFFF!important; }
.mce-menubar.mce-toolbar            { background-color: #373737!important; }
.mce-tinymce[role=application]      { border-color: #373737!important; }
.mce-menubar  .mce-menubtn:hover,
.mce-menubtn:active,
.mce-menubtn:focus                  { background-color:transparent!important; color: #FFFFFF!important; border-color:transparent!important; }
.mce-menubar  .mce-btn.mce-active   { color:white!important; border-color:transparent!important; background-color: transparent!important; }

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
		$class = '';

		if(is_string($mode) && $mode == 'admin_help') $class = ' '.str_replace('_', '-', $mode);
			
		if($mode == 'e_help')
		{
			$style = 'admin_menu';	
		}
		
		if($mode == 'core-infopanel_latest' || $mode == 'core-infopanel_status')
		{
			//return;
			echo '	
		<!-- Start Mode: '.$mode.' -->

					
					<div class="well" style="padding:10px;min-height:220px;" >  
					<div class="nav-header">'.$caption.'</div>
					<!-- Content Start -->
					'.$text.'
					<!-- Content End -->
					</div>
					

		<!-- End Mode: '.$mode.' -->
			';
			return;
		}	
	
		if($mode == 'personalize')
		{
			/*echo '
		<!-- Mode: '.$mode.' -->
		<div class="well" style="padding:10px">  
					<div class="nav-header">'.$caption.'</div>
					<!-- Content Start -->
					'.$text.'
					<!-- Content End -->
				</div>
		<!-- End Mode: '.$mode.' -->
		';*/
			$style = 'admin_menu';
		//	return;
		}
		
	
	/*
		
		if($style == 'core-infopanel')
		{
			echo '	
		<!-- Start Style: '.$style.' -->
			
		<li class="span12 col-md-12">
			
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
		*/
	
		if(deftrue('e_IFRAME'))
		{
			echo '<!-- Start Style: '.$style.' Mode: '.$mode.' and iFrame active -->
				<div class="block">
					<div class="block-text">
						'.$text.'
					</div>
				</div>
			';
			
			return;
		}
		
		if(trim($caption) == '')
		{
			$style = 'no_caption';	
		}

		$panelType = array('core-infopanel'=>'panel-default','admin_menu'=>'panel-primary', 'site_info'=>'panel-default');



		
		
		switch(varset($style, 'admin_content'))
		{
			case 'core-infopanel' :
			case 'admin_menu' :
			case 'site_info' :
				echo '<div class="panel '.$panelType[$style].'">
					  <div class="panel-heading">
					    <h3 class="panel-title">'.$caption.'</h3>
					  </div>
					  <div class="panel-body">
					    '.$text.'
					  </div>
					</div>';
			
			/*
				echo '
						<div class="well sidebar-nav" >  
						<div class="nav-header">'.$caption.'</div>
						'.$text.'
					</div>
				';
			 **/
			break;
		
			/*case 'site_info' :
				echo '
					<div class="panel panel-primary" >
						<div class="panel-heading">'.$caption.'</div>
						<p style="padding:10px">
							'.$text.'
						</p>
					</div>
				';
			break;*/
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
		
			case 'no_caption' :
				echo '<!-- Start Style: '.$style.' Mode: '.$mode.' -->
					<div class="block">
						<div class="block-text">
							'.$text.'
						</div>
					</div>
				';
			break;
		
		
			default:
				echo '<!-- Start Style: '.$style.' Mode: '.$mode.' -->
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
