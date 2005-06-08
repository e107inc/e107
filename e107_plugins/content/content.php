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
|		$Revision: 1.49 $
|		$Date: 2005-06-08 16:52:37 $
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

//include lan file
$lan_file = $plugindir."languages/".e_LANGUAGE."/lan_content.php";
include_once(file_exists($lan_file) ? $lan_file : $plugindir."languages/English/lan_content.php");

// check query
if(e_QUERY){
	$qs = explode(".", e_QUERY);

	if(is_numeric($qs[0])){
		$from = array_shift($qs);
	}else{
		$from = "0";
	}
}

// define e_pagetitle
$aa -> setPageTitle();

require_once(HEADERF);

//core_head function to prepare the meta tags
function core_head(){
	global $qs, $sql, $tp, $pref, $plugintable, $eArrayStorage;

	if($qs[0] == "content" && isset($qs[1]) && is_numeric($qs[1]) && isset($qs[2]) && is_numeric($qs[2])){
		if($sql -> db_Select($plugintable, "content_pref as custom", "content_id='".$qs[2]."'")){
			list($custom) = $sql -> db_Fetch();

			$custom = $eArrayStorage->ReadArray($custom);
			$custom_meta = $custom['content_custom_meta'];
			if($custom_meta != ""){
				$custom_meta = str_replace(", ", ",", $custom_meta);
				$custom_meta = str_replace(" ", ",", $custom_meta);
				$custom_meta = str_replace(",", ", ", $custom_meta);
				$newmeta = "";
				$checkmeta = preg_match_all("/\<meta name=&#039;(.*?)&#039; content=&#039;(.*?)&#039;>/si", $pref['meta_tag'], $matches);

				if(empty($matches[1])){
					$newmeta = "<meta name=&#039;keywords&#039; content=&#039;".$custom_meta."&#039; />";
				}else{
					for($i=0;$i<count($matches[1]);$i++){
						if($matches[1][$i] == "keywords"){
							$newmeta .= "<meta name=&#039;".$matches[1][$i]."&#039; content=&#039;".$matches[2][$i].", ".$custom_meta."&#039; />";
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
	echo "<script type='text/javascript' src='".e_PLUGIN."content/content.js'></script>\n";
}

//post comment
if(isset($_POST['commentsubmit'])){
	if(!is_object($sql)){ $sql = new db; }
	if(!$sql -> db_Select($plugintable, "content_comment", "content_id='".$qs[0]."' ")){
		header("location:".e_BASE."index.php"); exit;
	}else{
		$row = $sql -> db_Fetch();
		if(ANON === TRUE || USER === TRUE){
			//enter_comment($author_name, $comment, $table, $id, $pid, $subject)
			$pid = "0";
			$cobj -> enter_comment(USERNAME, $_POST['comment'], $plugintable, $qs[0], $pid, $_POST['subject']);
			$e107cache->clear("comment.{$plugintable}.{$qs[0]}");
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
}

// ##### REDIRECTION MANAGEMENT -------------------------------------------------------
//parent overview
if(!e_QUERY){
	show_content();
}else{
	//show search results
	$checkmainid		= (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
	$checkmainparent	= $aa -> getMainParent($checkmainid);
	$content_pref		= $aa -> getContentPref($checkmainparent);
	if($content_pref["content_searchmenu_{$checkmainparent}"]){ show_content_search_menu(); }
	if($resultmenu == TRUE){ show_content_search_result($searchkeyword); }

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
	}elseif( $qs[0] == "cat" && is_numeric($qs[1]) && (!isset($qs[2]) || $qs[2] == "comment") ){
		
		if( isset($qs[2]) && $qs[2] == "comment" ){
			show_content_cat("comment");
		}else{
			show_content_cat();
		}

	//top rated of parent='2'
	}elseif( $qs[0] == "top" && is_numeric($qs[1]) && !isset($qs[2]) ){
		show_content_top();

	//authorlist of parent='2'
	}elseif( $qs[0] == "author" && $qs[1] == "list" && is_numeric($qs[2]) && ( !isset($qs[3]) || substr($qs[3],0,5) == "order" ) ){
		show_content_author_all();

	//authorlist of parent='2' and content_id='5'
	}elseif( $qs[0] == "author" && is_numeric($qs[1]) && !isset($qs[2]) ){
		show_content_author();

	//archive of parent='2'
	}elseif( $qs[0] == "list" && is_numeric($qs[1]) && ( !isset($qs[2]) || substr($qs[2],0,5) == "order" ) ){
		show_content_archive();
	}else{
		//header("location:".e_SELF); exit;
	}
}
// ##### ------------------------------------------------------------------------------

// ##### CONTENT SEARCH MENU ----------------------------
function show_content_search_menu(){
				global $qs, $plugindir, $content_shortcodes, $tp, $ns, $rs, $aa;
				global $plugintable, $gen, $content_pref;
				global $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD;

				$thismain	= $aa -> getMainParent( (is_numeric($qs[1]) ? $qs[1] : $qs[2]) );
				$CONTENT_SEARCH_TABLE = "";
				if(!$CONTENT_SEARCH_TABLE){
					if(!$content_pref["content_theme_{$thismain}"]){
						require_once($plugindir."templates/default/content_search_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$thismain}"]."/content_search_template.php")){
							require_once($plugindir."templates/".$content_pref["content_theme_{$thismain}"]."/content_search_template.php");
						}else{
							require_once($plugindir."templates/default/content_search_template.php");
						}
					}
				}

				$CONTENT_SEARCH_TABLE_KEYWORD = $aa -> showOptionsSearch("page", $thismain);
				$CONTENT_SEARCH_TABLE_SELECT = $aa -> showOptionsSelect("page", $thismain);
				$CONTENT_SEARCH_TABLE_ORDER = $aa -> showOptionsOrder("page", $thismain);

				$text = $tp -> parseTemplate($CONTENT_SEARCH_TABLE, FALSE, $content_shortcodes);
				
				if($content_pref["content_searchmenu_rendertype_{$thismain}"] == "2"){
					$caption = CONTENT_LAN_77;
					$ns -> tablerender($caption, $text);
				}else{
					echo $text;
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
				$content_icon_path	= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);

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
							if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_searchresult_template.php")){
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
							$content_cat_icon_path_large	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$row['content_id']}"]);
							$content_cat_icon_path_small	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$row['content_id']}"]);
							$content_icon_path				= $aa -> parseContentPathVars($content_pref["content_icon_path_{$row['content_id']}"]);

							$array			= $aa -> getCategoryTree("", $row['content_id'], TRUE);
							$validparent	= implode(",", array_keys($array));
							$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
							$contenttotal	= $sql2 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."'" );

							$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE, FALSE, $content_shortcodes);
						}

						$SUBMIT_LINE = FALSE;
						$count = "0";
						$sql3 = "";
						if(!is_object($sql3)){ $sql3 = new db; }
						if($sql3 -> db_Select($plugintable, "content_id, content_pref", "content_parent = '0' ".$datequery." ORDER BY content_parent")){
							while($row = $sql3 -> db_Fetch()){
								if(isset($row['content_pref'])){
									$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
								}
								if($content_pref["content_submit_{$row['content_id']}"] && check_class($content_pref["content_submit_class_{$row['content_id']}"])){
									$count = $count + 1;
								}
							}
							if($count > "0"){
								$content_type_table_string .= $CONTENT_TYPE_TABLE_LINE;
								$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE_SUBMIT, FALSE, $content_shortcodes);
								$SUBMIT_LINE = TRUE;
							}
						}

						if(USERID){
							$personalmanagercheck = FALSE;

							$array = $aa -> getCategoryTree("", "", TRUE);
							$catarray = array_keys($array);
							foreach($catarray as $catid){
								if($sql -> db_Select($plugintable, "content_id, content_heading, content_pref", " content_id='".$catid."' ")){
									$row = $sql -> db_Fetch();

									if(isset($row['content_pref'])){
										$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
									}

									//assign new preferences
									if(getperms("0") ){
										$personalmanagercheck = TRUE;
									}
									if(isset($content_pref['content_manager_allowed']) ){
										$pcm = explode(",", $content_pref['content_manager_allowed']);
										if(in_array(USERID, $pcm)){
											$personalmanagercheck = TRUE;
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
//show archive list of all content items in a main parent
function show_content_archive(){
				global $ns, $plugindir, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $nextprevquery, $from, $number, $mainparent;
				global $CONTENT_ARCHIVE_TABLE, $datequery, $prefetchbreadcrumb, $unvalidcontent;
				global $qs;

				$mainparent		= $aa -> getMainParent($qs[1]);
				$content_pref	= $aa -> getContentPref($mainparent);
				$CONTENT_ARCHIVE_TABLE = "";
				if(!$CONTENT_ARCHIVE_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_archive_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_archive_template.php")){
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
					
					$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
					$number			= ($content_pref["content_archive_nextprev_number_{$mainparent}"] ? $content_pref["content_archive_nextprev_number_{$mainparent}"] : "30");
					$order			= $aa -> getOrder();
					$nextprevquery	= ($content_pref["content_archive_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");

					$sql1 = new db;
					$contenttotal = $sql1 -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ");
					if($from > $contenttotal-1){ header("location:".e_SELF); exit; }

					if($item = $sql1 -> db_Select($plugintable, "*", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){
						
						$content_archive_table_string = "";
						while($row = $sql1 -> db_Fetch()){
							$content_archive_table_string .= $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE, FALSE, $content_shortcodes);
						}
						$text = $CONTENT_ARCHIVE_TABLE_START.$content_archive_table_string.$CONTENT_ARCHIVE_TABLE_END;
					}

					if($content_pref["content_breadcrumb_{$mainparent}"]){
						$crumbpage = $aa -> getCrumbPage($array, $mainparent);
						if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
							echo $crumbpage;					
						}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
							$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
						}else{
							$text = $crumbpage.$text;
						}
					}

					$caption = CONTENT_LAN_84;
					$ns->tablerender($caption, $text);

					if($content_pref["content_archive_nextprev_{$mainparent}"]){
						require_once(e_HANDLER."np_class.php");
						$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
						$ix = new nextprev(e_SELF, $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
					}
					
					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data 			
				}
}

// ##### RECENT LIST ------------------------------------
function show_content_recent(){
				global $qs, $plugindir, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj;
				global $nextprevquery, $from, $number, $mainparent;
				global $CONTENT_RECENT_TABLE, $datequery, $content_icon_path;

				$mainparent		= $aa -> getMainParent($qs[1]);
				$content_pref	= $aa -> getContentPref($mainparent);
				if(!$CONTENT_RECENT_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_recent_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php")){
							require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php");
						}else{
							require_once($plugindir."templates/default/content_recent_template.php");
						}
					}
				}

				$cachestr = "$plugintable.recent.$qs[1]";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					$content_icon_path	= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
					$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
					$validparent		= implode(",", array_keys($array));
					$order				= $aa -> getOrder();
					$number				= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery		= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
					$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

					$sql1 = new db;
					$contenttotal = $sql1 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " );

					if($from > $contenttotal-1){ header("location:".e_SELF); exit; }

					if($resultitem = $sql1 -> db_Select($plugintable, "*", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){

						$content_recent_table_string = "";
						while($row = $sql1 -> db_Fetch()){
							$crumb = $aa -> getCrumbItem($row['content_parent'], $array);
							$content_recent_table_string .= $tp -> parseTemplate($CONTENT_RECENT_TABLE, FALSE, $content_shortcodes);
						}
					}
					$text = $CONTENT_RECENT_TABLE_START.$content_recent_table_string.$CONTENT_RECENT_TABLE_END;

					if($content_pref["content_breadcrumb_{$mainparent}"]){
						$crumbpage = $aa -> getCrumbPage($array, $mainparent);
						if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
							echo $crumbpage;
						}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
							$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
						}else{
							$text = $crumbpage.$text;
						}
					}

					$caption = CONTENT_LAN_23;
					$ns -> tablerender($caption, $text);
					if($content_pref["content_nextprev_{$mainparent}"]){
						require_once(e_HANDLER."np_class.php");
						$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
						$ix = new nextprev(e_SELF, $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
					}

					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data
				}
}
// ##### --------------------------------------------------



// ##### CATEGORY LIST ------------------------------------
function show_content_cat_all(){
				global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $aa, $e107cache, $tp, $pref, $content_pref, $keytocount, $CONTENT_CAT_TABLE, $CONTENT_CAT_TABLE_INFO_PRE, $CONTENT_CAT_TABLE_INFO_POST;
				global $sql, $datequery, $crumb, $amount, $from, $content_cat_icon_path_large, $content_icon_path, $n, $CONTENT_CAT_TABLE_HEADING, $mainparent;

				unset($text);

				$mainparent		= $aa -> getMainParent($qs[2]);
				$content_pref	= $aa -> getContentPref($mainparent);
				if(!$CONTENT_CAT_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_cat_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php")){
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

					$content_cat_icon_path_large	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$mainparent}"]);
					$content_icon_path				= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
					$array							= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent					= implode(",", array_keys($array));
					$order							= $aa -> getOrder();
					$number							= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery					= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
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
						$keytocount = $key;
						$sql -> db_Select($plugintable, "*", "content_id = '".$key."' ");
						$row = $sql -> db_Fetch();
						
						$date	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_DATE}');
						$auth	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_AUTHORDETAILS}');
						$ep		= $tp -> parseTemplate('{CONTENT_CAT_TABLE_EPICONS}');
						$com	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_COMMENT}');
						$CONTENT_CAT_TABLE_INFO_PRE = FALSE;
						$CONTENT_CAT_TABLE_INFO_POST = FALSE;
						if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
							$CONTENT_CAT_TABLE_INFO_PRE = TRUE;
							$CONTENT_CAT_TABLE_INFO_POST = TRUE;
						}

						$content_cat_table_string .= $tp -> parseTemplate($CONTENT_CAT_TABLE, FALSE, $content_shortcodes);

					}
					$text = $CONTENT_CAT_TABLE_START.$content_cat_table_string.$CONTENT_CAT_TABLE_END;

					if($content_pref["content_breadcrumb_{$mainparent}"]){
						$crumbpage = $aa -> getCrumbPage($array, $mainparent);
						if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
							echo $crumbpage;
						}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
							$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
						}else{
							$text = $crumbpage.$text;
						}
					}
					$caption = CONTENT_LAN_25;
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
				global $CONTENT_RECENT_TABLE, $CONTENT_CAT_LIST_TABLE, $CONTENT_CAT_LISTSUB_TABLE_START, $CONTENT_CAT_LISTSUB_TABLE, $CONTENT_CAT_LISTSUB_TABLE_END, $CONTENT_CAT_LIST_TABLE_INFO_PRE, $CONTENT_CAT_LIST_TABLE_INFO_POST;
				global $content_cat_icon_path_small, $content_cat_icon_path_large, $content_icon_path, $mainparent;

				$mainparent		= $aa -> getMainParent($qs[1]);
				$content_pref	= $aa -> getContentPref($mainparent);
				if(!$CONTENT_CAT_LIST_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_cat_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php")){
							require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php");
						}else{
							require_once($plugindir."templates/default/content_cat_template.php");
						}
					}
				}

				if(!$CONTENT_RECENT_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_recent_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php")){
							require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php");
						}else{
							require_once($plugindir."templates/default/content_recent_template.php");
						}
					}
				}

				if($mode == "comment"){
					$cachestr = "$plugintable.cat.$qs[1].comment";
				}else{
					$cachestr = "$plugintable.cat.$qs[1]";
				}
				if($cache = $e107cache->retrieve($cachestr)){
					if(!$CONTENT_CAT_LIST_TABLE){
						if(!$content_pref["content_theme_{$mainparent}"]){
							require_once($plugindir."templates/default/content_cat_template.php");
						}else{
							if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php")){
								require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_cat_template.php");
							}else{
								require_once($plugindir."templates/default/content_cat_template.php");
							}
						}
					}
					echo $cache;
				}else{
					ob_start();

					$content_cat_icon_path_large	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$mainparent}"]);
					$content_cat_icon_path_small	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$mainparent}"]);
					$content_icon_path				= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
					$array							= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent					= "0,0.".implode(",0.", array_keys($array));
					$order							= $aa -> getOrder();
					$number							= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery					= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
					$qry							= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

					// parent article
					if($content_pref["content_cat_showparent_{$mainparent}"]){
						if(!$resultitem = $sql -> db_Select($plugintable, "*", "content_id = '".$qs[1]."' AND content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " )){
							header("location:".e_SELF."?cat.list.".$mainparent); exit;
						}else{
							$row = $sql -> db_Fetch();

							$date = $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_DATE}');
							$auth = $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_AUTHORDETAILS}');
							$ep = $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_EPICONS}');
							$com = $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_COMMENT}');
							if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
								$CONTENT_CAT_LIST_TABLE_INFO_PRE = TRUE;
								$CONTENT_CAT_LIST_TABLE_INFO_POST = TRUE;
							}

							$textparent			= $tp -> parseTemplate($CONTENT_CAT_LIST_TABLE, FALSE, $content_shortcodes);
							$captionparent		= CONTENT_LAN_26." : ".$row['content_heading'];
						}
					}

					if(!$mode || $mode == ""){

						//list subcategories
						if($content_pref["content_cat_showparentsub_{$mainparent}"]){
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
							$subqry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validsub)."' ";

							$content_cat_listsub_table_string = "";
							for($i=0;$i<count($subparent);$i++){
								if($resultitem = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_icon, content_parent", "content_refer !='sa' AND content_id = '".$subparent[$i]."' AND ".$subqry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " )){
									while($row = $sql -> db_Fetch()){
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
						if($content_pref["content_cat_listtype_{$mainparent}"]){
							$validitem		= implode(",", $subparent);
							$qrycat			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validitem)."' ";
						}else{
							$qrycat			= " content_parent = '".$qs[1]."' ";
						}

						$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND ".$qrycat." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ");

						$textchild = "";
						$sql1 = new db;
						if($resultitem = $sql1 -> db_Select($plugintable, "*", "content_refer !='sa' AND ".$qrycat." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){

							$content_recent_table_string = "";
							while($row = $sql1 -> db_Fetch()){
								$content_recent_table_string .= $tp -> parseTemplate($CONTENT_RECENT_TABLE, FALSE, $content_shortcodes);
							}
							$textchild		= $CONTENT_RECENT_TABLE_START.$content_recent_table_string.$CONTENT_RECENT_TABLE_END;
							$captionchild	= "contents";
						}

						if($content_pref["content_nextprev_{$mainparent}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
						}

						if($content_pref["content_breadcrumb_{$mainparent}"]){
							$crumbpage = $aa -> getCrumbPage($array, $qs[1]);
							if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
								echo $crumbpage;
							}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
								$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
							}else{
								if(isset($textparent)){ 
									$textparent = $crumbpage.$textparent;
								}else{
									$textchild = $crumbpage.$textparent;
								}
							}
						}

						if($content_pref["content_cat_menuorder_{$mainparent}"] == "1"){
							if($content_pref["content_cat_rendertype_{$mainparent}"] == "1"){
								if(isset($textparent)){		$ns -> tablerender($captionparent, $textparent); }
								if(isset($textsubparent)){	$ns -> tablerender($captionsubparent, $textsubparent); }
								if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
							}else{
								//$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? "<br /><br />".$textsubparent : "")."<br /><br />".$textchild);
								$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? $textsubparent : "").$textchild);
							}
							if($content_pref["content_nextprev_{$mainparent}"]){
								$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
							}
						}else{
							if($content_pref["content_cat_rendertype_{$mainparent}"] == "1"){
								if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
								if($content_pref["content_nextprev_{$mainparent}"]){
									$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
								}
								if(isset($textparent)){		$ns -> tablerender($captionparent, $textparent); }
								if(isset($textsubparent)){	$ns -> tablerender($captionsubparent, $textsubparent); }
							}else{
								if(isset($textchild)){		$ns -> tablerender($captionchild, $textchild); }
								if($content_pref["content_nextprev_{$mainparent}"]){
									$ix = new nextprev("content.php", $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
								}
								//$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? "<br /><br />".$textsubparent : ""));
								$ns -> tablerender($captionparent, $textparent.(isset($textsubparent) ? $textsubparent : ""));
							}
						}

					}elseif($mode == "comment"){

						if($content_pref["content_breadcrumb_{$mainparent}"]){
							$crumbpage = $aa -> getCrumbPage($array, $mainparent);
							if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
								echo $crumbpage;
							}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
								$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
							}else{
								$textparent = $crumbpage.$textparent;
							}
						}

						if(isset($textparent)){ $ns -> tablerender($captionparent, $textparent); }

						if($row['content_comment']){
							if($cache = $e107cache->retrieve("comment.$plugintable.$qs[1]")){
								echo $cache;
							}else{
								ob_start();
								unset($text);
								if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$qs[1]."' AND comment_type='".$plugintable."' AND comment_pid='0' ORDER BY comment_datestamp")){
									$width = 0;
									while($row2 = $sql -> db_Fetch()){
										if($pref['nested_comments']){
											$text = $cobj -> render_comment($row2, $plugintable , "comment", $qs[1], $width, $row['content_heading']);
											$ns -> tablerender(CONTENT_LAN_35, $text);
										}else{
											$text .= $cobj -> render_comment($row2, $plugintable , "comment", $qs[1], $width, $row['content_heading']);
										}
									}
									if(!$pref['nested_comments']){$ns -> tablerender(CONTENT_LAN_35, $text); }
									if($pref['cachestatus']){
										$cache = ob_get_contents();
										$e107cache->set("comment.$plugintable.$qs[1]", $cache);
									}
								}
								ob_end_flush(); /* dump collected data */
							}
							if(ADMIN && getperms("B")){
								echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$plugintable.$qs[1]'>".CONTENT_LAN_36."</a></div><br />";
							}
							$cobj -> form_comment("comment", $plugintable, $qs[1], $row['content_heading']);
						}
					}

					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data
				}
}
// ##### --------------------------------------------------









