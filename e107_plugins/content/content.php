<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/content.php,v $
|		$Revision: 1.7 $
|		$Date: 2005-02-08 00:15:25 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");
require_once(e_HANDLER."emailprint_class.php");
$ep = new emailprint;
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(e_HANDLER."rate_class.php");
$rater = new rater;
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_PLUGIN."content/handlers/content_class.php");
$aa = new content;

$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';
include(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content.php');

// ##### QUERY HANDLER ----------------------------------------------------------------
if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	//if(!empty($tmp[0])) { $type = $tmp[0]; }
	$type = $tmp[0];
	if(!empty($tmp[1])) { $type_id = $tmp[1]; }
	if(!empty($tmp[2])) { $action = $tmp[2]; }
	if(!empty($tmp[3])) { $sub_action = $tmp[3]; }
	if(!empty($tmp[4])) { $id = $tmp[4]; }
	if(!empty($tmp[5])) { $id2 = $tmp[5]; }
	unset($tmp);
}
$query="";

if($type == "0" || is_numeric($type)){ //is only when nextprev is active
	$from = $type;
	$type = $type_id;
	$type_id = $action;
	$action = $sub_action;
	$sub_action = $id;
	$id = $id2;
}else{
	$from = "0";
}
// ##### ------------------------------------------------------------------------------

// ##### DEFINITION OF e_PAGETITLE ----------------------------------------------------
if(!e_QUERY){
	$page = CONTENT_LAN_0." / ".CONTENT_LAN_51;
}elseif($type == "type" && is_numeric($type_id)){
	if($sql -> db_Select($plugintable, "content_heading", "content_id = '".$type_id."'")){
		$row = $sql -> db_Fetch(); extract($row);
	}
	$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_1;
	if(isset($action)){
		if($action == "cat"){
			if(!isset($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_2;
			}elseif(is_numeric($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_3." / ";
				$query = "content_id = '".$sub_action."' ";
			}
		}elseif($action == "author"){
			if(!isset($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_4;
			}elseif(is_numeric($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_5." / ".CONTENT_LAN_1;
			}
		}elseif($action == "content"){
			if(!isset($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ".CONTENT_LAN_5;
			}elseif(is_numeric($sub_action)){
				$page = CONTENT_LAN_0." / ".$content_heading." / ";
				$query = "content_id = '".$sub_action."' ";
			}
		}
	}
}
if($query){
	if($sql -> db_Select($plugintable, "content_heading", $query)){
		$row = $sql -> db_Fetch(); extract($row);
		define("e_PAGETITLE", $page." ".$content_heading);
	}
}else{
	define("e_PAGETITLE", $page);
}
// ##### ------------------------------------------------------------------------------


require_once(HEADERF);

function core_head(){
	global $sql, $tp, $pref, $plugintable;
	global $type, $type_id, $action, $sub_action;

	if($type == "type" && is_numeric($type_id) && $action == "content" && is_numeric($sub_action) ){
			if($sql -> db_Select($plugintable, "content_pref as custom", "content_id='".$sub_action."'")){
				$row = $sql -> db_Fetch(); extract($row);
			
				$custom = unserialize(stripslashes($custom));
				$custom_meta = $custom['content_custom_meta'];
				if($custom_meta != ""){
					$custom_meta = str_replace(", ", ",", $custom_meta);
					$custom_meta = str_replace(" ", ",", $custom_meta);
					$custom_meta = str_replace(",", ", ", $custom_meta);
					$newmeta = "";
					$checkmeta = preg_match_all("/\<meta name=&#039;(.*?)&#039; content=&#039;(.*?)&#039;>/si", $pref['meta_tag'], $matches);
					for($i=0;$i<count($matches[1]);$i++){
						if($matches[1][$i] == "keywords"){
							$newmeta .= "<meta name=&#039;".$matches[1][$i]."&#039; content=&#039;".$matches[2][$i].", ".$custom_meta."&#039;>";
						}else{
							$newmeta .= "<meta name=&#039;".$matches[1][$i]."&#039; content=&#039;".$matches[2][$i]."&#039;>";
						}
					}
					$pref['meta_tag'] = $tp -> toHTML($newmeta, "admin");
				}
			}
	}
}


//retrieve and parse the preferences
if(isset($type) && $type == "type" && is_numeric($type_id)){
		$content_pref = $aa -> getContentPref($type_id);
		$content_pref["content_cat_icon_path_large_{$type_id}"] = ($content_pref["content_cat_icon_path_large_{$type_id}"] ? $content_pref["content_cat_icon_path_large_{$type_id}"] : "{e_PLUGIN}content/images/cat/48/" );
		$content_pref["content_cat_icon_path_small_{$type_id}"] = ($content_pref["content_cat_icon_path_small_{$type_id}"] ? $content_pref["content_cat_icon_path_small_{$type_id}"] : "{e_PLUGIN}content/images/cat/16/" );
		$content_pref["content_icon_path_{$type_id}"] = ($content_pref["content_icon_path_{$type_id}"] ? $content_pref["content_icon_path_{$type_id}"] : "{e_PLUGIN}content/images/icon/" );
		$content_pref["content_image_path_{$type_id}"] = ($content_pref["content_image_path_{$type_id}"] ? $content_pref["content_image_path_{$type_id}"] : "{e_PLUGIN}content/images/image/" );
		$content_pref["content_file_path_{$type_id}"] = ($content_pref["content_file_path_{$type_id}"] ? $content_pref["content_file_path_{$type_id}"] : "{e_PLUGIN}content/images/file/" );
		$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
		$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
		$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
		$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
		$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);
		$number = ($content_pref["content_nextprev_number_{$type_id}"] ? $content_pref["content_nextprev_number_{$type_id}"] : "5");
		$nextprevquery = ($content_pref["content_nextprev_{$type_id}"] ? "LIMIT ".$from.",".$number : "");
}else{
		$content_pref = $aa -> getContentPref();
		$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_0"]);
		$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_0"]);
		$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_0"]);
		$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_0"]);
		$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_0"]);
		$number = ($content_pref["content_nextprev_number_0"] ? $content_pref["content_nextprev_number_0"] : "5");
		$nextprevquery = ($content_pref["content_nextprev_0"] ? "LIMIT ".$from.",".$number : "");
}

//post comment
if(IsSet($_POST['commentsubmit'])){
	$tmp = explode(".", e_QUERY);
	if(!$sql -> db_Select($plugintable, "content_comment", "content_id='$sub_action' ")){
		header("location:".e_BASE."index.php");
		exit;
	}else{
		$row = $sql -> db_Fetch();
		if($row[0] && (ANON===TRUE || USER===TRUE)){
			$cobj -> enter_comment(USERNAME, $_POST['comment'], $plugintable, $sub_action, $pid, $_POST['subject']);
			$e107cache->clear("comment.{$plugintable}.{$sub_action}");
		}
	}
}

// ##### REDIRECTION MANAGEMENT -------------------------------------------------------
$resultmenu = FALSE;
$searchfieldname = "searchfield_{$type_id}";
$searchfieldmenuname = "searchfieldmenu_{$type_id}";
if(isset($_POST['searchsubmit']) || isset($_POST[$searchfieldname]) || isset($_POST[$searchfieldmenuname])){		//if active keyword search
	if($_POST[$searchfieldname] != "" && $_POST[$searchfieldname] != CONTENT_LAN_18){
		$resultmenu = TRUE;
		$searchkeyword = $_POST[$searchfieldname];
	}
	if($_POST[$searchfieldmenuname] != "" && $_POST[$searchfieldmenuname] != CONTENT_LAN_18){
		$resultmenu = TRUE;
		$searchkeyword = $_POST[$searchfieldmenuname];
	}
}

if(!isset($type)){
			show_content();												//show content type list

}elseif($type == "type" && is_numeric($type_id)){
	if(!isset($action) || substr($action,0,5) == "order"){
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_recent();										//show content recent list	

	}elseif($action == "cat"){
		if(!isset($sub_action) || !is_numeric($sub_action)){			//show all categories
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_cat_all();

		}elseif(is_numeric($sub_action)){								// show contents from category
			if(!isset($id)){
				if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
				if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
				show_content_cat();

			}elseif($id == "comment"){
				if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
				if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
				show_content_cat("comment");

			}else{
				header("location:".e_SELF."?".$type.".".$type_id.".cat.".$sub_action);
				exit;
			}				

		}else{
			header("location:".e_SELF."?".$type.".".$type_id.".cat");
			exit;
		}
	}elseif($action == "author"){
		if(!isset($sub_action) || !is_numeric($sub_action)){			//show all authors
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_author_all();

		}elseif(is_numeric($sub_action)){								// show contents from author
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_author();

		}else{
			header("location:".e_SELF."?".$type.".".$type_id.".author");
			exit;
		}
	}elseif($action == "content"){										//show content item
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_item();

	}elseif($action == "top"){											//show content top rated items
			if($content_pref["content_searchmenu_{$type_id}"]){ show_content_search_menu(); }
			if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
			show_content_top();
	}else{
		header("location:".e_SELF."?".$type.".".$type_id);
		exit;
	}
}
// ##### ------------------------------------------------------------------------------

// ##### CONTENT SEARCH MENU ----------------------------
function show_content_search_menu(){
				global $type, $type_id, $ns, $rs, $action, $sub_action, $id, $id2;
				global $plugintable, $gen, $aa, $content_pref;

				$CONTENT_SEARCH_TABLE = "";
				if(!$CONTENT_SEARCH_TABLE){
					if(!$content_pref["content_theme_{$type_id}"]){
						require_once(e_PLUGIN."content/templates/default/content_search_template.php");
					}else{
						if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_search_template.php")){
							require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_search_template.php");
						}else{
							require_once(e_PLUGIN."content/templates/default/content_search_template.php");
						}
					}
				}

				$CONTENT_SEARCH_TABLE_SELECT = "
				".$rs -> form_open("post", e_SELF.(e_QUERY ? "?".e_QUERY : ""), "contentdirect", "", "enctype='multipart/form-data'")."
				<select id='ordervalue' name='ordervalue' class='tbox' onchange=\"document.location=this.options[this.selectedIndex].value;\">
				".$rs -> form_option(CONTENT_LAN_56, 0, "none")."
				".$rs -> form_option(CONTENT_LAN_6, 0, "".e_SELF."?".$type.".".$type_id.".cat")."
				".$rs -> form_option(CONTENT_LAN_7, 0, "".e_SELF."?".$type.".".$type_id.".author")."
				".$rs -> form_option(CONTENT_LAN_8, 0, "".e_SELF."?".$type.".".$type_id.".top")."
				".$rs -> form_option(CONTENT_LAN_61, 0, "".e_SELF."?".$type.".".$type_id)."
				".$rs -> form_select_close()."
				".$rs -> form_close();

				if($action == "content"){
					$CONTENT_SEARCH_TABLE_ORDER = "";
				}elseif($action == "author" && !$sub_action){
					$CONTENT_SEARCH_TABLE_ORDER = "";
				}elseif($action == "cat" && !$sub_action){
					$CONTENT_SEARCH_TABLE_ORDER = "";
				}elseif($action == "top"){
					$CONTENT_SEARCH_TABLE_ORDER = "";
				}else{

					$querystring = ($action && substr($action,0,5) != "order" ? ".".$action : "").($sub_action && substr($sub_action,0,5) != "order" ? ".".$sub_action : "").($id && substr($id,0,5) != "order" ? ".".$id : "").($id2 && substr($id2,0,5) != "order" ? ".".$id2 : "");

					$CONTENT_SEARCH_TABLE_ORDER = "
					".$rs -> form_open("post", e_PLUGIN."content/content.php?type.1", "contentsearchorder", "", "enctype='multipart/form-data'")."
					<select id='ordervalue' name='ordervalue' class='tbox' onchange=\"document.location=this.options[this.selectedIndex].value;\">
					".$rs -> form_option(CONTENT_LAN_9, 0, "none")."
					".$rs -> form_option(CONTENT_LAN_10, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderaheading" )."
					".$rs -> form_option(CONTENT_LAN_11, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderdheading" )."
					".$rs -> form_option(CONTENT_LAN_12, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderadate" )."
					".$rs -> form_option(CONTENT_LAN_13, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderddate" )."
					".$rs -> form_option(CONTENT_LAN_14, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderarefer" )."
					".$rs -> form_option(CONTENT_LAN_15, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderdrefer" )."
					".$rs -> form_option(CONTENT_LAN_16, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderaparent" )."
					".$rs -> form_option(CONTENT_LAN_17, 0, e_PLUGIN."content/content.php?type.1".$querystring.".orderdparent" )."
					".$rs -> form_select_close()."
					".$rs -> form_close();
				}
				$searchfieldname = "searchfield_{$type_id}";
				$CONTENT_SEARCH_TABLE_KEYWORD = $rs -> form_open("post", e_SELF.(e_QUERY ? "?".e_QUERY : ""), "contentsearch_{$type_id}", "", "enctype='multipart/form-data'")."
				<input class='tbox' size='27' type='text' id='$searchfieldname' name='$searchfieldname' value='".(isset($_POST[$searchfieldname]) ? $_POST[$searchfieldname] : CONTENT_LAN_18)."' maxlength='100' onfocus=\"document.forms['contentsearch_{$type_id}'].$searchfieldname.value='';\" />
				<input class='button' type='submit' name='searchsubmit' value='".CONTENT_LAN_19."' />
				".$rs -> form_close();

				$text = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_SEARCH_TABLE);
				$caption = "content search";
				$ns -> tablerender($caption, $text);
				return TRUE;
}

