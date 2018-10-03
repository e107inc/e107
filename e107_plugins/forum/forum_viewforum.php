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
	require_once('../../class2.php');
}
$e107 = e107::getInstance();
if (!e107::isInstalled('forum'))
{
	e107::redirect();
	exit;
}
e107::lan('forum', "front", true);
//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_viewforum.php'); // now uses English_front.php

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


define('NAVIGATION_ACTIVE','forum');




if (!e_QUERY && empty($_GET))
{
	if(E107_DEBUG_LEVEL > 0)
	{
		echo __FILE__ .' Line: '.__LINE__;
		exit;
	}
	$url = e107::url('forum','index','full');
	e107::getRedirect()->go($url);
	//header('Location:'.e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
	exit;
}

if(!empty($_GET['sef']))
{
	if($newID =  $sql->retrieve('forum', 'forum_id', "forum_sef= '".$tp->toDB($_GET['sef'])."' LIMIT 1"))
	{
		$_REQUEST['id'] = $newID;
	}
}

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

//$view = 25;
$view = $forum->prefs->get('threadspage', 25);
if(!$view) { $view = 25; }
$page = (varset($_GET['p']) ? $_GET['p'] : 1);
$threadFrom = ($page - 1) * $view;

global $forum_info, $FORUM_CRUMB;

				$sc = e107::getScBatch('viewforum', 'forum');

//----$fVars = new e_vars;

/*--
$fVars->STARTERTITLE = LAN_FORUM_1004;
$fVars->THREADTITLE = LAN_FORUM_1003;
$fVars->REPLYTITLE = LAN_FORUM_0003;
$fVars->LASTPOSTITLE = LAN_FORUM_0004;
$fVars->VIEWTITLE = LAN_FORUM_1005;
--*/

$forumId = (int)$_REQUEST['id'];

if(!$forumId && e_QUERY) // BC Fix for old links.
{
	list($id,$from) = explode(".",e_QUERY);
	$forumId = intval($id);
	$threadFrom = intval($from);
	unset($id,$from);
}

if (!$forum->checkPerm($forumId, 'view'))
{
	// header('Location:'.e107::getUrl()->create('forum/forum/main'));

	$url = e107::url('forum','index','full');

	if(E107_DEBUG_LEVEL > 0)
	{
		print_a($_REQUEST);
		print_a($_GET);
		echo __FILE__ .' Line: '.__LINE__;
		echo "   forumId: ".$forumId;
		exit;
	}


	e107::getRedirect()->go($url);

	exit;
}

$forumInfo = $forum->forumGet($forumId);
$forumSCvars = array();
//----$threadsViewed = $forum->threadGetUserViewed();

if (empty($FORUM_VIEW_START))
{
	if(THEME_LEGACY !== true)
	{
		 $FORUM_VIEWFORUM_TEMPLATE = e107::getTemplate('forum','forum_viewforum');
	}
	else
	{
		if(file_exists(THEME.'templates/forum/forum_viewforum_template.php'))
		{
			require_once(THEME.'templates/forum/forum_viewforum_template.php');
		}
		elseif (file_exists(THEME.'forum_viewforum_template.php')) //v1.x
		{
			require_once(THEME.'forum_viewforum_template.php');
		}
		elseif (file_exists(THEME.'forum_template.php'))  //v1.x
		{
			require_once(THEME.'forum_template.php');
		}
		else
		{
			require_once(e_PLUGIN.'forum/templates/forum_viewforum_template.php');
		}

	}


}


if(!empty($FORUM_VIEWFORUM_TEMPLATE) && is_array($FORUM_VIEWFORUM_TEMPLATE) && THEME_LEGACY !== true) // New v2.x bootstrap Template.
{
	
	$FORUM_VIEW_CAPTION				= $FORUM_VIEWFORUM_TEMPLATE['caption'];
	$FORUM_VIEW_START_CONTAINER		= $FORUM_VIEWFORUM_TEMPLATE['start'];
	$FORUM_VIEW_START				= $FORUM_VIEWFORUM_TEMPLATE['header'];
	$FORUM_VIEW_FORUM				= $FORUM_VIEWFORUM_TEMPLATE['item'];
	$FORUM_VIEW_FORUM_STICKY		= $FORUM_VIEWFORUM_TEMPLATE['item-sticky'];
	$FORUM_VIEW_FORUM_ANNOUNCE		= $FORUM_VIEWFORUM_TEMPLATE['item-announce'];
	$FORUM_VIEW_END					= $FORUM_VIEWFORUM_TEMPLATE['footer'];
	$FORUM_VIEW_END_CONTAINER		= $FORUM_VIEWFORUM_TEMPLATE['end'];
	$FORUM_VIEW_SUB_START			= $FORUM_VIEWFORUM_TEMPLATE['sub-header'];		
	$FORUM_VIEW_SUB					= $FORUM_VIEWFORUM_TEMPLATE['sub-item'];		
	$FORUM_VIEW_SUB_END				= $FORUM_VIEWFORUM_TEMPLATE['sub-footer'];		
	$FORUM_IMPORTANT_ROW			= $FORUM_VIEWFORUM_TEMPLATE['divider-important'];
	$FORUM_NORMAL_ROW				= $FORUM_VIEWFORUM_TEMPLATE['divider-normal'];	
	
	$FORUM_CRUMB				    = $FORUM_VIEWFORUM_TEMPLATE['forum-crumb'];	
	
}



