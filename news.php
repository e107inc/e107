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
|     $Source: /cvs_backup/e107_0.7/news.php,v $
|     $Revision: 1.55 $
|     $Date: 2005-03-09 10:03:23 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
require_once(HEADERF);

if (isset($NEWSHEADER)) {
	require_once(FOOTERF);
	exit;
}
$cacheString = 'news.php_'.e_QUERY;
$action = '';
if (!defined("ITEMVIEW")){
	if ($pref['newsposts']==""){
		define("ITEMVIEW", 15);
	} else {
		define("ITEMVIEW", $pref['newsposts']);
	}
}
if(ADMIN && file_exists("install.php")){ echo "<div class='installe' style='text-align:center'><b>*** ".LAN_NEWS_3." ***</b><br />".LAN_NEWS_4."</div><br /><br />"; }

if (e_QUERY) {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
}

$from = (!is_numeric($action) || !e_QUERY ? 0 : ($action ? $action : e_QUERY));
if (isset($tmp[1]) && $tmp[1] == 'list') {
	$action = 'list';
	$from = intval($tmp[0]);
	$sub_action = intval($tmp[2]);
}
if ($action == 'all' || $action == 'cat') {
	$from = intval($tmp[2]);
	$sub_action = intval($tmp[1]);
}

$ix = new news;
if ($action == 'cat' || $action == 'all'){
	checkNewsCache($cacheString);
	ob_start();
	$qs = explode(".", e_QUERY);

	$category = $qs[1];
	if ($action == 'cat' && $category != 0)	{
		$gen = new convert;
		$sql->db_Select("news_category", "*", "category_id='$category'");
		$row = $sql->db_Fetch();
		extract($row);  // still required for the table-render.  :(
	}
	if ($action == 'all'){
		if(!defined("NEWSALL_LIMIT")){ define("NEWSALL_LIMIT",10); }
		// show archive of all news items using list-style template.
		$news_total = $sql->db_Count("news", "(*)", "WHERE news_class IN (".USERCLASS_LIST.")");
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")  ORDER BY n.news_datestamp DESC LIMIT $from,".NEWSALL_LIMIT;
		$category_name = "All";
	}
	elseif ($action == 'cat'){
		// show archive of all news items in a particular category using list-style template.   .
		$news_total = $sql->db_Count("news", "(*)", "WHERE news_class IN (".USERCLASS_LIST.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_category={$sub_action} ");
		if(!defined("NEWSLIST_LIMIT")){ define("NEWSLIST_LIMIT",10); }
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_category={$sub_action} ORDER BY n.news_datestamp DESC LIMIT $from,".NEWSLIST_LIMIT;
	}

	if(!$NEWSLISTSTYLE){
		$NEWSLISTSTYLE = "
		<div style='padding:3px;width:100%'>
		<table style='border-bottom:1px solid black;width:100%' cellpadding='0' cellspacing='0'>
		<tr>
		<td style='vertical-align:top;padding:3px;width:20px'>
		{NEWSCATICON}
		</td><td style='text-align:left;padding:3px'>
		{NEWSTITLELINK}
		<br />
		{NEWSSUMMARY}
		<span class='smalltext'>
		{NEWSDATE}
		{NEWSCOMMENTS}
		</span>
		</td><td style='width:55px'>
		{NEWSTHUMBNAIL}
		</td></tr></table>
		</div>\n";

	}
	$param['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
	$param['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
	$param['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
	$param['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : ICONSTYLE;

	$sql->db_Select_gen($query);
	while ($row = $sql->db_Fetch()) {
		$text .= $ix->parse_newstemplate($row,$NEWSLISTSTYLE,$param);
	}

	$amount = ($action == "all") ? NEWSALL_LIMIT : NEWSLIST_LIMIT;

	$icon = ($row['category_icon']) ? "<img src='".e_IMAGE."icons/".$row['category_icon']."' alt='' />" : "";
	$parms = $news_total.",".$amount.",".$from.",".e_SELF.'?'.$action.".".$sub_action.".[FROM]";
	$text .= ($news_total > $amount) ? LAN_NEWS_22."&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}") : "";
	$ns->tablerender(LAN_82." '".$category_name."'", $text);
	setNewsCache($cacheString);
	require_once(FOOTERF);
	exit;

}

if ($action == "extend") {

	checkNewsCache($cacheString);
	ob_start();
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_id={$sub_action}";
	$sql->db_Select_gen($query);
	$news = $sql->db_Fetch();
	$ix->render_newsitem($news);
	setNewsCache($cacheString);
	require_once(FOOTERF);
	exit;
}


// --->wmessage
if (!$pref['wmessage_sc']) {
	if (!defined("WMFLAG")) {
		$sql->db_Select("generic", "gen_chardata", "gen_type='wmessage' AND gen_intdata IN (".USERCLASS_LIST.") ORDER BY gen_intdata ASC");
		while ($row = $sql->db_Fetch()){
			$wmessage .= $tp->toHTML($row['gen_chardata'], TRUE, 'parse_sc')."<br />";
		}
	}
	if (isset($wmessage)) {
		if ($pref['wm_enclose']) {
			$ns->tablerender("", $wmessage, "wm");
		} else {
			echo $wmessage;
		}
	}
}
// --->wmessage end

if (isset($pref['nfp_display']) && $pref['nfp_display'] == 1){
	require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}

if (Empty($order)){
	$order = "news_datestamp";
}

if ($action == "list"){
	$sub_action = intval($sub_action);
	$news_total = $sql->db_Count("news", "(*)", "WHERE news_category=$sub_action AND news_class IN (".USERCLASS_LIST.") AND news_render_type!=2");
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type!=2 AND n.news_category={$sub_action} ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW;
}
elseif($action == "item")
{
	$sub_action = intval($sub_action);
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_id={$sub_action}";
}
elseif(strstr(e_QUERY, "month"))
{
	$tmp = explode(".", e_QUERY);
	$item = $tmp[1];
	$year = substr($item, 0, 4);
	$month = substr($item, 4);
	$startdate = mktime(0, 0, 0, $month, 1, $year);
	$lastday = date("t", $startdate);
	$enddate = mktime(23, 59, 59, $month, $lastday, $year);
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2 AND n.news_datestamp > $startdate AND n.news_datestamp < $enddate ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW;
}
elseif(strstr(e_QUERY, "day"))
{
	$tmp = explode(".", e_QUERY);
	$item = $tmp[1];
	$year = substr($item, 0, 4);
	$month = substr($item, 4, 2);
	$day = substr($item, 6, 2);
	$startdate = mktime(0, 0, 0, $month, $day, $year);
	$lastday = date("t", $startdate);
	$enddate = mktime(23, 59, 59, $month, $day, $year);
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2 AND n.news_datestamp > $startdate AND n.news_datestamp < $enddate ORDER BY ".$order." DESC LIMIT $from,".ITEMVIEW;
}
else
{
	$news_total = $sql->db_Count("news", "(*)", "WHERE news_class IN (".USERCLASS_LIST.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_render_type<2" );

	// #### changed for news archive ------------------------------------------------------------------------------
	if(!isset($pref['newsposts_archive']))
	{
		$pref['newsposts_archive'] = 0;
	}
	$interval = $pref['newsposts']-$pref['newsposts_archive'];
	$from2 = $interval+$from;
	$ITEMVIEW2 = ITEMVIEW-$interval;
	$ITEMVIEW1 = $interval;

	/*
	changes by jalist 19/01/05:
	altered database query to reduce calls to db
	*/

	// normal newsitems

	if(isset($pref['trackbackEnabled'])) {
		$query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon, COUNT(*) AS tbcount FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2
		GROUP by n.news_id ORDER BY news_sticky DESC, ".$order." DESC LIMIT $from,".$ITEMVIEW1;

		$query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2
		GROUP by n.news_id ORDER BY news_sticky DESC, ".$order." DESC LIMIT $from,".$ITEMVIEW1;
	}
	else
	{
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2 ORDER BY news_sticky DESC, ".$order." DESC LIMIT $from,".$ITEMVIEW1;

		// news archive
		$query2 = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE news_class IN (".USERCLASS_LIST.") AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") AND n.news_render_type<2 ORDER BY ".$order." DESC LIMIT $from2,".$ITEMVIEW2;
	}
	// #### END ---------------------------------------------------------------------------------------------------
}

checkNewsCache($cacheString, TRUE, TRUE);

/*
changes by jalist 03/02/2005:
news page templating
*/
if($pref['news_unstemplate'] && file_exists(THEME."news_template.php")) {
	// theme specific template required ...
	require_once(THEME."news_template.php");
	$newscolumns = (isset($NEWSCOLUMNS) ? $NEWSCOLUMNS : 1);
	$newspercolumn = (isset($NEWSITEMSPERCOLUMN) ? $NEWSITEMSPERCOLUMN : 10);
	$newsdata = array();
	if (!$sql->db_Select_gen($query)) {
		echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_462 : LAN_83)."</b></div><br /><br />";
	} else {
		$loop = 1;
		while($news = $sql->db_Fetch()) {
			$newsdata[$loop] .= $ix->render_newsitem($news, "return");
			$loop ++;
			if($loop > $newscolumns) {
				$loop = 1;
			}
		}
	}
	$loop = 1;
	foreach($newsdata as $data) {
		$var = "ITEMS$loop";
		$$var = $data;
		$loop++;
	}
	$text = preg_replace("/\{(.*?)\}/e", '$\1', $NEWSCLAYOUT);
	echo $text;

} else {
	/*
	changes by jalist 22/01/2005:
	added ability to add a new date header to news posts, turn on and off from news->prefs
	*/
	$newpostday = 0;
	$thispostday = 0;
	$pref['newsHeaderDate'] = 1;
	$gen = new convert();

	if (!defined("DATEHEADERCLASS")) {
		define("DATEHEADERCLASS", "nextprev");
		// if not defined in the theme, default class nextprev will be used for new date header
	}

	// #### normal newsitems, rendered via render_newsitem(), the $query is changed above (no other changes made) ---------
	ob_start();

	if (!$sql->db_Select_gen($query)) {
		echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_462 : LAN_83)."</b></div><br /><br />";
	} else {
		while ($news = $sql->db_Fetch()) {
			//        render new date header if pref selected ...
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
		}
	}
}

// ##### --------------------------------------------------------------------------------------------------------------

// #### new: news archive ---------------------------------------------------------------------------------------------
if ($action != "item" && $action != 'list' && $pref['newsposts_archive']) {
	// do not show the newsarchive on the news.php?item.X page (but only on the news mainpage)
	if ($sql->db_Select_gen($query2)) {
		while ($news2 = $sql->db_Fetch()) {
			if (check_class($news2['news_class'])) {
				if ($action == "item") {
					unset($news2['news_render_type']);
				}
				// Code from Lisa
				// copied from the rss creation, but added here to make sure the url for the newsitem is to the news.php?item.X
				// instead of the actual hyperlink that may have been added to a newstitle on creation
				$search = array();
				$replace = array();
				$search[0] = "/\<a href=\"(.*?)\">(.*?)<\/a>/si";
				$replace[0] = '\\2';
				$search[1] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
				$replace[1] = '\\2';
				$search[2] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
				$replace[2] = '\\2';
				$search[3] = "/\<a href=&quot;(.*?)&quot;>(.*?)<\/a>/si";
				$replace[3] = '\\2';
				$search[4] = "/\<a href=&#39;(.*?)&#39;>(.*?)<\/a>/si";
				$replace[4] = '\\2';
				$news2['news_title'] = preg_replace($search, $replace, $news2['news_title']);
				// End of code from Lisa

				$gen = new convert;
				$news2['news_datestamp'] = $gen->convert_date($news2['news_datestamp'], "short");

				$textnewsarchive .= "
					<div>
					<table style='width:98%;'>
					<tr>
					<td>
					<div><img src='".THEME."images/bullet2.gif' style='border:0px' alt='' /> <b><a href='news.php?item.".$news2['news_id']."'>".$news2['news_title']."</a></b> <span class='smalltext'><i><a href='".e_BASE."user.php?id.".$news2['user_id']."'>".$news2['user_name']."</a> @ (".$news2['news_datestamp'].") (".$news2['category_name'].")</i></span></div>
					</td>
					</tr>
					</table>
					</div>";
			}
		}
		$ns->tablerender($pref['newsposts_archive_title'], $textnewsarchive);
	}
}
// #### END -----------------------------------------------------------------------------------------------------------

if ($action != "item") {
	if (is_numeric($action)){
		$action = "";
	}
	$parms = $news_total.",".ITEMVIEW.",".$from.",".e_SELF.'?'."[FROM].".$action.(isset($sub_action) ? ".".$sub_action : "");
	$nextprev = ($news_total > ITEMVIEW) ? LAN_NEWS_22."&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}") : "";
	echo "<div class='nextprev' style='text-align:center'>".$nextprev."</div>";
}

if ($pref['nfp_display'] == 2) {
	require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}

// --  CNN Style Categories. ----
if (isset($pref['news_cats']) && $pref['news_cats'] == '1') {
    $text3 = $tp->toHTML("{NEWS_CATEGORIES}", TRUE, 'parse_sc');
 	$ns->tablerender("News Categories", $text3);
}

// ---------------------------
setNewsCache($cacheString);
require_once(FOOTERF);
// =========================================================================
function setNewsCache($cacheString) {
	global $pref, $e107cache;
	if ($pref['cachestatus']) {
		$cache = ob_get_contents();
		$e107cache->set($cacheString, $cache);
	}
	ob_end_flush();
}

function checkNewsCache($cacheString, $np = FALSE, $nfp = FALSE) {
	global $pref, $e107cache, $tp;
	$cache_data = $e107cache->retrieve($cacheString);
	if ($cache_data) {
		echo $cache_data;
		if ($nfp && $pref['nfp_display'] == 2) {
			require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
		}
		require_once(FOOTERF);
		exit;
	}
}

?>