function show_content_search_result($searchkeyword){
				global $type, $type_id, $ns, $rs, $action, $sub_action, $id, $id2;
				global $plugintable, $gen, $aa, $content_pref, $datequery;

				$querysr = " (content_heading REGEXP '".$searchkeyword."' OR content_subheading REGEXP '".$searchkeyword."' OR content_summary REGEXP '".$searchkeyword."' OR content_text REGEXP '".$searchkeyword."' ) ";

				$sqlsr = "";
				if(!is_object($sqlsr)){ $sqlsr = new db; }
				if(!$sqlsr -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon, content_datestamp", "LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$querysr." ".$datequery." ORDER BY content_heading")){
					$textsr = "<div style='text-align:center;'>no content items found with these keywords.</div>";
				}else{
					if(!$CONTENT_SEARCHRESULT_TABLE){
						if(!$content_pref["content_theme_{$type_id}"]){
							require_once(e_PLUGIN."content/templates/default/content_searchresult_template.php");
						}else{
							if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_searchresult_template.php")){
								require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_searchresult_template.php");
							}else{
								require_once(e_PLUGIN."content/templates/default/content_searchresult_template.php");
							}
						}
					}
					$content_searchresult_table_string = "";
					$gen = new convert;
					while($row = $sqlsr -> db_Fetch()){
					extract($row);
						$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($content_datestamp, "short"));
						$CONTENT_SEARCHRESULT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
						$CONTENT_SEARCHRESULT_TABLE_HEADING = ($content_heading ? "<a href='".e_SELF."?".$type.".".$type_id.".content.".$content_id."'>".$content_heading."</a>" : "");
						$CONTENT_SEARCHRESULT_TABLE_SUBHEADING = ($content_subheading ? $content_subheading : "");
						$CONTENT_SEARCHRESULT_TABLE_TEXT = parsesearch($content_text, $searchkeyword);
						$CONTENT_SEARCHRESULT_TABLE_ICON = $aa -> getIcon("item", $content_icon, $content_icon_path, $type.".".$type_id.".content.".$content_id, "50", $content_pref["content_blank_icon_{$type_id}"]."<br />");
						$authordetails = $aa -> getAuthor($content_author);
						$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS = $authordetails[1];
						if(USER){
							if(is_numeric($authordetails[3])){
								$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
							}else{
								$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
							}
						}else{
							$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
						}
						$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";

						$content_searchresult_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_SEARCHRESULT_TABLE);
					}
					$content_searchresult_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_SEARCHRESULT_TABLE_START);
					$content_searchresult_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_SEARCHRESULT_TABLE_END);
					$textsr = $content_searchresult_table_start.$content_searchresult_table_string.$content_searchresult_table_end;
				}
				$caption = CONTENT_LAN_20;
				$ns -> tablerender($caption, $textsr);
				require_once(FOOTERF);
				exit;
}

function parsesearch($text, $match){
				$text = strip_tags($text);
				$temp = stristr($text,$match);
				$pos = strlen($text)-strlen($temp);
				if($pos < 140){
						$text = "...".substr($text, 0, 140)."...";
				}else{
						$text = "...".substr($text, ($pos-140), 280)."...";
				}
				$text = eregi_replace($match, "<span class='searchhighlight' style='color:red;'>$match</span>", $text);
				return($text);
}

