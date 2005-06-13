<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/languages/English/lan_content_help.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-06-13 11:08:57 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_1", "Content Management Help Area");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>if you have not yet added main parent categories, please do so at the <a href='".e_SELF."?cat.create'>Create New Category</a> page.</i><br /><br /><b>category</b><br />select a category from the pulldown menu to manage content for that category.<br /><br />Selecting a main parent from the pulldown menu will show all content items in that main category.<br />Selecting a subcategory will show only those content items in the specified subcategory.<br /><br />You can also use the menu on the right to view all content items for a specified category.");

define("CONTENT_ADMIN_HELP_ITEM_2", "<b>first letters</b><br />if multiple content item starting letters of the content_heading are present, you will see buttons to select only those content items starting with that letter. Selecting the 'all' button will show a list containing all content items in this category.<br /><br /><b>detailed list</b><br />You see a list of all content items with their id, icon, author, heading [subheading] and options.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the content item<br />".CONTENT_ICON_EDIT." : edit the content item<br />".CONTENT_ICON_DELETE." : delete the content item<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<b>edit form</b><br />you can now edit all information for this content item and submit your changes.<br /><br />If you need to change the category for this content item, please do so first. After you have selectd the correct category, change or add any fields present, before you submit the changes.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>category</b><br />please select a category from the select box to create your content item for.<br />");

define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "<b>creation form</b><br />you can now provide all information for this content item and submit it.<br /><br />Be aware of the fact that diffenent main parent categories can have a different set of preferences; different fields can be available for you to fill in. Therefore you always need to select a category first before you fill in other fields !");

define("CONTENT_ADMIN_HELP_CAT_1", "<i>this page shows all categories and subcategories present.</i><br /><br /><b>detailed list</b><br />You see a list of all subcategories with their id, icon, author, category [subheading] and options.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_EDIT." : edit the category<br />".CONTENT_ICON_DELETE." : delete the category<br />");

define("CONTENT_ADMIN_HELP_CAT_2", "<i>this page allows you to create a new category</i><br /><br />Always choose a parent category before you fill in the other fields !<br /><br />This must be done, because some unique category preferences need to be loaded in the system.<br /><br />By default the category page is shown to create a new main category.");

define("CONTENT_ADMIN_HELP_CAT_3", "<i>this page shows the category edit form.</i><br /><br /><b>category edit form</b><br />you can now edit all information for this (sub)category and submit your changes.<br /><br />If you want to change the parent location for this category, please do so first. After you have set the correct category edit all other fields.");

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>this page shows all categories and subcategories present.</i><br /><br /><b>detailed list</b><br />you see the category id and the category name. also you see several options to manage the order of the categories.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_ORDERALL." : manage the global order of content item regardless of category.<br />".CONTENT_ICON_ORDERCAT." : manage the order of content items in the specific category.<br />".CONTENT_ICON_ORDER_UP." : the up button allows you to move a content item one up in order.<br />".CONTENT_ICON_ORDER_DOWN." : the down button allows you to move a content item one down in order.<br /><br /><b>order</b><br />here you can manually set the order of all the categories in this each parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />");

define("CONTENT_ADMIN_HELP_ORDER_2", "<i>this page shows all content items from the category you have selected.</i><br /><br /><b>detailed list</b><br />you see the content id, the content author and the content heading. also you see several options to manage the order of the content items.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the content item<br />".CONTENT_ICON_ORDER_UP." : the up button allows you to move a content item one up in order.<br />".CONTENT_ICON_ORDER_DOWN." : the down button allows you to move a content item one down in order.<br /><br /><b>order</b><br />here you can manually set the order of all the categories in this main parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />");

define("CONTENT_ADMIN_HELP_ORDER_3", "<i>this page shows all content items from main parent category you have selected.</i><br /><br /><b>detailed list</b><br />you see the content id, the content author and the content heading. also you see several options to manage the order of the content items.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the content item<br />".CONTENT_ICON_ORDER_UP." : the up button allow you to move a content item one up in order.<br />".CONTENT_ICON_ORDER_DOWN." : the down button allow you to move a content item one down in order.<br /><br /><b>order</b><br />here you can manually set the order of all the categories in this main parent. You need to change the values in the select boxes to the order of your kind and then press the update button below to save the new order.<br />");

define("CONTENT_ADMIN_HELP_OPTION_1", "On this page you can select a main parent category to set options for, or you can choose to edit the default preferences.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_OPTIONS." : edit the options<br />");

define("CONTENT_ADMIN_HELP_OPTION_2", "
<i>this page shows the options you can set for this main parent. Each main parent has their own specific set of options, so be sure to set them all correctly.</i><br /><br />
<b>default values</b><br />By default all values are present and already updated in the preferences when you view this page, but change any setting to your own standards.<br /><br />
");
/*
<b>division into eight sections</b><br />the options are divided into eight main sections. You see the different section in the right menu. you can click on them to go to the specific set of options for that section.<br /><br />
<b>create</b><br />in this section you can specify options for the creation of content items on the admin pages on the admins end.<br /><br />
<b>submit</b><br />in this section you can specify options for the submit form of content items.<br /><br />
<b>path and theme</b><br />in this section you can set a theme for this main parent, and provide path locations to where you have stored your images for this main parent.<br /><br /><b>general</b><br />in this section you can specify general options to use throughout all the content pages.<br /><br />
<b>list pages</b><br />in this section you can specify options pages, where content items are listed.<br /><br />
<b>category pages</b><br />in this section you can specify options how to show the category pages.<br /><br />
<b>content pages</b><br />in this section you can specify options how to show the content item page.<br /><br />
<b>menu</b><br />in this section you can specify options for the menu of this main parent.<br /><br />
*/
define("CONTENT_ADMIN_HELP_MANAGER_1", "On this page you see a list of all categories. You can manage the 'personal content manager' for each category by clicking the icon.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : edit the personal content managers<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>on this page you can assign users to the selected category you have clicked</i><br /><br /><b>personal manager</b><br />you can assign users to certain categories. In doing so, these users can manage their personal content items within these categories from outside of the admin page (content_manager.php).<br /><br />Assign users from the left colomn by clicking their name. you will see these names move to the right colomn. After clicking the assign button the users in the right colomn are assigned to this category.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>On this page you see a list of all content items that were submitted by users.</i><br /><br /><b>detailed list</b><br />You see a list of these content items with their id, icon, main parent, heading [subheading], author and options.<br /><br /><b>options</b><br />you can post or delete a content item using the buttons shown.");

?>