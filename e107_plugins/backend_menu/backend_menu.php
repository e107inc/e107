<?php
global $FILES_DIRECTORY;
$text = "<div style='text-align:center' class='smalltext'>
".BACKEND_MENU_L1."<br />
<a href='".SITEURL.$FILES_DIRECTORY."backend/news.xml'>news.xml</a> - <a href='".SITEURL.$FILES_DIRECTORY."backend/news.txt'>news.txt</a>
</div>";

$caption = (file_exists(THEME."images/backend_menu.png") ? "<img src='".THEME."images/backend_menu.png' alt='' style='vertical-align:middle' /> ".BACKEND_MENU_L2 : BACKEND_MENU_L2);

$ns -> tablerender($caption, $text);
?>