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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/links.php,v $
|     $Revision: 1.21 $
|     $Date: 2005-07-04 22:36:12 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once('../../class2.php');

require_once(e_HANDLER."rate_class.php");
$rater = new rater;
require_once(e_PLUGIN.'links_page/link_shortcodes.php');
require_once(e_PLUGIN.'links_page/link_defines.php');
require_once(e_HANDLER."userclass_class.php");
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass();
global $tp;

$linkspage_pref = $lc -> getLinksPagePref();

$deltest = array_flip($_POST);

if(e_QUERY){
	$qs = explode(".", e_QUERY);

	if(is_numeric($qs[0])){
		$from = array_shift($qs);
	}else{
		$from = "0";
	}
}
$lc -> setPageTitle();

//submit comment
if (isset($_POST['commentsubmit'])) {
	if (!$sql->db_Select("links_page", "link_id", "link_id = '{$qs[1]}' ")) {
		header("location:".e_BASE."index.php");
		exit;
	} else {
		$row = $sql->db_Fetch();
		if ($row[0] && (ANON === TRUE || USER === TRUE)) {

			$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "links_page", $qs[1], $pid, $_POST['subject']);
			$e107cache->clear("comment.links_page.{$qs[1]}");
		}
	}
}

//update refer
if (isset($qs[0]) && $qs[0] == "view" && isset($qs[1]) && is_numeric($qs[1]))
{
	if($sql->db_Select("links_page", "*", "link_id='$qs[1]' AND link_class REGEXP '".e_CLASS_REGEXP."' "))
	{
		$row = $sql->db_Fetch();
		$sql->db_Update("links_page", "link_refer=link_refer+1 WHERE link_id='$qs[1]' ");
		header("location:".$row['link_url']);
		exit;
	}
}

require_once(HEADERF);

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
if (is_readable(THEME."links_template.php")) {
	require_once(THEME."links_template.php");
	} else {
	require_once(e_PLUGIN."links_page/links_template.php");
}

//submit / manage link
if (isset($_POST['add_link']) && check_class($linkspage_pref['link_submit_class'])) {
	if($qs[0] == "submit"){
		$lc -> dbLinkCreate("submit");
	}
	if($qs[0] == "manage"){
		$lc -> dbLinkCreate();
	}
}
//message submitted link
if(isset($qs[0]) && $qs[0] == "s"){
	$lc->show_message(LAN_LINKS_29, LAN_LINKS_28);
}
$qsorder = FALSE;
if(isset($qs[0]) && substr($qs[0],0,5) == "order"){
	$qsorder = TRUE;
}
//show all categories
if((!isset($qs[0]) || $qsorder) && $linkspage_pref['link_page_categories']){
	displayNavigator('cat');
	displayCategory();
}
//show all categories
if(isset($qs[0]) && $qs[0] == "cat" && !isset($qs[1]) ){
	displayNavigator('cat');
	displayCategory();
}
//show all links in all categories
if( ((!isset($qs[0]) || $qsorder) && !$linkspage_pref['link_page_categories']) || (isset($qs[0]) && $qs[0] == "all") ){
	displayNavigator('');
	displayCategoryLinks();
}
//show all links in one categories
if(isset($qs[0]) && $qs[0] == "cat" && isset($qs[1]) && is_numeric($qs[1])){
	displayNavigator('');
	displayCategoryLinks($qs[1]);
}
//view top rated
if(isset($qs[0]) && $qs[0] == "rated"){
	displayNavigator('');
	displayTopRated();
}
//view top refer
if(isset($qs[0]) && $qs[0] == "top"){
	displayNavigator('');
	displayTopRefer();
}
//personal link managers
if (isset($qs[0]) && $qs[0] == "manage"){
	displayNavigator('');
	displayPersonalManager();
}
//comments on links
if (isset($qs[0]) && $qs[0] == "comment" && isset($qs[1]) && is_numeric($qs[1]) ){
	displayNavigator('');
	displayLinkComment();
}
//submit link
if (isset($qs[0]) && $qs[0] == "submit" && check_class($linkspage_pref['link_submit_class'])) {
	displayNavigator('');
	displayLinkSubmit();
}


