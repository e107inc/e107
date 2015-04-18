<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum Posting
 *
*/

require_once('../../class2.php');
define('NAVIGATION_ACTIVE','forum'); // ??

$e107 = e107::getInstance();
$tp = e107::getParser();
$ns = e107::getRender();
$mes = e107::getMessage();

if (!$e107->isInstalled('forum'))
{
	header('Location: '.SITEURL.'index.php');
	exit;
}

e107::lan('forum','English_front');





class forum_post_handler
{
	private $forumObj;
	private $action;
	private $id;
	private $data;

	function __construct()
	{
		$this->checkForumJump();

		require_once(e_PLUGIN.'forum/forum_class.php'); // includes LAN file.
		$forum = new e107forum();
		$this->forumObj = $forum;

		$this->action   = trim($_GET['f']); // action: rp|quote|nt|edit etc.
		$this->id       = (int) $_GET['id']; // forum thread/topic id.
		$this->post     = (int) $_GET['post']; // post ID if needed.

		define('MODERATOR', USER && $this->forumObj->isModerator(USERID));

		$this->checkPerms($this->id);
		$this->data = $this->processGet();
		$this->processPosted();
		$this->renderForm();

	}


	function checkForumJump()
	{
		if(isset($_POST['fjsubmit']))
		{
			header('Location:'.e107::getUrl()->create('forum/forum/view', array('id'=>(int) $_POST['forumjump']), '', 'full=1&encode=0'));
			exit;
		}

		if (!e_QUERY || empty($_GET['id']))
		{
			header('Location:'.e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
			exit;
		}

	}


	/**
	 * Handle all _GET request actions.
	 */
	function processGet()
	{
		switch($this->action)
		{
			case 'rp':
				$thread                 = $this->forumObj->threadGet($this->id, false);
				$extra                  = $this->forumObj->forumGet($thread['thread_forum_id']);
				$data                   = array_merge($thread,$extra);
				$data['action']         = $this->action;
				$this->setPageTitle($data);
				return $data;
				break;

			case 'nt':
				$forumInfo              = $this->forumObj->forumGet($this->id);
				$forumInfo['action']    = $this->action;
				$this->setPageTitle($forumInfo);
				return $forumInfo;
				break;

			case 'edit':
			case 'quote':
				$postInfo               = $this->forumObj->postGet($this->post, 'post');
				$forumInfo              = $this->forumObj->forumGet($postInfo['post_forum']);
				$data                   = array_merge($postInfo ,$forumInfo);
				$data['action']         = $this->action;
				$this->setPageTitle($data);
				return $data;
				break;

			default:
				header("Location:".e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
				exit;
		}
	}


	/**
	 * Handle all _POST actions.
	 */
	function processPosted()
	{

		if(!empty($_POST['action'])) // override from 'quote' mode to 'rp' mode.
		{
			$this->action = $_POST['action'];
		}

		if(isset($_POST['newthread']) || isset($_POST['reply']))
		{
			$this->insertPost();
		}

		if(isset($_POST['update_thread']))
		{
			$this->updateThread();
		}

		if(isset($_POST['update_reply']))
		{
			$this->updateReply();
		}

		if(!empty($_POST['fpreview']))
		{
			$this->renderPreview();
		}

		if (isset($_POST['submitpoll']))
		{
			$this->submitPoll();
		}


	}


	/**
	 * @param $url
	 */
	private function redirect($url)
	{
		e107::getRedirect()->go($url);

	}


	/**
	 *
	 */
	function submitPoll()
	{

		require_once(e_PLUGIN.'poll/poll_class.php');
		$poll = new poll;

		require_once(HEADERF);
		$template = $this->getTemplate('posted');
		echo $template['poll'];
		require_once(FOOTERF);
		exit;

	}



	function setPageTitle($data)
	{
		$tp = e107::getParser();

		$data['forum_name'] = $tp->toHTML($data['forum_name'], true);

		define('e_PAGETITLE', ($this->action == 'rp' ? LAN_FORUM_3003.": ".$data['thread_name'] : LAN_FORUM_1018).' / '.$data['forum_name'].' / '.LAN_FORUM_1001);


	}


	function checkPerms($forumId)
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();

		if (!$this->forumObj->checkPerm($forumId, 'post')) // check user has permission to post to this thread.
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3001);
		//	$mes->addDebug("action: ".$this->action);
		//	$mes->addDebug("id: ".$this->id);
		//	$mes->addDebug(print_a($threadInfo, true));
			$ns->tablerender(LAN_FORUM_1001, $mes->render());
			require_once(FOOTERF);
			exit;
		}

		$data  = $this->forumObj->threadGet($this->id, false);

