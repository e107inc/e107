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
| $Revision: 1.29 $
| $Date: 2005-02-12 07:56:41 $
| $Author: e107coders $
+---------------------------------------------------------------+
*/

class news {
	function submit_item($news) {
		global $e107cache, $e_event;
		if (!is_object($tp)) $tp = new e_parse;
		if (!is_object($sql)) $sql = new db;
		extract($news);
		$news_title = $tp->toDB($news_title, TRUE);
		$news_body = $tp->toDB($data, TRUE);
		$news_extended = $tp->toDB($news_extended, TRUE);
		$news_summary = $tp->toDB($news_summary, TRUE);
		if(!isset($news_sticky)) {$news_sticky = 0;}

		if ($news_id) {
			$vals = $update_datestamp ? "news_datestamp = ".time().", " :
			 "";
			$vals .= " news_title='$news_title', news_body='$news_body', news_extended='$news_extended', news_category='$cat_id', news_allow_comments='$news_allow_comments', news_start='$active_start', news_end='$active_end', news_class='$news_class', news_render_type='$news_rendertype' , news_summary='$news_summary', news_thumb='$news_thumb', news_sticky=$news_sticky WHERE news_id='$news_id' ";
			if ($sql->db_Update("news", $vals)) {
				$e_event->trigger("newsupd", $news);
				$message = LAN_NEWS_21;
				$e107cache->clear("news.php");
			} else {
				$message = "<strong>".LAN_NEWS_5."</strong>";
			}
		} else {
			if ($sql->db_Insert("news", "0, '$news_title', '$news_body', '$news_extended', ".time().", ".USERID.", $cat_id, $news_allow_comments, $active_start, $active_end, '$news_class', '$news_rendertype', 0 , '$news_summary', '$news_thumb', $news_sticky ")) {
				$e_event->trigger("newspost", $news);
				$message = LAN_NEWS_6;
				$e107cache->clear("news.php");
			} else {
				$message = "<strong>".LAN_NEWS_7."</strong>";
			}
		}
		return $message;
	}

	function render_newsitem($news, $mode = "default", $n_restrict = "") {

		//echo "<pre>"; print_r($news); echo "</pre>"; // debug ...

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
		extract($news);
		$preview = substr($preview,0,7);
		if(!defined("IMAGE_nonew_small"))
		{
			define("IMAGE_nonew_small", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/nonew_comments.png' alt=''  />"));
		}
		if(!defined("IMAGE_new_small"))
		{
			define("IMAGE_new_small", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/new_comments.png' alt=''  /> "));
		}
		if(!defined("IMAGE_sticky"))
		{
			define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE."generic/sticky.png' alt=''  /> "));
		}
		if (!$NEWSLISTSTYLE) {
			$NEWSLISTSTYLE = "
				<img src='".THEME."images/bullet2.gif' alt='bullet' />
				<b>
				{NEWSTITLE}
				</b>
				<div class='smalltext'>
				{NEWSAUTHOR}
				on
				{NEWSDATE}
				{NEWSCOMMENTS}
				</div>
				<hr />
				";
		}
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
		$active_start = ($active_start ? str_replace(" - 00:00:00", "", $con->convert_date($active_start, "long")) : LAN_NEWS_19);
		$active_end = ($active_end ? " to ".str_replace(" - 00:00:00", "", $con->convert_date($active_end, "long")) : "");
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
				$NEWIMAGE = IMAGE_new_small;
			} else {
				$NEWIMAGE = IMAGE_nonew_small;
			}
		} else {
			$NEWIMAGE = IMAGE_nonew_small;
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

		$search[0] = "/\{NEWSTITLE\}(.*?)/si";
		$replace[0] = ($news_render_type == 1 ? "<a href='".e_BASE."news.php?item.$news_id'>".$news_title."</a>" : $news_title);
		$search[13] = "/\{CAPTIONCLASS\}(.*?)/si";
		$replace[13] = "<div class='category".$category_id."'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?comment.news.$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";
		$search[14] = "/\{ADMINCAPTION\}(.*?)/si";
		$replace[14] = "<div class='$admin_name'>".($titleonly ? "&nbsp;<a href='".e_BASE."comment.php?comment.news.$news_id'>".$news_title."</a>" : "&nbsp;".$news_title)."</div>";
		$search[15] = "/\{ADMINBODY\}(.*?)/si";
		$replace[15] = "<div class='$admin_name'>".(strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body)."</div>";
		$search[1] = "/\{NEWSBODY\}(.*?)/si";
		$replace[1] = (strstr(e_QUERY, "extend") ? $news_body."<br /><br />".$news_extended : $news_body);
		$search[2] = "/\{NEWSICON\}(.*?)/si";
		$replace[2] = "<a href='".e_BASE."news.php?cat.$category_id'><img style='".ICONSTYLE."'  src='$category_icon' alt='' /></a>";
		$search[3] = "/\{NEWSHEADER\}(.*?)/si";
		$replace[3] = $category_icon;
		$search[4] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[4] = "<a href='".e_BASE."news.php?cat.$category_id'>".$category_name."</a>";
		$search[5] = "/\{NEWSAUTHOR\}(.*?)/si";
		$replace[5] = $news_author;
		$search[6] = "/\{NEWSDATE\}(.*?)/si";
		$replace[6] = $datestamp;
		$search[7] = "/\{NEWSCOMMENTS\}(.*?)/si";
		$replace[7] = ($news_allow_comments ? COMMENTOFFSTRING : "".$NEWIMAGE." <a href='".e_BASE."comment.php?comment.news.$news_id'>".COMMENTLINK.$news_comment_total."</a>");
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

		$search[13] = "/\{NEWSSUMMARY\}(.*?)/si";
		$replace[13] = ($news_summary) ? $news_summary."<br />" : "";

		$search[14] = "/\{NEWSTHUMBNAIL\}(.*?)/si";
		$replace[14] = ($news_thumb) ? "<img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='border:0px' />" : "";

		$search[15] = "/\{STICKY_ICON\}(.*?)/si";
		if($news['news_sticky'])
		{
			$replace[15] = IMAGE_sticky;
		}
		else
		{
			$replace[15] = '';
		}


		if (function_exists("news_style")) {
			$NEWSSTYLE = news_style($news);
		}
		$text = preg_replace($search, $replace, ($news_render_type == 1 && strstr(e_SELF, "news.php") ? $NEWSLISTSTYLE : $NEWSSTYLE));
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

}

?>