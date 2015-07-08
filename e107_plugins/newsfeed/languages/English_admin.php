<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
 * $URL$
 * $Id$
 *
*/


//define("NFLAN_01", "Newsfeeds");
//define("NFLAN_02", "This plugin will retrieve rss feeds from other websites and display them according to your preferences");
define("NFLAN_03", "Configure newsfeeds"); // FIX USE GENERIC
define("NFLAN_04", "Newsfeeds plugin has been successfully installed. To add newsfeeds and configure, return to the main admin page and click on the newsfeeds icon in the plugin section."); // FIX USE GENERIC
//define("NFLAN_05", "Edit");
//define("NFLAN_06", "Delete");
define("NFLAN_07", "Existing Newsfeeds");
define("NFLAN_08", "Newsfeeds front page");
define("NFLAN_09", "Create newsfeed");
//define("NFLAN_10", "URL to rss feed");
define("NFLAN_10", "URL to the RSS feed.");
define("NFLAN_11", "Path to image");
define("NFLAN_12", "Activation");
define("NFLAN_13", "Nowhere (inactive)");
define("NFLAN_14", "In menu only");
//define("NFLAN_15", "Create Newsfeed");
//define("NFLAN_16", "Update Newsfeed");
define("NFLAN_17", "Enter 'default' to use the image defined in the feed. To use your own image, enter full path. Leave blank for no image.");
define("NFLAN_18", "Update interval in seconds");
define("NFLAN_19", "e.g. 3600: newsfeed will update every hour");
define("NFLAN_20", "On newsfeed main page only");
define("NFLAN_21", "In both menu and newsfeed page");
define("NFLAN_22", "Choose where you want the newsfeed displayed.");
//define("NFLAN_23", "Newsfeed added to database.");
//define("NFLAN_24", "Required field(s) left blank.");
//define("NFLAN_25", "Newsfeed updated in database.");
define("NFLAN_26", "Update Interval");
//define("NFLAN_27", "Options");
//define("NFLAN_28", "URL");
//define("NFLAN_29", "Available newsfeeds");
//define("NFLAN_30", "Feed name");
//define("NFLAN_31", "Back to newsfeed list");
//define("NFLAN_32", "No feed with that identification number can be found.");
//define("NFLAN_33", "Date published: ");
//define("NFLAN_34", "not known");
//define("NFLAN_35", "posted by ");
//define("NFLAN_36", "Description");
define("NFLAN_37", "Short description of feed. Enter 'default' to use the description defined in the feed");
//define("NFLAN_38", "Headlines");
//define("NFLAN_39", "Details");
//define("NFLAN_40", "Newsfeed deleted");
define("NFLAN_41", "No newsfeeds defined yet");

define("NFLAN_42", "<b>&raquo;</b> <u>Feed Name:</u>
	The identifying name of the feed can be anything you like.
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
	Where you want the feed results to be displayed, to see menu feeds you will need to activate the newsfeeds menu on the <a href='".e_ADMIN."menus.php'>menus page</a>.
	<br /><br />For a good list of available feeds, see <a href='http://www.syndic8.com/' rel='external'>syndic8.com</a> or <a href='http://feedfinder.feedster.com/index.php' rel='external'>feedster.com</a>");
define("NFLAN_43", "Newsfeed help");
define("NFLAN_44", "click to view");

define("NFLAN_45", "Number of items to show in menu");
define("NFLAN_46", "Number of items to show on main page");
define("NFLAN_47", "0 or blank to show all");

//define("NFLAN_48", "Unable to save raw data in database.");
define("NFLAN_49", "Unable to unserialize rss data - uses non-standard syntax");
//define("NFLAN_50", "Write to database failed: ");

// Admin log messages
//===================
define("LAN_AL_NEWSFD_01","News Feed created");
define("LAN_AL_NEWSFD_02","News Feed updated");
define("LAN_AL_NEWSFD_03","News Feed deleted");
define("LAN_AL_NEWSFD_04","");
define("LAN_AL_NEWSFD_05","");


?>