		if ($this->action != 'nt' && !$data['thread_active'] && !MODERATOR) // check the thread is active.
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3002);
			$ns->tablerender(LAN_FORUM_1001, $mes->render());
			require_once(FOOTERF);
			exit;
		}



	}


	/**
	 * @return string
	 */
	function getTemplate($type = 'post')
	{

		global $FORUMPOST;

		$FORUM_POST_TEMPLATE        = array();
		$FORUM_POSTED_TEMPLATE      = array();
		$FORUMREPLYPOSTED           = '';
		$FORUMTHREADPOSTED          = '';
		$FORUMPOLLPOSTED            = '';

		$file = "forum_".$type."_template.php";

		if (empty($FORUMPOST) && empty($FORUMREPLYPOSTED) && empty($FORUMTHREADPOSTED))
		{
			if (is_readable(THEME.$file))
			{
				include_once(THEME.$file);
			}
			else
			{
				include_once(e_PLUGIN.'forum/templates/'.$file);
			}
		}

		if($type == 'post')
		{
			return (deftrue('BOOTSTRAP')) ? $FORUM_POST_TEMPLATE : array('form'=>$FORUMPOST);
		}
		else
		{
			if (deftrue('BOOTSTRAP')) //v2.x
			{
				return $FORUM_POSTED_TEMPLATE;
			}
			else //v1.x
			{
				return array(
					 "reply"    => $FORUMREPLYPOSTED,
					 "thread"   => $FORUMTHREADPOSTED,
					 "poll"     => $FORUMPOLLPOSTED
				);

			}


		}



	}



	function renderForm()
	{
		$data       = $this->data;
		$template   = $this->getTemplate();
		$sc         = e107::getScBatch('post', 'forum')->setScVar('forum', $this->forumObj)->setScVar('threadInfo', vartrue($data))->setVars($data);
		$text       = e107::getParser()->parseTemplate($template['form'], true, $sc);

		$this->render($text);

		if(empty($data))
		{
			e107::getMessage()->addError("No Data supplied");
		}


		if(E107_DEBUG_LEVEL > 0)
		{
			e107::getMessage()->addInfo(print_a($data,true));
			echo e107::getMessage()->render();
		}

		require_once(FOOTERF);
		exit;

	}


	/**
	 * @param $text
	 */
	function render($text)
	{
		$ns = e107::getRender();

		if ($this->forumObj->prefs->get('enclose'))
		{
			$ns->tablerender($this->forumObj->prefs->get('title'), $text);
		}
		else
		{
			echo $text;
		}


	}


	/**
	 *
	 */
	function renderPreview()
	{
		global $FORUM_PREVIEW; // BC v1.x

		$tp = e107::getParser();
		$ns = e107::getRender();

		process_upload();

		require_once(HEADERF);
		if (USER)
		{
			$poster = USERNAME;
		}
		else
		{
			$poster = ($_POST['anonname']) ? $_POST['anonname'] : LAN_ANONYMOUS;
		}

		$postdate = e107::getDate()->convert_date(time(), "forum");
		$tsubject = $tp->post_toHTML($_POST['subject'], true);
		$tpost = $tp->post_toHTML($_POST['post'], true);

		if ($_POST['poll_title'] != '' && check_class($this->forumObj->prefs->get('poll')))
		{
			require_once(e_PLUGIN."poll/poll_class.php");
			$poll = new poll;
			$poll_text = $poll->render_poll($_POST, 'forum', 'notvoted', true);
		}
		else
		{
			$poll_text = false;
		}

		if (empty($FORUM_PREVIEW))
		{

			if(deftrue('BOOTSTRAP')) //v2.x
			{
				$FORUM_PREVIEW = e107::getTemplate('forum','forum_preview', 'item');
			}
			else //1.x
			{
				if (file_exists(THEME."forum_preview_template.php"))
				{
					require_once(THEME."forum_preview_template.php");
				}
				else
				{
					require_once(e_PLUGIN."forum/templates/forum_preview_template.php");
				}
			}

		}

		$shortcodes = array('PREVIEW_DATE'=>$postdate, 'PREVIEW_SUBJECT'=>$tsubject, 'PREVIEW_POST'=>$tpost);


		$text = $tp->simpleParse($FORUM_PREVIEW,$shortcodes);

		if ($poll_text)
		{
			$ns->tablerender($_POST['poll_title'], $poll_text);
		}

		$ns->tablerender(LAN_FORUM_3005, $text);


		if ($this->action == 'edit')
		{
			if ($_POST['subject'])
			{
				$action = 'edit';
			}
			else
			{
				$action = 'rp';
			}
			$eaction = true;
		}
		else if($this->action == 'quote')
		{
			$action = 'rp';
			$eaction = false;
		}


	}


	/**
	 * Insert a new thread or a reply/quoted reply.
	 */
	function insertPost()
	{
		$postInfo = array();
		$threadInfo = array();
		$threadOptions = array();

		$fp = new floodprotect;


		if ((isset($_POST['newthread']) && trim($_POST['subject']) == '') || trim($_POST['post']) == '')
		{
			message_handler('ALERT', 5);
		}
		else
		{
			if ($fp->flood('forum_thread', 'thread_datestamp') == false && !ADMIN)
			{
				echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php'</script>\n";
				exit;
			}

			$hasPoll = ($this->action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '');


			if (USER)
			{
				$postInfo['post_user']              = USERID;
				$threadInfo['thread_lastuser']      = USERID;
				$threadInfo['thread_user']          = USERID;
				$threadInfo['thread_lastuser_anon'] = '';
			}
			else
			{
				$postInfo['post_user_anon']         = $_POST['anonname'];
				$threadInfo['thread_lastuser_anon'] = $_POST['anonname'];
				$threadInfo['thread_user_anon']     = $_POST['anonname'];
			}

			$time = time();
			$postInfo['post_entry']                 = $_POST['post'];
			$postInfo['post_forum']                 = $this->id;
			$postInfo['post_datestamp']             = $time;
			$postInfo['post_ip']                    = e107::getIPHandler()->getIP(FALSE);

			$threadInfo['thread_lastpost']          = $time;

			if(isset($_POST['no_emote']))
			{
				$postInfo['post_options']           = serialize(array('no_emote' => 1));
			}

			//If we've successfully uploaded something, we'll have to edit the post_entry and post_attachments
			$newValues = array();

			if($uploadResult = process_upload($newPostId))
			{
				foreach($uploadResult as $ur)
				{
					//$postInfo['post_entry'] .= $ur['txt'];
					//	$_tmp = $ur['type'].'*'.$ur['file'];
					//	if($ur['thumb']) { $_tmp .= '*'.$ur['thumb']; }
					//	if($ur['fname']) { $_tmp .= '*'.$ur['fname']; }

					$type = $ur['type'];
					$newValues[$type][] = $ur['file'];
					// $attachments[] = $_tmp;
				}

				//	$postInfo['_FIELD_TYPES']['post_attachments'] = 'array';
				$postInfo['post_attachments'] = e107::serialize($newValues); //FIXME XXX - broken encoding when saved to DB.
			}
//		var_dump($uploadResult);

			switch($this->action)
			{
				// Reply only.  Add the post, update thread record with latest post info.
				// Update forum with latest post info
				case 'rp':
					$postInfo['post_thread']        = $this->id;
					$newPostId = $this->forumObj->postAdd($postInfo);
					break;

				// New thread started.  Add the thread info (with lastest post info), add the post.
				// Update forum with latest post info
				case 'nt':

					$threadInfo['thread_sticky']    = (MODERATOR ? (int)$_POST['threadtype'] : 0);
					$threadInfo['thread_name']      = $_POST['subject'];
					$threadInfo['thread_forum_id']  = $this->id;
					$threadInfo['thread_active']    = 1;
					$threadInfo['thread_datestamp'] = $time;

					if($hasPoll)
					{
						$threadOptions['poll'] = '1';
					}

					if(is_array($threadOptions) && count($threadOptions))
					{
						$threadInfo['thread_options'] = serialize($threadOptions);
					}
					else
					{
						$threadInfo['thread_options'] = '';
					}

					if($postResult = $this->forumObj->threadAdd($threadInfo, $postInfo))
					{
						$newPostId = $postResult['postid'];
						$newThreadId = $postResult['threadid'];

						if($_POST['email_notify'])
						{
							$this->forumObj->track('add', USERID, $newThreadId);
						}
					}

					break;
			}




			if($postResult === -1 || $newPostId === -1) //Duplicate post
			{
				require_once(HEADERF);
				$message = LAN_FORUM_3006."<br ><a class='btn btn-default' href='".$_SERVER['HTTP_REFERER']."'>Return</a>";
				$text = e107::getMessage()->addError($message)->render();
				e107::getRender()->tablerender(LAN_PLUGIN_FORUM_NAME, $text); // change to forum-title pref.
				require_once(FOOTERF);
				exit;
			}

			$threadId = ($this->action == 'nt' ? $newThreadId : $this->id);


			//If a poll was submitted, let's add it to the poll db
			if ($this->action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '')
			{
				require_once(e_PLUGIN.'poll/poll_class.php');
				$_POST['iid'] = $threadId;
				$poll = new poll;
				$poll->submit_poll(2);
			}

			e107::getCache()->clear('newforumposts');


			$postInfo = $this->forumObj->postGet($newPostId, 'post');
			$forumInfo = $this->forumObj->forumGet($postInfo['post_forum']);



			$threadLink = e107::getUrl()->create('forum/thread/last', $postInfo);
			$forumLink = e107::getUrl()->create('forum/forum/view', $forumInfo);


			if ($this->forumObj->prefs->get('redirect'))
			{
				header('location:'.e107::getUrl()->create('forum/thread/last', $postInfo, array('encode' => false, 'full' => true)));
				exit;
			}
			else
			{
				require_once(HEADERF);
				$template = $this->getTemplate('posted');

				$SHORTCODES = array(
					'THREADLINK'    => $threadLink,
					'FORUMLINK'     => $forumLink
				);


				$txt = (isset($_POST['newthread']) ? $template['thread'] : $template['reply']);

				$txt = e107::getParser()->parseTemplate($txt,true, $SHORTCODES);


				e107::getRender()->tablerender('Forums', e107::getMessage()->render().$txt);
				require_once(FOOTERF);
				exit;
			}
		}



	}



	function updateThread()
	{

		$mes = e107::getMessage();

		if (empty($_POST['subject']) || empty($_POST['post']))
		{
			$mes->addError(LAN_FORUM_3007);
			return;
		}
		else
		{
			if (!$this->isAuthor())
			{
				$mes->addError(LAN_FORUM_3009);
				return;
			}

			$postVals = array();
			$threadVals = array();

			if($uploadResult = process_upload($this->data['post_id']))
			{
				$attachments = explode(',', $this->data['post_attachments']);
				foreach($uploadResult as $ur)
				{
					$_tmp = $ur['type'].'*'.$ur['file'];
					if($ur['thumb']) { $_tmp .= '*'.$ur['thumb']; }
					if($ur['fname']) { $_tmp .= '*'.$ur['fname']; }
					$attachments[] = $_tmp;
				}
				$postVals['post_attachments'] = implode(',', $attachments);
			}

			$postVals['post_edit_datestamp']    = time();
			$postVals['post_edit_user']         = USERID;
			$postVals['post_entry']             = $_POST['post'];

			$threadVals['thread_name'] = $_POST['subject'];

			$this->forumObj->threadUpdate($this->data['post_thread'], $threadVals);
			$this->forumObj->postUpdate($this->data['post_id'], $postVals);

			e107::getCache()->clear('newforumposts');

			$url = e107::getUrl()->create('forum/thread/post', array('name'=>$threadVals['thread_name'], 'id' => $this->data['post_id'], 'thread' => $this->data['post_thread']), array('encode'=>false));

			header('location:'.$url);
			exit;
		}


	}


	/**
	 * @param $id of the post
	 */
	function updateReply()
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();

		if (empty($_POST['post']))
		{
			$mes->addError(LAN_FORUM_3007);
			return;
		}

		if ($this->isAuthor()==false)
		{
			$mes->addError(LAN_FORUM_3009);
			return;
		}

		$postVals['post_edit_datestamp']    = time();
		$postVals['post_edit_user']         = USERID;
		$postVals['post_entry']             = $_POST['post'];

		$this->forumObj->postUpdate($this->data['post_id'], $postVals);

		e107::getCache()->clear('newforumposts');
		$url = e107::getUrl()->create('forum/thread/post', "id={$this->data['post_id']}", 'encode=0&full=1'); // XXX what data is available, find thread name

		header('location:'.$url);
		exit;

	}




	function isAuthor()
	{
		return ((USERID === (int)$this->data['post_user']) || MODERATOR);
	}



}

