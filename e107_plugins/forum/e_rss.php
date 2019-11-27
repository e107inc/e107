<?php

if(!defined('e107_INIT'))
{
	exit;
}
/* NOTES 

USERLIST = 253,254,250,251,0

output of forumthreads feed in v1.0.4
====
thread_thread (content of first post in topic) 
thread_id
thread_name
thread_datestamp
thread_parent  (is the id of the thread that the reply belongs to. For forumthreads, the value is 0.)
thread_user (creator of topic in 'userid.username' format)
thread_views
thread_lastpost (datestamp of last post in topic)
thread_lastuser (user id of user who posted last in the topic. In v1: 'userid.username') 
thread_total_replies
user_name (creator of topic in v1 in 'username' format) 
user_email (email address of user from user table) 

output of forumposts in v1.0.4
=====
parent_name (thread name) 
thread_thread = (content of post, can be reply) 
thread_id
thread_name  (empty for reply) 
thread_datestamp 
thread parent (value is 0 if it is the first post in a topic, contains the topic number if it is a reply)
thread_user (creator of topic in 'userid.username' format)
thread_views
thread_lastuser (user id of user who posted last in the topic. In v1: 'userid.username'. Empty for replies) 
thread_total_replies
forum_id
forum_name
forum_class 
user_name (creator of topic in v1 in 'username' format) 
user_email (email address of user from user table) 
*/

// v2.x Standard 
class forum_rss // plugin-folder + '_rss'
{
	private $rssQuery;

	/**
	 * Admin RSS Configuration 
	 */		
	function config() 
	{
		$config = array();
	

		$config[] = array(
			'name' => "Forum / All forum topics",
			'url' => '6',
			'topic_id' => '',
			'path' => 'forum|threads',
			'text' => 'This feed lists all the forum topics across the whole forum.',
			'class' => '1',
			'limit' => '9',
		);

		//forum threads (new url)
		$config[] = array(
			'name' => "Forum / All forum topics",
			'url' => 'forumthreads',
			'topic_id' => '',
		//	'path' => 'forum|threads',
			'text' => 'This feeds lists all the forum topics across the whole forum.',
			'class' => '0',
			'limit' => '9',
		);

		//forum posts (old url)
		$config[] = array(
			'name' => "Forum / All forum posts",
			'url' => '7',
			'topic_id' => '',
		//	'path' => 'forum|posts',
			'text' => 'This feed lists all the forum posts.',
			'class' => '1',
			'limit' => '9',
		);

		//forum posts (new url)
		$config[] = array(
			'name' => "Forum / All forum posts",
			'url' => 'forumposts',
			'topic_id' => '',
		//	'path' => 'forum|posts',
			'text' => 'This feed lists all the forum posts.',
			'class' => '0',
			'limit' => '9',
		);

		//forum topic (old url)
		$config[] = array(
			'name' => "Forum / All posts of a specific forum topic",
			'url' => '8',
			'topic_id' => '*',
		//	'path' => 'forum|topic',
			'text' => 'This feed lists all posts in a specific forum topic.',
			'class' => '1',
			'limit' => '9',
		);

		//forum topic (new url)
		$config[] = array(
			'name' => "Forum / All posts of a specific forum topic",
			'url' => 'forumtopic',
			'topic_id' => '*',
		//	'path' => 'forum|topic',
			'text' => 'This feed lists all posts in a specific forum topic.',
			'class' => '0',
			'limit' => '9',
		);

		//forum name (old url)
		$config[] = array(
			'name' => "Forum / All forums",
			'url' => '11',
			'topic_id' => '*',
		//	'path' => 'forum|name',
			'text' => 'This feed lists all the topics in a specific forum.',
			'class' => '1',
			'limit' => '9',
		);

		//forum name (new url)
		$config[] = array(
			'name' => "Forum / All forums",
			'url' => 'forumname',
			'topic_id' => '*',
		//	'path' => 'forum|name',
			'text' => 'This feed lists all the topics in a specific forum.',
			'class' => '0',
			'limit' => '9',
		);
		
		return $config;
	}
	

