<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum upgrade routines
 *
 */

define('e_ADMIN_AREA', true);
require_once ('../../class2.php');

if (!getperms('P'))
{
	e107::redirect();
	exit ;
}


require_once (e_PLUGIN . 'forum/forum_class.php');
require_once (e_ADMIN . 'auth.php');

if (e_QUERY == "reset")
{
	unset($_SESSION['forumUpgrade']);
	unset($_SESSION['forumupdate']);
}


//unset($_SESSION['forumupdate']['thread_last']);
//	unset($_SESSION['forumupdate']['thread_count']);

$forum = new e107forum(true);
$timestart = microtime();

$f = new forumUpgrade;

$sql = e107::getDb();

if($_GET['reset'])
{
	unset($_SESSION['forumUpgrade']);
	unset($_SESSION['forumupdate']);
	$f -> updateInfo['currentStep'] = intval($_GET['reset']);	
	$f -> setUpdateInfo();

}



if (e_AJAX_REQUEST)
{
	if (!vartrue($_GET['mode']))
	{
		echo "data-progress-mode not set!";
		exit ;
	}

	$func = 'step' . intval($_GET['mode']) . "_ajax";

	if (function_exists($func))
	{
		call_user_func($func);
	}
	else
	{
		echo $func . "() doesn't exist!";
	}

	exit ;
}

$upgradeNeeded = $f -> checkUpdateNeeded();
$upgradeNeeded = true;
if (!$upgradeNeeded)
{
	$mes = e107::getMessage();

	$mes -> addInfo("The forum is already at the most recent version, no upgrade is required");
	$ns -> tablerender('Forum Upgrade', $mes -> render());
	require (e_ADMIN . 'footer.php');
	exit ;
}

if (isset($_POST) && count($_POST))
{
	if (isset($_POST['skip_attach']))
	{
		$f -> updateInfo['skip_attach'] = 1;
		$f -> updateInfo['currentStep'] = 2;
		$f -> setUpdateInfo();
	}

	if (isset($_POST['nextStep']))
	{
		$tmp = array_keys($_POST['nextStep']);
		$f -> updateInfo['currentStep'] = $tmp[0];
		$f -> setUpdateInfo();
	}
}

$currentStep = (isset($f -> updateInfo['currentStep']) ? $f -> updateInfo['currentStep'] : 1);
$stepParms = (isset($stepParms) ? $stepParms : '');

//echo "currentStep = $currentStep <br />";
if (function_exists('step' . $currentStep))
{
	$result = call_user_func('step' . $currentStep, $stepParms);
}

require (e_ADMIN . 'footer.php');
exit ;

function step1()
{

	global $f;
	$f -> updateInfo['currentStep'] = 1;
	$f -> setUpdateInfo();

	$mes = e107::getMessage();
	//Check attachment dir permissions
	if (!isset($f -> updateInfo['skip_attach']))
	{
		$f -> checkAttachmentDirs();
		if (isset($f -> error['attach']))
		{
			$text = "
			<h3>ERROR:</h3>
			The following errors have occured.  These issues must be resolved if you ever want to enable attachment or image uploading in your forums. <br />If you do not ever plan on enabling this setting in your forum, you may click the 'skip' button <br /><br />
			";
			foreach ($f->error['attach'] as $e)
			{
				$text .= '** ' . $e . '<br />';
			}
			$text .= "
			<br />
			<form method='post' action='" . e_SELF . "?step=2'>
			<input class='btn' type='submit' name='retest_attach' value='Retest Permissions' />
			&nbsp;&nbsp;&nbsp;
			<input class='btn btn-success' type='submit' name='skip_attach'  value='Skip - I understand the risks' />
			</form>
			";
		}
		else
		{
			$mes -> addSuccess("Attachment and attachment/thumb directories are writable");

			$text = "<form method='post' action='" . e_SELF . "?step=2'>
			<input class='btn btn-success' type='submit' name='nextStep[2]' value='Proceed to step 2' />
			</form>
			";
		}
		e107::getRender() -> tablerender('Step 1: Attachment directory permissions', $mes -> render() . $text);
	}
}

function step2()
{
	$mes = e107::getMessage();
	$ns = e107::getRender();

	if (!isset($_POST['create_tables']))
	{
		$text = "
		This step will create the new forum_thread, forum_post, and forum_attach tables.  It will also create a forum_new table that will become the 'real' forum table once the data from the current table is migrated.
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...'  type='submit' name='create_tables' value='Proceed with table creation' />
		</form>
		";
		$ns -> tablerender('Step 2: Forum table creation', $text);
		return;
	}

	// FIXME - use db_verify. ??
	require_once (e_HANDLER . 'db_table_admin_class.php');
	$db = new db_table_admin;

	$tabList = array(
		'forum' => 'forum_new',
		'forum_thread' => '',
		'forum_post' => '',
		'forum_track' => ''
	);
	//
	$ret = '';
	$failed = false;
	$text = '';
	$sql = e107::getDb();
	foreach ($tabList as $name => $rename)
	{
		$message = 'Creating table ' . ($rename ? $rename : $name);

		$curTable = ($rename ? $rename : $name);

		if($sql->isTable($curTable) && $sql->isEmpty($curTable))
		{
			$mes -> addSuccess("Skipping table ".$name." (already exists)");
			continue;
		}

		$result = $db->createTable(e_PLUGIN . 'forum/forum_sql.php', $name, true, $rename);
		if ($result === true)
		{
			$mes -> addSuccess($message);
			//	$text .= 'Success <br />';
		}
		elseif ($result !== true)
		{
			//	$text .= 'Failed <br />';
			$mes -> addError($message);
			$failed = true;
		}
	}
	if ($failed)
	{
		$mes -> addError("Creation of table(s) failed.  You can not continue until these are created successfully!");

	}
	else
	{
		$text = "<form method='post' action='" . e_SELF . "?step=3'>
			<input class='btn btn-success' type='submit' name='nextStep[3]' value='Proceed to step 3' />
			</form>";
	}
	$ns -> tablerender('Step 2: Forum table creation', $mes -> render() . $text);
}



