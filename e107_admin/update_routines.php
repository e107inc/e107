<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/update_routines.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-12 16:31:01 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");

$dbupdate=array(                                "61x_to_700" => LAN_UPDATE_8." .61x ".LAN_UPDATE_9." .7",
                                                "616_to_617" => LAN_UPDATE_8." .616 ".LAN_UPDATE_9." .617",
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


function update_61x_to_700($type){
        global $sql,$ns;
        if($type=="do"){
            $sql -> db_Update("userclass_classes", "userclass_editclass='254' WHERE userclass_editclass ='0' ");

			 mysql_query("ALTER TABLE ".MPREFIX."banner CHANGE banner_active banner_active TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
			 $sql -> db_Update("banner", "banner_active='255' WHERE banner_active = '0' ");
			 $sql -> db_Update("banner", "banner_active='0' WHERE banner_active = '1' ");

        }else{
           if(!$sql -> db_Select("userclass_classes", "*", "userclass_editclass='254'") && $sql -> db_Count("userclass_classes") > 0 ){
               return FALSE;
           }else{
               return TRUE;
           }
        }
}


function update_616_to_617($type){
        global $sql;
        if($type=="do"){
                mysql_query("ALTER TABLE  ".MPREFIX."poll ADD poll_comment TINYINT( 3 ) UNSIGNED DEFAULT '1' NOT NULL ");
                mysql_query("ALTER TABLE  ".MPREFIX."menus ADD menu_pages TEXT NOT NULL ");
                $sql2 = new db;
                $sql2 -> db_Update("poll", "poll_comment='1' WHERE poll_id!='0'");
        } else {
                global $mySQLdefaultdb;
                $fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."menus");
                $columns = mysql_num_fields($fields);
                for ($i = 0; $i < $columns; $i++) {
                   if("menu_pages" == mysql_field_name($fields, $i)){return TRUE;}
                }
                return FALSE;
        }
}

function update_615_to_616($type){
        global $sql;
        if($type=="do"){
                mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (4, 'This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on.', '0')");
                mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (5, 'Member rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in members will see this.', '0')");
                mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (6, 'Administrator rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in administrators will see this.', '0')");
                mysql_query("ALTER TABLE ".MPREFIX."download ADD download_comment TINYINT( 3 ) UNSIGNED NOT NULL ");
                mysql_query("ALTER TABLE ".MPREFIX."chatbox CHANGE cb_nick cb_nick VARCHAR( 30 ) NOT NULL ");
                mysql_query("ALTER TABLE ".MPREFIX."comments CHANGE comment_type comment_type VARCHAR( 10 ) DEFAULT '0' NOT NULL ");
                 mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_pid INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL AFTER comment_id ");
                mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_subject VARCHAR( 100 ) NOT NULL AFTER comment_item_id ");
                mysql_query("ALTER TABLE ".MPREFIX."user ADD user_customtitle VARCHAR( 100 ) NOT NULL AFTER user_name ");
                mysql_query("ALTER TABLE ".MPREFIX."parser ADD UNIQUE (parser_regexp)");
                mysql_query("ALTER TABLE ".MPREFIX."userclass_classes ADD userclass_editclass TINYINT( 3 ) UNSIGNED NOT NULL ");
                update_extended_616();
        } else {
                global $mySQLdefaultdb;
                $fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."userclass_classes");
                $columns = mysql_num_fields($fields);
                for ($i = 0; $i < $columns; $i++) {
                   if("userclass_editclass" == mysql_field_name($fields, $i)){return TRUE;}
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

function update_extended_616(){
        global $sql, $ns;
        $sql2 = new db;
        if($sql2 -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                $row = $sql2 -> db_Fetch();
                $user_extended = unserialize($row[0]);
                if(count($user_extended)){
                        if($sql -> db_Select("user","user_id,user_prefs")){
                                while($row = $sql -> db_Fetch()){
                                        $uid = $row[0];
                                        $user_pref = unserialize($row[1]);
                                        foreach($user_extended as $key => $v){
                                                list($fname,$null)=explode("|",$v,2);
                                                $fname = $v;
                                                if(isset($user_pref[$fname])){
                                                        $user_pref["ue_{$key}"]=$user_pref[$fname];
                                                        unset($user_pref[$fname]);
                                                }
                                        }
                                        $tmp = addslashes(serialize($user_pref));
                                        $sql2 -> db_Update("user", "user_prefs='$tmp' WHERE user_id=$uid");
                                }
                        }
                }
        }
        $ns -> tablerender("Extended Users","Updated extended user field data");
}

?>