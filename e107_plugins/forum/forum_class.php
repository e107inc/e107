<?php
/*
* e107 website system
*
* Copyright (c) 2008-2014 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Forum class
*
*/

use e107\Database\SqlFragment;

// TODO LAN

/* Forum Header File */
if (!defined('e107_INIT')) { exit; }

e107::js('forum', 'js/forum.js', 'jquery', 5);
e107::css('forum','forum.css');

e107::lan('forum', "front", true);

if(!deftrue('BOOTSTRAP')) // test with 'jayya'
{
		$bcDefs = array(
			'FORLAN_11' => 'LAN_FORUM_0039',
			'FORLAN_12' => 'LAN_FORUM_0040',
			'FORLAN_13' => 'LAN_FORUM_0040',
			'FORLAN_14' => 'LAN_FORUM_0040',
			'FORLAN_15' => '',
			'FORLAN_16' => 'LAN_FORUM_1012',
			'FORLAN_17' => 'LAN_FORUM_1013',
			'FORLAN_18' => 'LAN_FORUM_1014',
			'LAN_435'   => 'LAN_DELETE',
			'LAN_401'   => 'LAN_FORUM_4011',
			'LAN_398'   => 'LAN_FORUM_4012',
			'LAN_399'   => 'LAN_FORUM_4013',
			'LAN_400'   => 'LAN_FORUM_4014',
			'LAN_402'   => 'LAN_FORUM_5019',
			'LAN_199'   => 'LAN_SEARCH',
			'LAN_397'   => 'LAN_FORUM_0030',
			'LAN_396'   => 'LAN_FORUM_1013',
			'LAN_392'   => 'LAN_FORUM_0070',
			'LAN_391'   => 'LAN_FORUM_4009',
			'LAN_400'   => 'LAN_EDIT',
			'LAN_401'   => 'LAN_FORUM_2041',
			'LAN_406'   => 'LAN_EDIT',
			'LAN_435'   => 'LAN_DELETE',
			'LAN_397'   => 'LAN_FORUM_2044',
			'LAN_398'   => 'LAN_FORUM_4007',
			'FORLAN_105'    => 'LAN_FORUM_3052',
			'LAN_408'   => 'LAN_FORUM_0007',
			'LAN_413'   => 'LAN_FORUM_2046',
			'FORLAN_10' => 'LAN_FORUM_1018',

		);

		e107::getLanguage()->bcDefs($bcDefs);
}

// include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum.php');
if(!defined('IMAGE_new') && !defined('IMAGE_e'))
{
	if (file_exists(THEME.'templates/forum/forum_icons_template.php')) // Preferred v2.x location.
	{
		require_once(THEME.'templates/forum/forum_icons_template.php');
	}
	elseif (file_exists(THEME.'forum/forum_icons_template.php'))
	{
		require_once(THEME.'forum/forum_icons_template.php');
	}
	elseif (file_exists(THEME.'forum_icons_template.php'))
	{
		require_once(THEME.'forum_icons_template.php');
	}
	else
	{
		require_once(e_PLUGIN.'forum/templates/forum_icons_template.php');
	}
}

class e107forum
{
//	var $fieldTypes = array();
	private $userViewed;
	private $permList = array();
	public $modArray, $prefs;

	/** @var array moderator rows keyed by userclass, for authorisation only */
	private $moderatorsByClass = array();
	private $forumData = array();

	public function __construct($update= false)
	{

		if (!empty($_POST['fjsubmit']) && !empty($_POST['forumjump']))
		{
			$url = e107::getParser()->filter($_POST['forumjump'],'url');
			e107::getRedirect()->go($_POST['forumjump']);
			exit;
		}

		$this->e107 = e107::getInstance();
		$this->userViewed = array();
		$this->modArray = array();
		
		if($update === false)
		{
			$this->loadPermList();
		}
		
		$this->prefs = e107::getPlugConfig('forum');
		if(!$this->prefs->get('postspage')) {
			$this->setDefaults();
		}
		
		$this->getForumData();
//		var_dump($this->prefs);

/*
		$this->fieldTypes['forum_post']['post_user'] 			= 'int';
		$this->fieldTypes['forum_post']['post_forum'] 			= 'int';
		$this->fieldTypes['forum_post']['post_datestamp'] 		= 'int';
		$this->fieldTypes['forum_post']['post_edit_datestamp']	= 'int';
		$this->fieldTypes['forum_post']['post_edit_user']		= 'int';
		$this->fieldTypes['forum_post']['post_thread'] 			= 'int';
		$this->fieldTypes['forum_post']['post_options'] 		= 'escape';
		$this->fieldTypes['forum_post']['post_attachments'] 	= 'escape';

		$this->fieldTypes['forum_thread']['thread_user'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_lastpost'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_lastuser'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_sticky'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_forum_id'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_active'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_datestamp']	= 'int';
		$this->fieldTypes['forum_thread']['thread_views'] 		= 'int';
		$this->fieldTypes['forum_thread']['thread_replies'] 	= 'int';
		$this->fieldTypes['forum_thread']['thread_options'] 	= 'escape';

		$this->fieldTypes['forum']['forum_lastpost_user']	 	= 'int';
*/
	}

	/**
	 * Translate the legacy '_NULL_' sentinel into a real null so values can be
	 * bound through the query builder while preserving the old array-form
	 * insert/update behaviour (which mapped '_NULL_' to SQL NULL).
	 *
	 * @param array $data column => value pairs
	 * @return array
	 */
	private function nullSentinels(array $data)
	{
		foreach($data as $k => $v)
		{
			if($v === '_NULL_')
			{
				$data[$k] = null;
			}
		}
		return $data;
	}

	/**
	 * Run an array-form UPDATE through the query builder: every value bound,
	 * '_NULL_' translated to SQL NULL, scoped by a single column = value WHERE.
	 *
	 * @param string $table logical table name
	 * @param array $data column => value pairs to set
	 * @param string $whereColumn column for the WHERE predicate
	 * @param mixed $whereValue bound value for the WHERE predicate
	 * @return int|bool affected rows, or false on error
	 */
	private function updateRow($table, array $data, $whereColumn, $whereValue)
	{
		$qb = e107::getDb()->createQueryBuilder()->update($table);
		foreach($this->nullSentinels($data) as $col => $val)
		{
			$qb->set($col, $val);
		}
		return $qb->where($whereColumn, $whereValue)->execute();
	}

	/**
	 * Grab the forum data up front to reduce LEFT JOIN usage. Currently only forum_id and forum_sef but may be expanded as needed.
	 */
	private function getForumData()
	{
		$data = e107::getDb()->createQueryBuilder()
			->select('forum_id', 'forum_sef', 'forum_class')->from('forum')
			->fetchAll(); // no ordering for better performance.

		$newData = array();
		foreach($data as $row)
		{
			$id = $row['forum_id'];
			$newData[$id ] = $row;
		}
		$this->forumData = $newData;
	}

	function getForumSef($threadInfo)
	{

		$forumId = !empty($threadInfo['post_forum']) ? $threadInfo['post_forum'] : $threadInfo['thread_forum_id'];

		if(!empty($this->forumData[$forumId]['forum_sef']))
		{
				$ret =  $this->forumData[$forumId]['forum_sef'];
		}
		else
		{
			$ret = null;
		}
	
		return $ret;

	}

	/**
	 * Get an array of the first alphabetical 50 usernames, user IDs, and the users' serialized user classes that match
	 * the forum's allowed viewers or a user class ID if the allowed viewers is a special user class except if the
	 * special user class is {@see e_UC_MAINADMIN} or {@see false} if the previous two possibilities are not encountered
	 *
	 * @deprecated v2.3.3 Due to the confusing usage, consider writing another method to get the list of members that
	 *                    can see the forum identified by its forum ID.
	 * @param $forumId int The ID from `e107_forum`.`forum_id`
	 * @param $type string Can only be "view"
	 * @return array|int|false When an array, the first 50 users, sorted alphabetically by username, that can view this
	 *                         forum, along with their user ID, username, and serialized user classes.
	 *                         When an int, the special user class ID as defined in {@see e107::set_constants()} except
	 *                         {@see e_UC_MAINADMIN}.
	 *                         When boolean false, this is an unhandled case probably due to the
	 *                         `e107_forum`.`forum_class` column missing from the table.
	 */
	function getForumClassMembers($forumId, $type='view')
	{

		$fieldTypes = array('view' => 'forum_class');
		$field = $fieldTypes[$type];

		if(isset($this->forumData[$forumId][$field]))
		{
			$class = $this->forumData[$forumId]['forum_class'];

			if($class == 0 || ($class > 250 && $class < 256))
			{
				return $class;
			}


			$qb = e107::getDb()->createQueryBuilder();
			return $qb->select('user_id', 'user_name', 'user_class')->from('user')
				->where($qb->expr()->findInSet('user_class', $class))
				->orWhere($qb->expr()->eq('user_class', $class)) // FIND_IN_SET(user_class, $class)
				->orderBy('user_name')->setMaxResults(50)
				->fetchAll();
		}

		return false;

	}


	/**
	 * @param $user int userid (if empty "anon" will be used)
	 * @param $create boolean creates the attachment folder if set to true
	 * @return string forum attachment path for specific user
	 */
	function getAttachmentPath($user, $create = FALSE)
	{
		$user = intval($user);
		$tp = e107::getParser();
		require_once(e_PLUGIN.'forum/forum_attachments.php');
		$paths = forum_attachments::paths();
		$baseDir = $paths[0];
		$baseDir .= ($user) ? "user_". $tp->leadingZeros($user, 6) : "anon";
		
		if($create == TRUE && !is_dir($baseDir))
		{
			mkdir($baseDir, 0755, TRUE); // recursively	
		}
		
		$baseDir .= "/";

		return $baseDir;
	}

    function sendFile($data)
    {
        $sql 		= e107::getDb();
        $post_id  	= intval($data['id']); // forum (post) id
        $file_id 	= intval($data['dl']); // file id
        $forum_id 	= $sql->createQueryBuilder()
            ->select('post_forum')->from('forum_post')
            ->where('post_id', $post_id)
            ->fetchOne();

        // Check if user is allowed to download this file (has 'view' permissions to forum)
    	if(!$this->checkPerm($forum_id, 'view'))
		{
			if(E107_DEBUG_LEVEL > 0)
			{
				echo "You don't have 'view' access to forum-id: : ".$forum_id;
				print_a($this->permList);
				return;
			}

			$url = e107::url('forum','index','full');
			e107::getRedirect()->go($url);
		//	header('Location:'.e107::getUrl()->create('forum/forum/main')); // FIXME needs proper redirect and 403 header
			exit;
		}

        $array 	= $sql->createQueryBuilder()
            ->select('post_user', 'post_attachments')->from('forum_post')
            ->where('post_id', $post_id)
            ->fetchRow();
        $attach = e107::unserialize($array['post_attachments']);
        $entry  = isset($attach['file'][$file_id]) ? $attach['file'][$file_id] : '';

        $filename = is_array($entry) ? varset($entry['file'], '') : $entry;
        $filename = basename((string) $filename);

        $file 	= $this->getAttachmentPath($array['post_user']).$filename;

        // Check if file exists. Send file for download if it does, return 404 error code when file does not exist.
 		if($filename !== '' && is_file($file))
 		{
 		   e107::getFile()->send($file, array('roots' => forum_attachments::paths()));
 		}
 		else
 		{
 		    if(E107_DEBUG_LEVEL > 0)
	        {
	            echo "Couldn't find file: ".$file;
	            print_a($attach);
	            return;
	        }

		    $url = e107::url('forum','index','full');
		    e107::getRedirect()->go($url);
		//	header('Location:'.e107::getUrl()->create('forum/forum/main', TRUE, 404)); // FIXME needs proper redirect and 404 header
			exit;
 		}
    }


	/**
	 * Where a thread lives, and whether it is still open.
	 *
	 * The forum a reply belongs to is a property of the thread, so it is read
	 * back from the thread rather than taken from the request. The request used
	 * to carry both, unrelated to each other, which let a reply be authorised
	 * against one forum and written into a thread in another.
	 *
	 * @param int $threadId
	 * @return array|false thread_forum_id and thread_active, or false if there is no such thread
	 */
	private function threadContext($threadId)
	{
		if(empty($threadId))
		{
			return false;
		}

		$row = e107::getDb()->createQueryBuilder()
			->select('thread_id', 'thread_forum_id', 'thread_active')
			->from('forum_thread')
			->where('thread_id', (int) $threadId)
			->fetchRow();

		return $row ? $row : false;
	}


