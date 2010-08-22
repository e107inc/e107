<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$news_archive_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
		
/*
SC_BEGIN ARCHIVE_BULLET
$bullet = '';
if(defined('BULLET'))
{
	$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
}
elseif(file_exists(THEME.'images/bullet2.gif'))
{
	$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
}
return $bullet;
SC_END

SC_BEGIN ARCHIVE_LINK
global $news2;
return "<a href='news.php?item.".$news2['news_id']."'>".$news2['news_title']."</a>";
SC_END


SC_BEGIN ARCHIVE_AUTHOR
global $news2;
return "<a href='".e_BASE."user.php?id.".$news2['user_id']."'>".$news2['user_name']."</a>";
SC_END


SC_BEGIN ARCHIVE_DATESTAMP
global $news2;
return $news2['news_datestamp'];
SC_END

SC_BEGIN ARCHIVE_CATEGORY
global $news2;
return $news2['category_name'];
SC_END

*/
?>