function displayTopRated(){
	global $qs, $sql, $lc, $tp, $rowl, $link_shortcodes, $from, $ns, $linkspage_pref;
	global $LINK_RATED_TABLE_START, $LINK_RATED_TABLE, $LINK_RATED_TABLE_END, $LINK_RATED_RATING, $LINK_RATED_APPEND;
	
	$number		= (isset($linkspage_pref["link_nextprev_number"]) && $linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$np			= ($linkspage_pref["link_nextprev"] ? "LIMIT ".$from.",".$number : "");
	$catrate	= (isset($qs[1]) && is_numeric($qs[1]) ? " AND l.link_category='".$qs[1]."' " : "");
	$ratemin	= (isset($linkspage_pref['link_rating_minimum']) && $linkspage_pref['link_rating_minimum'] ? $linkspage_pref['link_rating_minimum'] : "0");
	$qry = "
	SELECT l.*, r.*, lc.link_category_id, lc.link_category_name, (r.rate_rating / r.rate_votes) as rate_avg
	FROM #rate AS r
	LEFT JOIN #links_page AS l ON l.link_id = r.rate_itemid
	LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
	WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' ".$catrate." AND lc.link_category_class REGEXP '".e_CLASS_REGEXP."' AND r.rate_table='links_page'
	ORDER BY rate_avg DESC
	";
	$qry2 = $qry." ".$np;

	if(!is_object($sql)){ $sql = new db; }
	$linktotalrated = $sql -> db_Select_gen($qry);
	if (!$ratedlinks = $sql->db_Select_gen($qry2)){
		$lc -> show_message(LAN_LINKS_33, LAN_LINKS_11);
	}else{
		$link_rated_table_string = "";
		while ($rowl = $sql->db_Fetch()) {
			if( ($rowl['rate_avg'] > $ratemin) ){
			$cat = $rowl['link_category_name'];
			$LINK_RATED_APPEND			= parse_link_append($rowl['link_open'], $rowl['link_id']);
			$LINK_RATED_RATING			= $tp -> parseTemplate('{LINK_RATED_RATING}', FALSE, $link_shortcodes);
			$link_rated_table_string	.= $tp -> parseTemplate($LINK_RATED_TABLE, FALSE, $link_shortcodes);
			}
		}
		$link_rated_table_start = $tp -> parseTemplate($LINK_RATED_TABLE_START, FALSE, $link_shortcodes);
		$link_rated_table_end = $tp -> parseTemplate($LINK_RATED_TABLE_END, FALSE, $link_shortcodes);

		if(isset($qs[1])){
			$captioncat = " : ".LAN_LINKS_40." : ".$cat;
		}
		$caption = LAN_LINKS_11." ".(isset($captioncat) ? $captioncat : "");
		$text = $link_rated_table_start.$link_rated_table_string.$link_rated_table_end;
		
		$ns->tablerender($caption, $text);

		if(isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
			require_once(e_HANDLER."np_class.php");
			$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
			$ix = new nextprev(e_SELF, $from, $number, $linktotalrated, NP_3, ($np_querystring ? $np_querystring : ""));
		}
	}
}

function displayTopRefer(){
	global $qs, $sql2, $lc, $link_shortcodes, $cobj, $rowl, $from, $tp, $ns, $linkspage_pref;
	global $LINK_TABLE_START, $LINK_TABLE, $LINK_TABLE_END, $LINK_APPEND;

	$number	= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$np		= ($linkspage_pref["link_nextprev"] ? "LIMIT ".$from.",".$number : "");
	$min	= (isset($linkspage_pref['link_refer_minimum']) && $linkspage_pref['link_refer_minimum'] ? " AND l.link_refer > ".$linkspage_pref['link_refer_minimum'] : "");

	$qry = "
	SELECT l.*, lc.*, COUNT(c.comment_id) AS link_comment
	FROM #links_page AS l
	LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
	LEFT JOIN #comments as c ON c.comment_item_id=l.link_id AND comment_type='links_page'
	WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' ".$min."
	GROUP BY l.link_id
	ORDER BY l.link_refer DESC
	";
	$qry2 = $qry." ".$np;

	if(!is_object($sql2)){ $sql2 = new db; }
	$link_total = $sql2 -> db_Select_gen($qry);
	if(!$sql2 -> db_Select_gen($qry2)){
		$lc -> show_message(LAN_LINKS_42, LAN_LINKS_10);
	}else{
		$link_top_table_string = "";
		while ($rowl = $sql2 -> db_Fetch()) {
			$category				= $rowl['link_category_id'];
			$LINK_APPEND			= parse_link_append($rowl['link_open'], $rowl['link_id']);
			$link_top_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
		}
		$link_top_table_start		= $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
		$link_top_table_end			= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);

		$text = $link_top_table_start.$link_top_table_string.$link_top_table_end;
		$caption = LAN_LINKS_10;
		$ns->tablerender($caption, $text);

		if(isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
			require_once(e_HANDLER."np_class.php");
			$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
			$ix = new nextprev(e_SELF, $from, $number, $link_total, NP_3, ($np_querystring ? $np_querystring : ""));
		}
	}
}

