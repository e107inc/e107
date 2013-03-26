<?php

if ( ! defined('e107_INIT')) { exit(); }

e107::lan('theme','English');
e107::js('core','bootstrap/js/bootstrap.min.js');
e107::css('core','bootstrap/css/bootstrap-responsive.min.css');
//e107::js('theme', 'js/theme.js');
//e107::css('url', 'external url ');
//define("VIEWPORT","width=device-width, initial-scale=1.0");

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



// DEFAULT

$HEADER['default'] = '
{SETSTYLE=default}
{SETIMAGE: w=0}
    
';

$FOOTER['default'] = ' 
{SETSTYLE=menu}
{MENU=1}
';                 

               



// HOME page

$HEADER['home'] = '
	{SETSTYLE=hero}
	{SETIMAGE: w=0} 
';

	
$FOOTER['home'] = '	

';


// FULL PAGE (no menus) - eg. Forum

$HEADER['full'] = '
       {SETSTYLE=full}
	   {SETIMAGE: w=0} 
';

$FOOTER['full'] = '

   
';




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