// FIXME - use e107::getPlugin()->manage_extended_field('add', $name, $attrib, $source)
function step3()
{
	$ns = e107::getRender();
	$mes = e107::getMessage();

	$stepCaption = 'Step 3: Extended user field creation';
	if (!isset($_POST['create_extended']))
	{
		$text = "
		This step will create the new extended user fields required for the new forum code: <br />
		<ul>
		<li>user_plugin_forum_posts (to track number of posts for each user)</li>
		<li>user_plugin_forum_viewed (to track threads viewed by each user</li>
		</ul>
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='create_extended' value='Proceed with field creation' />
		</form>
		";

		$ns -> tablerender($stepCaption, $text);
		return;
	}


	$fieldList = array(
		'plugin_forum_posts' => 'integer',
		'plugin_forum_viewed' => 'radio'
	);

	$failed = false;
	$ext = e107::getUserExt();

	foreach ($fieldList as $fieldName => $fieldType)
	{

		$result = $ext->user_extended_add_system($fieldName, $fieldType);

		if ($result === true)
		{
			$mes -> addSuccess('Creating extended user field user_' . $fieldName);
		}
		else
		{
			$mes -> addError('Creating extended user field user_' . $fieldName);
			$mes->addDebug(print_a($result,true));
			$failed = true;
		}
	}

	if ($failed)
	{
		$mes -> addError("Creation of extended field(s) failed.  You can not continue until these are create successfully!");

	}
	else
	{
		$text = "
			<form method='post' action='" . e_SELF . "?step=4'>
			<input class='btn btn-success' type='submit' name='nextStep[4]' value='Proceed to step 4' />
			</form>
			";
	}

	$ns -> tablerender($stepCaption, $mes -> render() . $text);

}

function step4()
{
	global $pref;

	$mes = e107::getMessage();
	$ns = e107::getRender();

	$stepCaption = 'Step 4: Move user specific forum data and forum prefs';
	if (!isset($_POST['move_user_data']))
	{
		$text = "
		This step will move the main forum preferences into its own table row.  It will also move all user_viewed data from user table into the user extended table.<br />
		The user_forum field data will not be moved, as it will be recalculated later.<br />
		<br />
		Depending on the size of your user table, this step could take a while.
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='move_user_data' value='Proceed with user data move' />
		</form>
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}

	/** Convert forum prefs to their own row **/
	$fconf = e107::getPlugConfig('forum', '', false);
	$coreConfig = e107::getConfig();
	$old_prefs = array();
	foreach ($pref as $k => $v)
	{
		if (substr($k, 0, 6) == 'forum_')
		{
			$nk = substr($k, 6);
			$mes -> addDebug("Converting $k to $nk");
			$old_prefs[$nk] = $v;
			$coreConfig -> remove($k);
		}
	}

	// Remove old prefs (no longer used in v2)
	$forumPrefList = array(
		'reported_post_email',
		//'email_notify',
		//'email_notify_on'
	);

	foreach ($forumPrefList as $_fp)
	{
		$mes -> addDebug("converting $_fp to $_fp");
		$old_prefs[$_fp] = $coreConfig -> get($_fp);
		$coreConfig -> remove($_fp);
	}

	$fconf -> setPref($old_prefs) -> save(false, true);
	$coreConfig -> save(false, true);


	// -----Upgrade old menu prefs ----------------
	global $forum;
	$forum->upgradeLegacyPrefs();

	// --------------------



	$result = array(
		'usercount' => 0,
		'viewcount' => 0,
		'trackcount' => 0
	);
	$db = new db;
	if ($db -> select('user', 'user_id, user_viewed, user_realm', "user_viewed != '' OR user_realm != ''"))
	{
		require_once (e_HANDLER . 'user_extended_class.php');
		$ue = new e107_user_extended;

		while ($row = $db -> fetch())
		{
			$result['usercount']++;
			$userId = (int)$row['user_id'];

			$viewed = $row['user_viewed'];
			$viewed = trim($viewed, '.');
			$tmp = preg_split('#\.+#', $viewed);
			$viewed = implode(',', $tmp);

			$realm = $row['user_realm'];
			$realm = str_replace('USERREALM', '', $realm);
			$realm = trim($realm, '-.');
			$trackList = preg_split('#\D+#', $realm);

			$debug = 'user_id = ' . $userId . '<br />';
			$debug .= 'viewed = ' . $viewed . '<br />';
			$debug .= 'realm = ' . $realm . '<br />';
			$debug .= 'tracking = ' . implode(',', $trackList) . '<br />';
		//	$debug .= print_a($trackList, true);
		//	$mes -> addDebug($debug);

			if ($viewed != '')
			{
				$ue->user_extended_setvalue($userId, 'plugin_forum_viewed', ($viewed));
				$result['viewcount']++;
			}

			if (is_array($trackList) && count($trackList))
			{
				foreach ($trackList as $threadId)
				{
					$result['trackcount']++;
					$threadId = (int)$threadId;
					if ($threadId > 0)
					{
						$tmp = array();
						$tmp['track_userid'] = $userId;
						$tmp['track_thread'] = $threadId;

						e107::getDb() -> insert('forum_track', $tmp);
					}
				}
			}
		}
	}

	$mes -> addSuccess("User data move results:
	<ul>
	<li>Number of users processed: {$result['usercount']} </li>
	<li>Number of viewed data processed: {$result['viewcount']} </li>
	<li>Number of tracked records added: {$result['trackcount']} </li>
	</ul>
	");

	$text = "<form method='post' action='" . e_SELF . "?step=5'>
	<input class='btn btn-success' type='submit' name='nextStep[5]' value='Proceed to step 5' />
	</form>";

	$ns -> tablerender($stepCaption, $mes -> render() . $text);

}

function step5()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$mes = e107::getMessage();

	$stepCaption = 'Step 5: Migrate forum data';
	if (!isset($_POST['move_forum_data']))
	{
		$text = "This step will copy all of your forum configuration from the `forum` table into the `forum_new` table.<br />
		Once the information is successfully copied, the existing 1.0 forum table will be renamed `forum_old` and the newly created `forum_new` table will be renamed `forum`.<br />
		<br /><br />";
		$text .= "
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='move_forum_data' value='Proceed with forum data move' />
		</form>
		";
		$ns -> tablerender($stepCaption, $mes -> render() . $text);
		return;
	}

	$counts = array(
		'parents' => 0,
		'forums' => 0,
		'subs' => 0
	);
	//XXX Typo on 'parents' ?

	if ($sql -> select('forum'))
	{
		$forumList = $sql -> db_getList();
		$sefs = array();

		foreach ($forumList as $forum)
		{
			if ($forum['forum_parent'] == 0)
			{
				$counts['parents']++;
			}
			elseif ($forum['forum_sub'] != 0)
			{
				$counts['subs']++;
			}
			else
			{
				$counts['forums']++;
			}

			$tmp = $forum;
			$tmp['forum_threadclass'] = $tmp['forum_postclass'];
			$tmp['forum_options'] = '_NULL_';
			$forum_sef = eHelper::title2sef($forum['forum_name'],'dashl');

			if(isset($sefs[$forum_sef]))
            {
                $forum_sef .= "-2";
            }

			$tmp['forum_sef'] = $forum_sef;

            $sefs[$forum_sef] = true;

			//			$tmp['_FIELD_TYPES'] = $ftypes['_FIELD_TYPES'];
			if ($sql -> insert('forum_new', $tmp))
			{

			}
			else
			{
				$mes->addDebug("Insert failed on " . print_a($tmp, true));
				$mes->addError($sql->getLastErrorText());
			}

		}
	}
	else
	{
		$counts = array('parents'=>'n/a', 'forums'=>'n/a', 'subs'=>'n/a');
	}
		$mes -> addSuccess("
		Forum data move results:
		<ul>
		<li>Number of forum parents processed: {$counts['parents']} </li>
		<li>Number of forums processed: {$counts['forums']} </li>
		<li>Number of sub forums processed: {$counts['subs']} </li>
		</ul>
		");

		$result = $sql -> gen('RENAME TABLE `#forum`  TO `#forum_old` ') ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes -> add("Renaming forum to forum_old", $result);

		$result = $sql -> gen('RENAME TABLE `#forum_new`  TO `#forum` ') ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes -> add("Renaming forum_new to forum", $result);

		$text = "
		<form method='post' action='" . e_SELF . "?step=6'>
		<input class='btn btn-success' type='submit' name='nextStep[6]' value='Proceed to step 6' />
		</form>
		";

		$ns -> tablerender($stepCaption, $mes -> render() . $text);



}



function step6()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$mes = e107::getMessage();
	
	$stepCaption = 'Step 6: Thread and post data';

	$_SESSION['forumupdate']['thread_total'] = $sql -> count('forum_t', '(*)', "WHERE thread_parent = 0");
	$_SESSION['forumupdate']['thread_count'] = 0;
	$_SESSION['forumupdate']['thread_last'] = 0;

	$text = "This step will copy all of your existing forum threads and posts into the new `forum_thread` and `forum_post` tables.<br />
		Depending on your forum size and speed of server, this could take some time.<br /><br /> ";

	$text .= renderProgress("Begin thread data move", 6);
	$ns->tablerender($stepCaption, $mes -> render() . $text);

}


function renderProgress($caption, $step)
{
	
	if(!$step)
	{
		return "No step entered in function";	
	}
	
	$thisStep = 'step'.$step;
	$nextStep = 'step'.($step + 1);
	
	$text = '
		<div class="row-fluid">
			<div class="span9 well">
				<div class="progress progress-success progress-striped active" id="progressouter">
	   				<div class="progress-bar bar" role="progressbar" id="progress"></div>
				</div>
			
			<a id="'.$thisStep.'" data-loading-text="Please wait..." data-progress="' . e_SELF . '"  data-progress-target="progress"  data-progress-mode="'.$step.'" data-progress-show="'.$nextStep.'" data-progress-hide="'.$thisStep.'" class="btn btn-primary e-progress" >'.$caption.'</a>
			</div>
		</div>';

	$text .= "<form method='post' action='" . e_SELF . "?step=".($step+1)."'>
		<input id='".$nextStep."' style='display:none' class='btn btn-success' type='submit' name='nextStep[".($step+1)."]' value='Proceed to step ".($step+1)."' />
		</form>";	
	
	return $text;
}



function step6_ajax()
{
	global $f;
	$sql = e107::getDb();

	$lastThread = vartrue($_SESSION['forumupdate']['thread_last'], 0);

	$qry = "
	SELECT thread_id FROM `#forum_t`
	WHERE thread_parent = 0
	AND thread_id > {$lastThread}
	ORDER BY thread_id ASC
	LIMIT 0, 300
	";

	if ($sql -> gen($qry))
	{
		$threadList = $sql -> db_getList();

		foreach ($threadList as $t)
		{
			$id = (int)$t['thread_id'];
			$result = $f -> migrateThread($id);

			if ($result === false)
			{
				echo "Error";
			}
			else
			{
				$_SESSION['forumupdate']['thread_last'] = $id;
				$_SESSION['forumupdate']['thread_count']++;
			}
		}

	}
	else
	{
		echo 100;
		exit;
	}

	echo round(($_SESSION['forumupdate']['thread_count'] / $_SESSION['forumupdate']['thread_total']) * 100, 1);

}




function step7()
{
	$ns = e107::getRender();
	$stepCaption = 'Step 7: Calculate user post counts';
	if (!isset($_POST['calculate_usercounts']))
	{
		$text = "
		This step will calculate post count information for all users, as well as recount all for thread and reply counts.
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='calculate_usercounts' value='Proceed with post count calculation' />
		</form>
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}

	global $forum;
	require_once (e_HANDLER . 'user_extended_class.php');
	$ue = new e107_user_extended;

	$counts = $forum -> getUserCounts();
	foreach ($counts as $uid => $count)
	{
		$ue -> user_extended_setvalue($uid, 'user_plugin_forum_posts', $count, 'int');
	}
	$forum -> forumUpdateCounts('all', true);

	//	var_dump($counts);

	$text = "
	Successfully recalculated forum posts for " . count($counts) . " users.
	<br /><br />
	<form method='post' action='" . e_SELF . "?step=8'>
	<input class='btn btn-success' type='submit' name='nextStep[8]' value='Proceed to step 8' />
	</form>
	";
	$ns -> tablerender($stepCaption, $text);
}








function step8()
{
	$sql = e107::getDb();
	$mes = e107::getMessage();
	
	$stepCaption = 'Step 8: Calculate last post information';
	
	
	$_SESSION['forumupdate']['lastpost_total'] = $sql -> count('forum', '(*)', "WHERE forum_parent != 0");
	$_SESSION['forumupdate']['lastpost_count'] = 0;
	$_SESSION['forumupdate']['lastpost_last'] = 0;
	
	$mes->addDebug("Total LastPost: ".$_SESSION['forumupdate']['lastpost_total']);
	
	$text = "
		This step will recalculate all thread and forum lastpost information";
		
	$text .= renderProgress('Proceed with lastpost calculation',8);
	
	e107::getRender() -> tablerender($stepCaption, $mes->render(). $text);
	return;
	

}

function step8_ajax()
{
	$sql = e107::getDb();
	
	$lastThread = vartrue($_SESSION['forumupdate']['lastpost_last'], 0);
	
	global $forum;

	if ($sql->select('forum', 'forum_id', 'forum_parent != 0 AND forum_id > '.$lastThread.' ORDER BY forum_id LIMIT 2'))
	{
		while ($row = $sql->fetch())
		{
			$parentList[] = $row['forum_id'];
		}

		foreach($parentList as $id)
		{
			set_time_limit(60);
			$forum->forumUpdateLastpost('forum', $id, $updateThreads);
			$_SESSION['forumupdate']['lastpost_last'] = $id;
			$_SESSION['forumupdate']['lastpost_count']++;
		}
	}
	else
	{
		echo 100;
		exit;
	}

	echo round(($_SESSION['forumupdate']['lastpost_count'] / $_SESSION['forumupdate']['lastpost_total']) * 100);
}



function step9()
{

	$sql = e107::getDb();
	
	$stepCaption = 'Step 9: Migrate poll information';
	if (!isset($_POST['migrate_polls']))
	{
		$text = "
		This step will recalculate all poll information that has been entered in the forums.
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='migrate_polls' value='Proceed with poll migration' />
		</form>
		";
		e107::getRender() -> tablerender($stepCaption, $text);
		return;
	}

	$qry = "
	SELECT t.thread_id, p.poll_id FROM `#polls` AS p
	LEFT JOIN `#forum_thread` AS t ON t.thread_id =  p.poll_datestamp
	WHERE t.thread_id IS NOT NULL
	";
	if ($sql -> gen($qry))
	{
		while ($row = $sql -> fetch())
		{
			$threadList[] = $row['thread_id'];
		}
		foreach ($threadList as $threadId)
		{
			if ($sql -> select('forum_thread', 'thread_options', 'thread_id = ' . $threadId, 'default'))
			{
				$row = $sql -> fetch();
				if ($row['thread_options'])
				{
					$opts = unserialize($row['thread_options']);
					$opts['poll'] = 1;
				}
				else
				{
					$opts = array('poll' => 1);
				}
				$tmp = array();
				$tmp['thread_options'] = serialize($opts);
				$tmp['WHERE'] = 'thread_id = ' . $threadId;
				//				$tmp['_FIELD_TYPES']['thread_options'] = 'escape';
				$sql -> update('forum_thread', $tmp);
			}
		}
	}
	else
	{
		$text = 'No threads found! <br />';
	}

	$text .= "
	Successfully migrated forum poll information for " . count($threadList) . " thread poll(s).
	<br /><br />
	<form method='post' action='" . e_SELF . "?step=10'>
	<input class='btn btn-success' type='submit' name='nextStep[10]' value='Proceed to step 10' />
	</form>
	";
	e107::getRender() -> tablerender($stepCaption, $text);
}




function step10()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$mes = e107::getMessage();

	global $f;

	$stepCaption = 'Step 10: Migrate forum attachments';
	
	$_SESSION['forumupdate']['attachment_total'] = $sql -> count('forum_post', '(*)', "WHERE post_entry LIKE '%public/%' ");
	$_SESSION['forumupdate']['attachment_count'] = 0;
	$_SESSION['forumupdate']['attachment_last'] = 0;

	if ($_SESSION['forumupdate']['attachment_total'] == 0)
	{
		$text = "
		No forum attachments found. 
		<br /><br />
		<form method='post' action='" . e_SELF . "?step=11'>
		<input class='btn btn-success' type='submit' name='nextStep[11]' value='Proceed to step 11' />
		</form>
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}

	$text = "
		This step will migrate the forum attachment information that was found in <b>" . $_SESSION['forumupdate']['attachment_total'] . "</b> posts.<br />
		All files will be moved from the e107_files/public directory into the <b>" . e_MEDIA . "plugins/forum/ </b> directory and related posts will be updated accordingly.
		<br /><br />
		";

	$text .= renderProgress("Begin attachment migration",10);

	file_put_contents(e_LOG."forum_upgrade_attachments.log",'');		// clear the log. 

	$ns -> tablerender($stepCaption, $mes -> render() . $text);

}

