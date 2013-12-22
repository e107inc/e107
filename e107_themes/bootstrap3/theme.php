<?php
/**
 * Bootstrap 3 Theme for e107 v2.x
 */
if (!defined('e107_INIT')) { exit; }

define("BOOTSTRAP", 	3);
define("FONTAWESOME", 	4);
define('VIEWPORT', 		"width=device-width, initial-scale=1.0");

e107::js("url", 		"//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js");
e107::css('url', 		'//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css');
e107::css('url', 		"//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css");
					
define('OTHERNEWS_COLS',false); // no tables, only divs. 
define('OTHERNEWS_LIMIT', 3); // Limit to 3. 
define('OTHERNEWS2_COLS',false); // no tables, only divs. 
define('OTHERNEWS2_LIMIT', 3); // Limit to 3. 

define('PRE_EXTENDEDSTRING', '<br />');


function tablestyle($caption, $text, $mode='') 
{
	global $style;
	
	$type = $style;
	if(empty($caption))
	{
		$type = 'box';
	}
	
	if($style == 'navdoc')
	{
		echo $text;
		return;
	}
	
	
	if($style == 'jumbotron')
	{
		echo '<div class="jumbotron">
      	<div class="container">
        	<h1>'.$caption.'</h1>
        	<p>'.$text.'</p>
      	</div>
    	</div>';	
		return;
	}
	
	if($style == 'col-md-4')
	{
		echo ' <div class="col-md-4">
          <h2>'.$caption.'</h2>
          '.$text.'
        </div>';
		return;	
		
	}
		
	
	if($mode == 'loginbox') // Login Box Style. 
	{
		 echo '<div class="well sidebar-nav">
		 <ul class="nav nav-list"><li class="nav-header">'.$caption.'</li></ul>
		 
           '.$text.'
			
        </div><!--/.well -->';
          return;
	}
			
	if($mode == 'login_page')
	{
		$type = 'no_caption';	
	}
	
	switch($type) 
	{
		// Default Menu/Side-Panel Style
		case 'menu' :
			echo '<div class="well sidebar-nav">
		 <ul class="nav nav-list"><li class="nav-header">'.$caption.'</li></ul>
		 
           '.$text.'
			
        </div><!--/.well -->';
		break;
		
		case 'span4' :
			echo $text; 
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
		
		case 'no_caption':
			echo $text;
		break;
	
		default: // Main Content Style. 
			echo '
				<h2>'.$caption.'</h2>
					<p>
						'.$text.'
					</p>
				
			';
		break;
	}
}



// $LAYOUT is a combined $HEADER and $FOOTER, automatically split at the point of "{...}"
$LAYOUT['jumbotron'] = '
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">{SITENAME}</a>
        </div>
        <div class="navbar-collapse collapse">
        	{NAVIGATION=main}
         	{BOOTSTRAP_USERNAV}
           
		   <!-- 
		    <form class="navbar-form navbar-right" role="form">
		    <div class="form-group">
              <input type="text" placeholder="Email" class="form-control">
            </div>
            <div class="form-group">
              <input type="password" placeholder="Password" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Sign in</button>
          </form>
         -->
         
        </div><!--/.navbar-collapse -->
      </div>
    </div>

    <!-- Main jumbotron for a primary marketing message or call to action -->
    {SETSTYLE=jumbotron}
	{WMESSAGE}
	 {SETSTYLE=default}
	 <div class="container">
	 {ALERTS}
	 
	{---}
	
	
	</div>
    <div class="container">
      <!-- Example row of columns -->
      <div class="row">
      {SETSTYLE=col-md-4}
	  {CMENU=jumbotron-menu-1}
	  {CMENU=jumbotron-menu-2}
	  {CMENU=jumbotron-menu-3}
      </div>

      <hr>

      <footer>
       {SITEDISCLAIMER}
      </footer>
    </div> <!-- /container -->

';



















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