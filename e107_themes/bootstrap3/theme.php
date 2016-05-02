<?php
/**
 * Bootstrap 3 Theme for e107 v2.x
 */
if (!defined('e107_INIT')) { exit; }

define("BOOTSTRAP", 	3);
define("FONTAWESOME", 	4);
define('VIEWPORT', 		"width=device-width, initial-scale=1.0");


/* @see https://www.cdnperf.com */
// Warning: Some bootstrap CDNs are not compiled with popup.js
// use https if e107 is using https.

e107::js("url", 			"https://cdn.jsdelivr.net/bootstrap/3.3.6/js/bootstrap.min.js", 'jquery', 2);

if($bootswatch = e107::pref('theme', 'bootswatch',false))
{
	e107::css('url', 'https://maxcdn.bootstrapcdn.com/bootswatch/3.3.6/'.$bootswatch.'/bootstrap.min.css');
}
else
{
	e107::css('url', 'https://cdn.jsdelivr.net/bootstrap/3.3.6/css/bootstrap.min.css');
}

e107::css('url',    'https://cdn.jsdelivr.net/fontawesome/4.5.0/css/font-awesome.min.css');





/* @example prefetch  */
//e107::link(array('rel'=>'prefetch', 'href'=>THEME.'images/browsers.png'));



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




$HEADER['default'] = '
<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="'.SITEURL.'">{SITENAME}</a>
          <div class="nav-collapse collapse">
           {NAVIGATION=main}
           <div class="pull-right">{BOOTSTRAP_USERNAV}</div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
<div class="container-fluid">
	<div class="row-fluid">
		 <div class="span3">
           {NAVIGATION|s=side}          
          {SETSTYLE=menu}
          {MENU=1}
        </div><!--/span-->
		<div class="span9">
		 {SETSTYLE=default}
		 	{WMESSAGE}
';



$FOOTER['default'] = '
		 {SETSTYLE=span4}
      	
		</div><!--/span-->
	</div><!--/row-->

<hr>

<footer class="center"> 
	{SITEDISCLAIMER}
</footer>

</div><!--/.fluid-container-->';


$HEADER['default-home'] = $HEADER['default'];


$FOOTER['default-home'] = '
	
		 {SETSTYLE=span4}
		 
		 <div class="row-fluid">
            {MENU=2}
      	</div><!--/row-->
		 <div class="row-fluid">
            {MENU=3}
      	</div><!--/row-->	
      	
		</div><!--/span-->
	</div><!--/row-->

<hr>

<footer class="center"> 
		{SITEDISCLAIMER} 
</footer>

</div><!--/.fluid-container-->';










// HERO http://twitter.github.com/bootstrap/examples/hero.html
//FIXME insert shortcodes while maintaining only bootstrap classes. 

$HEADER['hero'] = '

	 <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Project name</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Action</a></li>
                  <li><a href="#">Another action</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>
            </ul>
            <form class="navbar-form pull-right">
              <input class="span2" type="text" placeholder="Email">
              <input class="span2" type="password" placeholder="Password">
              <button type="submit" class="btn">Sign in</button>
            </form>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">';
	  
	  /*
        <h1>Hello, world!</h1>
        <p>This is a template for a simple marketing or informational website. It includes a large callout called the hero unit and three supporting pieces of content. Use it as a starting point to create something more unique.</p>
        <p><a class="btn btn-primary btn-large">Learn more &raquo;</a></p>
     */
     
     
//FIXME insert shortcodes while maintaining classes. 
$FOOTER['hero'] = '
 </div>

      <!-- Example row of columns -->
      <div class="row">
        <div class="span4">
          <h2>Heading</h2>
          <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
        <div class="span4">
          <h2>Heading</h2>
          <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
       </div>
        <div class="span4">
          <h2>Heading</h2>
          <p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
          <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
      </div>

      <hr>

      <footer>
        <p>&copy; Company 2012</p>
      </footer>

    </div> <!-- /container -->';


// Marketing Narrow - http://twitter.github.com/bootstrap/examples/marketing-narrow.html
//FIXME insert shortcodes while maintaing classes. 

$HEADER['marketing-narrow'] = '
 <div class="container-narrow">

      <div class="masthead">
        <ul class="nav nav-pills pull-right">
          <li class="active"><a href="#">Home</a></li>
          <li><a href="#">About</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
        <h3 class="muted">Project name</h3>
      </div>

      <hr>

      <div class="jumbotron">
        <h1>Super awesome marketing speak!</h1>
        <p class="lead">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>
        <a class="btn btn-large btn-success" href="#">Sign up today</a>   
';


//FIXME insert shortcodes while maintaing classes. 

$FOOTER['marketing-narrow'] = '
</div>

      <hr>

      <div class="row-fluid marketing">
        <div class="span6">
          <h4>Subheading</h4>
          <p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p>

          <h4>Subheading</h4>
          <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>

          <h4>Subheading</h4>
          <p>Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
        </div>

        <div class="span6">
          <h4>Subheading</h4>
          <p>Donec id elit non mi porta gravida at eget metus. Maecenas faucibus mollis interdum.</p>

          <h4>Subheading</h4>
          <p>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</p>

          <h4>Subheading</h4>
          <p>Maecenas sed diam eget risus varius blandit sit amet non magna.</p>
        </div>
      </div>

      <hr>

      <div class="footer">
        <p>&copy; Company 2012</p>
      </div>

    </div> <!-- /container -->
    
';







/*

	$CUSTOMHEADER, CUSTOMFOOTER and $CUSTOMPAGES are deprecated.
	Default custom-pages can be assigned in theme.xml

 */

 
 
$HEADER['docs'] = <<<TMPL

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





TMPL;


$FOOTER['docs'] = <<<TMPL
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