	/**
	 * Handle the Ajax quick-reply.
	 */
	function ajaxQuickReply()
	{
		$tp = e107::getParser();

		// An absent token is deliberately left absent. Filling it in with an empty
		// string satisfies isset() and so lands on the invalid-token branch,
		// which refuses the reply outright rather than deferring to the mode the
		// operator chose.

		if(!e107::getSession()->check(false))
		{
			// Invalid token.
			exit;
		}

		// The thread decides the forum, and the forum decides the permission.
		//
		// This used to ask checkPerm() about $_POST['post'] and then write
		// $_POST['thread'] into post_thread, with nothing tying the two ids
		// together: naming a forum you may post in bought you a reply in any
		// thread on the site, including one in a forum you are redirected away
		// from. thread_active went unread as well, so a closed thread took
		// replies through this route while the ordinary form (forum_post.php,
		// checkPerms()) refused them.
		$thread = $this->threadContext(varset($_POST['thread']));

		if(!$thread || !$this->checkPerm($thread['thread_forum_id'], 'post'))
		{
			exit;
		}

		if(empty($thread['thread_active']) && !$this->canModerateThread($thread['thread_id']))
		{
			exit;
		}

		// Always an answer, and always one that matches what happened.
		//
		// The response used to be built only on the way through the success
		// path, so an empty reply produced neither status nor msg and the page
		// said nothing at all, and a duplicate produced 'ok' with postAdd()'s
		// -1 sentinel handed back as the post id: the reply slid into the page
		// and was gone on the next refresh. forum_post.php has reported the
		// duplicate properly since forever.
		$ret = array('status' => 'error', 'msg' => LAN_FORUM_8027, 'html' => false);

		if(varset($_POST['action']) == 'quickreply' && !vartrue($_POST['text']))
		{
			$ret['msg'] = LAN_FORUM_3007; // left required field(s) blank
		}
		elseif(varset($_POST['action']) == 'quickreply')
		{

			$postInfo = array();
			$postInfo['post_ip'] = e107::getIPHandler()->getIP(false);

			if(USER)
			{
				$postInfo['post_user'] = USERID;
			}
			else
			{
				$postInfo['post_user_anon'] = varset($_POST['anonname'], '');
			}

			$postInfo['post_entry'] = $_POST['text'];
			$postInfo['post_forum'] = (int) $thread['thread_forum_id'];
			$postInfo['post_datestamp'] = time();
			$postInfo['post_thread'] = (int) $thread['thread_id'];

			$postId = $this->postAdd($postInfo); // save it.

			if($postId === -1)
			{
				$ret['msg'] = LAN_FORUM_3006; // duplicate post
			}
			elseif(empty($postId))
			{
				$ret['msg'] = LAN_FORUM_8018; // there was a problem
			}
			else
			{
				$postInfo['post_id'] = $postId;
				$postInfo['user_name'] = defset('USERNAME');
				$postInfo['user_email'] = defset('USEREMAIL');
				$postInfo['user_image'] = defset('USERIMAGE');
				$postInfo['user_signature'] = defset('USERSIGNATURE');

				if(varset($_POST['insert']) == 1)
				{
					$tmpl = e107::getTemplate('forum', 'forum_viewtopic', 'replies');
					$sc = e107::getScBatch('view', 'forum');
					$sc->setScVar('postInfo', $postInfo);
					$ret['html'] = $tp->parseTemplate($tmpl, true, $sc) . "\n";
				}

				$ret['status'] = 'ok';
				$ret['msg'] = LAN_FORUM_3047;
			}
		}

		// The token is deliberately not rotated here.
		//
		// e107::getSession()->reset() was added alongside this method in
		// 29f74508c2967f6e869dc01bb0bf0bf70a755657 ("Forum quick-reply fix.",
		// June 2013) with no stated reason, and e_core_session::reset() was
		// created in that same commit for this one call site. It has no other
		// caller in core to this day.
		//
		// It buys nothing. The form token is a session-lifetime secret shared by
		// every form on every page, not a one-time nonce, so rotating it after
		// one reply prevents no replay: the new value is just as valid for
		// everything else. Session fixation is answered by regenerating the
		// session id, which happens in shutdown() at the higher security levels.
		// Rotation policy lives there and is level-driven; at the default level
		// e107 deliberately keeps the token stable, so this was an outlier rather
		// than an application of any rule. It also bypassed e_TOKEN_FREEZE, the
		// documented way for other code to ask that regeneration not happen.
		//
		// What it cost is everything else on the page. The rotation invalidated
		// the <meta name="e-token">, every injected form, the track button and
		// the moderator links, none of which the reply's response can reach, so a
		// visitor who stayed on the topic page had every later write refused. A
		// write-back of the new token was added in 2016
		// (391f7c2148a5a19f38a51eec50728cd31ba434a3, "Fixed: bug when you use
		// Quick Reply twice") but it only ever updated the clicked button, and
		// only when a reply actually succeeded. An empty reply rotated the token
		// and returned nothing to write back, which bricked the page outright.
		//
		// $ret is now always an array and always carries the current token, so a
		// customised forum.js that reads e_token keeps working and simply writes
		// back the value it already had.
		$ret['e_token'] = e107::getSession()->getFormToken();

		echo $tp->toJSON($ret);
		exit;
	}


	/**
	* Process Tracking Enable/disable
	*/
	public function ajaxTrack()
	{
		$ret = array();
		$ret['status'] 	= 'error';

		$threadID = intval(varset($_POST['thread']));

		if(!USER || empty($threadID))
		{
			exit;
		}

		// Subscribing is a way of reading. trackEmail() posts every reply in the
		// thread out to whoever is in forum_track, and asks nothing about the
		// forum it came from, so the only check standing between a member and
		// the contents of a forum closed to them is this one.
		$thread = $this->threadContext($threadID);

		if(!$thread || !$this->checkPerm($thread['thread_forum_id'], 'view'))
		{
			exit;
		}

		$trackByEmail = (bool) $this->prefs->get('trackemail', true);

		$sql = e107::getDb();

		if($sql->createQueryBuilder()->from('forum_track')
			->where('track_userid', (int) USERID)->where('track_thread', (int) $threadID)
			->count())
		{
			if($this->track('del', USERID, $threadID))
			{
				$ret['html'] = IMAGE_untrack;
				$ret['msg'] = ($trackByEmail) ? LAN_FORUM_8004 : LAN_FORUM_8006 ; // "Email notifications for this topic are now turned off. ";
				$ret['status'] = 'info';
			}
			else
			{
				$ret['msg'] = LAN_FORUM_8017;  
				$ret['status'] = 'error';
			}

		}
		else
		{
			if($this->track('add', USERID, $threadID))
			{
				$ret['msg'] = ($trackByEmail) ? LAN_FORUM_8003 : LAN_FORUM_8005; // "Email notifications for this topic are now turned on. ";
				$ret['html']  = IMAGE_track;
				$ret['status'] = 'ok';
			}
			else
			{
				$ret['html'] = IMAGE_untrack;
				$ret['msg'] = LAN_FORUM_8018;  
				$ret['status'] = 'error';
			}


		}

		echo json_encode($ret);

		exit;

	}


	/**
	 * Allow a user to delete their own post, if it is the last post in the thread.
	 */
	function usersLastPostDeletion()
	{
		$ret = array('hide' => false, 'msg' => LAN_FORUM_7008, 'status' => 'error');

		$postId = (isset($_POST['post']) && is_numeric($_POST['post'])) ? (int) $_POST['post'] : 0;
		$actionAllowed = $postId && varset($_POST['action']) === 'deletepost'
			&& $this->userMayDeleteOwnPost($postId);

		if ($actionAllowed)
		{
			if ($this->postDelete($postId))
			{
				$ret['msg'] 	= LAN_FORUM_8021.' #'.$postId;
				$ret['hide'] 	= true;
				$ret['status'] 	= 'ok';
			}
			else
			{
				$ret['msg'] 	= LAN_FORUM_8022." #".$postId;
				$ret['status'] 	= 'error';
			}
		}
		echo json_encode($ret);
		exit();
	}


	/**
	 * The rule this endpoint's name, its docblock and the control that offers it
	 * all describe: your own post, the last one in the thread, not the one that
	 * started it, and the thread still open.
	 *
	 * None of it was enforced. The whole ownership test was `USERID ==
	 * $row['post_user']`, and USERID is 0 for a caller with no account while an
	 * anonymous post stores post_user 0, so an unauthenticated visitor owned
	 * every anonymous post on the site and could delete any of them, in forums
	 * they could not even read. Carrying no cookie, such a request is not
	 * challenged for a token either. A member, meanwhile, could delete any post
	 * they had ever written, including one that opened a thread, which left the
	 * thread row behind with no first post.
	 *
	 * @param int $postId
	 * @return bool
	 */
	private function userMayDeleteOwnPost($postId)
	{
		if(!deftrue('USER'))
		{
			return false;
		}

		$qb = e107::getDb()->createQueryBuilder();
		$row = $qb
			->select('fp.post_user', 'fp.post_thread', 'ft.thread_active')
			->from('forum_post', 'fp')
			->innerJoin('forum_thread', 'ft', $qb->expr()->compareColumns('fp.post_thread', 'ft.thread_id'))
			->where('fp.post_id', (int) $postId)
			->fetchRow();

		if(!$row || empty($row['post_user']) || (int) $row['post_user'] !== (int) USERID)
		{
			return false;
		}

		if(empty($row['thread_active']))
		{
			return false;
		}

		$threadId = (int) $row['post_thread'];

		$later = (int) e107::getDb()->createQueryBuilder()
			->from('forum_post')
			->where('post_thread', $threadId)
			->where('post_id', '>', (int) $postId)
			->count();

		$earlier = (int) e107::getDb()->createQueryBuilder()
			->from('forum_post')
			->where('post_thread', $threadId)
			->where('post_id', '<', (int) $postId)
			->count();

		return $later === 0 && $earlier > 0;
	}


	/**
	 * get user ids with moderator permissions for the given $postId
	 * @param $postId id of a forum post
	 * @return array an array with user ids how have moderator permissions for the $postId
	 */
	public function getModeratorUserIdsByPostId($postId)
	{
		$qb = e107::getDb()->createQueryBuilder();
		$row = $qb
			->select('f.forum_moderators')->from('forum', 'f')
			->innerJoin('forum_thread', 'ft', $qb->expr()->compareColumns('f.forum_id', 'ft.thread_forum_id'))
			->innerJoin('forum_post', 'fp', $qb->expr()->compareColumns('ft.thread_id', 'fp.post_thread'))
			->where('fp.post_id', $postId)
			->fetchRow();
		if ($row)
		{
			return array_keys($this->moderatorsOfClass($row['forum_moderators']));
		}
		return array();
	}


	/**
	 * Moderators of one userclass, for deciding permission.
	 *
	 * Kept apart from forumGetMods(), which memoises a single list on the
	 * instance whatever class it is asked for. Templates read that memo to
	 * render a forum's moderator list, so it cannot simply be keyed without
	 * changing what they show. The page also primes it before any write is
	 * authorised, which meant every permission question in the request was
	 * answered for the forum named in the URL rather than for the thread or
	 * post being acted on.
	 *
	 * @param int|string $uclass value of forum.forum_moderators
	 * @return array moderator rows keyed by user id
	 */
	private function moderatorsOfClass($uclass)
	{
		$key = (string) $uclass;

		if(isset($this->moderatorsByClass[$key]))
		{
			return $this->moderatorsByClass[$key];
		}

		$mods = array();

		if($uclass == e_UC_ADMIN || trim((string) $uclass) === '')
		{
			$rows = e107::getDb()->createQueryBuilder()
				->select('user_id', 'user_name')->from('user')
				->where('user_admin', 1)->orderBy('user_name', 'ASC')
				->fetchAll();

			foreach($rows as $row)
			{
				$mods[$row['user_id']] = $row;
			}
		}
		else
		{
			$mods = e107::getUserClass()->getUsersInClass($uclass, 'user_name', true);
		}

		return $this->moderatorsByClass[$key] = $mods;
	}


	/**
	 * @param array $moderatorUserIds
	 * @return bool
	 */
	private function actorModerates(array $moderatorUserIds)
	{
		if(deftrue('USER') && in_array(USERID, $moderatorUserIds))
		{
			return true;
		}

		return (bool) getperms('0');
	}


	/**
	 * @param int $threadId
	 * @return bool
	 */
	public function canModerateThread($threadId)
	{
		return $this->actorModerates($this->getModeratorUserIdsByThreadId($threadId));
	}


	/**
	 * @param int $postId
	 * @return bool
	 */
	public function canModeratePost($postId)
	{
		return $this->actorModerates($this->getModeratorUserIdsByPostId($postId));
	}


	/**
	 * @param int $forumId
	 * @return bool
	 */
	public function canModerateForum($forumId)
	{
		return $this->actorModerates($this->getModeratorUserIdsByForumId($forumId));
	}


	/**
	 * get user ids with moderator permissions for the given $threadId
	 * @param $threadId id of a forum thread
	 * @return array an array with user ids how have moderator permissions for the $threadId
	 */
	public function getModeratorUserIdsByThreadId($threadId)
	{
		// get moderator-class for the thread to check permissions of the user
		$qb = e107::getDb()->createQueryBuilder();
		$row = $qb
			->select('f.forum_moderators')->from('forum', 'f')
			->innerJoin('forum_thread', 'ft', $qb->expr()->compareColumns('f.forum_id', 'ft.thread_forum_id'))
			->where('ft.thread_id', $threadId)
			->fetchRow();
		if ($row)
		{
			return array_keys($this->moderatorsOfClass($row['forum_moderators']));
		}
		return array();
	}


	/**
	 * get user ids with moderator permissions for the given $forumId
	 * @param int $forumId id of a forum
	 * @return array with user ids how have moderator permissions for the $forumId
	 */
	public function getModeratorUserIdsByForumId($forumId)
	{
		// get moderator-class for the thread to check permissions of the user
		$row = e107::getDb()->createQueryBuilder()
			->select('f.forum_moderators')->from('forum', 'f')
			->where('f.forum_id', $forumId)
			->fetchRow();
		if ($row)
		{
			return array_keys($this->moderatorsOfClass($row['forum_moderators']));
		}
		return array();
	}


	/**
	 * Whether an action posted to a forum page is one ajaxModerate() owns.
	 *
	 * The page used to call ajaxModerate() for any AJAX request at all as long
	 * as the viewer was a moderator, and ajaxModerate() always ends by printing
	 * JSON and exiting. So a moderator's poll vote, rating or plugin widget on a
	 * forum page was swallowed and answered with a forum error, while an
	 * ordinary member's went through: a fault that reads as a permissions
	 * problem and is not one.
	 *
	 * @param string $action
	 * @return bool
	 */
	public static function isModerationAction($action)
	{
		return in_array($action, array('delete', 'lock', 'unlock', 'stick', 'unstick', 'deletepost'), true);
	}


