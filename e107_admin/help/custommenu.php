<?php
$text = "From this screen you can create custom menus with your own content in them.<br /><br /><b>Important</b> - to use this feature you will need to CHMOD your /e107_menus/custom/ directory to 777.
<br /><br />
<i>Menu Filename</i>: The name of your custom menu, the menu will be saved as 'custom_this name.php' in the /menus directory<br />
<i>Menu Caption Title</i>: The text displayed in the captionbar of the menu item<br />
<i>Menu Text</i>: The actual data that is displayed in the menu body, can be text, images etc";

$ns -> tablerender("Custom Menus", $text);
?>