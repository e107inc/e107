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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/content_update_check.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-28 11:32:06 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
$dbupdate['content_07'] =  LAN_UPDATE_8." .61x content ".LAN_UPDATE_9." .7 content";
function update_content_07($type='') 
{
	global $sql, $mySQLdefaultdb;
	if($type == 'do')
	{
		if(!isset($_POST['updateall']))
		{	
			include_once(e_PLUGIN.'content/content_update.php');
		}
	}
	else
	{
		// FALSE = needed, TRUE = not needed.
		if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content'"))
		{
			$row = $sql->db_Fetch();
			if($row['plugin_version'] < 1.21)
			{
				return FALSE;
			}else{
				return TRUE;
			}
		}
		return FALSE; //needed
	}
}

?>
			