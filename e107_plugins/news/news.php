<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/news/news.php,v $
|     $Revision: 1.1.2.1 $
|     $Date: 2006-01-28 07:50:07 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");

$ix = new news;

$actions = explode(".", e_QUERY);

if(is_numeric($actions[0])) {
	// The link is a post
	$post_id = intval($actions[0]);
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		      LEFT JOIN #user AS u ON n.news_author = u.user_id
		      LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		      WHERE n.news_id = {$post_id}
		      AND n.news_class REGEXP '".e_CLASS_REGEXP."'
		      AND n.news_start < ".time()."
		      AND (n.news_end=0 || n.news_end > ".time().")
		      AND n.news_render_type < 2";

	$sql->db_Select_gen($query);
	$news_item = $sql -> db_getList();

	require_once(HEADERF);
	$ix->render_newsitem($news_item[1], "extend");
	require_once(FOOTERF);
	die();
}

$action = '';
if (!defined("ITEMVIEW")){
	if ($pref['newsposts']==""){
		define("ITEMVIEW", 15);
	} else {
		define("ITEMVIEW", $pref['newsposts']);
	}
}
if(ADMIN && file_exists("install.php")){ echo "<div class='installe' style='text-align:center'><b>*** ".LAN_NEWS_3." ***</b><br />".LAN_NEWS_4."</div><br /><br />"; }

$from = (!is_numeric($action) || !e_QUERY ? 0 : ($action ? $action : e_QUERY));

if (empty($order)){
	$order = "news_datestamp";
}
$order = $tp -> toDB($order, true);

$interval = 10;

$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_render_type<2" );

$interval = $pref['newsposts']-$pref['newsposts_archive'];

// Get number of news item to show
$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		  LEFT JOIN #user AS u ON n.news_author = u.user_id
		  LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		  WHERE n.news_class REGEXP '".e_CLASS_REGEXP."'
		  AND n.news_start < ".time()."
		  AND (n.news_end=0 || n.news_end>".time().")
		  AND n.news_render_type<2
		  ORDER BY n.news_sticky DESC, ".$order." DESC";

$sql->db_Select_gen($query);
$newsAr = $sql -> db_getList();

require_once(HEADERF);

$i= 1;
while(isset($newsAr[$i]) && $i <= $interval) {
	$news = $newsAr[$i];
	$thispostday = strftime("%j", $news['news_datestamp']);
	if ($newpostday != $thispostday && (isset($pref['news_newdateheader']) && $pref['news_newdateheader'])) {
		echo "<div class='".DATEHEADERCLASS."'>".strftime("%A %d %B %Y", $news['news_datestamp'])."</div>";
	}
	$newpostday = $thispostday;
	$news['category_id'] = $news['news_category'];
	if ($action == "item") {
		unset($news['news_render_type']);
	}
	$ix->render_newsitem($news);
	$i++;
}

require_once(FOOTERF);