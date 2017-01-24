<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2016 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */



// news rewrite for v2.x
if (!defined('e107_INIT'))
{
	require_once("../../class2.php");
}


class news_front
{

	private $action = null;
	private $subAction = null;
	private $route = null;
	private $defaultTemplate = '';
	private $cacheString = 'news.php_default_';
	private $from = 0;
	private $order = 'news_datestamp';
	private $nobody_regexp = '';
	private $ix = null;
	private $newsUrlparms = array();
	private $text = null;
	private $pref = array();
	private $debugInfo = array();
	private $cacheRefreshTime = false;
//	private $interval = 1;

	function __construct()
	{
		global $NEWSHEADER;

		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
		e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_news.php');		// Temporary

		$this->pref = e107::getPref();

		$this->cacheRefreshTime = vartrue($this->pref['news_cache_timeout'],false);
		// $this->interval = $this->pref['newsposts']-$this>pref['newsposts_archive'];

		require_once(e_HANDLER."news_class.php");

		if(isset($NEWSHEADER))
		{
			return false;
		}

		$this->nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$this->ix = new news;

		$this->setConstants();
		$this->setActions();
		$this->setRoute();
		$this->detect();

		return null;
	}


	private function detect()
	{

		if ($this->action == 'cat' || $this->action == 'all' || !empty($_GET['tag']) || !empty($_GET['author']))
		{	// --> Cache
			$this->text = $this->renderListTemplate();
			$this->text .= $this->render_newscats();
			return null;
		}

		if ($this->action == 'extend')
		{	// --> Cache
			$this->text = $this->renderViewTemplate();
			$this->text .= $this->render_newscats();
			return null;
		}

		$this->text .= $this->renderDefaultTemplate();

		if(isset($this->pref['nfp_display']) && $this->pref['nfp_display'] == 2 && is_readable(e_PLUGIN."newforumposts_main/newforumposts_main.php"))
		{
			ob_start();
			require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
			$this->text .= ob_get_contents();
			ob_end_clean();
		}

		$this->text .= $this->show_newsarchive();
		$this->text .= $this->render_newscats();
		return null;

	}

	public function render()
	{
		echo $this->text;
	}

