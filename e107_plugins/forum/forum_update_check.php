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
|     $Revision: 1.6 $
|     $Date: 2005-06-01 03:16:32 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
$dbupdate['forum_07'] =  LAN_UPDATE_8." .61x forums ".LAN_UPDATE_9." .7 forums";
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
		return FALSE; //needed
	}
}

?>
			