$forumInfo['forum_name'] = $tp->toHTML($forumInfo['forum_name'], true, 'no_hook, emotes_off');
$forumInfo['forum_description'] = $tp->toHTML($forumInfo['forum_description'], true, 'no_hook');

$_forum_name = (substr($forumInfo['forum_name'], 0, 1) == '*' ? substr($forumInfo['forum_name'], 1) : $forumInfo['forum_name']);
define('e_PAGETITLE', $_forum_name.' / '.LAN_FORUM_1001);

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

if (MODERATOR)
{
	if ($_POST)
	{
		require_once(e_PLUGIN.'forum/forum_mod.php');
		$forumSCvars['message'] = forum_thread_moderate($_POST);
	}
}

if(e_AJAX_REQUEST && MODERATOR) // see javascript above. 
{
	$forum->ajaxModerate();
}

if(varset($pref['track_online']))
{
//	$member_users = $sql->count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id != 0");
//	$guest_users = $sql->count('online', '(*)', "WHERE online_location REGEXP('viewforum.php.id=$forumId\$') AND online_user_id = 0");
	$member_users = $sql->count('online', '(*)', "WHERE online_location LIKE('".$tp->filter(e_REQUEST_URI, 'url')."%') AND online_user_id != 0");
	$guest_users = $sql->count('online', '(*)', "WHERE online_location LIKE('".$tp->filter(e_REQUEST_URI, 'url')."%') AND online_user_id = 0");


	$users = $member_users+$guest_users;

}
else
{
	$users = 0;
	$member_users= 0;
	$guest_users = 0;


}

require_once(HEADERF);


$text='';
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

if ($threadCount > $view)
{
	$pages = ceil($threadCount/$view);
}
else
{
	$pages = false;
}


if ($pages)
{
	if(strpos($FORUM_VIEW_START, 'THREADPAGES') !== false || strpos($FORUM_VIEW_END, 'THREADPAGES') !== false)
	{
		// issue #3087 url need to be decoded first (because the [FROM] get's encoded in url())
		// and to encode the full url to not loose the id param when being used in the $forumSCvars['parms']
		$url = rawurlencode(rawurldecode(e107::url('forum','forum',$forumInfo, array('query'=>array('p'=>'[FROM]')))));

		/*--
		$parms = "total={$pages}&type=page&current={$page}&url=".$url."&caption=off";
		$fVars->THREADPAGES = $tp->parseTemplate("{NEXTPREV={$parms}}");
		--*/
		$forumSCvars['parms'] = "total={$pages}&type=page&current={$page}&url=".$url."&caption=off";
//-- ?????????? unset $ulrparms????
		unset($urlparms);
	}
}

//-- if($forum->checkPerm($forumId, 'thread')) //new thread access only.
if($forum->checkPerm($forumId, 'post')) //new thread access only.
{
		$forumSCvars['ntUrl']= e107::url('forum','post')."?f=nt&amp;id=". $forumId;
/*--
	$ntUrl = e107::url('forum','post')."?f=nt&amp;id=". $forumId;
	$fVars->NEWTHREADBUTTON = "<a href='".$ntUrl."'>".IMAGE_newthread.'</a>';
	$fVars->NEWTHREADBUTTONX = newthreadjump($ntUrl);

}

if(!BOOTSTRAP)
{
	$fVars->NEWTHREADBUTTONX = $fVars->NEWTHREADBUTTON;
}
--*/
}

//XXX  What is this?
if(substr($forumInfo['forum_name'], 0, 1) == '*')
{
	$forum_info['forum_name'] = substr($forum_info['forum_name'], 1);
	$container_only = true;
}
else
{
	$container_only = false;
}

if(substr($forum_info['sub_parent'], 0, 1) == '*')
{
	$forum_info['sub_parent'] = substr($forum_info['sub_parent'], 1);
}

//----$forum->set_crumb(true, '', $fVars); // set $BREADCRUMB (and $BACKLINK)

//-- Function eventually to be reworked (move full function to shortcode file, or make a new breadcrumb function, like in downloads, maybe?)
$forum->set_crumb(true, '', $forumSCvars); // set $BREADCRUMB (and $BACKLINK)

