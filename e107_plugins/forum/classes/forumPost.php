<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * forumThread class file
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/classes/forumPost.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-09-06 04:30:46 $
 * $Author: mcfly_e107 $
 *
*/

class plugin_forum_classes_forumPost
{
	public $post;

	public function __construct($id=false, $start=0, $num=0)
	{
		if($id) {
			$this->loadPost($id, $start, $num);
		}
	}

	function loadPost($id, $start, $num)
	{
		$e107 = e107::getInstance();
		$id = (int)$id;
		$ret = false;
		if('post' === $start)
		{
			$qry = '
			SELECT u.user_name, t.thread_active, t.thread_datestamp, t.thread_name, p.* FROM `#forum_post` AS p
			LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
			LEFT JOIN `#user` AS u ON u.user_id = p.post_user
			WHERE p.post_id = '.$id;
		}
		else
		{
			$qry = "
				SELECT p.*,
				u.user_name, u.user_customtitle, u.user_hideemail, u.user_email, u.user_signature,
				u.user_admin, u.user_image, u.user_join, ue.user_plugin_forum_posts,
				eu.user_name AS edit_name
				FROM `#forum_post` AS p
				LEFT JOIN `#user` AS u ON p.post_user = u.user_id
				LEFT JOIN `#user` AS eu ON p.post_edit_user IS NOT NULL AND p.post_edit_user = eu.user_id
				LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = p.post_user
				WHERE p.post_thread = {$id}
				ORDER BY p.post_datestamp ASC
				LIMIT {$start}, {$num}
			";
		}
		if($e107->sql->db_Select_gen($qry))
		{
			$this->post = array();
			while($row = $e107->sql->db_Fetch())
			{
				$this->post[] = $row;
			}
			if('post' === $start) { $this->post = $this->post[0]; }
			return true;
		}
		return false;
	}


}
