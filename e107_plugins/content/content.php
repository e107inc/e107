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
|		$Revision: 1.75 $
|		$Date: 2005-07-01 13:15:18 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");
$plugindir = e_PLUGIN."content/";
require_once($plugindir."content_shortcodes.php");
require_once(e_HANDLER."emailprint_class.php");
$ep = new emailprint;
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(e_HANDLER."rate_class.php");
$rater = new rater;
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once($plugindir."handlers/content_class.php");
$aa = new content;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
$lan_file = $plugindir."languages/".e_LANGUAGE."/lan_content.php";
include_once(file_exists($lan_file) ? $lan_file : $plugindir."languages/English/lan_content.php");

if(e_QUERY){
	$qs = explode(".", e_QUERY);

	if(is_numeric($qs[0])){
		$from = array_shift($qs);
	}else{
		$from = "0";
	}
}

$aa -> setPageTitle();

require_once(HEADERF);

//core_head function to prepare the meta tags
function core_head(){
	global $qs, $sql, $tp, $pref, $plugintable, $eArrayStorage;

	if($qs[0] == "content" && isset($qs[1]) && is_numeric($qs[1]) ){
		if($sql -> db_Select($plugintable, "content_meta", "content_id='".$qs[1]."'")){
			list($row['content_meta']) = $sql -> db_Fetch();

			$exmeta = $row['content_meta'];
			if($exmeta != ""){
				$exmeta = str_replace(", ", ",", $exmeta);
				$exmeta = str_replace(" ", ",", $exmeta);
				$exmeta = str_replace(",", ", ", $exmeta);
				$newmeta = "";
				$checkmeta = preg_match_all("/\<meta name=&#039;(.*?)&#039; content=&#039;(.*?)&#039;>/si", $pref['meta_tag'], $matches);

				if(empty($matches[1])){
					$newmeta = "<meta name=&#039;keywords&#039; content=&#039;".$exmeta."&#039; />";
				}else{
					for($i=0;$i<count($matches[1]);$i++){
						if($matches[1][$i] == "keywords"){
							$newmeta .= "<meta name=&#039;".$matches[1][$i]."&#039; content=&#039;".$matches[2][$i].", ".$exmeta."&#039; />";
						}else{
							$newmeta .= "<meta name=&#039;".$matches[1][$i]."&#039; content=&#039;".$matches[2][$i]."&#039; />";
						}
					}
				}
				$pref['meta_tag'] = $tp -> toHTML($newmeta, "admin");
			}
		}
	}
}

//include js
function headerjs(){
	echo "<script type='text/javascript' src='".e_FILE."popup.js'></script>\n";
}

//post comment
if(isset($_POST['commentsubmit'])){
	if(!is_object($sql)){ $sql = new db; }
	if(!$sql -> db_Select($plugintable, "content_comment", "content_id='".$qs[1]."' ")){
		header("location:".e_BASE."index.php"); exit;
	}else{
		$row = $sql -> db_Fetch();
		if(ANON === TRUE || USER === TRUE){
			//enter_comment($author_name, $comment, $table, $id, $pid, $subject)
			$pid = "0";
			$rated = (isset($_POST['rateindex']) ? $_POST['rateindex'] : "");
			$cobj -> enter_comment(USERNAME, $_POST['comment'], $plugintable, $qs[1], $pid, $_POST['subject'], $rated);
			if($qs[0] == "content" && is_numeric($qs[1])){
				if(!isset($qs[2])){ $cacheid = 1; }else{ $cacheid = $qs[2]; }
				$e107cache->clear("comment.$plugintable.$qs[1].$cacheid");
				$e107cache->clear("$plugintable.content.$qs[1].$cacheid");
			}
			if($qs[0] == "cat" && is_numeric($qs[1])){
				$e107cache->clear("comment.$plugintable.$qs[1]");
			}
			$main = $aa -> getMainParent( (is_numeric($qs[1]) ? $qs[1] : $qs[2]) );
			$e107cache->clear("$plugintable.recent.$main");
			$e107cache->clear("$plugintable.cat.list.$main");
			$e107cache->clear("$plugintable.cat.$main");
			$e107cache->clear("$plugintable.author.$main");
			$e107cache->clear("$plugintable.top.$main");
			$e107cache->clear("$plugintable.score.$main");
		}
	}
}

//check active keyword search
$resultmenu = FALSE;
$searchfieldname = "searchfield_page";
$searchfieldmenuname = "searchfieldmenu_menu";
if(isset($_POST['searchsubmit']) || isset($_POST[$searchfieldname]) || isset($_POST[$searchfieldmenuname])){		//if active keyword search
	if(isset($_POST[$searchfieldname]) && $_POST[$searchfieldname] != "" && $_POST[$searchfieldname] != CONTENT_LAN_18){
		$resultmenu = TRUE;
		$searchkeyword = $_POST[$searchfieldname];
	}
	if(isset($_POST[$searchfieldmenuname]) && $_POST[$searchfieldmenuname] != "" && $_POST[$searchfieldmenuname] != CONTENT_LAN_18){
		$resultmenu = TRUE;
		$searchkeyword = $_POST[$searchfieldmenuname];
	}
	//show search results
	if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }
}

// ##### REDIRECTION MANAGEMENT -------------------------------------------------------
//parent overview
if(!e_QUERY){
	show_content();
}else{
	//recent of parent='2'
	if( $qs[0] == "recent" && is_numeric($qs[1]) && ( !isset($qs[2]) || substr($qs[2],0,5) == "order" ) ){
		show_content_recent();

	//item
	}elseif( $qs[0] == "content" && is_numeric($qs[1]) ){
		show_content_item();

	//all categories of parent='2'
	}elseif( $qs[0] == "cat" && $qs[1] == "list" && is_numeric($qs[2]) && !isset($qs[3]) ){
		show_content_cat_all();

	//category of parent='2' and content_id='5'
	}elseif( $qs[0] == "cat" && is_numeric($qs[1]) && (!isset($qs[2]) || $qs[2] == "comment" || substr($qs[2],0,5) == "order") ){
		
		if( isset($qs[2]) && $qs[2] == "comment" ){
			show_content_cat("comment");
		}else{
			show_content_cat();
		}

	//top rated of parent='2'
	}elseif( $qs[0] == "top" && is_numeric($qs[1]) && !isset($qs[2]) ){
		show_content_top();

	//top score of parent='2'
	}elseif( $qs[0] == "score" && is_numeric($qs[1]) ){
		// && !isset($qs[2])
		show_content_score();

	//authorlist of parent='2'
	}elseif( $qs[0] == "author" && $qs[1] == "list" && is_numeric($qs[2]) && ( !isset($qs[3]) || substr($qs[3],0,5) == "order" ) ){
		show_content_author_all();

	//authorlist of parent='2' and content_id='5'
	}elseif( $qs[0] == "author" && is_numeric($qs[1]) && (!isset($qs[2]) || substr($qs[2],0,5) == "order")  ){
		show_content_author();

	//archive of parent='2'
	}elseif( $qs[0] == "list" && is_numeric($qs[1])  ){
		show_content_archive();
	}else{
		js_location(e_SELF);
	}
}
// ##### ------------------------------------------------------------------------------