$modUser = array();
foreach ( $modArray as $user)
{
	$modUser[] = "<a href='".e107::getUrl()->create('user/profile/view', $user)."'>".$user['user_name']."</a>";
}
/*--
$fVars->FORUMTITLE = $forumInfo['forum_name'];
$fVars->MODERATORS = LAN_FORUM_1009.': '.implode(', ', $modUser);
$fVars->BROWSERS = '';
--*/
		$forumSCvars['forum_name']= $forumInfo['forum_name'];
		$forumSCvars['forum_image']= $forumInfo['forum_image'];
		$forumSCvars['modUser']= $modUser;
		$forumSCvars['track_online']= varset($pref['track_online']);

/*--
if(varset($pref['track_online']))
{
	$fVars->BROWSERS = $users.' '.($users == 1 ? LAN_FORUM_0059 : LAN_FORUM_0060).' ('.$member_users.' '.($member_users == 1 ? LAN_FORUM_0061 : LAN_FORUM_0062).", ".$guest_users." ".($guest_users == 1 ? LAN_FORUM_0063 : LAN_FORUM_0064).')';
}



if(defset('BOOTSTRAP')==3 && !empty($FORUM_VIEWFORUM_TEMPLATE['iconkey'])) // v2.x
{
	$fVars->ICONKEY = $tp->parseTemplate($FORUM_VIEWFORUM_TEMPLATE['iconkey'],true);
}
else // v1.x
{
	$fVars->ICONKEY = "
	<table class='table table-bordered' style='width:100%'>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0039."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0040."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_sticky_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1011."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_announce_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1013."</td>
	</tr>
	<tr>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_popular_small."</td>
	<td style='width:2%' class='smallblacktext'>".LAN_FORUM_0039." ".LAN_FORUM_1010."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_popular_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0040." ".LAN_FORUM_1010."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_stickyclosed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1012."</td>
	<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_closed_small."</td>
	<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1014."</td>
	</tr>
	</table>";

}
--*/

// ----------------- { VIEWABLE_BY } ---------------------------
/*--
if($users = $forum->getForumClassMembers($forumId))
{
	$userList = array();
	if(is_array($users))
	{
		foreach($users as $user)
		{
				$userList[] = "<a href='".e107::getUrl()->create('user/profile/view', $user)."'>".$user['user_name']."</a>";
		}

		$viewable = implode(', ', $userList);;
	}
	elseif($users == 0)
	{
		$viewable = '' ;
	}
	else
	{
		$viewable =  e107::getUserClass()->getFixedClassDescription($users);
	}

}

if(!empty($viewable))
{

	$fVars->VIEWABLE_BY = "

						<div class='panel panel-default' style='margin-top:10px'>
							<div class='panel-heading'>Viewable by</div>
								<div class='panel-body'>
									".$viewable."
								</div>
							</div>
						</div>
				";
}
else
{
	$fVars->VIEWABLE_BY = '';
}
--*/

// ------------------------------------------------------------
///TODO  XXX All these $fVars items need to be put into a shortcode class so they can be parsed with parms and wrappers. Big Job!

/*--
$fVars->SEARCH = "
	<form method='get' class='form-inline input-append' action='".e_BASE."search.php'>
	<p>
	<input class='tbox' type='text' name='q' size='20' value='' maxlength='50' />
	<button class='btn btn-default button' type='submit' name='s' >".LAN_SEARCH."</button>
	<input type='hidden' name='r' value='0' />
	<input type='hidden' name='ref' value='forum' />	
	</p>
	</form>";
--*/

/*--
	// ----- Perm Display ---

	$permDisplay = array();

	$permDisplay['topics'] = ($forum->checkPerm($forumId, 'thread')) ? LAN_FORUM_0043 : LAN_FORUM_0044;
	if($forum->checkPerm($forumId, 'post'))
	{
		$permDisplay['post'] =LAN_FORUM_0045;
		$permDisplay['edit'] = LAN_FORUM_0047;
	}
	else
	{
		$permDisplay['post'] =LAN_FORUM_0046;
		$permDisplay['edit'] = LAN_FORUM_0048;
	}


	$fVars->PERMS = implode("<span class='forum-perms-separator'><!-- --></span>", $permDisplay);
--*/


	// -------------------------------

$sticky_threads = 0;
$stuck = false;
$reg_threads = 0;
$unstuck = false;

$threadFilter = null;

if(!empty($_GET['srch']))
{
	$threadFilter = "t.thread_name LIKE '%".$tp->filter($_GET['srch'], 'w')."%'";
}

$threadList = $forum->forumGetThreads($forumId, $threadFrom, $view, $threadFilter);
/*--
$subList = $forum->forumGetSubs(vartrue($forum_id));
--*/
//------$gen = new convert;
$forumSCvars['forum_parent']= $forumInfo['forum_parent'];
/*--
$fVars->SUBFORUMS = '';
if(is_array($subList) && isset($subList[$forumInfo['forum_parent']][$forumId]))
{
	$newflag_list = $forum->forumGetUnreadForums();
	$sub_info = '';
	foreach($subList[$forumInfo['forum_parent']][$forumId] as $sub)
	{
		$sub_info .= parse_sub($sub);
	}
	$fVars->SUBFORUMS = $FORUM_VIEW_SUB_START.$sub_info.$FORUM_VIEW_SUB_END;
}
--*/
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
					// do nothing.
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

			$forum_view_forum .= parse_thread($thread_info);
		}
	}
