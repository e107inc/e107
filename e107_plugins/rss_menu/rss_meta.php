<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/rss_meta.php,v $
|     $Revision: 1.9 $
|     $Date: 2006-01-25 20:17:09 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

	$rss = explode(",",$pref['rss_feeds']);
	$feedlist[1] = "News";
	$feedlist[12] = "Downloads";
	$feedlist[6] = "Forum Threads";
	$feedlist[7] = "Forum Posts";

foreach($rss as $rss_id){
	if(!is_numeric($rss_id)){
    	$feedlist[$rss_id] = ucfirst($rss_id);
	}
	echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)." ".$feedlist[$rss_id] ."' href='".SITEURLBASE.e_PLUGIN_ABS."rss_menu/rss.php?{$rss_id}.2' />\n";
}

if($pref['rss_newscats']){
    $sql -> db_Select("news_category");
        while($row = $sql-> db_Fetch()){
		echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)." News - ".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)."' href='".SITEURLBASE.e_PLUGIN_ABS."rss_menu/rss.php?1.2.{$row['category_id']}' />\n";
        }
}

if($pref['rss_dlcats']){
	$class_list = "0,251,252,253";
    $sql -> db_Select("download_category","*","download_category_class IN (".$class_list.") ORDER BY download_category_name");
        while($row = $sql-> db_Fetch()){
		echo "<link rel='alternate' type='application/rss+xml' title='".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)." Downloads - ".htmlspecialchars(SITENAME, ENT_QUOTES, CHARSET)."' href='".SITEURLBASE.e_PLUGIN_ABS."rss_menu/rss.php?12.2.{$row['download_category_id']}' />\n";
        }
}


?>