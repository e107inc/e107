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
|     $Revision: 1.11 $
|     $Date: 2005-06-15 20:36:14 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once('../../class2.php');
require_once(e_HANDLER."rate_class.php");
$rater = new rater;
require_once(e_PLUGIN.'links_page/link_shortcodes.php');
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass();
global $tp;

$linkspage_pref = $lc -> getLinksPagePref();

$qs = explode(".", e_QUERY);
if (is_numeric($qs[0]))
{
	$id = $qs[0];
	if($sql->db_Select("links_page", "*", "link_id='$id' AND link_class REGEXP '".e_CLASS_REGEXP."' "))
	{
		$row = $sql->db_Fetch();
		$sql->db_Update("links_page", "link_refer=link_refer+1 WHERE link_id='$id' ");
		header("location:".$row['link_url']);
		exit;
	}
}


require_once(HEADERF);
if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE."_links.php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE."_links.php");
	} else {
	include_once(e_PLUGIN."links_page/languages/English_links.php");
}

if (isset($_POST['add_link']) && check_class($linkspage_pref['link_submit_class'])) {
	if ($_POST['link_name'] && $_POST['link_url'] && $_POST['link_description']) {
		$link_name			= $tp->toDB($_POST['link_name']);
		$link_url			= $tp->toDB($_POST['link_url']);
		$link_description	= $tp->toDB($_POST['link_description']);
		$link_button		= $tp->toDB($_POST['link_button']);
		$username			= (defined('USERNAME')) ? USERNAME : LAN_LINKS_3;
		$submitted_link		= $_POST['cat_name']."^".$link_name."^".$link_url."^".$link_description."^".$link_button."^".$username;
		$sql->db_Insert("tmp", "'submitted_link', '".time()."', '$submitted_link' ");
		$ns->tablerender(LAN_99, "<div style='text-align:center'>".LAN_100."</div>");
		$edata_ls = array("link_name" => $link_name, "link_url" => $link_url, "link_description" => $link_description, "link_button" => $link_button, "username" => $username, "submitted_link" => $submitted_link);
		$e_event->trigger("linksub", $edata_ls);
		} else {
		message_handler("ALERT", 5);
	}
}

if (e_QUERY == "submit" && check_class($linkspage_pref['link_submit_class'])) {
	if (!$LINK_SUBMIT_TABLE) {
		if (file_exists(THEME."links_template.php")) {
			require_once(THEME."links_template.php");
			} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/links_template.php");
		}
	}

	$link_submit_table_string .= parse_link_submit_table();

	$link_submit_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $LINK_SUBMIT_TABLE_START);
	$link_submit_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $LINK_SUBMIT_TABLE_END);
	$text .= $link_submit_table_start.$link_submit_table_string.$link_submit_table_end;

	$ns->tablerender(LAN_92, $text);
	require_once(FOOTERF);
	exit;
}