// ##### CONTENT SEARCH MENU ----------------------------
function show_content_search_menu($mode, $mainparent){
		global $qs, $plugindir, $content_shortcodes, $tp, $ns, $rs, $aa;
		global $plugintable, $gen, $content_pref;
		global $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD;

		if( (isset($content_pref["content_navigator_{$mode}_{$mainparent}"]) && $content_pref["content_navigator_{$mode}_{$mainparent}"]) || (isset($content_pref["content_search_{$mode}_{$mainparent}"]) && $content_pref["content_search_{$mode}_{$mainparent}"]) || (isset($content_pref["content_ordering_{$mode}_{$mainparent}"]) && $content_pref["content_ordering_{$mode}_{$mainparent}"]) ){

			$CONTENT_SEARCH_TABLE = "";
			if(!$CONTENT_SEARCH_TABLE){
				if(!$content_pref["content_theme_{$mainparent}"]){
					require_once($plugindir."templates/default/content_search_template.php");
				}else{
					if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_search_template.php")){
						require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_search_template.php");
					}else{
						require_once($plugindir."templates/default/content_search_template.php");
					}
				}
			}
			if(isset($content_pref["content_navigator_{$mode}_{$mainparent}"]) && $content_pref["content_navigator_{$mode}_{$mainparent}"]){
				$CONTENT_SEARCH_TABLE_SELECT = $aa -> showOptionsSelect("page", $mainparent);
			}
			if(isset($content_pref["content_search_{$mode}_{$mainparent}"]) && $content_pref["content_search_{$mode}_{$mainparent}"]){
				$CONTENT_SEARCH_TABLE_KEYWORD = $aa -> showOptionsSearch("page", $mainparent);
			}
			if(isset($content_pref["content_ordering_{$mode}_{$mainparent}"]) && $content_pref["content_ordering_{$mode}_{$mainparent}"]){
				$CONTENT_SEARCH_TABLE_ORDER = $aa -> showOptionsOrder("page", $mainparent);
			}

			$text = $tp -> parseTemplate($CONTENT_SEARCH_TABLE, FALSE, $content_shortcodes);

			if($content_pref["content_searchmenu_rendertype_{$mainparent}"] == "2"){
				$caption = CONTENT_LAN_77;
				$ns -> tablerender($caption, $text);
			}else{
				echo $text;
			}
		}
		return TRUE;

}

function show_content_search_result($searchkeyword){
		global $qs, $content_shortcodes, $ns, $rs, $tp, $plugindir, $plugintable, $gen, $aa, $content_pref, $datequery, $gen, $mainparent, $content_icon_path;

		$mainparent			= $aa -> getMainParent( (is_numeric($qs[1]) ? $qs[1] : $qs[2]) );
		$content_pref		= $aa -> getContentPref($mainparent);
		$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
		$validparent		= implode(",", array_keys($array));
		$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$qry				.= " AND (content_heading REGEXP '".$searchkeyword."' OR content_subheading REGEXP '".$searchkeyword."' OR content_summary REGEXP '".$searchkeyword."' OR content_text REGEXP '".$searchkeyword."' ) ";
		$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);

		$sqlsr = "";
		if(!is_object($sqlsr)){ $sqlsr = new db; }
		if(!$sqlsr -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon, content_datestamp", " ".$qry." ".$datequery." ORDER BY content_heading")){
			$textsr = "<div style='text-align:center;'>".CONTENT_SEARCH_LAN_0."</div>";
		}else{
			$CONTENT_SEARCHRESULT_TABLE = "";
			if(!$CONTENT_SEARCHRESULT_TABLE){
				if(!$content_pref["content_theme_{$mainparent}"]){
					require_once($plugindir."templates/default/content_searchresult_template.php");
				}else{
					if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_searchresult_template.php")){
						require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_searchresult_template.php");
					}else{
						require_once($plugindir."templates/default/content_searchresult_template.php");
					}
				}
			}
			$content_searchresult_table_string = "";
			if(!is_object($gen)){ $gen = new convert; }
			while($row = $sqlsr -> db_Fetch()){
				
				$row['content_heading']		= parsesearch($row['content_heading'], $searchkeyword, "full");
				$row['content_subheading']	= parsesearch($row['content_subheading'], $searchkeyword, "full");
				$row['content_text']		= parsesearch($row['content_text'], $searchkeyword, "");

				$content_searchresult_table_string .= $tp -> parseTemplate($CONTENT_SEARCHRESULT_TABLE, FALSE, $content_shortcodes);
			}
			$textsr = $CONTENT_SEARCHRESULT_TABLE_START.$content_searchresult_table_string.$CONTENT_SEARCHRESULT_TABLE_END;
		}
		$caption = CONTENT_LAN_20;
		$ns -> tablerender($caption, $textsr);
		require_once(FOOTERF);
		exit;
}

function parsesearch($text, $match, $amount){
		$text = strip_tags($text);
		$temp = stristr($text,$match);
		$pos = strlen($text)-strlen($temp);
		
		if($amount == "full"){
		}else{
			if($pos < 140){
					$text = "...".substr($text, 0, 140)."...";
			}else{
					$text = "...".substr($text, ($pos-140), 280)."...";
			}
		}
		$text = eregi_replace($match, "<span class='searchhighlight' style='color:red;'>$match</span>", $text);
		return($text);
}

// ##### CONTENT TYPE LIST ------------------------------
function show_content(){
		global $qs, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $content_cat_icon_path_large, $content_cat_icon_path_small, $datequery, $content_icon_path, $eArrayStorage;

		$CONTENT_TYPE_TABLE = "";
		if(!$CONTENT_TYPE_TABLE){
			require_once(e_PLUGIN."content/templates/content_type_template.php");
		}

		$cachestr = "$plugintable.typelist";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();
			if(!is_object($sql)){ $sql = new db; }
			if(!$sql -> db_Select($plugintable, "*", "content_parent = '0' AND content_class REGEXP '".e_CLASS_REGEXP."' ".$datequery." ORDER BY content_heading")){
				$text .= "<div style='text-align:center;'>".CONTENT_LAN_21."</div>";
			}else{

				$sql2 = "";
				$content_type_table_string = "";
				while($row = $sql -> db_Fetch()){
					if(!is_object($sql2)){ $sql2 = new db; }

					//$content_pref = unserialize(stripslashes($row['content_pref']));
					$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
					$content_pref["content_cat_icon_path_large_{$row['content_id']}"] = ($content_pref["content_cat_icon_path_large_{$row['content_id']}"] ? $content_pref["content_cat_icon_path_large_{$row['content_id']}"] : "{e_PLUGIN}content/images/cat/48/" );
					$content_pref["content_cat_icon_path_small_{$row['content_id']}"] = ($content_pref["content_cat_icon_path_small_{$row['content_id']}"] ? $content_pref["content_cat_icon_path_small_{$row['content_id']}"] : "{e_PLUGIN}content/images/cat/16/" );
					$content_cat_icon_path_large	= $tp->replaceConstants($content_pref["content_cat_icon_path_large_{$row['content_id']}"]);
					$content_cat_icon_path_small	= $tp->replaceConstants($content_pref["content_cat_icon_path_small_{$row['content_id']}"]);
					$content_icon_path				= $tp->replaceConstants($content_pref["content_icon_path_{$row['content_id']}"]);

					$array			= $aa -> getCategoryTree("", $row['content_id'], TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
					$contenttotal	= $sql2 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."'" );
					$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE, FALSE, $content_shortcodes);
				}

				$SUBMIT_LINE = FALSE;
				$submit = FALSE;
				$sql3 = "";
				if(!is_object($sql3)){ $sql3 = new db; }
				if($sql3 -> db_Select($plugintable, "content_id, content_pref", "content_parent = '0' ".$datequery." ORDER BY content_parent")){
					while($row = $sql3 -> db_Fetch()){
						if(isset($row['content_pref']) && $row['content_pref']){
							$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
						}
						if($content_pref["content_submit_{$row['content_id']}"] && check_class($content_pref["content_submit_class_{$row['content_id']}"])){
							$submit = TRUE;
							break;
						}
					}
					if($submit === TRUE){
						$content_type_table_string .= $CONTENT_TYPE_TABLE_LINE;
						$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE_SUBMIT, FALSE, $content_shortcodes);
						$SUBMIT_LINE = TRUE;
					}
				}

				if(USERID){
					$personalmanagercheck = FALSE;
					$array = $aa -> getCategoryTree("", "", TRUE);
					$catarray = array_keys($array);
					$qry = "";
					foreach($catarray as $catid){
						$qry .= " content_id='".$catid."' || ";
					}
					$qry = substr($qry,0,-3);
					if($sql -> db_Select($plugintable, "content_id, content_heading, content_pref", " ".$qry." ")){
						while($row = $sql -> db_Fetch()){
							if(isset($row['content_pref']) && $row['content_pref']){
								$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
							}
							//assign new preferences
							if(getperms("0") ){
								$personalmanagercheck = TRUE;
								break;
							}
							$contentid = $row['content_id'];
							if(isset($content_pref["content_manager_allowed_{$row['content_id']}"]) ){
								$pcm = explode(",", $content_pref["content_manager_allowed_{$row['content_id']}"]);
								if(in_array(USERID, $pcm)){
									$personalmanagercheck = TRUE;
									break;
								}
							}
						}
					}
					if($personalmanagercheck == TRUE){
						if($SUBMIT_LINE != TRUE){
							$content_type_table_string .= $CONTENT_TYPE_TABLE_LINE;
						}
						$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE_MANAGER, FALSE, $content_shortcodes);
					}
				}
				$text = $CONTENT_TYPE_TABLE_START.$content_type_table_string.$CONTENT_TYPE_TABLE_END;
			}
			$caption = CONTENT_LAN_22;
			$ns -> tablerender($caption, $text);

			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