/**
 * Attachments 
 */
function step10_ajax()//TODO
{
	$sql = e107::getDb();
	global $f;

	$lastPost = vartrue($_SESSION['forumupdate']['attachment_last'], 0);

/*
	$qry = "
	SELECT post_id, post_thread, post_entry, post_user FROM `#forum_post`
	WHERE post_entry REGEXP '_[[:digit:]]'
	AND post_id > {$lastPost} ORDER BY post_id LIMIT 50
	";
*/

	$qry = "
	SELECT post_id, post_thread, post_entry, post_user FROM `#forum_post`
	WHERE post_id > {$lastPost} AND post_entry LIKE '%public/%'
	 ORDER BY post_id LIMIT 50
	";

	// file_put_contents(e_LOG."forum_update_step10.log",$qry."\n",FILE_APPEND);
	
	
	if ($sql->gen($qry))
	{
		while ($row = $sql->fetch())
		{
			$postList[] = $row;
		}
		$i = 0;
		$pcount = 0;
		$f -> log("Found " . count($postList) . " posts with attachments");

		foreach ($postList as $post)
		{
			//			echo htmlentities($post['post_entry'])."<br />";
			$_SESSION['forumupdate']['attachment_last'] = $post['post_id'];
			$_SESSION['forumupdate']['attachment_count']++;

			$i++;
			//			if($pcount++ > 10) { die('here 10'); }
			$attachments = array();
			$foundFiles = array();

			//			echo $post['post_entry']."<br /><br />";

			//[link={e_FILE}public/1230091080_1_FT0_julia.jpg][img:width=60&height=45]{e_FILE}public/1230091080_1_FT0_julia_.jpg[/img][/link][br]
			//Check for images with thumbnails linking to full size
			
		//	if (preg_match_all('#\[link=(.*?)\]\[img.*?\]({e_FILE}.*?)\[/img\]\[/link\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))	
			
			if (preg_match_all('#\[link=([^\]]*?)\]\s*?\[img.*?\](({e_FILE}|e107_files|\.\./\.\./e107_files)[^\]]*)\[/img\]\s*?\[/link\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					$att = array();
					$att['thread_id'] = $post['post_thread'];
					$att['type'] = 'img';
					$att['html'] = $match[0];
					$att['name'] = str_replace (array("\r\n", "\n", "\r"), '', $match[1]);
					$att['thumb'] = $match[2];
					$attachments[] = $att;
					$foundFiles[] = $match[1];
					$foundFiles[] = $match[2];
					logAttachment($att['thread_id'],'link', $att['name']);
				}
			}
			
			if (preg_match_all('#\[lightbox=([^\]]*?)\]\s*?\[img.*?\](({e_FILE}|e107_files|\.\./\.\./e107_files)[^\]]*)\[/img\]\s*?\[/lightbox\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					$att = array();
					$att['thread_id'] = $post['post_thread'];
					$att['type'] = 'img';
					$att['html'] = $match[0];
					$att['name'] = str_replace (array("\r\n", "\n", "\r"), '', $match[1]);
					$att['thumb'] = $match[2];
					$attachments[] = $att;
					$foundFiles[] = $match[1];
					$foundFiles[] = $match[2];
					logAttachment($att['thread_id'],'lightbox', $att['name']);
				}
			}
			
			

/*
			if (preg_match_all('#\[link=(.*?)\]\[img.*?\](\.\./\.\./e107_files/public/.*?)\[/img\]\[/link\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					$att = array();
					$att['thread_id'] = $post['post_thread'];
					$att['type'] = 'img';
					$att['html'] = $match[0];
					$att['name'] = $match[1];
					$att['thumb'] = $match[2];
					$attachments[] = $att;
					$foundFiles[] = $match[1];
					$foundFiles[] = $match[2];
					logAttachment($att['thread_id'],'link2', $att['name']);
				}
			}
			*/
			//<div
			// class=&#039;spacer&#039;>[img:width=604&height=453]{e_FILE}public/1229562306_1_FT0_julia.jpg[/img]</div>
			//Check for attached full-size images
			
			;
			
		//	if (preg_match_all('#\[img.*?\]({e_FILE}.*?_FT\d+_.*?)\[/img\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			if (preg_match_all('#\[img[^\]]*?\]\s*?(({e_FILE}|e107_files|\.\./\.\./e107_files)[^\[]*)\s*?\[/img\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					//Ensure it hasn't already been handled above
					if (!in_array($match[1], $foundFiles))
					{
						$att = array();
						$att['thread_id'] = $post['post_thread'];
						$att['type'] = 'img';
						$att['html'] = $match[0];
						$att['name'] = str_replace (array("\r\n", "\n", "\r"), '', $match[1]);
						$att['thumb'] = '';
						$attachments[] = $att;
						logAttachment($att['thread_id'],'img', $att['name']);
					}
				}
			}

			/*
			if (preg_match_all('#\[img.*?\](\.\./\.\./e107_files/public/.*?_FT\d+_.*?)\[/img\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					//Ensure it hasn't already been handled above
					if (!in_array($match[1], $foundFiles))
					{
						$att = array();
						$att['thread_id'] = $post['post_thread'];
						$att['type'] = 'img';
						$att['html'] = $match[0];
						$att['name'] = $match[1];
						$att['thumb'] = '';
						$attachments[] = $att;
					}
				}
			}
			*/
			
			
			//[file={e_FILE}public/1230090820_1_FT0_julia.zip]julia.zip[/file]
			//Check for attached file (non-images)
			
			
			
		//	if (preg_match_all('#\[file=({e_FILE}.*?)\](.*?)\[/file\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			if (preg_match_all('#\[file=(({e_FILE}|e107_files|\.\./\.\./e107_files)[^\]]*)#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					$att = array();
					$att['thread_id'] = $post['post_thread'];
					$att['type'] = 'file';
					$att['html'] = $match[0];
					$att['name'] = $match[1];
					$att['thumb'] = '';
					$attachments[] = $att;
					
					logAttachment($att['thread_id'],'file', $att['name']);
				}
			}

			/*
			if (preg_match_all('#\[file=(\.\./\.\./e107_files/public/.*?)\](.*?)\[/file\]#ms', $post['post_entry'], $matches, PREG_SET_ORDER))
			{
				foreach ($matches as $match)
				{
					$att = array();
					$att['thread_id'] = $post['post_thread'];
					$att['type'] = 'file';
					$att['html'] = $match[0];
					$att['name'] = $match[1];
					$att['thumb'] = '';
					$attachments[] = $att;
				}
			}
			*/
			
			

			if (count($attachments))
			{
				$f->log("found " . count($attachments) . " attachments");
				$newValues = array();
				$info = array();
				$info['post_entry'] = $post['post_entry'];
				
				foreach ($attachments as $attachment)
				{
					$error = '';
					$f->log($attachment['name']);
					if ($f->moveAttachment($attachment, $post, $error))
					{
						$type = $attachment['type'];
						$newValues[$type][] = basename($attachment['name']);
						$info['post_entry'] = str_replace($attachment['html'], '', $info['post_entry']);
					}
					else
					{
						$errorText .= "Failure processing post {$post['post_id']} - file {$attachment['name']} - {$error}<br />";
						$f -> log("Failure processing post {$post['post_id']} - file {$attachment['name']} - {$error}");
					}
				}
				//				echo $errorText."<br />";

				// Did we make any changes at all?
				if (count($newValues))
				{
					$info['WHERE'] = 'post_id = ' . $post['post_id'];
					$info['post_attachments'] = e107::serialize($newValues);
				//	$sql->update('forum_post', $info); // XXX FIXME TODO screwed up due to _FIELD_DEFS 
					
					$sql->update('forum_post',"post_entry = \"".$info['post_entry']."\", post_attachments=\"".$info['post_attachments']."\" WHERE post_id = ".$post['post_id']."");
				}
				
			}

		
		}
		
			$totalOutput = round(($_SESSION['forumupdate']['attachment_count'] / $_SESSION['forumupdate']['attachment_total']) * 100, 1);
			echo $totalOutput;
			
			
		
			/*
			$debugRound = "
			forumupdate_attachment_count = ".$_SESSION['forumupdate']['attachment_count']."
			forumupdate_attachment_total = ".$_SESSION['forumupdate']['attachment_total']."
			calculated = ".$totalOutput."
			
			";
			
			file_put_contents(e_LOG."forum_update_step10.log",$debugRound,FILE_APPEND);
			*/
	}
	else
	{
		echo 100;
	}

}


