<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/languages/English/lan_content_help.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-05-14 16:46:43 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_ORDER_1", "
<i>this page shows all categories and subcategories present.</i><br /><br />
<b>detailed list</b><br />you see the category id and the category name. also you see several options to manage the order of the categories.<br />
<br />
<b>explanation of icons</b><br />
".CONTENT_ICON_ORDERALL." manage the global order of content item regardless of category.<br />
".CONTENT_ICON_ORDERCAT." manage the order of content items in the specific category.<br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> the up button allow you to move a content item one up in order.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> the down button allow you to move a content item one down in order.<br />
<br />
<b>order</b><br />here you can manually set the order of all the categories in this each parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_2", "
<i>this page shows all content items from the category you have selected.</i><br /><br />
<b>detailed list</b><br />you see the content id, the content author and the content heading. also you see several options to manage the order of the content items.<br />
<br />
<b>explanation of icons</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> the up button allow you to move a content item one up in order.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> the down button allow you to move a content item one down in order.<br />
<br />
<b>order</b><br />here you can manually set the order of all the categories in this main parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_3", "
<i>this page shows all content items from main parent category you have selected.</i><br /><br />
<b>detailed list</b><br />you see the content id, the content author and the content heading. also you see several options to manage the order of the content items.<br />
<br />
<b>explanation of icons</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> the up button allow you to move a content item one up in order.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> the down button allow you to move a content item one down in order.<br />
<br />
<b>order</b><br />here you can manually set the order of all the categories in this main parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />
");



define("CONTENT_ADMIN_HELP_SUBMIT_1", "
<i>On this page you see a list of all content items that were submitted by users.</i><br /><br />
<b>detailed list</b><br />You see a list of these content items with their id, icon, main parent, heading [subheading], author and options.<br /><br />
<b>options</b><br />you can post or delete a content item using the buttons shown.
");



define("CONTENT_ADMIN_HELP_CAT_1", "
<i>this page shows all categories and subcategories present.</i><br /><br />
<b>detailed list</b><br />You see a list of all subcategories with their id, icon, author, category [subheading] and options.<br />
<br />
<b>explanation of icons</b><br />
".CONTENT_ICON_EDIT." : for all categories you can click this button to edit the category.<br />
".CONTENT_ICON_DELETE." : for all categories you can click this button to delete the category.<br />
".CONTENT_ICON_OPTIONS." : for only the main category (at the top of the list) you can click this button to set and control all options.<br />
");
define("CONTENT_ADMIN_HELP_CAT_2", "
".CONTENT_ICON_CONTENTMANAGER_SMALL." : (site admin only) for each subcategory you can click this button to manage the personalmanager for other admins.<br />
<br />
<b>personal manager</b><br />you can assign admins to certain categories. In doing so, these admins can manage their personal content items within these categories from outside of the admin page (content_manager.php).
");
define("CONTENT_ADMIN_HELP_CAT_3", "
<i>this page allows you to create a new category</i><br /><br />
ALWAYS CHOOSE A PARENT CATEGORY BEFORE YOU FILL IN THE OTHER FIELDS !<br /><br />
This must be done, because some unique category preferences need to be loaded in the system.
");
define("CONTENT_ADMIN_HELP_CAT_4", "
<i>this page shows the category edit form.</i><br /><br />
<b>category edit form</b><br />you can now edit all information for this (sub)category and submit your changes.
");
define("CONTENT_ADMIN_HELP_CAT_5", "
");
define("CONTENT_ADMIN_HELP_CAT_6", "
<i>this page shows the options you can set for this main parent. Each main parent has their own specific set of options, so be sure to set them all correctly.</i><br /><br />
<b>default values</b><br />By default all values are present and already updated in the preferences when you view this page, but change any setting to your own standards.<br /><br />
<b>division into eight sections</b><br />the options are divided into eight main sections. You see the different section in the right menu. you can click on them to go to the specific set of options for that section.<br /><br />
<b>create</b><br />in this section you can specify options for the creation of content items on the admin pages on the admins end.<br /><br />
<b>submit</b><br />in this section you can specify options for the submit form of content items.<br /><br />
<b>path and theme</b><br />in this section you can set a theme for this main parent, and provide path locations to where you have stored your images for this main parent.<br /><br /><b>general</b><br />in this section you can specify general options to use throughout all the content pages.<br /><br />
<b>list pages</b><br />in this section you can specify options pages, where content items are listed.<br /><br />
<b>category pages</b><br />in this section you can specify options how to show the category pages.<br /><br />
<b>content pages</b><br />in this section you can specify options how to show the content item page.<br /><br />
<b>menu</b><br />in this section you can specify options for the menu of this main parent.<br /><br />
");
define("CONTENT_ADMIN_HELP_CAT_7", "
<i>on this page you can assign admins to the selected category you have clicked</i><br /><br />
Assign admin from the left colomn by clicking their name. you will see these names move to the right colomn. After clicking the assign button the admins in the right colomn are assigned to this category.
");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "
<b>category</b><br />please select a category from the select box to create your content item for.<br />
");
define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "
always select a category before you fill in other fields !<br />
this needs to be done, because each main parent category (and subcategories in it) can have different preferences.<br /><br />
<b>creation form</b><br />you can now provide all information for this content item and submit it.<br /><br />
Be aware of the fact that diffenent main parent categories can have a different set of preferences, and therefore can have more fields available for you to fill in.
");


define("CONTENT_ADMIN_HELP_ITEM_1", "
<i>if you have not yet added main parent categories, please do so at the <a href='".e_SELF."?type.0.cat.create'>Create New Category</a> page.</i><br /><br />
<b>category</b><br />select a category from the pulldown menu to manage content for that category.<br /><br />
the main parents are shown in bold and have the (ALL) extenstion behind them. selecting one of these will show all items from this main parent.<br /><br />
for each main parent all the subcategories are shown including the main parent category itself (these are all shown in plain text). Selecting on of these categories will shown all items from that category only.
");
define("CONTENT_ADMIN_HELP_ITEM_2", "
<b>first letters</b><br />if multiple content item starting letters of the content_heading are present, you will see buttons to select only those content items starting with that letter. Selecting the 'all' button will show a list containing all content items in this category.<br /><br />
<b>detailed list</b><br />You see a list of all content items with their id, icon, author, heading [subheading] and options.<br /><br />
<b>explanation of icons</b><br />
".CONTENT_ICON_EDIT." : edit the content item.<br />
".CONTENT_ICON_DELETE." : delete the content item.<br />
");


define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "
<b>edit form</b><br />you can now edit all information for this content item and submit your changes.<br /><br />
if you change the category of this content item to another main parent category, you probably want to re-edit this item after the category change.<br />Because you change the main parent category other preferences may be available to fill in.
");

define("CONTENT_ADMIN_HELP_1", "Content Management Help Area");


define("CONTENT_ADMIN_HELP_ITEM_LETTERS", "Below you see the distinct letters of the content heading for all items in this category.<br />By clicking on one of the letters you will see a list of all items starting with the selected letter. You can also choose the ALL button to display all items in this category.");


?>