require_once(HEADERF);
new forum_post_handler;
exit;

require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum();



$action = trim($_GET['f']);     //
$id = (int)$_GET['id'];         // topic / thread.
$post = (int) $_GET['post'];    // individual post.

if(!empty($_POST['action']))
{
	$action = $_POST['action'];
}


switch($action)
{
	case 'rp':
		$threadInfo = $forum->threadGet($id, false);
		$forumId = $threadInfo['thread_forum_id'];
		$forumInfo = $forum->forumGet($forumId);
		break;

	case 'nt':
		$forumInfo = $forum->forumGet($id);
		$forumId = $id;
		break;

	case 'quote':
	case 'edit':
		$postInfo = $forum->postGet($post, 'post');
		$threadInfo = $postInfo;
		$forumId = $postInfo['post_forum'];
		$forumInfo = $forum->forumGet($forumId);
		break;

	default:
		header("Location:".e107::getUrl()->create('forum/forum/main', array(), 'full=1&encode=0'));
		exit;

}

// check if user can post to this forum ...
if (!$forum->checkPerm($forumId, 'post'))
{
	require_once(HEADERF);
	$mes->addError(LAN_FORUM_3001);
	//$mes->addDebug("action: ".$action);
//	$mes->addDebug("id: ".$id);
//	$mes->addDebug(print_a($threadInfo, true));
	$ns->tablerender(LAN_FORUM_1001, $mes->render());
	require_once(FOOTERF);
	exit;
}


