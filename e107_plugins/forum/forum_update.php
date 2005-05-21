<?php
if(!defined('e_HTTP'))
{
	exit;
}
require_once(e_PLUGIN.'forum/forum_class.php');
global $ns;
$forum = new e107forum;

$timestart = microtime();
$ttab = MPREFIX.'forum_t';
	
$text = "";
$text .= forum_stage1();
$text .= forum_stage2();
$text .= forum_stage3();
$text .= forum_stage4();
$text .= forum_stage5();
$text .= forum_stage6();

$timeend = microtime();
$diff = number_format(((substr($timeend, 0, 9)) + (substr($timeend, -10)) - (substr($timestart, 0, 9)) - (substr($timestart, -10))), 4);
$text .= "<br />script generation took $diff s";

$ns->tablerender('forum upgrade',$text);
	
function forum_stage1()
{
	global $sql;
	$ttab = MPREFIX.'forum_t';
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_edit_datestamp INT( 10 ) UNSIGNED NOT NULL");
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_lastuser VARCHAR( 30 ) NOT NULL");
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_total_replies INT UNSIGNED NOT NULL");
	$sql->db_Select_gen("ALTER TABLE #forum ADD forum_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;");
	$sql->db_Select_gen("ALTER TABLE #forum ADD `forum_sub` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `forum_parent` ;");
	return "Updated table structure <br />";
}
	
function forum_stage2()
{
	global $sql;
	$ttab = MPREFIX.'forum_t';
//	$numrows = $sql->db_Update('forum_t', "thread_anon = SUBSTRING(thread_user,3) WHERE thread_user LIKE '0.%'");
	$numrows = $sql->db_Update('forum_t', "thread_user = CAT('0.', thread_anon) WHERE thread_user = '0'");
	return $ret."Updated anonymous post info ... $numrows rows updated<br />";
}
	
function forum_stage3()
{
	global $sql;
	$sql->db_Select_gen("ALTER TABLE #forum_t CHANGE thread_user thread_user varchar( 250 ) NOT NULL");
	$sql->db_Select_gen("ALTER TABLE #forum_t DROP thread_anon"); 
	return "Updated thread_user & forum_anon field<br />";
}
	
function forum_stage4()
{	
	global $sql, $forum;
	$sql->db_Select_gen("SELECT thread_parent AS id, COUNT(*) AS amount FROM #forum_t WHERE thread_parent !=0 GROUP BY thread_parent");
	$threadArray = $sql->db_getList('ALL', FALSE, 0);
	foreach($threadArray as $threads)
	{
		extract($threads);
		$sql->db_Update("forum_t", "thread_total_replies=$amount WHERE thread_id=$id");
	}

	$ret = "Updated thread reply info...".count($threadArray). " threads updated.<br />";
	$forum = new e107forum;
	$forum->forum_update_counts('all');
	return $ret."Updated forum thread count info. <br />";
}

function forum_stage5()
{
	global $sql, $forum;
	$sql->db_Select_gen("ALTER TABLE #forum CHANGE forum_lastpost forum_lastpost_user VARCHAR( 200 ) NOT NULL"); 
	$sql->db_Select_gen("ALTER TABLE #forum ADD forum_lastpost_info VARCHAR( 40 ) NOT NULL AFTER forum_lastpost_user");
	set_time_limit(180);
	$forum->update_lastpost('forum', 'all', TRUE);
	return "Updated lastpost info <br />";
}

function forum_stage6()
{
	global $sql;
	global $PLUGINS_DIRECTORY;
	if(!$sql->db_Count('plugin','(*)',"WHERE plugin_name = 'Forum'"))
	{
		$sql->db_Insert('plugin',"0,'Forum','1.1','forum',1");
		return "Forum entry added to plugin table, set as installed.<br />";
	}
	else
	{
		$sql->db_Update('plugin',"plugin_installflag = 1 WHERE plugin_name='Forum'");
		$sql->db_Update('plugin',"plugin_version = '1.1' WHERE plugin_name='Forum'");
	}
	$sql->db_Update('links',"link_url='{$PLUGINS_DIRECTORY}forum/forum.php' WHERE link_name='Forum'");

}
	
	
/*
1) Add new fields
forum_t = thread_anon, thread_edit_datestamp, thread_lastuser
	
2) move anonymous post info into thread_anon
	
3) change thread_user to int(10) UNSIGNED
	
4) Update ALL lastpost info (forum_t and forum)
	
5) Remove all 'edited' info from posts (possible regex to move it)

**** NEED TO ADD FORUM PLUGIN INSTALLATION INFO IN PLUGIN TABLE ****

*/
	
?>