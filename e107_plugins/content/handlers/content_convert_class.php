<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/handlers/content_convert_class.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-17 13:23:59 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php");
include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content_admin.php");

require_once($plugindir."handlers/content_class.php");
$aa = new content;

class content_convert{


		//update content_author
		function upgrade_1_2(){
			global $sql;

			$text = "";
			$field1 = $sql->db_Field("pcontent",19);
			$field2 = $sql->db_Field("pcontent",20);
			$field3 = $sql->db_Field("pcontent",21);
			if($field1 != "content_score" && $field2 != "content_meta" && $field3 != "content_layout"){
				mysql_query("ALTER TABLE ".MPREFIX."pcontent ADD content_score TINYINT ( 3 ) UNSIGNED NOT NULL DEFAULT '0';");
				mysql_query("ALTER TABLE ".MPREFIX."pcontent ADD content_meta TEXT NOT NULL;");
				mysql_query("ALTER TABLE ".MPREFIX."pcontent ADD content_layout VARCHAR ( 255 ) NOT NULL DEFAULT '';");
				$text = CONTENT_ADMIN_CONVERSION_LAN_64."<br />";
			}
			return $text;
		}

		//update content_author
		function upgrade_1_21(){
			global $sql;
			$sql = new db; $sql1 = new db;
			$upgrade = FALSE;
			if($sql -> db_Select("pcontent", "content_id, content_author", "content_author != '' ")){
				while($row = $sql -> db_Fetch()){
					if(is_numeric($row['content_author'])){
					}else{
						$tmp = explode("^", $row['content_author']);
						if($tmp[0] == "0"){
							$upgrade = TRUE;
							$newauthor = $tmp[1].($tmp[2] ? "^".$tmp[2] : "");
							$sql1 -> db_Update("pcontent", " content_author = '".$newauthor."' WHERE content_id='".$row['content_id']."' ");
						}
					}
				}
			}
			if($upgrade){
				return CONTENT_ADMIN_CONVERSION_LAN_65."<br />";
			}else{
				return FALSE;
			}
		}

		//update preferences storage method
		function upgrade_1_22(){
			global $sql, $sql2, $eArrayStorage, $tp, $aa;

			$upgrade = TRUE;

			$sqlc = new db;
			$sqld = new db;

			//convert preferences for core default preferences
			if($sqlc -> db_Select("core", "*", "e107_name='pcontent' ")){
				$row = $sqlc -> db_Fetch();

				$tmp = $eArrayStorage->ReadArray($row['e107_value']);

				//replace the id value for the content_pref
				$content_pref = array();
				foreach($tmp as $k=>$v){
					if(substr($k,-2) == "_0"){
						$k = str_replace("_0", "", $k);
					}
					if(strpos($k, "content_") === 0){
						$content_pref[$k] = $tp->toDB($v);
					}
				}
				if(!isset($content_pref['content_admin_subheading'])){
					//add new options to the preferences
					$content_pref = $this->upgrade_1_22_prefs($content_pref);

					$tmp1 = $eArrayStorage->WriteArray($content_pref);
					$sqld -> db_Update("core", "e107_value = '{$tmp1}' WHERE e107_name = 'pcontent' ");
				}else{
					$upgrade=FALSE;
				}
			}

			//convert preferences for all main parents
			if($sqlc -> db_Select("pcontent", "content_id, content_heading, content_pref", "LEFT(content_parent, 1) = '0' ")){
				while($row=$sqlc->db_Fetch()){

					$id = $row['content_id'];
					$tmp = $eArrayStorage->ReadArray($row['content_pref']);

					//replace the id value for the content_pref
					$l = strlen($id);
					$content_pref = array();
					foreach($tmp as $k=>$v){
						if(substr($k,-($l+1)) == "_".$id){
							$k = str_replace("_".$id, "", $k);
						}
						if(strpos($k, "content_") === 0){
							$content_pref[$k] = $tp->toDB($v);
						}
					}
					//add new options to the preferences
					if(!isset($content_pref['content_admin_subheading'])){
						$content_pref = $this->upgrade_1_22_prefs($content_pref);

						$tmp1 = $eArrayStorage->WriteArray($content_pref);
						$sqld -> db_Update("pcontent", "content_pref='{$tmp1}' WHERE content_id='$id' ");
					}else{
						$upgrade=FALSE;
					}

					//update menus
					$plugintable	= "pcontent";
					$plugindir		= e_PLUGIN."content/";
					if(!is_object($aa)){
						require_once($plugindir."handlers/content_class.php");
						$aa = new content;
					}
					if($row['content_parent']==0){
						//remove menu
						@unlink(e_PLUGIN."content/menus/content_".$row['content_heading']."_menu.php");
						//create menu
						$aa -> CreateParentMenu($id);
					}
				}
			}

			if($upgrade===TRUE){
				return CONTENT_ADMIN_CONVERSION_LAN_66."<br />";
			}
		}

