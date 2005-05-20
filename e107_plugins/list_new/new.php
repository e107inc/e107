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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/list_new/new.php,v $
|     $Revision: 1.14 $
|     $Date: 2005-05-20 21:56:38 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
	
if (!USER) {
	header("location:".e_BASE."index.php");
	exit;
}
	
require_once(HEADERF);
@include(e_PLUGIN."list_new/languages/".e_LANGUAGE.".php");
@include(e_PLUGIN."list_new/languages/English.php");
	
$BULLET = (defined("BULLET") ? "<img src='".THEME."images/".BULLET."' alt='' style='vertical-align: middle;' />" : "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='bullet' style='vertical-align: middle;' />");

$lvisit = (e_QUERY ? intval(e_QUERY) : USERLV);

/* --- get template --- */
if (file_exists(THEME."list_new_template.php"))
{
	require_once(THEME."list_new_template.php");
}
else if(!$LISTNEW_MAIN_START)
{
	require_once(e_PLUGIN."list_new/templates/list_new_template.php");
}


$mainStr = preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_START);

/* --- news --- */
if($ITEMS = $sql->db_Select("news", "*", "news_class REGEXP '".e_CLASS_REGEXP."' AND news_datestamp>$lvisit  ORDER BY news_datestamp DESC LIMIT 0,10"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_1;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_NEWS);
	foreach($newArray as $item)
	{
		$TITLE = "<a href='".e_BASE."comment.php?comment.news.".$item['news_id']."'>".($item['news_title'] ? $item['news_title'] : "( ".LIST_2." )")."</a>";
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_NEWS);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_NEWS);
}
/* --- end news --- */

/* --- chatbox --- */
if($ITEMS = $sql->db_Select("chatbox", "*", "cb_datestamp>$lvisit ORDER BY cb_datestamp DESC LIMIT 0,50"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_5;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_CHATBOX);
	foreach($newArray as $item)
	{
		extract($item);
		$cb_id = substr($cb_nick , 0, strpos($cb_nick , "."));
		$cb_nick = substr($cb_nick , (strpos($cb_nick , ".")+1));
		$cb_message = ($cb_blocked ? LIST_6 : str_replace("<br />", "", $tp -> toHTML($cb_message)));
		$USER = "<a href='".e_BASE."user.php?id.$cb_id'>$cb_nick</a>";
		$POST = $cb_message;
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_CHATBOX);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_CHATBOX);
}
/* --- end chatbox --- */




/* --- forum --- */
$query = "SELECT tp.thread_name AS parent_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_parent, f.forum_id, f.forum_name FROM #forum_t AS t 
LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id 
LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id 
WHERE f.forum_class  IN (".USERCLASS_LIST.") 
AND t.thread_datestamp > $lvisit
ORDER BY t.thread_datestamp DESC LIMIT 0, 50";
if($ITEMS = $sql->db_Select_gen($query))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_7;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_FORUM);
	foreach($newArray as $item)
	{
		extract($item);
		$FORUM = "<a href='".e_PLUGIN."forum/forum_viewforum.php?$forum_id'>$forum_name</a>";
		$TITLE = ($thread_parent ? "Re: <a href='".e_PLUGIN."forum/forum_viewtopic.php?$thread_parent#$thread_id'>".$tp -> toHTML($parent_name)."</a>" : "<a href='".e_PLUGIN."forum/forum_viewtopic.php?$thread_id'>".$tp -> toHTML($thread_name)."</a>");
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_FORUM);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_FORUM);
}
/* --- end forum --- */