// ##### CONTENT ARCHIVE ------------------------------------------
function show_content_archive(){
		global $ns, $plugindir, $plugintable, $sql, $aa, $rs, $e107cache, $tp, $pref, $content_pref, $cobj;
		global $qs, $searchkeyword, $nextprevquery, $from, $number, $mainparent, $content_shortcodes;
		global $CONTENT_ARCHIVE_TABLE, $CONTENT_ARCHIVE_TABLE_START, $datequery, $CONTENT_ARCHIVE_TABLE_LETTERS;
		global $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD, $CONTENT_ARCHIVE_TABLE_AUTHOR;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);

		show_content_search_menu("archive", $mainparent);		//show navigator/search/order menu

		$CONTENT_ARCHIVE_TABLE = "";
		if(!$CONTENT_ARCHIVE_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_archive_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_archive_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_archive_template.php");
				}else{
					require_once($plugindir."templates/default/content_archive_template.php");
				}
			}
		}

		$cachestr = "$plugintable.archive.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();
			
			$text = "";
			$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
			$validparent	= implode(",", array_keys($array));
			$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
			$number			= (isset($content_pref["content_archive_nextprev_number_{$mainparent}"]) && $content_pref["content_archive_nextprev_number_{$mainparent}"] ? $content_pref["content_archive_nextprev_number_{$mainparent}"] : "30");
			$order			= $aa -> getOrder();
			$nextprevquery	= (isset($content_pref["content_archive_nextprev_{$mainparent}"]) && $content_pref["content_archive_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
			$sql1 = new db;
			
			if(isset($content_pref["content_archive_letterindex_{$mainparent}"]) && $content_pref["content_archive_letterindex_{$mainparent}"]){
				$distinctfirstletter = $sql1 -> db_Select($plugintable, "DISTINCT(LEFT(content_heading,1)) as letter", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ORDER BY content_heading ASC ");
				if ($distinctfirstletter > 1){
					$CONTENT_ARCHIVE_TABLE_LETTERS = "<form method='post' action='".e_SELF."?list.".$mainparent."'>";
					while($row = $sql1 -> db_Fetch()){
						if($row['letter'] != ""){
							$thisletter = $row['letter'];
							$CONTENT_ARCHIVE_TABLE_LETTERS .= "<input class='button' style='width:20px' type='button' name='letter' value='".strtoupper($row['letter'])."' onclick=\"document.location='".e_SELF."?list.".$mainparent.".".strtoupper($thisletter)."'\" />";
						}
					}
					$CONTENT_ARCHIVE_TABLE_LETTERS .= "<input class='button' style='width:20' type='submit' name='letter' value='all' />";
					$CONTENT_ARCHIVE_TABLE_LETTERS .= "</form>";
				}
				//check posted letter
				$letter=(isset($qs[2]) && strlen($qs[2]) == "1" && substr($qs[2],0,5) != "order" ? $qs[2] : "");
				if ($letter != "" && $letter != "all" ) { $qry .= " AND content_heading LIKE '".$letter."%' "; }else{ $qry .= ""; }
			}
			$CONTENT_ARCHIVE_TABLE_START = $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE_START, FALSE, $content_shortcodes);

			$contenttotal = $sql1 -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ");
			if($from > $contenttotal-1){ header("location:".e_SELF); exit; }

			if($item = $sql1 -> db_Select($plugintable, "*", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){
				$content_archive_table_string = "";
				while($row = $sql1 -> db_Fetch()){
					$CONTENT_ARCHIVE_TABLE_AUTHOR	= $aa -> prepareAuthor("archive", $row['content_author'], $row['content_id']);
					$content_archive_table_string .= $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE, FALSE, $content_shortcodes);
				}
				$text .= $CONTENT_ARCHIVE_TABLE_START.$content_archive_table_string.$CONTENT_ARCHIVE_TABLE_END;
			}
			$text		= $aa -> getCrumbPage("archive", $array, $mainparent).$text;
			$caption	= CONTENT_LAN_84;
			$ns->tablerender($caption, $text);
			$aa -> ShowNextPrev("archive", $from, $number, $contenttotal);
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

//this function renders the preview of a content_item
//used in recent list, view author list, category items list
function displayPreview($qry){
		global $sql, $sql2, $tp, $plugintable, $plugindir, $content_pref, $mainparent, $CONTENT_RECENT_TABLE, $CONTENT_RECENT_TABLE_AUTHORDETAILS;

		if(!$CONTENT_RECENT_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_recent_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php");
				}else{
					require_once($plugindir."templates/default/content_recent_template.php");
				}
			}
		}
		if($resultitem = $sql2 -> db_Select($plugintable, "*", $qry )){
			$content_recent_table_string = "";
			while($row = $sql2 -> db_Fetch()){
				$rdate	= $tp -> parseTemplate('{CONTENT_RECENT_TABLE_DATE}', FALSE, $content_shortcodes);
				$rauth	= $tp -> parseTemplate('{CONTENT_RECENT_TABLE_AUTHORDETAILS}', FALSE, $content_shortcodes);
				$rep	= $tp -> parseTemplate('{CONTENT_RECENT_TABLE_EPICONS}', FALSE, $content_shortcodes);
				$rpar	= $tp -> parseTemplate('{CONTENT_RECENT_TABLE_PARENT}', FALSE, $content_shortcodes);
				$redi	= $tp -> parseTemplate('{CONTENT_RECENT_TABLE_EDITICON}', FALSE, $content_shortcodes);
				$CONTENT_RECENT_TABLE_INFOPRE = FALSE;
				$CONTENT_RECENT_TABLE_INFOPOST = FALSE;
				if ($rdate!="" || $rauth!="" || $rep!="" || $rpar!="" || $redi!="" ) {
					$CONTENT_RECENT_TABLE_INFOPRE = TRUE;
					$CONTENT_RECENT_TABLE_INFOPOST = TRUE;
				}
				$CONTENT_RECENT_TABLE_AUTHORDETAILS = $aa -> prepareAuthor("list", $row['content_author'], $row['content_id']);
				$content_recent_table_string .= $tp -> parseTemplate($CONTENT_RECENT_TABLE, FALSE, $content_shortcodes);
			}
		}
		$text = $CONTENT_RECENT_TABLE_START.$content_recent_table_string.$CONTENT_RECENT_TABLE_END;

		return $text;
}

