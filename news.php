<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News frontend
 *
 * $URL$
 * $Id$
*/
/**
 *	@package    e107
 *	@subpackage	user
 *	@version 	$Id$;
 *
 *	News front page display
 */

require_once("class2.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

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
// XXX remove ITEMVIEW?
if(!defined("NEWSALL_LIMIT")) 
{
	define("NEWSALL_LIMIT",varset($pref['newsposts'],15)); 
}

if (e_QUERY) //TODO add support for $_GET['cat'] and $_GET['mode'] and phase-out the x.x.x format. 
{

	$tmp = explode(".",e_QUERY);
	$action = $tmp[0];						// At least one parameter here
	$sub_action = varset($tmp[1],'');			// Usually a numeric category, or numeric news item number, but don't presume yet
	//	$id = varset($tmp[2],'');					// ID of specific news item where required
	$newsfrom = intval(varset($tmp[2],0));	// Item number for first item on multi-page lists
	$cacheString = 'news.php_'.e_QUERY;
}

//$newsfrom = (!is_numeric($action) || !e_QUERY ? 0 : ($action ? $action : e_QUERY));

// Usually the first query parameter is the action.
// For any of the 'list' modes (inc month, day), the action being second is a legacy situation
// .... which can hopefully go sometime
//SecretR: Gone, gone...
if (is_numeric($action) && isset($tmp[1]) && (($tmp[1] == 'list') || ($tmp[1] == 'month') || ($tmp[1] == 'day')))
{
	$action = $tmp[1];
	$sub_action = varset($tmp[0],'');
}



if ($action == 'all' || $action == 'cat')
{
	$sub_action = intval(varset($tmp[1],0));
}

/*
Variables Used:
$action - the basic display format/filter
$sub_action - category number or news item number
$newsfrom - first item number in list (default 0) - derived from nextprev
$order - sets the listing order for 'list' format
*/


$ix = new news;
$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";

// URL settings (nextprev)
	$newsUrlparms = array('page' => '--FROM--');
	if($sub_action)
	{
		
		switch ($action) 
		{
			case 'list':
				$newsUrlparms['id'] = $sub_action;
				$newsRoute = 'list/category';
			break;
			
			case 'cat':	
				$newsUrlparms['id'] = $sub_action;
				$newsRoute = 'list/short';
			break;
			
			case 'day':	
			case 'month':
				$newsUrlparms['id'] = $sub_action;
				$newsRoute = 'list/'.$action;
			break;
			
			default:
				$newsRoute = 'list/items';
			break;
		}
	}
	elseif($action == 'all')
	{
		$newsRoute = 'list/all';
		$newsUrlparms['id'] = $sub_action;
	}
	else $newsRoute = 'list/items';
	$newsRoute = 'news/'.$newsRoute;

//------------------------------------------------------
//		DISPLAY NEWS IN 'CATEGORY' FORMAT HERE
//------------------------------------------------------
// Just title and a few other details

if ($action == 'cat' || $action == 'all' || vartrue($_GET['tag']))
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
		// show archive of all news items using list-style template.
		$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")");
		$query = "
		SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
		nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
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
		$query = "
		SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
		nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_category=".intval($sub_action)."
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		ORDER BY n.news_datestamp DESC
		LIMIT ".intval($newsfrom).",".NEWSLIST_LIMIT;
	}
	elseif(vartrue($_GET['tag']))
	{
		$tagsearch = preg_replace('#[^a-zA-Z0-9\-]#','', $_GET['tag']);
		if(!defined("NEWSLIST_LIMIT")) { define("NEWSLIST_LIMIT",10); }
		$query = "
		SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
		nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_meta_keywords LIKE '%".$tagsearch."%'
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		ORDER BY n.news_datestamp DESC
		LIMIT ".intval($newsfrom).",".NEWSLIST_LIMIT;	
		$category_name = 'Tag: "'.$tagsearch.'"';
	}

	$newsList = array();
	if($sql->db_Select_gen($query))
	{
		$newsList = $sql->db_getList();
	}

	if($action == 'cat') setNewsFrontMeta($newsList[1], 'category');
	elseif($category_name)
	{
		define('e_PAGETITLE', $tp->toHTML($category_name,FALSE,'TITLE'));
	}

	$currentNewsAction = $action;
	require_once(HEADERF);
	$action = $currentNewsAction;

	/*
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
	
		}*/

	if(vartrue($NEWSLISTSTYLE))
	{
		 $template =  $NEWSLISTSTYLE;
	}
	else 
	{
		$tmp = e107::getTemplate('news', 'news', 'list');
		$template = $tmp['item'];
		unset($tmp);
	}

	$param = array();
	$param['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
	$param['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
	$param['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
	$param['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : ICONSTYLE;
	$param['current_action'] = $action;

	// NEW - allow news batch shortcode override (e.g. e107::getScBatch('news', 'myplugin', true); )
	e107::getEvent()->trigger('news_list_parse', $newsList);

	foreach($newsList as $row)
	{
		$text .= $ix->render_newsitem($row, 'return', '', $template, $param);
	}

	$icon = ($row['category_icon']) ? "<img src='".e_IMAGE."icons/".$row['category_icon']."' alt='' />" : "";

	// Deprecated.
	// $parms = $news_total.",".$amount.",".$newsfrom.",".$e107->url->getUrl('core:news', 'main', "action=nextprev&to_action={$action}&subaction={$category}");
	//	$parms = $news_total.",".$amount.",".$newsfrom.",".e_SELF.'?'.$action.".".$category.".[FROM]";
	//
	//	$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";

	$amount = NEWSLIST_LIMIT;
	$nitems = defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
	$url = rawurlencode(e107::getUrl()->create($newsRoute, $newsUrlparms));
	$parms  = 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$newsfrom.$nitems.'&url='.$url;
	$text  .= $tp->parseTemplate("{NEXTPREV={$parms}}");

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
	$ns->tablerender($NEWSLISTTITLE, $text, 'news');
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
		$query = "
	  	SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
		nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
	  	FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_id=".intval($sub_action)." AND n.news_class REGEXP '".e_CLASS_REGEXP."'
		AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		GROUP by n.news_id";
	}
	else
	{
		$query = "
	  	SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
		nc.category_meta_description
	  	FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."'
		AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()."
		AND (n.news_end=0 || n.news_end>".time().")
		AND n.news_id=".intval($sub_action);
	}
	if ($sql->db_Select_gen($query))
	{
		$news = $sql->db_Fetch();
		$id = $news['news_category'];		// Use category of this news item to generate next/prev links

		//***NEW [SecretR] - comments handled inside now
		e107::setRegistry('news/page_allow_comments', !$news['news_allow_comments']);
		if(!$news['news_allow_comments'] && isset($_POST['commentsubmit']))
		{
			$pid = intval(varset($_POST['pid'], 0));				// ID of the specific comment being edited (nested comments - replies)

			$clean_authorname = $_POST['author_name'];
			$clean_comment = $_POST['comment'];
			$clean_subject = $_POST['subject'];

			e107::getSingleton('comment')->enter_comment($clean_authorname, $clean_comment, 'news', $sub_action, $pid, $clean_subject);
		}

		//More SEO
		setNewsFrontMeta($news);
		/*
		if($news['news_title'])
		{
		if($pref['meta_news_summary'] && $news['news_title'])
		{
		define("META_DESCRIPTION",SITENAME.": ".$news['news_title']." - ".$news['news_summary']);
		}
		define("e_PAGETITLE",$news['news_title']);
		}*/
		/* FIXME - better implementation: cache, shortcodes, do it inside the model/shortcode class itself.
		if (TRUE)
		{
		// Added by nlStart - show links to previous and next news
		if (!isset($news['news_extended'])) $news['news_extended'] = '';
		$news['news_extended'].="<div style='text-align:center;'><a href='".e_SELF."?cat.".$id."'>".LAN_NEWS_85."</a> &nbsp; <a href='".e_SELF."'>".LAN_NEWS_84."</a></div>";
		$prev_query = "SELECT news_id, news_title FROM `#news`
		WHERE `news_id` < ".intval($sub_action)." AND `news_category`=".$id." AND `news_class` REGEXP '".e_CLASS_REGEXP."'
		AND NOT (`news_class` REGEXP ".$nobody_regexp.")
		AND `news_start` < ".time()." AND (`news_end`=0 || `news_end` > ".time().') ORDER BY `news_id` DESC LIMIT 1';
		$sql->db_Select_gen($prev_query);
		$prev_news = $sql->db_Fetch();
		if ($prev_news)
		{
		$news['news_extended'].="<div style='float:right;'><a href='".e_SELF."?extend.".$prev_news['news_id']."'>".LAN_NEWS_86."</a></div>";
		}
		$next_query = "SELECT news_id, news_title FROM `#news` AS n
		WHERE `news_id` > ".intval($sub_action)." AND `news_category` = ".$id." AND `news_class` REGEXP '".e_CLASS_REGEXP."'
		AND NOT (`news_class` REGEXP ".$nobody_regexp.")
		AND `news_start` < ".time()." AND (`news_end`=0 || `news_end` > ".time().') ORDER BY `news_id` ASC LIMIT 1';
		$sql->db_Select_gen($next_query);
		$next_news = $sql->db_Fetch();
		if ($next_news)
		{
		$news['news_extended'].="<div style='float:left;'><a href='".e_SELF."?extend.".$next_news['news_id']."'>".LAN_NEWS_87."</a></div>";
		}
		$news['news_extended'].="<br /><br />";
		}*/

		$currentNewsAction = $action;
		require_once(HEADERF);
		$action = $currentNewsAction;

		$param = array();
		$param['current_action'] = $action;
		
		if(vartrue($NEWSSTYLE)) 
		{
			$template =  $NEWSSTYLE;
		}
		elseif(function_exists("news_style")) // BC
		{
			$template = news_style($news, 'extend', $param);	
		}
		else 
		{
			$tmp = e107::getTemplate('news', 'news', 'view');
			$template = $tmp['item'];
			unset($tmp);
		}
		ob_start();
		$ix->render_newsitem($news, 'extend', '', $template, $param);
		if(e107::getRegistry('news/page_allow_comments'))
		{
			global $comment_edit_query; //FIXME - kill me
			$comment_edit_query = 'comment.news.'.$news['news_id'];
			e107::getSingleton('comment')->compose_comment('news', 'comment', $news['news_id'], null, $news['news_title'], FALSE);
		}
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
$order = $tp -> toDB($order, true);			/// @todo - try not to use toDB() - triggers prefilter

$interval = $pref['newsposts'];

switch ($action)
{
	case "list" :
		$sub_action = intval($sub_action);
		//	$news_total = $sql->db_Count("news", "(*)", "WHERE news_category={$sub_action} AND news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")");
		$query = "
		SELECT  SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
		nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
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
			$query = "
	  	SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
		nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_id={$sub_action} AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		GROUP by n.news_id";
		}
		else
		{
			$query = "
	  	SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
		nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
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

		$query = "
		SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
		nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type)) AND n.news_datestamp BETWEEN {$startdate} AND {$enddate}
		ORDER BY ".$order." DESC LIMIT ".intval($newsfrom).",".ITEMVIEW;
		break;

	case 'default' :
	default :
		//$action = '';
		$cacheString = 'news.php_default_';		// Make sure its sensible
		//	$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_render_type<2" );

		if(!isset($pref['newsposts_archive']))
		{
			$pref['newsposts_archive'] = 0;
		}
		$interval = $pref['newsposts']-$pref['newsposts_archive'];		// Number of 'full' posts to show

		// Get number of news item to show
		if(isset($pref['trackbackEnabled']) && $pref['trackbackEnabled']) {
			$query = "
		SELECT SQL_CALC_FOUND_ROWS COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id
		nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords, nc.category_meta_description,
		COUNT(*) AS tbcount
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type))
		GROUP by n.news_id
		ORDER BY news_sticky DESC, ".$order." DESC LIMIT ".intval($newsfrom).",".ITEMVIEW;
		}
		else
		{
			$query = "
		SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
		nc.category_meta_keywords, nc.category_meta_description
		FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
		AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type))
		ORDER BY n.news_sticky DESC, ".$order." DESC LIMIT ".intval($newsfrom).",".ITEMVIEW;
		}
}	// END - switch($action)