function logAttachment($thread, $type, $attach)
{
	$tab = ($type == 'img') ? "\t\t\t" : "\t\t";	
	
	$text = $thread."\t\t".$type.$tab.$attach."\n";
	file_put_contents(e_LOG."forum_upgrade_attachments.log",$text, FILE_APPEND);			
}
		
	

function step11()
{
	$ns = e107::getRender();
	$stepCaption = 'Step 11: Delete old attachments';
	if (!isset($_POST['delete_orphans']))
	{
		$text = "
		The previous versions of the forum had difficulty deleting attachment files when posts or threads were deleted.
		<br />
		As a result of this, there is a potential for numerous files to exist that do not point to anything. In this step
		we will try to identify these files and delete them.
		<br /><br />
		<form method='post'>
		<input class='btn btn-success' data-loading-text='Please wait...' type='submit' name='delete_orphans' value='Proceed with attachment deletion' />
		</form>
		<form method='post' action='" . e_SELF . "?step=12'>
			<input class='btn btn-primary' type='submit' name='nextStep[12]' value='Skip this step' />
		</form>
		";
		e107::getRender() -> tablerender($stepCaption, $text);
		return;
	}

	global $forum;
	require_once (e_HANDLER . 'file_class.php');
	$f = new e_file;

	$flist = $f -> get_files(e_FILE . 'public', '_\d+_FT\d+_');
	$numFiles = count($flist);

	if ($numFiles)
	{
		if ($_POST['delete_orphans'] == 'Delete files')
		{
			//Do the deletion
			$success = 0;
			$failText = '';
			foreach ($flist as $file)
			{
				$fileName = e_FILE . 'public/' . $file['fname'];
				$r = unlink($fileName);
				if ($r)
				{
					$success++;
				}
				else
				{
					$failText .= "Deletion failed: {$file['fname']}<br />";
				}
			}
			if ($failText)
			{
				$failText = "<br /><br />The following failures occured: <br />" . $failText;
			}
			$text .= "
				Successfully removed {$success} orphaned files <br />
				{$failText}
				<br /><br />
				<form method='post' action='" . e_SELF . "?step=12'>
				<input class='btn' type='submit' name='nextStep[12]' value='Proceed to step 12' />
				</form>
			";
			$ns -> tablerender($stepCaption, $text);
			return;
		}
		$text = "There were {$numFiles} orphaned files found<br /><br />";
		if ($_POST['delete_orphans'] == 'Show files' || $numFiles < 31)
		{
			$i = 1;
			foreach ($flist as $file)
			{
				$text .= $i++ . ') ' . $file['fname'] . '<br />';
			}
			$extra = '';
		}
		else
		{
			$extra = "<input class='btn' type='submit' name='delete_orphans' value='Show files' />&nbsp; &nbsp; &nbsp; &nbsp;";
		}
		$text .= "
			<br /><br />
			<form method='post'>
			{$extra}
			<input class='btn' type='submit' name='delete_orphans' value='Delete files' />
			</form>
			
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}
	else
	{
		$text .= "
			There were no orphaned files found <br />
			<br /><br />
			<form method='post' action='" . e_SELF . "?step=12'>
			<input class='btn' type='submit' name='nextStep[12]' value='Proceed to step 12' />
			</form>
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}
}