else
{
	$forum_view_forum .= deftrue('BOOTSTRAP') ? "<div class='alert alert-warning'>".LAN_FORUM_1008."</div>":
"<tr><td class='forumheader alert alert-warning alert-block' colspan='6'>".LAN_FORUM_1008."</td></tr>";
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


//var_dump ($FORUM_VIEW_START);
//  	var_dump ($FORUM_VIEW_SUB);
$forum_view_start = $tp->parseTemplate($FORUM_VIEW_START_CONTAINER.$FORUM_VIEW_START, true, $sc);
$forum_view_forum = $tp->parseTemplate($forum_view_forum, true, $sc);
$forum_view_end = $tp->parseTemplate($FORUM_VIEW_END.$FORUM_VIEW_END_CONTAINER, true, $sc);

//$forum_view_start .= "<hr><hr>FVARS FORUM<hr><hr>".$tp->simpleParse($FORUM_VIEW_START, $fVars);
//$forum_view_end = $tp->simpleParse($FORUM_VIEW_END, $fVars);

if ($forum->prefs->get('enclose'))
{	
// $forum_view_subs????
	$caption = varset($FORUM_VIEW_CAPTION) ? $tp->parseTemplate($FORUM_VIEW_CAPTION, true, $sc) : $forum->prefs->get('title');

	$ns->tablerender($caption, $forum_view_start.$forum_view_subs.$forum_view_forum.$forum_view_end, 'forum-viewforum');
}
else
{
	echo $forum_view_start.$forum_view_forum.$forum_view_end;
}

echo "<script type=\"text/javascript\">
	function confirm_(thread_id)
	{
		return confirm(\"".$tp->toJS(LAN_JSCONFIRM)."\");
	}
	</script>";

require_once(FOOTERF);

function parse_thread($thread_info)
{
//var_dump ($thread_info);
/* OLD CODE
	global $forum, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE, $gen, $menu_pref, $threadsViewed;
	$tp = e107::getParser();
	$tVars = new e_vars;

	$threadId = $thread_info['thread_id'];
	$forumId = $thread_info['thread_forum_id'];

	$tVars = array();

	$tVars['VIEWS'] = $thread_info['thread_views'];
	$tVars['REPLIES'] = $thread_info['thread_total_replies'];
	
	$badge = ($thread_info['thread_views'] > 0) ? "badge-info" : "";
	
	$tVars['REPLIESX'] = "<span class='badge badge-info'>".$thread_info['thread_total_replies']."</span>";
	$tVars['VIEWSX'] = "<span class='badge {$badge}'>".$thread_info['thread_views']."</span>";

	if ($tVars['REPLIES'])
	{
		$lastpost_datestamp = $gen->convert_date($thread_info['thread_lastpost'], 'forum');
		if($thread_info['lastpost_username'])
		{
			// XXX hopefully & is not allowed in user name - it would break parsing of url parameters, change to array if something wrong happens
			$url = e107::getUrl()->create('user/profile/view', "name={$thread_info['lastpost_username']}&id={$thread_info['thread_lastuser']}");
			$tVars['LASTPOST'] = "<a href='{$url}'>".$thread_info['lastpost_username']."</a>";
			$tVars['LASTPOSTUSER'] = "<a href='{$url}'>".$thread_info['lastpost_username']."</a>";
		}
		else
		{
			if(!$thread_info['thread_lastuser'])
			{
				$tVars['LASTPOST'] = $tp->toHTML($thread_info['thread_lastuser_anon']);
				$tVars['LASTPOSTUSER'] = $tp->toHTML($thread_info['thread_lastuser_anon']);
			}
			else
			{
				$tVars['LASTPOST'] = LAN_FORUM_1015;
				$tVars['LASTPOSTUSER'] = LAN_FORUM_1015;
			}
		}
		$tVars['LASTPOST'] .= '<br />'.$lastpost_datestamp;

		$tVars['LASTPOSTUSER'] = $thread_info['lastpost_username']; // $lastpost_name;

		$thread_info['thread_sef'] = eHelper::title2sef($thread_info['thread_name'],'dashl');

		$urlData = array('forum_sef'=>$thread_info['forum_sef'], 'thread_id'=>$thread_info['thread_id'],'thread_sef'=>$thread_info['thread_sef']);
		$url = e107::url('forum', 'topic', $urlData);
		$url .= (strpos($url,'?')!==false) ? '&' : '?';
		$url .= "last=1#post-".$thread_info['lastpost_id'];

		$tVars['LASTPOSTDATE'] .= "<a href='".$url."'>".  $gen->computeLapse($thread_info['thread_lastpost'],time(), false, false, 'short')."</a>";
	}

	$newflag = (USER && $thread_info['thread_lastpost'] > USERLV && !in_array($thread_info['thread_id'], $threadsViewed));

	$tVars['THREADDATE'] = $gen->convert_date($thread_info['thread_datestamp'], 'forum');
	
	$tVars['THREADTIMELAPSE'] = $gen->computeLapse($thread_info['thread_datestamp'],time(), false, false, 'short'); //  convert_date($thread_info['thread_datestamp'], 'forum');



	/// ---------- Icon ----------------

	$tVars['ICON'] = ($newflag ? IMAGE_new : IMAGE_nonew);
	if ($tVars['REPLIES'] >= $forum->prefs->get('popular', 10))
	{
	  $tVars['ICON'] = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
	}
	elseif(empty($tVars['REPLIES']) && defined('IMAGE_noreplies'))
	{
		 $tVars['ICON'] = IMAGE_noreplies;
	}

	$tVars['THREADTYPE'] = '';
	if ($thread_info['thread_sticky'] == 1)
	{
		$tVars['ICON'] = ($thread_info['thread_active'] ? IMAGE_sticky : IMAGE_stickyclosed);
		$tVars['THREADTYPE'] = '['.LAN_FORUM_1011.']<br />';
	}
	elseif($thread_info['thread_sticky'] == 2)
	{
		$tVars['ICON'] = IMAGE_announce;
		$tVars['THREADTYPE'] = '['.LAN_FORUM_1013.']<br />';
	}
	elseif(!$thread_info['thread_active'])
	{
		$tVars['ICON'] = IMAGE_closed;
	}

// $tVars['ICON'] = $tVars['REPLIES'];

	// ------------------------------------------------------







	$thread_name = strip_tags($tp->toHTML($thread_info['thread_name'], false, 'no_hook, emotes_off'));
	if(isset($thread_info['thread_options']['poll']))
	{
		$thread_name = '['.LAN_FORUM_1016.'] ' . $thread_name;
	}
//	if (strtoupper($THREADTYPE) == strtoupper(substr($thread_name, 0, strlen($THREADTYPE))))
//	{
//		$thread_name = substr($thread_name, strlen($THREADTYPE));
//	}
	if ($forum->prefs->get('tooltip'))
	{
		$thread_thread = strip_tags($tp->toHTML($thread_info['thread_thread'], true, 'no_hook'));
		$tip_length = $forum->prefs->get('tiplength', 400);
		if (strlen($thread_thread) > $tip_length)
		{
			//$thread_thread = substr($thread_thread, 0, $tip_length).' '.$menu_pref['newforumposts_postfix'];
			$thread_thread = $tp->text_truncate($thread_thread, $tip_length, $menu_pref['newforumposts_postfix']);	// Doesn't split entities
		}
		$thread_thread = str_replace("'", '&#39;', $thread_thread);
		$title = "title='".$thread_thread."'";
	}
	else
	{
		$title = '';
	}
	// $tVars['THREADNAME'] = "<a {$title} href='".e107::getUrl()->create('forum/thread/view', array('id' => $threadId, 'name' => $thread_name))."'>{$thread_name}</a>";

//	$url = e107::getUrl()->create('forum/thread/view', array('id' => $threadId, 'name' => $thread_name));

	$thread_info['thread_sef'] = eHelper::title2sef($thread_info['thread_name'],'dashl');
	$url = e107::url('forum','topic', $thread_info);
	$tVars['THREADNAME'] = "<a {$title} href='".$url."'>{$thread_name}</a>";


	// FIXME - pages -> convert to nextprev shortcode
	/*
	$pages = ceil(($tVars['REPLIES'])/$forum->prefs->get('postspage'));
	$urlparms = $thread_info;
	if ($pages > 1)
	{
		if($pages > 6)
		{
			for($a = 0; $a <= 2; $a++)
			{
				$aa = $a + 1;
				$tVars['PAGES'] .= $tVars['PAGES'] ? ' ' : '';
				$urlparms['page'] = $aa;
				$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$tVars['PAGES'] .= "<a href='{$url}'>{$aa}</a>";
			}
			$tVars['PAGES'] .= ' ... ';
			for($a = $pages-3; $a <= $pages-1; $a++)
			{
				$aa = $a + 1;
				$tVars['PAGES'] .= $tVars['PAGES'] ? ' ' : '';
				$urlparms['page'] = $aa;
				$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$tVars['PAGES'] .= "<a href='{$url}'>{$aa}</a>";
			}
		}
		else
		{
			for($a = 0; $a <= ($pages-1); $a++)
			{
				$aa = $a + 1;
				$tVars['PAGES'] .= $tVars['PAGES'] ? ' ' : '';
				$urlparms['page'] = $aa;
				$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$tVars['PAGES'] .= "<a href='{$url}'>{$aa}</a>";
			}
		}
		$tVars['PAGES'] = LAN_GOTO.' [&nbsp;'.$tVars['PAGES'].'&nbsp;]';
	}
	else
	{
		$tVars['PAGES'] = '';
	}
	*/
/* OLD CODE

	$tVars['PAGES'] = fpages($thread_info, $tVars['REPLIES']);
	$tVars['PAGESX'] = fpages($thread_info, $tVars['REPLIES']);

	if (MODERATOR)
	{
		// FIXME _URL_ thread name
		// e107::getUrl()->create('forum/forum/view', "id={$thread_info['thread_forum_id']}")
		// USED self instead

		$moveUrl        = e107::url('forum','move', $thread_info);

		$tVars['ADMIN_ICONS'] = "
		<form method='post' action='".e_REQUEST_URI."' id='frmMod_{$forumId}_{$threadId}' style='margin:0;'><div class='forum-viewforum-admin-icons'>
		<input type='image' ".IMAGE_admin_delete." name='deleteThread_{$threadId}' value='thread_action' onclick=\"return confirm_({$threadId})\" />
		".($thread_info['thread_sticky'] == 1 ? "<input type='image' ".IMAGE_admin_unstick." name='unstick_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_stick." name='stick_{$threadId}' value='thread_action' /> ")."
		".($thread_info['thread_active'] ? "<input type='image' ".IMAGE_admin_lock." name='lock_{$threadId}' value='thread_action' /> " : "<input type='image' ".IMAGE_admin_unlock." name='unlock_{$threadId}' value='thread_action' /> "). "
		<a class='e-tip' title=\"".LAN_FORUM_5019."\" href='".$moveUrl."'>".IMAGE_admin_move.'</a>
		</div></form>
		';
		
		$tVars['ADMINOPTIONS'] = fadminoptions($thread_info);
	}


	if($thread_info['user_name'])
	{
		$tVars['POSTER'] = "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $thread_info['thread_user'], 'name' => $thread_info['user_name']))."'>".$thread_info['user_name']."</a>";
	}
	else
	{
		if($thread_info['thread_user_anon'])
		{
			$tVars['POSTER'] = $tp->toHTML($thread_info['thread_user_anon']);
		}
		else
		{
			$tVars['POSTER'] = LAN_FORUM_1015;
		}
	}

	if (!$tVars['REPLIES'])
	{
		$tVars['REPLIES'] = '0';
		$tVars['REPLIESX'] = "<span class='badge'>0</span>";
		$tVars['LASTPOST'] = ' - ';
		$tVars['LASTPOSTDATE'] = ' - ';
	}

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
	
	
	if(substr($_TEMPLATE,0,4) == '<tr>') // Inject id into table row. //XXX Find a better way to do this without placing in template. . 
	{
		$_TEMPLATE = "<tr id='thread-{$threadId}'>".substr($_TEMPLATE,4);	
	}
	
	if(!deftrue('BOOTSTRAP'))
	{
		$tVars['REPLIESX']        = 	$tVars['REPLIES'];
		$tVars['VIEWSX']	        = $tVars['VIEWS'];
		$tVars['ADMINOPTIONS']    = $tVars['ADMIN_ICONS'];
	}
	
	$tVars['_WRAPPER_'] = 'forum_viewforum';

	return $tp->parseTemplate($_TEMPLATE, true, $tVars);
*/
// NEW REWRITTEN CODE
	global $sc, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE;
	$tp = e107::getParser();

// Initial ideia, to have a separate shortcode var ($threadsc)....
//  $threadsc = e107::getScBatch('viewforum', 'forum', 'viewforumthread');
//				$threadsc->setVars($thread_info);
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
	
	
	if(substr($_TEMPLATE,0,4) == '<tr>') // Inject id into table row. //XXX Find a better way to do this without placing in template. . 
	{

	$threadId = $thread_info['thread_id'];

		$_TEMPLATE = "<tr id='thread-{$threadId}'>".substr($_TEMPLATE,4);	
	}

	return $tp->parseTemplate($_TEMPLATE, true, $sc);

}

/*----
function parse_sub($subInfo)
{
	global $FORUM_VIEW_SUB, $gen, $newflag_list;
	$tp = e107::getParser();
//	$tVars = new e_vars;

	$forumName = $tp->toHTML($subInfo['forum_name'], true);
	$tVars['SUB_FORUMTITLE'] = "<a href='".e107::getUrl()->create('forum/forum/view', $subInfo)."'>{$forumName}</a>";
	$tVars['SUB_DESCRIPTION'] = $tp->toHTML($subInfo['forum_description'], false, 'no_hook');
	$tVars['SUB_THREADS'] = $subInfo['forum_threads'];
	$tVars['SUB_REPLIES'] = $subInfo['forum_replies'];
	
	$badgeReplies = ($subInfo['forum_replies']) ? "badge-info" : "";
	$badgeThreads = ($subInfo['forum_replies']) ? "badge-info" : "";
	
	$tVars['SUB_THREADSX'] = "<span class='badge {$badgeThreads}'>".$subInfo['forum_threads']."</span>";
	$tVars['SUB_REPLIESX'] = "<span class='badge {$badgeReplies}'>".$subInfo['forum_replies']."</span>";

//	$tVars['REPLIESX'] = "<span class='badge badge-info'>".$thread_info['thread_total_replies']."</span>";
//	$tVars['VIEWSX'] = "<span class='badge {$badge}'>".$thread_info['thread_views']."</span>";
	
	if(USER && is_array($newflag_list) && in_array($subInfo['forum_id'], $newflag_list))
	{

		$tVars['NEWFLAG'] = "<a href='".e107::getUrl()->create('forum/forum/mfar', 'id='.$subInfo['forum_id'])."'>".IMAGE_new.'</a>';
	}
	else
	{
		$tVars['NEWFLAG'] = IMAGE_nonew;
	}

	if($subInfo['forum_lastpost_info'])
	{
		$tmp = explode('.', $subInfo['forum_lastpost_info']);
		$lp_thread = "<a href='".e107::getUrl()->create('forum/thread/last', array('id' => $tmp[1]))."'>".IMAGE_post2.'</a>';
		$lp_date = $gen->convert_date($tmp[0], 'forum');

		if($subInfo['user_name'])
		{
			$lp_name = "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $subInfo['forum_lastpost_user'], 'name' => $subInfo['user_name']))."'>{$subInfo['user_name']}</a>";
		}
		else
		{
			$lp_name = $subInfo['forum_lastpost_user_anon'];
		}
		$tVars['SUB_LASTPOST'] = $lp_date.'<br />'.$lp_name.' '.$lp_thread;
		
		$tVars['SUB_LASTPOSTDATE'] = $gen->computeLapse($tmp[0], time(), false, false, 'short');
		$tVars['SUB_LASTPOSTUSER'] = $lp_name;
	}
	else
	{
		$tVars['SUB_LASTPOST'] = '-';
		$tVars['SUB_LASTPOSTUSER'] = '';
		$tVars['SUB_LASTPOSTDATE'] = '';
	}

	$tVars['_WRAPPER_'] = 'forum_viewforum';

	return $tp->parseTemplate($FORUM_VIEW_SUB, true,  $tVars);
}
----*/

function forumjump()
{
	global $forum;
	$jumpList = $forum->forumGetAllowed('view');
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_FORUM_1017.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".e107::url('forum','forum',$val, 'full')."'>".$val['forum_name']."</option>";
	}
	$text .= "</select> <input class='btn btn-default btn-secondary button' type='submit' name='fjsubmit' value='".LAN_GO."' /></form>";
	return $text;
}

function fadminoptions($thread_info)
{
//-- $tVars here???
//----	$tVars = new e_vars;
	$e107 = e107::getInstance();
	$tp = e107::getParser();
	
//	$text = "<form method='post' action='".e_REQUEST_URI."' id='frmMod_{$forumId}_{$threadId}' style='margin:0;'>";
	$text = '<div class="btn-group"><button class="btn btn-default btn-secondary btn-sm btn-mini dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu pull-right">	
   ';
   
	//FIXME - not fully working. 

	$moveUrl        = e107::url('forum','move', $thread_info);
// What's the use of $splitUrl?????
	$splitUrl        = e107::url('forum','split', $thread_info);

	$lockUnlock 	= ($thread_info['thread_active'] ) ? 'lock' : 'unlock';
	$stickUnstick 	= ($thread_info['thread_sticky'] == 1) ? 'unstick' : 'stick';
	$id = intval($thread_info['thread_id']);
	
	$lan = array('stick'=>LAN_FORUM_8007,'unstick'=>LAN_FORUM_8008,'lock'=>LAN_FORUM_8009, 'unlock'=>LAN_FORUM_8010);
	$icon = array(
		'unstick'	=>	$tp->toGlyph('chevron-down'),
		'stick'		=>	$tp->toGlyph('chevron-up'),
		'lock'		=>	$tp->toGlyph('lock'),
		'unlock'	=>	$tp->toGlyph('unlock'),
	);
	


	$text .= "<li class='text-right'><a href='".e_REQUEST_URI."' data-forum-action='delete' data-forum-thread='".$id."'>".LAN_DELETE." ".$tp->toGlyph('trash')."</a></li>";
	$text .= "<li class='text-right'><a href='".e_REQUEST_URI."' data-forum-action='".$stickUnstick."' data-forum-thread='".$id."'>".$lan[$stickUnstick]." ".$icon[$stickUnstick]."</a></li>";
	$text .= "<li class='text-right'><a href='".e_REQUEST_URI."' data-forum-action='".$lockUnlock."' data-forum-thread='".$id."'>".$lan[$lockUnlock]." ".$icon[$lockUnlock]."</a></li>";
	
	$text .= "<li class='text-right'><a href='{$moveUrl}'>".LAN_FORUM_2042." ".$tp->toGlyph('move')."</a></li>";

	if(e_DEVELOPER)
	{
//		$text .= "<li class='text-right'><a href='{$splitUrl}'>Split ".$tp->toGlyph('scissors')."</i></a></li>";
//		print_a($thread_info);
	}

/*
	$text .= "<li><input type='image' ".IMAGE_admin_delete." name='deleteThread_{$threadId}' value='thread_action' onclick=\"return confirm_({$threadId})\" /> Delete</li>";
	
	$text .= "<li>".($thread_info['thread_sticky'] == 1 ? "<input type='image' ".IMAGE_admin_unstick." name='unstick_{$threadId}' value='thread_action' /> Unstick" : "<input type='image' ".IMAGE_admin_stick." name='stick_{$threadId}' value='thread_action' /> Stick")."
		</li>";
		
	$text .= "<li>".($thread_info['thread_active'] ? "<input type='image' ".IMAGE_admin_lock." name='lock_{$threadId}' value='thread_action' /> Lock" : "<input type='image' ".IMAGE_admin_unlock." name='unlock_{$threadId}' value='thread_action' /> Unlock"). "
		</li>";
		
	$text .= "<li><a href='".e107::getUrl()->create('forum/thread/move', "id={$thread_info['thread_id']}")."'>Move</a></li>";
*/		
	
	$text .= "</ul></div>";
//	$text .= "</form>";	
	return $text;
}
	
	
function fpages($thread_info, $replies)
{
	global $forum;
	$tp = e107::getParser();

	$replies        = (int) $replies;
	$postsPerPage   = (int) $forum->prefs->get('postspage');
	
	$pages = ceil(($replies)/$postsPerPage);

	$thread_info['thread_sef'] = eHelper::title2sef($thread_info['thread_name'],'dashl');
	$urlparms = $thread_info;
	$text = '';

	if ($pages > 1)
	{
		if($pages > 6)
		{
			for($a = 0; $a <= 2; $a++)
			{
				$aa = $a + 1;
				$text .= $text ? ' ' : '';
			//	$urlparms['page'] = $aa;

			//	$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$title = $tp->lanVars(LAN_GOTOPAGEX, $aa);
				$url = e107::url('forum','topic',$urlparms).'&amp;p='.$aa;
				$opts[] = "<a data-toggle='tooltip' title=\"".$title."\" href='{$url}'>{$aa}</a>";
			}
			$text .= ' ... ';
			for($a = $pages-3; $a <= $pages-1; $a++)
			{
				$aa = $a + 1;
				$text .= $text ? ' ' : '';
			//	$urlparms['page'] = $aa;
			//	$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$title = $tp->lanVars(LAN_GOTOPAGEX, $aa);
				$url = e107::url('forum','topic',$urlparms).'&amp;p='.$aa;
				$opts[] = "<a data-toggle='tooltip' title=\"".$title."\" href='{$url}'>{$aa}</a>";
			}
		}
		else
		{

			for($a = 0; $a <= ($pages-1); $a++)
			{
				$aa = $a + 1;
				$text .= $text ? ' ' : '';
			//	$urlparms['page'] = $aa;
			//	$url = e107::getUrl()->create('forum/thread/view', $urlparms);
				$title = $tp->lanVars(LAN_GOTOPAGEX, $aa);
				$url = e107::url('forum','topic',$urlparms).'&amp;p='.$aa;
				$opts[] =  "<a data-toggle='tooltip' title=\"".$title."\" href='{$url}'>{$aa}</a>";
			}
		}

		if(deftrue('BOOTSTRAP'))
		{
			$text = "<ul class='pagination pagination-sm forum-viewforum-pagination'>
						<li>";
			
			$text .= implode("</li><li>",$opts); // ."</div>";	
			$text .= "</li></ul>";
		}
		else 
		{
			$text = implode(" ",$opts); // ."</div>";
		}
		
	}
	else
	{
		$text = '';
	}	
	
	return $text; 
}	

function newthreadjump($url)
{
	global $forum;
	$jumpList = $forum->forumGetAllowed('view');

	$text = '<div class="btn-group">
    <a href="'.$url.'" class="btn btn-primary">'.LAN_FORUM_1018.'</a>
    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu pull-right">
    ';
	
	foreach($jumpList as $key => $val)
	{
		$text .= '<li><a href="'.e107::url('forum','forum',$val).'">'.LAN_FORUM_1017.': '.$val['forum_name'].'</a></li>';
	}
	
	$text .= '
    </ul>
    </div>';
	
	return $text;
}
?>