// ##### AUTHOR LIST --------------------------------------
function show_content_author_all(){
				global $qs, $plugindir, $ix, $content_shortcodes, $ns, $plugintable, $from, $sql, $aa, $e107cache, $tp, $pref, $mainparent, $content_pref, $cobj, $datequery;
				$mainparent		= $aa -> getMainParent($qs[2]);
				$content_pref	= $aa -> getContentPref($mainparent);
				$CONTENT_AUTHOR_TABLE = "";
				if(!$CONTENT_AUTHOR_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_author_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_author_template.php")){
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
					$number			= ($content_pref["content_author_nextprev_number_{$mainparent}"] ? $content_pref["content_author_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery	= ($content_pref["content_author_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
					$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

					$sql1 = new db;
					$contenttotal = $sql1 -> db_Select($plugintable, "DISTINCT(content_author)", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."'");					
					if(!$result = $sql1 -> db_Select($plugintable, "DISTINCT(content_author)", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ORDER BY content_author ".$nextprevquery )){
						$text = "<div style='text-align:center;'>".CONTENT_LAN_52."</div>";
					}else{
						while($row = $sql1 -> db_Fetch()){
							$authordetails[] = $aa -> getAuthor($row['content_author']);
						}
						//sort by authorname ascending (default or $qs[3] == "orderaauthor")
						usort($authordetails, create_function('$a,$b','return strcasecmp ($a[1], $b[1]);')); 
						//sort by authorname descending ($qs[3] == "orderdauthor")
						if(isset($qs[3]) && $qs[3] == "orderdauthor"){
							$authordetails = array_reverse($authordetails);
						}
						$sql2 = "";
						$content_author_table_string = "";
						for($i=0;$i<count($authordetails);$i++){
							if(!is_object($sql2)){ $sql2 = new db; }
							$totalcontent = $sql2 -> db_Select($plugintable, "content_id, content_heading, content_datestamp", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND content_author = '".$authordetails[$i][3]."' ORDER BY content_datestamp DESC");
							list($row['content_id'], $row['content_heading'], $row['content_datestamp']) = $sql2 -> db_Fetch();

							$content_author_table_string .= $tp -> parseTemplate($CONTENT_AUTHOR_TABLE, FALSE, $content_shortcodes);
						}
						$text = $CONTENT_AUTHOR_TABLE_START.$content_author_table_string.$CONTENT_AUTHOR_TABLE_END;

						if($content_pref["content_breadcrumb_{$mainparent}"]){
							$crumbpage = $aa -> getCrumbPage($array, $mainparent);
							if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
								echo $crumbpage;
							}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
								$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
							}else{
								$text = $crumbpage.$text;
							}
						}
					}
					$caption = CONTENT_LAN_32;
					$ns -> tablerender($caption, $text);

					if($content_pref["content_author_nextprev_{$mainparent}"]){
						require_once(e_HANDLER."np_class.php");
						$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
						$ix = new nextprev(e_SELF, $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
					}

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
				$CONTENT_RECENT_TABLE = "";
				if(!$CONTENT_RECENT_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_recent_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php")){
							require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_recent_template.php");
						}else{
							require_once($plugindir."templates/default/content_recent_template.php");
						}
					}
				}

				$cachestr = "$plugintable.author.$qs[1]";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					$content_icon_path	= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
					$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
					if(array_key_exists($qs[1], $array)){
						$validparent	= "0,0.".implode(",0.", array_keys($array));
					}else{
						$validparent	= implode(",", array_keys($array));
					}
					$order				= $aa -> getOrder();
					$number				= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery		= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");
					$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

					$sqla = "";
					if(!is_object($sqla)){ $sqla = new db; }
					if(!$author = $sqla -> db_Select($plugintable, "content_author", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_id = '".$qs[1]."' AND content_class REGEXP '".e_CLASS_REGEXP."' ")){
						header("location:".e_SELF."?author.list.".$mainparent); exit;
					}else{
						list($content_author)	= $sqla -> db_Fetch();
						$sqlb = new db;
						$authordetails			= $aa -> getAuthor($content_author);						
						$query					= " content_author = '".$authordetails[3]."' || content_author REGEXP '^".$authordetails[1]."^' ".(is_numeric($content_author) ? " || content_author = '".$authordetails[0]."' " : "")." ";
						$validparent			= implode(",", array_keys($array));
						$qry					= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
						$contenttotal			= $sqlb -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND ".$qry." AND (".$query.") ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ");

						if($result = $sqlb -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_author, content_icon, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_pref as contentprefvalue", "content_refer !='sa' AND ".$qry." AND (".$query.") ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){

							$content_recent_table_string = "";
							while($row = $sqlb -> db_Fetch()){
								$content_recent_table_string	.= $tp -> parseTemplate($CONTENT_RECENT_TABLE, FALSE, $content_shortcodes);
							}
							$text = $CONTENT_RECENT_TABLE_START.$content_recent_table_string.$CONTENT_RECENT_TABLE_END;
						}

						if($content_pref["content_breadcrumb_{$mainparent}"]){
							$crumbpage = $aa -> getCrumbPage($array, $mainparent);
							if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
								echo $crumbpage;
							}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
								$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
							}else{
								$text = $crumbpage.$text;
							}
						}

						$caption = CONTENT_LAN_32." : ".$authordetails[1];
						$ns -> tablerender($caption, $text);

						if($content_pref["content_nextprev_{$mainparent}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
							$ix = new nextprev(e_SELF, $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
						}
					}
					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data
				}
}
// ##### --------------------------------------------------

// ##### TOP RATED LIST -----------------------------------
function show_content_top(){
				global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $cobj, $content_icon_path;
				global $from, $datequery, $content_pref, $mainparent;

				$mainparent		= $aa -> getMainParent($qs[1]);
				$content_pref	= $aa -> getContentPref($mainparent);
				$CONTENT_TOP_TABLE = "";
				if(!$CONTENT_TOP_TABLE){
					if(!$content_pref["content_theme_{$mainparent}"]){
						require_once($plugindir."templates/default/content_top_template.php");
					}else{
						if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_top_template.php")){
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

					$content_icon_path	= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
					$array				= $aa -> getCategoryTree("", $qs[1], TRUE);
					$validparent		= implode(",", array_keys($array));
					$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
					$order				= $aa -> getOrder();
					$number				= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
					$nextprevquery		= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");

					if(!is_object($sql)){ $sql = new db; }

					if(!$sql -> db_Select("rate", "*", "rate_table='".$plugintable."' ORDER BY rate_itemid " )){
						$text		= "<div style='text-align:center;'>".CONTENT_LAN_37."</div>";
						$caption	= CONTENT_LAN_38;
						$ns -> tablerender($caption, $text);
						require_once(FOOTERF);
						exit;
					}else{

						$sql2 = new db;
						while($row = $sql -> db_Fetch()){
							$tmp		= $row['rate_rating'] / $row['rate_votes'];
							$tmp		= explode(".", $tmp);
							$rating[1]	= $tmp[0];										// $ratomg[1] = main result
							$rating[2]	= (!empty($tmp[1]) ? substr($tmp[1],0,1) : "");	// $rating[2] = remainder
							$rate_avg	= $rating[1].".".($rating[2] ? $rating[2] : "0");	// rate average
							if($sql2 -> db_Select($plugintable, "content_id, content_heading, content_author, content_icon", "content_id='".$row['rate_itemid']."' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."'")){
								$rate_array[] = array($row['rate_itemid'], $row['rate_rating'], $row['rate_votes'], $rate_avg, $rating[1], $rating[2]);
							}
						}
						if(empty($rate_array)){
							$text		= "<div style='text-align:center;'>".CONTENT_LAN_37."</div>";
							$caption	= CONTENT_LAN_38;
							$ns -> tablerender($caption, $text);
							require_once(FOOTERF);
							exit;
						}
						usort($rate_array, create_function('$a,$b','return $a[3]==$b[3]?0:($a[3]>$b[3]?-1:1);')); 
						$contenttotal = count($rate_array);
						$content_top_table_string = "";

						for($i=$from;$i<$from+$number;$i++){
							if(isset($rate_array[$i])){
								if($sql2 -> db_Select($plugintable, "content_id, content_heading, content_author, content_icon", "content_id='".$rate_array[$i][0]."' " )){
									while($row = $sql2 -> db_Fetch()){
										$thisratearray				= $rate_array[$i];
										$content_top_table_string	.= $tp -> parseTemplate($CONTENT_TOP_TABLE, FALSE, $content_shortcodes);
									}
								}
							}
						}

						if($content_pref["content_breadcrumb_{$mainparent}"]){
							$crumbpage = $aa -> getCrumbPage($array, $mainparent);
							if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
									echo $crumbpage;
							}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
									$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
							}else{
									$text = $crumbpage.$text;
							}
						}
						$text		= $CONTENT_TOP_TABLE_START.$content_top_table_string.$CONTENT_TOP_TABLE_END;
						$caption	= CONTENT_LAN_38;
						$ns -> tablerender($caption, $text);

						if($content_pref["content_nextprev_{$mainparent}"]){
							require_once(e_HANDLER."np_class.php");
							$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
							$ix = new nextprev(e_SELF, $from, $number, $contenttotal, CONTENT_LAN_33, ($np_querystring ? $np_querystring : ""));
						}
					}
					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data
				}
}
// ##### --------------------------------------------------


// ##### CONTENT ITEM ------------------------------------------
function show_content_item(){
				global $pref, $content_pref;
				global $CONTENT_CONTENT_TABLE_TEXT, $CONTENT_CONTENT_TABLE_PAGENAMES, $CONTENT_CONTENT_TABLE_SUMMARY, $CONTENT_CONTENT_TABLE_CUSTOM_TAGS, $CONTENT_CONTENT_TABLE_PARENT, $CONTENT_CONTENT_TABLE_INFO_PRE, $CONTENT_CONTENT_TABLE_INFO_POST;
				global $content_icon_path, $content_image_path, $content_file_path, $custom;
				global $plugindir, $plugintable, $content_shortcodes, $datequery, $order, $nextprevquery, $from, $number;
				global $qs, $gen, $sql, $aa, $tp, $rs, $cobj, $e107, $e107cache, $eArrayStorage, $ns, $rater, $ep, $row, $authordetails, $mainparent; 

				$cachestr = "$plugintable.content.$qs[1]";
				if($cache = $e107cache->retrieve($cachestr)){
					echo $cache;
				}else{
					ob_start();

					$mainparent			= $aa -> getMainParent($qs[1]);
					$content_pref		= $aa -> getContentPref($mainparent);
					$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent		= implode(",", array_keys($array));
					$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

					if(!$resultitem = $sql -> db_Select($plugintable, "*", "content_id='".$qs[1]."' AND content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ")){
						header("location:".e_SELF."?recent.".$mainparent); exit;
					}else{
						$row = $sql -> db_Fetch();

						$content_pref["content_cat_icon_path_large_{$mainparent}"] = ($content_pref["content_cat_icon_path_large_{$mainparent}"] ? $content_pref["content_cat_icon_path_large_{$mainparent}"] : "{e_PLUGIN}content/images/cat/48/" );
						$content_pref["content_cat_icon_path_small_{$mainparent}"] = ($content_pref["content_cat_icon_path_small_{$mainparent}"] ? $content_pref["content_cat_icon_path_small_{$mainparent}"] : "{e_PLUGIN}content/images/cat/16/" );
						$content_pref["content_icon_path_{$mainparent}"] = ($content_pref["content_icon_path_{$mainparent}"] ? $content_pref["content_icon_path_{$mainparent}"] : "{e_PLUGIN}content/images/icon/" );
						$content_pref["content_image_path_{$mainparent}"] = ($content_pref["content_image_path_{$mainparent}"] ? $content_pref["content_image_path_{$mainparent}"] : "{e_PLUGIN}content/images/image/" );
						$content_pref["content_file_path_{$mainparent}"] = ($content_pref["content_file_path_{$mainparent}"] ? $content_pref["content_file_path_{$mainparent}"] : "{e_PLUGIN}content/images/file/" );
						$content_cat_icon_path_large	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$mainparent}"]);
						$content_cat_icon_path_small	= $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_icon_path				= $aa -> parseContentPathVars($content_pref["content_icon_path_{$mainparent}"]);
						$content_image_path				= $aa -> parseContentPathVars($content_pref["content_image_path_{$mainparent}"]);
						$content_file_path				= $aa -> parseContentPathVars($content_pref["content_file_path_{$mainparent}"]);
						$number							= ($content_pref["content_nextprev_number_{$mainparent}"] ? $content_pref["content_nextprev_number_{$mainparent}"] : "5");
						$nextprevquery					= ($content_pref["content_nextprev_{$mainparent}"] ? "LIMIT ".$from.",".$number : "");

						if($content_pref["content_log_{$mainparent}"]){
							$ip			= $e107->getip();
							$self		= e_SELF;
							$refertmp	= explode("^", $row['content_refer']);
							if(!ereg($ip, $refertmp[1]) && (!eregi("admin", $self))){
								$referiplist		= ($refertmp[1] ? $refertmp[1]."-".$ip."-" : $ip."-" );
								$contentrefernew	= ($refertmp[0]+1)."^".$referiplist;
								$sql = new db;
								$sql -> db_Update($plugintable, "content_refer='".$contentrefernew."' WHERE content_id='".$qs[1]."' ");
							}
						}

						$date	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_DATE}');
						$auth	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_AUTHORDETAILS}');
						$ep		= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_EPICONS}');
						$edit	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_EDITICON}');
						$par	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_PARENT}');
						$com	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_COMMENT}');
						$score	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_SCORE}');
						$ref	= $tp -> parseTemplate('{CONTENT_CONTENT_TABLE_REFER}');
						if ($date!="" || $auth!="" || $ep!="" || $edit!="" || $par!="" || $com!="" || $score!="" || $ref!="") {
							$CONTENT_CONTENT_TABLE_INFO_PRE = TRUE;
							$CONTENT_CONTENT_TABLE_INFO_POST = TRUE;
						}

						$CONTENT_CONTENT_TABLE_TEXT = $row['content_text'];

						if(preg_match_all("/\[newpage.*?]/si", $row['content_text'], $matches)){

							$pages = preg_split("/\[newpage.*?]/si", $row['content_text'], -1, PREG_SPLIT_NO_EMPTY);
							$pages = array_values($pages);

							if(count($pages) == count($matches[0])){
							}elseif(count($pages) > count($matches[0])){
								$matches[0] = array_pad($matches[0], -count($pages), "[newpage]");
							}elseif(count($pages) < count($matches[0])){
							}

							$CONTENT_CONTENT_TABLE_TEXT = $pages[(!$qs[2] ? 0 : $qs[2]-1)];
							for ($i=0; $i < count($pages); $i++) {
								if(!isset($qs[3])){ $idp = 1; }else{ $idp = $qs[2]; }
								if($idp == $i+1){ $pre = " - current"; }else{ $pre = ""; }
								if($matches[0][$i] == "[newpage]"){
									$pagename[$i] = CONTENT_LAN_78;
								}else{
									$arrpagename = explode("[newpage=", $matches[0][$i]);
									$pagename[$i] = substr($arrpagename[1],0,-1);
								}
								$CONTENT_CONTENT_TABLE_PAGENAMES .= CONTENT_LAN_79." ".($i+1)." ".$pre." : <a href='".e_SELF."?".$qs[0].".".$qs[1].".".($i+1)."'>".$pagename[$i]."</a><br />";

								if($idp==1){
									$CONTENT_CONTENT_TABLE_SUMMARY = ($content_pref["content_content_summary_{$mainparent}"] && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
									$CONTENT_CONTENT_TABLE_SUMMARY = $aa -> parseContentPathVars($CONTENT_CONTENT_TABLE_SUMMARY);
								}else{
									$CONTENT_CONTENT_TABLE_SUMMARY = "";
								}
							}
						}else{
							$CONTENT_CONTENT_TABLE_SUMMARY	= ($content_pref["content_content_summary_{$mainparent}"] && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
							$CONTENT_CONTENT_TABLE_SUMMARY	= $aa -> parseContentPathVars($CONTENT_CONTENT_TABLE_SUMMARY);
						}

						$CONTENT_CONTENT_TABLE_TEXT		= $aa -> parseContentPathVars($CONTENT_CONTENT_TABLE_TEXT);
						$CONTENT_CONTENT_TABLE_TEXT		= $tp -> toHTML($CONTENT_CONTENT_TABLE_TEXT, TRUE, "");
						$custom							= $eArrayStorage->ReadArray($row['content_pref']);

						$CONTENT_CONTENT_TABLE = "";
						if(!$CONTENT_CONTENT_TABLE){
							//if no theme has been set, use default theme
							if(!$content_pref["content_theme_{$mainparent}"]){

								//if custom layout is set
								if($custom['content_custom_template']){
									//if custom layout file exists
									if(file_exists($plugindir."templates/default/".$custom['content_custom_template'])){
										require_once($plugindir."templates/default/".$custom['content_custom_template']);
									}else{
										require_once($plugindir."templates/default/content_content_template.php");
									}
								}else{
									require_once($plugindir."templates/default/content_content_template.php");
								}
							}else{
								//if custom layout is set
								if($custom['content_custom_template']){
									//if custom layout file exists
									if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/".$custom['content_custom_template'])){
										require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/".$custom['content_custom_template']);
									}else{
										//if default layout from the set theme exists
										if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php")){
											require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php");
										//else use default theme, default layout
										}else{
											require_once($plugindir."templates/default/content_content_template.php");
										}
									}
								//if no custom layout is set
								}else{
									//if default layout from the set theme exists
									if(file_exists($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php")){
										require_once($plugindir."templates/".$content_pref["content_theme_{$mainparent}"]."/content_content_template.php");
									//else use default theme, default layout
									}else{
										require_once($plugindir."templates/default/content_content_template.php");
									}
								}
							}
						}

						$CONTENT_CONTENT_TABLE_CUSTOM_TAGS = "";
						if(!empty($custom)){
							$CONTENT_CONTENT_TABLE_CUSTOM_PRE = "";
							$CONTENT_CONTENT_TABLE_CUSTOM_TAGS = "";
							//ksort($custom);
							foreach($custom as $k => $v){
								if(!($k == "content_custom_score" || $k == "content_custom_meta" || $k == "content_custom_template")){
									if(substr($k,0,22) == "content_custom_preset_"){
										if($content_pref["content_content_presettags_{$mainparent}"]){
											$key = substr($k,22);
										}
									}else{
										if($content_pref["content_content_customtags_{$mainparent}"]){
											$key = substr($k,15);
										}
									}
									if( isset($key) && $key != "" && isset($v) && $v!="" ){
										$CONTENT_CONTENT_TABLE_CUSTOM_KEY		= $key;
										$CONTENT_CONTENT_TABLE_CUSTOM_VALUE		= $v;
										$CONTENT_CONTENT_TABLE_CUSTOM_TAGS		.= preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CONTENT_TABLE_CUSTOM);
									}
								}
							}
						}
						$text = $tp -> parseTemplate($CONTENT_CONTENT_TABLE, FALSE, $content_shortcodes);
					}
					if($content_pref["content_breadcrumb_{$mainparent}"]){
						$crumbpage = $aa -> getCrumbPage($array, $row['content_parent']);
						if($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "1"){
							echo $crumbpage;
						}elseif($content_pref["content_breadcrumb_rendertype_{$mainparent}"] == "2"){
							$ns -> tablerender(CONTENT_LAN_24, $crumbpage);
						}else{
							$text = $crumbpage.$text;
						}
					}

					//$caption = CONTENT_LAN_34;
					$caption = $row['content_heading'];
					$ns -> tablerender($caption, $text);

					if(preg_match_all("/\[newpage.*?]/si", $row['content_text'], $matches)){
						
						$pages = preg_split("/\[newpage.*?]/si", $row['content_text'], -1, PREG_SPLIT_NO_EMPTY);
						$pages = array_values($pages);
						
						if(count($pages) == count($matches[0])){
						}elseif(count($pages) > count($matches[0])){
							$matches[0] = array_pad($matches[0], -count($pages), "[newpage]");
						}elseif(count($pages) < count($matches[0])){
						}
					}
					if(isset($pages)){
						$comflag = (count($pages) == $qs[3] ? TRUE : FALSE);
					}else{
						$comflag = TRUE;
					}

					if(($row['content_comment'] || $content_pref["content_content_comment_all_{$mainparent}"]) && $comflag){
						if($cache = $e107cache->retrieve("comment.$plugintable.$qs[1]")){
							echo $cache;
						}else{
							ob_start();
							unset($text);
							$query = ($pref['nested_comments'] ?
							"SELECT #comments.*, user_id, user_name, user_admin, user_image, user_signature, user_join, user_comments, user_location FROM #comments
							LEFT JOIN #user ON #comments.comment_author = #user.user_id WHERE comment_item_id='".$qs[1]."' AND comment_type='".$plugintable."' AND comment_pid='0' ORDER BY comment_datestamp"
							:
							"SELECT #comments.*, user_id, user_name, user_admin, user_image, user_signature, user_join, user_comments, user_location FROM #comments
							LEFT JOIN #user ON #comments.comment_author = #user.user_id WHERE comment_item_id='".$qs[1]."' AND comment_type='".$plugintable."' ORDER BY comment_datestamp"
							);

							$comment_total = $sql->db_Select_gen($query); 
							if ($comment_total) {
								$width = 0;
								while ($row2 = $sql->db_Fetch()) {
									if ($pref['nested_comments']) {
										$text .= $cobj->render_comment($row2, $plugintable , "comment", $qs[1], $width, $row['content_heading']);
									} else {
										$text = $cobj->render_comment($row2, $plugintable , "comment", $qs[1], $width, $row['content_heading']);
									}
								}
								$ns->tablerender(CONTENT_LAN_35, $text);

								if(ADMIN && getperms("B")){
									echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?$plugintable.$qs[1]'>".CONTENT_LAN_36."</a></div><br />";
								}
							}
							ob_end_flush(); // dump collected data
						}
						$cobj->form_comment("comment", $plugintable, $qs[1], $row['content_heading']); 
					}

					if($pref['cachestatus']){
						$cache = ob_get_contents();
						$e107cache->set($cachestr, $cache);
					}
					ob_end_flush(); // dump collected data
				}
}
// ##### --------------------------------------------------


require_once(FOOTERF);

?>