// ##### CONTENT TYPE LIST ------------------------------
function show_content(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $content_cat_icon_path_large, $content_cat_icon_path_small, $datequery;

				if(!is_object($sql)){ $sql = new db; }
				$CONTENT_TYPE_TABLE = "";
				$cachestr = "$plugintable.typelist";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();
					if(!$sql -> db_Select($plugintable, "*", "content_parent = '0' ".$datequery." ORDER BY content_heading")){
						$text .= "<div style='text-align:center;'>".CONTENT_LAN_21."</div>";
					}else{
						if(!$CONTENT_TYPE_TABLE){
							require_once(e_PLUGIN."content/templates/default/content_type_template.php");
						}
						$sql2 = "";
						$content_type_table_string = "";
						while($row = $sql -> db_Fetch()){
						extract($row);
							if(!is_object($sql2)){ $sql2 = new db; }

							$content_pref = unserialize(stripslashes($row['content_pref']));
							$content_pref["content_cat_icon_path_large_{$content_id}"] = ($content_pref["content_cat_icon_path_large_{$content_id}"] ? $content_pref["content_cat_icon_path_large_{$content_id}"] : "{e_PLUGIN}content/images/cat/48/" );
							$content_pref["content_cat_icon_path_small_{$content_id}"] = ($content_pref["content_cat_icon_path_small_{$content_id}"] ? $content_pref["content_cat_icon_path_small_{$content_id}"] : "{e_PLUGIN}content/images/cat/16/" );
							$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$content_id}"]);
							$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$content_id}"]);
							$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$content_id}"]);

							// check userclasses for contents, and do not use those content_ids in the query
							// if no valid content is found within a main parent, then don't show a link, else show a link
							$query = " LEFT(content_parent,".(strlen($content_id)).") = '".$content_id."' AND content_refer != 'sa' ";
							$UnValidArticleIds = $aa -> checkUnValidContent($query);
							$contenttotal = $sql2 -> db_Count($plugintable, "(*)", "WHERE ".$query." ".$UnValidArticleIds." ".$datequery." ");
							$CONTENT_TYPE_TABLE_TOTAL = ($contenttotal ? $contenttotal : "");
							$CONTENT_TYPE_TABLE_TOTAL_LAN = ($contenttotal ? ($contenttotal == 1 ? CONTENT_LAN_53 : CONTENT_LAN_54) : "");
							$CONTENT_TYPE_TABLE_HEADING = ($contenttotal != "0" ? "<a href='".e_SELF."?type.".$content_id."'>".$content_heading."</a>" : $content_heading );
							$CONTENT_TYPE_TABLE_SUBHEADING = ($content_subheading ? $content_subheading : "");
							if($contenttotal != "0"){
								$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $content_icon, $content_cat_icon_path_large, "type.".$content_id, "", $content_pref["content_blank_caticon_{$content_id}"]);
							}else{
								$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $content_icon, $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$content_id}"]);
							}
							
							if(check_class($content_class)){
								$content_type_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE);
							}									
						}

						$SUBMIT_LINE = FALSE;
						$count = "0";
						if(!is_object($sql3)){ $sql3 = new db; }
						if($sql3 -> db_Select($plugintable, "content_id, content_pref as prefvalue", "content_parent = '0' ".$datequery." ORDER BY content_parent")){
							while($row = $sql3 -> db_Fetch()){
							extract($row);
								$content_pref = unserialize(stripslashes($row['prefvalue']));
								if($content_pref["content_submit_{$content_id}"] && check_class($content_pref["content_submit_class_{$content_id}"])){
									$count = $count + 1;
								}
							}
							if($count > "0"){
								$content_type_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_LINE);
								$CONTENT_TYPE_TABLE_SUBMIT_ICON = "<a href='".e_PLUGIN."content/content_submit.php'>".CONTENT_ICON_SUBMIT."</a>";
								$CONTENT_TYPE_TABLE_SUBMIT_HEADING = "<a href='".e_PLUGIN."content/content_submit.php'>".CONTENT_LAN_65."</a>";
								$CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING = CONTENT_LAN_66;
								$content_type_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_SUBMIT);
								$SUBMIT_LINE = TRUE;
							}
						}
						if($sql3 -> db_Select($plugintable, "content_pref as managerclass", "LEFT(content_parent,2) = '0.' ".$datequery." ORDER BY content_parent")){
							while($row = $sql3 -> db_Fetch()){
							extract($row);
								if($managerclass != ""){
									$managerclassarray[] = explode(".", $managerclass);
								}
							}
							if(in_array(USERID, $managerclassarray) || getperms("0")){
								$MANAGER_LINE = TRUE;
								if($SUBMIT_LINE != TRUE && $MANAGER_LINE == TRUE){
									$content_type_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_LINE);
								}
								$CONTENT_TYPE_TABLE_MANAGER_ICON = "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_ICON_CONTENTMANAGER."</a>";
								$CONTENT_TYPE_TABLE_MANAGER_HEADING = "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_LAN_67."</a>";
								$CONTENT_TYPE_TABLE_MANAGER_SUBHEADING = CONTENT_LAN_68;
								$content_type_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_MANAGER);
								
							}
						}

						$content_type_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_START);
						$content_type_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TYPE_TABLE_END);
						$text = $content_type_table_start.$content_type_table_string.$content_type_table_end;
					}
					$caption = CONTENT_LAN_22;
					$ns -> tablerender($caption, $text);

					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}

// ##### RECENT LIST ------------------------------------
function show_content_recent(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $nextprevquery, $from, $number;
				global $CONTENT_RECENT_TABLE, $datequery;

				$cachestr = "$plugintable.recent";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();
					$order = $aa -> getOrder();

					if(!$CONTENT_RECENT_TABLE){
						if(!$content_pref["content_theme_{$type_id}"]){
							require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
						}else{
							if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php")){
								require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php");
							}else{
								require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
							}
						}
					}

					if(!is_object($sql)){ $sql = new db; }
					// check userclasses for contents, and do not use those content_ids in the query
					//$query = " LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' ";
					//$UnValidArticleIds = $aa -> checkUnValidContent($query);
					//$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE ".$query." ".$UnValidArticleIds." ".$datequery." ");

					$UnValidArticleIds2 = $aa -> checkMainCat($type_id);
					$UnValidArticleIds2 = ($UnValidArticleIds2 == "" ? "" : substr($UnValidArticleIds2, 0, -3) );

					$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ");

					if($from > $contenttotal-1){
						header("location:".e_SELF);
						exit;
					}

					if($resultitem = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ".$order." ".$nextprevquery )){

						$content_recent_table_string = "";
						while($row = $sql -> db_Fetch()){
						extract($row);
							$content_recent_table_string .= parse_content_recent_table($row);
						}
					}
					$content_recent_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_START);
					$content_recent_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_END);
					$text = $content_recent_table_start.$content_recent_table_string.$content_recent_table_end;


//echo "content_refer !='sa' AND ".$query." ".$UnValidArticleIds."";
//echo "contenttotal: ".$contenttotal."<br />";

//new style
//content_refer !='sa' AND LEFT(content_parent,1) = '2' AND content_id != '38' AND content_id != '50' AND content_id != '52' AND content_id != '53' AND content_id != '54' AND content_id != '61' AND content_id != '62' AND content_id != '65' AND content_id != '66' AND content_id != '67' AND content_id != '70' AND content_id != '71' AND content_id != '52' AND content_id != '50'
//contenttotal: 45
//Render time: 0.3950 second(s); 0.1408 of that for queries. DB queries: 63. 

//old style
//content_refer !='sa' AND LEFT(content_parent,1) = '2' AND content_id != '38' AND content_id != '50' AND content_id != '52' AND content_id != '53' AND content_id != '54' AND content_id != '61' AND content_id != '62' AND content_id != '65' AND content_id != '66' AND content_id != '67' AND content_id != '70' AND content_id != '71'
//contenttotal: 45
//Render time: 0.6956 second(s); 0.3513 of that for queries. DB queries: 236. 


					if($content_pref["content_breadcrumb_{$type_id}"]){
						$breadcrumb = $aa -> getBreadCrumb($type_id);
						$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
						if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
								echo $breadcrumbstring;					
						}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
								$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
						}else{
								$text = $breadcrumbstring.$text;
						}
					}


					$caption = CONTENT_LAN_23;
					$ns -> tablerender($caption, $text);
					if($content_pref["content_nextprev_{$type_id}"]){
						require_once(e_HANDLER."np_class.php");
						$np_querystring = ($type ? $type : "").($type_id ? ".".$type_id : "").($action ? ".".$action : "").($sub_action ? ".".$sub_action : "").($id ? ".".$id : "");
						$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
					}

					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}
// ##### --------------------------------------------------

// ##### CATEGORY LIST ------------------------------------
function show_content_cat_all(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $nextprevquery, $from, $number;
				global $CONTENT_CAT_TABLE, $datequery;
				unset($text);

				$parentarray = $aa -> getParent("", "", $type_id, "1");
				if(empty($parentarray)){
					header("location:".e_SELF."?".$type.".".$type_id); exit;
				}else{
					$cachestr = "$plugintable.cat";
					if($cache = $e107cache->retrieve($cachestr)){
						echo $cache;
					}else{
						ob_start();

						if(!$CONTENT_CAT_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
								}
							}
						}
						$counter = "0";
						$content_cat_table_string = "";
						for($a=0;$a<count($parentarray);$a++){
							$content_cat_table_string .= parse_content_cat_table($parentarray[$a]);
							$counter = $counter+1;
						}
													
						$content_cat_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_TABLE_START);
						$content_cat_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_TABLE_END);
						$text = $content_cat_table_start.$content_cat_table_string.$content_cat_table_end;

						if($content_pref["content_breadcrumb_{$type_id}"]){
							$breadcrumb = $aa -> getBreadCrumb($type_id);
							$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
									$text = $breadcrumbstring.$text;
							}
						}
						$caption = CONTENT_LAN_25;
						$ns -> tablerender($caption, $text);

						if($pref['cachestatus']){
							$cache = $tp -> toDB(ob_get_contents());
							$e107cache->set($cachestr, $cache);
						}
						ob_end_flush(); // dump collected data 	
					}		
				}
}

