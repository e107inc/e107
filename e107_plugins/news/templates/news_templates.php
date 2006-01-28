<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/news/templates/news_templates.php,v $
|     $Revision: 1.1.2.2 $
|     $Date: 2006-01-28 07:56:26 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

// Main news layout - posts location, pagination, location of archive etc..
if(!isset($NEWS_MAIN_LAYOUT)) {
	$NEWS_MAIN_LAYOUT = "{POSTS}\n</tr>";
}
