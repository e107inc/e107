<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
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
global $news2;
return "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' style='border:0px' alt='' />";
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