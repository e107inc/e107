<?php

class forum_management
{
	function forum_install_pre(&$var)
	{
		print_a($var);
		echo "custom install 'pre' function<br /><br />";
	}

	function forum_install_post(&$var)
	{
		global $sql;
		echo "Setting all user_forums to 0 <br />";
		$sql -> db_Update("user", "user_forums='0'");		
	}
	
	function forum_uninstatll(&$var)
	{
		global $sql;
		$sql -> db_Update("user", "user_forums='0'");
	}

	function forum_upgrade(&$var)
	{
		global $sql;
		if(version_compare($var['current_plug']['plugin_version'], "1.2", "<"))
		{
			$qry = "ALTER TABLE #forum ADD forum_postclass TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL ;"
			$sql->db_Select_gen($qry);
		}
	}
}

