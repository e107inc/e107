<?php

$dbupdate=array(	
						"615_to_616" => LAN_UPDATE_8." .615 ".LAN_UPDATE_9." .616",
						"614_to_615" => LAN_UPDATE_8." .614 ".LAN_UPDATE_9." .615",
						"613b_to_614" => LAN_UPDATE_8." .613b ".LAN_UPDATE_9." .614",
						"611_to_612" => LAN_UPDATE_8." .611 ".LAN_UPDATE_9." .612",
						"603_to_604" => LAN_UPDATE_8." .603 ".LAN_UPDATE_9." .604",
					);


function update_check(){
	global $ns, $dbupdate;
	foreach($dbupdate as $func => $rmks){
		if(function_exists("update_".$func)){
			if(!call_user_func("update_".$func)){
				$update_needed=TRUE;
				continue;
			}
		}
	}
	if($update_needed === TRUE){
		$txt = "<div style='text-align:center;'>".ADLAN_120;
		$txt .= "<br /><form method='POST' action='".e_ADMIN."e107_update.php'>
		<input class='button' type='submit' value='".ADLAN_122."' />
		</form></div>";
		$ns -> tablerender(ADLAN_122,$txt);
	}
}

function update_615_to_616($type){
	global $sql;
	if($type=="do"){
 		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_pid INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL AFTER comment_id ");
		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_subject VARCHAR( 100 ) NOT NULL AFTER comment_item_id ");
		mysql_query("ALTER TABLE ".MPREFIX."user ADD user_customtitle VARCHAR( 100 ) NOT NULL AFTER user_name ");

	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."user");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
	   	if("user_customtitle" == mysql_field_name($fields, $i)){return TRUE;}
		}
		return FALSE;
	}
}

function update_614_to_615($type){
	global $sql;
	if($type=="do"){
		mysql_query("ALTER TABLE ".MPREFIX."submitnews ADD submitnews_category TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER submitnews_title");
		mysql_query("ALTER TABLE ".MPREFIX."upload ADD upload_category TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
		mysql_query("ALTER TABLE ".MPREFIX."online ADD online_pagecount tinyint(3) unsigned NOT NULL default '0'");
		mysql_query("ALTER TABLE ".MPREFIX."submitnews ADD submitnews_file VARCHAR(100) NOT NULL default '' ");

		global $DOWNLOADS_DIRECTORY;
		$sql2 = new db;
		$sql -> db_Select("download", "download_id, download_url", "download_filesize=0");
		while($row = $sql -> db_Fetch()){
			extract($row);
			$sql2 -> db_Update("download", "download_filesize='".filesize(e_BASE.$DOWNLOADS_DIRECTORY.$download_url)."' WHERE download_id='".$download_id."'");
		}
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."submitnews");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
	   	if("submitnews_file" == mysql_field_name($fields, $i)){return TRUE;}
		}
		return FALSE;
	}
}


function update_613b_to_614($type){
	global $sql;
	if($type=="do"){
		$qry = "CREATE TABLE ".MPREFIX."parser (
		parser_id int(10) unsigned NOT NULL auto_increment,
		parser_pluginname varchar(100) NOT NULL default '',
	  	parser_regexp varchar(100) NOT NULL default '',
	  	PRIMARY KEY  (parser_id)) TYPE=MyISAM;";
		$sql -> db_Select_gen($qry);
	} else {
		if($sql -> db_Select("parser") === FALSE){
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

function update_611_to_612($type){
	global $sql;
	if($type=="do"){
		mysql_query("ALTER TABLE ".MPREFIX."news ADD news_render_type TINYINT UNSIGNED NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_parent content_parent INT UNSIGNED DEFAULT '0' NOT NULL ");
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."news");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
	   	if("news_render_type" == mysql_field_name($fields, $i)){return TRUE;}
		}
		return FALSE;
	}
}

function update_603_to_604($type){
	global $sql;
	if($type=="do"){
		mysql_query("ALTER TABLE ".MPREFIX."link_category ADD link_category_icon VARCHAR( 100 ) NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."headlines ADD headline_image VARCHAR( 100 ) NOT NULL AFTER headline_description");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_page content_parent TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."content ADD content_review_score TINYINT UNSIGNED NOT NULL AFTER content_type");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_author content_author VARCHAR( 200 ) NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."content ADD content_pe_icon TINYINT( 1 ) UNSIGNED NOT NULL AFTER content_review_score");
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."link_category");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
	   	if("link_category_icon" == mysql_field_name($fields, $i)){return TRUE;}
		}
		return FALSE;
	}
}

?>