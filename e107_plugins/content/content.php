<?php
/*
* e107 website system
*
* Copyright (C) 2008-2011 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Content management main file
*
* $URL$
* $Id$
*
*/

require_once('../../class2.php');
$e107 = e107::getInstance();
if (!$e107->isInstalled('content')) 
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

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
include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php");

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

//post comment
if(isset($_POST['commentsubmit'])){
	if(!is_object($sql)){ $sql = new db; }
	if(!$sql -> db_Select($plugintable, "content_comment", "content_id='".intval($qs[1])."' ")){
		header("location:".e_BASE."index.php"); exit;
	}else{
		$row = $sql -> db_Fetch();
		if(ANON === TRUE || USER === TRUE){
			//enter_comment($author_name, $comment, $table, $id, $pid, $subject)
			$author = ($_POST['author_name'] ? $_POST['author_name'] : USERNAME);
			$pid = "0";
			$rated = (isset($_POST['rateindex']) ? $_POST['rateindex'] : "");
			$cobj -> enter_comment($author, $_POST['comment'], $plugintable, $qs[1], $pid, $_POST['subject'], $rated);
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
	//recent of parent='X'
	if( $qs[0] == "recent" && is_numeric($qs[1]) && intval($qs[1])>0 && ( !isset($qs[2]) || substr($qs[2],0,5) == "order" ) ){
		show_content_recent();

	//item
	}elseif( $qs[0] == "content" && is_numeric($qs[1]) && intval($qs[1])>0 ){
		show_content_item();

	//all categories of parent='X'
	}elseif( $qs[0] == "cat" && $qs[1] == "list" && is_numeric($qs[2]) && intval($qs[2])>0 && !isset($qs[3]) ){
		show_content_cat_all();

	//category of parent='X'
	}elseif( $qs[0] == "cat" && is_numeric($qs[1]) && intval($qs[1])>0 && (!isset($qs[2]) || $qs[2] == "view" || $qs[2] == "comment" || substr($qs[2],0,5) == "order") ){

		if( isset($qs[2]) && $qs[2] == "comment" ){
			show_content_cat("comment");
		}elseif( isset($qs[2]) && $qs[2] == "view" ){
			show_content_cat('view');
		}else{
			show_content_cat();
		}

	//top rated of parent='X'
	}elseif( $qs[0] == "top" && is_numeric($qs[1]) && intval($qs[1])>0 && !isset($qs[2]) ){
		show_content_top();

	//top score of parent='X'
	}elseif( $qs[0] == "score" && is_numeric($qs[1]) && intval($qs[1])>0 ){
		// && !isset($qs[2])
		show_content_score();

	//authorlist of parent='X'
	}elseif( $qs[0] == "author" && $qs[1] == "list" && is_numeric($qs[2]) && intval($qs[2])>0 && ( !isset($qs[3]) || substr($qs[3],0,5) == "order" ) ){
		show_content_author_all();

	//authorlist of content_id='X'
	}elseif( $qs[0] == "author" && is_numeric($qs[1]) && intval($qs[1])>0 && (!isset($qs[2]) || substr($qs[2],0,5) == "order")  ){
		show_content_author();

	//archive of parent='X'
	}elseif( $qs[0] == "list" && is_numeric($qs[1]) && intval($qs[1])>0  ){
		show_content_archive();
	}else{
		//js_location(e_SELF);
		header("location:".e_SELF);
	}
}
// ##### ------------------------------------------------------------------------------

// ##### CONTENT SEARCH MENU ----------------------------
function show_content_search_menu($mode, $mainparent){
		global $qs, $plugindir, $content_shortcodes, $tp, $ns, $rs, $aa, $plugintable, $gen, $content_pref, $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD;

		if( varsettrue($content_pref["content_navigator_{$mode}"]) || varsettrue($content_pref["content_search_{$mode}"]) || varsettrue($content_pref["content_ordering_{$mode}"]) ){

			$template_vars = array("CONTENT_SEARCH_TABLE");
			foreach($template_vars as $t){ global $$t; }
			$aa -> gettemplate($template_vars, 'content_search_template.php');

			if( varsettrue($content_pref["content_navigator_{$mode}"]) ){
				$CONTENT_SEARCH_TABLE_SELECT = $aa -> showOptionsSelect("page", $mainparent);
			}
			if( varsettrue($content_pref["content_search_{$mode}"]) ){
				$CONTENT_SEARCH_TABLE_KEYWORD = $aa -> showOptionsSearch("page", $mainparent);
			}
			if( varsettrue($content_pref["content_ordering_{$mode}"]) ){
				$CONTENT_SEARCH_TABLE_ORDER = $aa -> showOptionsOrder("page", $mainparent);
			}

			$text = $tp -> parseTemplate($CONTENT_SEARCH_TABLE, FALSE, $content_shortcodes);

			if($content_pref["content_searchmenu_rendertype"] == "2"){
				$caption = CONTENT_LAN_77;
				$ns -> tablerender($caption, $text);
			}else{
				echo $text;
			}
		}
		return TRUE;
}

function show_content_search_result($searchkeyword){
		global $row, $qs, $content_shortcodes, $ns, $rs, $tp, $plugindir, $plugintable, $gen, $aa, $content_pref, $datequery, $mainparent;

		$mainparent			= $aa -> getMainParent( (is_numeric($qs[1]) ? $qs[1] : intval($qs[2])) );
		$content_pref		= $aa -> getContentPref($mainparent, true);
		$array				= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
		$validparent		= implode(",", array_keys($array));
		$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$searchkeyword		= $tp -> toDB($searchkeyword);
		$qry				.= " AND (content_heading REGEXP '".$searchkeyword."' OR content_subheading REGEXP '".$searchkeyword."' OR content_summary REGEXP '".$searchkeyword."' OR content_text REGEXP '".$searchkeyword."' ) ";

		$sqlsr = "";
		if(!is_object($sqlsr)){ $sqlsr = new db; }
		if(!$sqlsr -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_icon, content_datestamp", " ".$qry." ".$datequery." ORDER BY content_heading")){
			$textsr = "<div style='text-align:center;'>".CONTENT_SEARCH_LAN_0."</div>";
		}else{

			$template_vars = array("CONTENT_SEARCHRESULT_TABLE", "CONTENT_SEARCHRESULT_TABLE_START", "CONTENT_SEARCHRESULT_TABLE_END");
			foreach($template_vars as $t){ global $$t; }
			$aa -> gettemplate($template_vars, 'content_searchresult_template.php');

			$string = "";
			if(!is_object($gen)){ $gen = new convert; }
			while($row = $sqlsr -> db_Fetch()){

				$row['content_heading']		= parsesearch($row['content_heading'], $searchkeyword, "full");
				$row['content_subheading']	= parsesearch($row['content_subheading'], $searchkeyword, "full");
				$row['content_text']		= parsesearch($row['content_text'], $searchkeyword, "");

				$string .= $tp -> parseTemplate($CONTENT_SEARCHRESULT_TABLE, FALSE, $content_shortcodes);
			}
			$textsr = $CONTENT_SEARCHRESULT_TABLE_START.$string.$CONTENT_SEARCHRESULT_TABLE_END;
		}
		$ns -> tablerender(CONTENT_LAN_20, $textsr);
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
		$text = preg_replace("/".$match."/i", "<span class='searchhighlight'>$match</span>", $text);
		return($text);
}

// ##### CONTENT CACHE PRE ------------------------------
function CachePre($cachestring=''){
	global $e107cache;
	if($cache = $e107cache->retrieve($cachestring)){
		return $cache;
	}else{
		ob_start();
	}
}
// ##### CONTENT CACHE POST ------------------------------
function CachePost($cachestring=''){
	global $pref, $e107cache;
	if($pref['cachestatus']){
		$cache = ob_get_contents();
		$e107cache->set($cachestring, $cache);
	}
	ob_end_flush(); // dump collected data
}

// ##### CONTENT TYPE LIST ------------------------------
function show_content(){
		global $qs, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $datequery, $eArrayStorage, $contenttotal, $row;

		if(is_readable(e_THEME.$pref['sitetheme']."/content/content_type_template.php")){
			require_once(e_THEME.$pref['sitetheme']."/content/content_type_template.php");
		}else{
			require_once(e_PLUGIN."content/templates/content_type_template.php");
		}

		$cachestr = "$plugintable.typelist";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		if(!is_object($sql)){ $sql = new db; }
		if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_icon, content_pref", "content_parent = '0' AND content_class REGEXP '".e_CLASS_REGEXP."' ".$datequery." ORDER BY round(content_order)")){
			$text .= "<div style='text-align:center;'>".CONTENT_LAN_21."</div>";
		}else{

			$sql2 = "";
			$content_type_table_string = "";
			$plist = $sql->db_getList();
			foreach($plist as $row)
			{
				if(!is_object($sql2)){ $sql2 = new db; }

				$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
				$content_pref = $aa->parseConstants($content_pref);

				$array			= $aa -> getCategoryTree("", $row['content_id'], TRUE);
				$validparent	= implode(",", array_keys($array));
				$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
				$contenttotal	= $sql2 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."'" );
				$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE, FALSE, $content_shortcodes);
			}

			//check if user is allowed on the manager page
			$personalmanagercheck = FALSE;
			//get all categories
			$array = $aa -> getCategoryTree("", "", TRUE);
			$catarray = array_keys($array);
			$qry = "";
			foreach($catarray as $catid){
				$qry .= " content_id='".$catid."' || ";
			}
			$qry = substr($qry,0,-3);
			if($sql -> db_Select($plugintable, "content_id, content_heading, content_pref", " ".$qry." ")){
				while($row = $sql -> db_Fetch()){
					if( varsettrue($row['content_pref'],'') ){
						$content_pref = $eArrayStorage->ReadArray($row['content_pref']);
					}
					//if inherit is used in the manager, we need to get the preferences from the core plugin table default preferences
					//and use those preferences in the permissions check.
					if( varsettrue($content_pref['content_manager_inherit'],'') ){
						$sql2 -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
						$row2 = $sql2 -> db_Fetch();
						$content_pref = $eArrayStorage->ReadArray($row2['e107_value']);
					}
					if( (isset($content_pref["content_manager_submit"]) && check_class($content_pref["content_manager_submit"])) ||
						(isset($content_pref["content_manager_approve"]) && check_class($content_pref["content_manager_approve"])) || (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) ){
						$personalmanagercheck = TRUE;
						break;
					}
				}
			}
			if($personalmanagercheck == TRUE){
				$content_type_table_string .= $tp -> parseTemplate($CONTENT_TYPE_TABLE_MANAGER, FALSE, $content_shortcodes);
			}
			$text = $CONTENT_TYPE_TABLE_START.$content_type_table_string.$CONTENT_TYPE_TABLE_END;
		}
		$ns -> tablerender(CONTENT_LAN_22, $text);
		$cachecheck = CachePost($cachestr);
}

