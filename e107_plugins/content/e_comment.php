<?php

if (!defined('e107_INIT')) { exit; }

/*
$e_plug_table	= "pcontent"; //This is set to the table name you have decided to use.
$reply_location	= e_PLUGIN."content/content.php?content.$nid"; //This is set to the location you'd like the user to return to after replying to a comment.
$db_table		= "pcontent"; //This is the name of your plugins database table.
$link_name		= "content_heading"; //This is the name of the field in your plugin's db table that corresponds to it's name or title.
$db_id			= "content_id"; // This is the name of the field in your plugin's db table that correspond to it's unique id number.
$plugin_name	= "Content"; // A name for your plugin. It will be used in links to comments, in list_new/new.php.
*/

$e_comment['eplug_comment_ids'] = "pcontent"; //This is set to the table name you have decided to use.
$e_comment['plugin_path'] = "content"; //The path of your plugin.
$e_comment['plugin_name'] = "content"; //A name for your plugin. It will be used in links to comments, in list_new/new.php.
//This is set to the location you'd like the user to return to after replying to a comment.
$e_comment['reply_location'] = e_PLUGIN_ABS."content/content.php?content.{NID}"; 
$e_comment['db_title'] = "content_heading"; //This is the name of the field in your plugin's db table that corresponds to it's name or title.
$e_comment['db_id'] = "content_id"; // This is the name of the field in your plugin's db table that correspond to it's unique id number.

//qry must be set with a select_gen query.
//the main reason would be to check if a category from another table has a class restriction
//the id of the item should be provided as {NID}
//returned fields should at least contain the 'link_id' and 'db_id' fields set above
$e_comment['qry']				= "
SELECT c.*
FROM #pcontent as c
WHERE c.content_id='{NID}' AND c.content_refer !='sa' AND c.content_datestamp < ".time()." AND (c.content_enddate=0 || c.content_enddate>".time().") AND c.content_class REGEXP '".e_CLASS_REGEXP."' ";

?>