	private function setActions()
	{

		$this->defaultTemplate = e107::getPref('news_default_template');

		if (e_QUERY) //TODO add support for $_GET['cat'] and $_GET['mode'] and phase-out the x.x.x format.
		{

			$tmp = explode(".",e_QUERY);
			$action = $tmp[0];						// At least one parameter here
			$sub_action = varset($tmp[1],'');			// Usually a numeric category, or numeric news item number, but don't presume yet
			//	$id = varset($tmp[2],'');					// ID of specific news item where required
			$this->from = intval(varset($tmp[2],0));	// Item number for first item on multi-page lists
			$this->cacheString = 'news.php_'.e_QUERY;
		}
		else
		{

			$opt = array('default'=>'', 'list'=>'all');
			$action = varset($opt[$this->defaultTemplate],'');
			$sub_action = '';
			$tmp = array();

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

		$this->action = $action;
		$this->subAction= $sub_action;


	}


	private function setRoute()
	{
		$this->newsUrlparms = array('page' => '--FROM--');
		if($this->subAction)
		{

			switch ($this->action)
			{
				case 'list':
					$this->newsUrlparms['id'] = $this->subAction;
					$newsRoute = 'list/category';
				break;

				case 'cat':
					$this->newsUrlparms['id'] = $this->subAction;
					$newsRoute = 'list/short';
				break;

				case 'day':
				case 'month':
					$this->newsUrlparms['id'] = $this->subAction;
					$newsRoute = 'list/'.$this->action;
				break;

				default:
					$newsRoute = 'list/items';
				break;
			}
		}
		elseif($this->action == 'all')
		{
			$newsRoute = 'list/all';
			$this->newsUrlparms['id'] = $this->subAction;
		}
		else
		{
			$newsRoute = 'list/items';
		}



		$this->route = 'news/'.$newsRoute;

		$tp = e107::getParser();

		if(vartrue($_GET['tag']) || substr($this->action,0,4) == 'tag=')
		{

			$this->route = 'news/list/tag';
			if(!vartrue($_GET['tag']))
			{
				list($this->action,$word) = explode("=",$this->action,2);
				$_GET['tag'] = $word;
				unset($word,$tmp);
			}

			$this->newsUrlparms['tag'] = $tp->filter($_GET['tag']);
			$this->from = intval(varset($_GET['page'],0));
		}

		if(!empty($_GET['author']) || substr($this->action,0,4) == 'author=')
		{

			$this->route = 'news/list/author';
			if(!vartrue($_GET['author']))
			{
				list($action,$author) = explode("=",$this->action,2);
				$_GET['author'] = $author;
				unset($author,$tmp);
			}

			$this->newsUrlparms['author'] = $tp->filter($_GET['author']);
			$this->from = intval(varset($_GET['page'],0));

		}
	}


	private function setConstants()
	{


		if (!defined('ITEMVIEW'))
		{
			define('ITEMVIEW', varset($this->pref['newsposts'],15));
		}

		// ?all and ?cat.x and ?tag are the same listing functions - just filtered differently.
		// NEWSLIST_LIMIT is suitable for all

		if(!defined("NEWSLIST_LIMIT"))
		{
			 define("NEWSLIST_LIMIT", varset($this->pref['news_list_limit'],15));
		}


	}

	public function debug()
	{
		echo "<div class='alert alert-info'>";
		echo "<h4>News Debug Info</h4>";
		echo "<b>action:</b> ".$this->action."  ";
		echo "<br /><b>subaction:</b> ".$this->subAction."  ";
		echo "<br /><b>route:</b> ".$this->route."  ";
		echo "<br /><b>e_QUERY:</b> ".e_QUERY."  ";
		echo "<br /><b>CacheTimeout:</b> ".$this->cacheRefreshTime." ";
		echo "<br /><b>_GET:</b> ".print_r($_GET,true);

		foreach($this->debugInfo as $key=>$val)
		{
			echo "<br /><b>".$key.":</b> ".$val;
		}


		echo "</div>";


	}


	private function addDebug($key,$message)
	{
		if(is_array($message))
		{
			$this->debugInfo[$key] = print_a($message,true);
		}
		else
		{
			$this->debugInfo[$key] = $message;
		}

	}

	// ----------- old functions ------------------------


	private function show_newsarchive()
	{

		// do not show the news archive on the news.php?item.X page (but only on the news mainpage)
	    if(empty($this->defaultTemplate)  || !empty($this->action) || empty($this->pref['newsposts_archive']))
	    {
	        return null;
	    }


		global $NEWSARCHIVE;

		$sql = e107::getDb();
		$tp = e107::getParser();
		$ns = e107::getRender();

		$query = $this->getQuery();

		if($newsarchive = $this->checkCache('newsarchive'))
		{
			$this->addDebug("News Archive Cache", 'active');
			return $newsarchive;
		}

		$newsAr = array();

		if ($sql->gen($query))
		{
			$newsAr = $sql -> db_getList();
		}

		$i = $this->interval;


		// require_once(e_CORE.'shortcodes/batch/news_archives.php');
		 $sc = e107::getScBatch('news_archive');

		if(!$NEWSARCHIVE)
		{
			$NEWSARCHIVE ="<div>
					<table  style='width:100%;'>
						<tr>
						<td>
						<div>{ARCHIVE_BULLET} <b>{ARCHIVE_LINK}</b> <span class='smalltext'><i>{ARCHIVE_AUTHOR} @ ({ARCHIVE_DATESTAMP}) ({ARCHIVE_CATEGORY})</i></span></div>
						</td>
						</tr>
						</table>
						</div>";
		}

		$text = '';

		foreach($newsAr as $row)
		{
			$sc->setVars($row);
			$text .= $tp->parseTemplate($NEWSARCHIVE, FALSE, $sc);
		}



		$ret = $ns->tablerender($this->pref['newsposts_archive_title'], $text, 'news_archive', true);

		$this->setNewsCache('newsarchive', $ret);

		return $ret;

	}


	private function setNewsFrontMeta($news, $type='news')
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

			// include news-thumbnail/image in meta. - always put this one first.
			if($news['news_thumbnail'])
			{
				$iurl = (substr($news['news_thumbnail'],0,3)=="{e_") ? $news['news_thumbnail'] : SITEURL.e_IMAGE."newspost_images/".$news['news_thumbnail'];
				$tmp = explode(",", $iurl);
				foreach($tmp as $mimg)
				{
					if(substr($mimg,-8) == '.youtube')
					{
						continue;
					}
					e107::meta('og:image',$tp->thumbUrl($tmp[0],'w=500',false,true) );
				//	e107::meta('og:image',$mimg);
				}

			}

			// grab all images in news-body and add to meta.
			$images = e107::getBB()->getContent('img',$news['news_body'],SITEURL.e_IMAGE."newspost_images/");
			$c =1;
			foreach($images as $im)
			{
				if($c == 4){ break; }
				e107::meta('og:image',$im);
				$c++;
			}

			// grab all youtube videos in news-body and add thumbnails to meta.
			$youtube = e107::getBB()->getContent('youtube',$news['news_body']);
			$c = 1;
			foreach($youtube as $yt)
			{
				if($c == 3){ break; }
				list($img,$tmp) = explode("?",$yt);
				e107::meta('og:image',"http://img.youtube.com/vi/".$img."/0.jpg");
				$c++;
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



		if($news['category_name'] && !defined('e_PAGETITLE') && $type == 'category')
		{
			define('e_PAGETITLE', $tp->toHtml($news['category_name'],false,'TITLE_PLAIN'));
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



	private function setNewsCache($cache_tag, $cache_data, $rowData=array())
	{
		$e107cache = e107::getCache();
		$e107cache->setMD5(null,true);

		$e107cache->set($cache_tag, $cache_data);
		$e107cache->set($cache_tag."_title", defined("e_PAGETITLE") ? e_PAGETITLE : '');
		$e107cache->set($cache_tag."_diz", defined("META_DESCRIPTION") ? META_DESCRIPTION : '');

		$e107cache->set($cache_tag."_rows", e107::serialize($rowData,'json'));

	}


	/**
	 * @param $cache_tag
	 * @param string $type 'title' or 'diz' or 'rows' or empty for html.
	 */
	private function getNewsCache($cachetag, $type=null)
	{
		if(!empty($type))
		{
			$cachetag .= "_".$type;
		}
		$this->addDebug('CaheString lookup', $cachetag);
		e107::getDebug()->log('Retrieving cache string:' . $cachetag);

		$ret =  e107::getCache()->setMD5(null)->retrieve($cachetag);

		if($type == 'rows')
		{
			return e107::unserialize($ret);
		}

		return $ret;
	}

	/**
	 * @param $cacheString
	 * @return bool|string
	 */
	private function checkCache($cacheString)
	{
		$e107cache = e107::getCache();
		$this->addDebug("checkCache", 'true');
		$e107cache->setMD5(null);

		$cache_data = $e107cache->retrieve($cacheString, $this->cacheRefreshTime);
		$cache_title = $e107cache->retrieve($cacheString."_title", $this->cacheRefreshTime);
		$cache_diz = $e107cache->retrieve($cacheString."_diz", $this->cacheRefreshTime);
		$etitle = ($cache_title != "e_PAGETITLE") ? $cache_title : "";
		$ediz = ($cache_diz != "META_DESCRIPTION") ? $cache_diz : "";

		if($etitle)
		{
			define('e_PAGETITLE', $etitle);
		}

		if($ediz)
		{
			define("META_DESCRIPTION",$ediz);
		}

		if ($cache_data)
		{
			return $cache_data;
		}
		else
		{
			return false;
		}
	}


	private function renderCache($cache, $nfp = FALSE)
	{
		global $pref,$tp,$sql,$CUSTOMFOOTER, $FOOTER,$cust_footer,$ph;
		global $db_debug,$ns,$eTimingStart, $error_handler, $db_time, $sql2, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb,$e107;

		return $cache;

		/*if (isset($nfp) && isset($this->pref['nfp_display']) && $this->pref['nfp_display'] == 2)
		{
			require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
		}
		//	render_newscats(); //fixme this shouldn't be here.
		return $cache;*/
	}


	private function render_newscats() // --  CNN Style Categories. ----
	{
		$tp = e107::getParser();
		$ns = e107::getRender();

		if (isset($this->pref['news_cats']) && $this->pref['news_cats'] == '1')
		{
			$text3 = $tp->toHTML("{NEWS_CATEGORIES}", TRUE, 'TITLE');
			return $ns->tablerender(LAN_NEWS_23, $text3, 'news_cat', true);
		}
	}



	private function renderListTemplate()
	{
		$this->addDebug("Method",'renderListTemplate()');
		$sql = e107::getDb();
		$tp = e107::getParser();

		global $NEWSLISTSTYLE, $NEWSLISTTITLE;


		if($newsCachedPage = $this->checkCache($this->cacheString))
		{
			$this->addDebug("Cache", 'active');
			return $this->renderCache($newsCachedPage, TRUE);
		}
		else
		{
			$this->addDebug("Cache", 'inactive: '.$this->cacheString);
		}

		$category = intval($this->subAction);
		if ($this->action == 'cat' && $category != 0)
		{

			$gen = new convert;
			$sql->select("news_category", "*", "category_id='{$category}'");
			$row = $sql->fetch();
			extract($row);  // still required for the table-render.  :(
		}

		if ($this->action == 'all') // show archive of all news items using list-style template.
		{
			$renTypeQry = '';

			if(!empty($this->pref['news_list_templates']) && is_array($this->pref['news_list_templates']))
			{
				$renTypeQry = " AND (n.news_render_type REGEXP '(^|,)(".implode("|", $this->pref['news_list_templates']).")(,|$)')";
			}

		//	$news_total = $sql->count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")". str_replace("n.news", "news", $renTypeQry));
			$query = "
			SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
			nc.category_meta_keywords, nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.") AND n.news_start < ".time()."
			AND (n.news_end=0 || n.news_end>".time().") ";

			$query .= $renTypeQry;

			$query .= "
			ORDER BY n.news_sticky DESC, n.news_datestamp DESC
			LIMIT ".intval($this->from).",".deftrue('NEWSALL_LIMIT', NEWSLIST_LIMIT); // NEWSALL_LIMIT just for BC. NEWSLIST_LIMIT is sufficient.
			$category_name = ($this->defaultTemplate == 'list') ? PAGE_NAME : "All";
			unset($renTypeQry);
		}
		elseif ($this->action == 'cat') // show archive of all news items in a particular category using list-style template.
		{

		//	$news_total = $sql->count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_category=".intval($sub_action));

			$query = "
			SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
			nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_category=".intval($this->subAction)."
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
			AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
			ORDER BY n.news_datestamp DESC
			LIMIT ".intval($this->from).",".NEWSLIST_LIMIT;
		}
		elseif(vartrue($_GET['tag']))
		{
			$tagsearch = e107::getParser()->filter($_GET['tag']);

			$query = "
			SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
			nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_meta_keywords LIKE '%".$tagsearch."%'
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
			AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
			ORDER BY n.news_datestamp DESC
			LIMIT ".intval($this->from).",".NEWSLIST_LIMIT;
			$category_name = 'Tag: "'.$tagsearch.'"';

		}
		elseif(!empty($_GET['author']))
		{
			$authorSearch = e107::getParser()->filter($_GET['author']);

			$query = "
			SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords,
			nc.category_meta_description
			FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE u.user_name = '".$authorSearch."'
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
			AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
			ORDER BY n.news_datestamp DESC
			LIMIT ".intval($this->from).",".NEWSLIST_LIMIT;
			$category_name = 'Author: "'.$authorSearch.'"';



		}

		$newsList = array();

		if(!empty($query) && $sql->gen($query))
		{
			$news_total = $sql->foundRows();
			$newsList = $sql->db_getList();
			$ogImageCount = 0;
			foreach($newsList as $row)
			{
				if(!empty($row['news_thumbnail']))
				{
					$iurl = (substr($row['news_thumbnail'],0,3)=="{e_") ? $row['news_thumbnail'] : SITEURL.e_IMAGE."newspost_images/".$row['news_thumbnail'];
					$tmp = explode(",", $iurl);

					if($tp->isImage($tmp[0]))
					{
						if($ogImageCount > 6)
						{
							break;
						}

						e107::meta('og:image',$tp->thumbUrl($tmp[0],'w=500',false,true) );
						$ogImageCount++;

					}
				}

			}
		}
		else
		{

			$this->addDebug("Query",str_replace('#',MPREFIX, $query));
		}


		if($this->action == 'cat')
		{
			$this->setNewsFrontMeta($newsList[1], 'category');
		}
	//	elseif($category_name)
	//	{
	//		define('e_PAGETITLE', $tp->toHTML($category_name,FALSE,'TITLE'));
	//	}
		e107::getDebug()->log("PageTitle: ".e_PAGETITLE);
		$currentNewsAction = $this->action;

		$action = $currentNewsAction;

		if(deftrue('BOOTSTRAP'))  // v2.x
		{
			$template = e107::getTemplate('news', 'news', 'list');
		}
		else  // v1.x
		{
			if(empty($NEWSLISTSTYLE))
			{
				$NEWSLISTSTYLE = "
				<div style='padding:3px;width:100%'>
				<table style='border-bottom:1px solid black;width:100%' cellpadding='0' cellspacing='0'>
				<tr>
				<td style='vertical-align:top;padding:3px;width:20px'>
				{NEWS_CATEGORY_ICON}
				</td><td style='text-align:left;padding:3px'>
				{NEWSTITLELINK=extend}
				<br />
				{NEWS_SUMMARY}
				<span class='smalltext'>
				{NEWS_DATE}
				{NEWSCOMMENTS}
				</span>
				</td><td style='width:55px'>
				{SETIMAGE: w=55&h=55&crop=1}
				{NEWSTHUMBNAIL}
				</td></tr></table>
				</div>\n";
			}

			$template =  array('start'=>'', 'item'=>$NEWSLISTSTYLE, 'end'=>'');

		}

		// Legacy Styling..
		$param = array();
		$param['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
		$param['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
		$param['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
		$param['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : defset('ICONSTYLE','');
		$param['current_action'] = $action;
		$param['template_key'] = 'list';

		// NEW - allow news batch shortcode override (e.g. e107::getScBatch('news', 'myplugin', true); )
		e107::getEvent()->trigger('news_list_parse', $newsList);

		$text = '';

		if(vartrue($template['start']))
		{
			$text .= $tp->parseTemplate($template['start'], true);
		}

		if(!empty($newsList))
		{
			foreach($newsList as $row)
			{
				$text .= $this->ix->render_newsitem($row, 'return', '', $template['item'], $param);
			}
		}
		else // No News - empty.
		{
			$text .= "<div class='news-empty'><div class='alert alert-info'>".(strstr(e_QUERY, "month") ? LAN_NEWS_462 : LAN_NEWS_83)."</div></div>";
		}

		if(vartrue($template['end']))
		{
			$text .= $tp->parseTemplate($template['end'], true);
		}

		$icon = ($row['category_icon']) ? "<img src='".e_IMAGE."icons/".$row['category_icon']."' alt='' />" : "";

		// Deprecated.
		// $parms = $news_total.",".$amount.",".$newsfrom.",".$e107->url->getUrl('core:news', 'main', "action=nextprev&to_action={$action}&subaction={$category}");
		//	$parms = $news_total.",".$amount.",".$newsfrom.",".e_SELF.'?'.$action.".".$category.".[FROM]";
		//
		//	$text .= "<div class='nextprev'>".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";

		$amount 	= NEWSLIST_LIMIT;
		$nitems 	= defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
		$url 		= rawurlencode(e107::getUrl()->create($this->route, $this->newsUrlparms));
		$parms  	= 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$this->from.$nitems.'&url='.$url;


		$this->addDebug('newsUrlParms',$this->newsUrlparms);

		$text  		.= $tp->parseTemplate("{NEXTPREV={$parms}}");

		if(varset($template['caption'])) // v2.x
		{
			$NEWSLISTTITLE = str_replace("{NEWSCATEGORY}",$tp->toHTML($category_name,FALSE,'TITLE'), $template['caption']);
		}
		elseif(empty($NEWSLISTTITLE)) // default
		{
			$NEWSLISTTITLE = LAN_NEWS_82." '".$tp->toHTML($category_name,FALSE,'TITLE')."'";
		}
		else // v1.x
		{
			$NEWSLISTTITLE = str_replace("{NEWSCATEGORY}",$tp->toHTML($category_name,FALSE,'TITLE'),$NEWSLISTTITLE);
		}

		if($this->defaultTemplate != 'list')
		{
			$text .= "<div class='center news-list-footer'><a class='btn btn-default' href='".e107::getUrl()->create('news/list/all')."'>".LAN_NEWS_84."</a></div>";
		}


		$cache_data = e107::getRender()->tablerender($NEWSLISTTITLE, $text, 'news', true);

		$this->setNewsCache($this->cacheString, $cache_data);


		return $cache_data;




	}


	private function renderViewTemplate()
	{
		$this->addDebug("Method",'renderViewTemplate()');

		if($newsCachedPage = $this->checkCache($this->cacheString))
		{
			$this->addDebug("Cache",'active');
			$rows = $this->getNewsCache($this->cacheString,'rows');
			e107::getEvent()->trigger('user_news_item_viewed', $rows);
			$this->addDebug("Event-triggered:user_news_item_viewed", $rows);
			$this->setNewsFrontMeta($rows);
			$text = $this->renderCache($newsCachedPage, TRUE);		// This exits if cache used
			$text .= $this->renderComments($rows);
			return $text;
		}
		else
		{
			$this->addDebug("Cache",'inactive');
		}

		$sql = e107::getDb();
		// <-- Cache

		if(isset($this->pref['trackbackEnabled']) && $this->pref['trackbackEnabled'])
		{
			$query = "
		    SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
			nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
		    FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
			WHERE n.news_id=".intval($this->subAction)." AND n.news_class REGEXP '".e_CLASS_REGEXP."'
			AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
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
			AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
			AND n.news_start < ".time()."
			AND (n.news_end=0 || n.news_end>".time().")
			AND n.news_id=".intval($this->subAction);
		}


		if ($sql->gen($query))
		{
			$news = $sql->fetch();
			$id = $news['news_category'];		// Use category of this news item to generate next/prev links

			e107::getEvent()->trigger('user_news_item_viewed', $news);
			$this->addDebug("Event-triggered:user_news_item_viewed", $news);

			//***NEW [SecretR] - comments handled inside now
			e107::setRegistry('news/page_allow_comments', !$news['news_allow_comments']);
			if(!$news['news_allow_comments'] && isset($_POST['commentsubmit']))
			{
				$pid = intval(varset($_POST['pid'], 0));				// ID of the specific comment being edited (nested comments - replies)

				$clean_authorname = $_POST['author_name'];
				$clean_comment = $_POST['comment'];
				$clean_subject = $_POST['subject'];

				e107::getSingleton('comment')->enter_comment($clean_authorname, $clean_comment, 'news', $this->subAction, $pid, $clean_subject);
			}

			//More SEO
			$this->setNewsFrontMeta($news);
			/*
			if($news['news_title'])
			{
			if($this->pref['meta_news_summary'] && $news['news_title'])
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

			$currentNewsAction = $this->action;

			$action = $currentNewsAction;

			$param = array();
			$param['current_action'] = $action;
			$param['template_key'] = 'view';

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
				$this->ix->render_newsitem($news, 'extend', '', $template, $param);
				$cache_data = ob_get_contents();
			ob_end_clean();

			$this->setNewsCache($this->cacheString, $cache_data, $news);

			$text = $cache_data;
			$text .= $this->renderComments($news);

			return $text;
		}
		else
		{

			header("HTTP/1.0 404 Not Found",true,404);
			require_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_error.php");
			$text = e107::getMessage()->setTitle(LAN_ERROR_7, E_MESSAGE_INFO)->addInfo(LAN_NEWS_308)->render(); // Perhaps you're looking for one of the news items below?

			$this->action = 'all';
			$text .= $this->renderListTemplate();

			return $text;

		}


	}


	private function renderComments($news)
	{
		$this->addDebug("Calling", "renderComments()");

	//	if(e107::getRegistry('news/page_allow_comments'))
		if(isset($news['news_allow_comments']) && empty($news['news_allow_comments'])) // ie. comments active
		{
			global $comment_edit_query; //FIXME - kill me
			$comment_edit_query = 'comment.news.'.$news['news_id'];
			$text = e107::getComment()->compose_comment('news', 'comment', $news['news_id'], null, $news['news_title'], false, 'html');


			if(!empty($text))
			{
			//	$text = $comments['comment_form'] . $comments['comment'] .$comments['moderate'];
				return $text;
			//	return e107::getRender()->tablerender($comments['caption'], $text,'comment', true);
			}

		}

		$this->addDebug("Failed", "renderComments()");

		return '';
	}


	private function getQuery()
	{
		$query = "
				SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon,
				nc.category_meta_keywords, nc.category_meta_description
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type))
				ORDER BY n.news_sticky DESC, ".$this->order." DESC LIMIT ".intval($this->from).",".ITEMVIEW;

		return $query;


	}



	private function renderDefaultTemplate()
	{
		$this->addDebug("Method",'renderDefaultTemplate()');
		$tp = e107::getParser();
		$sql = e107::getDb();



		$interval = $this->pref['newsposts'];

		global $NEWSSTYLE;

		switch ($this->action)
		{
			case "list" :
				$sub_action = intval($this->subAction);
				//	$news_total = $sql->db_Count("news", "(*)", "WHERE news_category={$sub_action} AND news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().")");
				$query = "
				SELECT  SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
				nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND n.news_category={$sub_action}
				ORDER BY n.news_sticky DESC,".$this->order." DESC LIMIT ".intval($this->from).",".ITEMVIEW;

				$noNewsMessage = LAN_NEWS_463;
				break;


			case "item" :
				$sub_action = intval($this->subAction);
				$news_total = 1;
				if(isset($this->pref['trackbackEnabled']) && $this->pref['trackbackEnabled'])
				{
					$query = "
			    SELECT COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
				nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
				WHERE n.news_id=".$this->subAction." AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
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
				WHERE n.news_id=".$this->subAction." AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")";
				}

				$noNewsMessage = LAN_NEWS_83;
				break;


			case "month" :
			case "day" :
				$item = $tp -> toDB($this->subAction).'20000101';
				$year = substr($item, 0, 4);
				$month = substr($item, 4,2);



				if ($this->action == 'day')
				{
					$day = substr($item, 6, 2);
					$lastday = $day;
					$startdate = mktime(0, 0, 0, $month, $day, $year);
				}
				else
				{	// A month's worth
					$day = 1;
					$startdate = mktime(0, 0, 0, $month, $day, $year);
					$lastday = date("t", $startdate);
				}


				$enddate = mktime(23, 59, 59, $month, $lastday, $year);

				$query = "
				SELECT SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id, nc.category_name, nc.category_sef,
				nc.category_icon, nc.category_meta_keywords, nc.category_meta_description
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type)) AND n.news_datestamp BETWEEN {$startdate} AND {$enddate}
				ORDER BY ".$this->order." DESC LIMIT ".intval($this->from).",".ITEMVIEW;

				$noNewsMessage = LAN_NEWS_462;

				break;

			case 'default' :
			default :
				//$action = '';
				$this->cacheString = 'news.php_default_';		// Make sure its sensible
				//	$news_total = $sql->db_Count("news", "(*)", "WHERE news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.") AND news_start < ".time()." AND (news_end=0 || news_end>".time().") AND news_render_type<2" );

				if(!isset($this->pref['newsposts_archive']))
				{
					$this->pref['newsposts_archive'] = 0;
				}
				$interval = $this->pref['newsposts']-$this->pref['newsposts_archive'];		// Number of 'full' posts to show

				// Get number of news item to show
				if(isset($this->pref['trackbackEnabled']) && $this->pref['trackbackEnabled']) {
					$query = "
				SELECT SQL_CALC_FOUND_ROWS COUNT(tb.trackback_pid) AS tb_count, n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_id,
				nc.category_name, nc.category_sef, nc.category_icon, nc.category_meta_keywords, nc.category_meta_description,
				COUNT(*) AS tbcount
				FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				LEFT JOIN #trackback AS tb ON tb.trackback_pid  = n.news_id
				WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$this->nobody_regexp.")
				AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND (FIND_IN_SET('0', n.news_render_type) OR FIND_IN_SET(1, n.news_render_type))
				GROUP by n.news_id
				ORDER BY news_sticky DESC, ".$this->order." DESC LIMIT ".intval($this->from).",".ITEMVIEW;
				}
				else
				{
					$query = $this->getQuery();


				}

				$noNewsMessage = LAN_NEWS_83;
		}	// END - switch($action)


		if($newsCachedPage = $this->checkCache($this->cacheString)) // normal news front-page - with cache.
		{


			if(!$this->action)
			{
				// Removed, themes should use {FEATUREBOX} shortcode instead
				//		if (isset($this->pref['fb_active']))
				//		{
				//			require_once(e_PLUGIN."featurebox/featurebox.php");
				//		}
				// Removed, legacy
				// if (isset($this->pref['nfp_display']) && $this->pref['nfp_display'] == 1)
				// {
					// require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
				// }

			}

			//news archive
			if ($this->action != "item" && $this->action != 'list' && $this->pref['newsposts_archive'])
			{
				$sql = e107::getDb();

				if ($sql->gen($query))
				{

					$newsAr = $sql -> db_getList();

					if($newsarchive = $this->checkCache('newsarchive'))
					{
						$newsCachedPage = $newsCachedPage.$newsarchive;
					}
					else
					{
					//	$this->show_newsarchive($newsAr,$interval);
					}
				}
			}
			$this->renderCache($newsCachedPage, TRUE);
		}


		if (!($news_total = $sql->gen($query)))
		{  // No news items
			return "<div class='news-empty'><div class='alert alert-info' style='text-align:center'>".$noNewsMessage."</div></div>";

		}

		$newsAr = $sql -> db_getList();
		$news_total=$sql->total_results;
		// Get number of entries
		//$sql -> db_Select_gen("SELECT FOUND_ROWS()");
	//	$frows = $sql -> db_Fetch();
		//$news_total = $frows[0];

		//echo "<br />Total ".$news_total." items found, ".count($newsAr)." displayed, Interval = {$interval}<br /><br />";

		$p_title = ($this->action == "item") ? $newsAr[1]['news_title'] : $tp->toHTML($newsAr[1]['category_name'],FALSE,'TITLE');

		switch($this->action)
		{
			case 'item':
				$this->setNewsFrontMeta($newsAr[1]);
				break;

			case 'list':
			default:
				$this->setNewsFrontMeta($newsAr[1], 'list');
				break;
		}

		/*if($action != "" && !is_numeric($action))
		{
		if($action == "item" && $this->pref['meta_news_summary'] && $newsAr[1]['news_title'])
		{
		define("META_DESCRIPTION",SITENAME.": ".$newsAr[1]['news_title']." - ".$newsAr[1]['news_summary']);
		}
		define("e_PAGETITLE", $p_title);
		}*/

		$currentNewsAction = $this->action;

		$action = $currentNewsAction;

		if(!$action)
		{
			// Removed, themes should use {FEATUREBOX} shortcode instead
			//	if (isset($this->pref['fb_active'])){   // --->feature box
			//		require_once(e_PLUGIN."featurebox/featurebox.php");
			//	}

			// Removed, legacy
			// if (isset($this->pref['nfp_display']) && $this->pref['nfp_display'] == 1){
				// require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
			// }
		}

		if(isset($this->pref['news_unstemplate']) && $this->pref['news_unstemplate'] && file_exists(THEME."news_template.php"))
		{
			// theme specific template required ...

			$ALTERNATECLASSES = null;
			$NEWSCLAYOUT = null;

			require_once(THEME."news_template.php");

			if(empty($ALTERNATECLASS1))
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

				if(is_array($ALTERNATECLASSES))
				{
					$newsdata[$loop] .= "<div class='{$ALTERNATECLASSES[0]}'>".$this->ix->render_newsitem($news, "return", '', '', $param)."</div>";
					$ALTERNATECLASSES = array_reverse($ALTERNATECLASSES);
				}
				else
				{
					$newsdata[$loop] .= $this->ix->render_newsitem($news, 'return', '', '', $param);
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


			// Deprecated
			// $parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".$e107->url->getUrl('core:news', 'main', "action=nextprev&to_action=".($action ? $action : 'default' )."&subaction=".($sub_action ? $sub_action : "0"));

		//	$sub_action = intval($sub_action);
			//    $parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";

			$amount = ITEMVIEW;
			$nitems = defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
			$url = rawurlencode(e107::getUrl()->create($this->route, $this->newsUrlparms));
			$parms  = 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$this->from.$nitems.'&url='.$url;

			$text  .= $tp->parseTemplate("{NEXTPREV={$parms}}");

			//    $nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
			//    $text .= ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");
			//    $text=''.$text.'<center>'.$nextprev.'</center>';

			echo $text;
			$this->setNewsCache($this->cacheString, $text);
		}
		else
		{
			ob_start();

			$newpostday = 0;
			$thispostday = 0;
			$this->pref['newsHeaderDate'] = 1;
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
			$param['template_key'] = 'default';

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

			$socialInstalled = e107::isInstalled('social');

			while(isset($newsAr[$i]) && $i <= $interval)
			{
				$news = $newsAr[$i];

				// Set the Values for the social shortcode usage.
				if($socialInstalled == true)
				{
					$socialArray = array('url'=>e107::getUrl()->create('news/view/item', $news, 'full=1'), 'title'=>$tp->toText($news['news_title']), 'tags'=>$news['news_meta_keywords']);
					$socialObj = e107::getScBatch('social');

					if(is_object($socialObj))
					{
						$socialObj->setVars($socialArray);
					}
				}

				if(function_exists("news_style")) // BC
				{
					$template = news_style($news, $action, $param);
				}


				//        render new date header if pref selected ...
				$thispostday = strftime("%j", $news['news_datestamp']);
				if ($newpostday != $thispostday && (isset($this->pref['news_newdateheader']) && $this->pref['news_newdateheader']))
				{
					echo "<div class='".DATEHEADERCLASS."'>".strftime("%A %d %B %Y", $news['news_datestamp'])."</div>";
				}
				$newpostday = $thispostday;
				$news['category_id'] = $news['news_category'];
				if ($action == "item")
				{
					unset($news['news_render_type']);
					e107::getEvent()->trigger('user_news_item_viewed', $news);
					//e107::getDebug()->log($news);
				}
				// $template = false;
				$this->ix->render_newsitem($news, 'default', '', $template, $param);


				$i++;
			}

			$amount = ITEMVIEW;
			$nitems = defined('NEWS_NEXTPREV_NAVCOUNT') ? '&navcount='.NEWS_NEXTPREV_NAVCOUNT : '' ;
			$url = rawurlencode(e107::getUrl()->create($this->route, $this->newsUrlparms));
			// Example of passing route data instead building the URL outside the shortcode - for a reference only
			// $url = rawurlencode('url::'.$newsRoute.'::'.http_build_query($newsUrlparms, null, '&'));
			$parms  = 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='.$news_total.'&amount='.$amount.'&current='.$this->from.$nitems.'&url='.$url;

			echo $tp->parseTemplate("{NEXTPREV={$parms}}");

			//	$parms = $news_total.",".ITEMVIEW.",".$newsfrom.",".e_SELF.'?'.($action ? $action : 'default' ).($sub_action ? ".".$sub_action : ".0").".[FROM]";
			//	$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
			// 	echo ($nextprev ? "<div class='nextprev'>".$nextprev."</div>" : "");

			$cache_data = ob_get_clean();

			$this->setNewsCache($this->cacheString, $cache_data);

			return $cache_data;
		}

	}
}

$newsObj = new news_front;
require_once(HEADERF);
$newsObj->render();
if(E107_DBG_BASIC && ADMIN)
{
	$newsObj->debug();
}
require_once(FOOTERF);
exit;




//require_once(e_HANDLER."comment_class.php");
//$cobj = new comment;



//------------------------------------------------------
//		DISPLAY NEWS IN 'CATEGORY' LIST FORMAT HERE
//------------------------------------------------------
// Just title and a few other details





//------------------------------------------------------
//		DISPLAY SINGLE ITEM IN EXTENDED FORMAT HERE
//------------------------------------------------------



//------------------------------------------------------
//			DISPLAY NEWS IN LIST FORMAT HERE
//------------------------------------------------------
// Show title, author, first part of news item...


// ##### --------------------------------------------------------------------------------------------------------------


// #### new: news archive ---------------------------------------------------------------------------------------------

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

if (isset($this->pref['nfp_display']) && $this->pref['nfp_display'] == 2 && is_readable(e_PLUGIN."newforumposts_main/newforumposts_main.php"))
{
	require_once(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}

render_newscats();

require_once(FOOTERF);


// =========================================================================


?>
