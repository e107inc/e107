<?php

/*
This file will be highly updated before e107 v.700
define("DO NOT TRANSLATE THIS FILE","DO NOT TRANSLATE THIS FILE");
*/

define("MLAD_LAN_1", "Languages");
define("MLAD_LAN_2", "Multilanguage system currently running.");
define("MLAD_LAN_3", "You are not currently using multilanguage.");
define("MLAD_LAN_4", "Click on a language, then check one or more checkbox to activate multilanguage AND automatically create tables in your database.
<br />
Or use <b>PHPMyAdmin</b> (or similar tool) to update your database like required.");
define("MLAD_LAN_5", "News");
define("MLAD_LAN_6", "Articles/Reviews/Content");
define("MLAD_LAN_7", "Reviews");
define("MLAD_LAN_8", "Content");
define("MLAD_LAN_9", "Links");
define("MLAD_LAN_10", "Downloads");
define("MLAD_LAN_11", "Forums");
define("MLAD_LAN_12", "Polls");
define("MLAD_LAN_13", "Welcome Messages");
define("MLAD_LAN_14", "Plugins");
define("MLAD_LAN_15", "Create tables");
define("MLAD_LAN_16", "STOP to use multilanguage");
define("MLAD_LAN_17", "<b>The Dev Team recommend:</b> Put your site in maintenance mode during the multilanguage system configuration if you choose to configure your database...
<br />
<br />
<a href=\"".e_ADMIN."ugflag.php\" >Click here</a> to go to the maintenance page...
<br />
<br />");
define("MLAD_LAN_18", "Info: ");
define("MLAD_LAN_19", "<b>Important note:</b> Your site is currently in maintenance mode. To unactivate your maintenance flag, <a href=\"".e_ADMIN."ugflag.php\" >click here.</a>
<br />
<br />");
define("MLAD_LAN_20", "Following table was created in your database:");
define("MLAD_LAN_21", "Info update:");
define("MLAD_LAN_22", "Following tables were created in your database :");
define("MLAD_LAN_23", "To start to use multilanguage system for your site, click on the following button.");
define("MLAD_LAN_24", "To unactivate the multilanguage system but keep Database config, click on the following button.<br />");
define("MLAD_LAN_25", "Activate multilanguage");
define("MLAD_LAN_26", "To delete tables in your database and optimize your site for your main language ONLY, <a href=\"".e_SELF."?db\" >go to this page to manage your database.</a>");
define("MLAD_LAN_27", "Following languages are existing in your database.
<br /><br />
Click on a language to see which tables are existing in your database and which type of information you can publish...");
define("MLAD_LAN_28", " used for ");
define("MLAD_LAN_29", "Drop existing tables?");
define("MLAD_LAN_30", "If you check this box, existing tables with content will be deleted before to be created again.<br />If required backup these tables at first using e107, PhpMyAdmin or a similar backup tool.");
define("MLAD_LAN_31", "Other languages used");
define("MLAD_LAN_32", "Confirm delete");
define("MLAD_LAN_33", "Check this box before to click on the button to delete tables.
<br />
<b>BE CAREFUL</b>: content will be lost for these languages !!!");
define("MLAD_LAN_34", "Delete tables");
define("MLAD_LAN_35", "Following tables were deleted in your database :");
define("MLAD_LAN_36", "Following table was deleted in your database :");
define("MLAD_LAN_37", "Full help and info about multilanguage");
define("MLAD_LAN_38", "Please read that before to use multilanguage system");
define("MLAD_LAN_39", "-----------------------------------------------------
<br />
<b>IMPORTANT NOTE: </b>The multilanguage system is actually a BETA release not enough tested, you are using this one for your website at your own risks... Not yet recommended on Prod Server. Support on e107.org and etalkers.org.
<br />
<br />
-----------------------------------------------------
<br /><br /><br />
With e107, you can publish information on Internet with a powerful multilanguage system.
<br />
This one is pretty easy to use, but the e107 dev team recommends you to be careful with following information...
<br /><br /><br />
<b>1 Use multilanguage for the interface of your site.</b> (No international publishers required)
<br /><br />
You only need to install language files for all required languages.
<br />
In the folder <b>".$LANGUAGES_DIRECTORY."</b>, create a new folder for each new language and extract all files of the language package to download on e107.org or local e107 sites if your language is already translated by a member of the e107 community, if not feel free to submit new language(s)- keeping the folder structure.
<br /><br />
By default the install procedure of e107 is available in different languages, but NOT the interface of your public site. If you see a folder called like your new language, be sure this one has all required language files for the e107 system. Ask for help on e107.org.
<br /><br />
<br /><br />
<b>2 Use multilanguage for the content of your site (saved in database).</b>
<br /><br />
<b>2.1</b> Click on the button to activate multilanguage on this page... See below on this page.
<br /><br />
It will activate system, but it's not enough to use it.
<br />
You will see new options in the multilanguage admin menu (probably on the right side), to install and configure multilanguage.
<br /><br />
<b>2.2</b> For each existing language folder on your server, you will be able to configure this language for the database content.
<br />
At first, create required Database config, using the <a href=\"".e_SELF."?db\" >'Database config'</a> page in the admin menu for multilanguage.
<br /><br />
Existing tables for multilanguage (after you create them like explained before) will be displayed on this page...
<br />
<br />
To be able to manage other languages, you need to create new folders in <b>".$LANGUAGES_DIRECTORY."</b>. The e107 dev team recommends to install interface language files too (makes sense your visitors get interface and content with same language... procedure explained before), but you can also choose to display only the content using multilanguage with only some interface language files...
<br /><br />
<br />
<b>IMPORTANT NOTE:</b> If you have problems using the 'Database config' feature (tables not correctly created because too much content or too slow CPU), you can use PHPMyAdmin to create tables and be able to use multilanguage anyway:
<br /><br />
- Set the maintenance flag for your site
<br /><br />
- Export your default tables as sql file, structure AND content.<br />
For example, tables ".MPREFIX."news and ".MPREFIX."news_category for news or ".MPREFIX."content for content/articles and reviews
<br /><br />
- Edit this sql files.
<br />
Replace everywhere ".MPREFIX." with ".MPREFIX."XXXXXX_, where XXXXXX is the new languages. For example ".MPREFIX."French_news, ".MPREFIX."French_news_categories  for french...
<br />
If you want to create more languages in one step. Duplicate your original sql file one time for each language and edit it like precedent.
<br /><br />
- Import your sql file(s) with PHPMyAdmin
<br /><br />
- Activate multilanguage system
<br /><br />
- Unset the maintenance flag for your site
<br /><br /><br />
<b>3 STOP to use multilanguage.</b>
<br /><br />
If you want to go temporarly back to a single language for the content, you just need to unactivate the multilanguage system, with a click on the button below on this page.
<br />
<b>IMPORTANT NOTE: </b>If you install the multilanguage system and unactivate it, you will NOT be able to use it correctly later without using PHPMyAdmin to copy content published  only in one language...<br />
A feature will be added later to REactivate multilanguage system... Just be patient. ;)<br />
<br />
To uninstall the multilanguage system for ever, delete at first all new tables created for multilanguage (optimize performances for your single language website), then unactivate multilanguage...
<br />
");
define("MLAD_LAN_40","<br /><b>A menu was automatically added in your Area 1 to allow users to switch languages.</b><br />Go to your menus config page to move/unactivate it like required.<br /><br />");
define("MLAD_LAN_41","Default language");
define("MLAD_LAN_42","Default language: ");
define("MLAD_LAN_43","It's not recommended to change default language after starting to use multilanguage system !<br/>
If you change the default language for a language already used in multilanguage system (See below), you will need to overwrite the database content of default tables content of translated tables using PHPMyAdmin or a similar tool...<br />Need hep ? Go to <a href=\"javascript:void(0);\" onclick=\"window.open('http://e107.org','e107org');\" >http://e107.org</a>");
define("MLAD_LAN_44"," Tables for multilanguage were successful created !");
define("MLAD_LAN_45","To see a more detailled view of the sql query used to create tables, please ");
define("MLAD_LAN_46","click here.");
define("MLAD_LAN_47","<b>IMPORTANT NOTE</b> : The following code is only here for info, and not the right sql query used to create tables.");
define("MLAD_LAN_48","This query was used for all required languages.");
define("MLAD_LAN_49","Error with the SQL backup... Tables are maybe already existing (You need to check the box '".MLAD_LAN_29."' to overwrite existing tables).");
define("MLAD_LAN_50","Check with PHPMyAdmin.");
define("MLAD_LAN_51","");





define("MLAD_LANadv_1", "Different languages for interface and content");
define("MLAD_LANadv_2", "If checked, users will be able to use a language for the interface and an other language for the content.");
define("MLAD_LANadv_3", "Other options will be added here if required by e107 users...<br /> Contact the e107 Dev Team to submit ideas...");
define("MLAD_LANadv_4", "Update");
define("MLAD_LANadv_5", "Advanced options successfully updated.");
define("MLAD_LANadv_6", "Messages");
define("MLAD_LANadv_7", "Email alerts");
define("MLAD_LANadv_8", "Email administrator multilanguage");
define("MLAD_LANadv_9", "If checked, an administrator will get an email IF there is an error related to multilanguage publishing.");
define("MLAD_LANadv_10", "You can let empty if the main administrator manage the multilanguage system for ".SITENAME);
define("MLAD_LANadv_11", "Multilanguage publishing<br /><b class='smalltext' >without update administrator language config</b>");
define("MLAD_LANadv_12", "If checked, publisher will be allow to publish in every language without to change his own language.<br /><b>Example:</b> If checked, a french publisher will be allowed to publish in english and german too using the french version of the site. Unchecked, this publisher will need to change his language to english or german to be able to publish for these languages.");
define("MLAD_LANadv_13", "Flags Menu");
define("MLAD_LANadv_14", "If checked, flags icons will be displayed instead text, to switch languages.<br /> You will maybe need to upload new flags in your folder images/generic/flags called YOUR_LANGUAGE.png");

define("MLHELP_LAN_1", "Help Multilanguage");
define("MLHELP_LAN_2", "Frontpage");
define("MLHELP_LAN_3", "You will find here all info required to use the multilanguage system for your site.
<br /><br />
You can define the current status for multilanguage (used or not used).
<br /><br />
NOTE: No database modification done here. To create or delete tables, use the 'Database config' button in the admin menu.
<br /><br />");
define("MLHELP_LAN_4", "Database config");
define("MLHELP_LAN_5", "To add new languages, install at first a new language package for the interface (download on <a href=\"http://e107.org\" rel=\"external\" >http://e107.org</a>) and install it on your server in the ".e_LANGUAGEDIR." folder like other existing languages. Refresh this page to see this one in the list and be allowed to configure it.
<br /><br />
Click on a language to choose which tables to create to allow publishing in other languages or to delete tables.
<br /><br />");
define("MLHELP_LAN_6", "Advanced options");
define("MLHELP_LAN_7", "Use this page to configure advanced features for your multilanguage system...
<br /><br />");
define("MLHELP_LAN_8", "Check Interface Files");
define("MLHELP_LAN_9", "Tool to verify interface language files.");

define("MLAD_MENU_0", "Options multilanguage");
define("MLAD_MENU_1", "Multilanguage Frontpage");
define("MLAD_MENU_2", "Advanced Options");
define("MLAD_MENU_3", "Database config");
define("MLAD_MENU_4", "Check Interface Files");
define("MLAD_MENU_5", "");
define("MLAD_MENU_6", "");
?>