		//add new preferences that come with this upgrade
		function upgrade_1_22_prefs($content_pref){

			//create : item page
			$content_pref['content_admin_subheading'] = '1';
			$content_pref['content_admin_summary'] = '1';
			$content_pref['content_admin_startdate'] = '1';
			$content_pref['content_admin_enddate'] = '1';

			//create : category page
			$content_pref['content_admincat_subheading'] = '1';
			$content_pref['content_admincat_comment'] = '1';
			$content_pref['content_admincat_rating'] = '1';
			$content_pref['content_admincat_pe'] = '1';
			$content_pref['content_admincat_visibility'] = '1';
			$content_pref['content_admincat_startdate'] = '1';
			$content_pref['content_admincat_enddate'] = '1';
			$content_pref['content_admincat_uploadicon'] = '1';
			$content_pref['content_admincat_selecticon'] = '1';

			//create : submit page
			$content_pref['content_submit_subheading'] = '1';
			$content_pref['content_submit_summary'] = '1';
			$content_pref['content_submit_startdate'] = '1';
			$content_pref['content_submit_enddate'] = '1';

			//content manager
			$content_pref['content_manager_approve'] = '255';
			$content_pref['content_manager_personal'] = '255';
			$content_pref['content_manager_category'] = '255';

			return $content_pref;
		}