	public function ajaxModerate()
	{
		$ret = array('hide' => false, 'msg' => 'unknown', 'status' => 'error');

		$action   = varset($_POST['action'], '');
		$threadId = (isset($_POST['thread']) && is_numeric($_POST['thread'])) ? (int) $_POST['thread'] : 0;
		$postId   = (isset($_POST['post']) && is_numeric($_POST['post'])) ? (int) $_POST['post'] : 0;

		/* Authorise against the object the action changes, and nothing else.
		 * This used to derive permission from whichever of the two ids arrived
		 * last and then act on the thread regardless, so a request naming a post
		 * in a forum you moderate could delete, lock or stick a thread in one you
		 * do not. Requiring the id here also means the cases below can no longer
		 * be reached without one. */
		if(in_array($action, array('delete', 'lock', 'unlock', 'stick', 'unstick'), true))
		{
			$targetId = $threadId;
			$permitted = $threadId && $this->canModerateThread($threadId);
		}
		elseif($action === 'deletepost')
		{
			$targetId = $postId;
			$permitted = $postId && $this->canModeratePost($postId);
		}
		else
		{
			$ret['msg'] = LAN_FORUM_8027;

			echo json_encode($ret);

			exit();
		}

		if(!$targetId)
		{
			$ret['msg'] = LAN_FORUM_7008;

			echo json_encode($ret);

			exit();
		}

		if(!$permitted)
		{
			$ret['msg'] 	= LAN_FORUM_8030;
			$ret['hide'] 	= false;
			$ret['status'] 	= 'error';
		}
		else
		{
			switch ($action)
			{
				case 'delete':
					if($this->threadDelete($threadId))
					{
						$ret['msg'] 	= LAN_FORUM_8020.' #'.$threadId;
						$ret['hide'] 	= true; 
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] 	= LAN_FORUM_8019;
						$ret['status'] 	= 'error';	
					}
				break;
				
				case 'deletepost':
					if($this->postDelete($postId))
					{
						$ret['msg'] 	= LAN_FORUM_8021.' #'.$postId;
						$ret['hide'] 	= true; 
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] 	= LAN_FORUM_8022." #".$postId;
						$ret['status'] 	= 'error';	
					}
				break;
				
				case 'lock':
					if(e107::getDb()->createQueryBuilder()->update('forum_thread')->set('thread_active', 0)->where('thread_id', (int) $threadId)->execute())
					{
						$ret['msg'] 	= LAN_FORUM_CLOSE;
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] 	= LAN_FORUM_8023;
						$ret['status'] 	= 'error';	
					}
				break;

