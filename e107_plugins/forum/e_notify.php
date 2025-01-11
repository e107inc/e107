<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin notify configuration
 *
*/

if (!defined('e107_INIT')) { exit; }

e107::lan('forum','notify',true); 

// v2.x Standard 
class forum_notify extends notify
{		
	function config()
	{
			
		$config = array();
	
		$config[] = array(
			'name'			=> LAN_FORUM_NT_NEWTOPIC,
			// 'function'		=> "forum_nt",
			'function'		=> "user_forum_topic_created",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_NEWTOPIC_PROB,
			//'function'		=> "forum_ntp",
			'function'		=> "user_forum_topic_created_probationary",
			'category'		=> ''
		);

		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_UPDATED,
			'function'		=> "user_forum_topic_updated",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_DELETED,
			//'function'		=> "forum_topic_del",
			'function'		=> "user_forum_topic_deleted",
			'category'		=> ''
		);

		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_MOVED,
			'function'		=> "user_forum_topic_moved",
			'category'		=> ''
		);
/*
	    // todo: implement thread split
		$config[] = array(
			'name'			=> LAN_FORUM_NT_TOPIC_SPLIT,
			//'function'		=> "forum_topic_split",
			'function'		=> "user_forum_topic_split",
			'category'		=> ''
		);	
*/
		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_CREATED,
			'function'		=> "user_forum_post_created",
			'category'		=> ''
		);	

		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_UPDATED,
			'function'		=> "user_forum_post_updated",
			'category'		=> ''
		);

		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_DELETED,
			//'function'		=> "forum_post_del",
			'function'		=> "user_forum_post_deleted",
			'category'		=> ''
		);

		$config[] = array(
			'name'			=> LAN_FORUM_NT_POST_REPORTED,
			//'function'		=> "forum_post_rep",
			'function'		=> "user_forum_post_report",
			'category'		=> ''
		);		

		return $config;
	}


	private function getData($type, $id)
	{
		if (intval($id) < 1) return false;

		switch($type)
		{
			case 'post':
				$qry = 'SELECT f.forum_name, f.forum_sef, t.thread_id, t.thread_name, p.post_entry 
						FROM `#forum_post` AS p
						LEFT JOIN `#forum_thread` AS t ON (t.thread_id = p.post_thread)
						LEFT JOIN `#forum` AS f ON (f.forum_id = t.thread_forum_id) 
						WHERE p.post_id = ' . intval($id);
				break;

			case 'thread':
				$qry = 'SELECT f.forum_name, f.forum_sef, t.thread_id, t.thread_name
						FROM `#forum_thread` AS t
						LEFT JOIN `#forum` AS f ON (f.forum_id = t.thread_forum_id) 
						WHERE t.thread_id = ' . intval($id);
				break;

			default:
				return false;
		}

		$sql = e107::getDb();
		if($sql->gen($qry))
		{
			return $sql->fetch();
		}
		return false;

	}
	
	//function forum_nt($data) 
	function user_forum_topic_created($data) 
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: New thread created';
		}
		else
		{
			$sef = $data['thread_sef'];

			$data = $this->getData('post', vartrue($data['post_id'], 0));
			if ($data === false) return false;

			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_NEWTOPIC_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'post' => e107::getParser()->toHTML($data['post_entry'], true, 'BODY')
			));
		}
		$this->send('user_forum_topic_created', LAN_PLUGIN_FORUM_NAME, $message);
		return true;
	}

	//function forum_ntp($data)
	function user_forum_topic_created_probationary($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: New thread (probationary) created';
		}
		else
		{
			$sef = $data['thread_sef'];

			$data = $this->getData('post', vartrue($data['post_id'], 0));
			if ($data === false) return false;

			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_NEWTOPIC_PROB_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'post' => e107::getParser()->toHTML($data['post_entry'], true, 'BODY')
			));
		}

		$this->send('user_forum_topic_created_probationary', LAN_FORUM_NT_7, $message);
		return true;
	}

	function user_forum_topic_moved($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Thread moved';
		}
		else
		{
			if(!isset($data['old_thread']) || !isset($data['new_thread']))
			{
				return false;
			}

			$url = e107::url('forum', 'forum', array('forum_sef' => $data['new_thread']['forum_sef'], 'forum_id' => $data['new_thread']['forum_id']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_TOPIC_MOVED_MSG), array(
				'user' => USERNAME,
				'forum' => $data['old_thread']['forum_name'],
				'forum2' => sprintf('<a href="%s">%s</a>', $url, $data['new_thread']['forum_name']),
				'thread' => $data['new_thread']['thread_name']
			));
		}

		$this->send('user_forum_topic_moved', LAN_FORUM_NT_13, $message);
		return true;
	}

	//function forum_topic_del($data)
	function user_forum_topic_deleted($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Thread deleted';
		}
		else
		{
			if(isset($data['thread_id']) && intval($data['thread_id']) < 1)
			{
				return false;
			}

			$url = e107::url('forum', 'forum', array('forum_id' => $data['forum_id'], 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_TOPIC_DELETED_MSG), array(
				'user' => USERNAME,
				'forum' => sprintf('<a href="%s">%s</a>', $url, $data['forum_name']),
				'thread' => $data['thread_name']
			));
		}

		$this->send('user_forum_topic_deleted', LAN_FORUM_NT_8, $message);
		return true;
	}

	function user_forum_topic_updated($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Thread updated';
		}
		else
		{
			$data = $this->getData('thread', vartrue($data['thread_id'],0));
			if ($data === false) return false;

			$sef = eHelper::title2sef($data['thread_name'],'dashl');

			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_TOPIC_UPDATED_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'post' => e107::getParser()->toHTML($data['post_entry'], true, 'BODY')
			));
		}
		$this->send('user_forum_topic_updated', LAN_FORUM_NT_12, $message);
		return true;
	}

	//function forum_topic_split($data)
	function user_forum_topic_split($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Topic splitted';
		}
		else
		{
			$message = $data;
		}

		$this->send('forum_topic_split', LAN_FORUM_NT_9, $message);
	}

	function user_forum_post_created($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Post created';
		}
		else
		{
			$data = $this->getData('post', vartrue($data['post_id'], 0));
			if ($data === false) return false;

			$sef = eHelper::title2sef($data['thread_name'],'dashl');
			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_POST_CREATED_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'post' => e107::getParser()->toHTML($data['post_entry'], true, 'BODY')
			));
		}
		$this->send('user_forum_post_created', LAN_FORUM_NT_14, $message);
		return true;
	}

	function user_forum_post_updated($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Post updated';
		}
		else
		{
			$data = $this->getData('post', vartrue($data['post_id'], 0));
			if ($data === false) return false;

			$sef = eHelper::title2sef($data['thread_name'],'dashl');
			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_POST_UPDATED_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'post' => e107::getParser()->toHTML($data['post_entry'], true, 'BODY')
			));
		}
		$this->send('user_forum_post_updated', LAN_FORUM_NT_15, $message);
		return true;
	}

	//function forum_post_del($data)
	function user_forum_post_deleted($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Post deleted';
		}
		else
		{
			$entry = e107::getParser()->toHTML($data['post_entry'], true, 'BODY');
			$postid = $data['post_id'];

			$data = $this->getData('thread', vartrue($data['post_thread'], 0));
			if ($data === false) return false;

			$sef = eHelper::title2sef($data['thread_name'],'dashl');
			$url = e107::url('forum', 'topic', array('thread_id' => $data['thread_id'], 'thread_sef' => $sef, 'forum_sef' => $data['forum_sef']), array('mode' => 'full'));
			$message = e107::getParser()->lanVars(nl2br(LAN_FORUM_NT_POST_DELETED_MSG), array(
				'user' => USERNAME,
				'forum' => $data['forum_name'],
				'thread' => sprintf('<a href="%s">%s</a>', $url, $data['thread_name']),
				'postid' => $postid,
				'post' => $entry
			));
		}
		$this->send('user_forum_post_deleted', LAN_FORUM_NT_10, $message);
		return true;
	}

	//function forum_post_rep($data)
	function user_forum_post_report($data)
	{
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Post reported';
		}
		else
		{
			$message = $data['notify_message'];
		}

		$this->send('user_forum_post_report', LAN_FORUM_NT_11, $message);
		return true;
	}
	
}