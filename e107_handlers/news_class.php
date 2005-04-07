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
| $Revision: 1.58 $
| $Date: 2005-04-07 01:05:38 $
| $Author: sweetas $
+---------------------------------------------------------------+
*/

class news {
	
	var $news_item;
	var $param;
	
	function submit_item($news) {
		global $sql, $tp, $e107cache, $e_event, $pref;
		if (!is_object($tp)) $tp = new e_parse;
		if (!is_object($sql)) $sql = new db;

		$news['news_title'] = $tp->toDB($news['news_title'], TRUE);
		$news['news_body'] = $tp->toDB($news['data'], TRUE);
		$news['news_extended'] = $tp->toDB($news['news_extended'], TRUE);
		$news['news_summary'] = $tp->toDB($news['news_summary'], TRUE);
		if(!isset($news['news_sticky'])) {$news['news_sticky'] = 0;}
		$author_insert = ($news['news_author'] == 0) ? "news_author = '".USERID."'," : "";

		if ($news['news_id']) {
			$vals = "news_datestamp = '".$news['news_datestamp']."', ".$author_insert." news_title='".$news['news_title']."', news_body='".$news['news_body']."', news_extended='".$news['news_extended']."', news_category='".$news['cat_id']."', news_allow_comments='".$news['news_allow_comments']."', news_start='".$news['news_start']."', news_end='".$news['news_end']."', news_class='".$news['news_class']."', news_render_type='".$news['news_rendertype']."' , news_summary='".$news['news_summary']."', news_thumbnail='".$news['news_thumbnail']."', news_sticky='".$news['news_sticky']."' WHERE news_id='".$news['news_id']."' ";
			if ($sql -> db_Update('news', $vals)) {
				$e_event -> trigger('newsupd', $news);
				$message = LAN_NEWS_21;
				$e107cache -> clear('news.php');
			} else {
				$message = "<strong>".LAN_NEWS_5."</strong>";
			}
		} else {
			if ($sql ->db_Insert('news', "0, '".$news['news_title']."', '".$news['news_body']."', '".$news['news_extended']."', ".$news['news_datestamp'].", ".USERID.", '".$news['cat_id']."', '".$news['news_allow_comments']."', '".$news['news_start']."', '".$news['news_end']."', '".$news['news_class']."', '".$news['news_rendertype']."', '0' , '".$news['news_summary']."', '".$news['news_thumbnail']."', '".$news['news_sticky']."' ")) {
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
			$permLink = $e107->HTTPPath."comment.php?comment.news.$id";

			require_once(e_PLUGIN."trackback/trackbackClass.php");
			$trackback = new trackbackClass();

			if($_POST['trackback_urls'])
			{
				$urlArray = explode("\n", $_POST['trackback_urls']);
				foreach($urlArray as $pingurl) {
					if(!$error = $trackback -> sendTrackback($permLink, $pingurl, $news['news_title'], $excerpt))
					{
						$message .= "<br />successfully pinged $pingurl.";
					} else {
						$message .= "<br />was unable to ping $pingurl<br />[ Error message returned was : '$error'. ]";
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
	 						$message .= "<br />successfully pinged $pingurl.";
						}
						else
						{
							$message .= "Pingback to $pingurl failed ...";
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

	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $params='') {
		global $tp, $sql, $override, $pref, $ns, $NEWSSTYLE, $NEWSLISTSTYLE;
		if ($override_newsitem = $override->override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news);
			if ($result == 'return') {
				return;
			}
		}
		if (!is_object($tp)) $tp = new e_parse;
		$this -> news_item = $news;
		
		if ($n_restrict == 'userclass') {
			$this -> news_item['news_id'] = 0;
			$this -> news_item['news_title'] = LAN_NEWS_1;
			$this -> news_item['data'] = LAN_NEWS_2;
			$this -> news_item['news_extended'] = "";
			$this -> news_item['news_allow_comments'] = 1;
			$this -> news_item['news_start'] = 0;
			$this -> news_item['news_end'] = 0;
			$this -> news_item['news_render_type'] = 0;
			$this -> news_item['comment_total'] = 0;
		}

		if ($params) {
			$this -> param = $params;
		} else {
			if (!defined("IMAGE_nonew_small")){
				define("IMAGE_nonew_small", (file_exists(THEME."generic/nonew_comments.png") ? "<img src='".THEME."generic/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/".IMODE."/nonew_comments.png' alt=''  />"));
			}
			if (!defined("IMAGE_new_small"))	{
				define("IMAGE_new_small", (file_exists(THEME."generic/new_comments.png") ? "<img src='".THEME."generic/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/".IMODE."/new_comments.png' alt=''  /> "));
			}
			if (!defined("IMAGE_sticky")){
				define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE."generic/".IMODE."/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
			}

			$this -> param['image_nonew_small'] = IMAGE_nonew_small;
			$this -> param['image_new_small'] = IMAGE_new_small;
			$this -> param['image_sticky'] = IMAGE_sticky;
			$this -> param['caticon'] = ICONSTYLE;
			$this -> param['commentoffstring'] = COMMENTOFFSTRING;
			$this -> param['commentlink'] = COMMENTLINK;
			$this -> param['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
			$this -> param['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
			$this -> param['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");
		}

		if ($this -> news_item['news_render_type'] == 1) {
			if (function_exists("news_list")) {
				$NEWS_PARSE = news_list($this -> news_item);
			} else if ($NEWSLISTSTYLE) {
				$NEWS_PARSE = $NEWSLISTSTYLE;
			} else {
				$NEWS_PARSE = "{NEWSICON}&nbsp;<b>{NEWSTITLELINK}</b><div class='smalltext'>{NEWSAUTHOR} ".LAN_100." {NEWSDATE} | {NEWSCOMMENTS}</div>";
			}
		} else {
			if ($NEWS_TEMPLATE) {
				$NEWS_PARSE = $NEWS_TEMPLATE;
			} else {
				if (function_exists("news_style")) {
					$NEWS_PARSE = news_style($this -> news_item);
				} else {
					$NEWS_PARSE = $NEWSSTYLE;
				}
			}
		}

		require_once(e_FILE.'shortcode/batch/news_shortcodes.php');
		$text = $tp -> parseTemplate($NEWS_PARSE, FALSE, $news_shortcodes);

		if($mode == 'return') {
			return $text;
		} else {
			echo $text;
			return TRUE;
		}		
	}

	function make_xml_compatible($original) {
		global $tp, $ml;
		if (!is_object($tp)) $tp = new e_parse;
		$original = $tp->toHTML($original, TRUE);
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		return htmlspecialchars($original);
	}
	
	function news_info() {
		global $ns;
		$this -> news_item['news_start'] = (isset($this -> news_item['news_start']) && $this -> news_item['news_start'] ? str_replace(" - 00:00:00", "", $con->convert_date($this -> news_item['news_start'], "long")) : LAN_NEWS_19);
		$this -> news_item['news_end'] = (isset($this -> news_item['news_end']) && $this -> news_item['news_end'] ? " to ".str_replace(" - 00:00:00", "", $con->convert_date($this -> news_item['news_end'], "long")) : "");
		$info = $this -> news_item['news_render_type'] == 1 ? LAN_NEWS_9 : "";
		$info .= $this -> news_item['news_class'] == 255 ? LAN_NEWS_10 : LAN_NEWS_11;
		$info .= $this -> news_item['news_sticky'] ? "<br />".LAN_NEWS_31 : "";
		$info .= "<br />".($this -> news_item['news_allow_comments'] ? LAN_NEWS_13 : LAN_NEWS_12);
		$info .= LAN_NEWS_14.$this -> news_item['news_start'].$this -> news_item['news_end']."<br />";
		$info .= LAN_NEWS_15.strlen($this -> news_item['news_body']).LAN_NEWS_16.strlen($this -> news_item['news_extended']).LAN_NEWS_17."<br /><br /></div>";
		return $ns -> tablerender(LAN_NEWS_18, $info);
	}
}

?>