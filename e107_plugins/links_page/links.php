<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/links_page/links.php,v $
 * $Revision: 1.16 $
 * $Date: 2009-11-18 01:05:46 $
 * $Author: e107coders $
 */

require_once('../../class2.php');
if (!plugInstalled('links_page')) 
{
	header("location:".e_BASE."index.php");
	exit;
}

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

	if(is_numeric($qs[0]))
	{
		$from = array_shift($qs);
	}
	else
	{
		$from = "0";
	}
}
include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");

$lc -> setPageTitle();

//submit comment
if (isset($_POST['commentsubmit'])) 
{
	if (!$sql->db_Select("links_page", "link_id", "link_id = '".intval($qs[1])."' ")) 
	{
		header("location:".e_BASE."index.php");
		exit;
	} 
	else 
	{
		$row = $sql->db_Fetch();
		if ($row[0] && (ANON === TRUE || USER === TRUE)) 
		{
			$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "links_page", $qs[1], $pid, $_POST['subject']);
			$e107cache->clear("comment.links_page.{$qs[1]}");
		}
	}
}

//update refer
if (isset($qs[0]) && $qs[0] == "view" && isset($qs[1]) && is_numeric($qs[1]))
{
	if($sql->db_Select("links_page", "*", "link_id='".intval($qs[1])."' AND link_class REGEXP '".e_CLASS_REGEXP."' "))
	{
		$row = $sql->db_Fetch();
		$sql->db_Update("links_page", "link_refer=link_refer+1 WHERE link_id='".intval($qs[1])."' ");
		//header("location:".$row['link_url']); exit;
		js_location($row['link_url']);
	}
}

require_once(HEADERF);


if (is_readable(THEME."links_template.php")) 
{
	require_once(THEME."links_template.php");
} 
else 
{
	require_once(e_PLUGIN."links_page/links_template.php");
}

//submit / manage link
if (isset($_POST['add_link'])) 
{
	if($qs[0] == "submit"){
		if(check_class($linkspage_pref['link_submit_class']))
		{
			if(isset($linkspage_pref['link_submit_directpost']) && $linkspage_pref['link_submit_directpost'])
			{
				$lc -> dbLinkCreate();
			}
			else
			{
				$lc -> dbLinkCreate("submit");
			}
		}
		else
		{
			js_location(e_SELF);
		}
	}
	if($qs[0] == "manage")
	{
		if(check_class($linkspage_pref['link_manager_class']))
		{
			if(isset($qs[2]) && is_numeric($qs[2]))
			{
				$lc->verify_link_manage($qs[2]);
			}
			
			if(isset($linkspage_pref['link_directpost']) && $linkspage_pref['link_directpost'])
			{
				$lc -> dbLinkCreate();
			}
			else
			{
				$lc -> dbLinkCreate("submit");
			}
		}
		else
		{
			js_location(e_SELF);
		}
	}
}

//message submitted link
if(isset($qs[0]) && $qs[0] == "s")
{
	$lc->show_message(LAN_LINKS_29, LAN_LINKS_28);
}
$qsorder = FALSE;
if(isset($qs[0]) && substr($qs[0],0,5) == "order")
{
	$qsorder = TRUE;
}

//show all categories
if((!isset($qs[0]) || $qsorder) && $linkspage_pref['link_page_categories'])
{
  	echo displayNavigator('cat');
	displayCategory();
}
//show all categories
if(isset($qs[0]) && $qs[0] == "cat" && !isset($qs[1]) ){
   echo displayNavigator('cat');
	displayCategory();
}
//show all links in all categories
if( ((!isset($qs[0]) || $qsorder) && !$linkspage_pref['link_page_categories']) || (isset($qs[0]) && $qs[0] == "all") )
{
  //	displayNavigator('');
	displayCategoryLinks();
}

