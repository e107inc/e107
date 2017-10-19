<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Bootstrap 3 Theme for e107 v2.x.
 */

if(!defined('e107_INIT'))
{
	exit;
}

define("BOOTSTRAP", 3);
define("FONTAWESOME", 4);
define('VIEWPORT', "width=device-width, initial-scale=1.0");

// CDN provider for Bootswatch.
$cndPref = e107::pref('theme', 'cdn', 'cdnjs');
$bootswatch = e107::pref('theme', 'bootswatch', false);

switch($cndPref)
{
	case "jsdelivr":
		if($bootswatch)
		{
			e107::css('url', 'https://cdn.jsdelivr.net/bootswatch/3.3.7/' . $bootswatch . '/bootstrap.min.css');
		}
		break;

	case "cdnjs":
	default:
		if($bootswatch)
		{
			e107::css('url', 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.3.7/' . $bootswatch . '/bootstrap.min.css');
		}
		break;
}

/* @example prefetch  */
//e107::link(array('rel'=>'prefetch', 'href'=>THEME.'images/browsers.png'));

e107::js("footer-inline", 	"$('.e-tip').tooltip({container: 'body'});"); // activate bootstrap tooltips.

// Legacy Stuff.
define('OTHERNEWS_COLS',false); // no tables, only divs. 
define('OTHERNEWS_LIMIT', 3); // Limit to 3. 
define('OTHERNEWS2_COLS',false); // no tables, only divs. 
define('OTHERNEWS2_LIMIT', 3); // Limit to 3. 
// define('COMMENTLINK', 	e107::getParser()->toGlyph('fa-comment'));
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
	
	if($style == 'navdoc' || $style == 'none')
	{
		echo $text;
		return;
	}
	
	/*
	if($id == 'wm') // Example - If rendered from 'welcome message' 
	{
		
	}
	
	if($id == 'featurebox') // Example - If rendered from 'featurebox' 
	{
		
	}	
	*/
	
	
	if($style == 'jumbotron')
	{
		echo '<div class="jumbotron">
      	<div class="container">';
        	if(!empty($caption))
	        {
	            echo '<h1>'.$caption.'</h1>';
	        }
        echo '
        	'.$text.'
      	</div>
    	</div>';	
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

	if($style == 'portfolio')
	{
		 echo '
		 <div class="col-lg-4 col-md-4 col-sm-6">
            '.$text.'
		</div>';	
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
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{SITEURL}">{BOOTSTRAP_BRANDING}</a>
        </div>
        <div class="navbar-collapse collapse {BOOTSTRAP_NAV_ALIGN}">
        	{NAVIGATION=main}
         	{BOOTSTRAP_USERNAV: placement=top}
        </div><!--/.navbar-collapse -->
      </div>
    </div>

  
	
';

// applied after every layout. 
$LAYOUT['_footer_'] = '<hr>
</div> <!-- /container -->
{SETSTYLE=default}
<footer>
	<div class="container">
		<div class="row">

			<div>
				<div class="col-lg-6">
					{MENU=100}
				</div>
				<div class="col-lg-6">
					{MENU=101}
				</div>
			</div>

			<div>
				<div class="col-sm-12 col-lg-4">
					{MENU=102}
				</div>

				<div class="col-sm-12 col-lg-8">
					{MENU=103}
				</div>
			</div>

			<div >
				<div class="col-lg-12">
					{MENU=104}
				</div>
			</div>

			<div>
				<div class="col-lg-6">
					{MENU=105}
					{NAVIGATION=footer}
					{MENU=106}
				</div>
				<div class="col-lg-6 text-right">
					{XURL_ICONS: size=2x}
					{BOOTSTRAP_USERNAV: placement=bottom&dir=up}
				</div>
			</div>

			<div>
				<div class="col-lg-12">
					{MENU=107}
				</div>
			</div>

			<div>
				<div id="sitedisclaimer" class="col-lg-12 text-center">
					<small >{SITEDISCLAIMER}</small>
				</div>
			</div>

		</div>	 <!-- /row -->
	</div> <!-- /container -->
</footer>
';



// $LAYOUT is a combined $HEADER and $FOOTER, automatically split at the point of "{---}"

$LAYOUT['jumbotron_home'] =  <<<TMPL
  <!-- Main jumbotron for a primary marketing message or call to action -->

  <div class="container">
     {ALERTS}
  </div>

    {SETSTYLE=jumbotron}

	{WMESSAGE=force}   

	{SETSTYLE=default}
	<div class="container">	

	{MENU=1}
	
	{---}
	
	</div>
    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
      {SETSTYLE=col-md-4}
	  {MENU=2}
	  {MENU=3}
	  {MENU=4}
      </div>

       <div class="row">
      {SETSTYLE=col-md-4}
	  {MENU=5}
	  {MENU=6}
	  {MENU=7}
      </div>
      {SETSTYLE=default}
      <div class="row" >
			<div>
				<div class="col-lg-6">
					{MENU=8}
				</div>
				<div class="col-lg-6">
					{MENU=9}
				</div>
			</div>

			<div>
				<div class="col-sm-12 col-lg-4">
					{MENU=10}
				</div>

				<div class="col-sm-12 col-lg-8">
					{MENU=11}
				</div>
			</div>

			<div>
				<div class="col-sm-12 col-lg-8">
					{MENU=12}
				</div>

				<div class="col-sm-12 col-lg-4">
					{MENU=13}
				</div>
			</div>

			<div >
				<div class="col-lg-12">
					{MENU=14}
				</div>
			</div>
	 </div>
TMPL;

//TODO Add {GALLERY_PORTFOLIO}  to portfolio_menu.php 
$LAYOUT['modern_business_home'] =  <<<TMPL


<!-- Main jumbotron for a primary marketing message or call to action -->
    {SETSTYLE=none}

	{FEATUREBOX}   
	
	<div class="container">	
	{ALERTS}
<!-- Start Menu 1 --> 
	{MENU=10}
<!-- End Menu 1 --> 
	</div>
	
	<div class="section">
	    <div class="container">
	      <!-- Example row of columns -->
	      <div class="row">
	      {SETSTYLE=col-md-4}
		  {CMENU=jumbotron-menu-1}
		  {CMENU=jumbotron-menu-2}
		  {CMENU=jumbotron-menu-3}
	      </div>
		</div>
	</div>
		
{SETSTYLE=default}

	<div class="section-colored text-center">
      <div class="container">
        <div class="row">
            <div class="col-lg-12">
            	{WMESSAGE}   
          </div>
        </div><!-- /.row -->
      </div><!-- /.container -->

  	</div><!-- /.section-colored -->

	
	
	<div class="section">

      <div class="container">

        <div class="row">
          <div class="col-lg-12 text-center">
            <h2>Display Some Work on the Home Page Portfolio</h2>
            <hr>
          </div>
          
		  {SETSTYLE=portfolio}
		  {SETIMAGE: w=400&h=400&crop=1}
		  {GALLERY_PORTFOLIO: placeholder=1&limit=6}   
		  
        </div><!-- /.row -->

      </div><!-- /.container -->

    </div><!-- /.section -->



{SETSTYLE=none}

	<div class="section-colored">

		<div class="container">

			<div class="row">
			
        		{CMENU=feature-menu-1}
				
			</div>
			
		</div><!-- /.container -->

	</div><!-- /.section-colored -->


	
	 <div class="section">
	
		<div class="container">
		
			<div class="row">
			
				{CMENU=feature-menu-2}
				
			</div>
			
		</div><!-- /.container -->
	
	</div><!-- /.section -->



	<div class="container">

		<div class="row well">
      
        	{CMENU=feature-menu-3}
		
		</div><!-- /.row -->

	</div><!-- /.container -->






	 <div class="container">
	{---}



TMPL;






$LAYOUT['jumbotron_full'] = '
   
	{SETSTYLE=default}
	<div class="container">	
	{ALERTS}
   	 {MENU=1}
	{---}
	
	</div>
    <div class="container">
   
     

	';



$LAYOUT['jumbotron_sidebar_right'] =  '
   
	{SETSTYLE=default}
	<div class="container">	
	{ALERTS}
		<div class="row">
   			<div class="col-xs-12 col-md-8">	
   		
				{---}
	
 			</div>
        	<div id="sidebar" class="col-xs-12 col-md-4">
        	{SETSTYLE=menu}
        		{MENU=1}
        	</div>
      </div>
	
	</div>
    <div class="container">
           {SETSTYLE=default}
      <div class="row" >
			<div>
				<div class="col-lg-6">
					{MENU=2}
				</div>
				<div class="col-lg-6">
					{MENU=3}
				</div>
			</div>

			<div>
				<div class="col-sm-12 col-lg-4">
					{MENU=4}
				</div>

				<div class="col-sm-12 col-lg-8">
					{MENU=5}
				</div>
			</div>

			<div>
				<div class="col-sm-12 col-lg-8">
					{MENU=6}
				</div>

				<div class="col-sm-12 col-lg-4">
					{MENU=7}
				</div>
			</div>

			<div >
				<div class="col-lg-12">
					{MENU=8}
				</div>
			</div>
	 </div>
	 </div>
  <div class="container">
	';





/* XXX EVERYTHING BELOW THIS POINT IS UNUSED FOR NOW */
/**
* $HEADER AND $FOOTER are deprecated.
*/


/*

	$CUSTOMHEADER, CUSTOMFOOTER and $CUSTOMPAGES are deprecated.
	Default custom-pages can be assigned in theme.xml

 */

/*
 
$LAYOUT['docs'] = <<<TMPL

  <!-- Navbar
    ================================================== -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="./index.html">Bootstrap</a>
          <div class="nav-collapse collapse">
             {NAVIGATION=main}
          </div>
        </div>
      </div>
    </div>

<!-- Subhead
================================================== -->
<header class="jumbotron subhead" id="overview">
  <div class="container">
    <h1>{PAGE_CHAPTER_NAME}</h1>
    <p class="lead">{PAGE_CHAPTER_DESCRIPTION}</p>
  </div>
</header>


  <div class="container">

    <!-- Docs nav
    ================================================== -->
    <div class="row">
    
      <div class="span3 bs-docs-sidebar">
      {SETSTYLE=navdoc}
	  {PAGE_NAVIGATION: template=navdocs&auto=1}
      </div>
		{SETSTYLE=doc}
	  
      <div class="span9">


		{---}


 	</div>
	  </div>
 </div>

    <!-- Footer
    ================================================== -->
    <footer class="footer">
      <div class="container">
        <p>{SITEDISCLAIMER}</p>
        <!--
        <ul class="footer-links">
          <li><a href="http://blog.getbootstrap.com">Blog</a></li>
          <li class="muted">&middot;</li>
          <li><a href="https://github.com/twitter/bootstrap/issues?state=open">Issues</a></li>
          <li class="muted">&middot;</li>
          <li><a href="https://github.com/twitter/bootstrap/blob/master/CHANGELOG.md">Changelog</a></li>
        </ul>
        -->
      </div>
    </footer> 
 
 
 
TMPL;
 
 
 */
 
 
 
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