function displayPersonalManager(){
	global $qs, $sql, $sql2, $lc, $link_shortcodes, $cobj, $row, $from, $tp, $ns, $linkspage_pref;
	global $LINK_TABLE_MANAGE_START, $LINK_TABLE_MANAGE, $LINK_TABLE_MANAGE_END;

	if(!(isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager'])){
		js_location(e_SELF);
	}
	//delete link
	if(isset($linkspage_pref['link_directdelete']) && $linkspage_pref['link_directdelete']){
		if(isset($_POST['delete'])){
			$tmp = array_pop(array_flip($_POST['delete']));
			list($delete, $del_id) = explode("_", $tmp);
		}
		if (isset($delete) && $delete == 'main') {
			$sql->db_Select("links_page", "link_order", "link_id='".$del_id."'");
			$row = $sql->db_Fetch();
			$sql2 = new db;
			$sql->db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".$id."'");
			while ($row = $sql->db_Fetch()) {
				$sql2->db_Update("links_page", "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
			}
			if ($sql->db_Delete("links_page", "link_id='".$del_id."'")) {
				$lc->show_message(LCLAN_ADMIN_10." #".$del_id." ".LCLAN_ADMIN_11);
			}
		}
	}
	//upload link icon
	if(isset($_POST['uploadlinkicon'])){
		$lc -> uploadLinkIcon($_POST);
	}

	//show existing links
	if(!(check_class($linkspage_pref['link_manager_class']))){
		js_location(e_SELF);
	}else{
		$qry = "
		SELECT l.*, lc.*
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
		WHERE l.link_author = '".USERID."'
		ORDER BY l.link_name
		";
		$link_table_manage = "";
		if(!$manager_total = $sql -> db_Select_gen($qry)){
			$text = LAN_LINKS_MANAGER_4;
		}else{
			$link_table_manage_start	= $tp -> parseTemplate($LINK_TABLE_MANAGE_START, FALSE, $link_shortcodes);
			while($row = $sql -> db_Fetch()){
				$link_table_manage .= $tp -> parseTemplate($LINK_TABLE_MANAGE, FALSE, $link_shortcodes);
			}
			$link_table_manage_end		= $tp -> parseTemplate($LINK_TABLE_MANAGE_END, FALSE, $link_shortcodes);
			$text = $link_table_manage_start.$link_table_manage.$link_table_manage_end;
		}
		$ns->tablerender(LAN_LINKS_35, $text);

		//show link create
		$lc->show_link_create();
	}
	return;
}

//comments on links
function displayLinkComment(){
	global $qs, $cobj, $tp, $sql, $lc, $rowl, $link_shortcodes, $ns, $linkspage_pref, $LINK_TABLE_START, $LINK_TABLE, $LINK_TABLE_END, $LINK_APPEND;
	if(!(isset($linkspage_pref["link_comment"]) && $linkspage_pref["link_comment"])){
		js_location(e_SELF);
	}else{
		$qry = "
		SELECT l.*, lc.*
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
		WHERE l.link_id = '".$qs[1]."' AND lc.link_category_class REGEXP '".e_CLASS_REGEXP."' AND l.link_class REGEXP '".e_CLASS_REGEXP."' 
		";
		$link_comment_table_string = "";
		if(!$linkcomment = $sql -> db_Select_gen($qry)){
			js_location(e_SELF);
		}else{
			$rowl = $sql->db_Fetch();
			$LINK_APPEND	= parse_link_append($rowl['link_open'], $rowl['link_id']);
			$subject		= $rowl['link_name'];
			$text = $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
			$ns->tablerender(LAN_LINKS_36, $text);

			$cobj->compose_comment("links_page", "comment", $qs[1], $width, $subject, $showrate=FALSE);
		}
	}
	return;
}

function displayLinkSubmit(){
	global $qs, $sql, $tp, $rs, $ns, $linkspage_pref, $link_shortcodes, $LINK_SUBMIT_TABLE;
	if ($link_cats = $sql->db_Select("links_page_cat", "*", " link_category_class REGEXP '".e_CLASS_REGEXP."' ")) {
		$LINK_SUBMIT_CAT = $rs -> form_select_open("cat_name");
		while (list($cat_id, $cat_name, $cat_description) = $sql->db_Fetch()) {
			$LINK_SUBMIT_CAT .= $rs -> form_option($cat_name, "0", $cat_id);
		}
		$LINK_SUBMIT_CAT .= $rs -> form_select_close();
	}
	$text = $tp -> parseTemplate($LINK_SUBMIT_TABLE, FALSE, $link_shortcodes);

	$ns->tablerender(LAN_LINKS_31, $text);
	return;
}

function displayCategory(){
	global $sql, $sql2, $ns, $lc, $tp, $qs, $rowl, $link_shortcodes, $linkspage_pref, $total_links, $category_total, $alllinks;
	global $LINK_MAIN_TABLE_END_ALL, $LINK_MAIN_TABLE, $LINK_MAIN_TABLE_START;

	$qry = "
	SELECT lc.*
	FROM #links_page_cat AS lc
	WHERE lc.link_category_class REGEXP '".e_CLASS_REGEXP."' 
	";

	if(!is_object($sql)){ $sql = new db; }
	if(!is_object($sql2)){ $sql2 = new db; }
	if (!$category_total = $sql->db_Select_gen($qry)){
		$lc -> show_message(LAN_LINKS_41, LAN_LINKS_30);
	}else{
		$link_main_table_string = "";
		while ($rowl = $sql->db_Fetch())
		{
			$rowl['total_links'] = $sql2 -> db_Count("links_page", "(*)", "WHERE link_category = '".$rowl['link_category_id']."' AND link_class REGEXP '".e_CLASS_REGEXP."' ");
			if((!isset($linkspage_pref['link_cat_empty']) || $linkspage_pref['link_cat_empty'] == 0 && $rowl['total_links'] > "0") || (isset($linkspage_pref['link_cat_empty']) && $linkspage_pref['link_cat_empty'])){
				$alllinks = $alllinks + $rowl['total_links'];
				$link_main_table_string .= $tp -> parseTemplate($LINK_MAIN_TABLE, FALSE, $link_shortcodes);
			}
		}
		$link_main_table_start = $tp -> parseTemplate($LINK_MAIN_TABLE_START, FALSE, $link_shortcodes);
		$link_main_table_end = $tp -> parseTemplate($LINK_MAIN_TABLE_END_ALL, FALSE, $link_shortcodes);
		$text = $link_main_table_start.$link_main_table_string.$link_main_table_end;

		$caption = LAN_LINKS_30;
		$ns->tablerender($caption, $text);
	}
	return;
}

function displayNavigator($mode=''){
	global $sql2, $ns, $lc, $tp, $cobj, $rowl, $qs, $linkspage_pref, $from, $link_shortcodes;
	global $LINK_NAVIGATOR_TABLE, $LINK_SORTORDER, $LINK_NAVIGATOR, $LINK_NAVIGATOR_TABLE_PRE, $LINK_NAVIGATOR_TABLE_POST;

	if($mode == "cat"){
		if(isset($linkspage_pref['link_cat_sortorder']) && $linkspage_pref['link_cat_sortorder']){
			$LINK_SORTORDER = $lc->showLinkSort('cat');
		}	
	}else{
		if(isset($linkspage_pref['link_sortorder']) && $linkspage_pref['link_sortorder']){
			$LINK_SORTORDER = $lc->showLinkSort();
		}
	}
	$nav	= $tp -> parseTemplate('{LINK_NAVIGATOR}', FALSE, $link_shortcodes);
	$so		= $tp -> parseTemplate('{LINK_SORTORDER}', FALSE, $link_shortcodes);
	$LINK_NAVIGATOR_TABLE_PRE = FALSE;
	$LINK_NAVIGATOR_TABLE_POST = FALSE;
	if ($nav!="" || $so!="" ) {
		$LINK_NAVIGATOR_TABLE_PRE = TRUE;
		$LINK_NAVIGATOR_TABLE_POST = TRUE;
	}
	$text = $tp -> parseTemplate($LINK_NAVIGATOR_TABLE, FALSE, $link_shortcodes);
	echo $text;
}

function displayCategoryLinks($mode=''){
	global $sql2, $ns, $lc, $tp, $cobj, $rowl, $qs, $linkspage_pref, $from, $link_shortcodes;
	global $LINK_TABLE_START, $LINK_TABLE, $LINK_TABLE_END, $LINK_APPEND, $LINK_TABLE_START_ALL, $LINK_TABLE_END_ALL;

	$order			= $lc -> getOrder();
	$number			= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$nextprevquery	= ($mode && $linkspage_pref["link_nextprev"] ? "LIMIT ".$from.",".$number : "");
	$cat			= ($mode ? " AND l.link_category='".$mode."' " : "");
	$qry			= "
	SELECT l.*, lc.*, COUNT(c.comment_id) AS link_comment
	FROM #links_page AS l
	LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
	LEFT JOIN #comments as c ON c.comment_item_id=l.link_id AND comment_type='links_page'
	WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' AND lc.link_category_class REGEXP '".e_CLASS_REGEXP."' ".$cat." 
	GROUP BY l.link_id
	".$order."
	".$nextprevquery."
	";

	$link_table_string = "";
	if(!is_object($sql2)){ $sql2 = new db; }
	$link_total = $sql2 -> db_Count("links_page as l", "(*)", "WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' ".$cat." ");
	if (!$sql2->db_Select_gen($qry)){
		$lc -> show_message(LAN_LINKS_34, LAN_LINKS_39);
	}else{
		while ($rowl = $sql2->db_Fetch())
		{
			if($mode){
				$cat_name			= $rowl['link_category_name'];
				$cat_desc			= $rowl['link_category_description'];
				$LINK_APPEND		= parse_link_append($rowl['link_open'], $rowl['link_id']);
				$link_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);			
			}else{
				$arr[$rowl['link_category_id']][] = $rowl;
			}
		}
		if($mode){
			$link_table_start		= $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
			$link_table_end			= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);
			$text = $link_table_start.$link_table_string.$link_table_end;
			$caption = LAN_LINKS_32." ".$cat_name." ".($cat_desc ? " <i>[".$cat_desc."]</i>" : "");
			//number of links
			$caption .= " (<b title='".(ADMIN ? LAN_LINKS_2 : LAN_LINKS_1)."' >".$link_total."</b>".(ADMIN ? "/<b title='".(ADMIN ? LAN_LINKS_1 : "" )."' >".$link_total."</b>" : "").") ";
			$ns->tablerender($caption, $text);

			if(is_numeric($mode) && isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
				require_once(e_HANDLER."np_class.php");
				$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
				$ix = new nextprev(e_SELF, $from, $number, $link_total, NP_3, ($np_querystring ? $np_querystring : ""));
			}
		}else{
			foreach($arr as $key => $value){
				$link_table_string = "";
				$i=0;
				for($i=0;$i<count($value);$i++){
					$rowl				= $value[$i];
					$cat_name			= $rowl['link_category_name'];
					$cat_desc			= $rowl['link_category_description'];
					$LINK_APPEND		= parse_link_append($rowl['link_open'], $rowl['link_id']);
					$link_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
				}
				$caption = LAN_LINKS_32." ".$cat_name." ".($cat_desc ? " <i>[".$cat_desc."]</i>" : "");
				//number of links
				$caption .= " (<b title='".(ADMIN ? LAN_LINKS_2 : LAN_LINKS_1)."' >".count($value)."</b>".(ADMIN ? "/<b title='".(ADMIN ? LAN_LINKS_1 : "" )."' >".count($value)."</b>" : "").") ";

				$link_table_start		= $tp -> parseTemplate($LINK_TABLE_START_ALL, FALSE, $link_shortcodes);
				$link_table_end			= $tp -> parseTemplate($LINK_TABLE_END_ALL, FALSE, $link_shortcodes);
				$text = $link_table_start.$link_table_string.$link_table_end;
				$ns->tablerender($caption, $text);
			}
		}
	}
	return;
}

require_once(FOOTERF);


function parse_link_append($open, $id){
	global $linkspage_pref;

	if($linkspage_pref['link_open_all'] && $linkspage_pref['link_open_all'] == "5"){
		$link_open_type = $open;
	}else{
		$link_open_type = $linkspage_pref['link_open_all'];
	}
	switch ($link_open_type) {
		case 1:
		$link_append = "<a href='".e_SELF."?view.".$id."' rel='external'>";
		break;
		case 2:
		$link_append = "<a href='".e_SELF."?view.".$id."'>";
		break;
		case 3:
		$link_append = "<a href='".e_SELF."?view.".$id."'>";
		break;
		case 4:
		$link_append = "<a href=\"javascript:open_window('".e_SELF."?view.".$id."')\">";
		break;
		default:
		$link_append = "<a href='".e_SELF."?view.".$id."'>";
	}
	return $link_append;
}

?>