				case 'unlock':
					if(e107::getDb()->createQueryBuilder()->update('forum_thread')->set('thread_active', 1)->where('thread_id', (int) $threadId)->execute())
					{
						$ret['msg'] = LAN_FORUM_OPEN;
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] = LAN_FORUM_8024;
						$ret['status'] 	= 'error';	
					}
				break;

				case 'stick':
					if(e107::getDb()->createQueryBuilder()->update('forum_thread')->set('thread_sticky', 1)->where('thread_id', (int) $threadId)->execute())
					{
						$ret['msg'] = LAN_FORUM_STICK;
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] = LAN_FORUM_8025;
						$ret['status'] 	= 'error';	
					}
				break;

				case 'unstick':
					if(e107::getDb()->createQueryBuilder()->update('forum_thread')->set('thread_sticky', 0)->where('thread_id', (int) $threadId)->execute())
					{
						$ret['msg'] = LAN_FORUM_UNSTICK;
						$ret['status'] 	= 'ok';		
					}
					else
					{
						$ret['msg'] = LAN_FORUM_8026;
						$ret['status'] 	= 'error';	
					}
				break;	
				
				
				
						
				
				default:
					$ret['status'] 	= 'error';	
					$ret['msg'] 	= LAN_FORUM_8027;
				break;
			}
		}
		echo json_encode($ret);
			
		exit();
	}
				
				
			
			
		
		
		
	

	private function loadPermList()
	{
		$this->permList = self::permList();
	}


	/**
	 * The permission lists for whoever holds this request, keyed by type.
	 *
	 * Static, and so answerable without constructing the controller: a listing
	 * that only wants to know which forums it may name has no business entering
	 * the forum's request handling to find out.
	 *
	 * Cached under forum_perms, keyed on the language and the caller's
	 * userclass list. The forum admin's create, update and delete hooks clear
	 * it; nothing else invalidates it.
	 *
	 * @return array permission type => forum ids, plus a "<type>_list" string
	 */
	public static function permList()
	{
		if($tmp = e107::getCache()->setMD5(e_LANGUAGE.USERCLASS_LIST)->retrieve('forum_perms'))
		{
			e107::getDebug()->log("Using Permlist cache: True");

			return e107::unserialize($tmp);
		}

		e107::getDebug()->log("Using Permlist cache: False");
		$permList = self::buildPermList();
		e107::getCache()->setMD5(e_LANGUAGE.USERCLASS_LIST)->set('forum_perms', e107::serialize($permList, false));

		return $permList;
	}


	/**
	 * The forum ids checkPerm($id, $type) accepts for this caller.
	 *
	 * The list holds the id of every forum whose own class column admits the
	 * caller and whose parent row's column admits them too, plus the ids of
	 * those parent rows, so a category containing at least one permitted forum
	 * is a member of the list in its own right.
	 *
	 * Two levels only. forum_sub, the third, is not consulted here and is not
	 * consulted by forum_viewforum.php either: a sub-forum's forum_parent names
	 * its category, so it is the category and not the parent forum that is
	 * asked. Feeds built on this list are therefore exactly as permissive as
	 * the site route, which is the property that matters.
	 *
	 * @param string $type view, post or thread
	 * @return int[]
	 */
	public static function visibleForumIds($type = 'view')
	{
		$permList = self::permList();

		return isset($permList[$type]) ? $permList[$type] : array();
	}



	public function getForumPermList($what = null)
	{

		if(null !== $what)
		{
			return (isset($this->permList[$what]) ? $this->permList[$what] : array());
		}

		return $this->permList;
	}



	private function setDefaults()
	{
		$this->prefs->set('show_topics', '1');
		$this->prefs->set('postfix', '[more...]');
		$this->prefs->set('poll', '255');
		$this->prefs->set('popular', '10');
		$this->prefs->set('track', '1');
		$this->prefs->set('eprefix', '[forum]');
		$this->prefs->set('enclose', '1');
		$this->prefs->set('title', 'Forums');
		$this->prefs->set('postspage', '10');
		$this->prefs->set('threadspage', '25');
		$this->prefs->set('highlightsticky', '1');
	}



	/**
	 * @return array permission type => forum ids, plus a "<type>_list" string
	 */
	private static function buildPermList()
	{
		$permList = array();

		// Static map of permission key => forum class column (identifiers are fixed literals).
		$classColumns = array(
			'view'   => 'forum_class',
			'post'   => 'forum_postclass',
			'thread' => 'forum_threadclass',
		);

		$classList = explode(',', USERCLASS_LIST);

		// The predicate itself lives in forum_attachments.php, which thumb.php
		// can load and this file cannot. One rule, asked from both sides.
		require_once(e_PLUGIN.'forum/forum_attachments.php');

		foreach($classColumns as $key => $col)
		{
			$rows = forum_attachments::readableForumRows($classList, $col);

			$tmp = array();
			foreach($rows as $row)
			{
				$tmp[$row['forum_id']] = 1;
				$tmp[$row['forum_parent']] = 1;
			}
			ksort($tmp);
			$permList[$key] = array_keys($tmp);
			$permList[$key.'_list'] = implode(',', array_keys($tmp));
		}

		return $permList;
	}

	
	
	function checkPerm($forumId, $type='view')
	{
	//	print_a( $this->permList[$type]);
		if(empty($this->permList[$type]))
		{
			return false;
		}

		return (in_array($forumId, $this->permList[$type]));
	}


	/**
	 * "The thread sits in a forum the caller may read", as a WHERE fragment.
	 *
	 * For queries that are still built by concatenation. A query builder should
	 * pass visibleForumIds() to whereIn() instead, which binds the ids and
	 * compiles the same "nothing is readable" case to 1=0.
	 *
	 * @param string $alias the forum_thread alias in the query being built
	 * @return string a WHERE fragment; "1=0" when the caller may read nothing
	 */
	public static function threadVisibleSql($alias = 't')
	{
		$visible = self::visibleForumIds('view');

		if(empty($visible))
		{
			return '1=0';
		}

		return $alias.'.thread_forum_id IN ('.implode(',', array_map('intval', $visible)).')';
	}


	function threadViewed($threadId)
	{
		$e107 = e107::getInstance();
		if(!$this->userViewed)
		{
			if(isset($e107->currentUser['user_plugin_forum_viewed']))
			{
				$this->userViewed = explode(',', $e107->currentUser['user_plugin_forum_viewed']);
			}
		}
		return (is_array($this->userViewed) && in_array($threadId, $this->userViewed));
	}

	
	
	function getTrackedThreadList($id, $retType = 'array')
	{
		$sql = e107::getDb();

		$id = (int)$id;
		$rows = $sql->createQueryBuilder()
			->select('track_thread')->from('forum_track')
			->where('track_userid', $id)
			->fetchAll();
		if($rows)
		{
			$ret = array();

			foreach($rows as $row)
			{
				$ret[] = $row['track_thread'];
			}

			return ($retType == 'array' ? $ret : implode(',', $ret));
		}
		return false;
	}


	function isDuplicatePost($postInfo)
	{

		$sql = e107::getDb();
		$tp = e107::getParser();

		$post = $tp->toDB($postInfo['post_entry']);

		$found = $sql->createQueryBuilder()
			->select('post_id')->from('forum_post')
			->where('post_forum', intval($postInfo['post_forum']))
			->where('post_entry', $post)
			->where('post_user', (int) USERID)
			->setMaxResults(1)
			->fetchOne();
		if($found !== null)
		{
			return true;
		}

		return false;

	}




	/*
	 * Add a post to the db.
	 *
	 * If threadinfo is given, then we're adding a new thread.
	 * We must get thread_id to provide to postInfo after insertion
	*/
	function postAdd($postInfo, $updateThread = true, $updateForum = true)
	{
//		var_dump($postInfo);

		if($this->isDuplicatePost($postInfo)==true)
		{
			return -1;
		}



		$addUserPostCount = true;
		$result = false;

		$sql = e107::getDb();
		$info = array();
		$tp = e107::getParser();

//		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_post'];

		$postInfo['post_entry'] = $tp->toDB($postInfo['post_entry']);

		$info['data'] = $postInfo;
		$postId = $sql->createQueryBuilder()->insert('forum_post')->insertGetId($this->nullSentinels($info['data']));

		$info['data']['post_id'] = $postId; // Append last inserted ID to data array for passing it to event callbacks.


		$triggerData = $info['data'];
	  	e107::getEvent()->trigger('user_forum_post_created', $triggerData);

	  	ob_start(); // precaution so json doesn't break.
		$this->trackEmail($info['data']);
		ob_end_clean();

		$forumInfo = array();

		if($postId && $updateThread)
		{
			$threadInfo = array();
			if(varset($postInfo['post_user']))
			{
				$threadInfo['thread_lastuser'] = $postInfo['post_user'];
				$threadInfo['thread_lastuser_anon'] = '_NULL_';
				$forumInfo['forum_lastpost_user'] = $postInfo['post_user'];
				$forumInfo['forum_lastpost_user_anon'] = '_NULL_';
			}
			else
			{
				$threadInfo['thread_lastuser'] = 0;
				$threadInfo['thread_lastuser_anon'] = $postInfo['post_user_anon'];
				$forumInfo['forum_lastpost_user'] = 0;
				$forumInfo['forum_lastpost_user_anon'] = $postInfo['post_user_anon'];
			}

			$threadInfo['thread_lastpost'] = !empty($postInfo['post_edit_datestamp']) ? $postInfo['post_edit_datestamp'] : $postInfo['post_datestamp'];

			if(!empty($postInfo['post_edit_user']))
			{
				$threadInfo['thread_lastuser'] = $postInfo['post_edit_user'];
			}


			$threadInfo['thread_total_replies'] = 'thread_total_replies + 1';

			$info = array();
			$info['data'] = $threadInfo;
			$info['WHERE'] = 'thread_id = '.$postInfo['post_thread'];

			$qb = $sql->createQueryBuilder()->update('forum_thread');
			foreach($this->nullSentinels($threadInfo) as $col => $val)
			{
				if($col === 'thread_total_replies')
				{
					$qb->increment('thread_total_replies');
				}
				else
				{
					$qb->set($col, $val);
				}
			}
			$result = $qb->where('thread_id', (int) $postInfo['post_thread'])->execute();

			e107::getMessage()->addDebug("Updating Thread with: ".print_a($info,true));

			$triggerData = $info['data'];
			$triggerData['thread_id'] = $postInfo['post_thread'];
		  	e107::getEvent()->trigger('user_forum_topic_updated', $triggerData);
		}

		if(($result || !$updateThread) && $updateForum)
		{
			if(varset($postInfo['post_user']))
			{
				$forumInfo['forum_lastpost_user'] = $postInfo['post_user'];
				$forumInfo['forum_lastpost_user_anon'] = '_NULL_';
			}
			else
			{
				$forumInfo['forum_lastpost_user'] = 0;
				$forumInfo['forum_lastpost_user_anon'] = $postInfo['post_user_anon'];
			}

			$info = array();
			//If we update the thread, then we assume it was a reply, otherwise we've added a reply only.
//			$info['_FIELD_TYPES'] = $this->fieldTypes['forum'];
			if($updateThread)
			{
				$forumInfo['forum_replies'] = 'forum_replies+1';
				$info['_FIELD_TYPES']['forum_replies'] = 'cmd';
			}
			else
			{
				$forumInfo['forum_threads'] = 'forum_threads+1';
				$info['_FIELD_TYPES']['forum_threads'] = 'cmd';
			}
			$info['data'] = $forumInfo;
			$info['data']['forum_lastpost_info'] = $postInfo['post_datestamp'].'.'.$postInfo['post_thread'];
			$info['WHERE'] = 'forum_id = '.$postInfo['post_forum'];

			$qb = $sql->createQueryBuilder()->update('forum');
			foreach($this->nullSentinels($info['data']) as $col => $val)
			{
				if($col === 'forum_replies' || $col === 'forum_threads')
				{
					$qb->increment($col);
				}
				else
				{
					$qb->set($col, $val);
				}
			}
			$result = $qb->where('forum_id', (int) $postInfo['post_forum'])->execute();
		}

		if($result && USER && $addUserPostCount)
		{
			// ON DUPLICATE KEY UPDATE with a column-referencing expression (IFNULL(...) + 1)
			// is not expressible by the query builder; use a bound execute().
			$result = e107::getDb()->execute(
				'INSERT INTO `#user_extended` (user_extended_id, user_plugin_forum_posts)
				VALUES (:uid, 1)
				ON DUPLICATE KEY UPDATE user_plugin_forum_posts = IFNULL(user_plugin_forum_posts, 0) + 1',
				array('uid' => (int) USERID)
			);
		}


		$this->clearReadThreads($postInfo['post_thread']);

		return $postId;
	}

	/**
	 * Remove threadID from the 'viewed list' list of other users.
	 * @param $threadId
	 * @return false|void
	 */
	private function clearReadThreads($threadId)
	{
		if(empty($threadId))
		{
			return false;
		}

		$threadId = intval($threadId);

		// Vendor functions (TRIM/REPLACE/CONCAT/FIND_IN_SET) are not expressible by the
		// query builder; use a bound execute(). $threadId is bound as :tid.
		e107::getDb()->execute(
			"UPDATE `#user_extended`
			SET
			user_plugin_forum_viewed = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_plugin_forum_viewed, ','), CONCAT(',', :tid, ','), ','))
			WHERE
			FIND_IN_SET(:tid, user_plugin_forum_viewed)",
			array('tid' => $threadId)
		);

	}



	function threadAdd($threadInfo, $postInfo)
	{

		$info = array();
//		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];

	//	$threadInfo['thread_sef'] = eHelper::title2sef($threadInfo['thread_name'],'dashl');

		$info['data'] = $threadInfo;


		if($newThreadId = e107::getDb()->createQueryBuilder()->insert('forum_thread')->insertGetId($this->nullSentinels($info['data'])))
		{
			if($postInfo !== false)
			{
				$postInfo['post_thread'] = $newThreadId;

				if(!$newPostId = $this->postAdd($postInfo, false))
				{
					e107::getMessage()->addDebug("There was a problem: ".print_a($postInfo,true));
				}
			}
			else
			{
				$newPostId = 0;
			}

			$this->threadMarkAsRead($newThreadId);
			$threadInfo['thread_sef'] = $this->getThreadSef($threadInfo);

			$triggerData                = $info['data'];
			$triggerData['thread_id']   = $newThreadId;
			$triggerData['thread_sef']  = $threadInfo['thread_sef'];
			$triggerData['post_id']     = $newPostId;


			if (e107::getDb()->createQueryBuilder()->from('forum_post')->where('post_user', (int) USERID)->count('post_id') > 1)
			{
				e107::getEvent()->trigger('user_forum_topic_created', $triggerData);
			}
			else
			{
				e107::getEvent()->trigger('user_forum_topic_created_probationary', $triggerData);
			}

			return array('postid' => $newPostId, 'threadid' => $newThreadId, 'threadsef'=>$threadInfo['thread_sef']);
		}
		return false;
	}


	function getThreadSef($threadInfo)
	{
		return eHelper::title2sef($threadInfo['thread_name'],'dashl');
	}



	function threadMove($threadId, $newForumId, $threadTitle= '', $titleType=0)
	{
		$sql = e107::getDb();
		// IDs are SQL-injection-sensitive integer columns; enforce int regardless of caller.
		$threadId   = (int) $threadId;
		$newForumId = (int) $newForumId;
		$threadInfo = $this->threadGet($threadId);
		$oldForumId = (int) $threadInfo['thread_forum_id'];

		//Move thread to new forum, changing thread title if needed
		$qb = $sql->createQueryBuilder()->update('forum_thread')->set('thread_forum_id', $newForumId);
		if(!empty($threadTitle))
		{
			// toDB() is the storage transform for thread_name; the value is bound.
			$threadTitle = e107::getParser()->toDB($threadTitle);

			if($titleType == 0)
			{
				//prepend to existing title
				$qb->setExpression('thread_name', $qb->raw('CONCAT('.$qb->createNamedParameter($threadTitle.' ').', thread_name)'));
			}
			else
			{
				//Replace title
				$qb->set('thread_name', $threadTitle); // , thread_sef='".eHelper::title2sef($threadTitle,'dashl')."' ";
			}
		}
		$qb->where('thread_id', $threadId)->execute();

		//Move all posts to new forum
		$posts = $sql->createQueryBuilder()->update('forum_post')->set('post_forum', $newForumId)->where('post_thread', $threadId)->execute();
		$replies = $posts-1;
		if($replies < 0) { $replies = 0; }

		//change thread counts accordingly
		$sql->createQueryBuilder()->update('forum')->decrement('forum_threads')->decrement('forum_replies', (int) $replies)->where('forum_id', $oldForumId)->execute();
		$sql->createQueryBuilder()->update('forum')->increment('forum_threads')->increment('forum_replies', (int) $replies)->where('forum_id', $newForumId)->execute();

		// update lastpost information for old and new forums
		$this->forumUpdateLastpost('forum', $oldForumId, false);
		$this->forumUpdateLastpost('forum', $newForumId, false);

		e107::getEvent()->trigger('user_forum_topic_moved', array(
			'old_thread' => $threadInfo,
			'new_thread' => $this->threadGet($threadId)
			));

	}


	function threadUpdate($threadId, $threadInfo)
	{
		$info = array();
	//	$threadInfo['thread_sef'] = eHelper::title2sef($threadInfo['thread_name'],'dashl');

		$info['data'] = $threadInfo;
//		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
		$info['WHERE'] = 'thread_id = '.(int)$threadId;

		if($this->updateRow('forum_thread', $info['data'], 'thread_id', (int)$threadId)===false)
		{
			e107::getMessage()->addDebug("Thread Update Failed: ".print_a($info,true));
		}

		$triggerData = $threadInfo;
		$triggerData['thread_id'] = intval($threadId);
	  	e107::getEvent()->trigger('user_forum_topic_updated', $triggerData);
	}



	function postUpdate($postId, $postInfo)
	{
		$info = array();
		$info['data'] = $postInfo;
//		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_post'];
		$info['WHERE'] = 'post_id = '.(int)$postId;

		if($this->updateRow('forum_post', $info['data'], 'post_id', (int)$postId)===false)
		{
			e107::getMessage()->addDebug("Post Update Failed: ".print_a($info,true));
		}

		$triggerData = $postInfo;
		$triggerData['post_id'] = intval($postId);
	  	e107::getEvent()->trigger('user_forum_post_updated', $triggerData);
	}



	function threadGet($id, $joinForum = true, $uid = USERID)
	{
		$id = (int)$id;
		$uid = (int)$uid;
		$sql = e107::getDb();

		if($joinForum)
		{
			//TODO: Fix query to get only forum and parent info needed, with correct naming
			$qb = $sql->createQueryBuilder();
			$tmp = $qb->select('t.*', 'f.*')
				->selectAs('fp.forum_id', 'parent_id')
				->selectAs('fp.forum_name', 'parent_name')
				->selectAs('sp.forum_id', 'forum_sub')
				->selectAs('sp.forum_name', 'sub_parent')
				->selectAs('fp.forum_sef', 'parent_sef')
				->selectAs('sp.forum_sef', 'sub_parent_sef')
				->addSelect('tr.track_userid')
				->from('forum_thread', 't')
				->leftJoin('forum', 'f', $qb->expr()->compareColumns('t.thread_forum_id', 'f.forum_id'))
				->leftJoin('forum', 'fp', $qb->expr()->compareColumns('fp.forum_id', 'f.forum_parent'))
				->leftJoin('forum', 'sp', $qb->expr()->compareColumns('sp.forum_id', 'f.forum_sub'))
				->leftJoin('forum_track', 'tr', $qb->raw('tr.track_thread = t.thread_id AND tr.track_userid = '.$qb->createNamedParameter($uid)))
				->where('thread_id', $id)
				->fetchRow();
		}
		else
		{
			$tmp = $sql->createQueryBuilder()
				->select('*')->from('forum_thread')
				->where('thread_id', $id)
				->fetchRow();
		}

		if($tmp)
		{
			if(trim($tmp['thread_options']) != '')
			{
				$tmp['thread_options'] = unserialize($tmp['thread_options']);
			}

			$tmp['thread_sef'] = eHelper::title2sef($tmp['thread_name'],'dashl');

			if($joinForum && empty($tmp['forum_sef']))
			{
				e107::getDebug()->log("Forum ".$tmp['forum_name']." is missing a SEF URL. Please add one via the admin area. ");
			}

			return $tmp;
		}
		return false;
	}


	function postGet($id, $start, $num = NULL)
	{
		$id = (int)$id;
		$ret = false;
		$sql = e107::getDb();

		if('post' === $start)
		{
			$qb = $sql->createQueryBuilder();
			$ret = $qb
				->select('u.user_name', 't.thread_active', 't.thread_datestamp', 't.thread_name', 't.thread_user', 't.thread_id', 't.thread_sticky', 'p.*')
				->from('forum_post', 'p')
				->leftJoin('forum_thread', 't', $qb->expr()->compareColumns('t.thread_id', 'p.post_thread'))
				->leftJoin('user', 'u', $qb->expr()->compareColumns('u.user_id', 'p.post_user'))
				->where('p.post_id', $id)
				->fetchAll();
		}
		else
		{
			$qb = $sql->createQueryBuilder();
			$ret = $qb
				->select(
					'p.*',
					'u.user_name', 'u.user_customtitle', 'u.user_hideemail', 'u.user_email', 'u.user_signature',
					'u.user_admin', 'u.user_image', 'u.user_join', 'ue.user_plugin_forum_posts'
				)
				->selectAs('eu.user_name', 'edit_name')
				->addSelect('t.thread_name')
				->from('forum_post', 'p')
				->leftJoin('user', 'u', $qb->expr()->compareColumns('p.post_user', 'u.user_id'))
				->leftJoin('user', 'eu', $qb->raw('p.post_edit_user IS NOT NULL AND p.post_edit_user = eu.user_id'))
				->leftJoin('user_extended', 'ue', $qb->expr()->compareColumns('ue.user_extended_id', 'p.post_user'))
				->leftJoin('forum_thread', 't', $qb->expr()->compareColumns('t.thread_id', 'p.post_thread'))
				->where('p.post_thread', $id)
				->orderBy('p.post_datestamp', 'ASC')
				->setFirstResult((int) $start)->setMaxResults((int) $num)
				->fetchAll();
		}

		foreach($ret as $k => $row)
		{
			$row['thread_sef'] = $this->getThreadSef($row); // eHelper::title2sef($row['thread_name'],'dashl');

			$ret[$k] = $row;
		}

	//	print_a($ret);

		if($start === 'post')
		{
			$ret[0]['forum_sef']= $this->getForumSef($ret[0]);
			$ret[0]['thread_sef'] = $this->getThreadSef($ret[0]);

			return $ret[0];
		}


		return $ret;
	}

	/**
	 * Checks if post is the initial post which started the topic.
	 * Retrieves list of post_id's belonging to one post_thread. When lowest value is equal to input param, return true.
	 * Used to prevent deleting of the initial post (so topic shows empty does not get hidden accidently while posts remain in database)
	 *
	 * @param $postId
	 * @return bool true if post is the initial post of the topic (false, if not)
	 *
	 * @internal param int $postid
	 */
	function threadDetermineInitialPost($postId)
	{
		$sql = e107::getDb();
		$postId = (int)$postId;
		$threadId = $sql->createQueryBuilder()
			->select('post_thread')->from('forum_post')
			->where('post_id', $postId)
			->fetchOne();

		if($rows = $sql->createQueryBuilder()
			->select('post_id')->from('forum_post')
			->where('post_thread', (int) $threadId)
			->fetchAll())
		{
			$postids = array();

			foreach($rows as $row)
			{
				$postids[] = $row['post_id'];
			}

			if($postId == min($postids))
			{
				return true;
			}
		}
		return false;
	}



	function threadGetUserPostcount($threadId)
	{
		$threadId = (int)$threadId;
		$sql = e107::getDb();
		$ret = false;
		$rows = $sql->createQueryBuilder()
			->select('post_user')->selectAggregate('COUNT', 'post_user', 'post_count')->from('forum_post')
			->where('post_thread', $threadId)->whereNotNull('post_user')
			->groupBy('post_user')
			->fetchAll();
		if($rows)
		{
			$ret = array();
			foreach($rows as $row)
			{
				$ret[$row['post_user']] = $row['post_count'];
			}
		}
		return $ret;
	}

	
	function threadGetUserViewed($uid = USERID)
	{
		$e107 = e107::getInstance();
		if($uid == USERID)
		{
			// A visitor with no account has no row and so no read state. Read
			// defensively rather than assuming the key: forum.php?new is public.
			$viewed = isset($e107->currentUser['user_plugin_forum_viewed'])
				? $e107->currentUser['user_plugin_forum_viewed'] : '';
		}
		else
		{
			$tmp = e107::user($uid);
			$viewed = isset($tmp['user_plugin_forum_viewed']) ? $tmp['user_plugin_forum_viewed'] : '';
			unset($tmp);
		}
		return explode(',', (string) $viewed);
	}


	/**
	 * Remove one attachment, refusing anything that is not a bare filename in
	 * the post owner's own attachment directory.
	 *
	 * post_attachments is written from $_POST['post_attachments_json'] with no
	 * validation of any kind, so an entry is whatever the poster sent. This
	 * concatenated it onto the attachment directory and unlinked the result, so
	 * a member could post with a relative path, delete their own post, and have
	 * e107 remove any file the web user could reach.
	 *
	 * The array form is the shape uploads actually store, and only sendFile()
	 * ever accounted for it. Here the array was concatenated onto the path, so
	 * every legitimate attachment was "deleted" as a file named Array while the
	 * real one was orphaned and its record then cleared.
	 *
	 * @param string $baseDir attachment directory of the post's owner
	 * @param mixed $entry stored attachment record
	 * @param string $kind 'file' or 'image', for the log
	 * @param object $log
	 * @return void
	 */
	private function unlinkAttachment($baseDir, $entry, $kind, $log)
	{
		if(is_array($entry))
		{
			$entry = isset($entry['file']) ? $entry['file'] : '';
		}

		$entry = (string) $entry;

		if($entry === '' || strpos($entry, "\0") !== false || $entry !== basename($entry)
			|| $entry === '.' || $entry === '..')
		{
			$log->addWarning("Refused to delete ".$kind." with an unacceptable name: ".$entry);

			return;
		}

		$path = $baseDir.$entry;

		@unlink($path);

		if(file_exists($path))
		{
			$log->addWarning("Could not delete ".$kind.": ".$path.". Please delete manually as this file is now no longer in use (orphaned).");
		}
	}


	function postDeleteAttachments($type = 'post', $id = '') // postDeleteAttachments($type = 'post', $id='', $f='')
	{
		$e107 = e107::getInstance();
		$sql  = e107::getDb();
		$log  = e107::getLog();

		$id = (int)$id;
		if(!$id) { return; }
		
		// Moc: Is the code below used at all? When deleting a thread, threadDelete() loops through each post separately to delete attachments (type=post)
		/*
		if($type == 'thread')
		{
			if(!$sql->select('forum_post', 'post_id', 'post_attachments IS NOT NULL'))
			{
				return true;
			}

			$postList = array();
			
			while($row = $sql->Fetch())
			{
				$postList[] = $row['post_id'];
			}

			foreach($postList as $postId)
			{
				$this->postDeleteAttachment('post', $postId);
			}
		}
		*/
		
		// if we are deleting just a single post
		if($type == 'post')
		{
			$tmp = $sql->createQueryBuilder()
				->select('post_user', 'post_attachments')->from('forum_post')
				->where('post_id', $id)
				->fetchRow();
			if(!$tmp)
			{
				return true;
			}

			$attachment_array = e107::unserialize($tmp['post_attachments']);
			$baseDir = $this->getAttachmentPath($tmp['post_user']);

			foreach(array('file' => 'file', 'img' => 'image') as $key => $kind)
			{
				if(empty($attachment_array[$key]) || !is_array($attachment_array[$key]))
				{
					continue;
				}

				foreach($attachment_array[$key] as $entry)
				{
					$this->unlinkAttachment($baseDir, $entry, $kind, $log);
				}
			}

	   		// At this point we assume that all attachments have been deleted from the post. The log file may prove otherwise (see above). 
	   		$log->toFile('forum_delete_attachments', 'Forum plugin - Delete attachments', TRUE);

	   		// Empty the post_attachments field for this post in the database (prevents loop when deleting entire thread)
	   		$sql->createQueryBuilder()->update('forum_post')->set('post_attachments', null)->where('post_id', $id)->execute();

		}
	}


	/**
	 * Given threadId and postId, determine which number of post in thread the postid is
	 *
	*/
	function postGetPostNum($threadId, $postId)
	{
		$threadId = (int)$threadId;
		$postId = (int)$postId;
		return e107::getDb()->createQueryBuilder()->from('forum_post')
			->where('post_id', '<=', $postId)->where('post_thread', $threadId)
			->count();
	}


	function forumUpdateLastpost($type, $id, $updateThreads = false)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$sql2 = e107::getDb('sql2');


		if ($type == 'thread')
		{
			$id = (int)$id;
			$lpInfo = $this->threadGetLastpost($id);
			$tmp = array();
			if($lpInfo['user_name'])
			{
				$tmp['thread_lastuser'] = $lpInfo['post_user'];
				$tmp['thread_lastuser_anon'] = '_NULL_';
			}
			else
			{
				$tmp['thread_lastuser'] = 0;
				$tmp['thread_lastuser_anon'] = ($lpInfo['post_user_anon'] ? $lpInfo['post_user_anon'] : 'Anonymous');
			}

			$tmp['thread_lastpost'] = $lpInfo['post_datestamp'];

			$this->updateRow('forum_thread', $tmp, 'thread_id', $id);

			return $lpInfo;
		}



		if ($type == 'forum')
		{
			if ($id == 'all')
			{
				$rows = $sql->createQueryBuilder()
					->select('forum_id')->from('forum')
					->where('forum_parent', '!=', 0)
					->fetchAll();
				if ($rows)
				{
					$parentList = array();
					foreach ($rows as $row)
					{
						$parentList[] = $row['forum_id'];
					}
					foreach($parentList as $id)
					{
						set_time_limit(60);
						$this->forumUpdateLastpost('forum', $id, $updateThreads);
					}
				}
			}
			else
			{
				$id = (int)$id;
				$lp_info = '';
				$lp_user = 'NULL';
				if($updateThreads == true)
				{
					//if ($sql2->select('forum_t', 'thread_id', "thread_forum_id = $id AND thread_parent = 0")) // forum_t used in forum_update
					//  issue #3337 fixed usage of old v1 table names
					$threadRows = $sql2->createQueryBuilder()
						->select('thread_id')->from('forum_thread')
						->where('thread_forum_id', $id)
						->fetchEach();
					foreach ($threadRows as $row)
					{
						set_time_limit(60);
						$this->forumUpdateLastpost('thread', $row['thread_id']);
					}
				}
				// thread_lastpost, not thread_datestamp.
				//
				// forum_lastpost_info is "when the last post happened, and in
				// which thread": postAdd() writes post_datestamp into it. This
				// read the thread's *creation* time instead, and ordered by it,
				// so the forum's last post was really its newest thread, dated
				// when that thread began and credited to whoever posted in it
				// most recently. postDelete() and threadDelete() both call this,
				// so removing one spam post could point a busy forum at a thread
				// nobody had touched in years.
				$row = $sql->createQueryBuilder()
					->select('thread_id', 'thread_lastuser', 'thread_lastuser_anon', 'thread_lastpost')->from('forum_thread')
					->where('thread_forum_id', $id)
					->orderBy('thread_lastpost', 'DESC')->setMaxResults(1)
					->fetchRow();
				if ($row)
				{
					$lp_info = $row['thread_lastpost'].'.'.$row['thread_id'];
					$lp_user = $row['thread_lastuser'];
				}
				if(!empty($row['thread_lastuser_anon']))
				{
					$sql->createQueryBuilder()->update('forum')->set('forum_lastpost_user', 0)->set('forum_lastpost_user_anon', $row['thread_lastuser_anon'])->set('forum_lastpost_info', $lp_info)->where('forum_id', (int) $id)->execute();
				}
				else
				{
					// $lp_user is either the literal 'NULL' (no row) or a numeric user id from the row above.
					$sql->createQueryBuilder()->update('forum')
						->set('forum_lastpost_user', ($lp_user === 'NULL' ? null : (int) $lp_user))
						->set('forum_lastpost_user_anon', null)
						->set('forum_lastpost_info', $lp_info)
						->where('forum_id', (int) $id)->execute();
				}
			}
		}
	}

	
	
	/**
	 * Mark threads read: one forum's worth, or the whole board when no forum is
	 * named.
	 *
	 * The identity test used to be against 0, but the caller that means "all of
	 * them" passes null (forum.php, where the id is only cast when it is
	 * present), so "mark all forums read" took the per-forum branch, built a
	 * list containing null, matched nothing and redirected having done nothing.
	 * Every shipped link carries an id, so only the board-wide one was affected.
	 *
	 * @param int|null $forum_id
	 */
	function forumMarkAsRead($forum_id)
	{
		$sql = e107::getDb();
		$flist = null;
		$newIdList = array();
		if (!empty($forum_id))
		{
			$forum_id = (int)$forum_id;
			$flist = array();
			$flist[] = $forum_id;
			if($subList = $this->forumGetSubs($forum_id))
			{
				foreach($subList as $sub)
				{
					$flist[] = $sub['forum_id'];
				}
			}
		}

		$qb = $sql->createQueryBuilder()
			->select('thread_id')->from('forum_thread')
			->where('thread_lastpost', '>', USERLV);
		if($flist !== null)
		{
			$qb->whereIn('thread_forum_id', $flist);
		}
		$rows = $qb->fetchAll();

		if ($rows)
		{
			foreach ($rows as $row)
			{
		  		$newIdList[] = $row['thread_id'];
			}
			if(count($newIdList))
			{
				$this->threadMarkAsRead($newIdList);
			}
		}
		//header('location:'.e_SELF);
		// issue #3338: using e_SELF runs caused an infinite loop
		if (empty($forum_id))
		{
			header('location: '.e107::url('forum', 'index'));
		}
		else
		{
			$forum_sef = e107::getDb()->createQueryBuilder()
				->select('forum_sef')->from('forum')
				->where('forum_id', $forum_id)
				->fetchOne();
			header('location: '.e107::url('forum', 'forum', array('forum_id' => $forum_id, 'forum_sef' => $forum_sef)));
		}
		exit;
	}



	function threadMarkAsRead($threadId)
	{
		global $currentUser;

		$_tmp = preg_split('#\,+#', $currentUser['user_plugin_forum_viewed']);
		if(!is_array($threadId)) { $threadId = array($threadId); }
		foreach($threadId as $tid)
		{
			$_tmp[] = (int)$tid;
		}
		$tmp = array_unique($_tmp);
		// issue #3338 fixed typo, that caused issue with not marking threads are read
		$viewed = trim(implode(',', $tmp), ',');
		$currentUser['user_plugin_forum_viewed'] =  $viewed;
		return e107::getDb()->createQueryBuilder()->update('user_extended')
			->set('user_plugin_forum_viewed', $viewed)
			->where('user_extended_id', (int) USERID)
			->execute();
	}



	function forum_getparents()
	{
		$rows = e107::getDb()->createQueryBuilder()
			->select('*')->from('forum')
			->where('forum_parent', 0)->orderBy('forum_order', 'ASC')
			->fetchAll();
		if ($rows)
		{
			$ret = [];
			foreach ($rows as $row)
			{
				$ret[] = $row;
			}
			return $ret;
		}
		return FALSE;
	}



	function forumGetMods($uclass = e_UC_ADMIN, $force=false)
	{
		$sql = e107::getDb();
		if(count($this->modArray) && !$force)
		{
			return $this->modArray;
		}
		if($uclass == e_UC_ADMIN || trim($uclass) == '')
		{
			$rows = $sql->createQueryBuilder()
				->select('user_id', 'user_name')->from('user')
				->where('user_admin', 1)->orderBy('user_name', 'ASC')
				->fetchAll();
			foreach($rows as $row)
			{
				$this->modArray[$row['user_id']] = $row;
			}
		}
		else
		{
			$this->modArray = e107::getUserClass()->getUsersInClass($uclass, 'user_name', true);
		}


		return $this->modArray;
	}



	function isModerator($uid)
	{
		return ($uid && in_array($uid, array_keys($this->forumGetMods())));
	}



	function forumGetForumList($all=false)
	{
		$sql = e107::getDb();

		$qb = $sql->createQueryBuilder()
			->select('f.*', 'u.user_name')->from('forum', 'f')
			->leftJoin('user', 'u', SqlFragment::raw('f.forum_lastpost_user IS NOT NULL AND u.user_id = f.forum_lastpost_user'));
		if(!$all && !empty($this->permList['view_list']))
		{
			$qb->whereIn('forum_id', $this->permList['view']);
		}
		$rows = $qb->orderBy('f.forum_order', 'ASC')->fetchAll();
		if ($rows)
		{
			$ret = array();
			foreach ($rows as $row)
			{

				if(!$row['forum_parent'])
				{
					$ret['parents'][] = $row;
				}
				elseif($row['forum_sub'])
				{
					$ret['subs'][$row['forum_sub']][] = $row;
				}
				else
				{
					$ret['forums'][$row['forum_parent']][] = $row;
				}

				$id = $row['forum_id'];
				$ret['all'][$id] = $row;
			}
			return $ret;
		}
		return false;
	}


	function forum_getforums($type = 'all')
	{
		$sql = e107::getDb();
		$rows = $sql->createQueryBuilder()
			->select('f.*', 'u.user_name')->from('forum', 'f')
			->leftJoin('user', 'u', SqlFragment::raw("SUBSTRING_INDEX(f.forum_lastpost_user,'.',1) = u.user_id"))
			->where('forum_parent', '!=', 0)->where('forum_sub', 0)
			->orderBy('f.forum_order', 'ASC')
			->fetchAll();
		if ($rows)
		{
			$ret = [];

			foreach ($rows as $row)
			{
				if($type == 'all')
				{
					$ret[$row['forum_parent']][] = $row;
				}
				else
				{
					$ret[] = $row;
				}
			}

			return $ret;
		}
		return FALSE;
	}


	function forumGetSubs($forum_id = '')
	{
		$sql = e107::getDb();
		$qb = $sql->createQueryBuilder();
		$qb->select('f.*', 'u.user_name')->from('forum', 'f')
			->leftJoin('user', 'u', $qb->expr()->compareColumns('f.forum_lastpost_user', 'u.user_id'))
			->where('forum_sub', '!=', 0);
		if($forum_id != '' && $forum_id != 'bysub')
		{
			$qb->where('forum_sub', (int)$forum_id);
		}
		$rows = $qb->orderBy('f.forum_order', 'ASC')->fetchAll();
		if ($rows)
		{
			$ret = [];
			foreach ($rows as $row)
			{
				if($forum_id == '')
				{
					$ret[$row['forum_parent']][$row['forum_sub']][] = $row;
				}
				elseif($forum_id == 'bysub')
				{
					$ret[$row['forum_sub']][] = $row;
				}
				else
				{
					$ret[] = $row;
				}
			}
			return $ret;
		}
		return false;
	}

	
	/**
	* List of forums with unread threads
	*
	* Get a list of forum IDs that have unread threads.
	* If a forum is a subforum, also ensure the parent is in the list.
	*
	* @return 	array|bool	description
	* @access 	public
	*/
	function forumGetUnreadForums()
	{
		if (!USER) {return false; }		// Can't determine new threads for non-logged in users
		$e107 = e107::getInstance();
		$sql = e107::getDb();

		$forumViewed = e107::getUserExt()->get(USERID, 'user_plugin_forum_viewed' );

		$qb = $sql->createQueryBuilder();
		$qb->select('f.forum_sub', 'ft.thread_forum_id')->distinct()->from('forum_thread', 'ft')
			->leftJoin('forum', 'f', $qb->expr()->compareColumns('f.forum_id', 'ft.thread_forum_id'))
			->where('ft.thread_lastpost', '>', defset('USERLV', strtotime('1 month ago')));
		if($forumViewed)
		{
			$qb->whereNotIn('thread_id', explode(',', $forumViewed));
		}

		$ret = array();

	//	e107::getDebug()->log(e107::getParser()->toDate(USERLV,'relative'));

		$rows = $qb->fetchAll();
		if(!empty($rows))
		{
			foreach($rows as $row)
			{
				$ret[] = $row['thread_forum_id'];
				if($row['forum_sub'])
				{
					$ret[] = $row['forum_sub'];
				}
			}
			return $ret;
		}
		else
		{
			return false;
		}
	}


	function thread_user($post_info)
	{
		if($post_info['user_name'])
		{
			return $post_info['user_name'];
		}
		else
		{
			$tmp = explode(".", $post_info['thread_user'], 2);
			return $tmp[1];
		}
	}



	/**
	 * Topic/Thread tracking add/remove
	 * @param $which
	 * @param $uid
	 * @param $threadId
	 * @param bool|false $force
	 * @return bool|int
	 */
	function track($which, $uid, $threadId, $force=false)
	{
		$sql = e107::getDb();

		if ($this->prefs->get('track') != 1 && !$force) { return false; }

		$threadId = (int)$threadId;
		$uid = (int)$uid;
		$result = false;
		switch($which)
		{
			case 'add':
				// forum_track has no auto-increment key; use execute() (affected rows)
				// so success returns a truthy count, matching the legacy insert().
				$result = $sql->createQueryBuilder()->insert('forum_track')->values(array(
					'track_userid' => $uid,
					'track_thread' => $threadId,
				))->execute();
				break;

			case 'delete':
			case 'del':
			 	$result = $sql->createQueryBuilder()->delete('forum_track')
			 		->where('track_userid', $uid)->where('track_thread', $threadId)
			 		->execute();
			 	break;

			case 'check':
				$result = $sql->createQueryBuilder()->from('forum_track')
					->where('track_userid', $uid)->where('track_thread', $threadId)
					->count();
				break;
		}
		return $result;
	}


	/**
	 * The two user classes a thread's forum is read through.
	 *
	 * forum_track records a user and a thread and nothing else, so where the
	 * subscription was taken out has to be recovered before anyone can be judged
	 * on it. A thread names its forum, and the forum's parent carries the second
	 * half of the view permission, exactly as buildPermList() reads it. The
	 * INNER JOIN to the parent is also what refuses a forum sitting at the top
	 * level, which buildPermList() spells forum_parent != 0.
	 *
	 * @param int $threadId
	 * @return array|false forum_class and parent_class, or false when the thread
	 *                     names no forum readable by anybody
	 */
	private function trackForumClasses($threadId)
	{
		$qb = e107::getDb()->createQueryBuilder();
		$expr = $qb->expr();

		$row = $qb->selectAs('f.forum_class', 'forum_class')->selectAs('fp.forum_class', 'parent_class')
			->from('forum_thread', 'th')
			->innerJoin('forum', 'f', $expr->compareColumns('th.thread_forum_id', 'f.forum_id'))
			->innerJoin('forum', 'fp', $expr->compareColumns('f.forum_parent', 'fp.forum_id'))
			->where('th.thread_id', (int) $threadId)
			->fetchRow();

		return empty($row) ? false : $row;
	}


	/**
	 * The subscribers to a thread who may still read the forum it sits in.
	 *
	 * The join to user is INNER: a subscription naming an account that no longer
	 * exists names nobody, and under the LEFT JOIN this replaced it arrived as a
	 * recipient with a NULL address.
	 *
	 * @param int $threadId
	 * @return array user rows, each with user_id, user_name, user_email and
	 *               user_lastvisit
	 */
	private function trackRecipients($threadId)
	{
		$classes = $this->trackForumClasses($threadId);

		if($classes === false)
		{
			return array();
		}

		$qb = e107::getDb()->createQueryBuilder();

		return $qb->select('u.user_id', 'u.user_name', 'u.user_email', 'u.user_lastvisit')
			->from('forum_track', 't')
			->innerJoin('user', 'u', $qb->expr()->compareColumns('t.track_userid', 'u.user_id'))
			->where('t.track_thread', (int) $threadId)
			->where($this->trackClassHeld($qb, $classes['forum_class']))
			->where($this->trackClassHeld($qb, $classes['parent_class']))
			->fetchAll();
	}


	/**
	 * "The recipient, aliased u, holds user class $classId", as SQL.
	 *
	 * The rule being answered is e_user_model::_setClassList(): a signed-in user
	 * holds every class written in user_class along with everything those
	 * inherit, plus the fixed classes their own row answers for. So the
	 * predicate is a match against user_class, ORed with whichever property of
	 * the user row the class is a name for, shaped after
	 * user_class::getUsersInClass(). It answers readability and nothing else;
	 * whether an address is worth writing to is the mailer's question.
	 *
	 * e_UC_PUBLIC, e_UC_READONLY and e_UC_MEMBER are held by every account, and
	 * the join to user is what establishes that there is one.
	 *
	 * e_UC_GUEST and e_UC_NOBODY answer nobody rather than being matched against
	 * user_class, one being the absence of an account and the other the refusal
	 * of every one. That is narrower than checkPerm(), which would grant either
	 * to a row carrying the id literally, and deliberately so.
	 *
	 * @param QueryBuilder $qb the query the recipient is aliased u in
	 * @param int $classId
	 * @return SqlFragment
	 */
	private function trackClassHeld($qb, $classId)
	{
		$expr = $qb->expr();
		$classId = (int) $classId;

		if($classId === e_UC_PUBLIC || $classId === e_UC_READONLY || $classId === e_UC_MEMBER)
		{
			return $qb->raw('1=1');
		}

		if($classId === e_UC_GUEST || $classId === e_UC_NOBODY)
		{
			return $qb->raw('1=0');
		}

		$terms = array($expr->regexp('u.user_class',
			'(^|,)('.implode('|', $this->trackClassHolders($classId)).')(,|$)'));

		switch($classId)
		{
			case e_UC_NEWUSER:
				$period = e107::getPref('user_new_period', 0);
				if(!empty($period))
				{
					$terms[] = $expr->gt('u.user_join', strtotime($period.' days ago'));
				}
				break;

			case e_UC_ADMIN:
				$terms[] = $expr->eq('u.user_admin', 1);
				break;

			case e_UC_MAINADMIN:
				// e_userperms::simulateHasAdminPerms('0', ...): one of the
				// dot-separated segments of user_perms is 0.
				$terms[] = $expr->allOf($expr->eq('u.user_admin', 1),
					$qb->raw("CONCAT('.', u.user_perms, '.') LIKE ".$qb->createNamedParameter('%.0.%')));
				break;
		}

		return call_user_func_array(array($expr, 'anyOf'), $terms);
	}


	/**
	 * The classes whose members hold $classId.
	 *
	 * get_all_user_classes() answers the other direction, "what does a member of
	 * this class hold", and is what USERCLASS_LIST is built from; reversing it
	 * over the class tree is what recognises a member of a child class as
	 * holding its ancestors. FIND_IN_SET on user_class, the shorthand used by
	 * notify::send(), asks only about the classes a user was literally given and
	 * so misses every inherited one.
	 *
	 * $classId is always in the answer, including when the tree has no row for
	 * it: _setClassList() expands user_class literally, so a class deleted from
	 * userclass_classes while members still carry the id is still granted by
	 * checkPerm() and still has to be honoured here.
	 *
	 * @param int $classId
	 * @return int[]
	 */
	private function trackClassHolders($classId)
	{
		$userClass = e107::getUserClass();
		$classId = (int) $classId;
		$holders = array($classId);

		foreach(array_keys($userClass->uc_get_classlist()) as $candidate)
		{
			$held = array_map('intval', $userClass->get_all_user_classes((string) $candidate, true));

			if(in_array($classId, $held, true))
			{
				$holders[] = (int) $candidate;
			}
		}

		return array_unique($holders);
	}


	/**
	 * Send an email to users who are tracking the topic/thread.
	* @param $post
	 * @return bool
	*/
	function trackEmail($post)
	{
		$tp = e107::getParser();

		$trackingPref = $this->prefs->get('track');
		$trackingEmailPref = $this->prefs->get('trackemail',true);

		if(empty($trackingPref) || empty($trackingEmailPref))
		{
			return false;
		}

		$data = $this->trackRecipients(intval($post['post_thread']));

		if(empty($data))
		{
			return false;
		}

		$threadData = $this->threadGet($post['post_thread']);

		$recipients = array();

		$thread_name = $tp->toText($threadData['thread_name']);
	//	$thread_name = str_replace('&quot;', '"', $thread_name);		// This not picked up by toText();
		$datestamp = $tp->toDate($post['post_datestamp']);
		$email_post = $tp->toHTML($post['post_entry'], true);

	//	$mail_link = "<a href='".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent.".last'>".SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent.".last</a>";

		$query = array('last'=>1);
		$mail_link = e107::url('forum','topic', $threadData, array('mode'=>'full','query'=>$query));
		$subject = $this->prefs->get('eprefix')." ".$thread_name;

		foreach($data as $row)
		{

			$recipients[] = array(
							'mail_recipient_id'     => $row['user_id'],
							'mail_recipient_name'   => $row['user_name'],		// Should this use realname?
							'mail_recipient_email'  => $row['user_email'],
							'mail_target_info'		=> array(
								'USERID'		        => $row['user_id'],
								'DISPLAYNAME' 	        => $row['user_name'],
								'SUBJECT'               => $subject,
								'USERNAME' 		        => $row['user_name'],
								'USERLASTVISIT'         => $row['user_lastvisit'],
						//		'UNSUBSCRIBE'	        => (!in_array($notifyTarget, $exclude)) ? $unsubUrl : '',
						//		'UNSUBSCRIBE_MESSAGE'   => (!in_array($notifyTarget, $exclude)) ? $unsubMessage : '',
						//		'USERCLASS'             => $notifyTarget,
								'DATE_SHORT'            => $tp->toDate(time(),'short'),
								'DATE_LONG'             => $tp->toDate(time(),'long'),


							)
						);
		}


		require_once(e_HANDLER.'mail_manager_class.php');

		$mailer = new e107MailManager;

		$vars = array('x'=>USERNAME, 'y'=>$thread_name, 'z'=>$datestamp);

		$message = "[html]".$tp->lanVars(LAN_FORUM_8001, $vars,true)."<br /><br /><blockquote>".$tp->toEmail($email_post, false)."</blockquote><br />".LAN_FORUM_8002."<br /><a href='".$mail_link."'>".$mail_link."</a>[/html]";


			// Create the mail body
		$mailData = array(
				'mail_total_count'      => count($recipients),
				'mail_content_status' 	=> MAIL_STATUS_TEMP,
				'mail_create_app' 		=> 'forum',
				'mail_title' 			=> 'FORUM TRACKING',
				'mail_subject' 			=> $subject,
				'mail_sender_email' 	=> e107::getPref('replyto_email',SITEADMINEMAIL),
				'mail_sender_name'		=> e107::getPref('replyto_name',SITEADMIN),
				'mail_notify_complete' 	=> 0,	// NEVER notify when this email sent!
				'mail_body' 			=> $message,
				'template'				=> 'default',
				'mail_send_style'       => 'default'
		);

		/*	if(!empty($media) && is_array($media))
			{
				foreach($media as $k=>$v)
				{
					$mailData['mail_media'][$k] = array('path'=>$v);
				}
			}*/


		$opts =  array(); // array('mail_force_queue'=>1);
		$mailer->sendEmails('default', $mailData, $recipients, $opts);


	}


	function forumGet($forum_id)
	{
		$sql = e107::getDb();
		$forum_id = (int)$forum_id;
		$qb = $sql->createQueryBuilder();
		$row = $qb
			->select('f.*')
			->selectAs('fp.forum_class', 'parent_class')
			->selectAs('fp.forum_name', 'parent_name')
			->selectAs('fp.forum_id', 'parent_id')
			->selectAs('fp.forum_postclass', 'parent_postclass')
			->selectAs('sp.forum_name', 'sub_parent')
			->selectAs('sp.forum_sef', 'parent_sef')
			->from('forum', 'f')
			->leftJoin('forum', 'fp', $qb->expr()->compareColumns('fp.forum_id', 'f.forum_parent'))
			->leftJoin('forum', 'sp', $qb->expr()->allOf($qb->expr()->compareColumns('f.forum_sub', 'sp.forum_id'), $qb->expr()->gt('f.forum_sub', 0)))
			->where('f.forum_id', $forum_id)
			->fetchRow();
		if ($row)
		{
			if(empty($row['forum_sef']))
			{
				e107::getDebug()->log("Forum ".$row['forum_name']." is missing a SEF URL. Please add one via the admin area. ");
			}

			return $row;
		}
		return FALSE;
	}


	function forumGetAllowed($type='view')
	{
		if(empty($this->permList[$type]))
		{
			return array();
		}

		$sql = e107::getDb();
		$rows = $sql->createQueryBuilder()
			->select('forum_id', 'forum_name', 'forum_sef')->from('forum')
			->whereIn('forum_id', $this->permList[$type])->where('forum_parent', '!=', 0)
			->fetchAll();

		$ret = [];
		foreach($rows as $row)
		{
			$ret[$row['forum_id']] = $row;
		}
		return $ret;
	}


	/**
	 * @param $forumId
	 * @param $from
	 * @param $view
	 * @param null $filter
	 * @return array
	 */
	function forumGetThreads($forumId, $from, $view, $filter = null)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$forumId = (int)$forumId;

		$qb = $sql->createQueryBuilder();
		$qb->select('t.*', 'f.forum_id', 'f.forum_sef', 'f.forum_name', 'u.user_name')->selectAs('lpu.user_name', 'lastpost_username')->selectAggregate('MAX', 'p.post_id', 'lastpost_id')
			->from('forum_thread', 't')
			->leftJoin('forum', 'f', $qb->expr()->compareColumns('t.thread_forum_id', 'f.forum_id'))
			->leftJoin('forum_post', 'p', $qb->expr()->compareColumns('t.thread_id', 'p.post_thread'))
			->leftJoin('user', 'u', $qb->expr()->compareColumns('t.thread_user', 'u.user_id'))
			->leftJoin('user', 'lpu', $qb->expr()->compareColumns('t.thread_lastuser', 'lpu.user_id'))
			->where('t.thread_forum_id', $forumId);

		if(!empty($filter))
		{
			// $filter is a free-text thread-name search term (see forum_viewforum.php);
			// bind it as a LIKE so the value can never reach SQL unescaped.
			$qb->where($qb->expr()->contains('t.thread_name', $filter));
		}

		$rows = $qb->groupBy('thread_id')
			->orderBy('t.thread_sticky', 'DESC')->addOrderBy('t.thread_lastpost', 'DESC')
			->setFirstResult((int)$from)->setMaxResults((int)$view)
			->fetchAll();

		$ret = array();
		foreach ($rows as $row)
		{
			if(empty($row['forum_sef']))
			{
				e107::getDebug()->log("Forum ".$row['forum_name']." is missing a SEF URL. Please add one via the admin area. ");
			}

			$ret[] = $row;
		}
		return $ret;
	}


	function threadGetLastpost($id)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$id = (int)$id;
		$qb = $sql->createQueryBuilder();
		$row = $qb
			->select('p.post_user', 'p.post_id', 'p.post_user_anon', 'p.post_datestamp', 'p.post_thread', 't.thread_name', 'u.user_name')
			->from('forum_post', 'p')
			->leftJoin('forum_thread', 't', $qb->expr()->compareColumns('p.post_thread', 't.thread_id'))
			->leftJoin('user', 'u', $qb->expr()->compareColumns('u.user_id', 'p.post_user'))
			->where('p.post_thread', $id)
			->orderBy('p.post_datestamp', 'DESC')->setFirstResult(0)->setMaxResults(1)
			->fetchRow();
		if ($row)
		{
			$row['thread_sef'] = eHelper::title2sef($row['thread_name'],'dashl');
			return $row;
		}
		return false;
	}