function show_content_cat($mode=""){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj, $datequery;
				global $type, $type_id, $action, $sub_action, $id, $id2, $nextprevquery, $from, $number;
				global $CONTENT_RECENT_TABLE, $CONTENT_CAT_LIST_TABLE, $CONTENT_CAT_LISTSUB_TABLE_START, $CONTENT_CAT_LISTSUB_TABLE, $CONTENT_CAT_LISTSUB_TABLE_END;

				if($mode == "comment"){
					$cachestr = "$plugintable.cat.$sub_action.comment";
				}else{
					$cachestr = "$plugintable.cat.$sub_action";
				}
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					$breadcrumb_parent = $sub_action;
					if($content_pref["content_breadcrumb_{$type_id}"]){
						$breadcrumb = $aa -> getBreadCrumb($breadcrumb_parent);
						$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
					}

					// parent article
					$CONTENT_CAT_LIST_TABLE = "";
					if($content_pref["content_cat_showparent_{$type_id}"]){
						if(!$CONTENT_CAT_LIST_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
								}
							}
						}
						$parentcontent = $aa -> getContent($sub_action);
						if(empty($parentcontent)){
							header("location:".e_SELF."?".$type.".".$type_id.".cat"); exit;
						}else{
							$textparent = parse_content_cat_list_table($parentcontent);
							$captionparent = CONTENT_LAN_26;
						}
					}

					if(!$mode || $mode == ""){

						// parent subcategories
						if($type_id == $sub_action){
							$subcats = $aa -> getParent($type_id, "", "", "1");
						}else{
							$subcats = $aa -> getParent($type_id.".".$sub_action, "", "", "1");
						}
						if(!empty($subcats)){
							if(!$CONTENT_CAT_LISTSUB_TABLE){
								if(!$content_pref["content_theme_{$type_id}"]){
									require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
								}else{
									if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php")){
										require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_cat_template.php");
									}else{
										require_once(e_PLUGIN."content/templates/default/content_cat_template.php");
									}
								}
							}

							$content_cat_listsub_table_string = "";
							for($i=0;$i<count($subcats);$i++){

								$CONTENT_CAT_LISTSUB_TABLE_AMOUNT = $aa -> countItemsInCat($subcats[$i][0], $subcats[$i][9]);
								$CONTENT_CAT_LISTSUB_TABLE_ICON = $aa -> getIcon("catsmall", $subcats[$i][6], "", "", "", $content_pref["content_blank_caticon_{$type_id}"]);

								$CONTENT_CAT_LISTSUB_TABLE_HEADING = "<a href='".e_SELF."?".$type.".".$type_id.".cat.".$subcats[$i][0]."'>".$subcats[$i][1]."</a>";
								$CONTENT_CAT_LISTSUB_TABLE_SUBHEADING = ($subcats[$i][2] ? "[".$subcats[$i][2]."]" : "");
								$CONTENT_CAT_LISTSUB_TABLE_PADDING = "";
								$levellistsub = substr_count($subcats[$i][9], ".");
								$CONTENT_CAT_LISTSUB_TABLE_WIDTH = "width:".(1+($levellistsub*20))."px;";

								$content_cat_listsub_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_LISTSUB_TABLE);
							}
							$content_cat_listsub_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_LISTSUB_TABLE_START);
							$content_cat_listsub_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_LISTSUB_TABLE_END);
							$textsubparent = $content_cat_listsub_table_start.$content_cat_listsub_table_string.$content_cat_listsub_table_end;
							$captionsubparent = CONTENT_LAN_28;
						}

						//list all contents within this category
						unset($text);				
						if(!$CONTENT_RECENT_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
								}
							}
						}
						// check userclasses for contents, and do not use those content_ids in the query
						$leftlength = strlen($type_id)+2;
						if($content_pref["content_cat_listtype_{$type_id}"]){
							$query = " LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND (content_parent REGEXP('.".$sub_action."')) ";
						}else{
							$query = " LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND (content_parent REGEXP('.".$sub_action."')) AND NOT (content_parent REGEXP('.".$sub_action.".') ) ";
						}
						//$UnValidArticleIds = $aa -> checkUnValidContent($query);
						//$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE ".$query." ".$UnValidArticleIds." ");
						$order = $aa -> getOrder();

						$UnValidArticleIds2 = $aa -> checkMainCat($type_id);
						$UnValidArticleIds2 = ($UnValidArticleIds2 == "" ? "" : substr($UnValidArticleIds2, 0, -3) );

						$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." AND ".$query." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ");

						if(!is_object($sql)){ $sql = new db; }
						if($resultitem = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$query." AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ".$order." ".$nextprevquery )){

						//if(!is_object($sql)){ $sql = new db; }
						//if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class", " content_refer != 'sa' AND ".$query." ".$UnValidArticleIds." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ".$order." ".$nextprevquery)){
							$content_recent_table_string = "";
							while($row = $sql -> db_Fetch()){
							extract($row);
								$content_recent_table_string .= parse_content_recent_table($row);
							}
							$content_recent_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_START);
							$content_recent_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_END);
							$textchild = $content_recent_table_start.$content_recent_table_string.$content_recent_table_end;
							$captionchild = "contents";
						}
					

						if($content_pref["content_nextprev_{$type_id}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = ($type ? $type : "").($type_id ? ".".$type_id : "").($action ? ".".$action : "").($sub_action ? ".".$sub_action : "").($id ? ".".$id : "").($id2 ? ".".$id2 : "");
						}

						if($content_pref["content_breadcrumb_{$type_id}"]){
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
								if(isset($textparent)){ 
									$textparent = $breadcrumbstring.$textparent;
								}else{
									$textchild = $breadcrumbstring.$textparent;
								}
							}
						}

						if($content_pref["content_cat_menuorder_{$type_id}"] == "1"){
							if($content_pref["content_cat_rendertype_{$type_id}"] == "1"){
								if(isset($textparent)){ $ns -> tablerender($captionparent, $textparent); }
								if(isset($textsubparent)){ $ns -> tablerender($captionsubparent, $textsubparent); }
								if(isset($textchild)){ $ns -> tablerender($captionchild, $textchild); }
							}else{
								$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? "<br /><br />".$textsubparent : "")."<br /><br />".$textchild);
							}
							if($content_pref["content_nextprev_{$type_id}"]){
									$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
							}
						}else{
							if($content_pref["content_cat_rendertype_{$type_id}"] == "1"){
								if(isset($textchild)){ $ns -> tablerender($captionchild, $textchild); }
								if($content_pref["content_nextprev_{$type_id}"]){
										$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
								}
								if(isset($textparent)){ $ns -> tablerender($captionparent, $textparent); }
								if(isset($textsubparent)){ $ns -> tablerender($captionsubparent, $textsubparent); }
							}else{
								if(isset($textchild)){ $ns -> tablerender($captionchild, $textchild); }
								if($content_pref["content_nextprev_{$type_id}"]){
										$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
								}
								$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? "<br /><br />".$textsubparent : ""));
							}
						}

				}elseif($mode == "comment"){

						if($content_pref["content_breadcrumb_{$type_id}"]){
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
								if(isset($textparent)){ 
									$textparent = $breadcrumbstring.$textparent;
								}else{
									$textchild = $breadcrumbstring.$textparent;
								}
							}
						}

						if(isset($textparent)){ $ns -> tablerender($captionparent, $textparent); }

						if($parentcontent['content_comment']){
							if($cache = $e107cache->retrieve("comment.$plugintable.$sub_action")){
								echo $cache;
							}else{
								ob_start();
								unset($text);
								if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$sub_action."' AND comment_type='".$plugintable."' AND comment_pid='0' ORDER BY comment_datestamp")){
									$width = 0;
									while($row = $sql -> db_Fetch()){
										if($pref['nested_comments']){
											$text .= $cobj -> render_comment($row, $plugintable , "comment", $sub_action, $width, $parentcontent['content_heading']);
											$ns -> tablerender(CONTENT_LAN_35, $text);
										}else{
											$text .= $cobj -> render_comment($row, $plugintable , "comment", $sub_action, $width, $parentcontent['content_heading']);
										}
									}
									if(!$pref['nested_comments']){$ns -> tablerender(CONTENT_LAN_35, $text); }
									if($pref['cachestatus']){
										$cache = $tp -> toDB(ob_get_contents());
										$e107cache->set("comment.$plugintable.$sub_action", $cache);
									}
								}
								ob_end_flush(); /* dump collected data */		
							}
							if(ADMIN && getperms("B")){
								echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$plugintable.$sub_action'>".CONTENT_LAN_36."</a></div><br />";
							}
							$cobj -> form_comment("comment", $plugintable, $sub_action, $parentcontent['content_heading']);
						}
					}

					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}
// ##### --------------------------------------------------

// ##### AUTHOR LIST --------------------------------------
function show_content_author_all(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $datequery;

				$cachestr = "$plugintable.author";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();
					if(!is_object($sql)){ $sql = new db; }
					// check userclasses for contents, and do not use those content_ids in the query
					//$query = " LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' ";
					//$UnValidArticleIds = $aa -> checkUnValidContent($query);

					$UnValidArticleIds2 = $aa -> checkMainCat($type_id);
					$UnValidArticleIds2 = ($UnValidArticleIds2 == "" ? "" : substr($UnValidArticleIds2, 0, -3) );
					$order = $aa -> getOrder();

					if(!is_object($sql)){ $sql = new db; }
					if(!$result = $sql -> db_Select($plugintable, "DISTINCT(content_author)", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ORDER BY content_author" )){
						$text = "<div style='text-align:center;'>".CONTENT_LAN_52."</div>";
					}else{
						while($row = $sql -> db_Fetch()){
						extract($row);
							$authordetails[] = $aa -> getAuthor($content_author);
						}

						function cmp($a, $b){ return strcasecmp ($a[1], $b[1]); }
						usort($authordetails, "cmp");
						$CONTENT_AUTHOR_TABLE = "";
						if(!$CONTENT_AUTHOR_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_author_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_author_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_author_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_author_template.php");
								}
							}
						}
						$sql2 = "";
						$content_author_table_string = "";
						for($i=0;$i<count($authordetails);$i++){
							if(!is_object($sql2)){ $sql2 = new db; }
							$gen = new convert;
							$totalcontent = $sql2 -> db_Select($plugintable, "content_id, content_heading, content_datestamp", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") AND content_author = '".$authordetails[$i][3]."' ORDER BY content_datestamp DESC");
							list($content_id, $content_heading, $content_datestamp) = $sql2 -> db_Fetch();

							$name = ($authordetails[$i][1] == "" ? "... ".CONTENT_LAN_29." ..." : $authordetails[$i][1]);
							$authorlink = "<a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."'>".$name."</a>";

							$CONTENT_AUTHOR_TABLE_NAME = $authorlink;
							$CONTENT_AUTHOR_TABLE_ICON = "<a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."'>".CONTENT_ICON_AUTHORLIST."</a>";
							$CONTENT_AUTHOR_TABLE_TOTAL = $totalcontent." ".($totalcontent==1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
							$CONTENT_AUTHOR_TABLE_HEADING = "<a href='".e_SELF."?".$type.".".$type_id.".content.".$content_id."'>".$content_heading."</a>";
							$CONTENT_AUTHOR_TABLE_DATE = ereg_replace(" -.*", "", $gen -> convert_date($content_datestamp, "short"));
							$content_author_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_AUTHOR_TABLE);
						}
						$content_author_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_AUTHOR_TABLE_START);
						$content_author_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_AUTHOR_TABLE_END);
						$text = $content_author_table_start.$content_author_table_string.$content_author_table_end;

						if($content_pref["content_breadcrumb_{$type_id}"]){
							$breadcrumb = $aa -> getBreadCrumb($type_id);
							$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
									$text = $breadcrumbstring.$text;
							}
						}
					}
					$caption = CONTENT_LAN_32;
					$ns -> tablerender($caption, $text);
					
					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}

