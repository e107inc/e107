<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2019 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum main page
 *
*/
if(!defined('e107_INIT'))
{
	require_once('../../class2.php');
}
$e107 = e107::getInstance();
$tp = e107::getParser();
$sql = e107::getDb();

if (!$e107->isInstalled('forum'))
{
	e107::redirect();
	exit;
}

e107::lan('forum', "front", true);

if(!deftrue('BOOTSTRAP'))
{
	$bcDefs = array(
		'FORLAN_11' => 'LAN_FORUM_0039',
		'FORLAN_12' => 'LAN_FORUM_0040',
		'FORLAN_18' => 'LAN_FORUM_0041',
	);

	e107::getLanguage()->bcDefs($bcDefs);
}


require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

if(e_AJAX_REQUEST)
{
	if(varset($_POST['action']) == 'track')
	{
		$forum->ajaxTrack();
	}

}

/*
if ($untrackId = varset($_REQUEST['untrack']))
{
	$forum->track('del', USERID, $untrackId);
	header('location:'.$e107->url->create('forum/thread/track', array(), 'full=1&encode=0'));
	exit;
}
*/

if(isset($_GET['f']))
{
	if(isset($_GET['id']))
	{
		$id = (int)$_GET['id'];
	}

	switch($_GET['f'])
	{
		case 'mfar':
			$forum->forumMarkAsRead($id);
			header('location:'.e_SELF);
			exit;
			break;

		case 'rules':
		include_once(HEADERF);

		forum_rules('show');
			include_once(FOOTERF);
			exit;
			break;

		case 'track':
			include_once(HEADERF);

			forum_track();
			include_once(FOOTERF);
			exit;
			break;
	}
}



/** @var forum_shortcodes $sc */
$sc = e107::getScBatch('forum', true);


if(empty($FORUM_TEMPLATE))
{
	// Override with theme template
	if(THEME_LEGACY !== true) //v2.x
	{
		$FORUM_TEMPLATE = e107::getTemplate('forum','forum'); // required to use v2.x wrapper shortcode wrappers.
	}
	elseif (file_exists(THEME.'forum_template.php')) //v1.x fallback.
	{
		include(e_PLUGIN.'forum/templates/forum_template.php');
		include_once(THEME.'forum_template.php');
	}
	elseif(file_exists(THEME.'templates/forum/forum_template.php'))
	{
	//	$FORUM_TEMPLATE = e107::getTemplate('forum','forum');
		require_once(THEME.'templates/forum/forum_template.php');
	}
	else
	{
		require_once(e_PLUGIN.'forum/templates/forum_template.php');
	}
}

if(is_array($FORUM_TEMPLATE) && THEME_LEGACY !== true) // new v2.x format.
{

	if(varset($FORUM_TEMPLATE['main-start'])) // correction of previous v2.x setup.
	{
		$FORUM_TEMPLATE['main']['start']    = $FORUM_TEMPLATE['main-start'];
		$FORUM_TEMPLATE['main']['parent']   = $FORUM_TEMPLATE['main-parent'];
		$FORUM_TEMPLATE['main']['forum']    = $FORUM_TEMPLATE['main-forum'];
		$FORUM_TEMPLATE['main']['end']      = $FORUM_TEMPLATE['main-end'];
	}

	$FORUM_MAIN_START		= $FORUM_TEMPLATE['main']['start'];
	$FORUM_MAIN_PARENT 		= $FORUM_TEMPLATE['main']['parent'];
	$FORUM_MAIN_PARENT_END 	= varset($FORUM_TEMPLATE['main']['parent_end']);
	$FORUM_MAIN_FORUM		= $FORUM_TEMPLATE['main']['forum'];
	$FORUM_MAIN_END			= $FORUM_TEMPLATE['main']['end'];

	$FORUM_NEWPOSTS_START	= $FORUM_TEMPLATE['main']['start']; // $FORUM_TEMPLATE['new-start'];
	$FORUM_NEWPOSTS_MAIN 	= $FORUM_TEMPLATE['main']['forum']; // $FORUM_TEMPLATE['new-main'];
	$FORUM_NEWPOSTS_END 	= $FORUM_TEMPLATE['main']['end']; // $FORUM_TEMPLATE['new-end'];
}

require_once(HEADERF);

$forumList = $forum->forumGetForumList();
$newflag_list = $forum->forumGetUnreadForums();

$sc->newFlagList = $newflag_list;


if (!$forumList)
{
	$ns->tablerender(LAN_PLUGIN_FORUM_NAME, LAN_FORUM_0067, 'forum-empty');
	require_once(FOOTERF);
	exit;
}