//	function forum_get_topic_count($forum_id)
//	{
//		$e107 = e107::getInstance();
//		return $sql->count('forum_thread', '(*)', 'WHERE thread_forum_id='.(int)$forum_id);
//	}


	function threadGetNextPrev($which, $threadId, $forumId, $lastpost)
	{
		$sql = e107::getDb();
		
		$forumId = (int)$forumId;
		$lastpost = (int)$lastpost;

		$dir = ($which == 'next') ? '<' : '>';


		$qb = $sql->createQueryBuilder();
		$row = $qb
			->select('t.thread_id', 't.thread_name', 'f.forum_id', 'f.forum_sef')
			->from('forum_thread', 't')
			->leftJoin('forum', 'f', $qb->expr()->compareColumns('t.thread_forum_id', 'f.forum_id'))
			->where('t.thread_forum_id', $forumId)
			->where('t.thread_lastpost', $dir, $lastpost)
			->orderBy('t.thread_sticky', 'DESC')->addOrderBy('t.thread_lastpost', 'ASC')
			->setMaxResults(1)
			->fetchRow();

	//		e107::getMessage()->addDebug(ucfirst($which)." Thread Qry: ".$qry);

			if ($row)
			{
				$row['thread_sef'] = eHelper::title2sef($row['thread_name'],'dashl');
		//		e107::getMessage()->addInfo(ucfirst($which).print_a($row,true));
				return $row;
			//	return $row['thread_id'];

			}
			//else
		//	{
			//	e107::getMessage()->addDebug(ucfirst($which)." Thread Qry Returned Nothing: ".$qry);
		//	}

			return false;
	}


	function threadIncView($id)
	{
		$id = (int)$id;
		return e107::getDb()->createQueryBuilder()->update('forum_thread')
			->increment('thread_views')
			->where('thread_id', $id)->execute();
	}



	function _forum_lp_update($lp_type, $lp_user, $lp_info, $lp_forum_id, $lp_forum_sub)
	{
		$sql = e107::getDb();

		// $lp_type is a dynamic column name; validate it fail-closed before use.
		$lpTypeQuoted = $sql->quoteIdentifier($lp_type);
		if($lpTypeQuoted === false)
		{
			return;
		}

		$sql->createQueryBuilder()->update('forum')
			->setExpression($lp_type, SqlFragment::raw($lpTypeQuoted.'+1'))
			->set('forum_lastpost_user', $lp_user)
			->set('forum_lastpost_info', $lp_info)
			->where('forum_id', intval($lp_forum_id))->execute();
		if($lp_forum_sub)
		{
			$sql->createQueryBuilder()->update('forum')
				->set('forum_lastpost_user', $lp_user)
				->set('forum_lastpost_info', $lp_info)
				->where('forum_id', intval($lp_forum_sub))->execute();
		}
	}




	/**
	 * Threads with activity the caller has not seen, for forum.php?new.
	 *
	 * Three things were wrong here, and the route is public, so all three were
	 * reachable without an account:
	 *
	 *  - the "already read" filter compared thread ids against
	 *    thread_forum_id, so reading one thread hid every thread in whatever
	 *    forum shared that id. forumGetUnreadForums() has always compared the
	 *    right column.
	 *  - no forum_class predicate, unlike every other listing this plugin
	 *    ships (e_search, e_rss, e_list), so the page offered thread names and
	 *    last posters out of forums the caller cannot open.
	 *  - USERLV is only defined for a signed-in visitor (class2.php), and it
	 *    was dereferenced bare, which on PHP 8 is a fatal rather than a notice.
	 *    A guest or a crawler asking for forum.php?new got a blank page.
	 *
	 * The permission list is the viewer's, not $uid's. The output goes to
	 * whoever asked, so theirs is the question worth answering.
	 *
	 * @param int $count
	 * @param bool $unread
	 * @param int $uid whose read-state to filter by
	 * @return array
	 */
	function threadGetNew($count = 50, $unread = true, $uid = USERID)
	{
		$sql = e107::getDb();

		$visible = $this->getForumPermList('view');

		if(empty($visible))
		{
			return array();
		}

		$viewedList = array();
		if($unread)
		{
			$viewedList = array_filter($this->threadGetUserViewed($uid), 'strlen');
		}

		//  issue #3337 fixed usage of old v1 table names
		$qb = $sql->createQueryBuilder();
		$qb->select('t.*', 'u.user_name')
			->from('forum_thread', 't')
			->leftJoin('user', 'u', $qb->expr()->compareColumns('u.user_id', 't.thread_lastuser'))
			->where('t.thread_lastpost', '>', (int) defset('USERLV', 0))
			->whereIn('t.thread_forum_id', $visible);
		if(!empty($viewedList))
		{
			$qb->whereNotIn('t.thread_id', $viewedList);
		}

		return $qb->orderBy('t.thread_lastpost', 'DESC')
			->setFirstResult(0)->setMaxResults((int)$count)
			->fetchAll();
	}


	function forumPrune($type, $days, $forumArray)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$tp = e107::getParser();

		$prunedate = time() - (int)$days * 86400;
		$forumList = $tp->filter($forumArray,'int');

		if($type == 'delete')
		{
			//Get list of threads to prune
			$threadList = $sql->createQueryBuilder()
				->select('thread_id')->from('forum_thread')
				->where('thread_lastpost', '<', $prunedate)
				->where('thread_sticky', '!=', 1)
				->whereIn('thread_forum_id', $forumList)
				->fetchAll();
			if ($threadList)
			{
				$thread_count = count($threadList);
				$reply_count = 0;
				foreach($threadList as $thread)
				{
					$reply_count += $sql->createQueryBuilder()->from('forum_post')->where('post_thread', $thread['thread_id'])->count();
					$this->threadDelete($thread['thread_id'], false);
				}
				foreach($forumArray as $fid)
				{
					$this->forumUpdateLastpost('forum', $fid);
					$this->forumUpdateCounts($fid);
				}
				return FORLAN_8." ( ".$thread_count." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
			//	return FORLAN_8." ( ".count($threadList)." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
			}
			else
			{
				return FORLAN_9;
			}
		}
		if($type == 'make_inactive')
		{
			$pruned = $sql->createQueryBuilder()->update('forum_thread')
				->set('thread_active', 0)
				->where('thread_lastpost', '<', $prunedate)
				->whereIn('thread_forum_id', $forumList)
				->execute();
			return FORLAN_8.' '.$pruned.' '.FORLAN_91;
		}
	}


	function forumUpdateCounts($forumId, $recalcThreads = false)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		if($forumId == 'all')
		{
			$flist = $sql->createQueryBuilder()
				->select('forum_id')->from('forum')
				->where('forum_parent', '!=', 0)
				->fetchAll();
			foreach($flist as $f)
			{
				set_time_limit(60);
				$this->forumUpdateCounts($f['forum_id'], $recalcThreads);
			}
			return;
		}
		$forumId = (int)$forumId;
		$threads = $sql->createQueryBuilder()->from('forum_thread')->where('thread_forum_id', $forumId)->count();
		$replies = $sql->createQueryBuilder()->from('forum_post')->where('post_forum', $forumId)->count();
		$replies = $replies - $threads;

		$sql->createQueryBuilder()->update('forum')
			->set('forum_threads', $threads)->set('forum_replies', $replies)
			->where('forum_id', $forumId)->execute();
		if($recalcThreads == true)
		{
			set_time_limit(60);
			$tlist = $sql->createQueryBuilder()
				->select('post_thread')->selectAggregate('COUNT', 'post_thread', 'replies')->from('forum_post')
				->where('post_forum', $forumId)->groupBy('post_thread')
				->fetchAll();
			foreach($tlist as $t)
			{
				$tid = $t['post_thread'];
				$replies = (int)$t['replies'] - 1;
				$sql->createQueryBuilder()->update('forum_thread')
					->set('thread_total_replies', $replies)
					->where('thread_id', $tid)->execute();
			}
		}
	}


	/**
	 * @param $threadID
	 * @return int
	 */
	/**
	 * Recount a thread's replies, as splitting a topic requires.
	 *
	 * thread_total_replies excludes the opening post: postAdd() increments it
	 * once per reply and every reader adds one back for the first post. This
	 * stored the raw row count, so a split topic came out one reply heavy at
	 * both ends, which the page turns into a phantom extra page.
	 *
	 * @param int $threadID
	 * @return mixed
	 */
	function threadUpdateCounts($threadID)
	{
		$sql = e107::getDb();

		$posts = (int) $sql->createQueryBuilder()->from('forum_post')->where('post_thread', (int) $threadID)->count();
		$replies = max(0, $posts - 1);

		return $sql->createQueryBuilder()->update('forum_thread')
			->set('thread_total_replies', $replies)
			->where('thread_id', (int) $threadID)->execute();

	}




	function getUserCounts()
	{
		$sql = e107::getDb();
		$rows = $sql->createQueryBuilder()
			->select('post_user')->selectAggregate('COUNT', 'post_user', 'cnt')->from('forum_post')
			->where('post_user', '>', 0)
			->groupBy('post_user')
			->fetchAll();

		$ret = array();
		foreach($rows as $row)
		{
			$ret[$row['post_user']] = $row['cnt'];
		}
		return $ret;
	}



