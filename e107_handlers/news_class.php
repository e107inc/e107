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
| $Revision: 1.47 $
| $Date: 2005-03-10 20:11:45 $
| $Author: stevedunstan $
+---------------------------------------------------------------+
*/

class news {
	function submit_item($news) {
		global $sql, $tp, $e107cache, $e_event, $pref;
		if (!is_object($tp)) $tp = new e_parse;
		if (!is_object($sql)) $sql = new db;

		extract($news);

		$attach = "";
		if($news_thumb)
		{
			$attach = "thumb:".$news_thumb.chr(1);
		}
		if($news_file)
		{
			$attach .= "file:".$news_file.chr(1);
		}
		if($news_image)
		{
			$attach .= "image:".$news_image.chr(1);
		}

		$news_title = $tp->toDB($news_title, TRUE);
		$news_body = $tp->toDB($data, TRUE);
		$news_extended = $tp->toDB($news_extended, TRUE);
		$news_summary = $tp->toDB($news_summary, TRUE);
		if(!isset($news_sticky)) {$news_sticky = 0;}
		$insertime = ($update_datestamp) ? time() : mktime($ds_hour,$ds_min,$ds_sec,$ds_month,$ds_day,$ds_year);

		if ($news_id) {
			$vals = "news_datestamp = '$insertime', news_title='$news_title', news_body='$news_body', news_extended='$news_extended', news_category='$cat_id', news_allow_comments='$news_allow_comments', news_start='$active_start', news_end='$active_end', news_class='$news_class', news_render_type='$news_rendertype' , news_summary='$news_summary', news_attach='$attach', news_sticky=$news_sticky WHERE news_id='$news_id' ";
			if ($sql->db_Update("news", $vals)) {
				$e_event->trigger("newsupd", $news);
				$message = LAN_NEWS_21;
				$e107cache->clear("news.php");
			} else {
				$message = "<strong>".LAN_NEWS_5."</strong>";
			}
		} else {
			if ($sql->db_Insert("news", "0, '$news_title', '$news_body', '$news_extended', ".$insertime.", ".USERID.", $cat_id, $news_allow_comments, $active_start, $active_end, '$news_class', '$news_rendertype', 0 , '$news_summary', '$attach', $news_sticky ")) {
				$e_event->trigger("newspost", $news);
				$message = LAN_NEWS_6;
				$e107cache->clear("news.php");
			} else {
				$message = "<strong>".LAN_NEWS_7."</strong>";
			}
		}

		/* trackback	*/
		if($pref['trackbackEnabled'])
		{
			$excerpt = substr($news_body, 0, 100)."...";
			$id=mysql_insert_id();
			$permLink = $e107->HTTPPath."comment.php?comment.news.$id";
			
			require_once(e_PLUGIN."trackback/trackbackClass.php");
			$trackback = new trackbackClass();

			if($_POST['trackback_urls'])
			{
				$urlArray = explode("\n", $_POST['trackback_urls']);				
				foreach($urlArray as $pingurl) {
					if(!$error = $trackback -> sendTrackback($permLink, $pingurl, $news_title, $excerpt))
					{
						$message .= "<br />successfully pinged $pingurl.";
					} else {
						$message .= "<br />was unable to ping $pingurl<br />[ Error message returned was : '$error'. ]";
					}
				}
			}

			if(isset($_POST['pingback_urls']))
			{
				if ($urlArray = $trackback -> getPingUrls($news_body))
				{
					foreach($urlArray as $pingurl)
					{

						if ($trackback -> sendTrackback($permLink, $pingurl, $news_title, $excerpt))
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

	function render_newsitem($news, $mode = "default", $n_restrict = "") {

		//echo "<pre>"; print_r($news); echo "</pre>"; // debug ...
/*
 Some variables from here probably still need to be moved
 into the function below.
*/
		global $tp, $sql, $override;
		$active_start = 0;
		$active_end = 0;
		$admin_name = '';
		$preview = '';
		if (!is_object($tp)) $tp = new e_parse;
		if ($n_restrict == "userclass") {
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

		if ($override_newsitem = $override->override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news);
			if ($result == "return") {
				return;
			}
		}

		global $NEWSSTYLE, $NEWSLISTSTYLE;
		$news['news_title'] = $tp->toHTML($news['news_title'], TRUE);
		$news['news_body'] = $tp->toHTML($news['news_body'], TRUE);
		if ($news['news_extended'] && ($preview == "Preview" || strstr(e_QUERY, "extend"))) {
			$news['news_extended'] = trim(chop($tp->toHTML($news['news_extended'], TRUE)));
		}
	//	extract($news);

		if (!defined("IMAGE_nonew_small")){
			define("IMAGE_nonew_small", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/nonew_comments.png' alt=''  />"));
		}
		if (!defined("IMAGE_new_small"))	{
			define("IMAGE_new_small", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/new_comments.png' alt=''  /> "));
		}
		if (!defined("IMAGE_sticky")){
			define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE."generic/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
		}

// definitions converted to params for use in the parser function.
// allows for changing of these elements with each template.

		$param['image_nonew_small'] = IMAGE_nonew_small;
		$param['image_new_small'] = IMAGE_new_small;
		$param['image_sticky'] = IMAGE_sticky;
		$param['caticon'] = ICONSTYLE;
		$param['commentoffstring'] = COMMENTOFFSTRING;
		$param['commentlink'] = COMMENTLINK;
		$param['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
		$param['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
		$param['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");

// new parser.

		$text = $this->parse_newstemplate($news, $NEWSSTYLE, $param);

		if($mode == "return") {
			return $text;
		}
		echo $text;
		if ($preview == "Preview") {
			echo $info;
		}
		return TRUE;
	}


	function make_xml_compatible($original) {
		global $tp, $ml;
		if (!is_object($tp)) $tp = new e_parse;
		$original = $tp->toHTML($original, TRUE);
		// remove html-only entities
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		// encode rest
		return htmlspecialchars($original);
	}


	function parse_newstemplate($news, $NEWS_TEMPLATE, $param=""){

	/*
		News text parser - to allow plugins to parse news items etc.
		$news = array of news field values.
		$param = array of style types that will come from definitions..
			$param['caticon'] = ICONSTYLE ( see above);
			The $param will be used below, instead of the definitions - to allow multiple styles to be used.
			Definitions will still be used in the theme.php.
*/
		global $tp, $pref;
		extract($news);

		$category_name = $tp->toHTML($news['category_name']);
		$category_icon = $news['category_icon'];
		$category_id   = $news['news_category'];

		$preview = (isset($preview) ? substr($preview,0,7) : "");

		$highlight_search = FALSE;
		if (IsSet($_POST['highlight_search'])) {
			$highlight_search = TRUE;
		}
		if (!isset($comment_total)) $comment_total = "0";
		$con = new convert;
		$datestamp = $con->convert_date($news_datestamp, "long");
		$titleonly = (substr($news_body, 0, 6) == "&nbsp;" && $NEWSLISTSTYLE ? TRUE : FALSE);
		if ($news_title == "Welcome to e107") {
			$admin_name = "e107";
			$admin_email = "e107@jalist.com";
			$category_name = "e107 welcome message";
			$category_id = 0;
			$category_icon = (strstr(SITEBUTTON, "http") ? SITEBUTTON : e_IMAGE.SITEBUTTON);
		} else {
			$category_icon = str_replace("../", "", $category_icon);
			if ($category_icon && strstr("images", $category_icon)) {
				$category_icon = THEME.$category_icon;
			} else {
				$category_icon = e_IMAGE."icons/".$category_icon;
			}
		}
		$active_start = (isset($active_start) ? str_replace(" - 00:00:00", "", $con->convert_date($active_start, "long")) : LAN_NEWS_19);
		$active_end = (isset($active_end) ? " to ".str_replace(" - 00:00:00", "", $con->convert_date($active_end, "long")) : "");
		$info = "<div class='smalltext'><br /><br /><b>".LAN_NEWS_18."</b><br />";
		$info .= ($titleonly ? LAN_NEWS_9 : "");
		$info .= ($news_class == 255 ? LAN_NEWS_10 : LAN_NEWS_11);
		$info .= ($news_sticky) ? "<br />".LAN_NEWS_31 : "";
		$info .= "<br />".($news_allow_comments ? LAN_NEWS_13 : LAN_NEWS_12);
		$info .= LAN_NEWS_14.$active_start.$active_end."<br />";
		$info .= LAN_NEWS_15.strlen($news_body).LAN_NEWS_16.strlen($news_extended).LAN_NEWS_17."<br /><br /></div>";
		if ($comment_total) {
			$sql->db_Select("comments", "comment_datestamp", "comment_item_id='".$news['news_id']."' AND comment_type='0' ORDER BY comment_datestamp DESC LIMIT 0,1");
			list($comments['comment_datestamp']) = $sql->db_Fetch();
			$latest_comment = $comments['comment_datestamp'];
			if ($latest_comment > USERLV ) {
				$NEWIMAGE = $param['image_new_small'];
			} else {
				$NEWIMAGE = $param['image_nonew_small'];
			}
		} else {
			$NEWIMAGE = $param['image_nonew_small'];
		}
		$news_category = "<a href='".e_BASE."news.php?cat.".$category_id."'>".$category_name."</a>";
		$news_author = "<a href='".e_BASE."user.php?id.".$user_id."'>".$user_name."</a>";
		require_once(e_HANDLER."emailprint_class.php");
		$ep = new emailprint;
		$textemail = $ep->render_emailprint("news", $news_id, 1);
		$textprint = $ep->render_emailprint("news", $news_id, 2);
		if (ADMIN && getperms("H")) {
			$adminoptions = "<a href='".e_BASE.e_ADMIN."newspost.php?create.edit.".$news_id."'><img src='".e_IMAGE."generic/newsedit.png' alt='' style='border:0' /></a>\n";
		}

		/* new attach code, added by jalist 10/03/2005 */

		if($news_attach)
		{
			$attach = explode(chr(1), $news_attach);
			foreach($attach as $attachment)
			{
				if(strstr($attachment, "thumb:"))
				{
					$news_thumb = str_replace("thumb:", "", $attachment);
				}
			}
			define("NEWSATTACH", $news_attach);
		}

		$news_body = $tp -> parseTemplate($news_body);

		$search[0] = "/\{NEWSTITLE\}(.*?)/si";
		$replace[0] = $news_title;
		$search[1] = "/\{NEWSBODY\}(.*?)/si";
		$replace[1] = ($abovepost ? "<br />".$abovepost."<br />" : "").(strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body).($belowpost ? "<br />".$belowpost : "");
		$search[2] = "/\{NEWSICON\}(.*?)/si";
		$replace[2] = "<a href='".e_BASE."news.php?cat.$category_id'><img style='".$param['caticon']."'  src='$category_icon' alt='' /></a>";
		$search[3] = "/\{NEWSHEADER\}(.*?)/si";
		$replace[3] = $category_icon;
		$search[4] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[4] = "<a style='".(isset($param['catlink']) ? $param['catlink'] : "#")."' href='".e_BASE."news.php?cat.$category_id'>".$category_name."</a>";
		$search[5] = "/\{NEWSAUTHOR\}(.*?)/si";
		$replace[5] = $news_author;
		$search[6] = "/\{NEWSDATE\}(.*?)/si";
		$replace[6] = $datestamp;
		$search[7] = "/\{NEWSCOMMENTS\}(.*?)/si";
		$replace[7] = ($news_allow_comments ? $param['commentoffstring'] : "".($pref['comments_icon'] ? $NEWIMAGE : "")." <a href='".e_BASE."comment.php?comment.news.$news_id'>".$param['commentlink'].$news_comment_total."</a>");
		$search[8] = "/\{EMAILICON\}(.*?)/si";
		$replace[8] = $textemail;
		$search[9] = "/\{PRINTICON\}(.*?)/si";
		$replace[9] = $textprint;
		$search[10] = "/\{NEWSID\}(.*?)/si";
		$replace[10] = $news_id;
		$search[11] = "/\{ADMINOPTIONS\}(.*?)/si";
		$replace[11] = $adminoptions;
		$search[12] = "/\{EXTENDED\}(.*?)/si";

		if ($news_extended && !strstr(e_QUERY, "extend")) {
			if (defined("PRE_EXTENDEDSTRING")) {
				$es1 = PRE_EXTENDEDSTRING;
			}
			if (defined("POST_EXTENDEDSTRING")) {
				$es2 = POST_EXTENDEDSTRING;
			}
			if ($preview == "Preview") {
				$replace[12] = $es1.EXTENDEDSTRING.$es2."<br />".$news_extended;
			} else {
				$replace[12] = $es1."<a href='".e_BASE."news.php?extend.".$news_id."'>".EXTENDEDSTRING."</a>".$es2;
			}
		} else {
			$replace[12] = "";
		}

		$search[13] = "/\{CAPTIONCLASS\}(.*?)/si";
		$replace[13] = "<div class='category".$category_id."'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?comment.news.$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";

		$search[14] = "/\{ADMINCAPTION\}(.*?)/si";
		$replace[14] = "<div class='".(isset($admin_name) ? $admin_name : "null")."'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?comment.news.$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";

		$search[15] = "/\{ADMINBODY\}(.*?)/si";
		$replace[15] = "<div class='".(isset($admin_name) ? $admin_name : "null")."'>".(strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body)."</div>";

		$search[16] = "/\{NEWSSUMMARY\}(.*?)/si";
		$replace[16] = ($news_summary) ? $news_summary."<br />" : "";

		$search[17] = "/\{NEWSTHUMBNAIL\}(.*?)/si";
		$replace[17] = ($news_thumb) ? "<a href='".e_BASE."news.php?item.$news_id.$category_id'><img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='".$param['thumbnail']."' /></a>" : "";

		$search[18] = "/\{STICKY_ICON\}(.*?)/si";
		$replace[18] = ($news['news_sticky'])? $param['image_sticky'] : "";

		$search[19] = "/\{NEWSTITLELINK\}(.*?)/si";
		$replace[19] = "<a style='".(isset($param['itemlink']) ? $param['itemlink'] : "null")."' href='".e_BASE."news.php?item.$news_id.$category_id'>".$news_title."</a>";

		$search[20] = "/\{NEWSCATICON\}(.*?)/si";
		$replace[20] = "<a href='".e_BASE."news.php?cat.$category_id'><img style='".$param['caticon']."'  src='$category_icon' alt='' /></a>";

		$search[21] = "/\{TRACKBACK\}(.*?)/si";
		if(isset($pref['trackbackEnabled'])) {
			$replace[21] = ($param['trackbackbeforestring'] ? $param['trackbackbeforestring'] : "")."<a href='".e_BASE."comment.php?comment.news.$news_id#track'>".$param['trackbackstring'].$tb_count."</a>".($param['trackbackafterstring'] ? $param['trackbackafterstring'] : "");
		} else {
			$replace[21] = "";
		}

		if (function_exists("news_style")) {
			$NEWS_TEMPLATE = news_style($news);
		}

		$text = preg_replace($search, $replace, $NEWS_TEMPLATE);

		return $text;
	}
}

?>