$forum_string = '';

foreach ($forumList['parents'] as $parent)
{

	$parent = (array) $parent;

	$sc->setVars($parent);
//	$sc->setScVar('forumParent', $parent);
	$sc->wrapper('forum/main/parent');

	$forum_string .= $tp->parseTemplate($FORUM_MAIN_PARENT, true, $sc);

	$fid = $parent['forum_id'];
	if (empty($forumList['forums'][$parent['forum_id']]))
	{
		$text .= "<td colspan='5' style='text-align:center' class='forumheader3'>".LAN_FORUM_0068."</td>";
	}
	else
	{
			//TODO: Rework the restricted string
		foreach($forumList['forums'][$parent['forum_id']] as $f)
		{
			if ($f['forum_class'] == e_UC_ADMIN && ADMIN)
			{
				$forum_string .= parse_forum($f, LAN_FORUM_0005);
			}
			elseif($f['forum_class'] == e_UC_MEMBER && USER)
			{
				$forum_string .= parse_forum($f, LAN_FORUM_0006);
			}
			elseif($f['forum_class'] == e_UC_READONLY)
			{
				$forum_string .= parse_forum($f, LAN_FORUM_0007);
			}
			elseif($f['forum_class'] && check_class($f['forum_class']))
			{
				$forum_string .= parse_forum($f, LAN_FORUM_0008);
			}
			elseif(!$f['forum_class'])
			{
				$forum_string .= parse_forum($f);
			}
		}
		if (isset($FORUM_MAIN_PARENT_END))
		{
//--			$forum_string .= $tp->simpleParse($FORUM_MAIN_PARENT_END, $pVars);
    	$forum_string .= $tp->parseTemplate($FORUM_MAIN_PARENT_END, true, $sc);
		}
	}
}


function parse_forum($f, $restricted_string = '')
{

	global $FORUM_MAIN_FORUM, $forumList, $sc;

	$tp = e107::getParser();

	if(!empty($forumList['subs']) && is_array($forumList['subs'][$f['forum_id']]))
	{

		$lastpost_datestamp = reset(explode('.', $f['forum_lastpost_info']));
		$ret = parse_subs($forumList, $f['forum_id'], $lastpost_datestamp);

		$f['forum_threads'] += $ret['threads'];
		$f['forum_replies'] += $ret['replies'];
		if(isset($ret['lastpost_info']))
		{
			$f['forum_lastpost_info'] = $ret['lastpost_info'];
			$f['forum_lastpost_user'] = $ret['lastpost_user'];
			$f['forum_lastpost_user_anon'] = $ret['lastpost_user_anon'];
			$f['user_name'] = $ret['user_name'];
		}

		$f['text'] = $ret['text'];
	}

	$sc->setVars($f);

	$sc->wrapper('forum/main/forum');

	return $tp->parseTemplate($FORUM_MAIN_FORUM, true, $sc);
}



function parse_subs($forumList, $id ='', $lastpost_datestamp)
{

	$tp = e107::getParser();
	$ret = array();

	$subList = $forumList['subs'][$id];

	$ret['text'] = '';
	$ret['threads'] = 0;
	$ret['replies'] = 0;

	foreach($subList as $sub)
	{
	//	print_a($sub);

		$ret['text'] .= ($ret['text'] ? ', ' : '');

		$urlData                = $sub;
		$urlData['parent_sef']  = $forumList['all'][$sub['forum_sub']]['forum_sef']; //   = array('parent_sef'=>
		$suburl                 = e107::url('forum','forum', $urlData);

		$ret['text']            .= "<a href='{$suburl}'>".$tp->toHTML($sub['forum_name']).'</a>';
		$ret['threads']         += $sub['forum_threads'];
		$ret['replies']         += $sub['forum_replies'];
		$tmp                    = explode('.', $sub['forum_lastpost_info']);

		if($tmp[0] > $lastpost_datestamp)
		{
			$ret['lastpost_info'] = $sub['forum_lastpost_info'];
			$ret['lastpost_user'] = $sub['forum_lastpost_user'];
			$ret['lastpost_user_anon'] = $sub['forum_lastpost_user_anon'];
			$ret['user_name'] = $sub['user_name'];
			$lastpost_datestamp = $tmp[0];
		}
	}


	return $ret;
}

if (e_QUERY == 'track')
{

}

