<?php
$text = "You can retrieve and parse other site's backend RSS news feeds and display them on your own site from here.<br />Enter the full path URL to the backend (ie http://e107.org/news.xml). You can add a path to an image if you don't like the default one, or it isn't defined. You can activate and de-activate the backend if the site goes down for instance.<br /><br />To see the headlines on your site, make sure the  headlines_menu is activated from your menus page.";

$ns -> tablerender("Headlines", $text);
?>