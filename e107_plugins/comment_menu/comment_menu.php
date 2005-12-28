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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/comment_menu/comment_menu.php,v $
|     $Revision: 1.17 $
|     $Date: 2005-12-28 16:12:59 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $tp, $e107;
$gen = new convert;

$query = "
SELECT c.*, u.user_id, u.user_name FROM #comments AS c
LEFT JOIN #user AS u ON FLOOR(c.comment_author) = u.user_id 
ORDER BY c.comment_datestamp DESC LIMIT 0, ".intval($menu_pref['comment_display']);

$results = $sql->db_Select_gen($query);
$commentArray = $sql->db_getList();

if(!$results)
{
	// no posts yet ..
	$text = CM_L1;
}
else
{
	$text = "";

	$bullet_img = THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif");
	foreach($commentArray as $c)
	{
		$datestamp = $gen->convert_date($c['comment_datestamp'], "short");
		$poster = substr($c['comment_author'], (strpos($c['comment_author'], ".")+1));
		$comment = strip_tags(preg_replace("/\[.*\]/", "", $c['comment_comment'])); // remove bbcode
		$comment = $tp->toHTML($comment, FALSE, "", "", $pref['menu_wordwrap']);
		
		if (strlen($comment) > $menu_pref['comment_characters'])
		{
			$comment = substr($comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
		}

		$link = "";
		switch ($c['comment_type']) {
			case "0":
				$link = "<img src='{$bullet_img}' alt='' /> <a href='".$e107->http_path."comment.php?comment.news.{$c['comment_item_id']}'><b>".$poster."</b> on ".$datestamp."</a><br />";
				break;
			case "4":
				$link = "<img src='{$bullet_img}' alt='' /> <a href='".$e107->http_path."comment.php?comment.poll.{$c['comment_item_id']}'><b>".$poster."</b> on ".$datestamp."</a><br />";
				break;
			case "profile":
				if(USER)
				{
					$link = "<img src='{$bullet_img}' alt='' /> <a href='".$e107->http_path."user.php?id.{$c['comment_item_id']}'><b>".$poster."</b> on ".$datestamp."</a><br />";
				}
				break;

			default:
				// It's probably from a 3rd party plugin so load in the e_comment file to get the URL to use
				$ecomment = e_PLUGIN.$c['comment_type']."/e_comment.php";
				if(is_readable($ecomment))
				{
					$nid = $c['comment_item_id'];
					include($ecomment);
					$link = "<img src='{$bullet_img}' alt='' /> <a href='$reply_location'><b>".$poster."</b> on ".$datestamp."</a><br />";
				}
				break;
		}

		if($link)
		{
			$heading = ($menu_pref['comment_title'] ? " [ Re: <i>{$c['comment_subject']}</i> ]<br />" : "");
			$text .= $link.$heading.$comment."<br /><br />";
		}
	}
}

$ns->tablerender($menu_pref['comment_caption'], $text, 'comment');
?>