if (e_QUERY == 'new')
{
//--	$nVars = new e_vars;
	$newThreadList = $forum->threadGetNew(10);
	foreach($newThreadList as $thread)
	{
		$sc->setVars($thread);
		$forum_newstring .= $tp->parseTemplate($FORUM_NEWPOSTS_MAIN, true, $sc);
	}

	if (empty($newThreadList))
	{
		$forum_newstring = $tp->parseTemplate($FORUM_NEWPOSTS_MAIN, true, $sc);

	}

	$forum_new_start = $tp->parseTemplate($FORUM_NEWPOSTS_START, true, $sc);
	$forum_new_end = $tp->parseTemplate($FORUM_NEWPOSTS_END, true, $sc);

	if ($forum->prefs->get('enclose'))
	{
		$ns->tablerender($forum->prefs->get('title'), $forum_new_start.$forum_newstring.$forum_new_end, 'forum');
	}
	else
	{
		echo $forum_new_start.$forum_newstring.$forum_new_end;
	}
}



$breadarray = array(
					array('text'=> $forum->prefs->get('title'), 'url' => e107::url('forum','index') )
);

e107::breadcrumb($breadarray);

$sc->wrapper('forum/main/start');
$forum_main_start = $tp->parseTemplate($FORUM_MAIN_START, true, $sc);
//--  $forum_main_end = $tp->simpleParse($FORUM_MAIN_END, $fVars);

$sc->wrapper('forum/main/end');
$forum_main_end = $tp->parseTemplate($FORUM_MAIN_END, true, $sc);

if ($forum->prefs->get('enclose'))
{
	$ns->tablerender($forum->prefs->get('title'), $forum_main_start.$forum_string.$forum_main_end, 'forum');
}
else
{
	echo $forum_main_start.$forum_string.$forum_main_end;
}


require_once(FOOTERF);

function forum_rules($action = 'check')
{
	if (ADMIN == true)
	{
		$type = 'forum_rules_admin';
	}
	elseif(USER == true)
	{
		$type = 'forum_rules_member';
	}
	else
	{
		$type = 'forum_rules_guest';
	}
	$result = e107::getDb()->select('generic', 'gen_chardata', "gen_type = '$type' AND gen_intdata = 1");
	if ($action == 'check') { return $result; }

	if ($result)
	{
		$row = e107::getDb()->fetch();
		$rules_text = e107::getParser()->toHTML($row['gen_chardata'], true);
	}
	else
	{
		$rules_text = LAN_FORUM_0072;
	}

	$text = '';

	if(deftrue('BOOTSTRAP'))
	{
		$breadarray = array(
			array('text'=> e107::pref('forum','title', LAN_PLUGIN_FORUM_NAME), 'url' => e107::url('forum','index') ),
			array('text'=>LAN_FORUM_0016, 'url'=>null)
		);

		$text = e107::getForm()->breadcrumb($breadarray);
		e107::breadcrumb($breadarray); // assign to {---BREADCRUMB---}
	}

	$text .= "<div id='forum-rules'>".$rules_text."</div>";
	$text .=  "<div class='center'>".e107::getForm()->pagination(e107::url('forum','index'), LAN_BACK)."</div>";

	e107::getRender()->tablerender(LAN_FORUM_0016, $text, 'forum-rules');
}


