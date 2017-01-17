<?php
/**
 * Bootstrap 3 Theme for e107 v2.x
 * Landing Zero Theme by www.bootstrapzero.com adapted for e107 CMS.
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 */
 
if (!defined('e107_INIT')) { exit; }

define("BOOTSTRAP", 	3);
define("FONTAWESOME", 	4);
define('VIEWPORT', 		"width=device-width, initial-scale=1.0");
 
e107::lan('theme');

$cndPref = e107::pref('theme', 'cdn','cdnjs');
 
switch($cndPref)
{
	case "jsdelivr":
		e107::css('url', 'https://cdn.jsdelivr.net/bootstrap/3.3.7/css/bootstrap.min.css');
		e107::css('url',    'https://cdn.jsdelivr.net/fontawesome/4.7.0/css/font-awesome.min.css');
		e107::js("footer", "https://cdn.jsdelivr.net/bootstrap/3.3.6/js/bootstrap.min.js", 'jquery');
        e107::js("footer", "https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js", 'jquery');


		break;			
	/*case "localhost": //@todo  add back once correct core path is determined.
		e107::js("theme", "js/bootstrap.min.js", 'jquery');
		e107::js("theme", "js/jquery.easing.min.js", 'jquery');
		break;	*/
	case "cdnjs":
	default:
		e107::css('url', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css');
		e107::css('url', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		e107::js("footer", "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js", 'jquery', 2);
	    e107::js("footer", "https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js", 'jquery', 2);


}


e107::js("theme", "js/wow.js", 'jquery');
e107::js("theme", "js/scripts.js", 'jquery');
 
e107::css('theme', 'css/animate.min.css');
e107::css('theme', 'css/ionicons.min.css');
e107::css('theme', 'css/styles.css');


$videomobilehide = e107::pref('landingzero', 'videomobilehide'); 

$foot1 = e107::pref('landingzero', 'foot1');
$foot2 = e107::pref('landingzero', 'foot2');
$foot3 = e107::pref('landingzero', 'foot3');
 
e107::js("footer-inline", 	"$('.e-tip').tooltip({container: 'body'})"); // activate bootstrap tooltips. 

// Legacy Stuff.
define('OTHERNEWS_COLS',false); // no tables, only divs. 
define('OTHERNEWS_LIMIT', 3); // Limit to 3. 
define('OTHERNEWS2_COLS',false); // no tables, only divs. 
define('OTHERNEWS2_LIMIT', 3); // Limit to 3. 
define('COMMENTLINK', 	e107::getParser()->toGlyph('fa-comment'));
define('COMMENTOFFSTRING', '');

define('PRE_EXTENDEDSTRING', '<br />');

/**
 * @param string $caption
 * @param string $text
 * @param string $id : id of the current render
 * @param array $info : current style and other menu data. 
 */
function tablestyle($caption, $text, $id='', $info=array()) 
{
//	global $style; // no longer needed. 
	
	$style = $info['setStyle'];
	
	echo "<!-- tablestyle: style=".$style." id=".$id." -->\n\n";

	$type = $style;
	if(empty($caption))
	{
		$type = 'box';
	}
	
	if($id == 'wm') // Example - If rendered from 'welcome message' 
	{
		echo '<h1 class="cursive">'.$caption.'</h1><h4>'.str_replace(array("<p>","</p>"), "", $text).'</h4>';
		return;
	} 
 
	if($style == 'col-md-4' || $style == 'col-md-6' || $style == 'col-md-8')
	{
		echo ' <div class="col-xs-12 '.$style.'">';
		
		if(!empty($caption))
		{
            echo '<h2>'.$caption.'</h2>';
		}

		echo '
          '.$text.'
        </div>';
		return;	
		
	}

	if($style === 'menu' && !empty($info['footer']) && !empty($info['text']))
	{
		$style = 'menu-footer';
	}


		
	if($style == 'menu')
	{
		echo '<div class="panel panel-default">
	  <div class="panel-heading">'.$caption.'</div>
	  <div class="panel-body">
	   '.$text.'
	  </div>
	</div>';
		return;
		
	}

	if($style == 'menu-footer')
	{
		echo '<div class="panel panel-default">
	  <div class="panel-heading">'.$caption.'</div>
	  <div class="panel-body">
	   '.$info['text'].'
	  </div>
	  <div class="panel-footer text-align:right">'.$info['footer'].'</div>
	</div>';
		return;

	}

	if($style == 'portfolio')
	{
		 echo '
		 <div class="col-lg-4 col-md-4 col-sm-6">
            '.$text.'
		</div>';	
		return;
	}

	if($style == 'nocaption')
	{
		echo str_replace(array("<p>","</p>"), "", $text);
		return;
	}

	if ($style == 'footercolumn')
	{ 
		echo '<h4 class="caption">' . $caption . '</h4>' . str_replace(array("<p>","</p>"), "", $text);
		return;
	}

	if ($style == 'footercolumn-12')
	{
		echo '<div class="col-xs-12 col-sm-3 footercolumn"> 
			  	<h4 class="caption">' . $caption . '</h4>' . $text . '</div>';
		return;
	}
	// default.

	if(!empty($caption))
	{
		echo '<h2 class="caption">'.$caption.'</h2>';
	}

	echo $text;


					
	return;
	
	
	
}

// applied before every layout.
$LAYOUT['_header_'] = '
    <nav id="topNav" class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand page-scroll" href="'.SITEURL.'#first"><i class="ion-ios-analytics-outline"></i>{SITENAME}</a>
            </div>
            <div class="navbar-collapse collapse" id="bs-navbar">
                {NAVIGATION=main}
                {BOOTSTRAP_USERNAV: placement=top}
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a class="page-scroll" data-toggle="modal" title="{SITETAG}" href="#aboutModal">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
';

// applied after every layout. 
$LAYOUT['_footer_'] = '<hr>
    <footer id="footer">
        <div class="container-fluid">
            <div class="row">
                {SETSTYLE=footercolumn}
                <div class="col-xs-6  col-sm-6 col-xxs-12 footercolumn">{NAVIGATION=footer}</div>
  				<div class="col-xs-6 col-sm-6 col-md-3 col-xxs-12 footercolumn">{LZ_SUBSCRIBE}</div>
				<div class="col-xs-12 col-sm-6 col-md-3 col-xxs-12 text-right"><h4>'.LAN_LZ_THEME_10.'</h4>{XURL_ICONS}</div>
            </div>
            <br/>
            <span class="pull-right text-muted small">{SITEDISCLAIMER=2016}</span>
        </div>
    </footer>
    <div id="galleryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
        	<div class="modal-body">
        		<img src="#" id="galleryImage" class="img-responsive" alt="gallery"/>
        		    <p>
        		    <br/>
        		    <button class="btn btn-primary btn-lg center-block" data-dismiss="modal" aria-hidden="true">'.LAN_LZ_THEME_05.' <i class="ion-android-close"></i></button>
        		</p>
        	</div>
        </div>
        </div>
    </div>
    {SETSTYLE=nocaption}
    {ABOUTMODAL}
';




// $LAYOUT is a combined $HEADER and $FOOTER, automatically split at the point of "{---}"
// Frontpage has to be welcome message
$LAYOUT['homepage'] =  '
    <header id="first">
	  <div class="header-content">
            <div class="inner">
					{WMESSAGE=force}
								{LANDING_TOGGLE}<a href="#one" class="btn btn-primary btn-xl page-scroll">'.LAN_LZ_THEME_01.'</a>
            </div>
        </div>
       {VIDEOBACKGROUND}
    </header>
    <section class="bg-primary" id="one">    		
        <div class="container">
            <div class="row">
              {ALERTS}
              {SETSTYLE=nocaption}
              {MENU=1}                
            </div>
        </div>
    </section>
    <section id="two">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2 class="margin-top-0 text-primary">'.LAN_LZ_THEME_04.'</h2>
                    <hr class="primary">
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
               {MENU=10}
            </div>
        </div>
    </section>

    {GALLERY_PORTFOLIO: limit=6}

    <section class="container-fluid" id="four">
        <div class="row">
            <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 col-md-4 col-md-offset-4">
                <h2 class="text-center text-primary">'.LAN_LZ_THEME_06.'</h2>
                <hr>
                {MENU=9}                
            </div>
        </div>
    </section> 
    <aside class="bg-dark">
        <div class="container text-center">
             {MENU=8}
        </div>
    </aside> 		
    <section id="last">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 col-lg-offset-2 text-center">
						<h2 class="margin-top-0 wow fadeIn">Get in Touch</h2>
						<hr class="primary">
						<p>We love feedback. Fill out the form below and we\'ll get back to you as soon as possible.</p>
					</div>
					<div class="col-lg-10 col-lg-offset-1 text-center">
						{MENU=contact/contact}
					</div>             
				</div>
			</div>
    </section> 
           {---}
';
 
$LAYOUT['full'] = '
  
{SETSTYLE=default}
<div class="container-fluid main-section">
  {ALERTS}
  {MENU=1}
  {---}
</div>';

$LAYOUT['sidebar_right'] =  '   
{SETSTYLE=default}
<div class="container-fluid main-section">
  {ALERTS}
  <div class="row">
    <div class="col-xs-12 col-sm-6 col-md-6">	      
    {---}      
    </div>
    <div id="sidebar" class="col-xs-12 col-sm-6 col-md-3">
      {SETSTYLE=menu}
      {MENU=1}
    </div>
    <div class="bg-dark col-xs-12  col-sm-6 col-md-3">
      {SETSTYLE=menu}
      {MENU=2}
    </div>    
  </div>
</div>';

$LAYOUT['sidebar_left'] =  $LAYOUT['sidebar_right'];

 
$NEWSCAT = "\n\n\n\n<!-- News Category -->\n\n\n\n
	<div style='padding:2px;padding-bottom:12px'>
	<div class='newscat_caption'>
	{NEWSCATEGORY}
	</div>
	<div style='width:100%;text-align:left'>
	{NEWSCAT_ITEM}
	</div>
	</div>
";


$NEWSCAT_ITEM = "\n\n\n\n<!-- News Category Item -->\n\n\n\n
		<div style='width:100%;display:block'>
		<table style='width:100%'>
		<tr><td style='width:2px;vertical-align:middle'>&#8226;&nbsp;</td>
		<td style='text-align:left;height:10px'>
		{NEWSTITLELINK}
		</td></tr></table></div>
";

?>