// ##### RECENT LIST ------------------------------------
function show_content_recent(){
		global $qs, $sql2, $plugindir, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
		global $nextprevquery, $from, $number, $mainparent, $datequery, $content_icon_path, $CONTENT_RECENT_TABLE;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("recent", $mainparent);		//show navigator/search/order menu

		$cachestr = "$plugintable.recent.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
			$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
			$validparent		= implode(",", array_keys($array));
			$order				= $aa -> getOrder();
			$number				= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
			$nextprevquery		= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
			$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

			$contenttotal = $sql2 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " );

			if($from > $contenttotal-1){ js_location(e_SELF); }
			
			$recentqry			= "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery;
			$text				= displayPreview($recentqry);
			$text				= $aa -> getCrumbPage("recent", $array, $mainparent).$text;
			$caption			= CONTENT_LAN_23;
			$ns -> tablerender($caption, $text);
			$aa -> ShowNextPrev("", $from, $number, $contenttotal);
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

// ##### CATEGORY LIST ------------------------------------
function show_content_cat_all(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $aa, $e107cache, $tp, $pref, $content_pref, $totalitems;
		global $sql, $datequery, $amount, $from, $content_cat_icon_path_large, $content_icon_path, $n, $mainparent, $CONTENT_CAT_TABLE, $CONTENT_CAT_TABLE_AUTHORDETAILS;

		unset($text);

		$mainparent		= $aa -> getMainParent($qs[2]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("catall", $mainparent);		//show navigator/search/order menu

		if(!$CONTENT_CAT_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_cat_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php");
				}else{
					require_once($plugindir."templates/default/content_cat_template.php");
				}
			}
		}

		$cachestr = "$plugintable.cat.list.$qs[2]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
			$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
			$array							= $aa -> getCategoryTree("", $mainparent, TRUE);
			$validparent					= implode(",", array_keys($array));
			$order							= $aa -> getOrder();
			$number							= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
			$nextprevquery					= (isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
			$qry							= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

			$content_cat_table_string = "";
			$newarray = array_merge_recursive($array);
			for($a=0;$a<count($newarray);$a++){
				for($b=0;$b<count($newarray[$a]);$b++){
					$newparent[$newarray[$a][$b]] = $newarray[$a][$b+1];
					$b++;
				}
			}
			foreach($newparent as $key => $value){
				$totalitems = $aa -> countCatItems($key);
				$sql -> db_Select($plugintable, "*", "content_id = '".$key."' ");
				$row = $sql -> db_Fetch();
				
				$date	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_DATE}', FALSE, $content_shortcodes);
				$auth	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_AUTHORDETAILS}', FALSE, $content_shortcodes);
				$ep		= $tp -> parseTemplate('{CONTENT_CAT_TABLE_EPICONS}', FALSE, $content_shortcodes);
				$com	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_COMMENT}', FALSE, $content_shortcodes);
				$CONTENT_CAT_TABLE_INFO_PRE = FALSE;
				$CONTENT_CAT_TABLE_INFO_POST = FALSE;
				if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
					$CONTENT_CAT_TABLE_INFO_PRE = TRUE;
					$CONTENT_CAT_TABLE_INFO_POST = TRUE;
				}
				$CONTENT_CAT_TABLE_AUTHORDETAILS = $aa -> prepareAuthor("catall", $row['content_author'], $row['content_id']);
				$content_cat_table_string .= $tp -> parseTemplate($CONTENT_CAT_TABLE, FALSE, $content_shortcodes);

			}
			$text		= $CONTENT_CAT_TABLE_START.$content_cat_table_string.$CONTENT_CAT_TABLE_END;
			$text		= $aa -> getCrumbPage("catall", $array, $mainparent).$text;
			$caption	= CONTENT_LAN_25;
			$ns -> tablerender($caption, $text);

			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

function show_content_cat($mode=""){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj, $datequery, $from;
		global $CONTENT_RECENT_TABLE, $CONTENT_CAT_LIST_TABLE, $CONTENT_CAT_LISTSUB_TABLE_START, $CONTENT_CAT_LISTSUB_TABLE, $CONTENT_CAT_LISTSUB_TABLE_END, $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS;
		global $content_cat_icon_path_small, $content_cat_icon_path_large, $content_icon_path, $mainparent, $totalparent, $totalsubcat;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);
		$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent	= "0,0.".implode(",0.", array_keys($array));
		$qry			= " content_id = '".$qs[1]."' AND content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

		show_content_search_menu("cat", $mainparent);		//show navigator/search/order menu

		if(!$CONTENT_CAT_LIST_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_cat_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php");
				}else{
					require_once($plugindir."templates/default/content_cat_template.php");
				}
			}
		}

		$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
		$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
		$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);					
		$order							= $aa -> getOrder();
		$number							= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
		$nextprevquery					= (isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
		$capqs							= array_reverse($array[$qs[1]]);
		$caption						= CONTENT_LAN_26." : ".$capqs[0];

		// parent article
		if(isset($content_pref["content_cat_showparent_{$mainparent}"]) && $content_pref["content_cat_showparent_{$mainparent}"]){
			if(!$resultitem = $sql -> db_Select($plugintable, "*", $qry )){
				header("location:".e_SELF."?cat.list.".$mainparent); exit;
			}else{
				$row = $sql -> db_Fetch();
				$date	= $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_DATE}', FALSE, $content_shortcodes);
				$auth	= $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_AUTHORDETAILS}', FALSE, $content_shortcodes);
				$ep		= $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_EPICONS}', FALSE, $content_shortcodes);
				$com	= $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_COMMENT}', FALSE, $content_shortcodes);
				if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
					$CONTENT_CAT_LIST_TABLE_INFO_PRE = TRUE;
					$CONTENT_CAT_LIST_TABLE_INFO_POST = TRUE;
				}
				$totalparent = $aa -> countCatItems($row['content_id']);
				$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = $aa -> prepareAuthor("cat", $row['content_author'], $row['content_id']);
				$textparent			= $tp -> parseTemplate($CONTENT_CAT_LIST_TABLE, FALSE, $content_shortcodes);							
			}
		}

		$cachestr = "$plugintable.cat.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			if(!$mode || $mode == ""){

				$check			= (isset($qs[1]) && is_numeric($qs[1]) ? $qs[1] : $mainparent);
				$array1			= $aa -> getCategoryTree("", $check, TRUE);
				$newarray		= array_merge_recursive($array1);
				for($a=0;$a<count($newarray);$a++){
					for($b=0;$b<count($newarray[$a]);$b++){
						$subparent[$newarray[$a][$b]] = $newarray[$a][$b+1];
						$b++;
					}
				}
				$subparent		= array_keys($subparent);
				$validsub		= "0.".implode(",0.", $subparent);
				$subqry			= " content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validsub)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

				//list subcategories
				if(isset($content_pref["content_cat_showparentsub_{$mainparent}"]) && $content_pref["content_cat_showparentsub_{$mainparent}"]){

					$content_cat_listsub_table_string = "";
					for($i=0;$i<count($subparent);$i++){
						if($resultitem = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_icon, content_parent", " content_id = '".$subparent[$i]."' AND ".$subqry." " )){
							while($row = $sql -> db_Fetch()){
								$totalsubcat = $aa -> countCatItems($row['content_id']);
								$content_cat_listsub_table_string .= $tp -> parseTemplate($CONTENT_CAT_LISTSUB_TABLE, FALSE, $content_shortcodes);
							}
							$textsubparent = $CONTENT_CAT_LISTSUB_TABLE_START.$content_cat_listsub_table_string.$CONTENT_CAT_LISTSUB_TABLE_END;
							$captionsubparent = CONTENT_LAN_28;
						}
					}
				}

				//list all contents within this category
				unset($text);

				//also show content items of subcategories of this category ?
				if(isset($content_pref["content_cat_listtype_{$mainparent}"]) && $content_pref["content_cat_listtype_{$mainparent}"]){
					$validitem		= implode(",", $subparent);
					$qrycat			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validitem)."' ";
				}else{
					$qrycat			= " content_parent = '".$qs[1]."' ";
				}
				$qrycat				= " content_refer !='sa' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND ".$qrycat." ";
				$contenttotal		= $sql -> db_Count($plugintable, "(*)", "WHERE ".$qrycat);
				$childqry			= $qrycat." ".$order." ".$nextprevquery;
				$textchild			= displayPreview($childqry);
				$captionchild		= CONTENT_LAN_31;

				if(isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"]){
					require_once(e_HANDLER."np_class.php");
					$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
				}
				$crumbpage = $aa -> getCrumbPage("cat", $array, $qs[1]);
				if(isset($textparent)){ 
					$textparent = $crumbpage.$textparent;
				}else{
					$textchild = $crumbpage.$textparent;
				}
				if(isset($content_pref["content_cat_menuorder_{$mainparent}"]) && $content_pref["content_cat_menuorder_{$mainparent}"] == "1"){
					if(isset($content_pref["content_cat_rendertype_{$mainparent}"]) && $content_pref["content_cat_rendertype_{$mainparent}"] == "1"){
						if(isset($textparent)){		$ns -> tablerender($caption, $textparent); }
						if(isset($textsubparent)){	$ns -> tablerender($captionsubparent, $textsubparent); }
						if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
					}else{
						$ns -> tablerender($caption, (isset($textparent) ? $textparent : "").(isset($textsubparent) ? $textsubparent : "").$textchild);
					}
					if(isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"]){
						$ix = new nextprev("content.php", $from, $number, $contenttotal, NP_3, ($np_querystring ? $np_querystring : ""));
					}
				}else{
					if(isset($content_pref["content_cat_rendertype_{$mainparent}"]) && $content_pref["content_cat_rendertype_{$mainparent}"] == "1"){
						if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
						if(isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"]){
							$ix = new nextprev("content.php", $from, $number, $contenttotal, NP_3, ($np_querystring ? $np_querystring : ""));
						}
						if(isset($textparent)){		$ns -> tablerender($caption, $textparent); }
						if(isset($textsubparent)){	$ns -> tablerender($captionsubparent, $textsubparent); }
					}else{
						if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
						if(isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"]){
							$ix = new nextprev("content.php", $from, $number, $contenttotal, NP_3, ($np_querystring ? $np_querystring : ""));
						}
						$ns -> tablerender($caption, (isset($textparent) ? $textparent : "").(isset($textsubparent) ? $textsubparent : ""));
					}
				}
			}
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}

		if($mode == "comment"){
			$textparent = $aa -> getCrumbPage("cat", $array, $mainparent).$textparent;
			if(isset($textparent)){ $ns -> tablerender($captionparent, $textparent); }

			if($resultitem = $sql -> db_Select($plugintable, "*", $qry )){
				$row = $sql -> db_Fetch();
				if($row['content_comment']){
					$cachestr = "comment.$plugintable.$qs[1]";
					if($cache = $e107cache->retrieve($cachestr)){
						echo $cache;
					}else{
						ob_start();
						if( (isset($content_pref["content_cat_rating_all_{$mainparent}"]) && $content_pref["content_cat_rating_all_{$mainparent}"]) || (isset($content_pref["content_cat_rating_{$mainparent}"]) && $content_pref["content_cat_rating_{$mainparent}"] && $row['content_rate'])){
							$showrate = TRUE;
						}else{
							$showrate = FALSE;
						}
						$cobj->compose_comment($plugintable, "comment", $qs[1], $width, $row['content_heading'], $showrate);

						if($pref['cachestatus']){
							$cache = ob_get_contents();
							$e107cache->set($cachestr, $cache);
						}
						ob_end_flush(); // dump collected data
					}
				}
			}
		}
}

