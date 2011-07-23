<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."news_class.php");
//require_once(e_HANDLER."comment_class.php");
//$cobj = new comment;

if (isset($NEWSHEADER)) 
{
  require_once(HEADERF);
  require_once(FOOTERF);
  exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_news.php');		// Temporary

$cacheString = 'news.php_default_';
$action = '';
$sub_action = '';
$order = 'news_datestamp';
$newsfrom = 0;

if (!defined('ITEMVIEW'))
{
  define('ITEMVIEW', varset($pref['newsposts'],15));
}

if (e_QUERY) 
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];							// At least one parameter here
	$sub_action = varset($tmp[1],'');			// Usually a numeric category, or numeric news item number, but don't presume yet
//	$id = varset($tmp[2],'');					// ID of specific news item where required
	$newsfrom = intval(varset($tmp[2],0));		// Item number for first item on multi-page lists
	$cacheString = 'news.php_'.e_QUERY;
}

//$newsfrom = (!is_numeric($action) || !e_QUERY ? 0 : ($action ? $action : e_QUERY));

// Usually the first query parameter is the action.
// For any of the 'list' modes (inc month, day), the action being second is a legacy situation
// .... which can hopefully go sometime
if (is_numeric($action) && isset($tmp[1]) && (($tmp[1] == 'list') || ($tmp[1] == 'month') || ($tmp[1] == 'day')))
{
	$action = $tmp[1];
	$sub_action = varset($tmp[0],'');
}



if ($action == 'all' || $action == 'cat') 
{
	$sub_action = intval(varset($tmp[1],0));
}
unset($tmp);
/*
Variables Used:
	$action - the basic display format/filter
	$sub_action - category number or news item number
	$newsfrom - first item number in list (default 0) - derived from nextprev
	$order - sets the listing order for 'list' format
*/


$ix = new news;
$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";

