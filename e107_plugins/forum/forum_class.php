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

// TODO LAN

/* Forum Header File */
if (!defined('e107_INIT')) { exit; }

e107::js('forum', 'js/forum.js', 'jquery', 5);
e107::css('forum','forum.css');

e107::lan('forum', "front", true);

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
	private $userViewed, $permList;
	public $modArray, $prefs;
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
		$tp = e107::getParser();
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
	 * Grab the forum data up front to reduce LEFT JOIN usage. Currently only forum_id and forum_sef but may be expanded as needed.
	 */
	private function getForumData()
	{
		$data = e107::getDb()->retrieve("SELECT forum_id, forum_sef, forum_class FROM `#forum`", true); // no ordering for better performance.

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


			$qry = "SELECT user_id, user_name, user_class FROM `#user` WHERE FIND_IN_SET(".$class.", user_class) OR user_class = ".$class." ORDER by user_name LIMIT 50"; // FIND_IN_SET(user_class, ".$class.")
			$users = e107::getDb()->retrieve($qry, true);

			return $users;
		}

		return false;

	}


	/**
	 * @param $user integer userid (if empty "anon" will be used)
	 * @param $create boolean creates the attachment folder if set to true
	 * @return forum attachment path for specific user
	 */
	function getAttachmentPath($user, $create = FALSE)
	{
		$user = intval($user);
		$tp = e107::getParser();
		$baseDir = e_MEDIA.'plugins/forum/attachments/';
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
        $forum_id 	= $sql->retrieve('forum_post','post_forum','post_id='.$post_id);

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

        $array 	= $sql->retrieve('forum_post','post_user,post_attachments','post_id='.$post_id);
        $attach = e107::unserialize($array['post_attachments']);

        $filename = is_array($attach['file'][$file_id]) ? $attach['file'][$file_id]['file'] : $attach['file'][$file_id];

        $file 	= $this->getAttachmentPath($array['post_user']).varset($filename);

        // Check if file exists. Send file for download if it does, return 404 error code when file does not exist. 
 		if(file_exists($file))
 		{
 		   e107::getFile()->send($file);
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
	 * Handle the Ajax quick-reply.
	 */
	function ajaxQuickReply()
	{
		$tp = e107::getParser();

		if(!isset($_POST['e_token'])) // Set the token if not included
		{
			$_POST['e_token'] = '';
		}

		if(!e107::getSession()->check(false) || !$this->checkPerm($_POST['post'], 'post'))
		{
			// Invalid token.
			exit;
		}

		if(varset($_POST['action']) == 'quickreply' && vartrue($_POST['text']))
		{

			$postInfo = array();
			$postInfo['post_ip'] = e107::getIPHandler()->getIP(false);

			if(USER)
			{
				$postInfo['post_user'] = USERID;
			}
			else
			{
				$postInfo['post_user_anon'] = $_POST['anonname'];
			}

			$postInfo['post_entry'] = $_POST['text'];
			$postInfo['post_forum'] = intval($_POST['post']);
			$postInfo['post_datestamp'] = time();
			$postInfo['post_thread'] = intval($_POST['thread']);

			$postInfo['post_id'] = $this->postAdd($postInfo); // save it.

			$postInfo['user_name'] = USERNAME;
			$postInfo['user_email'] = USEREMAIL;
			$postInfo['user_image'] = USERIMAGE;
			$postInfo['user_signature'] = USERSIGNATURE;

			if($_POST['insert'] == 1)
			{
				$tmpl = e107::getTemplate('forum', 'forum_viewtopic', 'replies');
				$sc = e107::getScBatch('view', 'forum');
				$sc->setScVar('postInfo', $postInfo);
				$ret['html'] = $tp->parseTemplate($tmpl, true, $sc) . "\n";
			}
			else
			{
				$ret['html'] = false;
			}

			$ret['status'] = 'ok';
			$ret['msg'] = LAN_FORUM_3047; 
		}

		e107::getSession()->reset();

		if(varset($ret, false))
		{
			$ret['e_token'] = e107::getSession()->getFormToken();
		}

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

		$threadID = intval($_POST['thread']);

		if(!USER || empty($threadID))
		{
			exit;
		}

		$trackByEmail = ($this->prefs->get('trackemail',true)) ? true : false;

		$sql = e107::getDb();

		if($sql->select('forum_track', '*', "track_userid=".USERID." AND track_thread=".$threadID))
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
	
	
	public function ajaxModerate()
	{
		$ret = array('hide' => false, 'msg' => 'unkown', 'status' => 'error');
		$modArray = array();

		// get moderator-class for the thread to check permissions of the user
		if (isset($_POST['thread']))
		{
			$threadId = intval($_POST['thread']);

			$sql = e107::getDb();
			$query = "SELECT f.forum_moderators
	                  FROM #forum AS f
	                  INNER JOIN #forum_thread AS ft ON f.forum_id = ft.thread_forum_id
	                  WHERE ft.thread_id = ". $threadId;
			$sql->gen($query);
			$row = $sql->fetch();
			$modArray = $this->forumGetMods($row[forum_moderators]);
		}

		// Check if user has moderator permissions for this thread
		if(!in_array(USERID, array_keys($modArray)))
		{
			$ret['msg'] 	= ''.LAN_FORUM_8030.' '. json_encode($_POST);
			$ret['hide'] 	= false;
			$ret['status'] 	= 'error';
		}
		else
		{
			switch ($_POST['action']) 
			{
				case 'delete':
					if($this->threadDelete($threadId))
					{
						$ret['msg'] 	= ''.LAN_FORUM_8020.' #'.$threadId;
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
					if(!$postId = vartrue($_POST['post']))
					{
						// echo "No Post";
						// exit;
						$ret['msg'] 	= LAN_FORUM_7008;
						$ret['status'] 	= 'error';		
					}
					
					if($this->postDelete($postId))
					{
						$ret['msg'] 	= ''.LAN_FORUM_8021.' #'.$postId;
						$ret['hide'] 	= true; 
						$ret['status'] 	= 'ok';	
					}
					else
					{
						$ret['msg'] 	= "".LAN_FORUM_8021." #".$postId;
						$ret['status'] 	= 'error';	
					}
				break;
				
				case 'lock':
					if(e107::getDb()->update('forum_thread', 'thread_active=0 WHERE thread_id='.$threadId))
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
					if(e107::getDb()->update('forum_thread', 'thread_active=1 WHERE thread_id='.$threadId))
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
					if(e107::getDb()->update('forum_thread', 'thread_sticky=1 WHERE thread_id='.$threadId))
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
					if(e107::getDb()->update('forum_thread', 'thread_sticky=0 WHERE thread_id='.$threadId))
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
		if($tmp = e107::getCache()->setMD5(e_LANGUAGE.USERCLASS_LIST)->retrieve('forum_perms'))
		{
			e107::getDebug()->log("Using Permlist cache: True");

			$this->permList = e107::unserialize($tmp);

		//	print_a($this->permList);

		}
		else
		{
			e107::getDebug()->log("Using Permlist cache: False");
			$this->_getForumPermList();
			$tmp = e107::serialize($this->permList, false);
			e107::getCache()->setMD5(e_LANGUAGE.USERCLASS_LIST)->set('forum_perms', $tmp);
		}
		unset($tmp);
	}



	public function getForumPermList($what = null)
	{
		if(null !== $what) return (isset($this->permList[$what]) ? $this->permList[$what] : null);
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



	private function _getForumPermList()
	{
		$sql = e107::getDb();

		$this->permList = array();
		$qryList = array();

		$qryList['view'] = "
		SELECT f.forum_id, f.forum_parent
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_class IN (".USERCLASS_LIST.")
		WHERE f.forum_class IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		$qryList['post'] = "
		SELECT f.forum_id, f.forum_parent
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_postclass IN (".USERCLASS_LIST.")
		WHERE f.forum_postclass IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		$qryList['thread'] = "
		SELECT f.forum_id, f.forum_parent
		FROM `#forum` AS f
		LEFT JOIN `#forum` AS fp ON f.forum_parent = fp.forum_id AND fp.forum_threadclass IN (".USERCLASS_LIST.")
		WHERE f.forum_threadclass IN (".USERCLASS_LIST.") AND f.forum_parent != 0 AND fp.forum_id IS NOT NULL
		";

		foreach($qryList as $key => $qry)
		{
			if($sql->gen($qry))
			{
				$tmp = array();
				while($row = $sql->fetch())
				{
					$tmp[$row['forum_id']] = 1;
					$tmp[$row['forum_parent']] = 1;
				}
				ksort($tmp);
				if($key == 'post')
				{
					//echo "<h3>Raw Perms</h3>";
				//	echo "Qry: ".$qryList['post'];
				//	print_a($tmp);
				}
				$this->permList[$key] = array_keys($tmp);
				$this->permList[$key.'_list'] = implode(',', array_keys($tmp));
			}
		}


		// print_a($this->permList);
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
		$e107 = e107::getInstance();
		$sql = e107::getDb();

		$id = (int)$id;
		if($sql->select('forum_track', 'track_thread', 'track_userid = '.$id))
		{
			$ret = array();

			while($row = $sql->fetch())
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

		if($sql->select('forum_post', 'post_id', "post_forum = ".intval($postInfo['post_forum'])." AND post_entry='".$post."' AND post_user = ".USERID." LIMIT 1"))
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
		$postId = $sql->insert('forum_post', $info);

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
//			$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$info['_FIELD_TYPES']['thread_total_replies'] = 'cmd';

			$result = $sql->update('forum_thread', $info);

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
			$result = $sql->update('forum', $info);
		}

		if($result && USER && $addUserPostCount)
		{
			$qry = '
			INSERT INTO `#user_extended` (user_extended_id, user_plugin_forum_posts)
			VALUES ('.USERID.', 1)
			ON DUPLICATE KEY UPDATE user_plugin_forum_posts = IFNULL(user_plugin_forum_posts, 0) + 1
			';
			$result = $sql->gen($qry);
		}


		$this->clearReadThreads($postInfo['post_thread']);

		return $postId;
	}

	/**
	 * Remove threadID from the 'viewed list' list of other users.
	 * @param $threadId
	*/
	private function clearReadThreads($threadId)
	{
		if(empty($threadId))
		{
			return false;
		}

		$sql = e107::getDb();

		$threadId = intval($threadId);

		$query = "UPDATE `#user_extended`
			SET
			user_plugin_forum_viewed = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_plugin_forum_viewed, ','), ',".$threadId.",', ','))
			WHERE
			FIND_IN_SET(".$threadId.", user_plugin_forum_viewed)
		  ";

		$sql->gen($query);

	}



	function threadAdd($threadInfo, $postInfo)
	{

		$info = array();
//		$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];

	//	$threadInfo['thread_sef'] = eHelper::title2sef($threadInfo['thread_name'],'dashl');

		$info['data'] = $threadInfo;


		if($newThreadId = e107::getDb()->insert('forum_thread', $info))
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
			$threadInfo['thread_sef'] = $this->getThreadsef($threadInfo);

			$triggerData                = $info['data'];
			$triggerData['thread_id']   = $newThreadId;
			$triggerData['thread_sef']  = $threadInfo['thread_sef'];
			$triggerData['post_id']     = $newPostId;


			if (e107::getDb()->count('forum_post', '(post_id)', 'WHERE post_user = "'.USERID.'"') > 0)
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
		$threadInfo = $this->threadGet($threadId);
		$oldForumId = $threadInfo['thread_forum_id'];

		//Move thread to new forum, changing thread title if needed
		if(!empty($threadTitle))
		{

			if($titleType == 0)
			{
				//prepend to existing title
				$threadTitle = ", thread_name = CONCAT('{$threadTitle} ', thread_name)";
			}
			else
			{
				//Replace title
				$threadTitle = ", thread_name = '{$threadTitle}' "; // , thread_sef='".eHelper::title2sef($threadTitle,'dashl')."' ";
			}
		}

		$sql->update('forum_thread', "thread_forum_id={$newForumId} {$threadTitle} WHERE thread_id={$threadId}");

		//Move all posts to new forum
		$posts = $sql->update('forum_post', "post_forum={$newForumId} WHERE post_thread={$threadId}");
		$replies = $posts-1;
		if($replies < 0) { $replies = 0; }

		//change thread counts accordingly
		$sql->update('forum', "forum_threads=forum_threads-1, forum_replies=forum_replies-$replies WHERE forum_id={$oldForumId}");
		$sql->update('forum', "forum_threads=forum_threads+1, forum_replies=forum_replies+$replies WHERE forum_id={$newForumId}");

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

		if(e107::getDb()->update('forum_thread', $info)===false)
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

		if(e107::getDb()->update('forum_post', $info)===false)
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
			$qry = '
			SELECT t.*, f.*,
			fp.forum_id AS parent_id, fp.forum_name AS parent_name,
			sp.forum_id AS forum_sub, sp.forum_name AS sub_parent,
			sp.forum_sef AS parent_sef,
			tr.track_userid
			FROM `#forum_thread` AS t
			LEFT JOIN `#forum` AS f ON t.thread_forum_id = f.forum_id
			LEFT JOIN `#forum` AS fp ON fp.forum_id = f.forum_parent
			LEFT JOIN `#forum` AS sp ON sp.forum_id = f.forum_sub
			LEFT JOIN `#forum_track` AS tr ON tr.track_thread = t.thread_id AND tr.track_userid = '.$uid.'
			WHERE thread_id = '.$id;
		}
		else
		{
			$qry = '
			SELECT *
			FROM `#forum_thread`
			WHERE thread_id = '.$id;
		}
		if($sql->gen($qry)!==false)
		{
			$tmp = $sql->fetch();
			if($tmp)
			{
				if(trim($tmp['thread_options']) != '')
				{
					$tmp['thread_options'] = unserialize($tmp['thread_options']);
				}

				$tmp['thread_sef'] = eHelper::title2sef($tmp['thread_name'],'dashl');

				if(empty($tmp['forum_sef']))
				{
					e107::getDebug()->log("Forum ".$tmp['forum_name']." is missing a SEF URL. Please add one via the admin area. ");
				}

				return $tmp;
			}
		}
		else
		{
			e107::getMessage()->addDebug('Query failed ('.__METHOD__.' ): '.str_replace('#', MPREFIX,$qry));
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
			$qry = '
			SELECT u.user_name, t.thread_active, t.thread_datestamp, t.thread_name, t.thread_user, t.thread_id, t.thread_sticky, p.* FROM `#forum_post` AS p
			LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
			LEFT JOIN `#user` AS u ON u.user_id = p.post_user
			WHERE p.post_id = '.$id;
		}
		else
		{
			$qry = "
				SELECT p.*,
				u.user_name, u.user_customtitle, u.user_hideemail, u.user_email, u.user_signature,
				u.user_admin, u.user_image, u.user_join, ue.user_plugin_forum_posts,
				eu.user_name AS edit_name,
				t.thread_name
				FROM `#forum_post` AS p
				LEFT JOIN `#user` AS u ON p.post_user = u.user_id
				LEFT JOIN `#user` AS eu ON p.post_edit_user IS NOT NULL AND p.post_edit_user = eu.user_id
				LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = p.post_user
				LEFT JOIN `#forum_thread` AS t ON t.thread_id = p.post_thread
				WHERE p.post_thread = {$id}
				ORDER BY p.post_datestamp ASC
				LIMIT {$start}, {$num}
			";
		}

		if($sql->gen($qry)!==false)
		{
			$ret = array();
			while($row = $sql->fetch())
			{

				$row['thread_sef'] = $this->getThreadSef($row); // eHelper::title2sef($row['thread_name'],'dashl');

				$ret[] = $row;
			}
		}
		else
		{
			e107::getMessage()->addDebug('Query failed ('.__METHOD__.' ): '.str_replace('#', MPREFIX,$qry));

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
		$threadId = $sql->retrieve('forum_post', 'post_thread', 'post_id = '.$postId);

		if($rows = $sql->retrieve('forum_post', 'post_id', 'post_thread = '.$threadId, TRUE))
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
		$qry = "
		SELECT post_user, count(post_user) AS post_count FROM `#forum_post`
		WHERE post_thread = {$threadId} AND post_user IS NOT NULL
		GROUP BY post_user
		";
		if($sql->gen($qry))
		{
			$ret = array();
			while($row = $sql->fetch())
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
			$viewed = $e107->currentUser['user_plugin_forum_viewed'];
		}
		else
		{
			$tmp = e107::user($uid);
			$viewed = $tmp['user_plugin_forum_viewed'];
			unset($tmp);
		}
		return explode(',', $viewed);
	}


	function postDeleteAttachments($type = 'post', $id = '') // postDeleteAttachments($type = 'post', $id='', $f='')
	{
		$e107 = e107::getInstance();
		$sql  = e107::getDb();
		$log  = e107::getAdminLog(); 

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
			if(!$sql->select('forum_post', 'post_user, post_attachments', 'post_id = '.$id))
			{
				return true;
			}

			$tmp = $sql->fetch();

			$attachment_array = e107::unserialize($tmp['post_attachments']);
	   		$files = $attachment_array['file'];
	   		$imgs  = $attachment_array['img']; 
	   		
	   		// TODO see if files/images check can be written more efficiently 
	   		// check if there are files to be deleted 
	   		if(is_array($files))
	   		{
		   		// loop through each file and delete it
		   		foreach ($files as $file) 
		   		{
		   			$file = $this->getAttachmentPath($tmp['post_user']).$file;
		   			@unlink($file);

	   				// Confirm that file has been deleted. Add warning to log file when file could not be deleted.
		   			if(file_exists($file))
		   			{
		   				$log->addWarning("Could not delete file: ".$file.". Please delete manually as this file is now no longer in use (orphaned).");
		   			}
		   		} 
	   		}
	   		
	   		// check if there are images to be deleted
	   		if(is_array($imgs))
	   		{
	   			// loop through each image and delete it
		   		foreach ($imgs as $img) 
		   		{
		   			$img = $this->getAttachmentPath($tmp['post_user']).$img;
		   			@unlink($img);

	   				// Confirm that file has been deleted. Add warning to log file when file could not be deleted.
		   			if(file_exists($img))
		   			{
		   				$log->addWarning("Could not delete image: ".$img.". Please delete manually as this file is now no longer in use (orphaned).");
		   			}
		   		} 	
	   		}

	   		// At this point we assume that all attachments have been deleted from the post. The log file may prove otherwise (see above). 
	   		$log->toFile('forum_delete_attachments', 'Forum plugin - Delete attachments', TRUE);

	   		// Empty the post_attachments field for this post in the database (prevents loop when deleting entire thread)
	   		$sql->update("forum_post", "post_attachments = NULL WHERE post_id = ".$id);

	    		
			/* Old code when attachments were still stored in plugin folder. 
			Left for review but may be deleted in future.  

			foreach($attachments as $k => $a)
			{
				$info = explode('*', $a);
				if('' == $f || $info[1] == $f)
				{
					$fname = e_PLUGIN."forum/attachments/{$info[1]}";
					@unlink($fname);

					//If attachment is an image and there is a thumb, remove it
					if('img' == $info[0] && $info[2])
					{
						$fname = e_PLUGIN."forum/attachments/thumb/{$info[2]}";
						@unlink($fname);
					}
				}
				unset($attachments[$k]);
			}

			$tmp = array();
			if(count($attachments))
			{
				$tmp['post_attachments'] = implode(',', $attachments);
			}
			else
			{
				$tmp['post_attachments'] = '_NULL_';
			}

			$info = array();
			$info['data'] = $tmp;
			$info['_FILE_TYPES']['post_attachments'] = 'array';
			$info['WHERE'] = 'post_id = '.$id;
			$sql->update('forum_post', $info);

			*/
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
		return e107::getDb()->count('forum_post', '(*)', "WHERE post_id <= {$postId} AND post_thread = {$threadId} ORDER BY post_id ASC");
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
			$info = array();
			$info['data'] = $tmp;
//			$info['_FIELD_TYPES'] = $this->fieldTypes['forum_thread'];
			$info['WHERE'] = 'thread_id = '.$id;

			$sql->update('forum_thread', $info);

			return $lpInfo;
		}



		if ($type == 'forum')
		{
			if ($id == 'all')
			{
				if ($sql->select('forum', 'forum_id', 'forum_parent != 0'))
				{
					while ($row = $sql->fetch())
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
					if ($sql2->select('forum_thread', 'thread_id', "thread_forum_id = $id"))
					{
						while ($row = $sql2->fetch())
						{
							set_time_limit(60);
							$this->forumUpdateLastpost('thread', $row['thread_id']);
						}
					}
				}
				if ($sql->select('forum_thread', 'thread_id, thread_lastuser, thread_lastuser_anon, thread_datestamp', 'thread_forum_id='.$id.' ORDER BY thread_datestamp DESC LIMIT 1'))
				{
					$row = $sql->fetch();
					$lp_info = $row['thread_datestamp'].'.'.$row['thread_id'];
					$lp_user = $row['thread_lastuser'];
				}
				if($row['thread_lastuser_anon'])
				{
					$sql->update('forum', "forum_lastpost_user = 0, forum_lastpost_user_anon = '{$row['thread_lastuser_anon']}', forum_lastpost_info = '{$lp_info}' WHERE forum_id=".$id);
				}
				else
				{
					$sql->update('forum', "forum_lastpost_user = {$lp_user}, forum_lastpost_user_anon = NULL, forum_lastpost_info = '{$lp_info}' WHERE forum_id=".$id);
				}
			}
		}
	}

	
	
	function forumMarkAsRead($forum_id)
	{
		$sql = e107::getDb();
		$extra = '';
		$newIdList = array();
		if ($forum_id !== 0)
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
			$forumList = implode(',', $flist);
			$extra = " AND thread_forum_id IN($forumList)";
		}
		$qry = 'thread_lastpost > '.USERLV.$extra;

		if ($sql->select('forum_thread', 'thread_id', $qry))
		{
			while ($row = $sql->fetch())
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
			$forum_sef = e107::getDb()->retrieve('forum', 'forum_sef', 'WHERE forum_id='.$forum_id);
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
		return e107::getDb()->update('user_extended', "user_plugin_forum_viewed = '{$viewed}' WHERE user_extended_id = ".USERID);
	}



	function forum_getparents()
	{
		if (e107::getDb()->select('forum', '*', 'forum_parent=0 ORDER BY forum_order ASC'))
		{
			while ($row = e107::getDb()->fetch()) {
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
			$sql->select('user', 'user_id, user_name','user_admin = 1 ORDER BY user_name ASC');
			while($row = $sql->fetch())
			{
				$this->modArray[$row['user_id']] = $row;
			}
		}
		else
		{
			$this->modArray = e107::getUserClass()->get_users_in_class($uclass, 'user_name', true);
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

		$where = '';
		if(!empty($this->permList['view_list']))
		{
			$where = ($all ? '' : " WHERE forum_id IN ({$this->permList['view_list']}) ");
		}

		$qry = 'SELECT f.*, u.user_name FROM `#forum` AS f
		LEFT JOIN `#user` AS u ON f.forum_lastpost_user IS NOT NULL AND u.user_id = f.forum_lastpost_user
		'.$where.'ORDER BY f.forum_order ASC';
		if ($sql->gen($qry))
		{
			$ret = array();
			while ($row = $sql->fetch())
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
		$qry = "
		SELECT f.*, u.user_name FROM #forum AS f
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(f.forum_lastpost_user,'.',1) = u.user_id
		WHERE forum_parent != 0 AND forum_sub = 0
		ORDER BY f.forum_order ASC
		";
		if ($sql->gen($qry))
		{
			while ($row = $sql->fetch())
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
		$where = ($forum_id != '' && $forum_id != 'bysub' ? 'AND forum_sub = '.(int)$forum_id : '');
		$qry = "
		SELECT f.*, u.user_name FROM `#forum` AS f
		LEFT JOIN `#user` AS u ON f.forum_lastpost_user = u.user_id
		WHERE forum_sub != 0 {$where}
		ORDER BY f.forum_order ASC
		";
		if ($sql->gen($qry))
		{
			while ($row = $sql->fetch())
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
	* @return 	type	description
	* @access 	public
	*/
	function forumGetUnreadForums()
	{
		if (!USER) {return false; }		// Can't determine new threads for non-logged in users
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$viewed = '';

		if($e107->currentUser['user_plugin_forum_viewed'])
		{
			$viewed = " AND thread_id NOT IN (".$e107->currentUser['user_plugin_forum_viewed'].")";
		}

		$_newqry = 	'
		SELECT DISTINCT f.forum_sub, ft.thread_forum_id FROM `#forum_thread` AS ft
		LEFT JOIN `#forum` AS f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_lastpost > '.USERLV.' '.$viewed;

		$ret = array();

	//	e107::getDebug()->log(e107::getParser()->toDate(USERLV,'relative'));

		if($sql->gen($_newqry))
		{
			while($row = $sql->fetch())
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
				$tmp = array();
				$tmp['data']['track_userid'] = $uid;
				$tmp['data']['track_thread'] = $threadId;
				$result = $sql->insert('forum_track', $tmp);
				unset($tmp);
				break;

			case 'delete':
			case 'del':
			 	$result = $sql->delete('forum_track', "`track_userid` = {$uid} AND `track_thread` = {$threadId}");
			 	break;

			case 'check':
				$result = $sql->count('forum_track', '(*)', "WHERE `track_userid` = {$uid} AND `track_thread` = {$threadId}");
				break;
		}
		return $result;
	}


	/**
	 * Send an email to users who are tracking the topic/thread.
	* @param $post
	 * @return bool
	*/
	function trackEmail($post)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$trackingPref = $this->prefs->get('track');
		$trackingEmailPref = $this->prefs->get('trackemail',true);

		if(empty($trackingPref) || empty($trackingEmailPref))
		{
			return false;
		}

		$data = $sql->retrieve('SELECT t.*, u.user_id, u.user_name, u.user_email, u.user_lastvisit FROM `#forum_track` AS t LEFT JOIN `#user` AS u ON t.track_userid = u.user_id WHERE t.track_thread='.intval($post['post_thread']), true);

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
		$qry = "
		SELECT f.*, fp.forum_class as parent_class, fp.forum_name as parent_name,
		fp.forum_id as parent_id, fp.forum_postclass as parent_postclass,
		sp.forum_name AS sub_parent, sp.forum_sef AS parent_sef
		FROM #forum AS f
		LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
		LEFT JOIN #forum AS sp ON f.forum_sub = sp.forum_id AND f.forum_sub > 0
		WHERE f.forum_id = {$forum_id}
		";
		if ($sql->gen($qry))
		{

			$row =  $sql->fetch();

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
		$sql = e107::getDb();
		$forumList = implode(',', $this->permList[$type]);
		$qry = "
		SELECT forum_id, forum_name, forum_sef FROM `#forum`
		WHERE forum_id IN ({$forumList}) AND forum_parent != 0
		";
		if ($sql->gen($qry))
		{
			while($row = $sql->fetch())
			{
				$ret[$row['forum_id']] = $row;
			}

		}
		return $ret;
	}


	/**
	 * @param $forumId
	 * @param $from
	 * @param $view
	 * @return array
	 */
	function forumGetThreads($forumId, $from, $view, $filter = null)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$forumId = (int)$forumId;
		$qry = "
		SELECT t.*, f.forum_id, f.forum_sef,f.forum_name, u.user_name, lpu.user_name AS lastpost_username, MAX(p.post_id) AS lastpost_id FROM `#forum_thread` as t
		LEFT JOIN `#forum` AS f ON t.thread_forum_id = f.forum_id
		LEFT JOIN `#forum_post` AS p ON t.thread_id = p.post_thread
		LEFT JOIN `#user` AS u ON t.thread_user = u.user_id
		LEFT JOIN `#user` AS lpu ON t.thread_lastuser = lpu.user_id
		WHERE t.thread_forum_id = {$forumId}
		";

		if(!empty($filter))
		{
			$qry .= " AND ".$filter;
		}

		$qry .= "
		GROUP BY thread_id
		ORDER BY
		t.thread_sticky DESC,
		t.thread_lastpost DESC
		LIMIT ".(int)$from.','.(int)$view;

		$ret = array();
		if ($sql->gen($qry))
		{
			while ($row = $sql->fetch())
			{
				if(empty($row['forum_sef']))
				{
					e107::getDebug()->log("Forum ".$row['forum_name']." is missing a SEF URL. Please add one via the admin area. ");
				}

				$ret[] = $row;
			}
		}
		return $ret;
	}


	function threadGetLastpost($id)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$id = (int)$id;
		$qry = "
		SELECT p.post_user, p.post_id, p.post_user_anon, p.post_datestamp, p.post_thread, t.thread_name, u.user_name FROM `#forum_post` AS p
		LEFT JOIN `#forum_thread` AS t ON p.post_thread = t.thread_id
		LEFT JOIN `#user` AS u ON u.user_id = p.post_user
		WHERE p.post_thread = {$id}
		ORDER BY p.post_datestamp DESC LIMIT 0,1
		";
		if ($sql->gen($qry))
		{
			$row = $sql->fetch();
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


		$qry = "
			SELECT t.thread_id, t.thread_name, f.forum_id, f.forum_sef FROM `#forum_thread` AS t
			LEFT JOIN `#forum` AS f ON t.thread_forum_id = f.forum_id
			WHERE t.thread_forum_id = $forumId
			AND t.thread_lastpost {$dir} $lastpost
			ORDER BY
			t.thread_sticky DESC,
			t.thread_lastpost ASC
			LIMIT 1";

	//		e107::getMessage()->addDebug(ucfirst($which)." Thread Qry: ".$qry);

			if ($sql->gen($qry))
			{
				$row = $sql->fetch();
				$row['thread_sef'] = eHelper::title2sef($row['thread_name'],'dashl');
		//		e107::getMessage()->addInfo(ucfirst($which).print_a($row,true));
				return $row;
			//	return $row['thread_id'];

			}
			else
			{
			//	e107::getMessage()->addDebug(ucfirst($which)." Thread Qry Returned Nothing: ".$qry);
			}

			return false;
	}


	function threadIncView($id)
	{
		$id = (int)$id;
		return e107::getDb()->update('forum_thread', 'thread_views=thread_views+1 WHERE thread_id='.$id);
	}



	function _forum_lp_update($lp_type, $lp_user, $lp_info, $lp_forum_id, $lp_forum_sub)
	{
		$sql = e107::getDb();
		$sql->update('forum', "{$lp_type}={$lp_type}+1, forum_lastpost_user='{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id='".intval($lp_forum_id)."' ");
		if($lp_forum_sub)
		{
			$sql->update('forum', "forum_lastpost_user = '{$lp_user}', forum_lastpost_info = '{$lp_info}' WHERE forum_id='".intval($lp_forum_sub)."' ");
		}
	}




	function threadGetNew($count = 50, $unread = true, $uid = USERID)
	{
		$sql = e107::getDb();
		$viewed = '';
		if($unread)
		{
			$viewed = implode(',', $this->threadGetUserViewed($uid));
			if($viewed != '')
			{
				//$viewed = ' AND p.post_forum NOT IN ('.$viewed.')';
				$viewed = " AND t.thread_forum_id NOT IN ({$viewed})";
			}
		}
		/*
		$qry = "
		SELECT ft.*, fp.thread_name as post_subject, fp.thread_total_replies as replies, u.user_id, u.user_name, f.forum_class
		FROM #forum_t AS ft
		LEFT JOIN #forum_thread as fp ON fp.thread_id = ft.thread_parent
		LEFT JOIN #user as u ON u.user_id = SUBSTRING_INDEX(ft.thread_user,'.',1)
		LEFT JOIN #forum as f ON f.forum_id = ft.thread_forum_id
		WHERE ft.thread_datestamp > ".USERLV. "
		AND f.forum_class IN (".USERCLASS_LIST.")
		{$viewed}
		ORDER BY ft.thread_datestamp DESC LIMIT 0, ".intval($count);

		$qry = "
		SELECT t.*, u.user_name FROM `#forum_thread` AS t
		LEFT JOIN `#user` AS u ON u.user_id = t.thread_lastuser
		WHERE t.thread_lastpost > ".USERLV. "
		{$viewed}
		ORDER BY t.thread_lastpost DESC LIMIT 0, ".(int)$count;
		*/

		//  issue #3337 fixed usage of old v1 table names
		$qry = "SELECT t.*, u.user_name 
		FROM `#forum_thread` AS t
		LEFT JOIN `#user` AS u ON u.user_id = t.thread_lastuser
		WHERE t.thread_lastpost > ".USERLV. "
		{$viewed}
		ORDER BY t.thread_lastpost DESC LIMIT 0, ".(int)$count;

		$ret = array();
		if($sql->gen($qry))
		{
			$ret = $sql->db_getList();
		}
		return $ret;
	}


	function forumPrune($type, $days, $forumArray)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		$tp = e107::getParser();

		$prunedate = time() - (int)$days * 86400;
		$forumList = implode(',', $tp->filter($forumArray,'int'));

		if($type == 'delete')
		{
			//Get list of threads to prune
			if ($sql->select('forum_thread', 'thread_id', "thread_lastpost < {$prunedate} AND thread_sticky != 1 AND thread_forum_id IN ({$forumList})"))
			{
				$threadList = $sql->db_getList();
				$thread_count = count($threadList);
				$reply_count = 0;
				foreach($threadList as $thread)
				{
					$reply_count += (int)$sql->count('forum_post', '(*)', 'WHERE post_thread = '.$thread['thread_id']);
					$this->threadDelete($thread['thread_id'], false);
				}
				foreach($forumArray as $fid)
				{
					$this->forumUpdateLastpost('forum', $fid);
					$this->forumUpdateCounts($fid);
				}
				return FORLAN_8." ( ".$thread_count." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
				return FORLAN_8." ( ".count($threadList)." ".FORLAN_92.", ".$reply_count." ".FORLAN_93." )";
			}
			else
			{
				return FORLAN_9;
			}
		}
		if($type == 'make_inactive')
		{
			$pruned = $sql->update('forum_thread', "thread_active=0 WHERE thread_lastpost < {$prunedate} thread_forum_id IN ({$forumList})");
			return FORLAN_8.' '.$pruned.' '.FORLAN_91;
		}
	}


	function forumUpdateCounts($forumId, $recalcThreads = false)
	{
		$e107 = e107::getInstance();
		$sql = e107::getDb();
		if($forumId == 'all')
		{
			$sql->select('forum', 'forum_id', 'forum_parent != 0');
			$flist = $sql->db_getList();
			foreach($flist as $f)
			{
				set_time_limit(60);
				$this->forumUpdateCounts($f['forum_id'], $recalcThreads);
			}
			return;
		}
		$forumId = (int)$forumId;
		$threads = $sql->count('forum_thread', '(*)', 'WHERE thread_forum_id='.$forumId);
		$replies = $sql->count('forum_post', '(*)', 'WHERE post_forum='.$forumId);
		$sql->update('forum', "forum_threads={$threads}, forum_replies={$replies} WHERE forum_id={$forumId}");
		if($recalcThreads == true)
		{
			set_time_limit(60);
			$sql->select('forum_post', 'post_thread, count(post_thread) AS replies', "post_forum={$forumId} GROUP BY post_thread");
			$tlist = $sql->db_getList();
			foreach($tlist as $t)
			{
				$tid = $t['post_thread'];
				$replies = (int)$t['replies'];
				$sql->update('forum_thread', "thread_total_replies={$replies} WHERE thread_id={$tid}");
			}
		}
	}


	/**
	 * @param $threadID
	 * @return int
	 */
	function threadUpdateCounts($threadID)
	{
		$sql = e107::getDb();

		$replies = $sql->count('forum_post', '(*)', 'WHERE post_thread='.$threadID);

		return $sql->update('forum_thread', "thread_total_replies={$replies} WHERE thread_id=".$threadID);

	}




	function getUserCounts()
	{
		$sql = e107::getDb();
		$qry = "
		SELECT post_user, count(post_user) AS cnt FROM `#forum_post`
		WHERE post_user > 0
		GROUP BY post_user
		";

		if($sql->gen($qry))
		{
			$ret = array();
			while($row = $sql->fetch())
			{
				$ret[$row['post_user']] = $row['cnt'];
			}
			return $ret;
		}
		return FALSE;
	}



// Function eventually to be reworked (move full function to shortcode file, or make a new breadcrumb function, like in downloads, maybe?)
	/*
	 * set bread crumb
	 * $forum_href override ONLY applies when template is missing FORUM_CRUMB
	 * $thread_title is needed for post-related breadcrumbs
	 */
	function set_crumb($forum_href=false, $thread_title='', &$templateVar)
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
				$replace 	= array(ltrim($forumInfo['sub_parent'], '*'), e107::url('forum', 'forum', array('forum_id'=>$forumInfo['parent_id'],'forum_sef'=>$forumInfo['parent_sef'])));
				$FORUM_CRUMB['subparent']['value'] = str_replace($search, $replace, $FORUM_CRUMB['subparent']['value']);
			}
			else
			{
				$FORUM_CRUMB['subparent']['value'] = '';
			}

			$search 	= array('{FORUM_TITLE}', '{FORUM_HREF}');
			$replace 	= array(ltrim($forumInfo['forum_name'], '*'), e107::url('forum', 'forum', $forumInfo));
			$FORUM_CRUMB['forum']['value'] = str_replace($search, $replace, $FORUM_CRUMB['forum']['value']);

			$threadInfo['thread_id'] = intval($threadInfo['thread_id']);
			$search 	= array('{THREAD_TITLE}', '{THREAD_HREF}');
			$replace 	= array(vartrue($threadInfo['thread_name']), ''); // $thread->threadInfo - no reference found
			$FORUM_CRUMB['thread']['value'] = str_replace($search, $replace, $FORUM_CRUMB['thread']['value']);

			$FORUM_CRUMB['fieldlist'] = 'sitename,forums,parent,subparent,forum,thread';

			$BREADCRUMB = $tp->parseTemplate('{BREADCRUMB=FORUM_CRUMB}', true);
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
			$breadcrumb[]	= array('text'=> ltrim($forumInfo['sub_parent'], '*')		, 'url'=> e107::url('forum','forum', array('forum_sef'=> $forumInfo['parent_sef'])));
			$breadcrumb[]	= array('text'=>ltrim($forumInfo['forum_name'], '*')		, 'url'=> (e_PAGE !='forum_viewforum.php') ? e107::url('forum', 'forum', $forumInfo) : null);

		}
		else
		{
			$breadcrumb[]	= array('text'=>ltrim($forumInfo['forum_name'], '*')		, 'url'=> (e_PAGE !='forum_viewforum.php') ? e107::url('forum', 'forum', $forumInfo) : null);

		}

		if(vartrue($forumInfo['thread_name']))
		{
			$breadcrumb[]	= array('text'=> $forumInfo['thread_name'] , 'url'=>null);
		}
		
		
		if(deftrue('BOOTSTRAP'))
		{
			$BREADCRUMB =  $frm->breadcrumb($breadcrumb);
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
	 * @param $threadId integer
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
			// delete poll if there is one
			if($sql->select('polls', '*', 'poll_datestamp='.$threadId))
			{
				$sql->delete('polls', 'poll_datestamp='.$threadId);
			} 
	
			// decrement user post counts
			if ($postCount = $this->threadGetUserPostcount($threadId))
			{
				foreach ($postCount as $k => $v)
				{
					$sql->update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-'.$v.',0) WHERE user_extended_id='.$k);
				}
			}

			// delete all posts
			if($sql->select('forum_post', 'post_id', 'post_thread = '.$threadId))
			{
				$postList = array();
				while($row = $sql->fetch())
				{
					$postList[] = $row['post_id'];
				}

				foreach($postList as $postId)
				{
					$this->postDelete($postId, false);
				}
			}

			// delete the thread itself
			if($sql->delete('forum_thread', 'thread_id='.$threadId))
			{
				$status = true;
			  	e107::getEvent()->trigger('user_forum_topic_deleted', $threadInfo);
			}

			//Delete any thread tracking
			if($sql->select('forum_track', '*', 'track_thread='.$threadId))
			{	
				$sql->delete('forum_track', 'track_thread='.$threadId);
			}
			
			// update forum with correct thread/reply counts
			$sql->update('forum', "forum_threads=GREATEST(forum_threads-1,0), forum_replies=GREATEST(forum_replies-{$threadInfo['thread_total_replies']},0) WHERE forum_id=".$threadInfo['thread_forum_id']);

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
	 * @param $postId integer
	 * @param $updateCounts boolean
	 * 
	 */
	function postDelete($postId, $updateCounts = true)
	{
		$postId 	= (int)$postId;
		$e107 		= e107::getInstance();		
		$sql 		= e107::getDb();
		$deleted 	= false;

		$postInfo   = $sql->retrieve('forum_post', '*', 'post_id = '.$postId);
		//if(!$sql->select('forum_post', '*', 'post_id = '.$postId))
		if(!is_array($postInfo) || empty($postInfo))
		{
			echo 'NOT FOUND!'; return;
		}
		

		$row = $sql->fetch();

		//delete attachments if they exist
		if($row['post_attachments'])
		{
			$this->postDeleteAttachments('post', $postId);
		}

		// delete post from database
		if($sql->delete('forum_post', 'post_id='.$postId))
		{
			$deleted = true;
		  	e107::getEvent()->trigger('user_forum_post_deleted', $postInfo);
		}

		// update statistics
		if($updateCounts)
		{
			// decrement user post counts
			if ($row['post_user'])
			{
				$sql->update('user_extended', 'user_plugin_forum_posts=GREATEST(user_plugin_forum_posts-1,0) WHERE user_extended_id='.$row['post_user']);
			}

			// update thread with correct reply counts
			$sql->update('forum_thread', "thread_total_replies=GREATEST(thread_total_replies-1,0) WHERE thread_id=".$row['post_thread']);

			// update forum with correct thread/reply counts
			$sql->update('forum', "forum_replies=GREATEST(forum_replies-1,0) WHERE forum_id=".$row['post_forum']);

			// update thread lastpost info
			$this->forumUpdateLastpost('thread', $row['post_thread']);

			// update forum lastpost info
			$this->forumUpdateLastpost('forum', $row['post_forum']);
		}
		return $deleted; // return boolean. $threadInfo['thread_total_replies'];
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
	$ML = (in_array($filename,$multilang)) ? TRUE : FALSE;

		if(file_exists(THEME.'forum/'.$filename) || is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename))
		{
			$image = ($ML && is_readable(THEME.'forum/'.e_LANGUAGE.'_'.$filename)) ? THEME_ABS.'forum/'.e_LANGUAGE."_".$filename :  THEME_ABS.'forum/'.$filename;
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




?>
