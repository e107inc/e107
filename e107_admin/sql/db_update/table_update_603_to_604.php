<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/table update #1
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if(ADMINPERMS == "0"){
	mysql_query("ALTER TABLE ".MPREFIX."link_category ADD link_category_icon VARCHAR( 100 ) NOT NULL");
	mysql_query("ALTER TABLE ".MPREFIX."headlines ADD headline_image VARCHAR( 100 ) NOT NULL AFTER headline_description");
	mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_page content_parent TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
	mysql_query("ALTER TABLE ".MPREFIX."content ADD content_review_score TINYINT UNSIGNED NOT NULL AFTER content_type");
	mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_author content_author VARCHAR( 200 ) NOT NULL");
	mysql_query("ALTER TABLE ".MPREFIX."content ADD content_pe_icon TINYINT( 1 ) UNSIGNED NOT NULL AFTER content_review_score");
	$text = "<div style='text-align:center'>Alterations made successfully to the database, please now delete the following file from your server ...<br /><br /><b>".ADMINDIR."sql/db_updates/table_update_603_to_604.php</b><br />";
	$ns -> tablerender("Upgrade completed", $text);
}





?>