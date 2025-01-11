<?php
if ( ! defined('e107_INIT')) { exit(); }
define("BOOTSTRAP", 	3);
define("FONTAWESOME", 	4);
define("VIEWPORT", "width=device-width, initial-scale=1.0");
// Multilinguage
e107::lan('theme');
// Bootstrap + Font Icons + IE Fixes
e107::css('theme','maw/css/theme.min.css');
e107::css('url','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
e107::css('theme','maw/css/ie10-viewport-bug-workaround.css');
e107::js('url','https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js','','2','<!--[if lt IE 9]>','');
e107::js('url','https://oss.maxcdn.com/respond/1.4.2/respond.min.js','','2','','<![endif]-->');
e107::js('url','https://cdnjs.cloudflare.com/ajax/libs/jquery-migrate/1.4.1/jquery-migrate.min.js', 'jquery', 2);
e107::js('url','https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', 'jquery', 2);
// Options
define("THEME_DISCLAIMER", "<br /><i>".MAW_THEME_1."</i>");
define("USER_WIDTH", "width:100%"); 
define ("NEXTPREV_NOSTYLE", "TRUE");
define("IMODE", "light");

$LAYOUT['default'] = '
    <div class="navbar-wrapper">
      <div class="container">
        <nav class="navbar navbar-default navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="'.e_HTTP.'">{SITENAME}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                {NAVIGATION=main}
            </div>
          </div>
        </nav>
      </div>
    </div>
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
        <li data-target="#myCarousel" data-slide-to="3"></li>
        <li data-target="#myCarousel" data-slide-to="4"></li>
      </ol>
      <div class="carousel-inner" role="listbox">
        <div class="item active">
          <img class="slader-img" src=" '.THEME.'images/carousel/img01.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img02.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img03.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img04.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img05.jpg " alt="">
        </div>
      </div>
      <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
    <div class="container page">
      <div class="row">
        <div class="col-md-8">
            {---}
        </div>
        <div class="col-md-4">
            {MENU=1}
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 col-sm-6">
            {MENU=2}
        </div>
        <div class="col-md-4 col-sm-6">
            {MENU=3}
        </div>
        <div class="col-md-4 col-sm-12">
            {MENU=4}
        </div>
      </div>
      <div class="row">
        <aside class="footer-left col-md-1">'.MAW_THEME_2.'</aside>
        <aside class="footer-right col-md-11">
            {SITEDISCLAIMER}
            {THEME_DISCLAIMER}
        </aside>
      </div>
    </div>
';

$LAYOUT['default-home'] = '
    <div class="navbar-wrapper">
      <div class="container">
        <nav class="navbar navbar-default navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="'.e_HTTP.'">{SITENAME}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                {NAVIGATION=main}
            </div>
          </div>
        </nav>
      </div>
    </div>
    <div id="myCarousel" class="carousel slide" data-ride="carousel">
      <!-- Indicators -->
      <ol class="carousel-indicators">
        <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
        <li data-target="#myCarousel" data-slide-to="1"></li>
        <li data-target="#myCarousel" data-slide-to="2"></li>
        <li data-target="#myCarousel" data-slide-to="3"></li>
        <li data-target="#myCarousel" data-slide-to="4"></li>
      </ol>
      <div class="carousel-inner" role="listbox">
        <div class="item active">
          <img class="slader-img" src=" '.THEME.'images/carousel/img01.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img02.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img03.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img04.jpg " alt="">
        </div>
        <div class="item">
          <img class="slader-img" src=" '.THEME.'images/carousel/img05.jpg " alt="">
        </div>
      </div>
      <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
      </a>
      <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
      </a>
    </div>
    <div class="container page">
      <div class="row">
        <div class="col-md-4 col-sm-6">
            {MENU=2}
        </div>
        <div class="col-md-4 col-sm-6">
            {MENU=3}
        </div>
        <div class="col-md-4 col-sm-12">
            {MENU=4}
        </div>
      </div>
      <div class="row">
        <div class="col-md-8">
            {---}
        </div>
        <div class="col-md-4">
            {MENU=1}
        </div>
      </div>
      <div class="row">
        <aside class="footer-left col-md-1">'.MAW_THEME_2.'</aside>
        <aside class="footer-right col-md-11">
            {SITEDISCLAIMER}
            {THEME_DISCLAIMER}
        </aside>
      </div>
    </div>
';

$LAYOUT['simple-page'] = '
    <div class="navbar-wrapper">
      <div class="container">
        <nav class="navbar navbar-default navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="'.e_HTTP.'">{SITENAME}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                {NAVIGATION=main}
            </div>
          </div>
        </nav>
      </div>
    </div>
    <div class="header"></div>
    <div class="container page">
      <div class="row">
        <div class="col-md-8">
            {---}
        </div>
        <div class="col-md-4">
            {MENU=1}
        </div>
      </div>
      <div class="row">
        <div class="col-md-4 col-sm-6">
            {MENU=2}
        </div>
        <div class="col-md-4 col-sm-6">
            {MENU=3}
        </div>
        <div class="col-md-4 col-sm-12">
            {MENU=4}
        </div>
      </div>
      <div class="row">
        <aside class="footer-left col-md-1">'.MAW_THEME_2.'</aside>
        <aside class="footer-right col-md-11">
            {SITEDISCLAIMER}
            {THEME_DISCLAIMER}
        </aside>
      </div>
    </div>
';

$LAYOUT['wide-page'] = '
    <div class="navbar-wrapper">
      <div class="container">
        <nav class="navbar navbar-default navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="'.e_HTTP.'">{SITENAME}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                {NAVIGATION=main}
            </div>
          </div>
        </nav>
      </div>
    </div>
    <div class="header"></div>
    <div class="container page">
      <div class="row">
        <div class="col-md-12">
            {---}
        </div>
      </div>
      <div class="row">
        <aside class="footer-left col-md-1">'.MAW_THEME_2.'</aside>
        <aside class="footer-right col-md-11">
            {SITEDISCLAIMER}
            {THEME_DISCLAIMER}
        </aside>
      </div>
    </div>
';	
// News Categories Menu
define('NEWSCAT_COLS',false);			
// Othernew + Othernus2
define('OTHERNEWS_COLS',false); // no tables, only divs. 
define('OTHERNEWS_LIMIT', 10); // Limit to 5. 
define('OTHERNEWS2_COLS',false); // no tables, only divs. 
define('OTHERNEWS2_LIMIT', 5); // Limit to 5. 
// Comments
define('COMMENTLINK', e107::getParser()->toGlyph('fa-comment'));
define('COMMENTOFFSTRING', '');
// Personal Messages Menu
define("PM_INBOX_ICON", "<i class='fa fa-envelope' style='margin-right:3px;'></i>");
define("PM_OUTBOX_ICON", "<i class='fa fa-envelope-o' style='margin-right:3px;'></i>");
define("BULLET", e107::getParser()->toGlyph('fa-check'));
//	Table style
function tablestyle($caption, $text, $mode){
	global $style;
    switch ($style) {
        case "no-caption":
        echo "<div class='menus ".$mode." panel'><div class='text clearfix'>$text</div></div>\n";
        break;
        default:
		echo "<div class='menus ".$mode." panel'><h4 class='menus-caption'>$caption</h4>\n<div class='text clearfix'>$text</div></div>\n";
        break;
	}
 }   
?>