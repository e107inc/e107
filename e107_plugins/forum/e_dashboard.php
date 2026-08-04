<?php
if (!defined('e107_INIT')) { exit; }


class forum_dashboard // include plugin-folder in the name.
{
	
	function chart()
	{
		return false;
	}
	
	
	function status()
	{
		$sql = e107::getDb();
		$forum_posts = $sql->createQueryBuilder()->from('forum_post')->count();
		
		$var[0]['icon'] 	= defset('E_16_FORUM');
		$var[0]['title'] 	= defset('LAN_PLUGIN_FORUM_POSTS');
		$var[0]['url']		= e_PLUGIN."forum/forum_admin.php";
		$var[0]['total'] 	= $forum_posts;

		return $var;
	}	
	
	
	function latest()
	{
		$sql = e107::getDb();
		$reported_posts = $sql->createQueryBuilder()->from('generic')
			->whereIn('gen_type', array('reported_post', 'Reported Forum Post'))->count();
		
		$var[0]['icon'] 	= defset('E_16_FORUM');
		$var[0]['title'] 	= defset('ADLAN_LAT_6');
		$var[0]['url']		= e_PLUGIN."forum/forum_admin.php?mode=report&action=list";
		$var[0]['total'] 	= $reported_posts;

		return $var;
	}	
	
	
}
