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
|     $Revision: 1.7 $
|     $Date: 2005-02-13 13:43:13 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

global $tp, $e107;
$gen = new convert;


$query = "
SELECT #comments.*, user_id, user_name FROM #comments
LEFT JOIN #user ON #comments.comment_author = #user.user_id 
ORDER BY comment_datestamp DESC LIMIT 0, ".$menu_pref['comment_display'];


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
	



	foreach($commentArray as $commentInfo)
	{

		extract($commentInfo);
		$datestamp = $gen->convert_date($comment_datestamp, "short");
		$poster = substr($comment_author, (strpos($comment_author, ".")+1));
		$comment_comment = strip_tags(eregi_replace("\[.*\]", "", $comment_comment)); // remove bbcode
		$comment_comment = $tp->toHTML($comment_comment, FALSE, "", "", $pref['menu_wordwrap']);

		
		
		if (strlen($comment_comment) > $menu_pref['comment_characters'])
		{
			$comment_comment = substr($comment_comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
		}

		switch ($comment_type) {
			case 0:
				$link = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".$e107->HTTPPath."comment.php?comment.news.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a><br />";
				break;
			case 2:
				$link = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".$e107->HTTPPath."content.php?article.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a><br />";
				break;
			case 4:
				$link = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".$e107->HTTPPath."content.php?poll.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a><br />";
				break;

		}

		$heading = ($menu_pref['comment_title'] ? " [ Re: <i>$comment_subject</i> ]<br />" : "");

		$text .= $link.$heading.$comment_comment."<br /><br />";
		

	}
}

$ns->tablerender($menu_pref['comment_caption'], $text, 'comment');






?>