function step12()
{
	$sql = e107::getDb();
	$ns = e107::getRender();
	$mes = e107::getMessage();

	$f = new forumUpgrade;

	$stepCaption = 'Step 12: Delete old forum data';

	if (!isset($_POST['delete_old']) && !isset($_POST['skip_delete_old']))
	{
		$text = "
		The forum upgrade should now be complete.<br />  During the upgrade process the old forum tables were
		retained. You may choose to keep these tables as a backup or delete them. <br /><br />
		We will also be marking the forum upgrade as completed!
		<br /><br />
		<form method='post'>
			<input class='btn btn-danger' data-loading-text='Please wait...' type='submit' name='delete_old' value='Remove old forum tables' />
			<input class='btn btn-primary' type='submit' name='skip_delete_old' value='Keep old forum tables' />
		</form>
		";
		$ns -> tablerender($stepCaption, $text);
		return;
	}

	if (vartrue($_POST['delete_old']))
	{
		$qryArray = array(
			"DROP TABLE `#forum_old`",
			"DROP TABLE `#forum_t`",
		);

		foreach ($qryArray as $qry)
		{
			$sql -> gen($qry);
		}
	}
	
	unset($_SESSION['forumUpgrade']);
	$ret = $f -> setNewVersion();

	$mes -> addSuccess("Congratulations, the forum upgrade is now completed!<br /><br />{$ret}");
	
	$text = "<a class='btn btn-primary' href='".e_ADMIN."e107_update.php'>Return to e107 Update</a>";
	
	$ns -> tablerender($stepCaption, $mes -> render() . $text);
	return;
}