//show all links in one category
if(isset($qs[0]) && $qs[0] == "cat" && isset($qs[1]) && is_numeric($qs[1]))
{
//	echo displayNavigator('');
	displayCategoryLinks($qs[1]);
}
//view top rated
if(isset($qs[0]) && $qs[0] == "rated")
{
	echo displayNavigator('');
	displayTopRated();
}
//view top refer
if(isset($qs[0]) && $qs[0] == "top"){
	echo displayNavigator('');
	displayTopRefer();
}
//personal link managers
if (isset($qs[0]) && $qs[0] == "manage"){
	echo displayNavigator('');
	displayPersonalManager();
}
//comments on links
if (isset($qs[0]) && $qs[0] == "comment" && isset($qs[1]) && is_numeric($qs[1]) ){
	echo displayNavigator('');
	displayLinkComment();
}
//submit link
if (isset($qs[0]) && $qs[0] == "submit")
{
  if (check_class($linkspage_pref['link_submit_class']))
  {
	echo displayNavigator('');
	displayLinkSubmit();
  }
  else
  {
	$lc->show_message(LAN_LINKS_50);
  }
}


function displayTopRated(){
	global $qs, $sql, $lc, $tp, $rowl, $link_shortcodes, $from, $ns, $linkspage_pref;
	global $LINK_RATED_TABLE_START, $LINK_RATED_TABLE, $LINK_RATED_TABLE_END, $LINK_RATED_RATING, $LINK_RATED_APPEND;

	$number		= (isset($linkspage_pref["link_nextprev_number"]) && $linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$np			= ($linkspage_pref["link_nextprev"] ? "LIMIT ".intval($from).",".intval($number) : "");
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
		$list = $sql -> db_getList();
  	    foreach($list as $rowl) {
			if( ($rowl['rate_avg'] > $ratemin) ){
			$cat = $rowl['link_category_name'];
			$LINK_RATED_APPEND			= $lc -> parse_link_append($rowl);
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
		$lc->ShowNextPrev($from, $number, $linktotalrated);
	}
}

function displayTopRefer(){
	global $qs, $sql2, $lc, $link_shortcodes, $cobj, $rowl, $from, $tp, $ns, $linkspage_pref;
	global $LINK_TABLE_START, $LINK_TABLE, $LINK_TABLE_END, $LINK_APPEND;

	$number	= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$np		= ($linkspage_pref["link_nextprev"] ? "LIMIT ".intval($from).",".intval($number) : "");
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
		$list = $sql2 -> db_getList();
  	    foreach($list as $rowl) {
			$category				= $rowl['link_category_id'];
			$LINK_APPEND			= $lc -> parse_link_append($rowl);
			$link_top_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
		}
		$link_top_table_start		= $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
		$link_top_table_end			= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);

		$text = $link_top_table_start.$link_top_table_string.$link_top_table_end;
		$caption = LAN_LINKS_10;
		$ns->tablerender($caption, $text);
		$lc->ShowNextPrev($from, $number, $link_total);
	}
}

