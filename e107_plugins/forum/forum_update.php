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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/forum/forum_update.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-12-18 22:03:45 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;
$timestart = microtime();

$f = new forumUpgrade;
$e107 = e107::getInstance();

//Check attachment dir permissions
if(!isset($f->updateInfo['skip_attach']))
{
	$f->checkAttachmentDirs();
	if(isset($f->error['attach']))
	{
		$errorText = "
		The following errors have occured.  These issues must be resolved if you ever want to enable attachment or image uploading in your forums. <br />If you do not ever plan on enabling this setting in your forum, you may click the 'skip' button <br /><br />
		";
		foreach($f->error['attach'] as $e)
		{
			$errorText .= '** '.$e.'<br />';
		}
		$e107->ns->tablerender('Attachment directory error', $errorText);
		require(e_ADMIN.'footer.php');
		exit;
	}
}


//print_a($f->error);

class forumUpgrade
{
	var	$newVersion = '2.0';
	var $error = array();
	var $updateInfo;

	function forumUpgrade()
	{
		$this->getUpdateInfo();
	}


	function checkAttachmentDirs()
	{
		$dirs = array(
		e_PLUGIN.'forum/attachments/',
		e_PLUGIN.'forum/attachments/thumb'
		);
		
		foreach($dirs as $dir)
		{
			if(!file_exists($dir))
			{
				if(!mkdir($dir))
				{
					$this->error['attach'][] = "Directory '{$dir}' does not exist and I was unable to create it";
				}
			}
			else
			{
				if(is_writable($dir))
				{
					$this->error['attach'][] = "Directory '{$dir}' exits, but it now writeable";
				}
			}
		}
	}

	function getUpdateInfo()
	{
		$e107 = e107::getInstance();
		if($e107->sql->db_Select('generic', '*', "gen_type = 'forumUpgrade'"))
		{
			$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
			$this->updateInfo = unserialize($row['gen_chardata']);
		}
		else
		{
			$qry = "INSERT INTO `#generic` (gen_type) VALUES ('forumUpgrade')";
			$e107->sql->db_Select_gen($qry);
			$this->updateInfo = array();
		}
	}

	function setUpdateInfo()
	{
		$info = mysql_real_escape_string(serialize($this->updateInfo));
		$qry = "UPDATE `#generic` Set gen_chardata = '{$info}' WHERE gen_type = 'forumUpgrade'";
		$e107->sql->db_Select_gen($qry);
	}
	
	function setNewVersion()
	{
		global $sql;
		$sql->db_Update('plugin',"plugin_version = '{$this->newVersion}' WHERE plugin_name='Forum'");
		return "Forum Version updated to version: {$this->newVersion} <br />";
	}	

}
?>