	/**
	 * Compile RSS Data
	 * @param $parms array	url, limit, id
	 * @return array|bool
	 */
	function data($parms=null)
	{
		$sqlrss = e107::getDb();

		$rss 		= array();
		$limit 		= $parms['limit'];
		$topicid 	= $parms['id'];

		switch($parms['url'])
		{
			// List of all forum topics, including content of first post. Does not list replies. 
			case 'forumthreads':
			case 6:
				$rssQuery =
					"SELECT 
						t.thread_id, 
						t.thread_name, 
						t.thread_datestamp, 
						t.thread_user,
						t.thread_user_anon,  
						p.post_entry, 
						p.post_datestamp, 
						p.post_user_anon, 
						p.post_user, 
						u.user_name, 
						u.user_email, 
						f.forum_sef 
					FROM 
						#forum_thread AS t
					LEFT JOIN
						#forum_post as p
						ON p.post_thread = t.thread_id 
						AND p.post_id in 
						(
							SELECT MIN(post_id) 
							FROM #forum_post 
							GROUP BY post_thread
						)
					LEFT JOIN 
						#user AS u 
						ON t.thread_user = u.user_id
					LEFT JOIN 
						#forum AS f 
						ON f.forum_id = t.thread_forum_id
					WHERE 
						f.forum_class IN (".USERCLASS_LIST.") 
					ORDER BY 
						t.thread_datestamp DESC 
					LIMIT 0," . $limit;

				$sqlrss->gen($rssQuery);
				$tmp 	= $sqlrss->db_getList();

				$rss 	= array();
				$i 		= 0;

				foreach($tmp as $value)
				{	
					// Generate SEF topic link
					$topic_link = 
					e107::url(
						'forum', 
						'topic', 
						array
							(
								'forum_sef' 	=> $value['forum_sef'],
								'thread_id' 	=> $value['thread_id'], 
								'thread_sef' 	=> eHelper::title2sef($value['thread_name']), 
							),
						array('mode' => 'full')
						);
					
					
					// Check if post was done anonymously 
					if($value['thread_user_anon']) // Anonymous user entered specific name
					{
						$rss[$i]['author'] 			= $value['thread_user_anon'];
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					elseif(empty($value['post_user_anon']) && $value['post_user'] == 0) // No specific username entered, use LAN_ANONYMOUS
					{
						$rss[$i]['author'] 			= LAN_ANONYMOUS;
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					else // Post by a user who was logged in
					{	
						$rss[$i]['author'] 			= $value['user_name'];
						$rss[$i]['author_email'] 	= $value['user_email'];  // must include an email address to be valid.
					}
					

					$rss[$i]['title'] 			= $value['thread_name'];
					//$rss[$i]['link'] 			= SITEURLBASE . e_PLUGIN_ABS . "forum/forum_viewtopic.php?" . $value['thread_id'];
					$rss[$i]['link'] 			= $topic_link;
					$rss[$i]['description'] 	= $value['post_entry'];
					$rss[$i]['datestamp'] 		= $value['thread_datestamp'];

					$i++;
				}
				break;

			// List of all forum posts (first post and replies) across all forums
			case 'forumposts':
			case 7:
				$rssQuery = "
				SELECT
				    t.thread_id, 
				    t.thread_name, 
				    t.thread_datestamp, 
				    t.thread_user,   
				    f.forum_id, 
				    f.forum_name, 
				    f.forum_class, 
				    f.forum_sef, 
				    p.post_entry, 
				    p.post_datestamp, 
				    p.post_user, 
				    p.post_user_anon, 
				    u.user_name, 
				    u.user_email
				FROM
				    #forum_thread AS t
				LEFT JOIN 
					#forum_post as p 
				    ON p.post_thread = t.thread_id
				LEFT JOIN 
					#user AS u
					ON p.post_user = u.user_id
				LEFT JOIN 
					#forum AS f
					ON f.forum_id = t.thread_forum_id
				WHERE
				    f.forum_class IN(".USERCLASS_LIST.")
				ORDER BY
				    t.thread_datestamp
				DESC
				LIMIT 0," . $limit;

				$sqlrss->gen($rssQuery);
				$tmp 	= $sqlrss->db_getList();
				
				$rss 	= array();
				$i 		= 0;

				foreach($tmp as $value)
				{
					// Generate SEF link
					$topic_link = 
					e107::url(
						'forum', 
						'topic', 
						array
							(
								'forum_sef' 	=> $value['forum_sef'],
								'thread_id' 	=> $value['thread_id'], 
								'thread_sef' 	=> eHelper::title2sef($value['thread_name']), 
							),
						array('mode' => 'full')
						);

					// Check if post was done anonymously 
					if($value['post_user_anon']) // Anonymous user entered specific name
					{
						$rss[$i]['author'] 			= $value['post_user_anon'];
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					elseif(empty($value['post_user_anon']) && $value['post_user'] == 0) // No specific username entered, use LAN_ANONYMOUS
					{
						$rss[$i]['author'] 			= LAN_ANONYMOUS;
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					else // Post by a user who was logged in
					{	
						$rss[$i]['author'] 			= $value['user_name'];
						$rss[$i]['author_email'] 	= $value['user_email'];  // must include an email address to be valid.
					}
					

					// FIXME - reply or topic start? If reply add "RE:" to title 
					/*if($value['parent_name'])
					{
						$rss[$i]['title'] 	= "Re: " . $value['parent_name'];
						$rss[$i]['link'] 	= SITEURLBASE . e_PLUGIN_ABS . "forum/forum_viewtopic.php?" . $value['thread_parent'];
					}
					else
					{
						$rss[$i]['title'] 	= $value['thread_name'];
						$rss[$i]['link'] 	= SITEURLBASE . e_PLUGIN_ABS . "forum/forum_viewtopic.php?" . $value['thread_id'];
					}*/

					$rss[$i]['title'] 		= $value['thread_name'];
					$rss[$i]['link'] 		= $topic_link;
					$rss[$i]['description'] = $value['post_entry'];
					$rss[$i]['datestamp'] 	= $value['post_datestamp'];

					$i++;
				}
				break;

			// Lists all posts in a specific forum topic 
			case 'forumtopic':
			case 8:
				if(!$topicid)
				{
					return false;
				}

				// Select first post (initial post in topic)
				$this->rssQuery = "
				SELECT 
					t.thread_id, 
					t.thread_name, 
					t.thread_datestamp, 
					t.thread_user, 
					p.post_entry, 
					p.post_datestamp, 
					u.user_name, 
					u.user_email, 
					f.forum_sef 
				FROM 
					#forum_thread AS t
				LEFT JOIN
					#forum_post as p
					ON p.post_thread = t.thread_id 
					AND p.post_id IN
					(
						SELECT MIN(post_id) 
						FROM #forum_post 
						GROUP BY post_thread
					)
				LEFT JOIN 
					#user AS u 
					ON t.thread_user = u.user_id
				LEFT JOIN 
					#forum AS f 
					ON f.forum_id = t.thread_forum_id
				WHERE 
					f.forum_class IN (".USERCLASS_LIST.") 
				AND
				    p.post_thread = ".intval($topicid)."
				LIMIT 0,1";


				$sqlrss->gen($this->rssQuery);
				$topic = $sqlrss->fetch();

				// Replies (exclude first post)
				$this->rssQuery = "
				SELECT 
					t.thread_id, 
					t.thread_name, 
					t.thread_datestamp, 
					t.thread_user, 
					t.thread_user_anon,
					p.post_entry, 
					p.post_datestamp, 
					p.post_user, 
					p.post_user_anon,
					u.user_name, 
					u.user_email, 
					f.forum_sef 
				FROM 
					#forum_thread AS t
				LEFT JOIN
					#forum_post as p
					ON p.post_thread = t.thread_id 
					AND p.post_id NOT IN
					(
						SELECT MIN(post_id) 
						FROM #forum_post 
						GROUP BY post_thread
					)
				LEFT JOIN 
					#user AS u 
					ON t.thread_user = u.user_id
				LEFT JOIN 
					#forum AS f 
					ON f.forum_id = t.thread_forum_id
				WHERE 
					f.forum_class IN (".USERCLASS_LIST.") 
				AND
				    p.post_thread = ".intval($topicid);

				$sqlrss->gen($this->rssQuery);
				$replies = $sqlrss->db_getList();

				$rss 	= array();
				$i 		= 0;

				$topic_link = 
					e107::url(
						'forum', 
						'topic', 
						array
							(
								'forum_sef' 	=> $topic['forum_sef'],
								'thread_id' 	=> $topic['thread_id'], 
								'thread_sef' 	=> eHelper::title2sef($topic['thread_name']), 
							),
						array('mode' => 'full')
						);

				
				// Check if post was done anonymously 
				if($topic['thread_user_anon']) // Anonymous user entered specific name
				{
					$rss[$i]['author'] 			= $topic['thread_user_anon'];
					$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
				}
				elseif(empty($topic['thread_user_anon']) && $topic['thread_user'] == 0) // No specific username entered, use LAN_ANONYMOUS
				{
					$rss[$i]['author'] 			= LAN_ANONYMOUS;
					$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
				}
				else // Post by a user who was logged in
				{	
					$rss[$i]['author'] 			= $topic['user_name'];
					$rss[$i]['author_email'] 	= $topic['user_email'];  // must include an email address to be valid.
				}

				$rss[$i]['title'] 			= $topic['thread_name'];
				$rss[$i]['link'] 			= $topic_link;
				$rss[$i]['description'] 	= $topic['post_entry'];
				$rss[$i]['datestamp'] 		= $topic['thread_datestamp'];
				$i++;

				foreach($replies as $value)
				{
					
					// Check if post was done anonymously 
					if($value['post_user_anon']) // Anonymous user entered specific name
					{
						$rss[$i]['author'] 			= $value['post_user_anon'];
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					elseif(empty($value['post_user_anon']) && $value['post_user'] == 0) // No specific username entered, use LAN_ANONYMOUS
					{
						$rss[$i]['author'] 			= LAN_ANONYMOUS;
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					else // Post by a user who was logged in
					{	
						$rss[$i]['author'] 			= $value['user_name'];
						$rss[$i]['author_email'] 	= $value['user_email'];  // must include an email address to be valid.
					}
					
					$rss[$i]['title'] 			= "Re: " . $topic['thread_name'];
					$rss[$i]['link'] 			= $topic_link;
					$rss[$i]['description'] 	= $value['post_entry'];
					$rss[$i]['datestamp'] 		= $value['post_datestamp'];
					$i++;
				}

				break;

			// Lists all the topics in a specific forum 
			case 'forumname':
			case 11:
				if(!$topicid)
				{
					return false;
				}

				$this->rssQuery = "
				SELECT 
					f.forum_id, 
					f.forum_name, 
					f.forum_class, 
					f.forum_sef,
					t.thread_id,
					t.thread_name,
					t.thread_datestamp, 
					t.thread_user, 
					t.thread_user_anon, 
					p.post_entry,
					u.user_name, 
					u.user_email
				FROM 
					#forum_thread as t
				LEFT JOIN 
					#user AS u 
					ON t.thread_user = u.user_id
				LEFT JOIN 
					#forum AS f 
					ON f.forum_id = t.thread_forum_id
				LEFT JOIN
					#forum_post as p
					ON p.post_thread = t.thread_id 
					AND p.post_id IN
					(
						SELECT MIN(post_id) 
						FROM #forum_post 
						GROUP BY post_thread
					)
				WHERE 
					t.thread_forum_id = ".intval($topicid)." 
				AND 
					f.forum_class IN (".USERCLASS_LIST.") 
				ORDER BY 
					t.thread_datestamp 
				DESC 
				LIMIT 0," . $limit;

				$sqlrss->gen($this->rssQuery);
				$tmp = $sqlrss->db_getList();
				
				//	$this->contentType = $this->contentType . " : " . $tmp[1]['forum_name'];
				
				$rss 	= array();
				$i 		= 0;

				foreach($tmp as $value)
				{

					$topic_link = 
						e107::url(
						'forum', 
						'topic', 
						array
							(
								'forum_sef' 	=> $value['forum_sef'],
								'thread_id' 	=> $value['thread_id'], 
								'thread_sef' 	=> eHelper::title2sef($value['thread_name']), 
							),
						array('mode' => 'full')
						);
					
					// Check if post was done anonymously 
					if($value['thread_user_anon']) // Anonymous user entered specific name
					{
						$rss[$i]['author'] 			= $value['thread_user_anon'];
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					elseif(empty($value['thread_user_anon']) && $value['thread_user'] == 0) // No specific username entered, use LAN_ANONYMOUS
					{
						$rss[$i]['author'] 			= LAN_ANONYMOUS;
						$rss[$i]['author_email'] 	= "anonymous@anonymous.com";
					}
					else // Post by a user who was logged in
					{	
						$rss[$i]['author'] 			= $value['user_name'];
						$rss[$i]['author_email'] 	= $value['user_email'];  // must include an email address to be valid.
					}
					
					$rss[$i]['title'] 		= $value['thread_name'];
					$rss[$i]['link'] 		= $topic_link;
					$rss[$i]['description'] = $value['post_entry'];
					$rss[$i]['datestamp'] 	= $value['thread_datestamp'];
					
					$i++;
				}
				break;
		}

		return $rss;

	}
}