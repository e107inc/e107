<?php
$text = "You can retrieve and parse other site's backend RSS news feeds and display them on your own site from here.<br />Enter the full path URL to the backend (ie http://e107.org/news.xml). If the RSS feed you are grabbing has a url to a link button and you want it displayed leave the image box blank, otherwise put a path in to the image, or enter 'none' to display no image. Then tick the boxes to display exactly what you want to in your headlines menu. You can activate and de-activate the backend if the site goes down for instance.<br /><br />To see the headlines on your site, make sure the  headlines_menu is activated from your menus page.";

$ns -> tablerender("Headlines", $text);
?>