define('MODERATOR', USER && $forum->isModerator(USERID));
require_once(e_HANDLER.'ren_help.php'); // FIXME deprecated

//e107::getScBatch('view', 'forum'); //XXX FIXME Conflicting shortcode names. Find a solution without renaming them. 
$sc = e107::getScBatch('post', 'forum')->setScVar('forum', $forum)->setScVar('threadInfo', vartrue($threadInfo));

$gen = new convert;
$fp = new floodprotect;
$e107 = e107::getInstance();

//if thread is not active and not new thread, show warning
if ($action != 'nt' && !$threadInfo['thread_active'] && !MODERATOR)
{
	require_once(HEADERF);
	$mes->addError(LAN_FORUM_3002);
	$ns->tablerender(LAN_FORUM_1001, $mes->render());
	require_once(FOOTERF);
	exit;
}

$forumInfo['forum_name'] = $tp->toHTML($forumInfo['forum_name'], true);
define('e_PAGETITLE', ($action == 'rp' ? LAN_FORUM_3003.": ".$threadInfo['thread_name'] : LAN_FORUM_1018).' / '.$forumInfo['forum_name'].' / '.LAN_FORUM_1001);

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($forum->prefs->get('attach'))
{
	global $allowed_filetypes, $max_upload_size;
	include_once(e_HANDLER.'upload_handler.php');
	$a_filetypes = get_filetypes();
	$max_upload_size = calc_max_upload_size(-1);		// Find overriding maximum upload size
	$max_upload_size = set_max_size($a_filetypes, $max_upload_size);
	$max_upload_size = $e107->parseMemorySize($max_upload_size, 0);
	$a_filetypes = array_keys($a_filetypes);
	$allowed_filetypes = implode(', ', $a_filetypes);
}