		//update preferences storage method
		function upgrade_1_23(){
			global $sql, $sql2, $eArrayStorage, $tp, $aa;

			$upgrade = TRUE;

			$sqlc = new db;
			$sqld = new db;
			//add new preferences in core
			if($sqlc -> db_Select("core", "*", "e107_name='pcontent' ")){
				$row = $sqlc -> db_Fetch();

				$content_pref = $eArrayStorage->ReadArray($row['e107_value']);

				//add new options to the preferences
				if(!isset($content_pref['content_admin_subheading'])){
					$content_pref = $this->upgrade_1_23_prefs($content_pref);

					$tmp1 = $eArrayStorage->WriteArray($content_pref);
					$sqld -> db_Update("core", "e107_value = '{$tmp1}' WHERE e107_name = 'pcontent' ");
				}else{
					$upgrade=FALSE;
				}
			}

			//add new preferences for each main parent
			if($sqlc -> db_Select("pcontent", "content_id, content_heading, content_pref", "content_parent = '0' ")){
				while($row=$sqlc->db_Fetch()){

					$id = $row['content_id'];
					$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

					if(!isset($content_pref['content_admin_subheading'])){
						//add new options to the preferences
						$content_pref = $this->upgrade_1_23_prefs($content_pref);

						$tmp1 = $eArrayStorage->WriteArray($content_pref);
						$sqld -> db_Update("pcontent", "content_pref='{$tmp1}' WHERE content_id='$id' ");
					}else{
						$upgrade=FALSE;
					}
				}
			}
			if($upgrade===TRUE){
				return CONTENT_ADMIN_CONVERSION_LAN_67."<br />";
			}
		}
		//add new preferences that come with this upgrade
		function upgrade_1_23_prefs($content_pref){

			$content_pref['content_list_caption'] = CONTENT_LAN_23;			//caption for recent list
			$content_pref['content_list_caption_append_name'] = '1';		//append category heading to caption
			$content_pref['content_catall_caption'] = CONTENT_LAN_25;		//caption for all categories page
			$content_pref['content_cat_caption'] = CONTENT_LAN_26;			//caption for single category page
			$content_pref['content_cat_caption_append_name'] = '1';			//append category heading to caption
			$content_pref['content_cat_sub_caption'] = CONTENT_LAN_28;		//caption for subcategories
			$content_pref['content_cat_item_caption'] = CONTENT_LAN_31;		//caption for items in category
			$content_pref['content_author_index_caption'] = CONTENT_LAN_32;	//caption for author index page
			$content_pref['content_author_caption'] = CONTENT_LAN_32;		//caption for single author page
			$content_pref['content_author_caption_append_name'] = '1';		//append author name to caption
			$content_pref['content_archive_caption'] = CONTENT_LAN_84;		//caption for archive page
			$content_pref['content_top_icon_width'] = '';					//use this size for icon
			$content_pref['content_top_caption'] = CONTENT_LAN_38;			//caption for top rated page
			$content_pref['content_top_caption_append_name'] = '1';			//append category heading to caption
			$content_pref['content_score_icon_width'] = '';					//use this size for icon
			$content_pref['content_score_caption'] = CONTENT_LAN_87;		//caption for top score page
			$content_pref['content_score_caption_append_name'] = '1';		//append category heading to caption

			return $content_pref;
		}

		//update custom theme
		function upgrade_1_24(){
			global $sql, $sql2, $eArrayStorage, $tp, $aa;

			$upgrade = TRUE;

			$sqlc = new db;
			$sqld = new db;
			//add new preferences in core
			if($sqlc -> db_Select("core", "*", "e107_name='pcontent' ")){
				$row = $sqlc -> db_Fetch();

				$content_pref = $eArrayStorage->ReadArray($row['e107_value']);

				//update theme
				if(strpos($content_pref['content_theme'], "{e_")!==FALSE){
				}else{
					$content_pref['content_theme'] = "{e_PLUGIN}content/templates/".$content_pref['content_theme']."/";
				}

				$tmp1 = $eArrayStorage->WriteArray($content_pref);
				$sqld -> db_Update("core", "e107_value = '{$tmp1}' WHERE e107_name = 'pcontent' ");
			}

			//add new preferences for each main parent
			if($sqlc -> db_Select("pcontent", "content_id, content_heading, content_pref", "content_parent = '0' ")){
				while($row=$sqlc->db_Fetch()){

					$id = $row['content_id'];
					$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

					//update theme
					if(strpos($content_pref['content_theme'], "{e_")!==FALSE){
					}else{
						$content_pref['content_theme'] = "{e_PLUGIN}content/templates/".$content_pref['content_theme']."/";
					}

					$tmp1 = $eArrayStorage->WriteArray($content_pref);
					$sqld -> db_Update("pcontent", "content_pref='{$tmp1}' WHERE content_id='$id' ");
				}
			}
			return CONTENT_ADMIN_CONVERSION_LAN_68."<br />";
		}

