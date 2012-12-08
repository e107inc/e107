<?php
if (!defined('e107_INIT')) { exit; }


class forum_status // include plugin-folder in the name.
{
	function config()
	{
		$sql = e107::getDb();
		$forum_posts = $sql->db_Count('forum_post');
		
		$var[0]['icon'] 	= E_16_FORUM;
		$var[0]['title'] 	= ADLAN_113;
		$var[0]['url']		= e_PLUGIN."forum/forum_admin.php";
		$var[0]['total'] 	= $forum_posts;

		return $var;
	}	
}
?>