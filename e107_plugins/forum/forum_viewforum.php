<?php
	/*
	* e107 website system
	*
	* Copyright (C) 2008-2013 e107 Inc (e107.org)
	* Released under the terms and conditions of the
	* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	*
	* View specific forums
	*
	*/

if(!defined('e107_INIT'))
{
	require_once(__DIR__ . '/../../class2.php');
}
$e107 = e107::getInstance();
if(!e107::isInstalled('forum'))
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
		'FORLAN_13' => 'LAN_FORUM_0040',
		'FORLAN_14' => 'LAN_FORUM_0040',
		'FORLAN_16' => 'LAN_FORUM_1012',
		'FORLAN_17' => 'LAN_FORUM_1013',
		'FORLAN_18' => 'LAN_FORUM_1014',
		'LAN_435'   => 'LAN_DELETE',
		'LAN_401'   => 'LAN_FORUM_4011',
		'LAN_398'   => 'LAN_FORUM_4012',
		'LAN_399'   => 'LAN_FORUM_4013',
		'LAN_400'   => 'LAN_FORUM_4014',
		'LAN_402'   => 'LAN_FORUM_5019',

	);

	e107::getLanguage()->bcDefs($bcDefs);
}

define('NAVIGATION_ACTIVE', 'forum');


class forum_viewforum_front
{

