<?php

$e_plug_table = "pcontent"; //This is set to the table name you have decided to use.
$reply_location= e_PLUGIN."content/content.php?content.$nid"; //This is set to the location you'd like the user to return to after replying to a comment.
$db_table = "pcontent"; //This is the name of your plugins database table.
$link_name = "content_heading"; //This is the name of the field in your plugin's db table that corresponds to it's name or title.
$db_id = "content_id"; // This is the name of the field in your plugin's db table that correspond to it's unique id number.
$plugin_name = "Content"; // A name for your plugin. It will be used in links to comments, in list_new/new.php.

?>