//------------------------------------------------------
//		DISPLAY NEWS IN 'CATEGORY' FORMAT HERE
//------------------------------------------------------
// Just title and a few other details
if ($action == 'cat' || $action == 'all')
{	// --> Cache
	if($newsCachedPage = checkCache($cacheString))
	{
		require_once(HEADERF);
		renderCache($newsCachedPage, TRUE);
	}
	// <-- Cache


	$category = intval($sub_action);
	if ($action == 'cat' && $category != 0)	
	{
		$gen = new convert;
		$sql->db_Select("news_category", "*", "category_id='{$category}'");
		$row = $sql->db_Fetch();
		extract($row);  // still required for the table-render.  :(
	}
	if ($action == 'all')
	{
		if(!defined("NEWSALL_LIMIT")) { define("NEWSALL_LIMIT",10); }
		// show archive of all news items using list-style template.
		$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")");
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") AND n.news_start < ".time()."
		AND (n.news_end=0 || n.news_end>".time().")  
		ORDER BY n.news_sticky DESC, n.news_datestamp DESC 
		LIMIT ".intval($newsfrom).",".NEWSALL_LIMIT;
		$category_name = "All";
	}
	elseif ($action == 'cat')
	{
		// show archive of all news items in a particular category using list-style template.
		$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_category=".intval($sub_action));
		if(!defined("NEWSLIST_LIMIT")) { define("NEWSLIST_LIMIT",10); }
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_category=".intval($sub_action)." 
		ORDER BY n.news_datestamp DESC 
		LIMIT ".intval($newsfrom).",".NEWSLIST_LIMIT;
	}

	if($category_name)
	{
		define('e_PAGETITLE', $tp->toHTML($category_name,FALSE,'TITLE'));
	}

	require_once(HEADERF);

	if(!$NEWSLISTSTYLE)
	{
		$NEWSLISTSTYLE = "
		<div style='padding:3px;width:100%'>
		<table style='border-bottom:1px solid black;width:100%' cellpadding='0' cellspacing='0'>
		<tr>
		<td style='vertical-align:top;padding:3px;width:20px'>
		{NEWSCATICON}
		</td><td style='text-align:left;padding:3px'>
		{NEWSTITLELINK=extend}
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
	$param['itemlink'] = (defined('NEWSLIST_ITEMLINK')) ? NEWSLIST_ITEMLINK : '';
	$param['thumbnail'] =(defined('NEWSLIST_THUMB')) ? NEWSLIST_THUMB : 'border:0px';
	$param['catlink']  = (defined('NEWSLIST_CATLINK')) ? NEWSLIST_CATLINK : '';
	$param['caticon'] =  (defined('NEWSLIST_CATICON')) ? NEWSLIST_CATICON : ICONSTYLE;
	$sql->db_Select_gen($query);
	$newsList = $sql->db_getList();
	foreach($newsList as $row)
	{
	  $text .= $ix->render_newsitem($row, 'return', '', $NEWSLISTSTYLE, $param);
	}

	$amount = ($action == "all") ? NEWSALL_LIMIT : NEWSLIST_LIMIT;

	$icon = ($row['category_icon']) ? "<img src='".e_IMAGE."icons/".$row['category_icon']."' alt='' />" : "";
	$parms = $news_total.",".$amount.",".$newsfrom.",".e_SELF.'?'.$action.".".$category.".[FROM]";
	$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";

    if(!$NEWSLISTTITLE)
	{
		$NEWSLISTTITLE = LAN_NEWS_82." '".$tp->toHTML($category_name,FALSE,'TITLE')."'";
	}
	else
	{
    	$NEWSLISTTITLE = str_replace("{NEWSCATEGORY}",$tp->toHTML($category_name,FALSE,'TITLE'),$NEWSLISTTITLE);
	}
	$text .= "<div style='text-align:center;'><a href='".e_SELF."'>".LAN_NEWS_84."</a></div>";
	ob_start();
	$ns->tablerender($NEWSLISTTITLE, $text);
	$cache_data = ob_get_flush();
	setNewsCache($cacheString, $cache_data);
	require_once(FOOTERF);
	exit;
}