class forumUpgrade
{
	private $newVersion = '2.0';
	var $error = array();
	public $updateInfo;
	private $attachmentData;
	private $logf;

	public function __construct()
	{
		$this -> updateInfo['lastThread'] = 0;
		$this -> attachmentData = array();
		$this -> logf = e_LOG . 'forum_upgrade.log';
		$this -> getUpdateInfo();
	}

	public function log($msg, $append = true)
	{
		//		echo "logf = ".$this->logf."<br />";
		$txt = sprintf("%s - %s\n", date('m/d/Y H:i:s'), $msg);
		//		echo $txt."<br />";
		$flag = ($append ? FILE_APPEND : '');
		file_put_contents($this -> logf, $txt, $flag);
	}

	public function checkUpdateNeeded()
	{
		return true;
		//	include_once(e_PLUGIN.'forum/forum_update_check.php');
		//	$needed = update_forum_08('check');
		//	return !$needed;
	}

	function checkAttachmentDirs()
	{
		$dirs = array(
			e_MEDIA . 'plugins/',
			e_MEDIA . 'plugins/forum/',
			e_MEDIA . 'plugins/forum/attachments/',
			e_MEDIA . 'plugins/forum/attachments/thumb'
		);

		foreach ($dirs as $dir)
		{
			if (!file_exists($dir))
			{
				if (!mkdir($dir, 0755, true))
				{
					$this -> error['attach'][] = "Directory '{$dir}' does not exist and I was unable to create it";
				}
			}
			else
			{
				if (!is_writable($dir))
				{
					$this -> error['attach'][] = "Directory '{$dir}' exits, but is not writeable";
				}
			}
		}
	}