// ##### AUTHOR LIST --------------------------------------
function show_content_author_all(){
		global $qs, $plugindir, $ix, $content_shortcodes, $ns, $plugintable, $from, $sql, $aa, $e107cache, $tp, $pref, $mainparent, $content_pref, $cobj, $datequery;
		$mainparent		= $aa -> getMainParent($qs[2]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("authorall", $mainparent);		//show navigator/search/order menu

		$CONTENT_AUTHOR_TABLE = "";
		if(!$CONTENT_AUTHOR_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_author_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_author_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_author_template.php");
				}else{
					require_once($plugindir."templates/default/content_author_template.php");
				}
			}
		}

		$cachestr = "$plugintable.author.list.$qs[2]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
			$validparent	= implode(",", array_keys($array));
			$number			= (isset($content_pref["content_author_nextprev_number_{$mainparent}"]) && $content_pref["content_author_nextprev_number_{$mainparent}"] ? $content_pref["content_author_nextprev_number_{$mainparent}"] : "5");
			$nextprevquery	= (isset($content_pref["content_author_nextprev_{$mainparent}"]) && $content_pref["content_author_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
			$qry			= " p.content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
			$dateqry		= "AND p.content_datestamp < ".time()." AND (p.content_enddate=0 || p.content_enddate>".time().")";

			$sql1 = new db; $sql2 = new db;
			$contenttotal = $sql1 -> db_Select($plugintable." AS p", "DISTINCT(p.content_author)", "p.content_refer !='sa' AND ".$qry." ".$datequery." AND p.content_class REGEXP '".e_CLASS_REGEXP."'");					
			
			$qry = "
			SELECT DISTINCT(p.content_author), p.content_heading, p.content_datestamp, p.content_id, u.user_id, u.user_name, u.user_email, SUBSTRING_INDEX(p.content_author, '^', 0) AS user_name2, COUNT(i.content_id) as amount 
			FROM #$plugintable AS p
			LEFT JOIN #$plugintable AS i ON i.content_author = p.content_author  
			LEFT JOIN #user AS u ON (u.user_id = p.content_author || SUBSTRING_INDEX(p.content_author, '^', 1) REGEXP 'u.user_name') 
			WHERE p.content_refer !='sa' AND ".$qry." ".$dateqry." AND p.content_class REGEXP '".e_CLASS_REGEXP."' AND i.content_class REGEXP '".e_CLASS_REGEXP."'
			GROUP BY i.content_datestamp
			ORDER BY p.content_datestamp ASC, SUBSTRING_INDEX(p.content_author, '^', 0)
			";

			$author = array();
			$options = "";
			if ($sql1->db_Select_gen($qry)){
				while($row = $sql1 -> db_Fetch()){
					if(strpos($row['content_author'], "^")){
						$tmp = explode("^", $row['content_author']);
						$row['content_author'] = $tmp[0];
					}
					if(is_numeric($row['content_author'])){
						$name = ($row['user_name'] ? $row['user_name'] : CONTENT_LAN_29);
					}else{
						$name = ($row['content_author'] ? $row['content_author'] : CONTENT_LAN_29);
					}
					if(is_array($author)){
						if(!in_array($name, $author)){

							$display = (strlen($name) > 29 ? substr($name,0,29)."..." : $name);
							$arrData[] = array($name, $row['content_id'], $row['content_heading'], $row['content_datestamp'], $row['amount']);
							$author[] = $name;
						}
					}
				}

				function cmp($a, $b)
				{
					$posa = strrpos($a[0], " ");
					if($posa == TRUE){
						$la = substr($a[0], $posa);
					}elseif($posa == FALSE){
						$la = $a[0];
					}

					$posb = strrpos($b[0], " ");
					if($posb == TRUE){
						$lb = substr($b[0], $posb);
					}elseif($posb == FALSE){
						$lb = $b[0];
					}

					if ($la == $lb) {
						return 0;
					}
					return strcasecmp ($la, $lb);
				}
				usort($arrData, "cmp");

				$content_author_table_string = "";
				$max = $from+$number;
				for($i=$from;$i<$max;$i++){
					if(is_array($arrData[$i])){
						$authordetails[$i][1] = $arrData[$i][0];
						$row['content_id'] = $arrData[$i][1];
						$row['content_heading'] = $arrData[$i][2];
						$row['content_datestamp'] = $arrData[$i][3];
						$totalcontent = $arrData[$i][4];
						$content_author_table_string .= $tp -> parseTemplate($CONTENT_AUTHOR_TABLE, FALSE, $content_shortcodes);
					}
				}
				$text = $CONTENT_AUTHOR_TABLE_START.$content_author_table_string.$CONTENT_AUTHOR_TABLE_END;
				$text = $aa -> getCrumbPage("authorall", $array, $mainparent).$text;
			}

			$caption = CONTENT_LAN_32;
			$ns -> tablerender($caption, $text);
			$aa -> ShowNextPrev("author", $from, $number, $contenttotal);
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

function show_content_author(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
		global $nextprevquery, $from, $number, $content_icon_path;
		global $CONTENT_RECENT_TABLE, $datequery, $crumb, $mainparent;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("author", $mainparent);		//show navigator/search/order menu

		$cachestr = "$plugintable.author.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
			$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
			if(array_key_exists($qs[1], $array)){
				$validparent	= "0,0.".implode(",0.", array_keys($array));
			}else{
				$validparent	= implode(",", array_keys($array));
			}
			$order				= $aa -> getOrder();
			$number				= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
			$nextprevquery		= (isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
			$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
			$sqla = "";
			if(!is_object($sqla)){ $sqla = new db; }
			if(!$author = $sqla -> db_Select($plugintable, "content_author", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_id = '".$qs[1]."' AND content_class REGEXP '".e_CLASS_REGEXP."' ")){
				header("location:".e_SELF."?author.list.".$mainparent); exit;
			}else{
				list($content_author)	= $sqla -> db_Fetch();
				$sqlb = new db;
				$authordetails	= $aa -> getAuthor($content_author);						
				$query			= " content_author = '".$authordetails[3]."' || content_author REGEXP '^".$authordetails[1]."^' ".(is_numeric($content_author) ? " || content_author = '".$authordetails[0]."' " : "")." ";
				$validparent	= implode(",", array_keys($array));
				$qry			= " content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND (".$query.") ";
				$contenttotal	= $sqlb -> db_Count($plugintable, "(*)", "WHERE ".$qry." ");
				$authorqry		= $qry." ".$order." ".$nextprevquery;
				$text			= displayPreview($authorqry);
				$text			= $aa -> getCrumbPage("author", $array, $mainparent).$text;
				$caption		= CONTENT_LAN_32." : ".$authordetails[1];
				$ns -> tablerender($caption, $text);
				$aa -> ShowNextPrev("", $from, $number, $contenttotal);
			}
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

// ##### TOP RATED LIST -----------------------------------
function show_content_top(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $cobj, $content_icon_path;
		global $from, $datequery, $content_pref, $mainparent;
		global $CONTENT_TOP_TABLE_AUTHOR;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("top", $mainparent);		//show navigator/search/order menu

		//$CONTENT_TOP_TABLE = "";
		if(!$CONTENT_TOP_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_top_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_top_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_top_template.php");
				}else{
					require_once($plugindir."templates/default/content_top_template.php");
				}
			}
		}

		$cachestr = "$plugintable.top.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
			$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
			$validparent		= implode(",", array_keys($array));
			$qry				= " c.content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
			$number				= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");

			$qry1 = "
			SELECT c.content_id, c.content_heading, c.content_author, c.content_icon, r.* 
			FROM #rate AS r
			LEFT JOIN #pcontent AS c ON c.content_id = r.rate_itemid  
			WHERE ".$qry." ".$datequery." AND c.content_class REGEXP '".e_CLASS_REGEXP."' AND r.rate_table='".$plugintable."'
			ORDER by r.rate_itemid
			";

			if(!is_object($sql)){ $sql = new db; }
			if (!$sql->db_Select_gen($qry1)){
				$err		= CONTENT_LAN_37;
			}else{
				while($row = $sql -> db_Fetch()){
					$tmp		= $row['rate_rating'] / $row['rate_votes'];
					$tmp		= explode(".", $tmp);
					$rating[1]	= $tmp[0];										// $ratomg[1] = main result
					$rating[2]	= (!empty($tmp[1]) ? substr($tmp[1],0,1) : "");	// $rating[2] = remainder
					$rate_avg	= $rating[1].".".($rating[2] ? $rating[2] : "0");	// rate average

					$arrRate[] = array($row['rate_itemid'], $row['rate_rating'], $row['rate_votes'], $rate_avg, $rating[1], $rating[2], $row['content_id'], $row['content_heading'], $row['content_author'], $row['content_icon']);
				}
				if(empty($arrRate)){
					$err		= CONTENT_LAN_37;
				}else{
					usort($arrRate, create_function('$a,$b','return $a[3]==$b[3]?0:($a[3]>$b[3]?-1:1);'));
					$contenttotal = count($arrRate);
					$content_top_table_string = "";

					for($i=$from;$i<$from+$number;$i++){
						if(isset($arrRate[$i])){
							$thisratearray				= $arrRate[$i];
							$row['content_id']			= $arrRate[$i][6];
							$row['content_heading']		= $arrRate[$i][7];
							$row['content_author']		= $arrRate[$i][8];
							$row['content_icon']		= $arrRate[$i][9];
							$CONTENT_TOP_TABLE_AUTHOR	= $aa -> prepareAuthor("top", $row['content_author'], $row['content_id']);
							$content_top_table_string	.= $tp -> parseTemplate($CONTENT_TOP_TABLE, FALSE, $content_shortcodes);
						}
					}
				}
			}
			if(isset($err)){
				$content_top_table_string = $err;
			}
			$content_top_table_string		= $aa -> getCrumbPage("top", $array, $mainparent).$content_top_table_string;
			$text		= $CONTENT_TOP_TABLE_START.$content_top_table_string.$CONTENT_TOP_TABLE_END;
			$caption	= CONTENT_LAN_38;
			$ns -> tablerender($caption, $text);
			$aa -> ShowNextPrev("", $from, $number, $contenttotal);
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

// ##### TOP SCORE LIST -----------------------------------
function show_content_score(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $cobj, $content_icon_path;
		global $from, $datequery, $content_pref, $mainparent, $eArrayStorage, $CONTENT_SCORE_TABLE_SCORE, $CONTENT_SCORE_TABLE_AUTHOR;

		$mainparent		= $aa -> getMainParent($qs[1]);
		$content_pref	= $aa -> getContentPref($mainparent);
		show_content_search_menu("score", $mainparent);		//show navigator/search/order menu

		$CONTENT_SCORE_TABLE = "";
		if(!$CONTENT_SCORE_TABLE){
			if(!$content_pref["content_theme_{$mainparent}"]){
				require_once($plugindir."templates/default/content_score_template.php");
			}else{
				if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_score_template.php")){
					require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_score_template.php");
				}else{
					require_once($plugindir."templates/default/content_score_template.php");
				}
			}
		}

		$cachestr = "$plugintable.score.$qs[1]";
		if($cache = $e107cache->retrieve($cachestr)){
			echo $cache;
		}else{
			ob_start();

			$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
			$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
			$validparent		= implode(",", array_keys($array));
			$qry				= " content_score != '0' AND content_score != '' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";
			$number				= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");

			if(!is_object($sql)){ $sql = new db; }
			$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE ".$qry." ");
			if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_author, content_icon, content_score", " ".$qry." ORDER BY content_score DESC LIMIT ".$from.",".$number." ")){
				$content_score_table_string = CONTENT_LAN_88;
			}else{
				while($row = $sql -> db_Fetch()){
					$CONTENT_SCORE_TABLE_AUTHOR	= $aa -> prepareAuthor("score", $row['content_author'], $row['content_id']);
					$content_score_table_string	.= $tp -> parseTemplate($CONTENT_SCORE_TABLE, FALSE, $content_shortcodes);
				}
			}
			$content_score_table_string = $aa -> getCrumbPage("score", $array, $mainparent).$content_score_table_string;
			$text		= $CONTENT_SCORE_TABLE_START.$content_score_table_string.$CONTENT_SCORE_TABLE_END;
			$caption	= CONTENT_LAN_87;
			$ns -> tablerender($caption, $text);
			$aa -> ShowNextPrev("", $from, $number, $contenttotal);
			if($pref['cachestatus']){
				$cache = ob_get_contents();
				$e107cache->set($cachestr, $cache);
			}
			ob_end_flush(); // dump collected data
		}
}

// ##### CONTENT ITEM ------------------------------------------
function show_content_item(){
		global $pref, $content_pref;
		global $CONTENT_CONTENT_TABLE_TEXT, $CONTENT_CONTENT_TABLE_PAGENAMES, $CONTENT_CONTENT_TABLE_SUMMARY, $CONTENT_CONTENT_TABLE_CUSTOM_TAGS, $CONTENT_CONTENT_TABLE_PARENT, $CONTENT_CONTENT_TABLE_INFO_PRE, $CONTENT_CONTENT_TABLE_INFO_POST, $CONTENT_CONTENT_TABLE_AUTHORDETAILS;
		global $content_icon_path, $content_image_path, $content_file_path, $custom;
		global $plugindir, $plugintable, $array, $content_shortcodes, $datequery, $order, $nextprevquery, $from, $number;
		global $qs, $gen, $sql, $aa, $tp, $rs, $cobj, $e107, $e107cache, $eArrayStorage, $ns, $rater, $ep, $row, $authordetails, $mainparent; 

		$mainparent			= $aa -> getMainParent($qs[1]);
		$content_pref		= $aa -> getContentPref($mainparent);
		show_content_search_menu("item", $mainparent);		//show navigator/search/order menu
		$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent		= implode(",", array_keys($array));
		$qry				= "content_id='".$qs[1]."' AND content_refer !='sa' AND  content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

		if(!$resultitem = $sql -> db_Select($plugintable, "*", $qry)){
			header("location:".e_SELF."?recent.".$mainparent); exit;
		}else{
			$row = $sql -> db_Fetch();

			//update refer count outside of cache
			if(isset($content_pref["content_log_{$mainparent}"]) && $content_pref["content_log_{$mainparent}"]){
				$ip			= $e107->getip();
				$self		= e_SELF;
				$refertmp	= explode("^", $row['content_refer']);
				if(!ereg($ip, $refertmp[1]) && (!eregi("admin", $self))){
					$referiplist		= ($refertmp[1] ? $refertmp[1]."-".$ip."-" : $ip."-" );
					$contentrefernew	= ($refertmp[0]+1)."^".$referiplist;
					$sql = new db;
					$sql -> db_Update($plugintable, "content_refer='".$contentrefernew."' WHERE content_id='".$qs[1]."' ");
					$e107cache->clear("$plugintable.content.$qs[1]");
					$e107cache->clear("$plugintable.recent.$mainparent");
					$e107cache->clear("$plugintable.cat.list.$mainparent");
					$e107cache->clear("$plugintable.cat.$mainparent");
					$e107cache->clear("$plugintable.author.$mainparent");
					$e107cache->clear("$plugintable.top.$mainparent");
				}
			}

			if(!isset($qs[2])){ $cacheid = 1; }else{ $cacheid = $qs[2]; }
			$cachestr = "$plugintable.content.$qs[1].$cacheid";
			if($cache = $e107cache->retrieve($cachestr)){
				echo $cache;
			}else{
				ob_start();
					$content_pref["content_cat_icon_path_large_{$mainparent}"] = ($content_pref["content_cat_icon_path_large_{$mainparent}"] ? $content_pref["content_cat_icon_path_large_{$mainparent}"] : "{e_PLUGIN}content/images/cat/48/" );
					$content_pref["content_cat_icon_path_small_{$mainparent}"] = ($content_pref["content_cat_icon_path_small_{$mainparent}"] ? $content_pref["content_cat_icon_path_small_{$mainparent}"] : "{e_PLUGIN}content/images/cat/16/" );
					$content_pref["content_icon_path_{$mainparent}"] = ($content_pref["content_icon_path_{$mainparent}"] ? $content_pref["content_icon_path_{$mainparent}"] : "{e_PLUGIN}content/images/icon/" );
					$content_pref["content_image_path_{$mainparent}"] = ($content_pref["content_image_path_{$mainparent}"] ? $content_pref["content_image_path_{$mainparent}"] : "{e_PLUGIN}content/images/image/" );
					$content_pref["content_file_path_{$mainparent}"] = ($content_pref["content_file_path_{$mainparent}"] ? $content_pref["content_file_path_{$mainparent}"] : "{e_PLUGIN}content/images/file/" );
					$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
					$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
					$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
					$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
					$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path_{$mainparent}"]);
					$number							= (isset($content_pref["content_nextprev_number_{$mainparent}"]) && $content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery					= (isset($content_pref["content_nextprev_{$mainparent}"]) && $content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");

					$CONTENT_CONTENT_TABLE_AUTHORDETAILS = $aa -> prepareAuthor("content", $row['content_author'], $row['content_id']);
					$CONTENT_CONTENT_TABLE_TEXT = $row['content_text'];

					$lastpage = FALSE;		//boolean whether or not the current page is the last page
					if(preg_match_all("/\[newpage.*?]/si", $row['content_text'], $matches)){
						$pages = preg_split("/\[newpage.*?]/si", $row['content_text'], -1, PREG_SPLIT_NO_EMPTY);
						$pages = array_values($pages);

						if(count($pages) == count($matches[0])){
						}elseif(count($pages) > count($matches[0])){
							$matches[0] = array_pad($matches[0], -count($pages), "[newpage]");
						}elseif(count($pages) < count($matches[0])){
						}

						$CONTENT_CONTENT_TABLE_TEXT = $pages[(!$qs[2] ? 0 : $qs[2]-1)];
						$options = "";
						for ($i=0; $i < count($pages); $i++) {
							if(!isset($qs[2])){ $idp = 1; }else{ $idp = $qs[2]; }
							if($idp == $i+1){ $pre = " - current"; }else{ $pre = ""; }
							if($matches[0][$i] == "[newpage]"){
								$pagename[$i] = CONTENT_LAN_78;
							}else{
								$arrpagename = explode("[newpage=", $matches[0][$i]);
								$pagename[$i] = substr($arrpagename[1],0,-1);
							}
							//0:normal links, 1:selectbox
							//$content_pref["content_content_pagenames_rendertype_{$mainparent}"] = "1";
							if(isset($content_pref["content_content_pagenames_rendertype_{$mainparent}"]) && $content_pref["content_content_pagenames_rendertype_{$mainparent}"] == "1"){
								$page = CONTENT_LAN_79." ".($i+1)." ".$pre." ".$pagename[$i];
								$url = e_SELF."?".$qs[0].".".$qs[1].".".($i+1);
								$options .= $rs -> form_option($page, ($idp == ($i+1) ? "1" : "0"), $url , "");
							}else{
								$options .= CONTENT_LAN_79." ".($i+1)." ".$pre." : <a href='".e_SELF."?".$qs[0].".".$qs[1].".".($i+1)."'>".$pagename[$i]."</a><br />";
							}

							if($idp==1){
								$CONTENT_CONTENT_TABLE_SUMMARY = (isset($content_pref["content_content_summary_{$mainparent}"]) && $content_pref["content_content_summary_{$mainparent}"] && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
								$CONTENT_CONTENT_TABLE_SUMMARY = $tp -> replaceConstants($CONTENT_CONTENT_TABLE_SUMMARY);
							}else{
								$CONTENT_CONTENT_TABLE_SUMMARY = "";
							}
							if($idp == count($pages)){
								$lastpage = TRUE;
							}
						}
						if($content_pref["content_content_pagenames_rendertype_{$mainparent}"] == "1"){
							$selectjs	= "onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\"";
							$CONTENT_CONTENT_TABLE_PAGENAMES = $rs -> form_select_open("pagenames", $selectjs).$rs -> form_option("select page", "1", "none" , "").$options.$rs -> form_select_close();
						}else{
							$CONTENT_CONTENT_TABLE_PAGENAMES = $options;
						}

					}else{
						$CONTENT_CONTENT_TABLE_SUMMARY	= (isset($content_pref["content_content_summary_{$mainparent}"]) && $content_pref["content_content_summary_{$mainparent}"] && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
						$CONTENT_CONTENT_TABLE_SUMMARY	= $tp -> replaceConstants($CONTENT_CONTENT_TABLE_SUMMARY);
						$lastpage = TRUE;
					}

					$CONTENT_CONTENT_TABLE_TEXT		= $tp -> replaceConstants($CONTENT_CONTENT_TABLE_TEXT);
					$CONTENT_CONTENT_TABLE_TEXT		= $tp -> toHTML($CONTENT_CONTENT_TABLE_TEXT, TRUE, "");
					$custom							= $eArrayStorage->ReadArray($row['content_pref']);

					$date	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_DATE}', FALSE, $content_shortcodes);
					$auth	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_AUTHORDETAILS}', FALSE, $content_shortcodes);
					$ep		= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_EPICONS}', FALSE, $content_shortcodes);
					$edit	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_EDITICON}', FALSE, $content_shortcodes);
					$par	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_PARENT}', FALSE, $content_shortcodes);
					$com	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_COMMENT}', FALSE, $content_shortcodes);
					$score	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_SCORE}', FALSE, $content_shortcodes);
					$ref	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_REFER}', FALSE, $content_shortcodes);
					$ico	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_ICON}', FALSE, $content_shortcodes);
					$sub	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_SUBHEADING}', FALSE, $content_shortcodes);
					$rat	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_RATING}', FALSE, $content_shortcodes);
					$fil	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_FILE}', FALSE, $content_shortcodes);

					if ($date!="" || $auth!="" || $ep!="" || $edit!="" || $par!="" || $com!="" || $score!="" || $ref!="" || $ico!="" || $sub!="" || $rat!="" || $fil!="") {
						$CONTENT_CONTENT_TABLE_INFO_PRE = TRUE;
						$CONTENT_CONTENT_TABLE_INFO_POST = TRUE;
					}

					$CONTENT_CONTENT_TABLE = "";
					if(!$CONTENT_CONTENT_TABLE){
						//if no theme has been set, use default theme
						if(!$content_pref["content_theme_{$mainparent}"]){

							//if custom layout is set
							if($row['content_layout']){
								//if custom layout file exists
								if(is_readable($plugindir."templates/default/".$row['content_layout'])){
									require_once($plugindir."templates/default/".$row['content_layout']);
								}else{
									require_once($plugindir."templates/default/content_content_template.php");
								}
							}else{
								require_once($plugindir."templates/default/content_content_template.php");
							}
						}else{
							//if custom layout is set
							if($row['content_layout']){
								//if custom layout file exists
								if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/".$row['content_layout'])){
									require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/".$row['content_layout']);
								}else{
									//if default layout from the set theme exists
									if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php")){
										require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php");
									//else use default theme, default layout
									}else{
										require_once($plugindir."templates/default/content_content_template.php");
									}
								}
							//if no custom layout is set
							}else{
								//if default layout from the set theme exists
								if(is_readable($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php")){
									require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php");
								//else use default theme, default layout
								}else{
									require_once($plugindir."templates/default/content_content_template.php");
								}
							}
						}
					}

					$months = array(CONTENT_ADMIN_DATE_LAN_0, CONTENT_ADMIN_DATE_LAN_1, CONTENT_ADMIN_DATE_LAN_2, CONTENT_ADMIN_DATE_LAN_3, CONTENT_ADMIN_DATE_LAN_4, CONTENT_ADMIN_DATE_LAN_5, CONTENT_ADMIN_DATE_LAN_6, CONTENT_ADMIN_DATE_LAN_7, CONTENT_ADMIN_DATE_LAN_8, CONTENT_ADMIN_DATE_LAN_9, CONTENT_ADMIN_DATE_LAN_10, CONTENT_ADMIN_DATE_LAN_11);

					$CONTENT_CONTENT_TABLE_CUSTOM_TAGS = "";
					$CUSTOM_TAGS = FALSE;
					if($lastpage === TRUE && !empty($custom)){
						$CONTENT_CONTENT_TABLE_CUSTOM_PRE = "";
						$CONTENT_CONTENT_TABLE_CUSTOM_TAGS = "";
						//ksort($custom);
						foreach($custom as $k => $v){
							if($k == "content_custom_presettags"){
								if(isset($content_pref["content_content_presettags_{$mainparent}"]) && $content_pref["content_content_presettags_{$mainparent}"]){
									foreach($v as $ck => $cv){
										if(is_array($cv)){	//date
											if(!($cv['day']=="" && $cv['month']=="" && $cv['year']=="")){
												$vv = $cv['day']." ".$months[($cv['month']-1)]." ".$cv['year'];
											}
										}else{
											$vv = $cv;
										}
										if( isset($ck) && $ck != "" && isset($vv) && $vv!="" ){
											$CUSTOM_TAGS = TRUE;
											$CONTENT_CONTENT_TABLE_CUSTOM_KEY		= $tp->toHTML($ck, true);
											$CONTENT_CONTENT_TABLE_CUSTOM_VALUE		= $tp->toHTML($vv, true);
											$CONTENT_CONTENT_TABLE_CUSTOM_TAGS		.= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENT_TABLE_CUSTOM);
										}
									}
								}
							}else{
								if(isset($content_pref["content_content_customtags_{$mainparent}"]) && $content_pref["content_content_customtags_{$mainparent}"]){
									$key = substr($k,15);
									if( isset($key) && $key != "" && isset($v) && $v!="" ){
										$CUSTOM_TAGS = TRUE;
										$CONTENT_CONTENT_TABLE_CUSTOM_KEY		= $tp->toHTML($key, true);
										$CONTENT_CONTENT_TABLE_CUSTOM_VALUE		= $tp->toHTML($v, true);
										$CONTENT_CONTENT_TABLE_CUSTOM_TAGS		.= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENT_TABLE_CUSTOM);
									}
								}
							}
						}
						if($CUSTOM_TAGS === TRUE){
							$CONTENT_CONTENT_TABLE_CUSTOM_TAGS = $CONTENT_CONTENT_TABLE_CUSTOM_START.$CONTENT_CONTENT_TABLE_CUSTOM_TAGS.$CONTENT_CONTENT_TABLE_CUSTOM_END;
						}
					}
					$text		= $tp -> parseTemplate($CONTENT_CONTENT_TABLE, FALSE, $content_shortcodes);
					$text		= $aa -> getCrumbPage("item", $array, $row['content_parent']).$text;
					$caption	= $row['content_heading'];
					$ns -> tablerender($caption, $text);

					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
				ob_end_flush(); // dump collected data
			}

			//recheck some thing when caching is enabled
			$pages = preg_split("/\[newpage.*?]/si", $row['content_text'], -1, PREG_SPLIT_NO_EMPTY);
			$pages = array_values($pages);
			$cachestr = "comment.$plugintable.$qs[1].$cacheid";
			if(count($pages) == 0){
				$lastpage = TRUE;
			}else{
				if($cacheid == count($pages)){
					$lastpage = TRUE;
				}
			}
			if($lastpage && ($row['content_comment'] || (isset($content_pref["content_content_comment_all_{$mainparent}"]) && $content_pref["content_content_comment_all_{$mainparent}"]))){
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();
						if((isset($content_pref["content_content_rating_{$mainparent}"]) && $content_pref["content_content_rating_{$mainparent}"] && $row['content_rate']) || (isset($content_pref["content_content_rating_all_{$mainparent}"]) && $content_pref["content_content_rating_all_{$mainparent}"]) ){
							$showrate = TRUE;
						}else{
							$showrate = FALSE;
						}
						$cobj->compose_comment($plugintable, "comment", $qs[1], $width, $row['content_heading'], $showrate);

						if($pref['cachestatus']){
							$cache = ob_get_contents();
							$e107cache->set($cachestr, $cache);
						}
					ob_end_flush(); // dump collected data
				}
			}
		} //close sql
}

require_once(FOOTERF);

?>