function show_content_author(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $nextprevquery, $from, $number;
				global $CONTENT_RECENT_TABLE, $datequery;

				$cachestr = "$plugintable.author.$sub_action";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					if(!is_object($sqla)){ $sqla = new db; }

					$UnValidArticleIds2 = $aa -> checkMainCat($type_id);
					$UnValidArticleIds2 = ($UnValidArticleIds2 == "" ? "" : substr($UnValidArticleIds2, 0, -3) );
					if(!is_object($sql)){ $sql = new db; }
					if(!$author = $sqla -> db_Select($plugintable, "content_author", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND ".$UnValidArticleIds2." ".$datequery." AND content_id = '".$sub_action."' AND content_class IN (".USERCLASS_LIST.") ")){
						header("location:".e_SELF."?".$type.".".$type_id.".author");
						exit;
					}else{
						list($content_author) = $sqla -> db_Fetch();
						$authordetails = $aa -> getAuthor($content_author);
						
						if(is_numeric($content_author)){
							$query = " content_author = '".$authordetails[3]."' || content_author = '".$authordetails[0]."' || content_author REGEXP '^".$authordetails[1]."^' ";
						}else{
							$query = " content_author = '".$authordetails[3]."' || content_author REGEXP '^".$authordetails[1]."^' ";
						}
						
						$CONTENT_RECENT_TABLE = "";
						if(!$CONTENT_RECENT_TABLE){
							if(!$content_pref["content_theme_{$type_id}"]){
								require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
							}else{
								if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php")){
									require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_recent_template.php");
								}else{
									require_once(e_PLUGIN."content/templates/default/content_recent_template.php");
								}
							}
						}

						$UnValidArticleIds2 = $aa -> checkMainCat($type_id);
						$UnValidArticleIds2 = ($UnValidArticleIds2 == "" ? "" : substr($UnValidArticleIds2, 0, -3) );
						$order = $aa -> getOrder();
						$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND (".$query.") AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ");

						if(!is_object($sql)){ $sql = new db; }
						//echo "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND (".$query.") AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.")";

						if($result = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' AND (".$query.") AND ".$UnValidArticleIds2." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ".$order." ".$nextprevquery )){
							
							$content_recent_table_string = "";
							while($row = $sql -> db_Fetch()){
							extract($row);
								$content_recent_table_string .= parse_content_recent_table($row);
							}
							$content_recent_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_START);
							$content_recent_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_END);
							$text = $content_recent_table_start.$content_recent_table_string.$content_recent_table_end;
						}

						if($content_pref["content_breadcrumb_{$type_id}"]){
							$breadcrumb = $aa -> getBreadCrumb($type_id);
							$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
									$text = $breadcrumbstring.$text;
							}
						}
						$caption = CONTENT_LAN_32." : ".$authordetails[1];
						$ns -> tablerender($caption, $text);

						if($content_pref["content_nextprev_{$type_id}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = ($type ? $type : "").($type_id ? ".".$type_id : "").($action ? ".".$action : "").($sub_action ? ".".$sub_action : "").($id ? ".".$id : "");
							$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
						}
					}
					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}
// ##### --------------------------------------------------

// ##### CONTENT ------------------------------------------
function show_content_item(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $datequery;

				if(!is_numeric($sub_action)){ header("location:".e_SELF."?".$type.".".$type_id); exit; }

				$cachestr = "$plugintable.content.$sub_action";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					$content = $aa -> getContent($sub_action);
					$checkparent = $aa -> checkUnValidContent("content_id='".$sub_action."'");

					if($content == FALSE || $checkparent != ""){
						header("location:".e_SELF."?".$type.".".$type_id); exit;
					}else{
						$text = parse_content_content_table($content);
						$sql = new db;
						$sql -> db_Select($plugintable, "content_parent", "content_id='".$sub_action."' ".$datequery." ");
						list($breadcrumb_parent) = $sql -> db_Fetch();
						if($content_pref["content_breadcrumb_{$type_id}"]){
							$breadcrumb = $aa -> getBreadCrumb($breadcrumb_parent);
							$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
									$text = $breadcrumbstring.$text;
							}
						}
						$caption = CONTENT_LAN_34;
						$ns -> tablerender($caption, $text);

						$totalpages = substr_count($content['content_text'], "[newpage");
						$comflag = ($totalpages == $id ? TRUE : FALSE);

						if($content['content_comment'] && $comflag){
							if($cache = $e107cache->retrieve("comment.$plugintable.$sub_action")){
								echo $cache;
							}else{
								ob_start();
								unset($text);
								if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$sub_action."' AND comment_type='".$plugintable."' AND comment_pid='0' ORDER BY comment_datestamp")){
									$width = 0;
									while($row = $sql -> db_Fetch()){
										if($pref['nested_comments']){
											$text .= $cobj -> render_comment($row, $plugintable , "comment", $sub_action, $width, $content['content_heading']);
											$ns -> tablerender(CONTENT_LAN_35, $text);
										}else{
											$text .= $cobj -> render_comment($row, $plugintable , "comment", $sub_action, $width, $content['content_heading']);
										}
									}
									if(!$pref['nested_comments']){$ns -> tablerender(CONTENT_LAN_35, $text); }
									if($pref['cachestatus']){
										$cache = $tp -> toDB(ob_get_contents());
										$e107cache->set("comment.$plugintable.$sub_action", $cache);
									}
								}
								ob_end_flush(); /* dump collected data */		
							}
							if(ADMIN && getperms("B")){
								echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$plugintable.$sub_action'>".CONTENT_LAN_36."</a></div><br />";
							}
							$cobj -> form_comment("comment", $plugintable, $sub_action, $content['content_heading']);
						}
					}
					if($pref['cachestatus']){
						$cache = $tp -> toDB(ob_get_contents());
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}
// ##### --------------------------------------------------