		//convert rows
		function upgrade_1_1(){
				global $sql, $sql2, $tp, $plugintable, $eArrayStorage;
				$plugintable	= "pcontent";

				$count = "0";
				$sql = new db;
				$thiscount = $sql -> db_Select("pcontent", "*", "ORDER BY content_id ", "mode=no_where" );
				if($thiscount > 0){
					while($row = $sql -> db_Fetch()){

						//main parent
						if($row['content_parent'] == "0"){
							$newparent = "0";

						//subcat
						}elseif(substr($row['content_parent'],0,2) == "0."){
							$newparent = "0".strrchr($row['content_parent'], ".");

						//item
						}elseif( strpos($row['content_parent'], ".") && substr($row['content_parent'],0,1) != "0"){
							$newparent = substr(strrchr($row['content_parent'], "."),1);
						}

						$sql2 -> db_Update("pcontent", " content_parent = '".$newparent."', content_pref='' WHERE content_id='".$row['content_id']."' ");
					}
				}
				return CONTENT_ADMIN_CONVERSION_LAN_58."<br /><br />".CONTENT_ADMIN_CONVERSION_LAN_46."<br />";
		}




		function show_main_intro(){
						global $sql, $ns, $rs, $type, $type_id, $action, $sub_action, $id, $plugintable;
						$plugintable	= "pcontent";

						if(!is_object($sql)){ $sql = new db; }
						$newcontent = $sql -> db_Count($plugintable, "(*)", "");
						if($newcontent > 0){
							return false;
						}else{

							$text .= "
							<div style='text-align:center'>
							<div style='width:70%; text-align:left'>
							".$rs -> form_open("post", e_SELF, "dataform")."
							<table class='fborder'>";
							
							$oldcontent = $sql -> db_Count("content", "(*)", "");
							if($oldcontent > 0){
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_8." ".CONTENT_ADMIN_MAIN_LAN_9." ".CONTENT_ADMIN_MAIN_LAN_11."</td></tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_18."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_19."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_43."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "convert_table", CONTENT_ADMIN_CONVERSION_LAN_59)."</td>
								</tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_22."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_23."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_54."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "create_default", CONTENT_ADMIN_CONVERSION_LAN_60)."</td>
								</tr>";

								$text .= "<tr><td style='height:20px; border:0;' colspan='2'></td></tr>";
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_20."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_21."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_56."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("button", "fresh", CONTENT_ADMIN_CONVERSION_LAN_61, "onclick=\"document.location='".e_PLUGIN."content/admin_content_config.php?type.0.cat.create'\"
								")."</td>
								</tr>";

							}else{
								$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_8." ".CONTENT_ADMIN_MAIN_LAN_9." ".CONTENT_ADMIN_MAIN_LAN_24."</td></tr>";
								$text .= "<tr><td class='forumheader3' colspan='2'>".CONTENT_ADMIN_MAIN_LAN_25."</td></tr>";
								$text .= "
								<tr>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_54."</td>
									<td class='forumheader3' style='width:50%; white-space:nowrap;'>".$rs -> form_button("submit", "create_default", CONTENT_ADMIN_CONVERSION_LAN_60)."</td>
								</tr>";
							}

							$text .= "</table>".$rs -> form_close()."
							</div>
							</div>";

							$ns -> tablerender(CONTENT_ADMIN_MAIN_LAN_7, $text);
							return true;
						}
		}

		//function to insert default preferences for a main parent
		function insert_default_prefs($id){
				global $sql, $aa, $plugintable, $eArrayStorage;
				$plugintable	= "pcontent";
				$plugindir		= e_PLUGIN."content/";
				unset($content_pref, $tmp);
				
				if(!is_object($aa)){
					require_once($plugindir."handlers/content_class.php");
					$aa = new content;
				}

				$content_pref = $aa -> ContentDefaultPrefs($id);
				$tmp = $eArrayStorage->WriteArray($content_pref);

				$sql -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$id' ");
		}


		//function to convert comments
		function convert_comments(){
				global $plugintable;
				$plugintable	= "pcontent";

				if(!is_object($sqlcc)){ $sqlcc = new db; }
				$numc = $sqlcc -> db_Count("comments", "(*)", "WHERE comment_type = '1' ");
				if($numc > 0){
					$sqlcc -> db_Update("comments", "comment_type = '".$plugintable."' WHERE comment_type = '1' ");
				}
		}


		//function to convert rating
		function convert_rating(){
				global $plugintable;
				$plugintable	= "pcontent";

				if(!is_object($sqlcr)){ $sqlcr = new db; }
				$numr = $sqlcr -> db_Count("rate", "(*)", "WHERE (rate_table = 'article' || rate_table = 'review' || rate_table = 'content') ");
				if($numr > 0){
					$sqlcr -> db_Update("rate", "rate_table = '".$plugintable."' WHERE (rate_table = 'article' || rate_table = 'review' || rate_table = 'content') ");
				}
		}


		//create main parent
		function create_mainparent($name, $tot, $order){
				global $sql, $aa, $plugintable, $tp;
				$plugintable	= "pcontent";

				$sql = new db;
				$sql -> db_Select("content", "MAX(content_id) as maxcid", "", "mode=no_where");
				list($maxcid) = $sql -> db_Fetch();
				$newid = $maxcid + $order;

				// ##### STAGE 4 : INSERT MAIN PARENT FOR ARTICLE ---------------------------------------------
				$checkinsert = FALSE;
				if($tot > 0){
					//if(!is_object($sql)){ $sql = new db; }
					$sql = new db;
					if(!$sql -> db_Select($plugintable, "content_heading", "content_heading = '".$name."' AND content_parent = '0' ")){
						$name = $tp -> toDB($name);

						$sql -> db_Insert($plugintable, "'".$newid."', '".$name."', '', '', '', '1', '', '', '', '0', '0', '0', '0', '', '".time()."', '0', '0', '', '".$order."', '0', '', '' ");

						//check if row is present in the db (is it a valid insert)
						//if(!is_object($sql2)){ $sql2 = new db; }
						$sql2 = new db;
						if(!$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' ")){
							$message = CONTENT_ADMIN_CONVERSION_LAN_45;
						}else{
							$message = $name." ".CONTENT_ADMIN_CONVERSION_LAN_7."<br />";
							$checkinsert = TRUE;

							//select main parent id
							$sql3 = new db;
							//if(!is_object($sql3)){ $sql3 = new db; }
							$sql3 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
							list($main_id) = $sql3 -> db_Fetch();

							//insert default preferences
							$this -> insert_default_prefs($main_id);

							//create menu
							$aa -> CreateParentMenu($main_id);

							$message .= $name." ".CONTENT_ADMIN_CONVERSION_LAN_8."<br />";
						}
					}else{
						$message = CONTENT_ADMIN_CONVERSION_LAN_9." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_10." : ".CONTENT_ADMIN_CONVERSION_LAN_53."<br />";
					}
				}else{
					$message = CONTENT_ADMIN_CONVERSION_LAN_9." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_10."<br />";
				}
				$create_mainparent = array($checkinsert, $message);		
				return $create_mainparent;
		}


		//analayse unknown rows
		function analyse_unknown(){
				global $sql;

				if(!is_object($sql)){ $sql = new db; }
				$totaloldrowsunknown = $sql -> db_Select("content", "*", " NOT ( (content_parent = '1' AND content_type = '1') || (content_parent = '0' AND content_type = '1') || (content_parent = '0' AND content_type = '6') || (content_parent = '0' AND content_type = '10') || (content_type = '3' || content_type = '16') || (content_type = '0' || content_type = '15') ) ");

				while($row = $sql -> db_Fetch()){
					$unknown_bug[]		= $row['content_id']." ".$row['content_heading'];
					$unknown_bug_id[]	= $row['content_id'];
					$unknown_bug_type[]	= "parent=".$row['content_parent']." - type=".$row['content_type'];
				}
				$analyse_unknown = array($unknown_bug, $unknown_bug_id);
				return $analyse_unknown;
		}


		//convert categories
		function convert_category($name, $query, $ordernr){
				global $sql, $plugintable, $tp;
				$plugintable	= "pcontent";

				// ##### STAGE 7 : INSERT CATEGORY ----------------------------------------------------
				if(!is_object($sql)){ $sql = new db; }
				if(!$sql -> db_Select("content", "*", " ".$query." ORDER BY content_id " )){
					$cat_present = false;
				}else{
					$count = $ordernr;
					$cat_present = true;
					while($row = $sql -> db_Fetch()){

						//select main parent id
						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
						list($main_id) = $sql2 -> db_Fetch();

						//summary can contain link to image in e107_images/link_icons/".$summary." THIS STILL NEEDS TO BE CHECKED
						$newcontent_heading		= $tp -> toDB($row['content_heading']);
						$newcontent_subheading	= ($row['content_subheading'] ? $tp -> toDB($row['content_subheading']) : "");
						$newcontent_summary		= ($row['content_summary'] ? $tp -> toDB($row['content_summary']) : "");
						$newcontent_text		= $tp -> toDB($row['content_content']);
						$newcontent_author		= (is_numeric($row['content_author']) ? $row['content_author'] : "0^".$row['content_author']);
						$newcontent_icon		= "";
						$newcontent_attach		= "";
						$newcontent_images		= "";
						$newcontent_parent		= "0.".$main_id;			//make each category a first level subcat of the main parent
						$newcontent_comment		= $row['content_comment'];
						$newcontent_rate		= "0";
						$newcontent_pe			= $row['content_pe_icon'];
						$newcontent_refer		= "";
						$newcontent_starttime	= $row['content_datestamp'];
						$newcontent_endtime		= "0";
						$newcontent_class		= $row['content_class'];
						$newcontent_pref		= "";
						$newcontent_score		= "0";
						$newcontent_meta		= "";
						$newcontent_layout		= "";

						if(!is_object($sql3)){ $sql3 = new db; }
						$sql3 -> db_Insert($plugintable, "'".$row['content_id']."', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."', '".$newcontent_score."', '".$newcontent_meta."', '".$newcontent_layout."' ");

						if(!$sql3 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
							$bug_cat_insert[]	= $row['content_id']." ".$row['content_heading'];
						}else{
							$valid_cat_insert[]	= $row['content_id']." ".$row['content_heading'];
							$count = $count + 1;
						}
					}
				}
				$convert_category = array($cat_present, $valid_cat_insert, $bug_cat_insert, $count);
				return $convert_category;
		}


		//convert rows
		function convert_row($name, $query, $startorder){
				global $sql, $tp, $plugintable, $eArrayStorage;
				$plugintable	= "pcontent";

				// ##### STAGE 8 : INSERT ROW -------------------------------------------------------------
				if(!is_object($sql)){ $sql = new db; }
				if(!$thiscount = $sql -> db_Select("content", "*", " ".$query." ORDER BY content_id " )){
					$check_present = false;
				}else{
					$count = $startorder;
					$check_present = true;
					while($row = $sql -> db_Fetch()){

						$oldcontentid = $row['content_id'];

						//select main parent id
						if(!is_object($sql2)){ $sql2 = new db; }
						$sql2 -> db_Select($plugintable, "content_id", "content_heading = '".$name."' AND content_parent = '0' ");
						list($main_id) = $sql2 -> db_Fetch();

						//item is in main cat
						if($row['content_parent'] == "0"){
							$newcontent_parent = $main_id;

						//item is in sub cat
						}else{
							//select old review cat heading
							if(!is_object($sql3)){ $sql3 = new db; }
							if(!$sql3 -> db_Select("content", "content_id, content_heading", "content_id = '".$row['content_parent']."' ")){
								$bug_oldcat[]			= $row['content_id']." ".$row['content_heading'];
								$newcontent_parent		= $main_id;
							}else{
								list($old_cat_id, $old_cat_heading) = $sql3 -> db_Fetch();

								//select new cat id from the cat with the old_cat_heading
								if(!is_object($sql4)){ $sql4 = new db; }
								if(!$sql4 -> db_Select($plugintable, "content_id", "content_heading = '".$old_cat_heading."' AND content_parent = '0.".$main_id."' ")){
									$bug_newcat[]		= $row['content_id']." ".$row['content_heading'];
									$newcontent_parent	= $main_id;
								}else{
									list($new_cat_id) = $sql4 -> db_Fetch();
									$newcontent_parent	= $new_cat_id;
								}
							}
						}
						
						if (strstr($row['content_content'], "{EMAILPRINT}")) {
							$row['content_content'] = str_replace("{EMAILPRINT}", "", $row['content_content']);
						}

						$newcontent_heading		= $tp -> toDB($row['content_heading']);
						$newcontent_subheading	= ($row['content_subheading'] ? $tp -> toDB($row['content_subheading']) : "");
						//summary can contain link to image in e107_images/link_icons/".$summary." THIS STILL NEEDS TO BE CHECKED
						$newcontent_summary		= ($row['content_summary'] ? $tp -> toDB($row['content_summary']) : "");
						$newcontent_text		= $tp -> toDB($row['content_content']);
						//$newcontent_author	= (is_numeric($row['content_author']) ? $row['content_author'] : "0^".$row['content_author']);
						$newcontent_author		= $row['content_author'];
						$newcontent_icon		= "";
						$newcontent_attach		= "";
						$newcontent_images		= "";
						$newcontent_comment		= $row['content_comment'];
						$newcontent_rate		= "0";
						$newcontent_pe			= $row['content_pe_icon'];
						$newcontent_refer		= ($row['content_type'] == "15" || $row['content_type'] == "16" ? "sa" : "");
						$newcontent_starttime	= $row['content_datestamp'];
						$newcontent_endtime		= "0";
						$newcontent_class		= $row['content_class'];
						$newcontent_pref		= "";
						$newcontent_score		= ($row['content_review_score'] && $row['content_review_score'] != "none" ? $row['content_review_score'] : "0");
						$newcontent_meta		= "";
						$newcontent_layout		= "";

						if(!is_object($sql5)){ $sql5 = new db; }
						$sql5 -> db_Insert($plugintable, "'".$row['content_id']."', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '1.".$count."', '".$newcontent_score."', '".$newcontent_meta."', '".$newcontent_layout."' ");

						if(!is_object($sql6)){ $sql6 = new db; }
						if(!$sql6 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
							$bug_insert[] = $row['content_id']." ".$row['content_heading'];
						}else{
							$valid_insert[] = $row['content_id']." ".$row['content_heading'];
							$count = $count + 1;
						}
					}
				}
				$convert_row = array($check_present, $count, $valid_insert, $bug_insert, $bug_oldcat, $bug_newcat);
				return $convert_row;
		}


		//show output of the category conversion
		function results_conversion_category($name, $array, $oldrows){

				//no pages present
				if($array[0] === false){
					if( !(count($array[1]) > 0 || count($array[2]) > 0) ){
						$text .= "<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_34." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_35."</td></tr>";
					}
				
				//pages present
				}else{
				
					//valid inserts
					if(count($array[1]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".count($array[1])." ".CONTENT_ADMIN_CONVERSION_LAN_38."</td>
							<td class='forumheader3'><a style='cursor:pointer;' onclick=\"expandit('validcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='validcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[1]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_OK." ".$array[1][$i]."</td>
											<td>".$name." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}

					//bug inserts
					if(count($array[2]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_39."</td>
							<td class='forumheader3'><a style='cursor:pointer;' onclick=\"expandit('failedcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='failedcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[2]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_ERROR." ".$array[2][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_23."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					$text .= "
					<tr>
						<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_3." : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td>
						<td class='forumheader3'>
							<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analysecat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
							<div id='analysecat_{$name}' style='display: none;'>
								<table style='width:100%; border:0;'>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_28."</td><td>".$oldrows."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_29."</td><td>".count($array[1])."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_30."</td><td>".count($array[2])."</td></tr>
								</table>
							</div>
						</td>
					</tr>";
				}
				return $text;
		}


		//show output of the item conversion
		function results_conversion_row($name, $array, $oldrows){

				//no rows present
				if($array[0] === false){
					$text .= "<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_34." ".$name." ".CONTENT_ADMIN_CONVERSION_LAN_36."</td></tr>";
				
				//rows present
				}else{
				
					//valid insert
					if(count($array[2]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[2])." ".CONTENT_ADMIN_CONVERSION_LAN_38."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('valid_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='valid_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[2]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_OK." ".$array[2][$i]."</td>
											<td>".$name." ".CONTENT_ADMIN_CONVERSION_LAN_6." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : old category
					if(count($array[4]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[4])." ".CONTENT_ADMIN_CONVERSION_LAN_31."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('oldcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='oldcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[4]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_WARNING." ".$array[4][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_32."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : new category
					if(count($array[5]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[5])." ".CONTENT_ADMIN_CONVERSION_LAN_31."</td>
							<td class='forumheader3'>
								<a style='cursor:pointer;' onclick=\"expandit('newcat_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='newcat_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[5]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_WARNING." ".$array[5][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_33."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					//bugs : insertion failed
					if(count($array[3]) > 0 ){
						$text .= "
						<tr>
							<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".count($array[3])." ".CONTENT_ADMIN_CONVERSION_LAN_39."</td>
							<td class='forumheader3'>
								<a style='cursor: pointer; cursor: hand' onclick=\"expandit('failed_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
								<div id='failed_{$name}' style='display: none;'>
									<table style='width:100%; border:0;'>";
									for($i=0;$i<count($array[3]);$i++){
										$text .= "
										<tr>
											<td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_ERROR." ".$array[3][$i]."</td>
											<td>".CONTENT_ADMIN_CONVERSION_LAN_23."</td>
										</tr>";
									}
									$text .= "
									</table>
								</div>
							</td>
						</tr>";
					}
					
					//analyses
					$text .= "
					<tr>
						<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_6." : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td>
						<td class='forumheader3'>
							<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analyse_{$name}');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
							<div id='analyse_{$name}' style='display: none;'>
								<table style='width:100%; border:0;'>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_28."</td><td>".$oldrows."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_29."</td><td>".count($array[2])."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_30."</td><td>".count($array[3])."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_31."</td><td>".count($array[4])."</td></tr>
								<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_31."</td><td>".count($array[5])."</td></tr>
								</table>
							</div>
						</td>
					</tr>";
				}


				return $text;
		}


		//show output of the mainparent conversion
		function results_conversion_mainparent($content, $review, $article){
				$text = "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_50."</td></tr>";
				$text .= "
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".($content[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_20."</td>
					<td class='forumheader3'>
						<a style='cursor: pointer; cursor: hand' onclick=\"expandit('contentmain');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
						<div id='contentmain' style='display: none;'>
							".$content[1]."
						</div>
					</td>
				</tr>
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".($review[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_21."</td>
					<td class='forumheader3'>
						<a style='cursor: pointer; cursor: hand' onclick=\"expandit('reviewmain');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
						<div id='reviewmain' style='display: none;'>
							".$review[1]."
						</div>
					</td>
				</tr>
				<tr>
					<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".($article[0] == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".CONTENT_ADMIN_CONVERSION_LAN_22."</td>
					<td class='forumheader3'>
						<a style='cursor: pointer; cursor: hand' onclick=\"expandit('articlemain');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
						<div id='articlemain' style='display: none;'>
							".$article[1]."
						</div>
					</td>
				</tr>";

							

				return $text;
		}

}

?>