//------------------------------------------------------
//		DISPLAY SINGLE ITEM IN EXTENDED FORMAT HERE
//------------------------------------------------------
if ($action == 'extend') 
{	// --> Cache
	if($newsCachedPage = checkCache($cacheString))
	{
		require_once(HEADERF);
		renderCache($newsCachedPage, TRUE);		// This exits if cache used
	}
	// <-- Cache

	if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled']) 
	{
		$query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
			WHERE n.news_id=".intval($sub_action)." AND n.news_class REGEXP '".e_CLASS_REGEXP."' 
			AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().') ';
	}
	else
	{
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_id=".intval($sub_action)." AND n.news_class REGEXP '".e_CLASS_REGEXP."' 
			AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") ";
	}
	if ($sql->db_Select_gen($query))
	{
		$news = $sql->db_Fetch();
		$id = $news['news_category'];		// Use category of this news item to generate next/prev links

		if($news['news_title'])
		{
		  if($pref['meta_news_summary'] && $news['news_title'])
		  {
				setNewsMeta('extend',$news);
		  }
		  
		  define("e_PAGETITLE",$news['news_title']);
		}

		if (TRUE)
		{
			/* Added by nlStart - show links to previous and next news */
			if (!isset($news['news_extended'])) $news['news_extended'] = '';
			
			$news['news_extended'].="<div style='text-align:center;'><a class='news-extended-category-link' href='".e_SELF."?cat.".$id."'>".LAN_NEWS_85."</a> &nbsp; <a class='news-extended-overview-link' href='".e_SELF."'>".LAN_NEWS_84."</a></div>";
			
			$prev_query = "SELECT news_id, news_title FROM `#news`
				WHERE `news_id` < ".intval($sub_action)." AND `news_category`=".$id." AND `news_class` REGEXP '".e_CLASS_REGEXP."' 
				AND NOT (`news_class` REGEXP ".$nobody_regexp.") 
				AND `news_start` < ".time()." AND (`news_end`=0 || `news_end` > ".time().') ORDER BY `news_id` DESC LIMIT 1';
			
			$sql->db_Select_gen($prev_query);
			$prev_news = $sql->db_Fetch();
			
			if ($prev_news)
			{
				$news['news_extended'].="<div class='news-extended-older' style='float:left;'><a class='news-extended-older' href='".e_SELF."?extend.".$prev_news['news_id']."'>".LAN_NEWS_86."</a></div>";
			}
			
			$next_query = "SELECT news_id, news_title FROM `#news` AS n
				WHERE `news_id` > ".intval($sub_action)." AND `news_category` = ".$id." AND `news_class` REGEXP '".e_CLASS_REGEXP."' 
				AND NOT (`news_class` REGEXP ".$nobody_regexp.") 
				AND `news_start` < ".time()." AND (`news_end`=0 || `news_end` > ".time().') ORDER BY `news_id` ASC LIMIT 1';
			
			$sql->db_Select_gen($next_query);
			$next_news = $sql->db_Fetch();
			
			if ($next_news)
			{
				$news['news_extended'].="<div class='news-extended-newer' style='float:right;'><a class='news-extended-newer' href='".e_SELF."?extend.".$next_news['news_id']."'>".LAN_NEWS_87."</a></div>";
			}
			$news['news_extended'].="<br /><br />";
		}
		
		require_once(HEADERF);
		ob_start();
		$ix->render_newsitem($news, 'extend');
		$cache_data = ob_get_contents();
		ob_end_flush();
		setNewsCache($cacheString, $cache_data);
		require_once(FOOTERF);
		exit;
	}
	else
	{
		$action = 'default';
	}
}


//------------------------------------------------------
//			DISPLAY NEWS IN LIST FORMAT HERE
//------------------------------------------------------
// Show title, author, first part of news item...
if (empty($order))
{
  $order = 'news_datestamp';
}
$order = $tp -> toDB($order, true);

$interval = $pref['newsposts'];

switch ($action)
{
  case "list" :
	$sub_action = intval($sub_action);
	$news_total = $sql->db_Count("news", "(*)", "WHERE news_category={$sub_action} AND news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")");
//	$query = "SELECT  SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
	$query = "SELECT  n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_category={$sub_action} 
		ORDER BY n.news_sticky DESC,".$order." DESC LIMIT ".intval($newsfrom).",".ITEMVIEW;
	break;


  case "item" :
	$sub_action = intval($sub_action);
	$news_total = 1;
	if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled']) 
	{
	  $query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_id={$sub_action} AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		GROUP by n.news_id";
	}
	else
	{ 
	  $query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_id={$sub_action} AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")";
	}
	break;

  case "month" :
  case "day" :
	$item = $tp -> toDB($sub_action).'20000101';
	$year = substr($item, 0, 4);
	$month = substr($item, 4,2);
	if ($action == 'day')
	{
	  $day = substr($item, 6, 2);
	  $lastday = $day;
	}
	else
	{	// A month's worth
	  $day = 1;
	  $lastday = date("t", $startdate);
	}
	$startdate = mktime(0, 0, 0, $month, $day, $year);
	$enddate = mktime(23, 59, 59, $month, $lastday, $year);
	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_render_type<2 AND n.news_datestamp > {$startdate} AND n.news_datestamp < {$enddate} 
		ORDER BY ".$order." DESC LIMIT ".intval($newsfrom).",".ITEMVIEW;

	$news_total = $sql->db_Count("news AS n", "(*)", "WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.") 
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_render_type<2 AND n.news_datestamp > {$startdate} AND n.news_datestamp < {$enddate}");
	break;

  case 'default' :
  default :
    $action = '';
	$cacheString = 'news.php_default_';		// Make sure its sensible
	$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_render_type<2" );

	if(!isset($pref['newsposts_archive']))
	{
		$pref['newsposts_archive'] = 0;
	}
	$interval = $pref['newsposts']-$pref['newsposts_archive'];		// Number of 'full' posts to show

	// Get number of news item to show
	if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled']) 
	{
		$query = "SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon, COUNT(*) AS tbcount FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND n.news_render_type<2
		GROUP by n.news_id
		ORDER BY news_sticky DESC, ".$order." DESC LIMIT ".intval($newsfrom).",".$pref['newsposts'];
	}
	else
	{
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND n.news_render_type<2
		ORDER BY n.news_sticky DESC, ".$order." DESC LIMIT ".intval($newsfrom).",".$pref['newsposts'];
	}
}	// END - switch($action)


if($newsCachedPage = checkCache($cacheString)) // normal news front-page - with cache.
{
	require_once(HEADERF);

	if(!$action)
	{
		if (isset($pref['fb_active']))
		{
			require_once(e_PLUGIN."featurebox/featurebox.php");
		}
		if (isset($pref['nfp_display']) && $pref['nfp_display'] == 1)
		{
			require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
		}

	}

	//news archive
	if ($action != "item" && $action != 'list' && $pref['newsposts_archive']) {
		if ($sql->db_Select_gen($query)) {
			$newsAr = $sql -> db_getList();
			if($newsarchive = checkCache('newsarchive')){
				$newsCachedPage = $newsCachedPage.$newsarchive;
			}else{
				show_newsarchive($newsAr,$interval);
			}
		}
	}
	renderCache($newsCachedPage, TRUE);
}


//if (!($news_total = $sql->db_Select_gen($query))) 
if (!$sql->db_Select_gen($query))
{  // No news items
  require_once(HEADERF);
  echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_NEWS_462 : LAN_NEWS_83)."</b></div><br /><br />";
  require_once(FOOTERF);
  exit;
} 

$newsAr = $sql -> db_getList();


$p_title = ($action == "item") ? $newsAr[1]['news_title'] : $tp->toHTML($newsAr[1]['category_name'],FALSE,'TITLE');

if($action != "" && !is_numeric($action))
{
    if($action == "item" && $pref['meta_news_summary'] && $newsAr[1]['news_title'])
	{
		setNewsMeta('item',$newsAr[1]);
		// define("META_DESCRIPTION",SITENAME.": ".$newsAr[1]['news_title']." - ".$newsAr[1]['news_summary']);
	}
	define("e_PAGETITLE", $p_title);
}

require_once(HEADERF);
if(!$action)
{
	if (isset($pref['fb_active'])){   // --->feature box
		require_once(e_PLUGIN."featurebox/featurebox.php");
	}

	if (isset($pref['nfp_display']) && $pref['nfp_display'] == 1){
		require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
	}
}

if(isset($pref['news_unstemplate']) && $pref['news_unstemplate'] && file_exists(THEME."news_template.php")) 
{
	// theme specific template required ...
	require_once(THEME."news_template.php");

	if($ALTERNATECLASS1)
	{
	  return TRUE;
	}

	$newscolumns = (isset($NEWSCOLUMNS) ? $NEWSCOLUMNS : 1);
	$newspercolumn = (isset($NEWSITEMSPERCOLUMN) ? $NEWSITEMSPERCOLUMN : 10);
	$newsdata = array();
	$loop = 1;
	foreach($newsAr as $news) {

		if(is_array($ALTERNATECLASSES)) {
			$newsdata[$loop] .= "<div class='{$ALTERNATECLASSES[0]}'>".$ix->render_newsitem($news, "return")."</div>";
			$ALTERNATECLASSES = array_reverse($ALTERNATECLASSES);
		} else {
			$newsdata[$loop] .= $ix->render_newsitem($news, "return");
		}
		$loop ++;
		if($loop > $newscolumns) {
			$loop = 1;
		}
	}
	$loop = 1;
	foreach($newsdata as $data) {
		$var = "ITEMS{$loop}";
		$$var = $data;
		$loop ++;
	}
	$text = preg_replace("/\{(.*?)\}/e", '$\1', $NEWSCLAYOUT);

	require_once(HEADERF);
	$sub_action = intval($sub_action);
	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";
    $nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
    $text .= ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");
//    $text=''.$text.'<center>'.$nextprev.'</center>';
   
    echo $text;
    setNewsCache($cacheString, $text);
} 
else 
{
	ob_start();

	$newpostday = 0;
	$thispostday = 0;
	$pref['newsHeaderDate'] = 1;
	$gen = new convert();

	if (!defined("DATEHEADERCLASS")) {
		define("DATEHEADERCLASS", "nextprev");
		// if not defined in the theme, default class nextprev will be used for new date header
	}

	// #### normal newsitems, rendered via render_newsitem(), the $query is changed above (no other changes made) ---------

	$i= 1;
	while(isset($newsAr[$i]) && $i <= $interval) {
		$news = $newsAr[$i];
		//        render new date header if pref selected ...
		$thispostday = strftime("%j", $news['news_datestamp']);
		if ($newpostday != $thispostday && (isset($pref['news_newdateheader']) && $pref['news_newdateheader'])) 
		{
		  echo "<div class='".DATEHEADERCLASS."'>".strftime("%A %d %B %Y", $news['news_datestamp'])."</div>";
		}
		$newpostday = $thispostday;
		$news['category_id'] = $news['news_category'];
		if ($action == "item") 
		{
		  unset($news['news_render_type']);
		}

		$ix->render_newsitem($news);
		$i++;
	}
	$sub_action = intval($sub_action);
	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";
	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
 	echo ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");

	$cache_data = ob_get_clean();
	require_once(HEADERF);
	echo $cache_data;
	setNewsCache($cacheString, $cache_data);
}

// ##### --------------------------------------------------------------------------------------------------------------

function show_newsarchive($newsAr, $i = 1)
{
	global $ns, $gen, $pref, $tp, $news_archive_shortcodes, $NEWSARCHIVE, $news2;

	// do not show the news archive on the news.php?item.X page (but only on the news mainpage)
	require_once(e_FILE.'shortcode/batch/news_archives.php');

	$textnewsarchive = '';
	ob_start();

	$i++;			// First entry to show
	while(isset($newsAr[$i]))
	{
		$news2 = $newsAr[$i];
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


		if(!$NEWSARCHIVE){
			$NEWSARCHIVE ="<div>
					<table style='width:98%;'>
					<tr>
					<td>
					<div>{ARCHIVE_BULLET} <b>{ARCHIVE_LINK}</b> <span class='smalltext'><i>{ARCHIVE_AUTHOR} @ ({ARCHIVE_DATESTAMP}) ({ARCHIVE_CATEGORY})</i></span></div>
					</td>
					</tr>
					</table>
					</div>";
		}

		$textnewsarchive .= $tp->parseTemplate($NEWSARCHIVE, FALSE, $news_archive_shortcodes);
		$i++;
	}
	$ns->tablerender($pref['newsposts_archive_title'], $textnewsarchive, 'news_archive');
	$newsarchive = ob_get_contents();
	ob_end_flush(); // dump collected data
	setNewsCache('newsarchive', $newsarchive);
}

// #### new: news archive ---------------------------------------------------------------------------------------------
if ($action != "item" && $action != 'list' && $pref['newsposts_archive']) 
{
  show_newsarchive($newsAr,$interval);
}
// #### END -----------------------------------------------------------------------------------------------------------

if ($action != "item") 
{
	if (is_numeric($action))
	{
		$action = "";
	}
 //	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'."[FROM].".$action.(isset($sub_action) ? ".".$sub_action : "");
 //	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
 //	echo ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");
}

if(is_dir("remotefile")) {
	require_once(e_HANDLER."file_class.php");
	$file = new e_file;
	$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*', 'Readme.txt');
	$crem = $file -> get_files(e_BASE."remotefile", "", $reject);
	if(count($crem)) {
		foreach($crem as $loadrem) {
			if(strstr($loadrem['fname'], "load_")) {
				require_once(e_BASE."remotefile/".$loadrem['fname']);
			}
		}
	}
}

if (isset($pref['nfp_display']) && $pref['nfp_display'] == 2) {
	require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}

render_newscats();

require_once(FOOTERF);


// =========================================================================
function setNewsCache($cache_tag, $cache_data) {
	global $e107cache;
	$e107cache->set($cache_tag, $cache_data);
	$e107cache->set($cache_tag."_title", defined("e_PAGETITLE") ? e_PAGETITLE : '');
	$e107cache->set($cache_tag."_diz", defined("META_DESCRIPTION") ? META_DESCRIPTION : '');
	$e107cache->set($cache_tag."_og", defined("META_OG") ? META_OG : '');
}

/**
 * Mode: extend or item
 */
function setNewsMeta($mode,$news)
{
	if($news['news_thumbnail'])
	{
		$image = (substr($news['news_thumbnail'],0,3)=="{e_") ? $tp->replaceConstants($news['news_thumbnail']) : SITEURL.e_IMAGE."newspost_images/".$news['news_thumbnail'];	
	}
	else
	{
		$image = "";
	}
		
	$og_array = array(
		'title'			=> $news['news_title'],
		'type'			=> 'article',		  		
		'url'			=> e_SELF."?".$mode.".".$news['news_id'],
		'image'			=> ($image) ? $image : '',
		'description' 	=> $news['news_summary'],
		'site_name'		=> SITENAME
	);
		  	
	define('META_OG',serialize($og_array));
	define('META_DESCRIPTION',SITENAME.': '.$news['news_title'].' - '.$news['news_summary']);
}

function checkCache($cacheString){
	global $pref,$e107cache;
	$cache_data = $e107cache->retrieve($cacheString);
	$cache_title = $e107cache->retrieve($cacheString."_title");
	$cache_diz = $e107cache->retrieve($cacheString."_diz");
	$cache_og = $e107cache->retrieve($cacheString."_og");
	
	$etitle = ($cache_title != "e_PAGETITLE") ? $cache_title : "";
	$ediz	= ($cache_diz != "META_DESCRIPTION") ? $cache_diz : "";
	$og		= ($cache_og != "META_OG") ? $cache_og : "";
	
	if($etitle)
	{
		define(e_PAGETITLE,$etitle);
	}
	
	if($ediz)
	{
    	define("META_DESCRIPTION",$ediz);
	}
	
	if($og)
	{
		define("META_OG",$og);
	}	

	if ($cache_data) {
		return $cache_data;
	} else {
		return false;
	}
}

function renderCache($cache, $nfp = FALSE){
	global $pref,$tp,$sql,$CUSTOMFOOTER, $FOOTER,$cust_footer,$ph;
	global $db_debug,$ns,$eTraffic,$eTimingStart, $error_handler, $db_time, $sql2, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb,$e107;
	echo $cache;
	if (isset($nfp) && isset($pref['nfp_display']) && $pref['nfp_display'] == 2) {
		require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
	}
	render_newscats();
	require_once(FOOTERF);
	exit;
}

function render_newscats(){  // --  CNN Style Categories. ----
	global $pref,$ns,$tp;
	if (isset($pref['news_cats']) && $pref['news_cats'] == '1') {
		$text3 = $tp->toHTML("{NEWS_CATEGORIES}", TRUE, 'parse_sc,nobreak,emotes_off,no_make_clickable');
		$ns->tablerender(LAN_NEWS_23, $text3, 'news_cat');
	}
}

?>