if (isset($_POST['submitpoll']))
{
	require_once(e_PLUGIN.'poll/poll_class.php');
	$poll = new poll;

	require_once(HEADERF);
	if (!$FORUMPOST)
	{
		if (file_exists(THEME.'forum_posted_template.php'))
		{
			require_once(THEME.'forum_posted_template.php');
		}
		else
		{
			require_once(e_PLUGIN.'forum/templates/forum_posted_template.php');
		}
	}
	echo $FORUMPOLLPOSTED;
	require_once(FOOTERF);
	exit;
}

/*if (isset($_POST['fpreview']))
{
	process_upload();
	require_once(HEADERF);
	if (USER)
	{
		$poster = USERNAME;
	}
	else
	{
		$poster = ($_POST['anonname']) ? $_POST['anonname'] : LAN_ANONYMOUS;
	}
	$postdate = $gen->convert_date(time(), "forum");
	$tsubject = $tp->post_toHTML($_POST['subject'], true);
	$tpost = $tp->post_toHTML($_POST['post'], true);

	if ($_POST['poll_title'] != '' && check_class($forum->prefs->get('poll')))
	{
		require_once(e_PLUGIN."poll/poll_class.php");
		$poll = new poll;
		$poll->render_poll($_POST, 'forum', 'notvoted');
	}

	if (!$FORUM_PREVIEW)
	{
		if (file_exists(THEME."forum_preview_template.php"))
		{
			require_once(THEME."forum_preview_template.php");
		}
		else
		{
			require_once(e_PLUGIN."forum/templates/forum_preview_template.php");
		}
	}

	$text = $FORUM_PREVIEW;

	if ($poll_text)
	{
		$ns->tablerender($_POST['poll_title'], $poll_text);
	}
	$ns->tablerender(LAN_FORUM_3005, $text);
	$anonname = $tp->post_toHTML($_POST['anonname'], FALSE);

  	$post = $tp->post_toForm($_POST['post']);
	$subject = $tp->post_toHTML($_POST['subject'], false);

	if ($action == 'edit')
	{
		if ($_POST['subject'])
		{
			$action = 'edit';
		}
		else
		{
			$action = 'reply';
		}
		$eaction = true;
	}
	else if($action == 'quote')
	{
		$action = 'reply';
		$eaction = false;
	}
}*/

