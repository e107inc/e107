<?php
if (!defined('e107_INIT')) { exit; }


class forum_latest // include plugin-folder in the name.
{
	function config()
	{
		$sql = e107::getDb();
		$reported_posts = $sql->db_Count('generic', '(*)', "WHERE gen_type='reported_post' OR gen_type='Reported Forum Post'");
		
		$var[0]['icon'] 	= E_16_FORUM;
		$var[0]['title'] 	= ADLAN_LAT_6;
		$var[0]['url']		= e_PLUGIN."forum/forum_admin.php?sr";
		$var[0]['total'] 	= $reported_posts;

		return $var;
	}	
}


?>