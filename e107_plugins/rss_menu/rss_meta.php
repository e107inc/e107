<?php

if(!e_PLUGIN){ exit; }

	$rss = explode(",",$pref['rss_feeds']);
	$feedlist[1] = "News";
	$feedlist[9] = "Chatbox Posts";
	$feedlist[12] = "Downloads";
	$feedlist[6] = "Forum Threads";
	$feedlist[7] = "Forum Posts";

foreach($rss as $rss_id){
	echo "<link rel='alternate' type='application/rss+xml' title='".SITENAME." RSS ".$feedlist[$rss_id] ."' href='".$e107->http_abs_location("PLUGINS_DIRECTORY", "rss_menu/rss.php?{$rss_id}.2")."' />\n";
}

if($pref['rss_newscats']){
    $sql -> db_Select("news_category");
        while($row = $sql-> db_Fetch()){
		echo "<link rel='alternate' type='application/rss+xml' title='".SITENAME." RSS News - ".$row['category_name'] ."' href='".$e107->http_abs_location("PLUGINS_DIRECTORY", "rss_menu/rss.php?1.2.{$row['category_id']}")."' />\n";
        }
}


?>