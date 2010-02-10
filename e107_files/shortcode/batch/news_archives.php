<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/news_archives.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$news_archive_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
		
/*
SC_BEGIN ARCHIVE_BULLET
//TODO review bullet
$bullet = '';
if(defined('BULLET'))
{
	$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
}
elseif(file_exists(THEME.'images/bullet2.gif'))
{
	$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
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