// ##### TOP RATED LIST -----------------------------------
function show_content_top(){
				global $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $type, $type_id, $action, $sub_action, $id, $id2, $nextprevquery, $from, $number, $datequery;

				if(!is_object($sql)){ $sql = new db; }
				$sql2 = ""; if(!is_object($sql2)){ $sql2 = new db; }

				if(!$sql -> db_Select("rate", "*", "rate_table='".$plugintable."' ORDER BY rate_itemid " )){
					$text = "<div style='text-align:center;'>".CONTENT_LAN_37."</div>";
					$caption = CONTENT_LAN_38;
					$ns -> tablerender($caption, $text);
					require_once(FOOTERF);
					exit;
				}else{
					$CONTENT_TOP_TABLE = "";
					if(!$CONTENT_TOP_TABLE){
						if(!$content_pref["content_theme_{$type_id}"]){
							require_once(e_PLUGIN."content/templates/default/content_top_template.php");
						}else{
							if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_top_template.php")){
								require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_top_template.php");
							}else{
								require_once(e_PLUGIN."content/templates/default/content_top_template.php");
							}
						}
					}
					while($row = $sql -> db_Fetch()){
					extract($row);
						$tmp = $rate_rating / $rate_votes;
						$tmp = explode(".", $tmp);
						$rating[1] = $tmp[0];				// $ratomg[1] = main result
						$rating[2] = (!empty($tmp[1]) ? substr($tmp[1],0,1) : "");	// $rating[2] = remainder
						$rate_avg = $rating[1].".".($rating[2] ? $rating[2] : "0");

						// check if article exists
						if($sql2 -> db_Select($plugintable, "content_id, content_class", "content_id='".$rate_itemid."' AND LEFT(content_parent,".(strlen($type_id)).") = '".$type_id."' ".$datequery." " )){
							list($content_id, $content_class) = $sql2 -> db_Fetch();
							$content = $aa -> getContent($content_id);
							$checkparent = $aa -> checkUnValidContent("content_id='".$content_id."'");
							if($content == FALSE || $checkparent != ""){
							}else{
								if(check_class($content_class)){
									$rate_array[] = array($rate_itemid, $rate_rating, $rate_votes, $rate_avg, $rating[1], $rating[2]);
								}
							}
						}
					}
					if(empty($rate_array)){
						$text = CONTENT_LAN_37;
					}else{
						usort($rate_array, create_function('$a,$b','return $a[3]==$b[3]?0:($a[3]>$b[3]?-1:1);')); 
						$contenttotal = count($rate_array);
						$content_top_table_string = "";

						for($i=$from;$i<$from+$number;$i++){

							if($sql2 -> db_Select($plugintable, "content_id, content_heading, content_author, content_icon", "content_id='".$rate_array[$i][0]."' ".$datequery." " )){
								while($row = $sql2 -> db_Fetch()){
								extract($row);

									$CONTENT_TOP_TABLE_ICON = $aa -> getIcon("item", $content_icon, $content_icon_path, $type.".".$type_id.".content.".$content_id, "50", $content_pref["content_blank_icon_{$type_id}"]);
									$CONTENT_TOP_TABLE_HEADING = "<a href='".e_PLUGIN."content/content.php?".$type.".".$type_id.".content.".$content_id."'>".$content_heading."</a>";

									$CONTENT_TOP_TABLE_RATING = "";
									for($c=1; $c<= $rate_array[$i][4]; $c++){
										$CONTENT_TOP_TABLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
									}
									if($rate_array[$i][4] < 10){
										for($c=9; $c>=$rate_array[$i][4]; $c--){
											$CONTENT_TOP_TABLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
										}
									}
									$CONTENT_TOP_TABLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
									$CONTENT_TOP_TABLE_RATING .= " ".$rate_array[$i][3];

									$authordetails = $aa -> getAuthor($content_author);
									$CONTENT_TOP_TABLE_AUTHOR = $authordetails[1]." ";
									
									if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
										$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
									}else{
										//$CONTENT_TOP_TABLE_AUTHOR .= " ".CONTENT_ICON_USER;
									}
									
									$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";

									$content_top_table_string .= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TOP_TABLE);
								}
							}
						}
						if($content_pref["content_breadcrumb_{$type_id}"]){
							$breadcrumb = $aa -> getBreadCrumb($type_id);
							$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "base");
							if($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "1"){
									echo $breadcrumbstring;					
							}elseif($content_pref["content_breadcrumb_rendertype_{$type_id}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $breadcrumbstring);
							}else{
									$text = $breadcrumbstring.$text;
							}
						}						
						$content_top_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TOP_TABLE_START);
						$content_top_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_TOP_TABLE_END);
						$text = $content_top_table_start.$content_top_table_string.$content_top_table_end;

						$caption = CONTENT_LAN_38;
						$ns -> tablerender($caption, $text);

						if($content_pref["content_nextprev_{$type_id}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = ($type ? $type : "").($type_id ? ".".$type_id : "").($action ? ".".$action : "").($sub_action ? ".".$sub_action : "").($id ? ".".$id : "");
							$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
						}
					}
				}
}
// ##### --------------------------------------------------



function parse_content_recent_table($row){
				global $rater, $ep, $tp, $aa, $gen, $content_icon_path, $content_image_path;
				global $type, $type_id, $action, $sub_action, $id, $content_pref, $plugintable, $datequery;
				global $CONTENT_RECENT_TABLE;
				extract($row);

				if($content_pref["content_list_parent_{$type_id}"]){
						$breadcrumb = $aa -> getBreadCrumb($content_parent);
						$CONTENT_RECENT_TABLE_PARENT = $aa -> printBreadCrumb($breadcrumb, "nobase");
				}
				if($content_pref["content_list_date_{$type_id}"]){
					$CCONTENT_RECENT_TABLE_DATE = "1";
						$gen = new convert;
						$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($content_datestamp, "long"));
						$CONTENT_RECENT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
				}
				$CONTENT_RECENT_TABLE_HEADING = ($content_heading ? "<a href='".e_SELF."?".$type.".".$type_id.".content.".$content_id."'>".$content_heading."</a>" : "");
				$CONTENT_RECENT_TABLE_ICON = $aa -> getIcon("item", $content_icon, $content_icon_path, $type.".".$type_id.".content.".$content_id, "100", $content_pref["content_blank_icon_{$type_id}"]);

				// ----------- NUMBER OF SUBHEADING CHARACTERS TO DISPLAY? ------------------------------
				if ($content_pref["content_list_subheading_{$type_id}"] && $content_subheading && $content_pref["content_list_subheading_char_{$type_id}"] && $content_pref["content_list_subheading_char_{$type_id}"] != "" && $content_pref["content_list_subheading_char_{$type_id}"] != "0"){
				
					if(strlen($content_subheading) > $content_pref["content_list_subheading_char_{$type_id}"]) {
						$content_subheading = substr($content_subheading, 0, $content_pref["content_list_subheading_char_{$type_id}"]).$content_pref["content_list_subheading_post_{$type_id}"];
					}
					$CONTENT_RECENT_TABLE_SUBHEADING = ($content_subheading != "" && $content_subheading != " " ? $content_subheading : "");
				}
				// -----------------------------------------------------------------------------------

				// ----------- NUMBER OF SUMMARY CHARACTERS TO DISPLAY? ------------------------------
				if ($content_pref["content_list_summary_{$type_id}"] && $content_summary && $content_pref["content_list_summary_char_{$type_id}"] && $content_pref["content_list_summary_char_{$type_id}"] != "" && $content_pref["content_list_summary_char_{$type_id}"] != "0"){

					if(strlen($content_summary) > $content_pref["content_list_summary_char_{$type_id}"]) {
						$content_summary = substr($content_summary, 0, $content_pref["content_list_summary_char_{$type_id}"]).$content_pref["content_list_summary_post_{$type_id}"];
					}
					$CONTENT_RECENT_TABLE_SUMMARY = ($content_summary != "" && $content_summary != " " ? $content_summary : "");
				}
				// -----------------------------------------------------------------------------------

				if($content_pref["content_list_authorname_{$type_id}"] || $content_pref["content_list_authoremail_{$type_id}"]){
					$authordetails = $aa -> getAuthor($content_author);
					if($content_pref["content_list_authorname_{$type_id}"]){
						if($content_pref["content_list_authoremail_{$type_id}"] && $authordetails[2]){
							if($authordetails[0] == "0"){
								if($content_pref["content_list_authoremail_nonmember_{$type_id}"]){
									$CONTENT_RECENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
								}else{
									$CONTENT_RECENT_TABLE_AUTHORDETAILS = $authordetails[1];
								}
							}else{
								$CONTENT_RECENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
							}
						}else{
							$CONTENT_RECENT_TABLE_AUTHORDETAILS = $authordetails[1];
						}
						if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
							$CONTENT_RECENT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
						}else{
							//$CONTENT_RECENT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
						}
					}
					$CONTENT_RECENT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}

				if(($content_pref["content_list_peicon_{$type_id}"] && $content_pe) || $content_pref["content_list_peicon_all_{$type_id}"]){
					$CONTENT_RECENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.$content_id}");
					$CONTENT_RECENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.$content_id}");
				}

				if($content_pref["content_log_{$type_id}"] && $content_pref["content_list_refer_{$type_id}"]){
					$refercounttmp = explode("^", $content_refer);
					$CONTENT_RECENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "0");
				}
		
				$CONTENT_RECENT_TABLE_RATING = "";
				if($content_pref["content_list_rating_all_{$type_id}"] || ($content_pref["content_list_rating_{$type_id}"] && $content_rate)){
					
					if($ratearray = $rater -> getrating($plugintable, $content_id)){
						for($c=1; $c<= $ratearray[1]; $c++){
							$CONTENT_RECENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
						}
						if($ratearray[1] < 10){
							for($c=9; $c>=$ratearray[1]; $c--){
								$CONTENT_RECENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
							}
						}
						$CONTENT_RECENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
						if($ratearray[2] == ""){ $ratearray[2] = 0; }
						$CONTENT_RECENT_TABLE_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
						$CONTENT_RECENT_TABLE_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
					}else{
						$CONTENT_RECENT_TABLE_RATING .= LAN_65;
					}
					if(!$rater -> checkrated($plugintable, $content_id) && USER){
						$CONTENT_RECENT_TABLE_RATING .= " - ".$rater -> rateselect(LAN_40, $plugintable, $content_id);
					}else if(USER){
						$CONTENT_RECENT_TABLE_RATING .= " - ".LAN_41;
					}
				}
				return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE));
}


