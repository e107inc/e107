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
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."forum_t");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			if ("thread_total_replies" == mysql_field_name($fields, $i)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

?>
			