if (isset($_POST['newthread']) || isset($_POST['reply']))
{
	$postInfo = array();
	$threadInfo = array();
	$postOptions = array();
	$threadOptions = array();

	if ((isset($_POST['newthread']) && trim($_POST['subject']) == '') || trim($_POST['post']) == '')
	{
		message_handler('ALERT', 5);
	}
	else
	{
		if ($fp->flood('forum_thread', 'thread_datestamp') == false && !ADMIN)
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php'</script>\n";
			exit;
		}
		$hasPoll = ($action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '');
		$postInfo['post_ip'] = e107::getIPHandler()->getIP(FALSE);

		if (USER)
		{
			$postInfo['post_user'] = USERID;
			$threadInfo['thread_lastuser'] = USERID;
			$threadInfo['thread_user'] = USERID;
			$threadInfo['thread_lastuser_anon'] = '';
		}
		else
		{
			$postInfo['post_user_anon'] = $_POST['anonname'];
			$threadInfo['thread_lastuser_anon'] = $_POST['anonname'];
			$threadInfo['thread_user_anon'] = $_POST['anonname'];
		}
		$time = time();
		$postInfo['post_entry'] = $_POST['post'];
		$postInfo['post_forum'] = $forumId;
		$postInfo['post_datestamp'] = $time;
		$threadInfo['thread_lastpost'] = $time;
		if(isset($_POST['no_emote']))
		{
			$postInfo['post_options'] = serialize(array('no_emote' => 1));
		}

		//If we've successfully uploaded something, we'll have to edit the post_entry and post_attachments
		if($uploadResult = process_upload($newPostId))
		{
			foreach($uploadResult as $ur)
			{
				//$postInfo['post_entry'] .= $ur['txt'];
			//	$_tmp = $ur['type'].'*'.$ur['file'];
			//	if($ur['thumb']) { $_tmp .= '*'.$ur['thumb']; }
			//	if($ur['fname']) { $_tmp .= '*'.$ur['fname']; }
								
				$type = $ur['type'];
				$newValues[$type][] = $ur['file'];
				// $attachments[] = $_tmp;
			}
			
		//	$postInfo['_FIELD_TYPES']['post_attachments'] = 'array'; 
			$postInfo['post_attachments'] = e107::serialize($newValues); //FIXME XXX - broken encoding when saved to DB. 
		}
//		var_dump($uploadResult);

		switch($action)
		{
			// Reply only.  Add the post, update thread record with latest post info.
			// Update forum with latest post info
			case 'rp':
				$postInfo['post_thread'] = $id;
				$newPostId = $forum->postAdd($postInfo);
				break;

			// New thread started.  Add the thread info (with lastest post info), add the post.
			// Update forum with latest post info
			case 'nt':
				$threadInfo['thread_sticky'] = (MODERATOR ? (int)$_POST['threadtype'] : 0);
				$threadInfo['thread_name'] = $_POST['subject'];
				$threadInfo['thread_forum_id'] = $forumId;
				$threadInfo['thread_active'] = 1;
				$threadInfo['thread_datestamp'] = $time;
				if($hasPoll)
				{
					$threadOptions['poll'] = '1';
				}
				if(is_array($threadOptions) && count($threadOptions))
				{
					$threadInfo['thread_options'] = serialize($threadOptions);
				}
				else
				{
					$threadInfo['thread_options'] = '';
				}
				if($postResult = $forum->threadAdd($threadInfo, $postInfo))
				{
					$newPostId = $postResult['postid'];
					$newThreadId = $postResult['threadid'];
					if($_POST['email_notify'])
					{
						$forum->track('add', USERID, $newThreadId);
					}
				}

				break;
		}
//		print_a($threadInfo);
//		print_a($postInfo);
	//	print_a('action: '.$action);
	//	print_a("newId:".$newPostId);
	//	print_a($_POST);
//		exit;

		if($postResult === -1) //Duplicate post
		{
			require_once(HEADERF);
			$ns->tablerender('', LAN_FORUM_3007);
			require_once(FOOTERF);
			exit;
		}

		$threadId = ($action == 'nt' ? $newThreadId : $id);


		//If a poll was submitted, let's add it to the poll db
		if ($action == 'nt' && varset($_POST['poll_title']) && $_POST['poll_option'][0] != '' && $_POST['poll_option'][1] != '')
		{
			require_once(e_PLUGIN.'poll/poll_class.php');
			$_POST['iid'] = $threadId;
			$poll = new poll;
			$poll->submit_poll(2);
		}

		e107::getCache()->clear('newforumposts');


		$postInfo = $forum->postGet($newPostId, 'post');
		$forumInfo = $forum->forumGet($postInfo['post_forum']);


		
		$threadLink = e107::getUrl()->create('forum/thread/last', $postInfo);
		$forumLink = e107::getUrl()->create('forum/forum/view', $forumInfo);
		if ($forum->prefs->get('redirect'))
		{
			header('location:'.e107::getUrl()->create('forum/thread/last', $postInfo, array('encode' => false, 'full' => true)));
			exit;
		}
		else
		{
			require_once(HEADERF);
			if (!$FORUMPOST)
			{
				if (file_exists(THEME."forum_posted_template.php"))
				{
					require_once(THEME."forum_posted_template.php");
				}
				else
				{
					require_once(e_PLUGIN."forum/templates/forum_posted_template.php");
				}
			}


		//	$FORUMTHREADPOSTED
			


			$txt = (isset($_POST['newthread']) ? $FORUMTHREADPOSTED : $FORUMREPLYPOSTED);
			e107::getRender()->tablerender('Forums', e107::getMessage()->render().$txt); 
			require_once(FOOTERF);
			exit;
		}
	}
}