function parse_content_cat_table($row){
				global $CONTENT_CAT_TABLE, $content_cat_icon_path_large, $content_cat_icon_path_small, $gen, $aa, $tp, $rater, $ep, $plugintable;
				global $type, $type_id, $action, $sub_action, $id, $content_pref, $datequery;
				extract($row);

				//$parent[] = array($content_id, $content_heading, $content_subheading, $content_summary, $content_text, $content_author, $content_icon, $content_file, $content_image, $content_parent, $content_comment, $content_rate, $content_pe, $content_refer, $content_datestamp, $content_class, $level);
				$content_pref['content_cat_tabbed_list_{$type_id}'] = "0";
				if($content_pref['content_cat_tabbed_list_{$type_id}']){
					$CONTENT_CAT_TABLE_WIDTH = "width:".(1+($row[16]*50))."px;";
				}else{
					$CONTENT_CAT_TABLE_WIDTH = "width:1px;";
				}
				$CONTENT_CAT_TABLE_AMOUNT = $aa -> countItemsInCat($row[0], $row[9]);
				$CONTENT_CAT_TABLE_ICON = $aa -> getIcon("catlarge", $row[6], $content_cat_icon_path_large, $type.".".$type_id.".cat.".$row[0], "", $content_pref["content_blank_caticon_{$type_id}"]);
				$CONTENT_CAT_TABLE_SUBHEADING = ($row[2] ? $row[2] : "");
				$CONTENT_CAT_TABLE_TEXT = ($row[4] ? $tp -> toHTML($row[4], TRUE, "") : "");

				$breadcrumb = $aa -> getBreadCrumb($row[0]);
				$breadcrumbstring = $aa -> printBreadCrumb($breadcrumb, "nobase");
				$CONTENT_CAT_TABLE_HEADING = $breadcrumbstring;

				$gen = new convert;
				$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row[14], "long"));
				$CONTENT_CAT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
			
				$authordetails = $aa -> getAuthor($row[5]);
				if(USER){
					$CONTENT_CAT_TABLE_AUTHORDETAILS = $authordetails[1]." ";
					if(is_numeric($authordetails[3])){
						$CONTENT_CAT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
					}else{
						$CONTENT_CAT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
					}
					$CONTENT_CAT_TABLE_AUTHORDETAILS .= "<a href='".e_SELF."?".$type.".".$type_id.".author' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}else{
					$CONTENT_CAT_TABLE_AUTHORDETAILS = $authordetails[1]." ".CONTENT_ICON_USER." <a href='".e_SELF."?".$type.".".$type_id.".author' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}

				if($row[12]){
					$CONTENT_CAT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$row[0]}");
					$CONTENT_CAT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$row[0]}");
				}
				$CONTENT_CAT_TABLE_RATING = "";
				if($row[11]){
					if($ratearray = $rater -> getrating("content_cat", $row[0])){
						for($c=1; $c<= $ratearray[1]; $c++){
							$CONTENT_CAT_TABLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
						}
						if($ratearray[1] < 10){
							for($c=9; $c>=$ratearray[1]; $c--){
								$CONTENT_CAT_TABLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
							}
						}
						$CONTENT_CAT_TABLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
						if($ratearray[2] == ""){ $ratearray[2] = 0; }
						$CONTENT_CAT_TABLE_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
						$CONTENT_CAT_TABLE_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
					}else{
						$CONTENT_CAT_TABLE_RATING .= LAN_65;
					}
					if(!$rater -> checkrated("content_cat", $row[0]) && USER){
						$CONTENT_CAT_TABLE_RATING .= " - ".$rater -> rateselect(LAN_40, "content_cat", $row[0]);
					}else if(USER){
						$CONTENT_CAT_TABLE_RATING .= " - ".LAN_41;
					}
				}
				return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_TABLE));
}

function parse_content_cat_list_table($row){
				global $rater, $sql, $CONTENT_CAT_LIST_TABLE, $content_cat_icon_path_large, $content_cat_icon_path_small, $plugintable, $gen, $aa, $tp, $ep, $sub_action, $type, $type_id, $content_pref, $datequery;
				extract($row);

				$gen = new convert;
				$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($content_datestamp, "long"));
				$CONTENT_CAT_LIST_TABLE_DATE = ($datestamp != "" ? $datestamp : "");

				$authordetails = $aa -> getAuthor($content_author);
				$CONTENT_CAT_LIST_TABLE_AUTHORNAME = (USER ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".$authordetails[1]."</a>" : $authordetails[1]);
				$CONTENT_CAT_LIST_TABLE_AUTHOREMAIL = ($authordetails[2] ? $authordetails[2] : "");

				if(USER){
					$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = $authordetails[1]." ";
					if(is_numeric($authordetails[3])){
						$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
					}else{
						$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
					}
					$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS .= "<a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}else{
					$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = $authordetails[1]." ".CONTENT_ICON_USER." <a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}

				$CONTENT_CAT_LIST_TABLE_ICON = $aa -> getIcon("catlarge", $content_icon, $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$type_id}"]);
				$CONTENT_CAT_LIST_TABLE_HEADING = ($content_heading ? $content_heading : "");
				$CONTENT_CAT_LIST_TABLE_SUBHEADING = ($content_subheading ? $content_subheading : "");
				$CONTENT_CAT_LIST_TABLE_SUMMARY = ($content_summary ? $tp -> toHTML($content_summary, TRUE, "") : "");
				$CONTENT_CAT_LIST_TABLE_TEXT = ($content_text ? $tp -> toHTML($content_text, TRUE, "") : "");

				if($content_comment){
					$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$sub_action."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
					$CONTENT_CAT_LIST_TABLE_COMMENT = "<a href='".e_SELF."?".$type.".".$type_id.".cat.".$sub_action.".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
				}

				$CONTENT_CAT_LIST_TABLE_RATING = "";
				if($content_rate){
					if($ratearray = $rater -> getrating("content_cat", $content_id)){
						for($c=1; $c<= $ratearray[1]; $c++){
							$CONTENT_CAT_LIST_TABLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
						}
						if($ratearray[1] < 10){
							for($c=9; $c>=$ratearray[1]; $c--){
								$CONTENT_CAT_LIST_TABLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
							}
						}
						$CONTENT_CAT_LIST_TABLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
						if($ratearray[2] == ""){ $ratearray[2] = 0; }
						$CONTENT_CAT_LIST_TABLE_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
						$CONTENT_CAT_LIST_TABLE_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
					}else{
						$CONTENT_CAT_LIST_TABLE_RATING .= LAN_65;
					}
					if(!$rater -> checkrated("content_cat", $content_id) && USER){
						$CONTENT_CAT_LIST_TABLE_RATING .= " - ".$rater -> rateselect(LAN_40, "content_cat", $content_id);
					}else if(USER){
						$CONTENT_CAT_LIST_TABLE_RATING .= " - ".LAN_41;
					}
				}
				if($content_pe){
					$CONTENT_CAT_LIST_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$sub_action}");
					$CONTENT_CAT_LIST_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$sub_action}");
				}
				return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CAT_LIST_TABLE));
}