	function __construct()
	{

		$this->init();

	}

/**
 * @param $FORUM_VIEW_START
 * @param $urlparms
 * @param $doNothing
 * @return array
 */
function init()
{
	// todo - remove all these globals.
	global $FORUM_VIEW_START, $urlparms, $doNothing;
	global $sc, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE;
	global	$FORUM_VIEW_FORUM,
		$FORUM_VIEW_FORUM_STICKY,
		$FORUM_VIEW_FORUM_ANNOUNCE,
		$FORUM_VIEW_END,
		$FORUM_VIEW_END_CONTAINER,
		$FORUM_VIEW_SUB_START,
		$FORUM_VIEW_SUB,
		$FORUM_VIEW_SUB_END,
		$FORUM_IMPORTANT_ROW,
		$FORUM_NORMAL_ROW;

	global $forum_info, $FORUM_CRUMB, $forumInfo, $forumId;
	global $forum;

	$sql = e107::getDb();
	$tp = e107::getParser();
	$ns = e107::getRender();
	$pref = e107::getPref();


	if(!e_QUERY && empty($_GET))
	{
		if(E107_DEBUG_LEVEL > 0)
		{
			echo __FILE__ . ' Line: ' . __LINE__;
			return null;
		}


		$url = e107::url('forum', 'index', null, ['mode' => 'full']);
		e107::getRedirect()->go($url);
		//header('Location:'.e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
		return null;
	}

	if(!empty($_GET['sef']))
	{
		if($newID = $sql->retrieve('forum', 'forum_id', "forum_sef= '" . $tp->toDB($_GET['sef']) . "' LIMIT 1"))
		{
			$_REQUEST['id'] = $newID;
		}
	}

	require_once(e_PLUGIN . 'forum/forum_class.php');
	$forum = new e107forum;

//$view = 25;
	$view = $forum->prefs->get('threadspage', 25);
	if(!$view)
	{
		$view = 25;
	}
	$page = (varset($_GET['p']) ? $_GET['p'] : 1);
	$threadFrom = ($page - 1) * $view;



	$sc = e107::getScBatch('viewforum', 'forum');

	$forumId = (int) varset($_REQUEST['id']);

	if(!$forumId && e_QUERY) // BC Fix for old links.
	{
		list($id, $from) = explode(".", e_QUERY);
		$forumId = intval($id);
		$threadFrom = intval($from);
		unset($id, $from);
	}

	if(!$forum->checkPerm($forumId, 'view'))
	{
		$url = e107::url('forum', 'index', null, ['mode' => 'full']);
		/*
				if(E107_DEBUG_LEVEL > 0)
				{
					print_a($_REQUEST);
					print_a($_GET);
					echo __FILE__ . ' Line: ' . __LINE__;
					echo "   forumId: " . $forumId;
					exit;
				}
		*/

		e107::getRedirect()->go($url);

		return null;
	}

	$forumInfo = $forum->forumGet($forumId);
	e107::canonical('forum', 'forum', $forumInfo);

	$forumSCvars = array();
//----$threadsViewed = $forum->threadGetUserViewed();

	if(empty($FORUM_VIEW_START))
	{
		if(THEME_LEGACY !== true)
		{
			$FORUM_VIEWFORUM_TEMPLATE = e107::getTemplate('forum', 'forum_viewforum');
			$GLOBALS['FORUM_VIEWFORUM_TEMPLATE'] = $FORUM_VIEWFORUM_TEMPLATE;
		}
		else
		{
			if(file_exists(THEME . 'templates/forum/forum_viewforum_template.php'))
			{
				require_once(THEME . 'templates/forum/forum_viewforum_template.php');
			}
			elseif(file_exists(THEME . 'forum_viewforum_template.php')) //v1.x
			{
				require_once(THEME . 'forum_viewforum_template.php');
			}
			elseif(file_exists(THEME . 'forum_template.php'))  //v1.x
			{
				require_once(THEME . 'forum_template.php');
			}
			else
			{
				require_once(e_PLUGIN . 'forum/templates/forum_viewforum_template.php');
			}

		}


	}


	if(!empty($FORUM_VIEWFORUM_TEMPLATE) && is_array($FORUM_VIEWFORUM_TEMPLATE) && THEME_LEGACY !== true) // New v2.x bootstrap Template.
	{

		$FORUM_VIEW_CAPTION = $FORUM_VIEWFORUM_TEMPLATE['caption'];
		$FORUM_VIEW_START_CONTAINER = $FORUM_VIEWFORUM_TEMPLATE['start'];
		$FORUM_VIEW_START = $FORUM_VIEWFORUM_TEMPLATE['header'];
		$FORUM_VIEW_FORUM = $FORUM_VIEWFORUM_TEMPLATE['item'];
		$FORUM_VIEW_FORUM_STICKY = $FORUM_VIEWFORUM_TEMPLATE['item-sticky'];
		$FORUM_VIEW_FORUM_ANNOUNCE = $FORUM_VIEWFORUM_TEMPLATE['item-announce'];
		$FORUM_VIEW_END = $FORUM_VIEWFORUM_TEMPLATE['footer'];
		$FORUM_VIEW_END_CONTAINER = $FORUM_VIEWFORUM_TEMPLATE['end'];
		$FORUM_VIEW_SUB_START = $FORUM_VIEWFORUM_TEMPLATE['sub-header'];
		$FORUM_VIEW_SUB = $FORUM_VIEWFORUM_TEMPLATE['sub-item'];
		$FORUM_VIEW_SUB_END = $FORUM_VIEWFORUM_TEMPLATE['sub-footer'];
		$FORUM_IMPORTANT_ROW = $FORUM_VIEWFORUM_TEMPLATE['divider-important'];
		$FORUM_NORMAL_ROW = $FORUM_VIEWFORUM_TEMPLATE['divider-normal'];

		$FORUM_CRUMB = $FORUM_VIEWFORUM_TEMPLATE['forum-crumb'];

	}

	$forumInfo['forum_name'] = $tp->toHTML($forumInfo['forum_name'], true, 'no_hook, emotes_off');
	$forumInfo['forum_description'] = $tp->toHTML($forumInfo['forum_description'], true, 'no_hook');

	$_forum_name = (substr($forumInfo['forum_name'], 0, 1) == '*' ? substr($forumInfo['forum_name'], 1) : $forumInfo['forum_name']);

	e107::title($_forum_name . ' / ' . LAN_FORUM_1001);
	// define('e_PAGETITLE', $_forum_name . ' / ' . LAN_FORUM_1001);

// SEO - meta description (auto)
	if(!empty($forumInfo['forum_description']))
	{
		define("META_DESCRIPTION", $tp->text_truncate(
			str_replace(
			//array('"', "'"), '', strip_tags($tp->toHTML($forumInfo['forum_description']))
				array('"', "'"), '', $tp->toText($forumInfo['forum_description'])
			), 250, '...'));
	}

	$moderatorUserIds = $forum->getModeratorUserIdsByForumId($forumId);
	define('MODERATOR', (USER && in_array(USERID, $moderatorUserIds)));

	if(MODERATOR)
	{
		if($_POST)
		{
			require_once(e_PLUGIN . 'forum/forum_mod.php');
			$forumSCvars['message'] = forum_thread_moderate($_POST);
		}
	}

	if(e_AJAX_REQUEST && MODERATOR) // see javascript above.
	{
		$forum->ajaxModerate();
	}

	if(varset($pref['track_online']))
	{
		$member_users = $sql->count('online', '(*)', "WHERE online_location LIKE('" . $tp->filter(e_REQUEST_URI, 'url') . "%') AND online_user_id != 0");
		$guest_users = $sql->count('online', '(*)', "WHERE online_location LIKE('" . $tp->filter(e_REQUEST_URI, 'url') . "%') AND online_user_id = 0");
		$users = $member_users + $guest_users;

	}
	else
	{
		$users = 0;
		$member_users = 0;
		$guest_users = 0;


	}

	require_once(HEADERF);


	$text = '';
// TODO - message batch shortcode
	/*--
	if ($message)
	{
		//$ns->tablerender('', $message, array('forum_viewforum', 'msg'));
		//e107::getMessage()->add($thread->message);
		$fVars->MESSAGE = $message;
	}
	--*/

	$threadCount = (int) $forumInfo['forum_threads'];

	if($threadCount > $view)
	{
		$pages = ceil($threadCount / $view);
	}
	else
	{
		$pages = false;
	}


	if($pages)
	{
		if(strpos($FORUM_VIEW_START, 'THREADPAGES') !== false || strpos($FORUM_VIEW_END, 'THREADPAGES') !== false)
		{
			// issue #3087 url need to be decoded first (because the [FROM] get's encoded in url())
			// and to encode the full url to not loose the id param when being used in the $forumSCvars['parms']
			$url = rawurlencode(rawurldecode(e107::url('forum', 'forum', $forumInfo, array('query' => array('p' => '[FROM]')))));
			$forumSCvars['parms'] = "total={$pages}&type=page&current={$page}&url=" . $url . "&caption=off";

			unset($urlparms);
		}
	}

//-- if($forum->checkPerm($forumId, 'thread')) //new thread access only.
	if($forum->checkPerm($forumId, 'post')) //new thread access only.
	{
		$forumSCvars['ntUrl'] = e107::url('forum', 'post') . "?f=nt&amp;id=" . $forumId;
	}

//XXX  What is this?
	if(!empty($forumInfo['forum_name']) && (substr($forumInfo['forum_name'], 0, 1) == '*'))
	{
		$forum_info['forum_name'] = substr($forum_info['forum_name'], 1);
		$container_only = true;
	}
	else
	{
		$container_only = false;
	}

	if(!empty($forum_info['sub_parent']) && (substr($forum_info['sub_parent'], 0, 1) == '*'))
	{
		$forum_info['sub_parent'] = substr($forum_info['sub_parent'], 1);
	}

//----$forum->set_crumb(true, '', $fVars); // set $BREADCRUMB (and $BACKLINK)

//-- Function eventually to be reworked (move full function to shortcode file, or make a new breadcrumb function, like in downloads, maybe?)
	$forum->set_crumb(true, '', $forumSCvars); // set $BREADCRUMB (and $BACKLINK)

	$modUser = array();
	$modArray = $forum->forumGetMods();
	foreach($modArray as $user)
	{
		$modUser[] = "<a href='" . e107::getUrl()->create('user/profile/view', $user) . "'>" . $user['user_name'] . "</a>";
	}


	$forumSCvars['forum_name'] = $forumInfo['forum_name'];
	$forumSCvars['forum_description'] = $forumInfo['forum_description'];
	$forumSCvars['forum_image'] = $forumInfo['forum_image'];
	$forumSCvars['modUser'] = $modUser;
	$forumSCvars['track_online'] = varset($pref['track_online']);


	$sticky_threads = 0;
	$stuck = false;
	$reg_threads = 0;
	$unstuck = false;

	$threadFilter = null;

	if(!empty($_GET['srch']))
	{
		$threadFilter = "t.thread_name LIKE '%" . $tp->filter($_GET['srch'], 'w') . "%'";
	}

	$threadList = $forum->forumGetThreads($forumId, $threadFrom, $view, $threadFilter);
	$forumSCvars['forum_parent'] = $forumInfo['forum_parent'];

	$forum_view_forum = '';

	if(count($threadList))
	{
		foreach($threadList as $thread_info)
		{
			if($thread_info['thread_options'])
			{
				$thread_info['thread_options'] = unserialize($thread_info['thread_options']);
			}
			else
			{
				$thread_info['thread_options'] = array();
			}

			if($thread_info['thread_sticky'])
			{
				$sticky_threads++;
			}

			if($sticky_threads > 0 && !$stuck && $forum->prefs->get('hilightsticky'))
			{
				if(!empty($FORUM_IMPORTANT_ROW))
				{
					$forum_view_forum .= $FORUM_IMPORTANT_ROW;
				}
				else
				{
					$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>" . LAN_FORUM_1006 . "</b></span></td></tr>";
				}

				$stuck = true;
			}

			if(!$thread_info['thread_sticky'])
			{
				$reg_threads++;
			}

			if($reg_threads === 1) //  Removed as not needed in new template. && !$unstuck && $stuck
			{
				if(THEME_LEGACY === true && ($stuck === false || $unstuck === true))
				{
					$doNothing; // do nothing.
				}
				elseif(!empty($FORUM_NORMAL_ROW))
				{
					$forum_view_forum .= $FORUM_NORMAL_ROW;
				}
				else
				{
					$forum_view_forum .= "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>" . LAN_FORUM_1007 . "</b></span></td></tr>";
				}

				$unstuck = true;
			}

			$forum_view_forum .= $this->parse_thread($thread_info);
		}
	}
	else
	{
		$forum_view_forum .= deftrue('BOOTSTRAP') ? "<div class='alert alert-warning'>" . LAN_FORUM_1008 . "</div>" :
			"<tr><td class='forumheader alert alert-warning alert-block' colspan='6'>" . LAN_FORUM_1008 . "</td></tr>";
	}

//--$fVars->FORUMJUMP = forumjump();
//--$fVars->TOPLINK = "<a href='".e_SELF.'?'.e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_GO.'</a>'; // FIXME - TOPLINK not used anymore?

	if($container_only)
	{
		$FORUM_VIEW_START = ($FORUM_VIEW_START_CONTAINER ? $FORUM_VIEW_START_CONTAINER : $FORUM_VIEW_START);
		$FORUM_VIEW_END = ($FORUM_VIEW_END_CONTAINER ? $FORUM_VIEW_END_CONTAINER : $FORUM_VIEW_END);
		$forum_view_forum = '';
	}


	$sc->setVars($forumSCvars);

	$forum_view_start = $tp->parseTemplate($FORUM_VIEW_START_CONTAINER . $FORUM_VIEW_START, true, $sc);
	$forum_view_forum = $tp->parseTemplate($forum_view_forum, true, $sc);
	$forum_view_end = $tp->parseTemplate($FORUM_VIEW_END . $FORUM_VIEW_END_CONTAINER, true, $sc);


	if($forum->prefs->get('enclose'))
	{
// $forum_view_subs????
		$caption = varset($FORUM_VIEW_CAPTION) ? $tp->parseTemplate($FORUM_VIEW_CAPTION, true, $sc) : $forum->prefs->get('title');

		$ns->tablerender($caption, $forum_view_start . /*$forum_view_subs.*/ $forum_view_forum . $forum_view_end, 'forum-viewforum');
	}
	else
	{
		echo $forum_view_start . $forum_view_forum . $forum_view_end;
	}

	echo "<script type=\"text/javascript\">
	function confirm_(thread_id)
	{
		return confirm(" . $tp->toJSON(LAN_JSCONFIRM) . ");
	}
	</script>";

	return array($forum_info, $FORUM_CRUMB, $forumSCvars, $FORUM_VIEW_CAPTION);
}



