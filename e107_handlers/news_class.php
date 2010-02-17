<?php
/*
+---------------------------------------------------------------+
| e107 website system
| /classes/news_class.php
|
| ©Steve Dunstan 2001-2002
| http://jalist.com
| stevedunstan@jalist.com
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
|
| $Source: /cvs_backup/e107_0.7/e107_handlers/news_class.php,v $
| $Revision$
| $Date$
| $Author$
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class news {

	function submit_item($news) {
		global $sql, $tp, $e107cache, $e_event, $pref;
		if (!is_object($tp)) $tp = new e_parse;
		if (!is_object($sql)) $sql = new db;

		$news['news_title'] = $tp->toDB($news['news_title']);
		$news['news_body'] = $tp->toDB($news['data']);
		$news['news_extended'] = $tp->toDB($news['news_extended']);
		$news['news_summary'] = $tp->toDB($news['news_summary']);
		$news['news_userid'] = ($news['news_userid']) ? $news['news_userid'] : USERID;
		if(!isset($news['news_sticky'])) {$news['news_sticky'] = 0;}
		$author_insert = ($news['news_author'] == 0) ? "news_author = '".USERID."'," : "";
        $news['news_author'] = ($news['news_author']) ? $news['news_author'] : USERID;

		if ($news['news_id']) {
			$vals = "news_datestamp = '".intval($news['news_datestamp'])."', ".$author_insert." news_title='".$news['news_title']."', news_body='".$news['news_body']."', news_extended='".$news['news_extended']."', news_category='".intval($news['cat_id'])."', news_allow_comments='".intval($news['news_allow_comments'])."', news_start='".intval($news['news_start'])."', news_end='".intval($news['news_end'])."', news_class='".$tp->toDB($news['news_class'])."', news_render_type='".intval($news['news_rendertype'])."' , news_summary='".$news['news_summary']."', news_thumbnail='".$tp->toDB($news['news_thumbnail'])."', news_sticky='".intval($news['news_sticky'])."' WHERE news_id='".intval($news['news_id'])."' ";
			if ($sql -> db_Update('news', $vals)) {
				$e_event -> trigger('newsupd', $news);
				$message = LAN_NEWS_21;
				$e107cache -> clear('news.php');
			} else {
				$message = "<strong>".(!mysql_errno() ? LAN_NEWS_46 : LAN_NEWS_5)."</strong>";
			}
		} else {
			if ($news['news_id'] = $sql ->db_Insert('news', "0, '".$news['news_title']."', '".$news['news_body']."', '".$news['news_extended']."', ".intval($news['news_datestamp']).", ".intval($news['news_author']).", '".intval($news['cat_id'])."', '".intval($news['news_allow_comments'])."', '".intval($news['news_start'])."', '".intval($news['news_end'])."', '".$tp->toDB($news['news_class'])."', '".intval($news['news_rendertype'])."', '0' , '".$news['news_summary']."', '".$tp->toDB($news['news_thumbnail'])."', '".intval($news['news_sticky'])."' ")) {
				$e_event -> trigger('newspost', $news);
				$message = LAN_NEWS_6;
				$e107cache -> clear('news.php');
			} else {
				$message = "<strong>".LAN_NEWS_7."</strong>";
			}
		}

		/* trackback	*/
		if($pref['trackbackEnabled'])
		{
			$excerpt = substr($news['news_body'], 0, 100)."...";
			$id=mysql_insert_id();
			$permLink = $e107->base_path."comment.php?comment.news.{$id}";

			require_once(e_PLUGIN."trackback/trackbackClass.php");
			$trackback = new trackbackClass();

			if($_POST['trackback_urls'])
			{
				$urlArray = explode("\n", $_POST['trackback_urls']);
				foreach($urlArray as $pingurl) {
					if(!$error = $trackback -> sendTrackback($permLink, $pingurl, $news['news_title'], $excerpt))
					{
						$message .= "<br />successfully pinged {$pingurl}.";
					} else {
						$message .= "<br />was unable to ping {$pingurl}<br />[ Error message returned was : '{$error}'. ]";
					}
				}
			}

			if(isset($_POST['pingback_urls']))
			{
				if ($urlArray = $trackback -> getPingUrls($news['news_body']))
				{
					foreach($urlArray as $pingurl)
					{

						if ($trackback -> sendTrackback($permLink, $pingurl, $news['news_title'], $excerpt))
						{
	 						$message .= "<br />successfully pinged {$pingurl}.";
						}
						else
						{
							$message .= "Pingback to {$pingurl} failed ...";
						}
					}
				}
				else
				{
					$message .= "<br />No pingback addresses were discovered";
				}
			}
		}

		/* end trackback */

		return $message;
	}

	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $param='') {
		global $tp, $sql, $override, $pref, $ns, $NEWSSTYLE, $NEWSLISTSTYLE, $news_shortcodes, $loop_uid;
		if ($override_newsitem = $override -> override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news, $mode, $n_restrict, $NEWS_TEMPLATE, $param);
			if ($result == 'return') {
				return;
			}
		}
		if (!is_object($tp)) $tp = new e_parse;

		if ($n_restrict == 'userclass') {
			$news['news_id'] = 0;
			$news['news_title'] = LAN_NEWS_1;
			$news['data'] = LAN_NEWS_2;
			$news['news_extended'] = "";
			$news['news_allow_comments'] = 1;
			$news['news_start'] = 0;
			$news['news_end'] = 0;
			$news['news_render_type'] = 0;
			$news['comment_total'] = 0;
		}

		if (!$param) 
		{
			$param['caticon'] = ICONSTYLE;
			$param['commentoffstring'] = COMMENTOFFSTRING;
			$param['commentlink'] = COMMENTLINK;
			$param['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
			$param['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
			$param['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");
			$param['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
			$param['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
			$param['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
			$param['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : ICONSTYLE;
		}

// Next three images aren't always defined by the caller, even if most of $param is.
		if (!isset($param['image_nonew_small']))
		{
		  if (!defined("IMAGE_nonew_small"))
		  {
			define("IMAGE_nonew_small", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME_ABS."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/nonew_comments.png' alt=''  />"));
		  }
		  $param['image_nonew_small'] = IMAGE_nonew_small;
		}
		
		if (!isset($param['image_new_small']))
		{
		  if (!defined("IMAGE_new_small"))	
		  {
			define("IMAGE_new_small", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME_ABS."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/new_comments.png' alt=''  /> "));
		  }
		  $param['image_new_small'] = IMAGE_new_small;
		}

		if (!isset($param['image_sticky']))
		{
		  if (!defined("IMAGE_sticky"))
		  {
			define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME_ABS."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
		  }
		  $param['image_sticky'] = IMAGE_sticky;
		}

		cachevars('current_news_item', $news);
		cachevars('current_news_param', $param);

		if ($news['news_render_type'] == 1 && $mode != "extend") {
			if (function_exists("news_list")) {
				$NEWS_PARSE = news_list($news);
			} else if ($NEWSLISTSTYLE) {
				$NEWS_PARSE = $NEWSLISTSTYLE;
			} else {
				$NEWS_PARSE = "{NEWSICON}&nbsp;<b>{NEWSTITLELINK}</b><div class='smalltext'>{NEWSAUTHOR} ".LAN_NEWS_100." {NEWSDATE} | {NEWSCOMMENTS}</div>";
			}
		} else {
			if ($NEWS_TEMPLATE) {
				$NEWS_PARSE = $NEWS_TEMPLATE;
			} else {
				if (function_exists("news_style")) {
					$NEWS_PARSE = news_style($news);
				} else {
					$NEWS_PARSE = $NEWSSTYLE;
				}
			}
		}
		$loop_uid = $news['news_author'];

		require_once(e_FILE.'shortcode/batch/news_shortcodes.php');
		$text = $tp -> parseTemplate($NEWS_PARSE, TRUE, $news_shortcodes);

		if ($mode == 'return') {
			return $text;
		} else {
			echo $text;
			return TRUE;
		}
	}

	function make_xml_compatible($original) {
		global $tp;
		if (!is_object($tp)) $tp = new e_parse;
		$original = $tp->toHTML($original, TRUE);
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		return htmlspecialchars($original);
	}
}

?>