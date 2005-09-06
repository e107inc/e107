<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Finnish/lan_content_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-06 17:41:17 $
|     $Author: timmerbacka $
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

define("CONTENT_ADMIN_HELP_OPTION_1", "Tällä sivulla you can select a main parent category to set options for, or you can choose to edit the default preferences.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_OPTIONS." : edit the options<br /><br /><br />
The default preferences are only used when you create a new main parent. So when you create a new main parent these default preferences will be stored. You can change these to make sure newly created main parents already have a certain set of features present.
<br /><br />
Each main parent has its own set of options, which are unique to that specific main parent category");

//define("CONTENT_ADMIN_HELP_OPTION_2", "<i>this page shows the options you can set for this main parent. Each main parent has their own specific set of options, so be sure to set them all correctly.</i><br /><br />");
//<b>default values</b><br />By default all values are present and already updated in the preferences when you view this page, but change any setting to your own standards.<br /><br />
define("CONTENT_ADMIN_HELP_MANAGER_1", "Tällä sivulla you see a list of all categories. You can manage the 'personal content manager' for each category by clicking the icon.<br /><br /><b>explanation of icons</b><br />".CONTENT_ICON_USER." : link to the author profile<br />".CONTENT_ICON_LINK." : link to the category<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : edit the personal content managers<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>on this page you can assign users to the selected category you have clicked</i><br /><br /><b>personal manager</b><br />you can assign users to certain categories. In doing so, these users can manage their personal content items within these categories from outside of the admin page (content_manager.php).<br /><br />Assign users from the left colomn by clicking their name. you will see these names move to the right colomn. After clicking the assign button the users in the right colomn are assigned to this category.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>Tällä sivulla you see a list of all content items that were submitted by users.</i><br /><br /><b>detailed list</b><br />You see a list of these content items with their id, icon, main parent, heading [subheading], author and options.<br /><br /><b>options</b><br />you can post or delete a content item using the buttons shown.");



define("CONTENT_ADMIN_HELP_OPTION_DIV_1", "this page allows you to set options for the admin create item page.<br /><br />You can define which sections are available when an admin (or personal content manager) creates a new content item<br /><br /><b>custom data tags</b><br />you can allow a user or admin to add optional fields to the content item by using these custom data tags. These optional fields are blank key=>value pairs. For instance: you could add a key field for 'photographer' and provide the value field with 'all photos by me'. Both these key and value fields are empty textfields which will be present in the create form.<br /><br /><b>preset data tags</b><br />apart from the custom data tags, you can provide preset data tags. The difference is that in preset data tags, the key field already is given and the user only needs to provide the value field for the preset. In the same example as above 'photographer' can be predefined, and the user needs to provide 'all photos by me'. You can choose the element type by selecting one option in the selectbox. In the popup window, you can provide all the information for the preset data tag.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_2", "The Submit Options have affect on the user submit form for content items.<br /><br />You can define which sections are available for a user when submitting a content item.<br /><br />".CONTENT_ADMIN_OPT_LAN_11.":<br />".CONTENT_ADMIN_OPT_LAN_12."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_3", "In the Path and Theme Options you can define where images and files are stored.<br /><br />you can define which theme will be used by this main parent. You can create additional themes by copying (and renaming) the whole 'default' directory in your templates directory.<br /><br />You can define a default layout scheme for new content items. You can create new layout schemes by creating a content_content_template_XXX.php file in your 'templates/default' folder. These layouts can be used to give each content item in this main parent a different layout.<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_4", "The General Options are options that are used throughout the content pages of the content management plugin.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_5", "These options have affect on the Personal Content Manager area on the content management admin area.<br /><br />".CONTENT_ADMIN_OPT_LAN_63."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_6", "These Options are used in the Menu for this main parent if you have activated the menu.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."<br /><br />".CONTENT_ADMIN_OPT_LAN_118.":<br />".CONTENT_ADMIN_OPT_LAN_119."<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_7", "The Content Item Preview options have affect on the small preview that is given for a content item.<br /><br />This preview is given on several pages, like the recent page, the view items in category page and the view items of author page.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_8", "The Category Pages show information on the content categories in this main parent.<br /><br />Keskustelualueella on two distinct areas present:<br /><br />all categories page:<br />this page shows all the categories in this main parent<br /><br />view category page:<br />this page shows the category item, optionally the subcategories in that category and the content items in that category or those categories<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_9", "The Content Page shows the Content Item.<br /><br />you can define which sections to show by checking/unchecking the boxes.<br /><br />you can show the emailaddress of a non-member author.<br /><br />you can override the email/print/pdf icons, the rating system and the comments.<br /><br />".CONTENT_ADMIN_OPT_LAN_74."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_10", "The Author Page shows a list of all unique authors of the content items in this main parent.<br /><br />you can define which sections to show by checking/unchecking the boxes.<br /><br />You can limit the number of items to show per page.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_11", "The Archive Page shows all content items in the main parent.<br /><br />you can define which sections to show by checking/unchecking the boxes.<br /><br />you can show the emailaddress of a non-member author.<br /><br />You can limit the number of items to show per page.<br /><br />".CONTENT_ADMIN_OPT_LAN_66."<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_12", "The Top Rated Page shows all content items that have been rated by users.<br /><br />You can choose the sections to display by checking the boxes.<br /><br />Also you can define if the emailaddress of a non-member author will be displayed.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_13", "The Top Score Page shows all content items that have been given a score by the author of the content item.<br /><br />You can choose the sections to display by checking the boxes.<br /><br />Also you can define if the emailaddress of a non-member author will be displayed.");

?>
