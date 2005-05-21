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
			