function forum_track()
{
	global $forum;

	$trackPref = $forum->prefs->get('track');
	$trackEmailPref = $forum->prefs->get('trackemail',true);


	if(empty($trackPref))
	{
		echo "Disabled";
		return false;
	}


	$FORUM_TEMPLATE = null;

	include(e_PLUGIN.'forum/templates/forum_template.php');

	// Override with theme template
	if (file_exists(THEME.'forum_template.php'))
	{
		include(THEME.'forum_template.php');
	}
	elseif(file_exists(THEME.'templates/forum/forum_template.php'))
	{
		require(THEME.'templates/forum/forum_template.php');
	}

	$IMAGE_nonew_small = IMAGE_nonew_small;
	$IMAGE_new_small = IMAGE_new_small;

	if(is_array($FORUM_TEMPLATE) && deftrue('BOOTSTRAP',false)) // new v2.x format.
	{
		$FORUM_TRACK_START		= $FORUM_TEMPLATE['track']['start']; // $FORUM_TEMPLATE['track-start'];
		$FORUM_TRACK_MAIN		= $FORUM_TEMPLATE['track']['item']; // $FORUM_TEMPLATE['track-main'];
		$FORUM_TRACK_END		= $FORUM_TEMPLATE['track']['end']; // $FORUM_TEMPLATE['track-end'];

		$IMAGE_nonew_small = IMAGE_nonew;
		$IMAGE_new_small = IMAGE_new;

	}

	$sql = e107::getDb();
	$tp = e107::getParser();

	$trackDiz = ($trackEmailPref) ? LAN_FORUM_3040 : LAN_FORUM_3041;


	if($trackedThreadList = $forum->getTrackedThreadList(USERID, 'list'))
	{

		$viewed = $forum->threadGetUserViewed();

		$qry = "SELECT t.*,th.*, f.*,u.user_name FROM `#forum_track` AS t
		LEFT JOIN `#forum_thread` AS th ON t.track_thread = th.thread_id
		LEFT JOIN `#forum` AS f ON th.thread_forum_id = f.forum_id
		LEFT JOIN `#user` AS u ON th.thread_lastuser = u.user_id
		WHERE t.track_userid = ".USERID." ORDER BY th.thread_lastpost DESC";

		$forum_trackstring = '';
		$data = array();
		if($sql->gen($qry))
		{
			while($row = $sql->fetch())
			{
			//	e107::getDebug()->log($row);
				$row['thread_sef'] = eHelper::title2sef($row['thread_name'],'dashl');

				$data['NEWIMAGE'] = $IMAGE_nonew_small;
				
				if ($row['thread_datestamp'] > USERLV && !in_array($row['thread_id'], $viewed))
				{
					$data['NEWIMAGE'] = $IMAGE_new_small;
				}

				$data['LASTPOSTUSER'] = !empty($row['user_name']) ? "<a href='".e107::url('user/profile/view', array('name' => $row['user_name'], 'id' => $row['thread_lastuser']))."'>".$row['user_name']."</a>" : LAN_ANONYMOUS;
				$data['LASTPOSTDATE'] = $tp->toDate($row['thread_lastpost'],'relative');
				
				$buttonId = "forum-track-button-".intval($row['thread_id']);

				$forumUrl = e107::url('forum','forum',$row);
				$threadUrl = e107::url('forum','topic',$row, array('query'=>array('last'=>1))); // ('forum/thread/view', $row); // configs will be able to map thread_* vars to the url
				$data['TRACKPOSTNAME'] = "<a href='".$forumUrl."'>". $row['forum_name']."</a> / <a href='".$threadUrl."'>".$tp->toHTML($row['thread_name'], false, 'TITLE').'</a>';
			//	$data['UNTRACK'] = "<a class='btn btn-default' href='".e_SELF."?untrack.".$row['thread_id']."'>".LAN_FORUM_0070."</a>";


				$data['UNTRACK'] = "<a id='".$buttonId."' href='#' title=\"".$trackDiz."\" data-token='".e_TOKEN."' data-forum-insert='".$buttonId."'  data-forum-post='".$row['thread_forum_id']."' data-forum-thread='".$row['thread_id']."' data-forum-action='track' name='track' class='btn btn-primary' >".IMAGE_track."</a>";

				$data['_WRAPPER_'] = 'forum/track/item';
				$forum_trackstring .= $tp->parseTemplate($FORUM_TRACK_MAIN, true, $data);
			}
		}
	//	print_a($FORUM_TRACK_START);


		if(deftrue('BOOTSTRAP'))
		{
			$breadarray = array(
				array('text'=> e107::pref('forum','title', LAN_PLUGIN_FORUM_NAME), 'url' => e107::url('forum','index') ),
				array('text'=>LAN_FORUM_0030, 'url'=>null)
			);

			e107::breadcrumb($breadarray); // assign to {---BREADCRUMB---}

			$data['FORUM_BREADCRUMB'] = e107::getForm()->breadcrumb($breadarray);
		}

		$data['_WRAPPER_'] = 'forum/track/start';
		$forum_track_start = $tp->parseTemplate($FORUM_TRACK_START, true, $data);

		$data['_WRAPPER_'] = 'forum/track/end';
		$forum_track_end = $tp->parseTemplate($FORUM_TRACK_END, true, $data);


	//	if ($forum->prefs->get('enclose'))
		{
			// $ns->tablerender($forum->prefs->get('title'), $forum_track_start.$forum_trackstring.$forum_track_end, array('forum', 'main1'));
		}
	//	else
		{
			$tracktext =  $forum_track_start.$forum_trackstring.$forum_track_end;
		}
	}


	$text ='';



	$text .= $tracktext;
	$text .=  "<div class='center'>".e107::getForm()->pagination(e107::url('forum','index'), LAN_BACK)."</div>";


	e107::getRender()->tablerender(LAN_FORUM_0030, $text, 'forum-track');

	return null;
}


?>