function parse_content_content_table($row){
				global $rater, $content_icon_path, $content_file_path, $content_image_path, $gen, $aa, $tp, $ep, $from, $number;
				global $type, $type_id, $action, $sub_action, $id, $id2, $content_pref, $plugintable;
				global $ns, $sql, $pref, $cobj, $datequery;

				$CONTENT_CONTENT_TABLE_ICON = "";
				$CONTENT_CONTENT_TABLE_IMAGES = "";
				$CONTENT_CONTENT_TABLE_FILE = "";
				$CONTENT_CONTENT_TABLE_SCORE = "";
				$CONTENT_CONTENT_TABLE_SUMMARY = "";
				$CONTENT_CONTENT_TABLE_TEXT = "";
				$CONTENT_CONTENT_TABLE_PAGENAMES = "";

				extract($row);

				if(substr($content_parent,0,1) == "0"){ return FALSE; }
				if(!check_class($content_class)){ return FALSE; }

				if($content_pref["content_log_{$type_id}"]){
					$ip = getip();
					$self = e_SELF;
					$refertmp = explode("^", $content_refer);
					if(!ereg($ip, $refertmp[1]) && (!eregi("admin", $self))){
						$referiplist = ($refertmp[1] ? $refertmp[1]."-".$ip."-" : $ip."-" );
						$contentrefernew = ($refertmp[0]+1)."^".$referiplist;
						$sql = new db;
						$sql -> db_Update($plugintable, "content_refer='".$contentrefernew."' WHERE content_id='".$sub_action."' ");
					}
				}
				if($content_pref["content_content_refer_{$type_id}"]){
					$sql = new db;
					$sql -> db_Select($plugintable, "content_refer", "content_id='".$sub_action."' ");
					list($content_refer) = $sql -> db_Fetch();
					$refercounttmp = explode("^", $content_refer);
					$CONTENT_CONTENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "");
				}

				if($content_comment){
					$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$sub_action."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
					$CONTENT_CONTENT_TABLE_COMMENT = $comment_total;
				}

				if($content_pref["content_content_date_{$type_id}"]){
					$gen = new convert;
					$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($content_datestamp, "long"));
					$CONTENT_CONTENT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
				}

				if($content_pref["content_content_authorname_{$type_id}"] || $content_pref["content_content_authoremail_{$type_id}"]){
					$authordetails = $aa -> getAuthor($content_author);
					if($content_pref["content_content_authoremail_{$type_id}"] && $authordetails[2]){
						if($authordetails[0] == "0"){
							if($content_pref["content_content_authoremail_nonmember_{$type_id}"]){
								$CONTENT_CONTENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
							}else{
								$CONTENT_CONTENT_TABLE_AUTHORDETAILS = $authordetails[1];
							}
						}else{
							$CONTENT_CONTENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
						}
					}else{
						$CONTENT_CONTENT_TABLE_AUTHORDETAILS = $authordetails[1];
					}
					if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
						$CONTENT_CONTENT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
					}else{
						//$CONTENT_CONTENT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
					}
				}
				$CONTENT_CONTENT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$content_id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";

				$filestmp = explode("[file]", $content_file);
				foreach($filestmp as $key => $value) { 
					if($value == "") { 
						unset($filestmp[$key]); 
					} 
				} 
				$files = array_values($filestmp);
				$content_files_popup_name = ereg_replace("'", "", $content_heading);
				$file = "";
				for($i=0;$i<count($files);$i++){
					if(file_exists($content_file_path.$files[$i])){
						$file .= "<a href='".$content_file_path.$files[$i]."' rel='external'>".CONTENT_ICON_FILE."</a> ";						
					}else{
						$file .= "&nbsp;";
					}
				}
				$CONTENT_CONTENT_TABLE_FILE = (count($files) == "0" ? "" : CONTENT_LAN_41." ".(count($files) == 1 ? CONTENT_LAN_42 : CONTENT_LAN_43)." ".$file." ");

				$imagestmp = explode("[img]", $content_image);
				foreach($imagestmp as $key => $value) { 
					if($value == "") { 
						unset($imagestmp[$key]); 
					} 
				} 
				$images = array_values($imagestmp);
				$content_image_popup_name = ereg_replace("'", "", $content_heading);
				$CONTENT_CONTENT_TABLE_IMAGES = "";
				for($i=0;$i<count($images);$i++){
					if(file_exists($content_image_path.$images[$i])){
						$CONTENT_CONTENT_TABLE_IMAGES .= "<a style='cursor:pointer' href=\"javascript:popImage('".$content_image_path.$images[$i]."','".$content_image_popup_name." ".($i+1)."')\"><img src='".$content_image_path.$images[$i]."' style='border:1px solid #000; width:100px' alt='' /></a><br /><br />";
					}else{
						$CONTENT_CONTENT_TABLE_IMAGES .= "";
					}
				}
				
				if($content_pref["content_content_rating_{$type_id}"] || $content_pref["content_content_rating_all_{$type_id}"] || $content_rate){
					$CONTENT_CONTENT_TABLE_RATING = "";
					if($ratearray = $rater -> getrating($plugintable, $content_id)){
						for($c=1; $c<= $ratearray[1]; $c++){
							$CONTENT_CONTENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
						}
						if($ratearray[1] < 10){
							for($c=9; $c>=$ratearray[1]; $c--){
								$CONTENT_CONTENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
							}
						}
						$CONTENT_CONTENT_TABLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
						if($ratearray[2] == ""){ $ratearray[2] = 0; }
						$CONTENT_CONTENT_TABLE_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
						$CONTENT_CONTENT_TABLE_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
					}else{
						$CONTENT_CONTENT_TABLE_RATING .= LAN_65;
					}
					if(!$rater -> checkrated($plugintable, $content_id) && USER){
						$CONTENT_CONTENT_TABLE_RATING .= " - ".$rater -> rateselect(LAN_40, $plugintable, $content_id);
					}else if(USER){
						$CONTENT_CONTENT_TABLE_RATING .= " - ".LAN_41;
					}
				}

				if(($content_pref["content_content_peicon_{$type_id}"] && $content_pe) || $content_pref["content_content_peicon_all_{$type_id}"]){
					$CONTENT_CONTENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.$content_id}");
					$CONTENT_CONTENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.$content_id}");
				}

				$content_text = ($content_text ? $content_text : "");
				if(preg_match_all("/\[newpage=(.*?)]/si", $content_text, $matches)) {

					$textpages = explode("[newpage=", $content_text);
					$CONTENT_CONTENT_TABLE_TEXT = substr($textpages[(!$id ? 1 : $id)], (strpos($textpages[(!$id ? 1 : $id)], "]")+1) );
					$CONTENT_CONTENT_TABLE_PAGENAMES = "";
					for ($i=0; $i < count($matches[1]); $i++) {
						if(!$id){ $id = ($id + 1); }
						if($id == $i+1){ $pre = " - current"; }else{ $pre = ""; }
						$CONTENT_CONTENT_TABLE_PAGENAMES .= "page ".($i+1)." ".$pre." : <a href='".e_SELF."?".$type.".".$type_id.".content.".$sub_action.".".($i+1)."'>".$matches[1][$i]."</a><br />";
					}
				}else{
					$CONTENT_CONTENT_TABLE_TEXT = $content_text;
				}
				$CONTENT_CONTENT_TABLE_TEXT = $aa -> parseContentPathVars($CONTENT_CONTENT_TABLE_TEXT);
				$CONTENT_CONTENT_TABLE_TEXT = $tp -> toHTML($CONTENT_CONTENT_TABLE_TEXT, TRUE, "");
				$CONTENT_CONTENT_TABLE_ICON = $aa -> getIcon("item", $content_icon, $content_icon_path, "", "100", $content_pref["content_blank_icon_{$type_id}"]);
				$CONTENT_CONTENT_TABLE_HEADING = ($content_heading ? $content_heading : "");
				$CONTENT_CONTENT_TABLE_SUBHEADING = ($content_pref["content_content_subheading_{$type_id}"] && $content_subheading ? $tp -> toHTML($content_subheading, TRUE, "") : "");
				$CONTENT_CONTENT_TABLE_SUMMARY = ($content_pref["content_content_summary_{$type_id}"] && $content_summary ? $tp -> toHTML($content_summary, TRUE, "") : "");
				$CONTENT_CONTENT_TABLE_SUMMARY = $aa -> parseContentPathVars($CONTENT_CONTENT_TABLE_SUMMARY);

				$custom = unserialize(stripslashes($contentprefvalue));

				if($custom['content_custom_score']){
					if(strlen($custom['content_custom_score']) == "2"){
						$CONTENT_CONTENT_TABLE_SCORE = substr($custom['content_custom_score'],0,1).".".substr($custom['content_custom_score'],1,2);
					}else{
						$CONTENT_CONTENT_TABLE_SCORE = "0.".$custom['content_custom_score'];
					}
				}

				foreach($custom as $k => $v){
					if(!($k == "content_custom_score" || $k == "content_custom_meta")){
						$key = substr($k,15);
						$CONTENT_CONTENT_TABLE_CUSTOM_KEY[] = $key;
						$CONTENT_CONTENT_TABLE_CUSTOM_VALUE[] = $v;
					}
				}
				
				$CONTENT_CONTENT_TABLE = "";
				if(!$CONTENT_CONTENT_TABLE){
					if(!$content_pref["content_theme_{$type_id}"]){
						require_once(e_PLUGIN."content/templates/default/content_content_template.php");
					}else{
						if(file_exists(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_content_template.php")){
							require_once(e_PLUGIN."content/templates/".$content_pref["content_theme_{$type_id}"]."/content_content_template.php");
						}else{
							require_once(e_PLUGIN."content/templates/default/content_content_template.php");
						}
					}
				}
				return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENT_TABLE));
}


require_once(FOOTERF);

?>

<script type='text/javascript'>

// Script Source: CodeLifter.com
// Copyright 2003
// Do not remove this notice.

// Set the horizontal and vertical position for the popup
PositionX = 10;
PositionY = 10;

// Set these value approximately 20 pixels greater than the
// size of the largest image to be used (needed for Netscape)
defaultWidth  = 600;
defaultHeight = 600;

// Set autoclose true to have the window close automatically
// Set autoclose false to allow multiple popup windows
var AutoClose = true;

if (parseInt(navigator.appVersion.charAt(0))>=4){
	var isNN=(navigator.appName=="Netscape")?1:0;
	var isIE=(navigator.appName.indexOf("Microsoft")!=-1)?1:0;
}
var optNN='scrollbars=no,width='+defaultWidth+',height='+defaultHeight+',left='+PositionX+',top='+PositionY;
var optIE='scrollbars=no,width=150,height=100,left='+PositionX+',top='+PositionY;

function popImage(imageURL,imageTitle, defaultWidth, defaultHeight){
	if (isNN){imgWin=window.open('about:blank','',optNN);}
	if (isIE){imgWin=window.open('about:blank','',optIE);}

	with (imgWin.document){
		writeln('<html><head><title>Loading...</title><style>body{margin:0px; text-align:center; background-color:#FFF;}</style>');
		writeln('<sc'+'ript>');
		writeln('var isNN,isIE;');
		writeln('var imageWidth, imageHeight;');
		writeln('if (parseInt(navigator.appVersion.charAt(0))>=4){');
		writeln('isNN=(navigator.appName=="Netscape")?1:0;');
		writeln('isIE=(navigator.appName.indexOf("Microsoft")!=-1)?1:0;}');

		writeln('function reSizeToImage(){');
		writeln('if (isIE){');
		writeln('window.resizeTo(100,100);');
		writeln('width=100-(document.body.clientWidth-document.images[0].width);');
		writeln('height=100-(document.body.clientHeight-document.images[0].height);');
		writeln('window.resizeTo(width,height);}');

		writeln('if (isNN || !isIE){');       
		writeln('window.innerWidth=document.images["imagename"].width;');
		writeln('window.innerHeight=document.images["imagename"].height;}}');

		writeln('function doTitle(){document.title="'+imageTitle+'";}');
		writeln('</sc'+'ript>');

		if (!AutoClose) 
			writeln('</head><body scroll="no" onload="reSizeToImage();doTitle();self.focus()">')
		else 
			writeln('</head><body scroll="no" onload="reSizeToImage();doTitle();self.focus()" onblur="self.close()">');
			writeln('<img name="imagename" src='+imageURL+' align=center valign=middle style="display:block; "></body></html>');
			close();		
	}
}

</script>