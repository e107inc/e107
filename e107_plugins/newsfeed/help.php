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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/help.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-28 18:23:56 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
$text = "
	<b>&raquo;</b> <u>Feed Name:</u>
	The identifying name of the feed, can be anything you like.
	<br /><br />
	<b>&raquo;</b> <u>URL to rss feed:</u>
	The address of the rss feed
	<br /><br />
	<b>&raquo;</b> <u>Path to image:</u>
	If the feed has an image defined in it, enter 'default' to use it. To use your own image, enter the full path to it. Leave blank to use no image at all.
	<br /><br />
	<b>&raquo;</b> <u>Description:</u>
	Enter a short description of the feed, or 'default' to use the description defined in the feed (if there is one).
	<br /><br />
	<b>&raquo;</b> <u>Update interval in seconds:</u>
	The amount of seconds that elapse before the feed is updated, for example, 1800: 30 minutes, 3600: an hour.
	<br /><br />
	<b>&raquo;</b> <u>Activation:</u>
	where you want the feed results to be displayed, to see menu feeds you will need to activate the newsfeeds menu on the <a href='".e_ADMIN."menus.php'>menus page</a>.
	<br /><br />For a good list of available feeds, see <a href='http://www.syndic8.com/' rel='external'>syndic8.com</a> or <a href='http://feedfinder.feedster.com/index.php' rel='external'>feedster.com</a>.
	";
$ns->tablerender("PM Help", $text);
?>