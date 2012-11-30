<?php
if ( ! defined('e107_INIT')) { exit(); }

define("VIEWPORT","width=device-width, initial-scale=1.0");

e107::lan('theme');
e107::js('core','bootstrap/js/bootstrap.min.js');
e107::css('core','bootstrap/css/bootstrap.min.css');
e107::css('core','bootstrap/css/bootstrap-responsive.min.css');
e107::css('core','bootstrap/css/jquery-ui.custom.css');

//$register_sc[]='FS_ADMIN_ALT_NAV';
$no_core_css = TRUE;

define("STANDARDS_MODE",TRUE);

// TODO - JS/CSS handling via JSManager
function theme_head() {

	$theme_pref = e107::getThemePref();

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

function tablestyle($caption, $text, $mod) 
{
	global $style;
	
	$type = $style;
	if(empty($caption))
	{
		$type = 'box';
	}
	if($mod == "loginbox")
	{
		$style = 'loginbox';
			echo '
				'.$text.'
			';
		return; 
	}

	
	switch($type) 
	{

		case 'menu' :
			echo '
				<div class="block">
					<h4 class="caption">'.$caption.'</h4>
					'.$text.'
				</div>
			';
		break;
		
		case 'box':
			echo '
				<div class="block">
					<div class="block-text">
						'.$text.'
					</div>
				</div>
			';
		break;
	
		default:
			echo '
				<div class="block">
					<h1 class="caption">'.$caption.'</h1>
					<div class="block-text">
						'.$text.'
					</div>
				</div>
			';
		break;
	}
}
$c_login = '
<div class="site-login">
	'.((!USER) ? '
	<div class="btn-toolbar">
		<a href="'.SITEURL.'signup.php" class="btn btn-success register" title="'.LAN_THEME_REGISTER.'">'.LAN_THEME_REGISTER.'<span class="caret"></span></a>
		<a href="#clogin" role="button" class="btn btn-login" data-toggle="modal" title="'.LAN_THEME_LOGIN.'">'.LAN_THEME_LOGIN.'<span class="caret"></span></a>
	</div>
	{SETSTYLE=clogin}
	{PLUGIN=login_menu}
	' : '
	<a href="#clogin" role="button" class="btn btn-login" data-toggle="modal" title="'.LAN_THEME_WELCOME.' '.USERNAME.'">'.LAN_THEME_WELCOME.' '.USERNAME.'<span class="caret"></span></a>
	{PLUGIN=login_menu}
	').'
</div>
';

$HEADER['default'] = '
<div class="container-fluid">
	<div class="row-fluid">
		<div class="navbar navbar-inverse navbar-fixed-top site-header">
			<div class="navbar-inner">
				<div class="span9">
					<div class="site-logo pull-left thumbnails"><a class="logolink" href="'.SITEURL.' title="">{LOGO}</a></div><div class="dropdown nav navbar-text pull-left">{SITELINKS}</div>
				</div>
				<div class="span3">
					<div class="pull-right">'.$c_login.'</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span2">
		{SETSTYLE=site_info}
		
		{MENU=2}
		
		</div>
		<div class="span10">
';
$FOOTER['default'] = '
		</div><!--/span-->
	</div><!--/row-->

<hr>

<footer class="center"> 
	Copyright &copy; 2008-2012 e107 Inc (e107.org)<br />
</footer>

</div><!--/.fluid-container-->';

$HEADER['alternate'] = '';
$FOOTER['alternate'] = '';

/*

	$CUSTOMHEADER, CUSTOMFOOTER and $CUSTOMPAGES are deprecated.
	Default custom-pages can be assigned in theme.xml

 */



?>