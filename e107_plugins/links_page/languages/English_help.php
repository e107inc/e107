<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|		Links plugin - help language file
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_handlers/db_debug_class.php $
|     $Revision: 11678 $
|     $Id: db_debug_class.php 11678 2010-08-22 00:43:45Z e107coders $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


define("LAN_ADMIN_HELP_0", "linkspage help area");

define("LAN_ADMIN_HELP_1", "<i>the manage link categories page shows all categories present.</i><br /><br /><b>detailed list</b><br />You see a list of all categories with their icon, name and description, options, and sorting options.<br /><br /><b>explanation of icons</b><br />
".LINK_ICON_LINK." : link to the category<br /><br />
".LINK_ICON_EDIT." : edit the category<br /><br />
".LINK_ICON_DELETE." : delete the category<br /><br />
".LINK_ICON_ORDER_UP." : the up button allows you to move the category item one up in order.<br /><br />
".LINK_ICON_ORDER_DOWN." : the down button allows you to move the category item one down in order.<br />
<br />
<b>order</b><br />here you can manually set the order of all the categories. You need to change the values in the select boxes to your desired order, and click on the reorder button below to save the new order.<br />");

define("LAN_ADMIN_HELP_2", "<i>the create link category page allows you to add new categories</i><br /><br />You can upload a new icon, and after uploading assign the icon to the category.");
define("LAN_ADMIN_HELP_3", "<i>the manage links page first show all categories.</i><br /><br />".LINK_ICON_LINK." : link to the category<br /><br />".LINK_ICON_EDIT." : click the icon to view all links in this category<br />");
define("LAN_ADMIN_HELP_4", "<i>the create link page allows you to add a new link</i><br /><br />You can upload a new icon, and after uploading assign the icon to the link.<br /><br />the open type allows you to define how the link will be opened when a user clicks on it.");
define("LAN_ADMIN_HELP_5", "<i>the submitted links page shows all links that are submitted by users</i><br /><br /><b>detailed list</b><br />You see the link url, the name of the user who submitted the link and options.<br /><br /><b>explanation of icons</b><br />
".LINK_ICON_EDIT." : post the submitted link to the link create form<br /><br />
".LINK_ICON_DELETE." : delete the submitted link<br />
");
define("LAN_ADMIN_HELP_6", "<i>the options page allows you to change the behaviour of the links_page plugin</i><br /><br />
general options<br />
these options are generally used throughout the link pages.<br /><br />
personal link managers<br />
the personal link managers are privileged users who can manage their own personally added links.<br /><br />
category page<br />
here you can change options for the category page.<br /><br />
links page<br />
These options are used on the link pages.<br /><br />
refer page<br />
These options are used on the top refer links page.<br /><br />
rating page<br />
These options are used on the top rated links page.<br />
");

define("LAN_ADMIN_HELP_7", "<i>the edit link category page allows you to edit an existing category</i><br /><br />You can upload a new icon, and after uploading assign the icon to the category.<br />You can update the timestamp of the link by checking the box.");

define("LAN_ADMIN_HELP_8", "<i>this page shows all existing links in the selected category.</i><br /><br /><b>detailed list</b><br />You see a list of the links with their image, name, options, and sorting options.<br /><br /><b>explanation of icons</b><br />
".LINK_ICON_LINK." : link to the website<br /><br />
".LINK_ICON_EDIT." : edit the link<br /><br />
".LINK_ICON_DELETE." : delete the link<br /><br />
".LINK_ICON_ORDER_UP." : the up button allows you to move a link up one in the list.<br /><br />
".LINK_ICON_ORDER_DOWN." : the down button allows you to move a link down one in the list.<br />
<br />
<b>order</b><br />here you can manually set the order of all the links. You need to change the values in the select boxes to your desired order, and click on the reorder button below to save the new order.<br />");

define("LAN_ADMIN_HELP_9", "<i>the edit link page allows you to edit an existing link</i><br /><br />You can upload a new icon, and after uploading assign the icon to the link.<br /><br />the open type allows you to define how the link will be opened when a user clicks on it.");
define("LAN_ADMIN_HELP_10", "<i>the post submitted link page allows you to add a submitted link to the existing links</i><br /><br />A small submitted text is added into the description field.<br /><br />You can upload a new icon, and after uploading assign the icon to the link.<br /><br />the open type allows you to define how the link will be opened when a user clicks on it.");
?>