// ##### CONTENT ARCHIVE ------------------------------------------
function show_content_archive(){
		global $row, $ns, $plugindir, $plugintable, $sql, $aa, $rs, $e107cache, $tp, $pref, $content_pref, $cobj, $qs, $searchkeyword, $nextprevquery, $from, $number, $mainparent, $content_shortcodes, $datequery, $CONTENT_ARCHIVE_TABLE_LETTERS, $CONTENT_SEARCH_TABLE_SELECT, $CONTENT_SEARCH_TABLE_ORDER, $CONTENT_SEARCH_TABLE_KEYWORD, $CONTENT_NEXTPREV;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("archive", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_ARCHIVE_TABLE", "CONTENT_ARCHIVE_TABLE_START", "CONTENT_ARCHIVE_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_archive_template.php');

		$cachestr = "$plugintable.archive.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$text = "";
		$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent	= implode(",", array_keys($array));
		$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$number			= varsettrue($content_pref["content_archive_nextprev_number"], '30');
		$order			= $aa -> getOrder('archive');
		$nextprevquery	= (varsettrue($content_pref["content_archive_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");
		$sql1 = new db;

		if( varsettrue($content_pref["content_archive_letterindex"]) ){
			$distinctfirstletter = $sql -> db_Select($plugintable, " DISTINCT(content_heading) ", "content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ORDER BY content_heading ASC ");
			while($row = $sql -> db_Fetch()){
				$head = $tp->toHTML($row['content_heading'], TRUE);
				$head_sub = ( ord($head) < 128 ? strtoupper(substr($head,0,1)) : substr($head,0,2) );
				$arrletters[] = $head_sub;
			}
			$arrletters = array_values( array_unique($arrletters) );
			sort($arrletters);

			if ($distinctfirstletter > 1){
				$CONTENT_ARCHIVE_TABLE_LETTERS = "<form method='post' action='".e_SELF."?list.".$mainparent."'>";
				$int=TRUE;
				for($i=0;$i<count($arrletters);$i++){
					if(is_numeric($arrletters[$i])){
						if($int===TRUE){
							$class = (isset($qs[2]) && is_numeric($qs[2]) ? 'nextprev_current' : 'nextprev_link');
							$CONTENT_ARCHIVE_TABLE_LETTERS .= "<a class='".$class."' href='".e_SELF."?list.".$mainparent.".0'>0-9</a> ";
						}
						$int=FALSE;
					}else{
						$lu = strtoupper($arrletters[$i]);
						$class = (isset($qs[2]) && strtoupper($qs[2]) == $lu ? 'nextprev_current' : 'nextprev_link');
						$CONTENT_ARCHIVE_TABLE_LETTERS .= "<a class='".$class."' href='".e_SELF."?list.".$mainparent.".".$lu."'>".$lu."</a> ";
					}
				}
				$class = (!isset($qs[2]) || (isset($qs[2]) && strtolower($qs[2])=='all') ? 'nextprev_current' : 'nextprev_link');
				$CONTENT_ARCHIVE_TABLE_LETTERS .= "<a class='".$class."' href='".e_SELF."?list.".$mainparent."'>ALL</a></form>";
			}
			//check letter
			if(isset($qs[2])){
				if(strlen($qs[2]) == 1 && $qs[2] == '0'){
					$qry .= " AND content_heading NOT REGEXP '^[[:alpha:]]' ";
				}elseif(strlen($qs[2]) == 1 && !is_numeric($qs[2]) ){
					$qry .= " AND content_heading LIKE '".$tp->toDB($qs[2])."%' ";
				}
			}
		}

		$contenttotal = $sql1 -> db_Count($plugintable, "(*)", "WHERE content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ");
		if($from > $contenttotal-1){ header("location:".e_SELF); exit; }

		if($item = $sql1 -> db_Select($plugintable, "content_id, content_heading, content_author, content_datestamp", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery )){
			$CONTENT_NEXTPREV = $aa->ShowNextPrev("archive", $from, $number, $contenttotal, true);
			$text = $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE_START, FALSE, $content_shortcodes);
			while($row = $sql1 -> db_Fetch()){
				$text .= $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE, FALSE, $content_shortcodes);
			}
			$text .= $tp -> parseTemplate($CONTENT_ARCHIVE_TABLE_END, FALSE, $content_shortcodes);
		}
		$text = $aa->getCrumbPage("archive", $array, $mainparent).$text;
		$ns->tablerender($content_pref['content_archive_caption'], $text);
		$cachecheck = CachePost($cachestr);
}

//this function renders the preview of a content_item
//used in recent list, view author list, category items list
function displayPreview($qry, $np=false, $array=false){
		global $qs, $array, $row, $gen, $rater, $aa, $sql2, $tp, $plugintable, $plugindir, $content_shortcodes, $content_pref, $mainparent, $CM_AUTHOR, $CONTENT_RECENT_TABLE_INFOPRE, $CONTENT_RECENT_TABLE_INFOPOST, $CONTENT_NEXTPREV;

		$template_vars = array("CONTENT_RECENT_TABLE", "CONTENT_RECENT_TABLE_START", "CONTENT_RECENT_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_recent_template.php');

		if($resultitem = $sql2 -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_icon, content_author, content_datestamp, content_parent, content_refer, content_rate", $qry )){
			if($np){
				$CONTENT_NEXTPREV = $np;
			}
			$text = $tp -> parseTemplate($CONTENT_RECENT_TABLE_START, FALSE, $content_shortcodes);
			while($row = $sql2 -> db_Fetch()){
				$CM_AUTHOR = $aa -> prepareAuthor("list", $row['content_author'], $row['content_id']);
				$rdate	= $tp -> parseTemplate('{CM_DATE|recent}', FALSE, $content_shortcodes);
				$rauth	= $tp -> parseTemplate('{CM_AUTHOR|recent}', FALSE, $content_shortcodes);
				$rep	= $tp -> parseTemplate('{CM_EPICONS|recent}', FALSE, $content_shortcodes);
				$rpar	= $tp -> parseTemplate('{CM_PARENT|recent}', FALSE, $content_shortcodes);
				$redi	= $tp -> parseTemplate('{CM_EDITICON|recent}', FALSE, $content_shortcodes);
				$CONTENT_RECENT_TABLE_INFOPRE = FALSE;
				$CONTENT_RECENT_TABLE_INFOPOST = FALSE;
				if ($rdate!="" || $rauth!="" || $rep!="" || $rpar!="" || $redi!="" ) {
					$CONTENT_RECENT_TABLE_INFOPRE = TRUE;
					$CONTENT_RECENT_TABLE_INFOPOST = TRUE;
				}
				$text .= $tp -> parseTemplate($CONTENT_RECENT_TABLE, FALSE, $content_shortcodes);
			}
			$text .= $tp -> parseTemplate($CONTENT_RECENT_TABLE_END, FALSE, $content_shortcodes);
		}
		return $text;
}

// ##### RECENT LIST ------------------------------------
function show_content_recent(){
		global $qs, $sql2, $plugindir, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj, $nextprevquery, $from, $number, $mainparent, $datequery, $CONTENT_RECENT_TABLE, $array;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("recent", $mainparent);		//show navigator/search/order menu
		$content_pref = $aa->parseConstants($content_pref);

		$cachestr = "$plugintable.recent.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$crumbarray			= $aa -> getCategoryTree("", intval($mainparent), TRUE);
		$array				= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
		$validparent		= implode(",", array_keys($array));
		$order				= $aa -> getOrder();
		$number				= varsettrue($content_pref["content_nextprev_number"], '5');
		$nextprevquery		= (varsettrue($content_pref["content_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");
		$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";

		$contenttotal = $sql2 -> db_Count($plugintable, "(*)", "WHERE content_refer != 'sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' " );

		if($from > $contenttotal-1){ js_location(e_SELF); }

		$recentqry = "content_refer !='sa' AND ".$qry." ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ".$order." ".$nextprevquery;
		$np = $aa->ShowNextPrev("", $from, $number, $contenttotal,true);
		$text = $aa->getCrumbPage("recent", $crumbarray, $mainparent);
		$text .= displayPreview($recentqry, $np, $array);

		$caption = $content_pref['content_list_caption'];
		if( varsettrue($content_pref['content_list_caption_append_name'],'') ){
			$caption .= " ".$array[intval($qs[1])][1];
		}
		$ns -> tablerender($caption, $text);
		$cachecheck = CachePost($cachestr);
}

//function to (multi)sort by key
function multi_sort($array, $key)
{
	$cmp_val="((\$a['$key']>\$b['$key'])?1:((\$a['$key']==\$b['$key'])?0:-1))";
	$cmp=create_function('$a, $b', "return $cmp_val;");
	uasort($array, $cmp);
	return $array;
}


// ##### CATEGORY LIST ------------------------------------
function show_content_cat_all(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $aa, $e107cache, $tp, $pref, $content_pref, $totalitems, $row, $datestamp, $comment_total, $gen, $authordetails, $rater, $crumb, $sql, $sql2, $datequery, $amount, $from, $n, $mainparent, $CM_AUTHOR, $CONTENT_CAT_TABLE_INFO_PRE, $CONTENT_CAT_TABLE_INFO_POST, $CONTENT_CAT_LIST_TABLE_INFO_PRE, $CONTENT_CAT_LIST_TABLE_INFO_POST;

		unset($text);

		$mainparent		= $aa -> getMainParent(intval($qs[2]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("catall", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_CAT_TABLE", "CONTENT_CAT_TABLE_START", "CONTENT_CAT_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_cat_template.php');

		$cachestr = "$plugintable.cat.list.$qs[2]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$array = $aa -> getCategoryTree("", $mainparent, TRUE);

		$newarray = array_merge_recursive($array);
		for($a=0;$a<count($newarray);$a++){
			for($b=0;$b<count($newarray[$a]);$b++){
				$newparent[$newarray[$a][$b]] = $newarray[$a][$b+1];
				$b++;
			}
		}
		$cids = implode(",", array_keys($newparent) );

		//we need to get the order for the current mainparent
		//the order value for all categories of this top level category will be increased with the top level categories order
		//that way, the top level category will always be the first result,
		//while all other categories are sorted correctly beneath it
		$sql2 -> db_Select($plugintable, "content_id, content_order", " content_id = '".$mainparent."' ");
		$row = $sql2 -> db_Fetch();
		$mainparent_order = $row['content_order'];

		//we parse the order string, dependent of the content_pref
		$order = $aa -> getOrder('catall');

		//get all records, and tmp store them in the $data array
		$data=array();
		$sql2 -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_icon, content_author, content_datestamp, content_parent, content_comment, content_rate, content_order", " content_id IN (".$cids.") ".$order." ");
		while($row = $sql2 -> db_Fetch()){
			if($row['content_id']!=$mainparent){
				$row['content_order'] += $mainparent_order;
			}
			$data[] = $row;
		}

		//we need to reorder the records, but only if we need to sort by the content_order value
		//in all other sort/order cases, the above query is already correct
		$orderstring = ($content_pref['content_catall_defaultorder'] ? $content_pref['content_catall_defaultorder'] : "orderaheading" );
		if(substr($orderstring,6) == "order"){
			//sort the array on the order field
			$data = multi_sort($data, "content_order");
		}

		//finally we can loop through all records
		$string = "";
		foreach($data as $row){
			$totalitems = $aa -> countCatItems($row['content_id']);
			$date	= $tp -> parseTemplate('{CM_DATE|cat}', FALSE, $content_shortcodes);
			$auth	= $tp -> parseTemplate('{CM_AUTHOR|cat}', FALSE, $content_shortcodes);
			$ep		= $tp -> parseTemplate('{CM_EPICONS|cat}', FALSE, $content_shortcodes);
			$com	= $tp -> parseTemplate('{CONTENT_CAT_TABLE_COMMENT}', FALSE, $content_shortcodes);
			$CONTENT_CAT_TABLE_INFO_PRE = FALSE;
			$CONTENT_CAT_TABLE_INFO_POST = FALSE;
			if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
				$CONTENT_CAT_TABLE_INFO_PRE = TRUE;
				$CONTENT_CAT_TABLE_INFO_POST = TRUE;
			}
			$CM_AUTHOR = $aa -> prepareAuthor("catall", $row['content_author'], $row['content_id']);
			$string .= $tp -> parseTemplate($CONTENT_CAT_TABLE, FALSE, $content_shortcodes);
		}

		$text = $aa->getCrumbPage("catall", $array, $mainparent);
		$text .= $tp -> parseTemplate($CONTENT_CAT_TABLE_START, FALSE, $content_shortcodes);
		$text .= $string;
		$text .= $tp -> parseTemplate($CONTENT_CAT_TABLE_END, FALSE, $content_shortcodes);

		$ns -> tablerender($content_pref['content_catall_caption'], $text);
		$cachecheck = CachePost($cachestr);
}

function show_content_cat($mode=""){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $sql2, $aa, $e107cache, $tp, $pref, $content_pref, $cobj, $datequery, $from, $CONTENT_RECENT_TABLE, $CM_AUTHOR, $CONTENT_CAT_LIST_TABLE_INFO_PRE, $CONTENT_CAT_LIST_TABLE_INFO_POST, $mainparent, $totalparent, $totalsubcat, $row, $datestamp, $comment_total, $gen, $authordetails, $rater, $crumb, $amount, $array;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent	= "0,0.".implode(",0.", array_keys($array));
		$qry			= " content_id = '".intval($qs[1])."' AND content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

		show_content_search_menu("cat", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_CAT_LIST_TABLE", "CONTENT_CAT_LISTSUB_TABLE", "CONTENT_CAT_LISTSUB_TABLE_START", "CONTENT_CAT_LISTSUB_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_cat_template.php');

		$order			= $aa -> getOrder();
		$number			= varsettrue($content_pref["content_nextprev_number"], '5');
		$nextprevquery	= (varsettrue($content_pref["content_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");
		$capqs			= array_reverse($array[intval($qs[1])]);
		$caption = $content_pref['content_cat_caption'];
		if( varsettrue($content_pref['content_cat_caption_append_name'],'') ){
			$caption .= " ".$capqs[0];
		}

		// parent article
		if( varsettrue($content_pref["content_cat_showparent"]) ){
			if(!$resultparent = $sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_icon, content_author, content_datestamp, content_comment, content_rate", $qry )){
				header("location:".e_SELF."?cat.list.".$mainparent); exit;
			}else{
				//if 'view' override the items pref to show only limited text adn show full catetgory text instead
				if($mode=='view' || $mode=='comment'){
					$content_pref['content_cat_text_char'] = 'all';
				}
				$row = $sql -> db_Fetch();
				$date	= $tp -> parseTemplate('{CM_DATE|catlist}', FALSE, $content_shortcodes);
				$auth	= $tp -> parseTemplate('{CM_AUTHOR|catlist}', FALSE, $content_shortcodes);
				$ep		= $tp -> parseTemplate('{CM_EPICONS|catlist}', FALSE, $content_shortcodes);
				$com	= $tp -> parseTemplate('{CONTENT_CAT_LIST_TABLE_COMMENT}', FALSE, $content_shortcodes);
				if ($date!="" || $auth!="" || $ep!="" || $com!="" ) {
					$CONTENT_CAT_LIST_TABLE_INFO_PRE = TRUE;
					$CONTENT_CAT_LIST_TABLE_INFO_POST = TRUE;
				}
				$totalparent = $aa -> countCatItems($row['content_id']);
				$CM_AUTHOR = $aa -> prepareAuthor("cat", $row['content_author'], $row['content_id']);
				$textparent = $tp -> parseTemplate($CONTENT_CAT_LIST_TABLE, FALSE, $content_shortcodes);
			}
		}

		$cachestr = "$plugintable.cat.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}

		if(!$mode || $mode == "" || $mode=='view'){
			$check			= (isset($qs[1]) && is_numeric($qs[1]) ? intval($qs[1]) : intval($mainparent));
			$array1			= $aa -> getCategoryTree("", $check, TRUE);
			$newarray		= array_merge_recursive($array1);
			$levels = 0;
			if(isset($content_pref['content_cat_levels']) && is_numeric($content_pref['content_cat_levels']) && $content_pref['content_cat_levels']>0){
				$levels = intval($content_pref['content_cat_levels']) + 1;
			}
			if($levels>0){
				for($a=0;$a<count($newarray);$a++){
					if( count($newarray[$a]) <= (2*$levels) ){
						$newarray2[] = $newarray[$a];
					}
				}
				$newarray = $newarray2;
			}
			for($a=0;$a<count($newarray);$a++){
				for($b=0;$b<count($newarray[$a]);$b++){
					$subparent[$newarray[$a][$b]] = $newarray[$a][$b+1];
					$b++;
				}
			}

			$subparent	= array_keys($subparent);
			$validsub	= "0.".implode(",0.", $subparent);
			$subqry		= " content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validsub)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

			//list subcategories
			if( varsettrue($content_pref["content_cat_showparentsub"]) ){

				$cids = implode(",", $subparent );

				//we parse the order string, dependent of the content_pref
				$order = $aa -> getOrder('cat');

				//finally we can loop through all records
				$content_cat_listsub_table_string = "";
				if($sql2 -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_icon, content_parent, content_order", " content_id IN (".$cids.") AND ".$subqry." ".$order." ")){
					while($row = $sql2 -> db_Fetch()){
						$totalsubcat = $aa -> countCatItems($row['content_id']);
						$content_cat_listsub_table_string .= $tp -> parseTemplate($CONTENT_CAT_LISTSUB_TABLE, FALSE, $content_shortcodes);
					}
					$textsubparent = $CONTENT_CAT_LISTSUB_TABLE_START.$content_cat_listsub_table_string.$CONTENT_CAT_LISTSUB_TABLE_END;
					$captionsubparent = $content_pref['content_cat_sub_caption'];
				}
			}

			//list all contents within this category
			unset($text);

			//also show content items of subcategories of this category ?
			if( varsettrue($content_pref["content_cat_listtype"]) ){
				$validitem = implode(",", $subparent);
				$qrycat = " content_parent REGEXP '".$aa -> CONTENTREGEXP($validitem)."' ";
			}else{
				$qrycat = " content_parent = '".intval($qs[1])."' ";
			}
			$qrycat = " content_refer !='sa' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND ".$qrycat." ";
			$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE ".$qrycat);
			$childqry = $qrycat." ".$order." ".$nextprevquery;
			$np=false;
			if( varsettrue($content_pref["content_nextprev"]) ){
				$np = $aa->ShowNextPrev(FALSE, $from, $number, $contenttotal, true);
			}
			$textchild = displayPreview($childqry, $np, $array);
			$captionchild = $content_pref['content_cat_item_caption'];

			$crumbpage = $aa->getCrumbPage("cat", $array, $qs[1]);
			if( varsettrue($textparent) ){ 
				$textparent = $crumbpage.$textparent;
			}else{
				$textchild = $crumbpage.$textchild;
			}
			if(isset($content_pref["content_cat_menuorder"]) && $content_pref["content_cat_menuorder"] == "1"){
				if(isset($content_pref["content_cat_rendertype"]) && $content_pref["content_cat_rendertype"] == "1"){
					if( varsettrue($textparent) ){ $ns -> tablerender($caption, $textparent); }
					if( varsettrue($textsubparent) ){ $ns -> tablerender($captionsubparent, $textsubparent); }
					if( varsettrue($textchild) ){ $ns -> tablerender($captionchild, $textchild); }
				}else{
					$ns -> tablerender($caption, varsettrue($textparent,'').varsettrue($textsubparent,'').$textchild);
				}
			}else{
				if(isset($content_pref["content_cat_rendertype"]) && $content_pref["content_cat_rendertype"] == "1"){
					if( varsettrue($textchild) ){ $ns -> tablerender($captionchild, $textchild); }
					if( varsettrue($textparent) ){ $ns -> tablerender($caption, $textparent); }
					if( varsettrue($textsubparent) ){ $ns -> tablerender($captionsubparent, $textsubparent); }
				}else{
					$ns -> tablerender($caption, varsettrue($textchild).varsettrue($textparent,'').varsettrue($textsubparent,''));
				}
			}
		}
		$cachecheck = CachePost($cachestr);

		if($mode == "comment"){
			$textparent = $aa->getCrumbPage("cat", $array, $mainparent).$textparent;
			if( varsettrue($textparent) ){ $ns -> tablerender($caption, $textparent); }

			if($resultitem = $sql -> db_Select($plugintable, "content_heading, content_rate, content_comment", $qry )){
				$row = $sql -> db_Fetch();
				if($row['content_comment']){
					$cachestr = "comment.$plugintable.$qs[1]";
					$cachecheck = CachePre($cachestr);
					if($cachecheck){
						echo $cachecheck;
						return;
					}
					if( (varsettrue($content_pref["content_cat_rating_all"])) || (varsettrue($content_pref["content_cat_rating"]) && $row['content_rate'])){
						$showrate = TRUE;
					}else{
						$showrate = FALSE;
					}
					$cobj->compose_comment($plugintable, "comment", $qs[1], $width, $row['content_heading'], $showrate);
					$cachecheck = CachePost($cachestr);
				}
			}
		}
}

// ##### AUTHOR LIST --------------------------------------
function show_content_author_all(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $from, $sql, $aa, $e107cache, $tp, $pref, $mainparent, $content_pref, $cobj, $datequery, $authordetails, $i, $gen, $totalcontent, $row, $CONTENT_NEXTPREV;

		$mainparent		= $aa -> getMainParent(intval($qs[2]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("authorall", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_AUTHOR_TABLE", "CONTENT_AUTHOR_TABLE_START", "CONTENT_AUTHOR_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_author_template.php');

		$cachestr = "$plugintable.author.list.$qs[2]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent	= implode(",", array_keys($array));
		$number			= varsettrue($content_pref["content_author_nextprev_number"],'5');
		$nextprevquery	= (varsettrue($content_pref["content_author_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");
		$qry			= " p.content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$dateqry		= "AND p.content_datestamp < ".time()." AND (p.content_enddate=0 || p.content_enddate>".time().")";

		$sql1 = new db; $sql2 = new db;
		$contenttotal = $sql1 -> db_Select($plugintable." AS p", "DISTINCT(p.content_author)", "p.content_refer !='sa' AND ".$qry." ".$datequery." AND p.content_class REGEXP '".e_CLASS_REGEXP."'");

		$query = "
		SELECT DISTINCT(p.content_author)
		FROM #$plugintable AS p
		WHERE p.content_refer !='sa' AND ".$qry." ".$dateqry." AND p.content_class REGEXP '".e_CLASS_REGEXP."'
		ORDER BY p.content_author";

		$arr = array();
		$arr2 = array();
		if (!$sql1->db_Select_gen($query)){
			$text = CONTENT_LAN_15;
		}else{
			while($row1 = $sql1 -> db_Fetch()){
				//parse db field and retrieve user info -> array($author_id, $author_name, $author_email, $content_author);
				$arr[] = $aa->getAuthor($row1['content_author']);
			}
			//combine unique authors
			for($i=0;$i<count($arr);$i++){
				$arr2[$arr[$i][1]][] = $arr[$i];
			}

			$arr3 = array();
			foreach($arr2 as $key=>$value){
				$db='';
				//prepare db field for author comparison
				if(count($arr2[$key])==1){
					$db = " p.content_author='".$arr2[$key][0][3]."' ";
				}else{
					for($k=0;$k<count($arr2[$key]);$k++){
						$db[] = " p.content_author='".$arr2[$key][$k][3]."' ";
					}
					if($db!=''){
						$db = implode(" || ", $db);
					}
				}
				if($db!=''){
					//count items
					$amount = $sql2->db_Count($plugintable." as p", "(*)", "WHERE ".$db );

					$query = "
					SELECT p.content_id, p.content_heading, p.content_datestamp
					FROM #$plugintable AS p
					WHERE (".$db.") AND p.content_refer !='sa' AND ".$qry." ".$dateqry." AND p.content_class REGEXP '".e_CLASS_REGEXP."'
					ORDER BY p.content_datestamp ASC, p.content_author LIMIT 0,1";

					//query to retrieve last created item for each author
					if ($sql2->db_Select_gen($query)){
						while($row2 = $sql2 -> db_Fetch()){
							$arr3[] = array($key, $row2['content_id'], $row2['content_heading'], $row2['content_datestamp'], $amount);
						}
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
			//do an alpha ordering on the author (if 'firstname lastname', lastname is the comparison factor)
			usort($arr3, "cmp");

			//define amount of records to show
			if( varsettrue($content_pref['content_author_nextprev'],'') ){
				$a = $from;
				$b = $from+$number;
			}else{
				$a = 0;
				$b = count($arr3);
			}

			$string = "";
			for($i=$a;$i<$b;$i++){
				if(is_array($arr3[$i])){
					$authordetails[$i][1] = $arr3[$i][0];
					$row['content_id'] = $arr3[$i][1];
					$row['content_heading'] = $arr3[$i][2];
					$row['content_datestamp'] = $arr3[$i][3];
					$totalcontent = $arr3[$i][4];
					$string .= $tp -> parseTemplate($CONTENT_AUTHOR_TABLE, FALSE, $content_shortcodes);
				}
			}
			$CONTENT_NEXTPREV = $aa->ShowNextPrev("author", $from, $number, $contenttotal,true);
			$text = $tp -> parseTemplate($CONTENT_AUTHOR_TABLE_START, FALSE, $content_shortcodes);
			$text .= $string;
			$text .= $tp -> parseTemplate($CONTENT_AUTHOR_TABLE_END, FALSE, $content_shortcodes);
		}
		$text = $aa->getCrumbPage("authorall", $array, $mainparent).$text;
		$ns -> tablerender($content_pref['content_author_index_caption'], $text);
		$cachecheck = CachePost($cachestr);
}


function show_content_author(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $content_pref, $cobj, $nextprevquery, $from, $number, $CONTENT_RECENT_TABLE, $datequery, $crumb, $mainparent, $array;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("author", $mainparent);		//show navigator/search/order menu

		$cachestr = "$plugintable.author.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
		if(array_key_exists($qs[1], $array)){
			$validparent	= "0,0.".implode(",0.", array_keys($array));
		}else{
			$validparent	= implode(",", array_keys($array));
		}
		$order				= $aa -> getOrder();
		$number				= varsettrue($content_pref["content_nextprev_number"],'10');
		$nextprevquery		= (varsettrue($content_pref["content_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");
		$qry				= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$sqla = "";
		if(!is_object($sqla)){ $sqla = new db; }
		if(!$author = $sqla -> db_Select($plugintable, "content_author", "content_refer !='sa' AND ".$qry." ".$datequery." AND content_id = '".intval($qs[1])."' AND content_class REGEXP '".e_CLASS_REGEXP."' ")){
			header("location:".e_SELF."?author.list.".$mainparent); exit;
		}else{
			list($content_author) = $sqla -> db_Fetch();
			$sqlb = new db;
			$authordetails = $aa -> getAuthor($content_author);
			$query = " content_author = '".$authordetails[3]."' || content_author REGEXP '\\\^".$authordetails[1]."' ".(is_numeric($content_author) && $authordetails[3]!=$authordetails[0] ? " || content_author = '".$authordetails[0]."' " : "")." ";
			$validparent = implode(",", array_keys($array));
			$qry = " content_refer !='sa' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' AND (".$query.") ";
			$contenttotal = $sqlb -> db_Count($plugintable, "(*)", "WHERE ".$qry." ");
			$authorqry = $qry." ".$order." ".$nextprevquery;
			
			$text = $aa->getCrumbPage("author", $array, $mainparent);

			$np = $aa->ShowNextPrev("", $from, $number, $contenttotal, true);
			$text .= displayPreview($authorqry, $np, $array);
			
			$caption = $content_pref['content_author_caption'];
			if( varsettrue($content_pref['content_author_caption_append_name'],'') ){
				$caption .= " ".$authordetails[1];
			}
			$ns -> tablerender($caption, $text);
		}
		$cachecheck = CachePost($cachestr);
}

// ##### TOP RATED LIST -----------------------------------
function show_content_top(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $cobj, $from, $datequery, $content_pref, $mainparent, $CM_AUTHOR, $authordetails, $row, $CONTENT_NEXTPREV;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);

		show_content_search_menu("top", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_TOP_TABLE", "CONTENT_TOP_TABLE_START", "CONTENT_TOP_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_top_template.php');

		$cachestr = "$plugintable.top.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$array			= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
		$validparent	= implode(",", array_keys($array));
		$datequery1		= " AND p.content_datestamp < ".time()." AND (p.content_enddate=0 || p.content_enddate>".time().") ";
		$qry			= " p.content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
		$number			= varsettrue($content_pref["content_nextprev_number"]);
		$np				= ($number ? " LIMIT ".intval($from).", ".intval($number) : "");

		$qry1 = "
		SELECT p.content_id, p.content_heading, p.content_icon, p.content_author, p.content_rate, (r.rate_rating / r.rate_votes) as rate_avg
		FROM #rate AS r
		LEFT JOIN #pcontent AS p ON p.content_id = r.rate_itemid
		WHERE p.content_refer !='sa' AND ".$qry." ".$datequery1." AND p.content_class REGEXP '".e_CLASS_REGEXP."' AND r.rate_table='pcontent'
		ORDER BY rate_avg DESC ";
		$qry2 = $qry1." ".$np;

		if(!is_object($sql)){ $sql = new db; }
		$total = $sql -> db_Select_gen($qry1);
		if(!$sql->db_Select_gen($qry2)){
			$text = CONTENT_LAN_37;
		}else{
			$CONTENT_NEXTPREV = $aa->ShowNextPrev("", $from, $number, $total, true);
			$text = $tp -> parseTemplate($CONTENT_TOP_TABLE_START, FALSE, $content_shortcodes);
			while($row = $sql -> db_Fetch()){
				$CM_AUTHOR = $aa -> prepareAuthor("top", $row['content_author'], $row['content_id']);
				$text .= $tp -> parseTemplate($CONTENT_TOP_TABLE, FALSE, $content_shortcodes);
			}
			$text .= $tp -> parseTemplate($CONTENT_TOP_TABLE_END, FALSE, $content_shortcodes);
		}
		$text = $aa->getCrumbPage("top", $array, $mainparent).$text;
		$caption = $content_pref['content_top_caption'];
		if( varsettrue($content_pref['content_top_caption_append_name'],'') ){
			$caption .= " ".$array[intval($qs[1])][1];
		}
		$ns -> tablerender($caption, $text);
		$cachecheck = CachePost($cachestr);
		unset($qry, $qry1, $qry2, $array, $validparent, $datequery);
}


// ##### TOP SCORE LIST -----------------------------------
function show_content_score(){
		global $qs, $plugindir, $content_shortcodes, $ns, $plugintable, $sql, $aa, $e107cache, $tp, $pref, $cobj, $from, $datequery, $content_pref, $mainparent, $eArrayStorage, $CM_AUTHOR, $authordetails, $row, $thisratearray, $CONTENT_NEXTPREV;

		$mainparent		= $aa -> getMainParent(intval($qs[1]));
		$content_pref	= $aa -> getContentPref($mainparent, true);
		show_content_search_menu("score", $mainparent);		//show navigator/search/order menu

		$template_vars = array("CONTENT_SCORE_TABLE", "CONTENT_SCORE_TABLE_START", "CONTENT_SCORE_TABLE_END");
		foreach($template_vars as $t){ global $$t; }
		$aa -> gettemplate($template_vars, 'content_score_template.php');

		$cachestr = "$plugintable.score.$qs[1]";
		$cachecheck = CachePre($cachestr);
		if($cachecheck){
			echo $cachecheck;
			return;
		}
		$array			= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
		$validparent	= implode(",", array_keys($array));
		$qry			= " content_score != '0' AND content_score != '' AND content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";
		$number			= varsettrue($content_pref["content_nextprev_number"],'5');

		if(!is_object($sql)){ $sql = new db; }
		$contenttotal = $sql -> db_Count($plugintable, "(*)", "WHERE ".$qry." ");
		if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_author, content_icon, content_score", " ".$qry." ORDER BY content_score DESC LIMIT ".$from.",".$number." ")){
			$text = CONTENT_LAN_88;
		}else{
			$CONTENT_NEXTPREV = $aa->ShowNextPrev("", $from, $number, $contenttotal, true);
			$text = $tp -> parseTemplate($CONTENT_SCORE_TABLE_START, FALSE, $content_shortcodes);
			while($row = $sql -> db_Fetch()){
				$CM_AUTHOR	= $aa -> prepareAuthor("score", $row['content_author'], $row['content_id']);
				$text .= $tp -> parseTemplate($CONTENT_SCORE_TABLE, FALSE, $content_shortcodes);
			}
			$text .= $tp -> parseTemplate($CONTENT_SCORE_TABLE_END, FALSE, $content_shortcodes);
		}
		$text = $aa->getCrumbPage("score", $array, $mainparent).$text;
		$caption = $content_pref['content_score_caption'];
		if( varsettrue($content_pref['content_score_caption_append_name'],'') ){
			$caption .= " ".$array[intval($qs[1])][1];
		}
		$ns -> tablerender($caption, $text);
		$cachecheck = CachePost($cachestr);
}

// ##### CONTENT ITEM ------------------------------------------
function show_content_item(){
		global $pref, $content_pref, $custom, $plugindir, $plugintable, $array, $content_shortcodes, $datequery, $order, $nextprevquery, $from, $number, $row, $qs, $gen, $sql, $aa, $tp, $rs, $cobj, $e107, $e107cache, $eArrayStorage, $ns, $rater, $ep, $row, $authordetails, $mainparent;
		global $CONTENT_CONTENT_TABLE_TEXT, $CONTENT_CONTENT_TABLE_PAGENAMES, $CONTENT_CONTENT_TABLE_SUMMARY, $CONTENT_CONTENT_TABLE_CUSTOM_TAGS, $CONTENT_CONTENT_TABLE_PARENT, $CONTENT_CONTENT_TABLE_INFO_PRE, $CONTENT_CONTENT_TABLE_INFO_POST, $CM_AUTHOR, $CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA, $CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA;
		global $CONTENT_CONTENT_TABLE_PREV_PAGE, $CONTENT_CONTENT_TABLE_NEXT_PAGE;
		global $comment_edit_query;

		$mainparent			= $aa -> getMainParent(intval($qs[1]));
		$content_pref		= $aa -> getContentPref($mainparent, true);
		$comment_edit_query = 'comment.pcontent.'.$qs[1];

		show_content_search_menu("item", $mainparent);		//show navigator/search/order menu
		$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
		$validparent		= implode(",", array_keys($array));
		$qry				= "content_id='".intval($qs[1])."' AND content_refer !='sa' AND  content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ".$datequery." AND content_class REGEXP '".e_CLASS_REGEXP."' ";

		if(!$resultitem = $sql -> db_Select($plugintable, "*", $qry)){
			header("location:".e_SELF."?recent.".$mainparent); exit;
		}else{
			$row = $sql -> db_Fetch();

			//update refer count outside of cache (count visits ^ count unique ips)
			if( varsettrue($content_pref["content_log"]) ){
				$ip			= $e107->getip();
				$self		= e_SELF;
				$refertmp	= explode("^", $row['content_refer']);
				if(strpos($self, "admin") === FALSE){
					if(strpos($refertmp[1], $ip) === FALSE){
						$referiplist		= ($refertmp[1] ? $refertmp[1]."-".$ip : $ip );
						$contentrefernew	= ($refertmp[0]+1)."^".$referiplist;
					}else{
						$contentrefernew	= ($refertmp[0]+1)."^".$refertmp[1];
					}
					if (!is_object($sql)){ $sql = new db; }  
					$sql -> db_Update($plugintable, "content_refer='".$contentrefernew."' WHERE content_id='".intval($qs[1])."' ");

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
			$cachecheck = CachePre($cachestr);
			if($cachecheck)
			{
				echo $cachecheck;
				//return;
			}
			else
			{
				$number = varsettrue($content_pref["content_nextprev_number"],'5');
				$nextprevquery = (varsettrue($content_pref["content_nextprev"]) ? "LIMIT ".intval($from).",".intval($number) : "");

				$CM_AUTHOR = $aa -> prepareAuthor("content", $row['content_author'], $row['content_id']);
				$CONTENT_CONTENT_TABLE_TEXT = $row['content_text'];

				$CONTENT_CONTENT_TABLE_PREV_PAGE = FALSE;
				$CONTENT_CONTENT_TABLE_NEXT_PAGE = FALSE;
				$lastpage = FALSE;		//boolean whether or not the current page is the last page
				if(preg_match_all("/\[newpage.*?]/si", $row['content_text'], $matches))
				{
					//remove html bbcode (since we're splitting the text, the html bbcode would not be parsed)
					$row['content_text'] = preg_replace("/\\[html\](.*?)\[\/html\]/si", '\1', $row['content_text']);
					//split newpage
					$pages = preg_split("/\[newpage.*?]/si", $row['content_text'], -1, PREG_SPLIT_NO_EMPTY);
					$pages = array_values($pages);

					//remove empty values
					if(trim($pages[0]) == ""){
						unset($pages[0]);
					}
					$pages = array_values($pages);

					if(count($pages) > count($matches[0])){
						$matches[0] = array_pad($matches[0], -count($pages), "[newpage]");
					}

					$CONTENT_CONTENT_TABLE_TEXT = $pages[(!$qs[2] ? 0 : $qs[2]-1)];
					$options = "";
					for ($i=0; $i < count($pages); $i++) {
						if(!isset($qs[2])){ $idp = 1; }else{ $idp = $qs[2]; }
						if($idp == $i+1){ $pre = CONTENT_LAN_92; }else{ $pre = ""; }
						if($matches[0][$i] == "[newpage]"){
							$pagename[$i] = CONTENT_LAN_78;
						}else{
							$arrpagename = explode("[newpage=", $matches[0][$i]);
							$pagename[$i] = substr($arrpagename[1],0,-1);
						}
						if( varsettrue($content_pref["content_content_pagenames_nextprev"]) ){
							if($idp>1){
								if( varsettrue($content_pref["content_content_pagenames_nextprev_prevhead"]) ){
									$cap = $content_pref["content_content_pagenames_nextprev_prevhead"];
									$cap = str_replace("{PAGETITLE}", $pagename[$idp-2], $cap);
								}else{
									$cap = CONTENT_LAN_90;
								}
								$CONTENT_CONTENT_TABLE_PREV_PAGE = "<a href='".e_SELF."?".$qs[0].".".$qs[1].".".($idp-1)."'>".$cap."</a>";
							}else{
								$CONTENT_CONTENT_TABLE_PREV_PAGE = ' ';
							}
							if($idp<count($pages)){
								if( varsettrue($content_pref["content_content_pagenames_nextprev_nexthead"]) ){
									$cap = $content_pref["content_content_pagenames_nextprev_nexthead"];
									$cap = str_replace("{PAGETITLE}", $pagename[$idp], $cap);
								}else{
									$cap = CONTENT_LAN_91;
								}
								$CONTENT_CONTENT_TABLE_NEXT_PAGE = "<a href='".e_SELF."?".$qs[0].".".$qs[1].".".($idp+1)."'>".$cap."</a>";
							}else{
								$CONTENT_CONTENT_TABLE_NEXT_PAGE = ' ';
							}
						}

						//0:normal links, 1:selectbox
						//$content_pref["content_content_pagenames_rendertype"] = "1";
						if(isset($content_pref["content_content_pagenames_rendertype"]) && $content_pref["content_content_pagenames_rendertype"] == "1"){
							$page = CONTENT_LAN_79." ".($i+1)." ".$pre." ".$pagename[$i];
							$url = e_SELF."?".$qs[0].".".$qs[1].".".($i+1);
							$options .= $rs -> form_option($page, ($idp == ($i+1) ? "1" : "0"), $url , "");
						}else{
							$options .= CONTENT_LAN_79." ".($i+1)." ".$pre." : <a href='".e_SELF."?".$qs[0].".".$qs[1].".".($i+1)."'>".$pagename[$i]."</a><br />";
						}

						if($idp==1){
							$CONTENT_CONTENT_TABLE_SUMMARY = ( varsettrue($content_pref["content_content_summary"]) && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "SUMMARY") : "");
							$CONTENT_CONTENT_TABLE_SUMMARY = $tp -> replaceConstants($CONTENT_CONTENT_TABLE_SUMMARY);
						}else{
							$CONTENT_CONTENT_TABLE_SUMMARY = "";
						}
						//render custom/preset on first page
						if( varsettrue($content_pref['content_content_multipage_preset']) ){
							if($idp == '1'){
								$lastpage = TRUE;
							}
						//render custom/preset on last page
						}else{
							if($idp == count($pages)){
								$lastpage = TRUE;
							}
						}
					}
					if($content_pref["content_content_pagenames_rendertype"] == "1"){
						$selectjs = "onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\"";
						$CONTENT_CONTENT_TABLE_PAGENAMES = $rs -> form_select_open("pagenames", $selectjs).$rs -> form_option(CONTENT_LAN_89, "1", "none" , "").$options.$rs -> form_select_close();
					}else{
						$CONTENT_CONTENT_TABLE_PAGENAMES = $options;
					}

				}else{
					$CONTENT_CONTENT_TABLE_SUMMARY = ( varsettrue($content_pref["content_content_summary"]) && $row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "SUMMARY") : "");
					$CONTENT_CONTENT_TABLE_SUMMARY = $tp -> replaceConstants($CONTENT_CONTENT_TABLE_SUMMARY);
					$lastpage = TRUE;
				}

				$CONTENT_CONTENT_TABLE_TEXT = $tp -> replaceConstants($CONTENT_CONTENT_TABLE_TEXT);
				$CONTENT_CONTENT_TABLE_TEXT = $tp -> toHTML($CONTENT_CONTENT_TABLE_TEXT, TRUE, "BODY");

				$custom = $eArrayStorage->ReadArray($row['content_pref']);

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

				$CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA = FALSE;
				$CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA = FALSE;
				$CONTENT_CONTENT_TABLE_INFO_PRE = FALSE;
				$CONTENT_CONTENT_TABLE_INFO_POST = FALSE;
				
				//if any of these exist, pre/post activate the container table
				if ($ico!="" || $date!="" || $auth!="" || $ep!="" || $edit!="" || $par!="" || $com!="" || $score!="" || $ref!="" || $sub!="" || $rat!="" || $fil!="") {
					$CONTENT_CONTENT_TABLE_INFO_PRE = TRUE;
					$CONTENT_CONTENT_TABLE_INFO_POST = TRUE;
					$CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA = TRUE;
					$CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA = TRUE;
				}

				if(!isset($CONTENT_CONTENT_TABLE)){
					//if no theme has been set, use default theme
					if(!$content_pref["content_theme"]){

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
							if(is_readable($tp->replaceConstants($content_pref["content_theme"]).$row['content_layout'])){
								require_once($tp->replaceConstants($content_pref["content_theme"]).$row['content_layout']);
							}else{
								//if default layout from the set theme exists
								if(is_readable($tp->replaceConstants($content_pref["content_theme"])."content_content_template.php")){
									require_once($tp->replaceConstants($content_pref["content_theme"])."content_content_template.php");
								//else use default theme, default layout
								}else{
									require_once($plugindir."templates/default/content_content_template.php");
								}
							}
						//if no custom layout is set
						}else{
							//if default layout from the set theme exists
							if(is_readable($tp->replaceConstants($content_pref["content_theme"])."content_content_template.php")){
								require_once($tp->replaceConstants($content_pref["content_theme"])."content_content_template.php");
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
							if( varsettrue($content_pref["content_content_presettags"]) ){
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
							if( varsettrue($content_pref["content_content_customtags"]) ){
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
				$text = $aa->getCrumbPage("item", $array, $row['content_parent']);
				$text .= $tp -> parseTemplate($CONTENT_CONTENT_TABLE, FALSE, $content_shortcodes);
				$ns -> tablerender($row['content_heading'], $text);
				$cachecheck = CachePost($cachestr);
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

			if($lastpage && ($row['content_comment'] || (varsettrue($content_pref["content_content_comment_all"])) ) ){
				$cachecheck = CachePre($cachestr);
				if($cachecheck){
					echo $cachecheck;
					return;
				}

				if( (varsettrue($content_pref["content_content_rating"]) && $row['content_rate']) || varsettrue($content_pref["content_content_rating_all"]) ){
					$showrate = TRUE;
				}else{
					$showrate = FALSE;
				}
				$width = 0;
				$cobj->compose_comment($plugintable, "comment", $qs[1], $width, $row['content_heading'], $showrate);
				$cachecheck = CachePost($cachestr);
			}
		} //close sql
}

require_once(FOOTERF);

?>