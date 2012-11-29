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

    $ret .= "
    <script type='text/javascript'>
       /**
    	* Decorate all tables having e-list class
    	*/
        e107.runOnLoad( function() {
            \$\$('table.e-list').each(function(element) {
            	e107Utils.Decorate.table(element, { tr_td: 'first last' });
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

function tablestyle($caption, $text, $mod) 
{
	global $style;
	
	$type = $style;
	if(empty($caption))
	{
		$type = 'box';
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

$HEADER['default'] = '
<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">  
          {SITELOGO}
          <div class="nav-collapse collapse">
            <div class="dropdown nav pull-right navbar-text ">
            {CUSTOM=login}
            </div>
            
			<div class="dropdown nav">     
        	 {SITELINKS}
   			 </div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div><div class="container-fluid">
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