if (isset($_POST['update_thread']))
{
//	var_dump($_POST);
//	var_dump($threadInfo);
//	var_dump($postInfo);
//	 exit;
	if (!$_POST['subject'] || !$_POST['post'])
	{
		$error = "<div style='text-align:center'>".LAN_FORUM_3007."</div>"; // TODO $mes
	}
	else
	{
		if (!isAuthor())
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3009);
			$ns->tablerender(LAN_FORUM_3008, $mes->render());
			require_once(FOOTERF);
			exit;
		}

		if($uploadResult = process_upload($postInfo['post_id']))
		{
			$attachments = explode(',', $postInfo['post_attachments']);
			foreach($uploadResult as $ur)
			{
				$_tmp = $ur['type'].'*'.$ur['file'];
				if($ur['thumb']) { $_tmp .= '*'.$ur['thumb']; }
				if($ur['fname']) { $_tmp .= '*'.$ur['fname']; }
				$attachments[] = $_tmp;
			}
			$postVals['post_attachments'] = implode(',', $attachments);
		}

		$postVals['post_edit_datestamp'] = time();
		$postVals['post_edit_user'] = USERID;
		$postVals['post_entry'] = $_POST['post'];

		$threadVals['thread_name'] = $_POST['subject'];

		$forum->threadUpdate($postInfo['post_thread'], $threadVals);
		$forum->postUpdate($postInfo['post_id'], $postVals);
		e107::getCache()->clear('newforumposts');
		$url = e107::getUrl()->create('forum/thread/post', array('name'=>$threadVals['thread_name'], 'id' => $postInfo['post_id'], 'thread' => $postInfo['post_thread']), array('encode'=>false));
		header('location:'.$url);
		exit;
	}
}

if (isset($_POST['update_reply']))
{
	if (!$_POST['post'])
	{
		$error = "<div style='text-align:center'>".LAN_FORUM_3007.'</div>'; // TODO $mes		
	}
	else
	{
		if (!isAuthor())
		{
			require_once(HEADERF);
			$mes->addError(LAN_FORUM_3009);
			$ns->tablerender(LAN_FORUM_3008, $mes->render());
			require_once(FOOTERF);
			exit;
		}
		$postVals['post_edit_datestamp'] = time();
		$postVals['post_edit_user'] = USERID;
		$postVals['post_entry'] = $_POST['post'];

		$forum->postUpdate($postInfo['post_id'], $postVals);
		e107::getCache()->clear('newforumposts');
		$url = e107::getUrl()->create('forum/thread/post', "id={$postInfo['post_id']}", 'encode=0&full=1'); // XXX what data is available, find thread name
		header('location:'.$url);
		exit;
	}
}

require_once(HEADERF);

if (vartrue($error))
{
	$ns->tablerender(EMESSLAN_TITLE_ERROR, $error); // LAN?
}



if ($action == 'edit' || $action == 'quote')
{
 	if ($action == 'edit')
	{
		if (!isAuthor())
		{
			$mes->addError(LAN_FORUM_3009);
			$ns->tablerender(LAN_FORUM_3008, $mes->render());
			require_once(FOOTERF);
			exit;
		}
	}

	if (!isset($_POST['fpreview']))
	{
		$post = $tp->toForm($postInfo['post_entry']);
		if($postInfo['post_datestamp'] == $postInfo['thread_datestamp'])
		{
			$subject = $tp->toForm($postInfo['thread_name']);
		}
	}

	if ($action == 'quote')
	{

		//remote [hide] bbcode, or else it doesn't hide stuff too well :)
	/*	$post = preg_replace('#\[hide].*?\[/hide]#s', '', $post);
		$quoteName = ($postInfo['user_name'] ? $postInfo['user_name'] : $postInfo['post_user_anon']);
		$post = "[quote={$quoteName}]\n".$post."\n[/quote]\n";*/
//		$eaction = true;
	//	$action = 'reply';

	}
	else
	{
		$eaction = true;
		if($postInfo['post_datestamp'] != $postInfo['thread_datestamp'])
		{
			$action = 'rp';
		}
		else
		{
			$action = 'nt';
			$sact = 'canc';	// added to override the bugtracker query below
		}
	}
}

	$postInfo['action'] = $action;

	$sc->setVars($postInfo); // send data to shortcodes - remove globals!





// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Load forumpost template

if (!vartrue($FORUMPOST))
{
  if (is_readable(THEME.'forum_post_template.php'))
  {
    include_once(THEME.'forum_post_template.php');
  }
  else
  {
  	include_once(e_PLUGIN.'forum/templates/forum_post_template.php');
  }
}

if(isset($FORUMPOST_TEMPLATE) && (deftrue('BOOTSTRAP',false)))
{
	$FORUMPOST 			= $FORUMPOST_TEMPLATE['form'];	
	$FORUMPOST_REPLY	 = $FORUMPOST_TEMPLATE['form'];	
}

