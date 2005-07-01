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
|     $Revision: 1.20 $
|     $Date: 2005-07-01 13:48:38 $
|     $Author: streaky $
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
if (file_exists(THEME."links_template.php")) {
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

if (!isset($qs[0]) && $linkspage_pref['link_page_categories'])
{
	if($linkspage_pref['link_cat_sort']){
		$sort = " ORDER BY ".$linkspage_pref['link_cat_sort']." ";
		if($linkspage_pref['link_cat_order']){
			$sort .= " ".$linkspage_pref['link_cat_order']." ";
		}else{
			$sort .= " ASC ";
		}
	}else{
		$sort = " ORDER BY link_category_name ASC ";
	}

	$category_total = $sql->db_Select("links_page_cat", "*", " link_category_class REGEXP '".e_CLASS_REGEXP."' ".$sort." ");
	$sql2 = new db;
	$link_main_table_string = "";
	while ($row = $sql->db_Fetch())
	{
		$total_links_cat = $sql2 -> db_Count("links_page", "(*)", " WHERE link_category={$row['link_category_id']} ");
		$link_main_table_string .= $tp -> parseTemplate($LINK_MAIN_TABLE, FALSE, $link_shortcodes);
	}
	$link_main_table_end = $tp -> parseTemplate($LINK_MAIN_TABLE_END, FALSE, $link_shortcodes);
	$text = $LINK_MAIN_TABLE_START.$link_main_table_string.$link_main_table_end;

	$caption = LAN_LINKS_30;
	$ns->tablerender($caption, $text);

} else {

	//comments on links
	if (isset($qs[0]) && $qs[0] == "comment" && isset($qs[1]) && is_numeric($qs[1]) ){
		if(isset($linkspage_pref["link_comment"]) && $linkspage_pref["link_comment"]){
			$qry = "
			SELECT l.*, lc.*
			FROM #links_page AS l
			LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
			WHERE l.link_id = '".$qs[1]."'
			";
			$link_comment_table_string = "";
			if(!$linkcomment = $sql -> db_Select_gen($qry)){
				js_location(e_SELF);
			}else{
				$row = $sql->db_Fetch();
				$link_append = parse_link_append();
				$LINK_CAT_APPEND	= $link_append;
				$LINK_CAT_NAME		= $row['link_name'];
				$subject			= $row['link_name'];
				$text = $tp -> parseTemplate($LINK_CAT_TABLE, FALSE, $link_shortcodes);
				$ns->tablerender(LAN_LINKS_36, $text);


				$cobj->compose_comment("links_page", "comment", $qs[1], $width, $subject, $showrate=FALSE);
				unset($row);
				require_once(FOOTERF);
				exit;
			}
		}else{
			js_location(e_SELF);
		}
	}

	//personal link managers
	if (isset($qs[0]) && $qs[0] == "manage"){
		if(!(isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager'])){
			js_location(e_SELF);
		}
		if(isset($linkspage_pref['link_directdelete']) && $linkspage_pref['link_directdelete']){
			if(isset($_POST['delete'])){
				$tmp = array_pop(array_flip($_POST['delete']));
				list($delete, $del_id) = explode("_", $tmp);
			}
			//delete link
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

		if(check_class($linkspage_pref['link_manager_class'])){
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

			$lc->show_link_create();
		}else{
			js_location(e_SELF);
		}
		require_once(FOOTERF);
		exit;
	}

	//submit link
	if (isset($qs[0]) && $qs[0] == "submit" && check_class($linkspage_pref['link_submit_class'])) {

		$LINK_SUBMIT_CAT = "";
		$sql = new db;
		if ($link_cats = $sql->db_Select("links_page_cat", "*", " link_category_class REGEXP '".e_CLASS_REGEXP."' ")) {
			$LINK_SUBMIT_CAT = "<select name='cat_name' class='tbox'>";
			while (list($cat_id, $cat_name, $cat_description) = $sql->db_Fetch()) {
				$LINK_SUBMIT_CAT .= "<option value='$cat_id'>".$cat_name."</option>\n";
			}
			$LINK_SUBMIT_CAT .= "</select>";
		}
		$text = preg_replace("/\{(.*?)\}/e", '$\1', $LINK_SUBMIT_TABLE);

		$ns->tablerender(LAN_LINKS_31, $text);
		require_once(FOOTERF);
		exit;
	}

	if (!isset($qs[0])){
		$category = "all";
	}elseif (isset($qs[0]) && $qs[0] == "cat") {
		$category = $qs[1];
	}elseif (isset($qs[1]) && $qs[1] == "cat") {
		$category = $qs[2];
	}
	if(isset($qs[0]) && ($qs[0] == "top" || $qs[0] == "rated") ){
		$category = FALSE;
	}

	if (isset($qs[0]) && $qs[0] != "top" && $qs[0] != "rated")
	{
		$sql->db_Select("links_page_cat", "*", "link_category_class REGEXP '".e_CLASS_REGEXP."' ORDER BY link_category_order");
	}

	if ($category) {
		if ($category == "all") {
			$sql->db_Select("links_page_cat", "*", "link_category_class REGEXP '".e_CLASS_REGEXP."' ORDER BY link_category_order" );
			$nextprevquery = "";
		} else {
			$sql->db_Select("links_page_cat", "*", "link_category_class REGEXP '".e_CLASS_REGEXP."' AND link_category_id='$category'  ORDER BY link_category_order" );
			$number				= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
			$nextprevquery		= ($linkspage_pref["link_nextprev"] ? "LIMIT ".$from.",".$number : "");
		}
	}

	$link_sort = (isset($_POST['link_sort']) && $_POST['link_sort'] ? $_POST['link_sort'] : (isset($linkspage_pref['link_sort']) && $linkspage_pref['link_sort'] ? $linkspage_pref['link_sort'] : "link_order" ) );
	$link_order = (isset($_POST['link_order']) && $_POST['link_order'] ? $_POST['link_order'] : (isset($linkspage_pref['link_order']) && $linkspage_pref['link_order'] ? $linkspage_pref['link_order'] : "ASC" ) );
	$sortorder = " ORDER BY ".$link_sort." ".$link_order." ";
	if($linkspage_pref['link_sortorder']){
		$LINK_CAT_SORTORDER = $lc->showLinkSort();
	}

	$link_cat_table_start = $tp -> parseTemplate($LINK_CAT_TABLE_START, FALSE, $link_shortcodes);

	
	$sql2 = new db;
	while (list($link_category_id, $link_category_name, $link_category_description) = $sql->db_Fetch()) {
		$link_total = $sql2->db_Select("links_page", "*", "link_class REGEXP '".e_CLASS_REGEXP."' AND link_category ='$link_category_id' ");
		if ($sql2->db_Select("links_page", "*", "link_class REGEXP '".e_CLASS_REGEXP."' AND link_category ='$link_category_id' ".$sortorder." ".$nextprevquery." ")) {
			unset($text, $link_cat_table_string);
			$text = "";
			$link_cat_table_string = "";
			$link_activ = 0;
			while ($row = $sql2->db_Fetch()) {
				$link_append = parse_link_append();
				$LINK_CAT_APPEND	= $link_append;
				$LINK_CAT_NAME		= $row['link_name'];
				$link_cat_table_string .= $tp -> parseTemplate($LINK_CAT_TABLE, FALSE, $link_shortcodes);
			}
			if ($link_total > 0) {
				$link_cat_table_end = $tp -> parseTemplate($LINK_CAT_TABLE_END, FALSE, $link_shortcodes);
				$text .= $link_cat_table_start.$link_cat_table_string.$link_cat_table_end;

				// Caption
				$caption = LAN_LINKS_32." ".$link_category_name;
				if ($link_category_description != "") {
					$caption .= " <i>[".$link_category_description."]</i>";
				}
				// Number of links displayed
				$caption .= " (<b title='".(ADMIN ? LAN_LINKS_2 : LAN_LINKS_1)."' >".$link_total."</b>".(ADMIN ? "/<b title='".(ADMIN ? LAN_LINKS_1 : "" )."' >".$link_total."</b>" :
				"").") ";
				$ns->tablerender($caption, $text);

				if($category != "all" && isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
					require_once(e_HANDLER."np_class.php");
					$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
					$ix = new nextprev(e_SELF, $from, $number, $link_total, NP_3, ($np_querystring ? $np_querystring : ""));
				}
			}
			//$link_activ = 0;
			$display_links = TRUE;
		}
	}

	//view top refer
	if(isset($qs[0]) && $qs[0] == "top"){
		$text = "";
		$link_top_table_string = "";
		$number				= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
		$nextprevquery		= ($linkspage_pref["link_nextprev"] ? "LIMIT ".$from.",".$number : "");

		$qry = "
		SELECT l.*, lc.*
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
		WHERE l.link_class REGEXP '".e_CLASS_REGEXP."'
		ORDER BY l.link_refer DESC
		";
		$qry2 = $qry." ".$nextprevquery;

		if(!is_object($sql)){ $sql = new db; }
		$link_total = $sql2 -> db_Select_gen($qry);
		if($sql2 -> db_Select_gen($qry2)){
			$display_links = TRUE;
			while ($row = $sql2 -> db_Fetch()) {
				$category = $row['link_category_id'];
				$link_append = parse_link_append();
				$LINK_CAT_APPEND	= $link_append;
				$LINK_CAT_NAME		= $row['link_name'];
				$link_top_table_string .= $tp -> parseTemplate($LINK_CAT_TABLE, FALSE, $link_shortcodes);
			}
			$link_top_table_start = $tp -> parseTemplate($LINK_CAT_TABLE_START, FALSE, $link_shortcodes);
			$link_top_table_end = $tp -> parseTemplate($LINK_CAT_TABLE_END, FALSE, $link_shortcodes);

			$text .= $link_top_table_start.$link_top_table_string.$link_top_table_end;
			$caption = LAN_LINKS_10;
			$ns->tablerender($caption, $text);

			if(isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
				require_once(e_HANDLER."np_class.php");
				$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
				$ix = new nextprev(e_SELF, $from, $number, $link_total, NP_3, ($np_querystring ? $np_querystring : ""));
			}
		}
	}

	//view top rated
	if(isset($qs[0]) && $qs[0] == "rated"){
		$text = "";
		$link_rated_table_string = "";
		$number	= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");

		$qry = "
		SELECT l.*, r.*
		FROM #rate AS r
		LEFT JOIN #links_page AS l ON l.link_id = r.rate_itemid
		WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' AND r.rate_table='links_page'
		ORDER by r.rate_itemid
		";

		if(!is_object($sql)){ $sql = new db; }
		if (!$sql->db_Select_gen($qry)){
			//$display_links = FALSE;
		}else{
			while ($row = $sql->db_Fetch()) {

				$tmp		= $row['rate_rating'] / $row['rate_votes'];
				$tmp		= explode(".", $tmp);
				$rating[1]	= $tmp[0];										// $ratomg[1] = main result
				$rating[2]	= (!empty($tmp[1]) ? substr($tmp[1],0,1) : "");	// $rating[2] = remainder
				$rate_avg	= $rating[1].".".($rating[2] ? $rating[2] : "0");	// rate average

				$arrRate[] = array($row['rate_itemid'], $row['rate_rating'], $row['rate_votes'], $rate_avg, $rating[1], $rating[2], $row['link_id'], $row['link_name'], $row['link_url'], $row['link_description'], $row['link_button'], $row['link_category'], $row['link_order'], $row['link_refer'], $row['link_open'], $row['link_class']);

			}
			if(empty($arrRate)){
				$err		= LAN_LINKS_33;
			}
			usort($arrRate, create_function('$a,$b','return $a[3]==$b[3]?0:($a[3]>$b[3]?-1:1);'));
			$linktotalrated = count($arrRate);
			for($i=$from;$i<$from+$number;$i++){
				if(isset($arrRate[$i])){
					$display_links = TRUE;

					$thisratearray				= $arrRate[$i];
					$row['link_id']				= $arrRate[$i][6];
					$row['link_name']			= $arrRate[$i][7];
					$row['link_url']			= $arrRate[$i][8];
					$row['link_description']	= $arrRate[$i][9];
					$row['link_button']			= $arrRate[$i][10];
					$row['link_category']		= $arrRate[$i][11];
					$row['link_order']			= $arrRate[$i][12];
					$row['link_refer']			= $arrRate[$i][13];
					$row['link_open']			= $arrRate[$i][14];
					$row['link_class']			= $arrRate[$i][15];

					$LINK_RATED_APPEND	= parse_link_append();
					$LINK_RATED_NAME	= $row['link_name'];
					$LINK_RATED_RATING	= $tp -> parseTemplate('{LINK_RATED_RATING}', FALSE, $link_shortcodes);
					$link_rated_table_string	.= $tp -> parseTemplate($LINK_RATED_TABLE, FALSE, $link_shortcodes);
				}
			}
			$link_rated_table_start = $tp -> parseTemplate($LINK_RATED_TABLE_START, FALSE, $link_shortcodes);
			$link_rated_table_end = $tp -> parseTemplate($LINK_RATED_TABLE_END, FALSE, $link_shortcodes);

			$text .= $link_rated_table_start.$link_rated_table_string.$link_rated_table_end;
			$caption = LAN_LINKS_11;
			$ns->tablerender($caption, $text);

			if(isset($linkspage_pref["link_nextprev"]) && $linkspage_pref["link_nextprev"]){
				require_once(e_HANDLER."np_class.php");
				$np_querystring = (isset($qs[0]) ? $qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
				$ix = new nextprev(e_SELF, $from, $number, $linktotalrated, NP_3, ($np_querystring ? $np_querystring : ""));
			}
		}
	}
	if (!$display_links) {
		$ns->tablerender("Links", "<div style='text-align: center'>".LAN_LINKS_34."</div>");
	}
}

require_once(FOOTERF);


function parse_link_append(){
	global $category, $linkspage_pref, $row;

	if($linkspage_pref['link_open_all'] && $linkspage_pref['link_open_all'] == "5"){
		$link_open_type = $row['link_open'];
	}else{
		$link_open_type = $linkspage_pref['link_open_all'];
	}
	switch ($link_open_type) {
		case 1:
		$link_append = "<a href='".e_SELF."?view.".$row['link_id']."' rel='external'>";
		break;
		case 2:
		$link_append = "<a href='".e_SELF."?view.".$row['link_id']."'>";
		break;
		case 3:
		$link_append = "<a href='".e_SELF."?view.".$row['link_id']."'>";
		break;
		case 4:
		$link_append = "<a href=\"javascript:open_window('".e_SELF."?view.".$row['link_id']."')\">";
		break;
		default:
		$link_append = "<a href='".e_SELF."?view.".$row['link_id']."'>";
	}

	return $link_append;
}

?>