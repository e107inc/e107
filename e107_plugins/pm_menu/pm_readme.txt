 /* /* 
|  Copyright (C) 2003 Thom Michelbrink
|
|  Author:  Thom Michelbrink     mcfly@e107.org
|
|  This program is free software which I release under the GNU General Public
|  License. You may redistribute and/or modify this program under the terms
|  of that license as published by the Free Software Foundation; either
|  version 2 of the License, or (at your option) any later version.
|
|  This program is distributed in the hope that it will be useful,
|  but WITHOUT ANY WARRANTY; without even the implied warranty of
|  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
|  GNU General Public License for more details.  Version 2 is in the
|  COPYRIGHT file in the top level directory of this distribution.
| 
|  To get a copy of the GNU General Puplic License, write to the Free Software
|  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

Purpose:

  This is a plugin for the E107 CMS system (e107.org).
  This plugin will enable Private Messaging funtionality on your site.
    
Requirements:

  This plugin requires e107 Verion 0.600+

############## INSTALLATION ####################### 

1) Upload all files to your e107_plugins directory on your server, retaining directory structure.
2) Go to the admin section of the website, go the to plugin manager and install the PM.
3) Go to the admin / plugins section and configure the PM settings.
4) Functionality should now be turned on.  You must be a registered user on the site to use it.

There are two ways for you to display the PM stats. You can either have them in their own menu or have them appear in the user login menu.  Here are the instructions for both:

Own menu:  Go to the admin / menus section and add the PM menu to desired menu area.

User Login menu:  Open up the file e107_plugins/login_menu/login_menu.php and add lines:

require_once(e_PLUGIN."pm_menu/pm_inc.php");
$text.=pm_show_stats();

above the line that reads : 

$ns -> tablerender($caption, $text);

This should be at approximately line #40

#####################################################################

How to include the PM icon anywhere?  This can be done with a few lines of code.  At the beginning of the file you want the icon to appear, add the line:

@include_once(e_PLUGIN."pm_menu/pm_inc.php");

This will load the function necessary to do display it.
At the pint where you would like the icon to appear add this:

$pm_icon = (defined("e_PM") ? pm_show_icon($to_user_id) : "");
echo $pm_icon;

This fuction will ensure that the sending and receiving user have the necessary right to use the PM system.



Files included with this release
---------------------------------
all files in the pm_menu directory tree


Files from previous releases that are no longer needed
------------------------------------------------------
files\languages\pmlan_*.php
plugins\pm.php
pm_readme.txt 										
pm_install.php										- PM installation file
pm.php 												- Main PM file
pm_inc.php											- Used to show the PM menu items
classes\pm_class.php								- Defines PM class and related functions
admin\pm_conf.php									- Admin configuration functions
menus\pm_menu.php									- This calls the pm_inc.php files to show the items
languages\plugins\pm\pmlan_*.php				- All language files.
admin\languages\plugins\pm\pm_lan_*.php	- All Admin languages files


=========================================================
Version history:

12/4/2002 - Initial beta release 
 
12/5/2002 - Next beta
	+ Ability to add stats to login_menu
	+ Added 'quote' message on reply option
	+ Fixed path problems with site is not at root level
	+ Fixed subject problem
	+ Added admin config options
	+ Added delete_on_read option
	+ Added auto-expiring option

12/8/2002 - 
	+ Moved configuration to the pm_prefs table, instead of the main prefs table.
	
12/12/2002
	+ Moved configuration back to the main pref table (jalist request).
	+ Added emoticon support
	+ Added html support
	+ Improved the internal messaging functionality
	+ Added ability to specify who to pm in URL.  (Thanks to Chris McLeod for the idea)
	To use this feature you need to know the users user_id.  Then you can make a link to
	www.yoursite.com/pm.php?send.$user_id
	+ Cleaned up code / fixed some other probs.

2/15/2003
	+ Updated plugin to work with e107 5.4
	+ If user not logged in, menu will not show up.
	+ Moved Delete_Expired function to allow for inactive pm systems to delete older 
	messages easier.
	+ Moved emotes from right column to bottom row.
	+ Added multilanguage support.
	
2/18/2003
	+ More changes to allow PM to work with subdirs.
	+ Added Dutch language file (thanks koot).

2/19/2003
	+ Fixed security hole (?view.xx was allowed by anyone) (found be kaputer)
	+ Added German language file (also by kaputer).

2/27/2003
	+ Added popup window notification.
	+ Fixed new pm image problem if using subdirs (thanks Cameron)
	+ There were non-translated phrases previously, I think I have all of 'em now.
		
3/3/2003
	+ Added Islandic language file (thanks MaxAlien)
	+ Moved the languages file to files/languages (as requested by Lolo Irie)
	+ Added French language file (thanks Lolo Irie)

3/4/2003
	+ Completed 'PM Admin Functions'
	+ Tweaked pop-up window behavior.
	+ Added Italian language file (thanks NotZen)
	+ Added upgraded Dutch language file (thanks again koot)
	+ Added upgraded German language file (thanks again kaputer)

3/7/2003 ( v1.4) ( adapted by Cameron )
	+ Popup Delay feature added 
	+ selected theme used for popup  	
		
5/7/2003 ( v1.5 ) (mod by Cameron and McFly)
	+ Added the avatar image to the inbox. 

5/31/2003 ( v1.6b ) 
	+ Converted to 'class' functionality, streamlined code.
	+ Added ability to restrict PM use to a specific userclass
	+ Added ability to send PMs to all members of any userclass the sender is a member of (if not restricted to a single userclass)
	 (original idea by kaputer a LONG time ago...glad I finally got it in there)
	+ 'new pm graphic' is now a link (idea by Kent Humphrey)
	+ 'x new messages' link only displayed if > 1 (idea also by Kent Humphrey)
	+ 'new pm graphic' (newpm.gif) can now be themed.  Just include it in your theme/xxx/images directory.
	+ Moved the language files into the language\plugins\pm directory.
	+ Added multi-language admin functionality
	+ Added uninstall functionality
	+ Now using the emote class included in the e107 core classes.
	+ Added code to support USERLAN
	+ Massive code cleanup (looks better to me anyway)
	+ Added config option to send notification email to PM recipients.
	+ Fixed broken avatar image bug
	+ Fixed message read time bug (e107coders bug #12)

6/9/2003 ( v1.6 ) 
	+ Fixed admin language file not found error, when site is installed in subdir
	+ Added new Dutch language file (thanks Koot)

6/12/2003 ( v1.61 ) 
	+ Added Dutch admin language file
	+ Added Danish language file
	+ Fixed PM sending bug, allowing anyone to send PMs even if restricted (e107coders bug #23)

6/13/2003 ( v1.62 ) 
	+ When deleting all PMs through admin, it now resets the pm_id counter to 1 (just in case). Suggested by Lumina.

6/27/2003 ( v2.0b2 ) 
	+ Converted PM to work with e107 v0.6
	+ Block messages now alerts sender that message was blocked.
	+ Added multilanguage ability to menu caption.  If you set the caption to PMLAN_PM in the admin section, it will then assign it to that value from the language file.
	+ Added some templating functionality.
	+ Added help.php
	
7/24/2003 ( v2.0b3 ) 
	+ Completed code necessary to integrate with 0.600 and the new plugin manager.

7/27/2003 ( v2.0b4 ) 
	+ Small bugfixes and appearance changes

8/6/2003 ( v2.0b5 ) 
	+ Added pm_show_icon() function to display PM icon in any menu / file.  See instructions how do do this above.
	+ Fixed sending to userclass.
	+ Fixed link to user.php
	+ Fixed email url if SITEURL was missing a trailing /.
	+ Now uses themed new.png and nonew.png from theme/forum directory (if exists).

8/12/2003 ( v2.0 ) 
	+ Updated this readme with correct instruction on how to add the PM menu into the login_menu
	+ Fixed double send form when replying.
	
8/23/2003 ( v2.01 ) 
	+ Added Swedish, Hungarian, and Hebrew languages, thanks all!
	+ Fixed a small bug when replying to a PM with quote marks.

8/23/2003 ( v2.02 ) 
	+ Fixed some small code errors, and a nice security hole (Thanks Barre)

12/14/2003 ( v2.03 ) 
	+ When able to send to userclass, classes are now alphabetized.
	+ You can now view your sent PMs, deleting is still receiver only.
	+ Find user function now in send pm screen.  You MUST turn OFF the 'show user dropdown list' for this to work.  This will make large member sites load MUCH faster.  Also added this function to the admin screen (delete messages)
	+ Fixed some hardcoded english
	+ Cleaned up some functions
	+ Added Lithuanian language files.
	
1/31/2004 ( v2.04 ) 
	+ Added a timeout function when sending PMs, will prevent script timing out when sending a LOT of PMs at once (to userclass).
	+ Fixed problem with the pm send being shown above pm reply (which also fixed a js error).
	+ Added page select to pm read view.  Will now only show 10 received PMs at a time.
	+ Fixed a pm_showmessge() error from appearing, if you are not the send/receiver of the pm.
	+ Removed the version number from being shown on the pm screens, was put there before the plugin manager existed, time to get rid of it :)

2/13/2004 ( v2.05 ) 
	+ Fixed the bug that made you click on unread PMs twice to read them.
	+ Added the checkbox next to the PMs, so you can delete multiple.
	+ Added userclass sending when the dropdown for users is off (which should be default now).

x/xx/xxxx ( v2.06 ) 
	+ Removed some debug code, causing errors when deleting PMs
	+ Added a few more phrases to the language files that were still hardcoded english
	+ Added a pm_get_stats() function to the pm_inc.php file, making it easier for others to get those values.
	+ Still cleaning up code ...lots more to do.