<?php
if ( ! defined('e107_INIT')) { exit(); }
/*
 * This is a 100% Pure Bootstrap Theme for e107 v2 
 */
 
define("VIEWPORT","width=device-width, initial-scale=1.0");

e107::lan('theme');
e107::js('core','bootstrap/js/bootstrap.min.js');
e107::css('core','bootstrap/css/bootstrap.min.css');
e107::css('core','bootstrap/css/bootstrap-responsive.min.css');
e107::css('core','bootstrap/css/jquery-ui.custom.css');

//$no_core_css = TRUE;

//define("STANDARDS_MODE",TRUE);

function theme_head() 
{
	return; 

	/*	
	$theme_pref = e107::getThemePref();
	
	$ret = '';

    if(THEME_LAYOUT == "alternate") // as matched by $HEADER['alternate'];
	{
        $ret .= "<!-- Include Something --> ";
	}

	if($theme_pref['_blank_example'] == 3)  // Pref from admin -> thememanager.
	{
        $ret .= "<!-- Include Something Else --> ";
	}
	
	return $ret;
	*/
}



$OTHERNEWS_STYLE = '<div class="span4">
              		<h2>{NEWSTITLE}</h2>
              		<p>{NEWSSUMMARY}</p>
              		<p><a class="btn" href="{NEWSURL}">View details &raquo;</a></p>
            		</div><!--/span-->';

$OTHERNEWS2_STYLE = '<div class="span4">
              		<h2>{NEWSTITLE}</h2>
              		<p>{NEWSSUMMARY}</p>
              		<p><a class="btn" href="{NEWSURL}">View details &raquo;</a></p>
            		</div><!--/span-->';
					
define('OTHERNEWS_COLS',false); // no tables, only divs. 
define('OTHERNEWS_LIMIT', 3); // Limit to 3. 
define('OTHERNEWS2_COLS',false); // no tables, only divs. 
define('OTHERNEWS2_LIMIT', 3); // Limit to 3. 


function tablestyle($caption, $text, $mode='') 
{
	global $style;
	
	$type = $style;
	if(empty($caption))
	{
		$type = 'box';
	}
	
	if($mode == 'wm') // Welcome Message Style. 
	{
		
		echo '<div class="hero-unit">
            <h1>'.$caption.'</h1>
            <p>'.$text.'</p>
            <p><a href="'.e_ADMIN.'admin.php" class="btn btn-primary btn-large">Go to Admin area &raquo;</a></p>
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



// TODO Convert to : default-home and default-other layouts. 

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
            <p class="navbar-text pull-right">
              Logged in as <a href="#" class="navbar-link">'.USERNAME.'</a>
            </p>
           {NAVIGATION=main}
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
<div class="container-fluid">
	<div class="row-fluid">
		 <div class="span3">
          <div class="well sidebar-nav">
            {NAVIGATION=side}
			
          </div><!--/.well -->
          {SETSTYLE=menu}
          {MENU=1}
        </div><!--/span-->
		<div class="span9">
		 {SETSTYLE=default}
';

$FOOTER['default'] = '
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
	Copyright &copy; 2008-2012 e107 Inc (e107.org)<br />
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



?>