<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_handlers/forum_mod.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
function forum_thread_moderate($p)
{
	global $sql;

	foreach($p as $key => $val)
	{
		if(preg_match("#(.*?)_(\d+)_x#",$key,$matches))
		{
			$act = $matches[1];
			$id = $matches[2];
	
			switch($act)
			{
				case 'lock' :
					$sql -> db_Update("forum_t", "thread_active='0' WHERE thread_id='$id' ");
					return FORLAN_CLOSE;
					break;

				case 'unlock' :
					$sql -> db_Update("forum_t", "thread_active='1' WHERE thread_id='$id' ");
					return FORLAN_OPEN;
					break;
				
				case 'stick' :
					$sql -> db_Update("forum_t", "thread_s='1' WHERE thread_id='$id' ");
					return FORLAN_STICK;
					break;
					
				case 'unstick' :
					$sql -> db_Update("forum_t", "thread_s='0' WHERE thread_id='$id' ");
					return FORLAN_UNSTICK;
					break;
			}
		}
	}
}


