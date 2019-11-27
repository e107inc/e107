<?php

if ( ! defined('e107_INIT')) { exit(); }

e107::lan('theme');

e107::meta('viewport', 'width=device-width, initial-scale=1.0');
//e107::meta('apple-mobile-web-app-capable','yes');

//e107::library('load', 'bootstrap');

define('BOOTSTRAP', 3);

//e107::js('theme', 'js/theme.js');

// e107::css('theme','assets/css/style.css');
// e107::css('url', 'external url ');


// Custom Shortcodes. 
//$register_sc[]='BLANK';


function tablestyle($caption, $text, $mode='') 
{
    global $style;
	
	if($mode == 'wmessage')
	{
		$style = '';	
	}
	
    switch($style) 
    {

        case 'home': 
            echo $caption;
			echo $text; 
		break;

		case 'menu': 
            echo $caption;
			echo $text; 
		break;

		case 'full': 
            echo $caption;
			echo $text; 
		break;

		default: 
        	echo $caption;
			echo $text; 
		break;
	}
	
}

// IMPORTANT: make sure there are no characters after <<<TMPL or TMPL;

// DEFAULT

$HEADER['default'] = <<<TMPL

{SETSTYLE=default}
{SETIMAGE: w=0}
    
TMPL;



$FOOTER['default'] = <<<TMPL

{SETSTYLE=menu}
{MENU=1}

TMPL;

               

// HOME page

$HEADER['home'] = <<<TMPL

	{SETSTYLE=home}
	{SETIMAGE: w=0} 
	
TMPL;


$FOOTER['home'] = <<<TMPL


TMPL;


// FULL PAGE (no menus) - eg. Forum

$HEADER['full'] = <<<TMPL

       {SETSTYLE=full}
	   {SETIMAGE: w=0} 
	   
TMPL;


$FOOTER['full'] = <<<TMPL

   
TMPL;




// News item styling
$NEWSSTYLE = '
{NEWSTITLE}
{NEWSAUTHOR}
{NEWSDATE=short}
{NEWSIMAGE}
{NEWSBODY} {EXTENDED}

';

// Comment Styling
$COMMENTSTYLE = '
{AVATAR} 
{USERNAME}
{REPLY}
{TIMEDATE}
{COMMENT} 
';

// news.php?cat.1
$NEWSLISTSTYLE = '
{NEWSTITLE}
{NEWSDATE=short}
{NEWSAUTHOR}
{NEWSIMAGE}
{NEWSBODY} 
{EXTENDED}
{EMAILICON} 
{PRINTICON}
{PDFICON}
{ADMINOPTIONS}
{NEWSCOMMENTS}
';

$NEWSARCHIVE ='
{ARCHIVE_BULLET}
{ARCHIVE_LINK}
{ARCHIVE_AUTHOR}
{ARCHIVE_DATESTAMP}
{ARCHIVE_CATEGORY}
';
//Render news categories on the bottom of the page
$NEWSCAT = '
{NEWSCATEGORY}
{NEWSCAT_ITEM}
';
//Loop for news items in category
$NEWSCAT_ITEM = '
{NEWSTITLELINK}
    
';

?>