	function getUpdateInfo()
	{
		$sql = e107::getDb();

		if ($_SESSION['forumUpgrade'])
		{
			$this -> updateInfo = $_SESSION['forumUpgrade'];
		}
		else
		{
			$this -> updateInfo = array();
		}

		return;
		
		/*
		if ($sql -> select('generic', '*', "gen_type = 'forumUpgrade'"))
		{
			$row = $sql -> fetch();
			$this -> updateInfo = unserialize($row['gen_chardata']);
		}
		else
		{
			$qry = "INSERT INTO `#generic` (gen_type) VALUES ('forumUpgrade')";
			$sql -> gen($qry);
			$this -> updateInfo = array();
		}
		 * */
	}

	function setUpdateInfo()
	{
		$_SESSION['forumUpgrade'] = $this -> updateInfo;
		return;
	}

	function setNewVersion()
	{
		// $sql = e107::getDb();

	//	$sql -> update('plugin', "plugin_version = '{$this->newVersion}' WHERE plugin_name='Forum' OR plugin_name = 'LAN_PLUGIN_FORUM_NAME'");
	//	e107::getConfig()->setPref('plug_installed/forum', $this->newVersion)->save(false,true,false);

		e107::getPlugin()->refresh('forum');

		return "Forum Version updated to version: {$this->newVersion} <br />";
	}

	function migrateThread($threadId)
	{
		global $forum;

		$threadId = (int)$threadId;
		if (e107::getDb()->select('forum_t', '*', "thread_parent = {$threadId} OR thread_id = {$threadId}", 'default'))
		{
			$threadData = e107::getDb()->db_getList();
			foreach ($threadData as $post)
			{
				if ($post['thread_parent'] == 0)
				{
					$result = $this -> addThread($post);
					if ($result)
					{
						$result = $this -> addPost($post);
					}
				}
				else
				{
					$result = $this -> addPost($post);
				}
			}
			return ($result ? count($threadData) : false);
		}
		return false;
	}

	function addThread(&$post)
	{
		global $forum;

		/*
		 * v1.x
		 * thread_id
		 * thread_name
		 * thread_thread
		 * thread_forum_id
		 * thread_datestamp
		 * thread_parent
		 * thread_user
		 * thread_views
		 * thread_active
		 * thread_lastpost
		 * thread_s
		 * thread_edit_datestamp
		 * thread_lastuser
		 * thread_total_replies
		 */

		/*
		 * v2.x
		 * thread_id
		 * thread_name
		 * thread_forum_id
		 * thread_views
		 * thread_active
		 * thread_lastpost
		 * thread_sticky
		 * thread_datestamp
		 * thread_user
		 * thread_user_anon
		 * thread_lastuser
		 * thread_lastuser_anon
		 * thread_total_replies
		 * thread_options
		 * thread_sef
		 */

		$detected 	= mb_detect_encoding($post['thread_name']); // 'ISO-8859-1'
		$threadName = iconv($detected,'UTF-8', $post['thread_name']); 
		 
		$thread = array();
		$thread['thread_id'] = $post['thread_id'];
		$thread['thread_name'] = $threadName;
		$thread['thread_forum_id'] = $post['thread_forum_id'];
		$thread['thread_datestamp'] = $post['thread_datestamp'];
		$thread['thread_lastpost'] = $post['thread_lastpost'];
		$thread['thread_views'] = $post['thread_views'];
		$thread['thread_active'] = $post['thread_active'];
		$thread['thread_sticky'] = $post['thread_s'];
		$thread['thread_lastuser']		= $this->getLastUser($post['thread_lastuser']);
		$thread['thread_total_replies'] = $post['thread_total_replies'];

		$userInfo = $this -> getUserInfo($post['thread_user']);
		$thread['thread_user'] = $userInfo['user_id'];
		$thread['thread_user_anon'] = $userInfo['anon_name'];

		//  If thread marked as 'tracked by starter', we must convert to using
		// forum_track table
		if ($thread['thread_active'] == 99 && $thread['thread_user'] > 0)
		{
			$forum -> track('add', $thread['thread_user'], $thread['thread_id'], true);
			$thread['thread_active'] = 1;
		}

		//		$thread['_FIELD_TYPES'] = $forum->fieldTypes['forum_thread'];
		//		$thread['_FIELD_TYPES']['thread_name'] = 'escape'; //use escape to prevent
		// double entities


		$result = e107::getDb() -> insert('forum_thread', $thread);
		return $result;
	}


	private function getLastUser($string)
	{

		if(empty($string))
		{
			return 0;
		}

		list($num,$name) = explode(".",$string,2);

		return intval($num);

	}



	function addPost(&$post)
	{
		global $forum;
		
		$detected 						= mb_detect_encoding($post['thread_thread']); // 'ISO-8859-1'
		$postEntry 						= iconv($detected,'UTF-8', $post['thread_thread']);

		$newPost = array();
		$newPost['post_id'] 			= $post['thread_id'];
		$newPost['post_thread'] 		= ($post['thread_parent'] == 0 ? $post['thread_id'] : $post['thread_parent']);
		$newPost['post_entry'] 			= $postEntry;
		$newPost['post_forum'] 			= $post['thread_forum_id'];
		$newPost['post_datestamp'] 		= $post['thread_datestamp'];
		$newPost['post_edit_datestamp'] = ($post['thread_edit_datestamp'] ? $post['thread_edit_datestamp'] : '_NULL_');

		$userInfo = $this -> getUserInfo($post['thread_user']);

		$newPost['post_user'] 			= $userInfo['user_id'];
		$newPost['post_user_anon'] 		= $userInfo['anon_name'];
		$newPost['post_ip'] 			= $userInfo['user_ip'];

		//		$newPost['_FIELD_TYPES'] = $forum->fieldTypes['forum_post'];
		//		$newPost['_FIELD_TYPES']['post_entry'] = 'escape'; //use escape to prevent
		// double entities
		//		print_a($newPost);
		//		exit;
		$result = e107::getDb() -> insert('forum_post', $newPost);
		//		exit;
		return $result;

	}