if($newsCachedPage = checkCache($cacheString)) // normal news front-page - with cache.
{
	require_once(HEADERF);

	if(!$action)
	{
		// Removed, themes should use {FEATUREBOX} shortcode instead
		//		if (isset($pref['fb_active']))
		//		{
		//			require_once(e_PLUGIN."featurebox/featurebox.php");
		//		}
		// Removed, legacy
		// if (isset($pref['nfp_display']) && $pref['nfp_display'] == 1)
		// {
			// require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
		// }

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


if (!($news_total = $sql->db_Select_gen($query)))
{  // No news items
	require_once(HEADERF);
	echo "<br /><br /><div style='text-align:center'><b>".(strstr(e_QUERY, "month") ? LAN_NEWS_462 : LAN_NEWS_83)."</b></div><br /><br />";
	require_once(FOOTERF);
	exit;
}

$newsAr = $sql -> db_getList();
$news_total=$sql->total_results;
// Get number of entries
//$sql -> db_Select_gen("SELECT FOUND_ROWS()");
$frows = $sql -> db_Fetch();
//$news_total = $frows[0];

//echo "<br />Total ".$news_total." items found, ".count($newsAr)." displayed, Interval = {$interval}<br /><br />";

$p_title = ($action == "item") ? $newsAr[1]['news_title'] : $tp->toHTML($newsAr[1]['category_name'],FALSE,'TITLE');

switch($action)
{
	case 'item':
		setNewsFrontMeta($newsAr[1]);
		break;
	
	case 'list':
	default:
		setNewsFrontMeta($newsAr[1], 'category');
		break;
}

/*if($action != "" && !is_numeric($action))
{
if($action == "item" && $pref['meta_news_summary'] && $newsAr[1]['news_title'])
{
define("META_DESCRIPTION",SITENAME.": ".$newsAr[1]['news_title']." - ".$newsAr[1]['news_summary']);
}
define("e_PAGETITLE", $p_title);
}*/

$currentNewsAction = $action;
require_once(HEADERF);
$action = $currentNewsAction;

if(!$action)
{
	// Removed, themes should use {FEATUREBOX} shortcode instead
	//	if (isset($pref['fb_active'])){   // --->feature box
	//		require_once(e_PLUGIN."featurebox/featurebox.php");
	//	}

	// Removed, legacy
	// if (isset($pref['nfp_display']) && $pref['nfp_display'] == 1){
		// require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
	// }
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
	$param = array();
	$param['current_action'] = $action;
	foreach($newsAr as $news) {

		if(is_array($ALTERNATECLASSES)) {
			$newsdata[$loop] .= "<div class='{$ALTERNATECLASSES[0]}'>".$ix->render_newsitem($news, "return", '', '', $param)."</div>";
			$ALTERNATECLASSES = array_reverse($ALTERNATECLASSES);
		} else {
			$newsdata[$loop] .= $ix->render_newsitem($news, 'return', '', '', $param);
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
	// Deprecated
	// $parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".$e107->url->getUrl('core:news', 'main', "action=nextprev&to_action=".($action ? $action : 'default' )."&subaction=".($sub_action ? $sub_action : "0"));

	$sub_action = intval($sub_action);
	//    $parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";

	$amount = ITEMVIEW;
	$nitems = defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
	$url = rawurlencode(e107::getUrl()->create($newsRoute, $newsUrlparms));
	$parms  = 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$newsfrom.$nitems.'&url='.$url;

	$text  .= $tp->parseTemplate("{NEXTPREV={$parms}}");

	//    $nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
	//    $text .= ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");
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
	/*
	if(vartrue($NEWSLISTSTYLE)) $template =  $NEWSLISTSTYLE; v1.x doesn't do this.. so no point doing it here. 
	else 
	{
		$tmp = e107::getTemplate('news', 'news', 'list');
		$template = $tmp['item'];
		unset($tmp);
	}
	*/
	if (!defined("DATEHEADERCLASS")) {
		define("DATEHEADERCLASS", "nextprev");
		// if not defined in the theme, default class nextprev will be used for new date header
	}

	// #### normal newsitems, rendered via render_newsitem(), the $query is changed above (no other changes made) ---------
	$param = array();
	$param['current_action'] = $action;
	
	// Get Correct Template 
	// XXX we use $NEWSLISTSTYLE above - correct as we are currently in list mode - XXX No this is not NEWSLISTSTYLE - which provides only summaries. 
	// TODO requires BC testing if we comment this one
	if(vartrue($NEWSSTYLE)) 
	{
		$template =  $NEWSSTYLE;
	}
	else 
	{
		$tmp = e107::getTemplate('news', 'news', 'default'); // default - we show the full items, except for the 'extended' part.. 
		$template = $tmp['item'];
		unset($tmp);
	}


	
	
	
	// NEW - news category title when in list
	if($sub_action && 'list' == $action && vartrue($newsAr[1]['category_name']))
	{
		// we know category name - pass it to the nexprev url
		$category_name = $newsAr[1]['category_name'];
		if(vartrue($newsAr[1]['category_sef'])) $newsUrlparms['name'] = $newsAr[1]['category_sef'];
		if(!isset($NEWSLISTCATTITLE))
		{
			$NEWSLISTCATTITLE = "<h1 class='newscatlist-title'>".$tp->toHTML($category_name,FALSE,'TITLE')."</h1>";
		}
		else
		{
			$NEWSLISTCATTITLE = str_replace("{NEWSCATEGORY}",$tp->toHTML($category_name,FALSE,'TITLE'),$NEWSLISTCATTITLE);
		}
		echo $NEWSLISTCATTITLE;
	}

	$i= 1;
	while(isset($newsAr[$i]) && $i <= $interval) 
	{
		$news = $newsAr[$i];
		
		if(function_exists("news_style")) // BC
		{
			$template = news_style($news, $action, $param);	
		}
		
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
		// $template = false;
		$ix->render_newsitem($news, 'default', '', $template, $param);
		$i++;
	}

	$amount = ITEMVIEW;
	$nitems = defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
	$url = rawurlencode(e107::getUrl()->create($newsRoute, $newsUrlparms));
	// Example of passing route data instead building the URL outside the shortcode - for a reference only
	// $url = rawurlencode('url::'.$newsRoute.'::'.http_build_query($newsUrlparms, null, '&'));
	$parms  = 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$newsfrom.$nitems.'&url='.$url;

	echo $tp->parseTemplate("{NEXTPREV={$parms}}");

	//	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";
	//	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
	// 	echo ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");

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
	require_once(e_CORE.'shortcodes/batch/news_archives.php');

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

if ($action != "item") {
	if (is_numeric($action)){
		$action = "";
	}
	//	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'."[FROM].".$action.(isset($sub_action) ? ".".$sub_action : "");
	//	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
	//	echo ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");
}

if(is_dir("remotefile")) {
	require_once(e_HANDLER."file_class.php");
	$file = new e_file;
	//	$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*', 'Readme.txt');
	//	$crem = $file -> get_files(e_BASE."remotefile", "", $reject);
	$crem = $file -> get_files(e_BASE."remotefile", '~Readme\.txt');
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
}

function checkCache($cacheString){
	global $pref,$e107cache;
	$cache_data = $e107cache->retrieve($cacheString);
	$cache_title = $e107cache->retrieve($cacheString."_title");
	$cache_diz = $e107cache->retrieve($cacheString."_diz");
	$etitle = ($cache_title != "e_PAGETITLE") ? $cache_title : "";
	$ediz = ($cache_diz != "META_DESCRIPTION") ? $cache_diz : "";
	if($etitle){
		define(e_PAGETITLE,$etitle);
	}
	if($ediz){
		define("META_DESCRIPTION",$ediz);
	}
	if ($cache_data) {
		return $cache_data;
	} else {
		return false;
	}
}

function renderCache($cache, $nfp = FALSE){
	global $pref,$tp,$sql,$CUSTOMFOOTER, $FOOTER,$cust_footer,$ph;
	global $db_debug,$ns,$eTimingStart, $error_handler, $db_time, $sql2, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb,$e107;
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
		$text3 = $tp->toHTML("{NEWS_CATEGORIES}", TRUE, 'TITLE');
		$ns->tablerender(LAN_NEWS_23, $text3, 'news_cat');
	}
}

function setNewsFrontMeta($news, $type='news')
{
	
	$tp = e107::getParser();

	if($type == 'news')
	{
		if($news['news_title'] && !defined('e_PAGETITLE'))
		{
			define('e_PAGETITLE', $news['news_title']);
			e107::meta('og:title',$news['news_title']);
			e107::meta('og:type','article');		
		}

		if($news['news_meta_description'] && !defined('META_DESCRIPTION'))
		{
			e107::meta('description',$news['news_meta_description']);
			e107::meta('og:description',$news['news_meta_description']);	
			//define('META_DESCRIPTION', $news['news_meta_description']); // deprecated
		}
		elseif($news['news_summary']) // BC compatibility
		{
			e107::meta('og:description',$news['news_summary']);		
		}
	
		// grab all images in news-body and add to meta. 
		$images = e107::getBB()->getContent('img',$news['news_body'],SITEURL.e_IMAGE."newspost_images/");
		foreach($images as $im)
		{
			e107::meta('og:image',$im);		
		}
		
		// grab all youtube videos in news-body and add thumbnails to meta. 
		$youtube = e107::getBB()->getContent('youtube',$news['news_body']);
		foreach($youtube as $yt)
		{
			list($img,$tmp) = explode("?",$yt);
			e107::meta('og:image',"http://img.youtube.com/vi/".$img."/0.jpg");		
		}	

		// include news-thumbnail/image in meta. 
		if($news['news_thumbnail'])
		{
			$iurl = (substr($news['news_thumbnail'],0,3)=="{e_") ? $tp->replaceConstants($news['news_thumbnail'],'full') : SITEURL.e_IMAGE."newspost_images/".$news['news_thumbnail'];	
			e107::meta('og:image',$iurl);			
		}

		$url = e107::getUrl()->create('news/view/item', $news,'full=1');
		e107::meta('og:url',$url);	
		
		e107::meta('article:section',$news['category_name']);	
		
		if($news['news_meta_keywords'] && !defined('META_KEYWORDS'))
		{		
			e107::meta('keywords',$news['news_meta_keywords']);
			$tmp = explode(",",$news['news_meta_keywords']);
			foreach($tmp as $t)
			{
				e107::meta('article:tag', trim($t));
			}

			//	define('META_KEYWORDS', $news['news_meta_keywords']); // deprecated
		}


		/* Facebook reference. 
		 * http://developers.facebook.com/docs/opengraph/objects/builtin/
		 */

		return;
	}



	if($news['category_name'] && !defined('e_PAGETITLE'))
	{
		define('e_PAGETITLE', $news['category_name']);
	}

	if($news['category_meta_keywords'] && !defined('META_KEYWORDS'))
	{
		define('META_KEYWORDS', $news['category_meta_keywords']);
	}

	if($news['category_meta_description'] && !defined('META_DESCRIPTION'))
	{
		define('META_DESCRIPTION', $news['category_meta_description']);
	}
	
			

}


?>
