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

return [
    'NFLAN_11' => "Path to image",
    'NFLAN_12' => "Activation",
    'NFLAN_13' => "Nowhere (inactive)",
    'NFLAN_14' => "In menu only",
    'NFLAN_18' => "Update interval in seconds",
    'NFLAN_19' => "e.g. 3600: newsfeed will update every hour",
    'NFLAN_20' => "On newsfeed main page only",
    'NFLAN_21' => "In both menu and newsfeed page",
    'NFLAN_26' => "Update Interval",
    'NFLAN_43' => "Newsfeed Help",
    'NFLAN_42' => "[h=4]Newsfeed Title[/h]
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

	Newsfeeds can be displayed in the menu only or on the newsfeed page. Enter the details where feeds should be displayed. To see newsfeeds in e107 menus you will need to activate the [b]Newsfeeds Menu[/b] in [link=\".e_ADMIN.\"menus.php]Menu Manager[/link].

	[h=4]Tip[/h]

There are many feed direcotries on the web, try [link=https://www.dmoz.org/Computers/Internet/On_the_Web/Syndication_and_Feeds/RSS/Directories/ external]dmoz[/link] or [link=http://www.feedster.com/ external]feedster.com[/link]",
    'NFLAN_45' => "Number of items to show in menu",
    'NFLAN_46' => "Number of items to show on main page",
];