	function getUserInfo($info)
	{
	    $tmp     = explode('.', $info);
	    $id     = (int)$tmp[0];

	    // Set default values
	    $ret = array(
	        'user_id'   => 0,
	        'user_ip'   => '_NULL_',
	        'anon_name' => '_NULL_'
	    );

	    // Check if post is done anonymously (ID = 0, and there should be a chr(1) value between the username and IP address)
	    if(strpos($info, chr(1)) !== false && $id == 0)
	    {
	        $anon = explode(chr(1), $info);
	        $anon_name = explode('.', $anon[0]);

	        $ret['anon_name'] = $anon_name[1];
	        $ret['user_ip']	  = e107::getIPHandler()->ipEncode($anon[1]);
	    }
	    // User id is known - NOT anonymous
	    {
	        $ret['user_id'] = $id;
	    }

	    return $ret;
	}

	function moveAttachment($attachment, $post, &$error)
	{
		global $forum;
		set_time_limit(30);
		$tp = e107::getParser();

		$post_id = $post['post_id'];
		$newPath = $forum->getAttachmentPath($post['post_user']);

		if (!is_dir($newPath))
		{
			mkdir($newPath, 0755);
		}

		$attachment['name'] = str_replace(array(
			' ',
			"\n",
			"\r"
		), '', $attachment['name']);
		$old = str_replace('{e_FILE}', e_FILE, $attachment['name']);
		$fileInfo = pathinfo($attachment['name']);
		$new = $newPath . "/" . $fileInfo['basename'];
		$hash = md5($new);

		if (!file_exists($old))
		{
			if (isset($this -> attachmentData[$hash]))
			{
				$error = "Post {$post_id} - Attachment already migrated with post: " . $this -> attachmentData[$hash];
			}
			else
			{
				$error = 'Original attachment not found (orphaned?)';
			}
			return false;
		}

		if (!file_exists($new))
		{
			$this -> log("Copying [{$old}] -> [{$new}]");
			$r = rename($old, $new);
			$this -> attachmentData[$hash] = $post_id;
			//			$r = true;
		}
		else
		{
			//File already exists, show some sort of error
			if (isset($this -> attachmentData[$hash]))
			{
				$error = "Post {$post_id} - Attachment already migrated with post: " . $this -> attachmentData[$hash];
			}
			else
			{
				$error = 'Attachment file already exists';
			}
			return false;
		}

		if (!$r)
		{
			//File copy failed!
			$error = 'Moving of attachments failed';
			return false;
		}

		$oldThumb = '';
		if ($attachment['thumb'])
		{
			$tmp = explode('/', $attachment['thumb']);
			$fileInfo = pathinfo($attachment['thumb']);

			$oldThumb = str_replace('{e_FILE}', e_FILE, $attachment['thumb']);
			//			$newThumb 	= e_PLUGIN.'forum/attachments/thumb/'.$tmp[1];
			$newThumb = e_MEDIA . 'files/plugins/forum/attachments/thumb/' . $fileInfo['basename'];
			$hash = md5($newThumb);

			if (!file_exists($newThumb))
			{
				$r = rename($oldThumb, $newThumb);
				//				$r = true;
			}
			else
			{
				//File already exists, show some sort of error
				if (isset($this -> attachmentData[$hash]))
				{
					$error = "Post {$post_id} - Thumb already migrated with post: " . $this -> attachmentData[$hash];
				}
				else
				{
					$error = 'Thumb file already exists';
				}
				return false;
			}
			if (!$r)
			{
				//File copy failed
				$error = 'Moving of thumb failed';
				return false;
			}
		}

		//Copy was successful, let's delete the original files now.
		//		$r = true;
		//	$r = unlink($old);
		if (!$r)
		{
			//		$error = 'Was unable to delete old attachment: '.$old;
			//		return false;
		}
		if ($oldThumb)
		{
			//			$r = true;
			//		$r = unlink($oldThumb);
			if (!$r)
			{
				//			$error = 'Was unable to delete old thumb: '.$oldThumb;
				//			return false;
			}
		}
		return true;
	}

}

function createThreadLimitDropdown($count)
{
	$ret = "
	<select class='tbox' name='threadLimit'>
	";
	$last = min($count, 10000);
	if ($count < 2000)
	{
		$ret .= "<option value='{$count}'>{$count}</option>";
	}
	else
	{
		for ($i = 2000; $i < $count; $i += 2000)
		{
			$ret .= "<option value='{$i}'>{$i}</option>";
		}
		if ($count < 10000)
		{
			$ret .= "<option value='{$count}'>{$count}</option>";
		}
	}
	$ret .= '</select>';
	return $ret;
}

function forum_update_adminmenu()
{
	$action = 1;

	$var[1]['text'] = '1 - Permissions';
	$var[1]['link'] = e_SELF . "?step=1";

	$var[2]['text'] = '2 - Create new tables';
	$var[2]['link'] = '#';

	$var[3]['text'] = '3 - Create extended fields';
	$var[3]['link'] = '#';

	$var[4]['text'] = '4 - Move user data';
	$var[4]['link'] = '#';

	$var[5]['text'] = '5 - Migrate forum config';
	$var[5]['link'] = '#';

	$var[6]['text'] = '6 - Migrate threads/replies';
	$var[6]['link'] = '#';

	$var[7]['text'] = '7 - Recalc all counts';
	$var[7]['link'] = '#';

	$var[8]['text'] = '8 - Calc lastpost data';
	$var[8]['link'] = '#';

	$var[9]['text'] = '9 - Migrate any poll data';
	$var[9]['link'] = '#';

	$var[10]['text'] = '10 - Migrate any attachments';
	$var[10]['link'] = '#';

	$var[11]['text'] = '11 - Delete old attachments';
	$var[11]['link'] = '#';

	$var[12]['text'] = '12 - Delete old forum data';
	$var[12]['link'] = '#';
	
	if(E107_DEBUG_LEVEL)
	{
		$var[13]['divider'] = true;
		
		$var[14]['text'] = 'Reset';
		$var[14]['link'] = e_SELF . "?reset";

		$var[15]['text'] = 'Reset to 3';
		$var[15]['link'] = e_SELF . "?step=3&reset=3";

		$var[16]['text'] = 'Reset to 6';
		$var[16]['link'] = e_SELF . "?step=6&reset=6";

		$var[17]['text'] = 'Reset to 7';
		$var[17]['link'] = e_SELF . "?step=7&reset=7";
		
		$var[18]['text'] = 'Reset to 10';
		$var[18]['link'] = e_SELF . "?step=10&reset=10";
		
	}
	
	

	if (isset($_GET['step']))
	{
		//	$action = key($_POST['nextStep']);
		$action = intval($_GET['step']);
	}

	show_admin_menu('Forum Upgrade', $action, $var);
}
?>