function displayPersonalManager()
{
	global $qs, $sql, $sql2, $lc, $link_shortcodes, $cobj, $row, $from, $tp, $ns, $linkspage_pref;
	global $LINK_TABLE_MANAGE_START, $LINK_TABLE_MANAGE, $LINK_TABLE_MANAGE_END;

	if(!(isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager']))
	{
	  js_location(e_SELF);
	}
	//delete link
	if(isset($linkspage_pref['link_directdelete']) && $linkspage_pref['link_directdelete'])
	{
	  if(isset($_POST['delete']))
	  {
		$tmp = array_pop(array_flip($_POST['delete']));
		list($delete, $del_id) = explode("_", $tmp);
	  }
	  if (isset($delete) && $delete == 'main') 
	  {
		$sql->db_Select("links_page", "link_category, link_order, link_author", "link_id='".intval($del_id)."'");		// Get the position of target in the order
		
		$row = $sql->db_Fetch();
	    if($row['link_author'] != USERID) {
			header('Location: '.SITEURL);
			exit;
	    }
			
		if (!is_object($sql2)){ $sql2 = new db; }
		$sql->db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".intval($row['link_category'])."'");
		while ($row = $sql->db_Fetch()) 
		{
		  $sql2->db_Update("links_page", "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
		}
		if ($sql->db_Delete("links_page", "link_id='".intval($del_id)."'")) 
		{
		  $lc->show_message(LCLAN_ADMIN_10." #".$del_id." ".LCLAN_ADMIN_11);
		}
	  }
	}
	//upload link icon
	if(isset($_POST['uploadlinkicon'])){
		$lc -> uploadLinkIcon();
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
	global $qs, $cobj, $tp, $sql, $linkbutton_count, $lc, $rowl, $link_shortcodes, $ns, $linkspage_pref, $LINK_TABLE_START, $LINK_TABLE, $LINK_TABLE_END, $LINK_APPEND;
	if(!(isset($linkspage_pref["link_comment"]) && $linkspage_pref["link_comment"])){
		js_location(e_SELF);
	}else{
		$qry = "
		SELECT l.*, lc.*, COUNT(c.comment_id) AS link_comment
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
		LEFT JOIN #comments as c ON c.comment_item_id=l.link_id AND comment_type='links_page'
		WHERE l.link_id = '".intval($qs[1])."' AND lc.link_category_class REGEXP '".e_CLASS_REGEXP."' AND l.link_class REGEXP '".e_CLASS_REGEXP."'
		GROUP BY l.link_id";
		$link_comment_table_string = "";
		if(!$linkcomment = $sql -> db_Select_gen($qry)){
			js_location(e_SELF);
		}else{
			$rowl = $sql->db_Fetch();
			$linkbutton_count   = ($rowl['link_button']) ?  $linkbutton_count + 1 : $linkbutton_count;
			$LINK_APPEND	= $lc -> parse_link_append($rowl);
			$subject		= $rowl['link_name'];
			$text = $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
			$text .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
			$text .= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);
			$ns->tablerender(LAN_LINKS_36, $text);

			$cobj->compose_comment("links_page", "comment", $qs[1], $width, $subject, $showrate=FALSE);
		}
	}
	return;
}

function displayLinkSubmit(){
	global $qs, $sql, $tp, $rs, $ns, $linkspage_pref, $link_shortcodes, $LINK_SUBMIT_TABLE, $LINK_SUBMIT_CAT;
	if ($link_cats = $sql->db_Select("links_page_cat", "*", " link_category_class REGEXP '".e_CLASS_REGEXP."' ")) {
		$LINK_SUBMIT_CAT = $rs -> form_select_open("cat_id");
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

	$order = $lc -> getOrder('cat');

	$qry = "
	SELECT lc.*
	FROM #links_page_cat AS lc
	WHERE lc.link_category_class REGEXP '".e_CLASS_REGEXP."'
	".$order."
	";

	if(!is_object($sql)){ $sql = new db; }
	if(!is_object($sql2)){ $sql2 = new db; }
	if (!$category_total = $sql->db_Select_gen($qry)){
		$lc -> show_message(LAN_LINKS_41, LAN_LINKS_30);
	}else{
		$link_main_table_string = "";
		$list = $sql->db_getList();
		foreach($list as $rowl) {
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

function displayNavigator($mode='')
{
	global $sql2, $ns, $lc, $tp, $cobj, $rowl, $qs, $linkspage_pref, $from, $link_shortcodes;
	global $LINK_NAVIGATOR_TABLE, $LINK_SORTORDER, $LINK_NAVIGATOR, $LINK_NAVIGATOR_TABLE_PRE, $LINK_NAVIGATOR_TABLE_POST;

	static $hasBeenShown = FALSE;
	
	if ($hasBeenShown) return '';
	$hasBeenShown = TRUE;

	if($mode == "cat")
	{
		if(isset($linkspage_pref['link_cat_sortorder']) && $linkspage_pref['link_cat_sortorder'])
		{
			$LINK_SORTORDER = $lc->showLinkSort('cat');
		}
	}
	else
	{
		if(isset($linkspage_pref['link_sortorder']) && $linkspage_pref['link_sortorder'])
		{
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
	return $text;
}

function displayCategoryLinks($mode=''){
	global $sql2, $ns, $lc, $tp, $cobj, $rowl, $qs, $linkspage_pref, $from, $link_shortcodes, $link_category_total;
	global $LINK_TABLE_START,$linkbutton_count, $LINK_TABLE,$link_category_total, $LINK_TABLE_CAPTION, $LINK_TABLE_END, $LINK_APPEND;

	$order			= $lc -> getOrder();
	$number			= ($linkspage_pref["link_nextprev_number"] ? $linkspage_pref["link_nextprev_number"] : "20");
	$nextprevquery	= ($mode && $linkspage_pref["link_nextprev"] ? "LIMIT ".intval($from).",".intval($number) : "");
	$cat			= ($mode ? " AND l.link_category='".intval($mode)."' " : "");
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
		$linkbutton_count = 0;
		$list = $sql2 -> db_getList();
  	    foreach($list as $rowl) {
			$linkbutton_count   = ($rowl['link_button']) ?  $linkbutton_count + 1 : $linkbutton_count;
			if($mode){
				$cat_name			= $rowl['link_category_name'];
				$cat_desc			= $rowl['link_category_description'];
				$LINK_APPEND		= $lc -> parse_link_append($rowl);
				$link_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
			}else{
				$arr[$rowl['link_category_id']][] = $rowl;
			}
		}
		if($mode)
		{
			$link_category_total	= $link_total;
			$link_table_start		= $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
			$link_table_end			= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);
			$text = $link_table_start.$link_table_string.$link_table_end;
			$caption = LAN_LINKS_32." ".$cat_name." ".($cat_desc ? " <i>[".$cat_desc."]</i>" : "");
			//number of links
			$caption .= " (<b title='".(ADMIN ? LAN_LINKS_2 : LAN_LINKS_1)."' >".$link_total."</b>".(ADMIN ? "/<b title='".(ADMIN ? LAN_LINKS_1 : "" )."' >".$link_total."</b>" : "").") ";
			$ns->tablerender($caption, $text);

			if(is_numeric($mode))
			{
				$lc->ShowNextPrev($from, $number, $link_total);
			}
		}
		else
		{
			$text = '';
			foreach($arr as $key => $value)
			{
				$link_table_string = "";
				$linkbutton_count = 0;
				$i=0;
				for($i=0;$i<count($value);$i++)
				{
					$rowl				= $value[$i];

					$linkbutton_count   = ($rowl['link_button']) ?  $linkbutton_count + 1 : $linkbutton_count;
					$cat_name			= $rowl['link_category_name'];
					$cat_desc			= $rowl['link_category_description'];
				 	$LINK_APPEND		= $lc -> parse_link_append($rowl);
					$link_table_string .= $tp -> parseTemplate($LINK_TABLE, FALSE, $link_shortcodes);
				}

				$link_category_total = count($value);
				$link_table_caption 	= $tp -> parseTemplate($LINK_TABLE_CAPTION, FALSE, $link_shortcodes);
				$link_table_start		= $tp -> parseTemplate($LINK_TABLE_START, FALSE, $link_shortcodes);
				$link_table_end			= $tp -> parseTemplate($LINK_TABLE_END, FALSE, $link_shortcodes);
				$text .= $link_table_start.$link_table_string.$link_table_end;

			}
//			$nav = $tp->parseTemplate("{NAVIGATOR}", FALSE, $link_shortcodes);		Navigator now positioned in template
//			$ns->tablerender($link_table_caption, $nav.$text);
			$ns->tablerender($link_table_caption, $text);
		}
	}
	return;
}

require_once(FOOTERF);




?>
