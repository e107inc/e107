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
|     $Revision: 1.5 $
|     $Date: 2008-12-20 00:55:29 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if(defined('e_PAGE') && e_PAGE == 'e107_update.php')
{
	echo "
	<script type='text/javascript'>
	window.location='".e_PLUGIN."forum/forum_update.php'
	</script>
	";
	exit;
}

$eplug_admin = true;
require_once('../../class2.php');
if (!getperms('P'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}
require_once(e_PLUGIN.'forum/forum_class.php');
require_once(e_ADMIN.'auth.php');
$forum = new e107forum;
$timestart = microtime();

$f = new forumUpgrade;
$e107 = e107::getInstance();

if(isset($_POST) && count($_POST))
{
	if(isset($_POST['skip_attach']))
	{
		$f->updateInfo['skip_attach'] = 1;
		$f->updateInfo['currentStep'] = 2;
		$f->setUpdateInfo();
	}

	var_dump($_POST);
	if(isset($_POST['nextStep']))
	{
		$tmp = array_keys($_POST['nextStep']);
		$f->updateInfo['currentStep'] = $tmp[0];
		$f->setUpdateInfo();
	}
}




$currentStep = (isset($f->updateInfo['currentStep']) ? $f->updateInfo['currentStep'] : 1);
$stepParms = (isset($stepParms) ? $stepParms : '');

if(function_exists('step'.$currentStep))
{
	$result = call_user_func('step'.$currentStep, $stepParms);
}

require(e_ADMIN.'footer.php');
exit;


function step1()
{
	global $f;
	$e107 = e107::getInstance();
	//Check attachment dir permissions
	if(!isset($f->updateInfo['skip_attach']))
	{
		$f->checkAttachmentDirs();
		if(isset($f->error['attach']))
		{
			$text = "
			<h3>ERROR:</h3>
			The following errors have occured.  These issues must be resolved if you ever want to enable attachment or image uploading in your forums. <br />If you do not ever plan on enabling this setting in your forum, you may click the 'skip' button <br /><br />
			";
			foreach($f->error['attach'] as $e)
			{
				$text .= '** '.$e.'<br />';
			}
			$text .= "
			<br />
			<form method='post'>
			<input class='button' type='submit' name='retest_attach' value='Retest Permissions' />
			&nbsp;&nbsp;&nbsp;
			<input class='button' type='submit' name='skip_attach'  value='Skip - I understand the risks' />
			</form>
			";
		}
		else
		{
			$text = "Attachment and attachment/thumb directories are writable
			<br /><br />
			<form method='post'>
			<input class='button' type='submit' name='nextStep[2]' value='Proceed to step 2' />
			</form>
			";
		}
		$e107->ns->tablerender('Attachment directory permissions', $text);
	}
}

function step2()
{
	$e107 = e107::getInstance();
	if(!isset($_POST['create_tables']))
	{
		$text = "
		This step will create the new forum_thread, forum_post, and forum_attach tables.  It will also create a forum_new table that will become the 'real' forum table once the data from the current table is migrated.
		<br /><br />
		<form method='post'>
		<input class='button' type='submit' name='create_tables' value='Proceed with table creation' />
		</form>
		";
		$e107->ns->tablerender('Step 2: Forum table creation', $text);
		return;
	}

	require_once(e_HANDLER.'db_table_admin_class.php');
	$db = new db_table_admin;
	
	$tabList = array('forum' => 'forum_new', 'forum_thread' => '', 'forum_post' => '', 'forum_track' => '');
	$ret = '';	
	$failed = false;
	$text = '';
	foreach($tabList as $name => $rename)
	{
		$text .= 'Creating table '.($rename ? $rename : $name).' -> ';
		$result = $db->createTable(e_PLUGIN.'forum/forum_sql.php', $name, true, $rename);
		if($result)
		{
			$text .= 'Success <br />';
		}
		else
		{
			$text .= 'Failed <br />';
			$failed = true;
		}
	}
	if($failed)
	{
		$text .= "
		<br /><br />
		Creation of table(s) failed.  You can not continue until these are create successfully!
		";
	}
	else
	{
			$text .= "
			<br /><br />
			<form method='post'>
			<input class='button' type='submit' name='nextStep[3]' value='Proceed to step 3' />
			</form>
			";
	}
	$e107->ns->tablerender('Step 2: Forum table creation', $text);
}

function step3()
{
	$e107 = e107::getInstance();
	$stepCaption = 'Step 3: Extended user field creation';
	if(!isset($_POST['create_extended']))
	{
		$text = "
		This step will create the new extended user fields required for the new forum code: <br />
		* user_plugin_forum_posts (to track number of posts for each user)<br />
		* user_plugin_forum_viewed (to track threads viewed by each user<br />
		<br /><br />
		<form method='post'>
		<input class='button' type='submit' name='create_extended' value='Proceed with field creation' />
		</form>
		";
		$e107->ns->tablerender($stepCaption, $text);
		return;
	}
	require_once(e_HANDLER.'user_extended_class.php');
	$ue = new e107_user_extended;
	$fieldList = array(
	'plugin_forum_posts' => EUF_INTEGER,
	'plugin_forum_viewed' => EUF_TEXTAREA
	);
	$failed = false;
	foreach($fieldList as $fieldName => $fieldType)
	{
		$text .= 'Creating extended user field user_'.$fieldName.' -> ';
		$result = $ue->user_extended_add_system($fieldName, $fieldType);
		if($result)
		{
			$text .= 'Success <br />';
		}
		else
		{
			$text .= 'Failed <br />';
			$failed = true;
		}
	}
	if($failed)
	{
		$text .= '
		<br /><br />
		Creation of extended field(s) failed.  You can not continue until these are create successfully!
		';
	}
	else
	{
			$text .= "
			<br /><br />
			<form method='post'>
			<input class='button' type='submit' name='nextStep[4]' value='Proceed to step 4' />
			</form>
			";
	}
	$e107->ns->tablerender($stepCaption, $text);
	
}


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
				if(!is_writable($dir))
				{
					$this->error['attach'][] = "Directory '{$dir}' exits, but is not writeable";
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
		$e107 = e107::getInstance();
		$info = mysql_real_escape_string(serialize($this->updateInfo));
		$qry = "UPDATE `#generic` Set gen_chardata = '{$info}' WHERE gen_type = 'forumUpgrade'";
		$e107->sql->db_Select_gen($qry);
	}
	
	function setNewVersion()
	{
		$e107 = e107::getInstance();
		$e107->sql->db_Update('plugin',"plugin_version = '{$this->newVersion}' WHERE plugin_name='Forum'");
		return "Forum Version updated to version: {$this->newVersion} <br />";
	}	

}

function forum_update_adminmenu()
{
		global $currentStep;
		
		$var[1]['text'] = 'Step 1 - Permissions';
		$var[1]['link'] = '#';

		$var[2]['text'] = 'Step 2 - Create new tables';
		$var[2]['link'] = '#';

		$var[3]['text'] = 'Step 3 - Create extended fields';
		$var[3]['link'] = '#';

		$var[4]['text'] = 'Step 4 - Move user data';
		$var[4]['link'] = '#';

		$var[5]['text'] = 'Step 5 - Migrate forum configuration';
		$var[5]['link'] = '#';

		$var[6]['text'] = 'Step 6 - Migrate threads/replies';
		$var[6]['link'] = '#';

		$var[7]['text'] = 'Step 7 - Calc counts/lastpost data';
		$var[7]['link'] = '#';

		$var[8]['text'] = 'Step 8 - Migrate any poll information';
		$var[8]['link'] = '#';

		$var[9]['text'] = 'Step 9 - Migrate any attachments';
		$var[9]['link'] = '#';

		$var[10]['text'] = 'Step 10 - Migrate any attachments';
		$var[10]['link'] = '#';

		$var[11]['text'] = 'Step 11 - Delete old forum data';
		$var[11]['link'] = '#';


		for($i=1; $i < $currentStep; $i++)
		{
			$var[$i]['text'] = "<span style='color:green;'>{$var[$i]['text']}</span>";
		}

		show_admin_menu('Forum Upgrade', $currentStep, $var);
}

?>