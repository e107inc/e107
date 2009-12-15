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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_update_check.php,v $
|     $Revision: 1.9 $
|     $Date: 2009-12-15 22:21:13 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$dbupdatep['forum_07'] =  LAN_UPDATE_8." .617 forums ".LAN_UPDATE_9." .7 forums";
function update_forum_07($type) 
{
	global $sql, $mySQLdefaultdb;
	if($type == 'do')
	{
		if(!isset($_POST['updateall']))
		{	
			include_once(e_PLUGIN.'forum/forum_update.php');
		}
	}
	else
	{
		// FALSE = needed, TRUE = not needed.
		if($sql->db_Select("plugin", "plugin_version", "plugin_name = 'Forum'"))
		{
			$row = $sql->db_Fetch();
			if($row['plugin_version'] < 1.2)
			{
				return FALSE;
			}
		}


		// Looking for:
		//	forum_lastpost_info in table 'forum' - update if absent
		//	thread_anon in table forum_t - update if absent
		//	forum_sub in table 'forum' - update if present
		// Bug in PHP5.3 using mysql_num_fields() with mysql_list_fields() - hence code replaced
		/*
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."forum");
		if(!$fields)
		{
			return TRUE;
		}
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++)
		{
			if ("forum_lastpost_info" == mysql_field_name($fields, $i))	
			{
				$flist = mysql_list_fields($mySQLdefaultdb, MPREFIX."forum_t");
				$cols = mysql_num_fields($flist);
				for ($x = 0; $x < $cols; $x++)
				{
					if("thread_anon" == mysql_field_name($flist, $x))
					{
						return FALSE; //needed
					}
				}
			}
			if("forum_sub" == mysql_field_name($fields, $i))
			{
				return TRUE; //not needed
			}
		}
		*/

		if ($sql->db_Field('forum', 'forum_lastpost_info'))
		{
			if (!$sql->db_Field('forum_t', 'thread_anon'))
			{
				if ($sql->db_Field('forum', 'forum_sub')) return TRUE;		// Return if no update needed
			}
		}
		return FALSE; //needed
	}
}

?>
			