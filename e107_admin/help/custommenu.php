<?php
$text = "From this screen you can create custom menus or custom pages with your own content in them.<br /><br /><b>Important Notes</b><br />
- to use this feature you will need to CHMOD your /e107_plugins/custom/ and /e107_plugins/custompages/ directories to 777.
<br />
- you can use HTML code using ' for HTML attributes !!! (Using \" for HTML attributes will crash your menu/page)
<br /><br />
<i>Menu/Page Filename</i>: The name of your custom menu or custom page, the menu will be saved as 'this name.php' in the ".e_PLUGIN."custom/ directory<br />
the page will be saved as this 'name.php' in the ".e_PLUGIN."custompages/ directory<br /><br />
<i>Menu/Page Caption Title</i>: The text displayed in the captionbar of the menu item or the title of the page<br /><br />
<i>Menu/Page Text</i>: The actual data that is displayed in the menu body or as normal page (For advanced users: no need to add lines to call the class2.php file or to display HEADER or FOOTER... will be automatically added), can be text, images etc";

$ns -> tablerender(CUSLAN_18, $text);
?>