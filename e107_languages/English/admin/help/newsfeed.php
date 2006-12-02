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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/newsfeed.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "You can retrieve and parse other site's backend RSS news feeds and display them on your own site from here.<br />Enter the full path URL to the backend (ie http://e107.org/news.xml). You can add a path to an image if you don't like the default one, or it isn't defined. You can activate and de-activate the backend if the site goes down for instance.<br /><br />To see the headlines on your site, make sure the  headlines_menu is activated from your menus page.";

$ns -> tablerender("Headlines", $text);
?>