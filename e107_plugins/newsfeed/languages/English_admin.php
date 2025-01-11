<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - newsfeeds
 *
*/


//define("NFLAN_01", "Newsfeeds");
//define("NFLAN_02", "This plugin will retrieve rss feeds from other websites and display them according to your preferences");
//define("NFLAN_03", "Configure newsfeeds"); // not used
//define("NFLAN_04", "Newsfeeds plugin has been successfully installed. To add newsfeeds and configure, return to the main admin page and click on the newsfeeds icon in the plugin section."); // FIX USE GENERIC
//define("NFLAN_05", "Edit");
//define("NFLAN_06", "Delete");
//define("NFLAN_07", "Existing Newsfeeds");// not used
//define("NFLAN_08", "Newsfeeds front page");// not used
//define("NFLAN_09", "Create newsfeed");// not used
//define("NFLAN_10", "URL to rss feed");
//define("NFLAN_10", "URL to the RSS feed.");// not used
define("NFLAN_11", "Path to image");
define("NFLAN_12", "Activation");
define("NFLAN_13", "Nowhere (inactive)");// not used
define("NFLAN_14", "In menu only");
//define("NFLAN_15", "Create Newsfeed");
//define("NFLAN_16", "Update Newsfeed");
//define("NFLAN_17", "Enter 'default' to use the image defined in the feed. To use your own image, enter full path. Leave blank for no image.");
define("NFLAN_18", "Update interval in seconds");
define("NFLAN_19", "e.g. 3600: newsfeed will update every hour");
define("NFLAN_20", "On newsfeed main page only");
define("NFLAN_21", "In both menu and newsfeed page");
//define("NFLAN_22", "Choose where you want the newsfeed displayed.");
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
//define("NFLAN_37", "Short description of feed. Enter 'default' to use the description defined in the feed");
//define("NFLAN_38", "Headlines");
//define("NFLAN_39", "Details");
//define("NFLAN_40", "Newsfeed deleted");
//define("NFLAN_41", "No newsfeeds defined yet");

define("NFLAN_43", "Newsfeed Help");
define("NFLAN_42", "[h=4]Newsfeed Title[/h]
	Enter a name to identify the newsfeed accurately.
	[h=4]URL to RSS Feed[/h]
	The RSS provider will give you a web address (URL) for the newsfeed.
	[h=4]Path to Image[/h]
	If the provider specifies an image to use, enter 'default' to use it or choose use your own image by entering the immage address. Leave blank to use no image at all.
	[h=4]Description[/h]
	Enter a short description for the feed, or 'default' to use the description defined in the feed (if there is one).
	[h=4]Update Interval[/h]
	Enter the number of seconds before the feed is updated. 
	For example, 1800 = 30 Minutes, 3600 = 1 Hour, 86400 = 1 Day.
	[h=4]Activation[/h]
	Newsfeeds can be displayed in the menu only or on the newsfeed page. Enter the details where feeds should be displayed. To see newsfeeds in e107 menus you will need to activate the [b]Newsfeeds Menu[/b] in [link=".e_ADMIN."menus.php]Menu Manager[/link].
	[h=4]Tip[/h]
	There are many feed direcotries on the web, try [link=https://www.dmoz.org/Computers/Internet/On_the_Web/Syndication_and_Feeds/RSS/Directories/ external]dmoz[/link] or [link=http://www.feedster.com/ external]feedster.com[/link]");


	//define("NFLAN_44", "click to view");LAN_CLICK_TO_VIEW

define("NFLAN_45", "Number of items to show in menu");
define("NFLAN_46", "Number of items to show on main page");
//define("NFLAN_47", "0 or blank to show all");//not used

//define("NFLAN_48", "Unable to save raw data in database.");
//define("NFLAN_49", "Unable to unserialize rss data - uses non-standard syntax");//not used
//define("NFLAN_50", "Write to database failed: ");

// Admin log messages
//===================
//define("LAN_AL_NEWSFD_01","News Feed created");//not used
//define("LAN_AL_NEWSFD_02","News Feed updated");//not used
//define("LAN_AL_NEWSFD_03","News Feed deleted");//not used
//define("LAN_AL_NEWSFD_04","");//not used
//define("LAN_AL_NEWSFD_05","");//not used

//define("NFLAN_50", "Last Refresh");//LAN_LAST_UPDATED
