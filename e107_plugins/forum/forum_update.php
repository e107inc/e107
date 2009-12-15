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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_update.php,v $
|     $Revision: 1.16 $
|     $Date: 2009-12-15 22:21:13 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN.'forum/forum_class.php');
global $ns;
$forum = new e107forum;

$timestart = microtime();
$ttab = MPREFIX.'forum_t';
	

if($sql->db_Select("plugin", "plugin_version", "plugin_name = 'Forum'"))
{
	$row = $sql->db_Fetch();
	$forum_version = $row['plugin_version'];
}


$forum_subs = FALSE;
/*
$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."forum");
$columns = mysql_num_fields($fields);		// Bug in PHP5.3 using mysql_num_fields() with mysql_list_fields()
for ($i = 0; $i < $columns; $i++)
{
	if("forum_sub" == mysql_field_name($fields, $i))
	{
		$forum_subs = TRUE;
	}
}
*/

if ($sql->db_Field('forum', 'forum_sub'))
{
	$forum_subs = TRUE;
}


$text = "";
if(!$forum_subs)
{
	$text .= forum_stage1();
	$text .= forum_stage2();
	$text .= forum_stage3();
	$text .= forum_stage4();
	$text .= forum_stage5();
	$text .= forum_stage6();
}

if($forum_version < 1.2)
{
	$text .= mods_to_userclass();
}
$text .= set_forum_version();

$timeend = microtime();
$diff = number_format(((substr($timeend, 0, 9)) + (substr($timeend, -10)) - (substr($timestart, 0, 9)) - (substr($timestart, -10))), 4);
$text .= "<br />script generation took $diff s";

if ($pref['developer']) {
	$ns->tablerender('forum upgrade',$text);
}
	
function forum_stage1()
{
	global $sql;
	$ttab = MPREFIX.'forum_t';
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_edit_datestamp int(10) unsigned NOT NULL default '0'");
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_lastuser varchar(30) NOT NULL default ''");
	$sql->db_Select_gen("ALTER TABLE #forum_t ADD thread_total_replies int(10) unsigned NOT NULL default '0'");
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
	$sql->db_Select_gen("ALTER TABLE #forum_t CHANGE thread_user thread_user varchar(250) NOT NULL default ''");
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
	$sql->db_Select_gen("ALTER TABLE #forum CHANGE forum_lastpost forum_lastpost_user varchar(200) NOT NULL default ''"); 
	$sql->db_Select_gen("ALTER TABLE #forum ADD forum_lastpost_info varchar(40) NOT NULL default '' AFTER forum_lastpost_user");
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
	}
	$sql->db_Update('links',"link_url='{$PLUGINS_DIRECTORY}forum/forum.php' WHERE link_name='Forum'");

}
	
function mods_to_userclass()
{
	global $sql;
	require_once(e_HANDLER."userclass_class.php");
	$_uc = new e_userclass;
	if($sql->db_Select("forum", "forum_id, forum_moderators","forum_parent != 0"))
	{
		$fList = $sql->db_getList();
		foreach($fList as $row)
		{
			if(!is_numeric($row['forum_moderators']))
			{
				$newclass = $_uc->class_create($row['forum_moderators'], "FORUM_MODS_");
				$sql->db_Update("forum", "forum_moderators = '{$newclass}' WHERE forum_id = '{$row['forum_id']}'");
			}
		}
	}
	return "Forum moderators converted to userclasses <br />";
}

function set_forum_version()
{
	global $sql;
	$new_version = "1.2";
	$sql->db_Update('plugin',"plugin_version = '{$new_version}' WHERE plugin_name='Forum'");
	return "Forum Version updated to version: $new_version <br />";
}	

?>