if (e_QUERY == "" && $linkspage_pref['link_page_categories'])
{
	if (!$LINK_MAIN_TABLE) {
		if (file_exists(THEME."links_template.php")) {
			require_once(THEME."links_template.php");
			} else {
			require_once(e_PLUGIN."links_page/links_template.php");
		}
	}
	$caption = LAN_61;

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

	$category_total = $sql->db_Select("links_page_cat", "*", " ".$sort." ", "mode=no_where");
	if(!is_object($sql2)){ $sql2 = new db; }
	while ($row = $sql->db_Fetch())
	{
		$total_links_cat = $sql2 -> db_Count("links_page", "(*)", " WHERE link_category={$row['link_category_id']} ");
		$link_main_table_string .= $tp -> parseTemplate($LINK_MAIN_TABLE, FALSE, $link_shortcodes);
	}
	$link_main_table_end .= $tp -> parseTemplate($LINK_MAIN_TABLE_END, FALSE, $link_shortcodes);

	$text .= $LINK_MAIN_TABLE_START.$link_main_table_string.$link_main_table_end;

	$ns->tablerender($caption, $text);
} else {
	$id = e_QUERY;
	if ($qs[0] == "cat") {
		$category = $qs[1];
		unset($id);
	}
	else if ($qs[1] == "cat") {
		$category = $qs[2];
		$id = $qs[0];
	}
	if($qs[0] == "top" || $qs[0] == "rated"){
		$category = FALSE;
	}

	if (isset($id) && $id != "top" && $id != "rated")
	{
		$id = $qs[0];
		$sql->db_Select("links_page_cat", "*");
	}

	if ($category) {
		if ($category == "all") {
			$sql->db_Select("links_page_cat", "*");
		} else {
			$sql->db_Select("links_page_cat", "*", "link_category_id='$category'");
		}
	}

	if (!$LINK_CAT_TABLE) {
		if (file_exists(THEME."links_template.php")) {
			require_once(THEME."links_template.php");
		} else {
			require_once(e_PLUGIN."links_page/links_template.php");
		}
	}

	$link_sort = ($_POST['link_sort'] ? $_POST['link_sort'] : ($linkspage_pref['link_sort'] ? $linkspage_pref['link_sort'] : "link_order" ) );
	$link_order = ($_POST['link_order'] ? $_POST['link_order'] : ($linkspage_pref['link_order'] ? $linkspage_pref['link_order'] : "ASC" ) );
	$sortorder = " ORDER BY ".$link_sort." ".$link_order." ";
	if($linkspage_pref['link_sortorder']){
		$LINK_CAT_SORTORDER = $lc->showLinkSort();
	}
	$link_cat_table_start = $tp -> parseTemplate($LINK_CAT_TABLE_START, FALSE, $link_shortcodes);

	$sql2 = new db;
	while (list($link_category_id, $link_category_name, $link_category_description) = $sql->db_Fetch()) {
		if ($link_total = $sql2->db_Select("links_page", "*", "link_class REGEXP '".e_CLASS_REGEXP."' AND link_category ='$link_category_id' ".$sortorder." ")) {
			unset($text, $link_cat_table_string);
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
				$caption = LAN_86." ".$link_category_name;
				if ($link_category_description != "") {
					$caption .= " <i>[".$link_category_description."]</i>";
				}
				// Number of links displayed
				$caption .= " (<b title='".(ADMIN ? LAN_Links_2 : LAN_Links_1)."' >".$link_total."</b>".(ADMIN ? "/<b title='".(ADMIN ? LAN_Links_1 : "" )."' >".$link_total."</b>" :
				"").") ";
				$ns->tablerender($caption, $text);
			}
			//$link_activ = 0;
			$display_links = TRUE;
		}
	}

	if($id == "top"){
		$text = "";
		$link_top_table_string = "";
		$number = ($linkspage_pref['link_pagenumber'] ? $linkspage_pref['link_pagenumber'] : "15");

		$qry = "
		SELECT l.*, lc.* 
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category  
		WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' 
		ORDER BY l.link_refer DESC
		LIMIT 0,".$number."
		";

		if(!is_object($sql)){ $sql = new db; }
		if($link_total = $sql2 -> db_Select_gen($qry)){
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
		}
	}

	if($id == "rated"){
		$text = "";
		$link_rated_table_string = "";

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
				$err		= CONTENT_LAN_37;
			}
			usort($arrRate, create_function('$a,$b','return $a[3]==$b[3]?0:($a[3]>$b[3]?-1:1);'));
			$linktotalrated = count($arrRate);
			for($i=0;$i<$linktotalrated;$i++){
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
		}
	}
	if (!$display_links) {
		$ns->tablerender("Links", "<div style='text-align: center'>".LAN_107."</div>");
	}
}

require_once(FOOTERF);

function parse_link_submit_table() {
	global $LINK_SUBMIT_TABLE;
	$sql = new db;
	if ($link_cats = $sql->db_Select("links_page_cat")) {
		$LINK_SUBMIT_CAT = "<select name='cat_name' class='tbox'>";
		while (list($cat_id, $cat_name, $cat_description) = $sql->db_Fetch()) {
			if ($cat_name != "Main") {
				$LINK_SUBMIT_CAT .= "<option value='$cat_id'>".$cat_name."</option>\n";
			}
		}
		$LINK_SUBMIT_CAT .= "</select>";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $LINK_SUBMIT_TABLE));
}

function parse_link_append(){
	global $category, $linkspage_pref, $row;

	if (isset($category)) {
		//if ($qs[0] == "cat") {
		//	$link_append = "<a href='".e_SELF."?".$row['link_id'].".cat.{$category}'>";
		//} else {
			if($linkspage_pref['link_open_all'] && $linkspage_pref['link_open_all'] == "5"){
				$link_open_type = $row['link_open'];
			}else{
				$link_open_type = $linkspage_pref['link_open_all'];
			}
			switch ($link_open_type) {
				case 1:
				$link_append = "<a href='".e_SELF."?".$row['link_id'].".cat.{$category}' rel='external'>";
				break;
				case 2:
				$link_append = "<a href='".e_SELF."?".$row['link_id'].".cat.{$category}'>";
				break;
				case 3:
				$link_append = "<a href='".e_SELF."?".$row['link_id'].".cat.{$category}'>";
				break;
				case 4:
				$link_append = "<a href=\"javascript:open_window('".e_SELF."?".$row['link_id'].".cat.{$category}')\">";
				break;
				default:
				$link_append = "<a href='".e_SELF."?".$row['link_id'].".cat.{$category}'>";
			}
		//}
	} else {
		if($linkspage_pref['link_open_all'] && $linkspage_pref['link_open_all'] == "5"){
			$link_open_type = $row['link_open'];
		}else{
			$link_open_type = $linkspage_pref['link_open_all'];
		}
		switch ($link_open_type) {
			case 1:
			$link_append = "<a href='".e_SELF."?".$row['link_id']."' rel='external'>";
			break;
			case 2:
			$link_append = "<a href='".e_SELF."?".$row['link_id']."'>";
			break;
			case 3:
			$link_append = "<a href='".e_SELF."?".$row['link_id']."'>";
			break;
			case 4:
			$link_append = "<a href=\"javascript:open_window('".e_SELF."?".$row['link_id']."')\">";
			break;
			default:
			$link_append = "<a href='".e_SELF."?".$row['link_id']."'>";
		}
	}
	return $link_append;
}

?>