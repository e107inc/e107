<?php
/*
 * e107 website system
 *
 * Copyright (C) 2002-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alternate news display
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER.'news_class.php');

function alt_news($news_category) 
{
	global $sql, $sql2, $tp, $ns;
	$ix = new news;
	$news_category = intval($news_category);
	if (strstr(e_QUERY, 'cat')) 
	{
		$category = $news_category;
		if ($category != 0) 
		{
			$gen = new convert;
			$sql->db_Select("news_category", "*", "category_id=".$category);
			list($category_id, $category_name, $category_icon) = $sql->db_Fetch();
			$category_name = $tp->toHTML($category_name);
			if (strstr($category_icon, "../")) 
			{
				$category_icon = str_replace("../", "", e_BASE.$category_icon);
			} 
			else 
			{
				$category_icon = THEME.$category_icon;
			}

			if ($count = $sql->db_Select('news', '*', 'news_category='.intval($category).' ORDER BY news_datestamp DESC')) 
			{
				while ($row = $sql->db_Fetch()) 
				{
					extract($row);
					if ($news_title == '') 
					{
						$news_title = 'Untitled';
					}
					$datestamp = $gen->convert_date($news_datestamp, 'short');
					$news_body = strip_tags(substr($news_body, 0, 100))." ...";
					$comment_total = $sql2->db_Count("comments", "(*)", "WHERE comment_item_id='".intval($news_id)."' AND comment_type='0' ");
					$bullet = '';
					if(defined('BULLET'))
					{
						$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" style="vertical-align: middle;" />';
					}
					elseif(file_exists(THEME.'images/bullet2.gif'))
					{
						$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" style="vertical-align: middle;" />';
					}
					$text .= "
						<div class='mediumtext'>
						".$bullet;

					if ($news_allow_comments)
					{
						$text .= "
						<a href='news.php?extend.".$news_id."'>".$news_title."</a>";
					}
					else
					{
						$text .= "
						<a href='comment.php?comment.news.".$news_id."'>".$news_title."</a>";
					}
					$text .= "<br />
						".LAN_NEWS_100." ".$datestamp." (".LAN_NEWS_99.": ";
					if ($news_allow_comments) {
						$text .= COMMENTOFFSTRING.")";
					} else {
						$text .= $comment_total.")";
					}
					$text .= "</div>
						".$news_body."
						<br /><br />\n";
				}
				$text = "<img src='$category_icon' alt='' /><br />". LAN_NEWS_307.$count."
					<br /><br />".$text;
				$ns->tablerender(LAN_NEWS_82." '".$category_name."'", $text, 'alt_news');
			}
		}
		return TRUE;
	}

	if ($sql->db_Select('news', 'news_id, news_class, news_author, news_comment_total, news_datestamp, news_body, news_extended', 
	"news_class != ".e_UC_NOBODY."  
	AND (news_start=0 || news_start < ".time().") 
	AND (news_end=0 || news_end>".time().") 
	AND news_category=".$news_category." ORDER BY news_datestamp DESC LIMIT 0,".ITEMVIEW)) 
	{
//		while (list($news['news_id'], $news['news_title'], $news['data'], $news['news_extended'], $news['news_datestamp'], $news['admin_id'], $news_category, $news['news_allow_comments'], $news['news_start'], $news['news_end'], $news['news_class']) = $sql->db_Fetch()) {
		while ($news = $sql->db_Fetch()) 
		{
			$news['user_id'] = intval($news['news_author']);
			if(!$news['user_name'] = getcachedvars($news['user_id'])) 
			{
				$sql2->db_Select('user', 'user_name', 'user_id='.$news['user_id']);
				list($news['user_name']) = $sql2->db_Fetch();
				cachevars($news['user_id'], $news['user_name']);		// Cache user_id, user_name
			}
//			if (check_class($news['news_class']) || !$news['news_class']) 
			if (check_class($news['news_class'])) 
			{
				$sql2->db_Select("news_category", "*", "category_id='".intval($news_category)."' ");

				list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2->db_Fetch();
				$ix->render_newsitem($news);
			} 
			else 
			{
				if ($pref['subnews_hide_news'] == 1) 
				{

					$sql2->db_Select("news_category", "*", "category_id='".intval($news_category)."' ");

					list($news['category_id'], $news['category_name'], $news['category_icon']) = $sql2->db_Fetch();
					$ix->render_newsitem($news, "", "userclass");
				}
			}
		}
	}
}

?>