if($action == 'rp')
{
	$FORUMPOST = $FORUMPOST_REPLY;
}
e107::lan('forum','English_front');
$text = $tp->parseTemplate($FORUMPOST, true, $sc);


// -------------------------------------------------------------------------------------------------------------------------------------------------------------

if ($forum->prefs->get('enclose'))
{
	$ns->tablerender($forum->prefs->get('title'), $text);
}
else
{
	echo $text;
}

function isAuthor()
{
	global $postInfo;
	return ((USERID === (int)$postInfo['post_user']) || MODERATOR);
}

function forumjump()
{
	global $forum;
	$jumpList = $forum->forumGetAllowed('view');
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_FORUM_1017.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='btn btn-default button' type='submit' name='fjsubmit' value='".LAN_GO."' /></p></form>";
	return $text;
}

function process_upload()
{
	return;
	global $forumInfo, $thread_info, $admin_log, $forum;

	$postId = (int)$postId;
	$ret = array();

	if (isset($_FILES['file_userfile']['error']))
	{
		require_once(e_HANDLER.'upload_handler.php');
		
		// retrieve and create attachment directory if needed
		$attachmentDir = $forum->getAttachmentPath(USERID, TRUE);

		if($uploaded = process_uploaded_files($attachmentDir, 'attachment', ''))
		{			
			foreach($uploaded as $upload)
			{
			  //print_a($upload); exit; 
			  if ($upload['error'] == 0)
			  {
				$_txt = '';
				$_att = '';
				$_file = '';
				$_thumb = '';
				$_fname = '';
				$fpath = '';
				if(strstr($upload['type'], 'image'))
				{
					$_type = 'img';
					
					//XXX v2.x Image-resizing is now dynamic. 

					/*if($forum->prefs->get('maxwidth', 0) > 0)
					{
						require_once(e_HANDLER.'resize_handler.php');
						$orig_file = $upload['name'];
						$new_file = 'th_'.$orig_file;

						$resizeDir = ($forum->prefs->get('linkimg') ? 'thumb/' : '');

						if(resize_image($attachmentDir.$orig_file, $attachmentDir.$resizeDir.$new_file, $forum->prefs->get('maxwidth')))
						{
							if($forum->prefs->get('linkimg'))
							{
								$parms = image_getsize($attachmentDir.$new_file);
								$_txt = '[br][link='.$fpath.$orig_file."][img{$parms}]".$fpath.$new_file.'[/img][/link][br]';
								$_file = $orig_file;
								$_thumb = $new_file;
								//show resized, link to fullsize
							}
							else
							{
								@unlink($attachmentDir.$orig_file);
								//show resized
								$parms = image_getsize($attachmentDir.$new_file);
								$_txt = "[br][img{$parms}]".$fpath.$new_file.'[/img][br]';
								$_file = $new_file;
							}
						}
						else
						{	//resize failed, show original
							$parms = image_getsize($attachmentDir.$upload['name']);
							$_txt = "[br][img{$parms}]".$fpath.$upload['name'].'[/img]';
							$_file = $upload['name'];
						}
					}
					else
					 
					 */
					{	//resizing disabled, show original
					//	$parms = image_getsize($attachmentDir.$upload['name']);
						//resizing disabled, show original
						$_txt = "[br][img]".$fpath.$upload['name']."[/img]\n";
						$_file = $upload['name'];
					}
				}
				else
				{
					//upload was not an image, link to file
					$_type = 'file';
					$_fname = (isset($upload['rawname']) ? $upload['rawname'] : $upload['name']);
					$_txt = '[br][file='.$fpath.$upload['name'].']'.$_fname.'[/file]';
					$_file = $upload['name'];
					$_thumb = $_fname;
				}
				if($_txt && $_file)
				{
					$ret[] = array('type' => $_type, 'txt' => $_txt, 'file' => $_file, 'thumb' => $_thumb, 'fname' => $_fname);
				}
			  }
			  else
			  {  
			  	// Error in uploaded file, proceed but add error message.
			    //echo 'Error in uploaded file: '.(isset($upload['rawname']) ? $upload['rawname'] : $upload['name']).'<br />';
			    e107::getMessage()->addError('Error in uploading attachment: '.vartrue($upload['message'])); 
			  }
			}

			return $ret;
		}
	}
	/* no file uploaded at all, proceed with creating the topic or reply
	// TODO don't call process_upload() when no attachments are uploaded.. (check  user input first, then call if needed)
	else
	{
		e107::getMessage()->addError('Something went wrong during the attachment uploading process.'); 
	}
	*/ 
}

function image_getsize($fname)
{
	if($imginfo = getimagesize($fname))
	{
		return ":width={$imginfo[0]}&height={$imginfo[1]}";
	}
	else
	{
		return '';
	}
}


require_once(FOOTERF);

?>