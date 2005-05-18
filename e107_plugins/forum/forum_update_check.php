<?php
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
				$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."forum_t");
				$columns = mysql_num_fields($fields);
				for ($i = 0; $i < $columns; $i++)
				{
					if("thread_anon" == mysql_field_name($fields, $i))
					{
						return FALSE; //needed
					}
				}
				return TRUE; // not needed
			}
		}
		return FALSE; //needed
	}
}

?>
			