/* --- comments --- */
if($ITEMS = $sql->db_Select("comments", "*", "comment_datestamp>$lvisit  ORDER BY comment_datestamp DESC LIMIT 0,10"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_9;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_COMMENT);
	foreach($newArray as $item)
	{
		extract($item);
	
		switch($comment_type) {

			case "ideas":	// ideas
				$sql -> db_Select("ideas", "ideas_summary", "ideas_id=$comment_item_id ");
				$row = $sql -> db_Fetch();
				extract($row);
				$TYPE = LIST_10;
				$TITLE = "Re: <a href='".e_PLUGIN."ideas/ideas.php?show.$comment_item_id'>".$tp -> toHTML($ideas_summary)."</a>";
			break;

			case 0: // news
				$sql -> db_Select("news", "*", "news_id=$comment_item_id ");
				$row = $sql -> db_Fetch();
				 extract($row);
				if (check_class($news_class))
				{
					$TYPE = LIST_12;
					$TITLE = "Re: <a href='".e_BASE."comment.php?comment.news.$comment_item_id'>".$tp -> toHTML($news_title, TRUE)."</a>";
				}
			break;

/*
			case 1:
			//        article, review or content page
			//        find out whether article, review or content page ...
			$sql2->db_Select("content", "content_heading, content_type, content_class", "content_id=$comment_item_id ");
			$row = $sql2->db_Fetch();
			extract($row);
			if (check_class($content_class)) {
				$comment_count++;
				switch($content_type) {
					case 0:
					//        article
					$str .= $bullet."[ ".LIST_14." ] Re: <a href='".e_BASE."content.php?article.$comment_item_id'>".$tp -> toHTML($content_heading, "admin")."</a><br />";
					break;
					case 1:
					//        content page
					$str .= $bullet."[ ".LIST_15." ] Re: <a href='".e_BASE."content.php?content.$comment_item_id'>".$tp -> toHTML($content_heading, "admin")."</a><br />";
					break;
					case 3:
					//        review
					$str .= $bullet."[ ".LIST_16." ] Re: <a href='".e_BASE."content.php?review.$comment_item_id.'>".$tp -> toHTML($content_heading, "admin")."</a><br />";
					break;
				}
			}
			break;
*/




			case 2: //        downloads
				$mp = MPREFIX;
				$qry = "SELECT download_name, {$mp}download_category.download_category_class FROM {$mp}download LEFT JOIN {$mp}download_category ON {$mp}download.download_category={$mp}download_category.download_category_id WHERE {$mp}download.download_id={$comment_item_id}";
				$sql -> db_Select_gen($qry);
				$row = $sql -> db_Fetch();
				extract($row);
				if (check_class($download_category_class))
				{
					$TYPE = LIST_11;
					$TITLE = " Re: <a href='".e_BASE."download.php?view.$comment_item_id'>".$tp -> toHTML($download_name, "admin")."</a>";
				}
			break;
			 
			case 3: //        faq
				$sql -> db_Select("faq", "faq_question", "faq_id=$comment_item_id ");
				$row = $sql -> db_Fetch();
				extract($row);
				$TYPE = LIST_15;
				$TITLE = "Re: <a href='".e_BASE."faq.php?view.$comment_item_id'>".$tp -> toHTML($faq_question, TRUE)."</a>";
			break;
			 
			case 4: //        poll comment
				$sql -> db_Select("polls", "*", "poll_id=$comment_item_id ");
				$row = $sql -> db_Fetch();
				extract($row);
				$TYPE = LIST_14;
				$TITLE = "Re: <a href='".e_BASE."comment.php?comment.poll.$comment_item_id'>".$tp -> toHTML($poll_title, TRUE)."</a>";
			break;
			 
			case 6: //        bugtracker
				$sql -> db_Select("bugtrack2_bugs", "bugtrack2_bugs_summary", "bugtrack2_bugs_id=$comment_item_id ");
				$row = $sql -> db_Fetch();
				 extract($row);
				$TYPE = LIST_13;
				$TITLE = "Re: <a href='".e_PLUGIN."bugtracker2/bugtracker2.php?0.bug.$comment_item_id'>".$tp -> toHTML($bugtrack2_bugs_summary)."</a>";
			break;
		}
		
		
		
		/* --- $handle = opendir(e_PLUGIN);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
				$plugin_handle = opendir(e_PLUGIN.$file."/");
				while (false !== ($file2 = readdir($plugin_handle))) {
					if ($file2 == "e_comment.php") {
						include(e_PLUGIN.$file."/".$file2);
						if ($comment_type == $e_plug_table) {
							$sql -> db_Select("".$db_table."", "".$link_name."", "".$db_id."=$comment_item_id ");
							$row = $sql -> db_Fetch();
							 extract($row);
							$nid = $comment_item_id;
							$link = $row[0];
							$str .= $bullet."[ ".$plugin_name." ] Re: <a href='".$reply_location."'>".$tp -> toHTML($link)."</a><br />";
							$comment_count++;
							break 2;
						}
					}
				}
			}
		} --- */

		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_COMMENT);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_COMMENT);
}
/* --- end comments --- */








/* --- bugs --- */
if($ITEMS = $sql->db_Select("bugtrack2_bugs", "*", "bugtrack2_bugs_datestamp>$lvisit ORDER BY bugtrack2_bugs_datestamp DESC LIMIT 0,20"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_16;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_BUG);
	foreach($newArray as $item)
	{
		extract($item);
		$TYPE = $bugtrack2_bugs_category;
		$TITLE = "<a href='".e_PLUGIN."bugtracker2/bugtracker2.php?0.bug.$bugtrack2_bugs_id'>".$tp -> toHTML($bugtrack2_bugs_summary)."</a>";
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_BUG);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_BUG);
}
/* --- end bugs --- */


/* --- ideas --- */
if($ITEMS = $sql->db_Select("ideas", "*", "ideas_datestamp>$lvisit ORDER BY deas_datestamp DESC LIMIT 0,20"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_17;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_IDEA);
	foreach($newArray as $item)
	{
		extract($item);
		$MEMBER = "<a href='".e_PLUGIN."ideas/ideas.php?show.$ideas_id'>".$tp -> toHTML($ideas_summary)."</a>";
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_IDEA);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_IDEA);
}
/* --- end ideas --- */
















/* --- new members --- */
if($ITEMS = $sql->db_Select("user", "user_id, user_name", "user_join>$lvisit AND user_ban='0' ORDER BY user_join DESC LIMIT 0,30"))
{
	$newArray = $sql -> db_getList();
	$HEADING = LIST_8;
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_START_MEMBER);
	foreach($newArray as $item)
	{
		extract($item);
		$MEMBER = "<a href='".e_BASE."user.php?id.$user_id'>$user_name</a>";
		$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_MAIN_MEMBER);
	}
	$mainStr .= preg_replace("/\{(.*?)\}/e", '$\1', $LISTNEW_END_MEMBER);
}
/* --- end new members --- */







if(trim(chop($mainStr)) == "")
{
	$mainStr = LIST_4;
}
if($LISTNEW_ENCLOSE)
{
	$ns -> tablerender($LISTNEW_HEADING, $mainStr);
}
else
{
	echo $mainStr;
}

require_once(FOOTERF);
?>