// Function eventually to be reworked (move full function to shortcode file, or make a new breadcrumb function, like in downloads, maybe?)
	/*
	 * set bread crumb
	 * $forum_href override ONLY applies when template is missing FORUM_CRUMB
	 * $thread_title is needed for post-related breadcrumbs
	 */
	function set_crumb($forum_href=false, $thread_title='', &$templateVar=null)
	{

		$tp = e107::getParser();
		$frm = e107::getForm();

		$forumTitle = e107::pref('forum','title', LAN_PLUGIN_FORUM_NAME);
		
//--		global $FORUM_CRUMB, $forumInfo, $threadInfo, $thread;
//--		global $BREADCRUMB,$BACKLINK;  // Eventually we should deprecate BACKLINK
		global $FORUM_CRUMB, $forumInfo, $threadInfo, $thread, $BREADCRUMB;

		if(!$forumInfo && $thread) { $forumInfo = $thread->threadInfo; }

		if(is_array($FORUM_CRUMB))
		{

			$search 	= array('{SITENAME}', '{SITENAME_HREF}');
			$replace 	= array(SITENAME, e107::getUrl()->create('/'));
			$FORUM_CRUMB['sitename']['value'] = str_replace($search, $replace, $FORUM_CRUMB['sitename']['value']);

			$search 	= array('{FORUMS_TITLE}', '{FORUMS_HREF}');
			$replace 	= array($forumTitle, e107::url('forum','index'));
			$FORUM_CRUMB['forums']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forums']['value']);

			$search 	= array('{PARENT_TITLE}', '{PARENT_HREF}');
			$replace 	= array($tp->toHTML($forumInfo['parent_name']), e107::url('forum','index')."#".$frm->name2id($forumInfo['parent_name']));
			$FORUM_CRUMB['parent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['parent']['value']);


			if($forumInfo['forum_sub'])
			{
				$search 	= array('{SUBPARENT_TITLE}', '{SUBPARENT_HREF}');
				$replace 	= array(ltrim($forumInfo['sub_parent'], '*'), e107::url('forum', 'forum', array('forum_id'=>$forumInfo['forum_sub'],'forum_sef'=>$forumInfo['parent_sef'])));
				$FORUM_CRUMB['subparent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['subparent']['value']);
			}
			else
			{
				$FORUM_CRUMB['subparent']['value'] = '';
			}

			$search 	= array('{FORUM_TITLE}', '{FORUM_HREF}');
			$replace 	= array(ltrim($forumInfo['forum_name'], '*'), e107::url('forum', 'forum', $forumInfo));
			$FORUM_CRUMB['forum']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forum']['value']);

			if(isset($threadInfo['thread_id']))
			{
				$threadInfo['thread_id'] = intval($threadInfo['thread_id']);
			}
			$search 	= array('{THREAD_TITLE}', '{THREAD_HREF}');
			$replace 	= array(vartrue($threadInfo['thread_name']), ''); // $thread->threadInfo - no reference found


			$FORUM_CRUMB['thread']['value'] = str_replace($search, $replace, varset($FORUM_CRUMB['thread']['value']));


			$FORUM_CRUMB['fieldlist'] = 'sitename,forums,parent,subparent,forum,thread';

			$BREADCRUMB = $tp->parseTemplate('{BREADCRUMB=FORUM_CRUMB}', false); // must stay as 'false' to prevent sending to theme shortcodes.
		}
		else
		{



			$dfltsep = ' :: ';
			$BREADCRUMB = "<a class='forumlink' href='".e_HTTP."index.php'>".SITENAME."</a>".$dfltsep.
			"<a class='forumlink' href='". e107::url('forum','index')."'>".$forumTitle."</a>".$dfltsep;

			if($forumInfo['sub_parent'])
			{
				$forum_sub_parent = (substr($forumInfo['sub_parent'], 0, 1) == '*' ? substr($forumInfo['sub_parent'], 1) : $forumInfo['sub_parent']);
				$BREADCRUMB .= "<a class='forumlink' href='".e_PLUGIN_ABS."forum/forum_viewforum.php?{$forumInfo['forum_sub']}'>{$forum_sub_parent}</a>".$dfltsep;
			}

			$tmpFname = $forumInfo['forum_name'];
			if(substr($tmpFname, 0, 1) == "*") { $tmpFname = substr($tmpFname, 1); }

			if ($forum_href)
			{
				$BREADCRUMB .= "<a class='forumlink' href='".e107::url('forum', 'forum', $forumInfo)."'>".$tp->toHTML($tmpFname, TRUE, 'no_hook,emotes_off')."</a>";
			} else
			{
				$BREADCRUMB .= $tmpFname;
			}

			if(strlen($thread_title))
			{
				$BREADCRUMB .= $dfltsep.$thread_title;
			}
		}



		// New v2.x Bootstrap Standardized Breadcrumb.

	//	print_a($forumInfo);
		// return;

		$breadcrumb = array();
		
		$breadcrumb[]	= array('text'=> $forumTitle	, 'url'=> e107::url('forum','index'));
		
		if($forumInfo['sub_parent'])
		{
				$forum_sub_parent = (substr($forumInfo['sub_parent'], 0, 1) == '*' ? substr($forumInfo['sub_parent'], 1) : $forumInfo['sub_parent']);
		}
		
		$breadcrumb[]	= array('text'=>$tp->toHTML($forumInfo['parent_name'])		, 'url'=> e107::url('forum', 'index')."#".$frm->name2id($forumInfo['parent_name']));
	
		if($forumInfo['forum_sub'])
		{
			$breadcrumb[]	= array('text'=> ltrim($forumInfo['sub_parent'], '*')		, 'url'=> e107::url('forum','forum', array('forum_sef'=> $forumInfo['sub_parent_sef'])));
			$breadcrumb[]	= array('text'=>ltrim($forumInfo['forum_name'], '*')		, 'url'=> (defset('e_PAGE') !='forum_viewforum.php') ? e107::url('forum', 'forum', $forumInfo) : null);

		}
		else
		{
			$breadcrumb[]	= array('text'=>ltrim($forumInfo['forum_name'], '*')		, 'url'=> (defset('e_PAGE') !='forum_viewforum.php') ? e107::url('forum', 'forum', $forumInfo) : null);
		}

		if(vartrue($forumInfo['thread_name']))
		{
			$breadcrumb[]	= array('text'=> $forumInfo['thread_name'] , 'url'=>null);
		}
		
		
		if(deftrue('BOOTSTRAP'))
		{
			$BREADCRUMB =  $frm->breadcrumb($breadcrumb);
			e107::breadcrumb($breadcrumb); // assign to {---BREADCRUMB---}
		}
		
		
		
/*
		$BACKLINK = $BREADCRUMB;

		$templateVar->BREADCRUMB = $BREADCRUMB;
	
	
		$templateVar->BACKLINK = $BACKLINK;
		$templateVar->FORUM_CRUMB = $FORUM_CRUMB;
*/
    // Backlink shortcode is defined inside shortcode file....
//---- var_dump ($templateVar);
//---- echo "<hr>";
		$templateVar['breadcrumb'] = $BREADCRUMB;
		$templateVar['forum_crumb'] = $FORUM_CRUMB;
	}


	/**
	 * Delete a Thread
	 * @param $threadId int
	 * @param $updateForumLastPost boolean
	 * @return true on success or false on error. 
	 */
	function threadDelete($threadId, $updateForumLastpost = true)
	{
		$e107 = e107::getInstance();		
		$sql = e107::getDb();
		$status = false; 
		
		if ($threadInfo = $this->threadGet($threadId))
		{
			$threadId = (int) $threadId;

			// delete poll if there is one
			if($sql->createQueryBuilder()->from('polls')->where('poll_datestamp', $threadId)->count())
			{
				$sql->createQueryBuilder()->delete('polls')->where('poll_datestamp', $threadId)->execute();
			}

			// decrement user post counts
			if ($postCount = $this->threadGetUserPostcount($threadId))
			{
				foreach ($postCount as $k => $v)
				{
					$sql->createQueryBuilder()->update('user_extended')
						->setExpression('user_plugin_forum_posts', SqlFragment::raw('GREATEST(user_plugin_forum_posts-'.(int) $v.',0)'))
						->where('user_extended_id', (int) $k)->execute();
				}
			}

			// delete all posts
			$postRows = $sql->createQueryBuilder()
				->select('post_id')->from('forum_post')
				->where('post_thread', $threadId)
				->fetchAll();
			if($postRows)
			{
				$postList = array();
				foreach($postRows as $row)
				{
					$postList[] = $row['post_id'];
				}

				foreach($postList as $postId)
				{
					$this->postDelete($postId, false);
				}
			}

			// delete the thread itself
			if($sql->createQueryBuilder()->delete('forum_thread')->where('thread_id', $threadId)->execute())
			{
				$status = true;
			  	e107::getEvent()->trigger('user_forum_topic_deleted', $threadInfo);
			}

			//Delete any thread tracking
			if($sql->createQueryBuilder()->from('forum_track')->where('track_thread', $threadId)->count())
			{
				$sql->createQueryBuilder()->delete('forum_track')->where('track_thread', $threadId)->execute();
			}

			// update forum with correct thread/reply counts
			$sql->createQueryBuilder()->update('forum')
				->setExpression('forum_threads', SqlFragment::raw('GREATEST(forum_threads-1,0)'))
				->setExpression('forum_replies', SqlFragment::raw('GREATEST(forum_replies-'.(int) $threadInfo['thread_total_replies'].',0)'))
				->where('forum_id', (int) $threadInfo['thread_forum_id'])->execute();

			if($updateForumLastpost)
			{
				// update lastpost info
				$this->forumUpdateLastpost('forum', $threadInfo['thread_forum_id']);
			}
			return $status; // - XXX should return true/false $threadInfo['thread_total_replies'];
		}
	}

	/**
	 * Delete a Post
	 * @param $postId int
	 * @param $updateCounts boolean
	 * @return "null" if this post does not exist, "true" if post could deleted, otherwise "false"
	 */
	function postDelete($postId, $updateCounts = true)
	{
		$postId 	= (int)$postId;

		$sql 		= e107::getDb();
		$deleted 	= false;

		$postInfo   = $sql->createQueryBuilder()
			->select('*')->from('forum_post')
			->where('post_id', $postId)
			->fetchRow();

		if(!is_array($postInfo) || empty($postInfo))
		{
			return null;
		}

		//delete attachments if they exist
		if($postInfo['post_attachments'])
		{
			$this->postDeleteAttachments('post', $postId);
		}

		// delete post from database
		if($sql->createQueryBuilder()->delete('forum_post')->where('post_id', $postId)->execute())
		{
			$deleted = true;
		  	e107::getEvent()->trigger('user_forum_post_deleted', $postInfo);
		}

		// update statistics
		if($updateCounts)
		{
			// decrement user post counts
			if ($postInfo['post_user'])
			{
				$sql->createQueryBuilder()->update('user_extended')
					->setExpression('user_plugin_forum_posts', SqlFragment::raw('GREATEST(user_plugin_forum_posts-1,0)'))
					->where('user_extended_id', (int) $postInfo['post_user'])->execute();
			}

			// update thread with correct reply counts
			$sql->createQueryBuilder()->update('forum_thread')
				->setExpression('thread_total_replies', SqlFragment::raw('GREATEST(thread_total_replies-1,0)'))
				->where('thread_id', (int) $postInfo['post_thread'])->execute();

			// update forum with correct thread/reply counts
			$sql->createQueryBuilder()->update('forum')
				->setExpression('forum_replies', SqlFragment::raw('GREATEST(forum_replies-1,0)'))
				->where('forum_id', (int) $postInfo['post_forum'])->execute();

			// update thread lastpost info
			$this->forumUpdateLastpost('thread', $postInfo['post_thread']);

			// update forum lastpost info
			$this->forumUpdateLastpost('forum', $postInfo['post_forum']);
		}
		return $deleted;
	}


	/**
	 *  Check for legacy Prefernces and upgrade if neccessary.
	 */
	public function upgradeLegacyPrefs()
	{

			$legacyMenuPrefs = array(
				'newforumposts_caption'     => 'caption',
				'newforumposts_display'     => 'display',
				'newforumposts_maxage'      => 'maxage',
				'newforumposts_characters'  => 'chars',
				'newforumposts_postfix'     => 'postfix',
				'newforumposts_title'       => 'title'
			);

			if($newPrefs = e107::getConfig('menu')->migrateData($legacyMenuPrefs, true)) // returns false if no match found.
			{
				if(e107::getMenu()->setParms('forum','newforumposts_menu', $newPrefs) !== false)
				{
					e107::getMessage()->addDebug("Successfully migrated newforumposts prefs from core to menu table.");
				}
				else
				{
					e107::getMessage()->addDebug("Legacy Forum menu pref detected. Upgrading...");
				}
			}

	}

}


/**
* @return string path to and filename of forum icon image
*
* @param string $filename  filename of forum image

*
* @desc checks for the existence of a forum icon image in the themes forum folder and if it is found
*  returns the path and filename of that file, otherwise it returns the path and filename of the
*  default forum icon image in e_IMAGES. The additional  args if specfied switch the process
*  to the sister multi-language function 
*
* @access public
*/
function img_path($filename)
{

	$multilang = array('reply.png','newthread.png','moderator.png','main_admin.png','admin.png');
	$ML = in_array($filename,$multilang);

		if(file_exists(THEME.'forum/'.$filename) || is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename))
		{
			$image = ($ML && is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename)) ? THEME_ABS.'forum/'.e_LANGUAGE."_".$filename :  THEME_ABS.'forum/'.$filename;
		}
		// #3948 - added for consistency with plugin folder structure. Also check THEME/forum/images/icons/ folder 
		elseif(file_exists(THEME.'forum/images/icons/'.$filename) || is_readable(THEME.'forum/images/icons/'.e_LANGUAGE.'_'.$filename))
		{
			$image = ($ML && is_readable(THEME.'forum/images/icons/'.e_LANGUAGE.'_'.$filename)) ? THEME_ABS.'forum/images/icons/'.e_LANGUAGE."_".$filename :  THEME_ABS.'forum/images/icons/'.$filename;
		}
		else
		{
			if(defined('IMODE'))
			{
				if($ML)
				{
                	$image = (is_readable(e_PLUGIN.'forum/images/icons/'.e_LANGUAGE.'_'.$filename)) ? e_PLUGIN_ABS.'forum/images/icons/'.e_LANGUAGE.'_'.$filename : e_PLUGIN_ABS.'forum/images/icons/English_'.$filename;
				}
				else
				{
                	$image = e_PLUGIN_ABS.'forum/images/icons/'.$filename;
				}
			}
			else
			{
				if($ML)
				{
					$image = (is_readable(e_PLUGIN."forum/images/lite/".e_LANGUAGE.'_'.$filename)) ? e_PLUGIN_ABS.'forum/images/icons/'.e_LANGUAGE.'_'.$filename : e_PLUGIN_ABS.'forum/images/icons/English_'.$filename;
				}
				else
                {
           			$image = e_PLUGIN_ABS.'forum/images/icons/'.$filename;
				}

			}
		}

	return $image;
}