	function parse_thread($thread_info)
	{

		// TODO Remove globals.
		global $sc, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE;
		$tp = e107::getParser();

		$sc->setVars($thread_info);

		switch($thread_info['thread_sticky'])
		{
			case 1:
				$_TEMPLATE = ($FORUM_VIEW_FORUM_STICKY ? $FORUM_VIEW_FORUM_STICKY : $FORUM_VIEW_FORUM);
				break;

			case 2:
				$_TEMPLATE = ($FORUM_VIEW_FORUM_ANNOUNCE ? $FORUM_VIEW_FORUM_ANNOUNCE : $FORUM_VIEW_FORUM);
				break;

			default:
				$_TEMPLATE = $FORUM_VIEW_FORUM;
				break;
		}


		if(substr($_TEMPLATE, 0, 4) == '<tr>') // Inject id into table row. //XXX Find a better way to do this without placing in template. .
		{

			$threadId = $thread_info['thread_id'];

			$_TEMPLATE = "<tr id='thread-{$threadId}'>" . substr($_TEMPLATE, 4);
		}

		return $tp->parseTemplate($_TEMPLATE, true, $sc);

	}

/*
	function forumjump()
	{

		global $forum;
		$jumpList = $forum->forumGetAllowed('view');
		$text = "<form method='post' action='" . e_SELF . "'><p>" . LAN_FORUM_1017 . ": <select name='forumjump' class='tbox'>";
		foreach($jumpList as $key => $val)
		{
			$text .= "\n<option value='" . e107::url('forum', 'forum', $val, 'full') . "'>" . $val['forum_name'] . "</option>";
		}
		$text .= "</select> <input class='btn btn-default btn-secondary button' type='submit' name='fjsubmit' value='" . LAN_GO . "' /></form>";

		return $text;
	}
*/




/*

	function newthreadjump($url)
	{

		global $forum;
		$jumpList = $forum->forumGetAllowed('view');

		$text = '<div class="btn-group">
	    <a href="' . $url . '" class="btn btn-primary">' . LAN_FORUM_1018 . '</a>
	    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
	    <span class="caret"></span>
	    </button>
	    <ul class="dropdown-menu pull-right float-right">
	    ';

			foreach($jumpList as $key => $val)
			{
				$text .= '<li><a href="' . e107::url('forum', 'forum', $val) . '">' . LAN_FORUM_1017 . ': ' . $val['forum_name'] . '</a></li>';
			}

			$text .= '
	    </ul>
	    </div>';

		return $text;
	}
*/


}



//list($forum_info, $FORUM_CRUMB, $forumSCvars, $FORUM_VIEW_CAPTION) = init();

new forum_viewforum_front();

require_once(FOOTERF);