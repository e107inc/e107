<?php

if (!defined('e107_INIT')) { exit; }

//FIXME TODO - Use v2 method. See chatbox_menu/e_rss.php

//##### create feed for admin, return array $eplug_rss_feed --------------------------------
$feed = get_forum_rss();
foreach($feed as $k=>$v){
	$eplug_rss_feed[] = $v;
}

function get_forum_rss(){
	$rss = array();

	//forum threads (old url)
	$feed['name']			= "Forum / threads";
	$feed['url']			= '6';
	$feed['topic_id']		= '';
	$feed['path']			= 'forum|threads'; //FIXME
	$feed['text']			= 'this is the rss feed for the forum_threads entries';
	$feed['class']			= '1';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum threads (new url)
	$feed['name']			= "Forum / threads";
	$feed['url']			= 'forumthreads';
	$feed['topic_id']		= '';
	$feed['path']			= 'forum|threads';//FIXME
	$feed['text']			= 'this is the rss feed for the forum_threads entries';
	$feed['class']			= '0';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum posts (old url)
	$feed['name']			= "Forum / posts";
	$feed['url']			= '7';
	$feed['topic_id']		= '';
	$feed['path']			= 'forum|posts';//FIXME
	$feed['text']			= 'this is the rss feed for the forum_posts entries';
	$feed['class']			= '1';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum posts (new url)
	$feed['name']			= "Forum / posts";
	$feed['url']			= 'forumposts';
	$feed['topic_id']		= '';
	$feed['path']			= 'forum|posts';//FIXME
	$feed['text']			= 'this is the rss feed for the forum_posts entries';
	$feed['class']			= '0';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum topic (old url)
	$feed['name']			= "Forum / topic";
	$feed['url']			= '8';
	$feed['topic_id']		= '*';
	$feed['path']			= 'forum|topic';//FIXME
	$feed['text']			= 'this is the rss feed for the forum_topic entries';
	$feed['class']			= '1';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum topic (new url)
	$feed['name']			= "Forum / topic";
	$feed['url']			= 'forumtopic';
	$feed['topic_id']		= '*';
	$feed['path']			= 'forum|topic'; //FIXME
	$feed['text']			= 'this is the rss feed for the forum_topic entries';
	$feed['class']			= '0';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum name (old url)
	$feed['name']			= "Forum / name";
	$feed['url']			= '11';
	$feed['topic_id']		= '*';
	$feed['path']			= 'forum|name'; //FIXME
	$feed['text']			= 'this is the rss feed for the forum_name entries';
	$feed['class']			= '1';
	$feed['limit']			= '9';
	$rss[] = $feed;

	//forum name (new url)
	$feed['name']			= "Forum / name";
	$feed['url']			= 'forumname';
	$feed['topic_id']		= '*';
	$feed['path']			= 'forum|name'; //FIXME
	$feed['text']			= 'this is the rss feed for the forum_name entries';
	$feed['class']			= '0';
	$feed['limit']			= '9';
	$rss[] = $feed;

	return $rss;
}
//##### ------------------------------------------------------------------------------------


//##### create rss data, return as array $eplug_rss_data -----------------------------------
$sqlrss = new db;

switch($this->parm){ //FIXME use v2.x standard and replace this with $parm['url'] check. 

	case threads:
	case 6:
		$this -> rssQuery =
		"SELECT t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_lastuser, t.thread_total_replies, u.user_name, u.user_email FROM #forum_t AS t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		WHERE f.forum_class IN (".USERCLASS_LIST.") AND t.thread_parent=0
		ORDER BY t.thread_datestamp DESC LIMIT 0,".$this -> limit;
		$sqlrss->db_Select_gen($this -> rssQuery);
		$tmp = $sqlrss->db_getList();

		$rss = array();
		$i=0;
		foreach($tmp as $value) {

			if($value['user_name']) {
				$rss[$i]['author'] = $value['user_name'];
				$rss[$i]['author_email'] = $value['user_email'];  // must include an email address to be valid.
		} else {
				$tmp=explode(".", $value['thread_user'], 2);
				list($rss[$i]['author'], $ip) = explode(chr(1), $tmp[1]);
			}

			$rss[$i]['title'] = $value['thread_name'];
			$rss[$i]['link'] = SITEURLBASE.e_PLUGIN_ABS."forum/forum_viewtopic.php?".$value['thread_id'];
			$rss[$i]['description'] = $value['thread_thread'];
			$rss[$i]['datestamp'] = $value['thread_datestamp'];

			$i++;
		}
	break;

	case posts:
	case 7:
		$this -> rssQuery = "SELECT tp.thread_name AS parent_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name, u.user_email FROM #forum_t AS t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		WHERE f.forum_class  IN (".USERCLASS_LIST.")
		ORDER BY t.thread_datestamp DESC LIMIT 0,".$this -> limit;
		$sqlrss->db_Select_gen($this -> rssQuery);
		$tmp = $sqlrss->db_getList();
		$rss = array();
		$i=0;
		foreach($tmp as $value) {

			if($value['user_name']) {
				$rss[$i]['author'] = $value['user_name'];
				$rss[$i]['author_email'] = $value['user_email'];  // must include an email address to be valid.
			} else {
				$tmp=explode(".", $value['thread_user'], 2);
				list($rss[$i]['author'], $ip) = explode(chr(1), $tmp[1]);
			}

			if($value['parent_name']) {
				$rss[$i]['title'] = "Re: ".$value['parent_name'];
				$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_parent'];
			} else {
				$rss[$i]['title'] = $value['thread_name'];
				$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_id'];
			}

			$rss[$i]['description'] = $value['thread_thread'];
			$rss[$i]['datestamp'] = $value['thread_datestamp'];

			$i++;
		}
	break;

	case topic:
	case 8:
		if(!$this -> topicid) {
			return FALSE;
		}

		/* get thread ...  */
		$this -> rssQuery = "SELECT t.thread_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, f.forum_id, f.forum_name, f.forum_class, u.user_name
		FROM #forum_t AS t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		WHERE f.forum_class  IN (".USERCLASS_LIST.") AND t.thread_id=".intval($this -> topicid);
		$sqlrss->db_Select_gen($this -> rssQuery);
		$topic = $sqlrss->db_Fetch();

		/* get replies ...  */
		$this -> rssQuery = "SELECT t.thread_name, t.thread_thread, t.thread_id, t.thread_name, t.thread_datestamp, t.thread_parent, t.thread_user, t.thread_views, t.thread_lastpost, f.forum_id, f.forum_name, f.forum_class, u.user_name, u.user_email
		FROM #forum_t AS t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		WHERE f.forum_class  IN (".USERCLASS_LIST.") AND t.thread_parent=".intval($this -> topicid);
		$sqlrss->db_Select_gen($this -> rssQuery);
		$replies = $sqlrss->db_getList();

		$rss = array();
		$i = 0;

		if($value['user_name']) {
			$rss[$i]['author'] = $value['user_name'] . " ( ".$e107->base_path."user.php?id.".intval($value['thread_user'])." )";
		} else {
			$tmp=explode(".", $value['thread_user'], 2);
			list($rss[$i]['author'], $ip) = explode(chr(1), $tmp[1]);
		}

		$rss[$i]['title'] = $topic['thread_name'];
		$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$topic['thread_id'];
		$rss[$i]['description'] = $topic['thread_thread'];
		$rss[$i]['datestamp'] = $topic['thread_datestamp'];
		$i ++;
		foreach($replies as $value) {
			if($value['user_name']) {
				$rss[$i]['author'] = $value['user_name'];
				$rss[$i]['author_email'] = $value['user_email'];  // must include an email address to be valid.
			} else {
				$tmp=explode(".", $value['thread_user'], 2);
				list($rss[$i]['author'], $ip) = explode(chr(1), $tmp[1]);
			}
			$rss[$i]['title'] = "Re: ".$topic['thread_name'];
			$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$this -> topicid;
			$rss[$i]['description'] = $value['thread_thread'];
			$rss[$i]['datestamp'] = $value['thread_datestamp'];
			$i++;
		}
	break;

	case name:
	case 11:
		if(!$this -> topicid) {
			return FALSE;
		}

		$this -> rssQuery = "
		SELECT f.forum_id, f.forum_name, f.forum_class, tp.thread_name AS parent_name, t.*, u.user_name, u.user_email
		FROM #forum_t as t
		LEFT JOIN #user AS u ON SUBSTRING_INDEX(t.thread_user,'.',1) = u.user_id
		LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id
		WHERE t.thread_forum_id = ".intval($this->topicid)." AND f.forum_class IN (0, 251, 255)
		ORDER BY t.thread_datestamp DESC LIMIT 0,".$this -> limit;

		$sqlrss->db_Select_gen($this -> rssQuery);
		$tmp = $sqlrss->db_getList();
		$this -> contentType = $this -> contentType." : ".$tmp[1]['forum_name'];
		$rss = array();
		$i=0;
		foreach($tmp as $value) {
			if($value['user_name']) {
				$rss[$i]['author'] = $value['user_name'];
				$rss[$i]['author_email'] = $value['user_email'];
			} else {
				$tmp=explode(".", $value['thread_user'], 2);
				list($rss[$i]['author'], $ip) = explode(chr(1), $tmp[1]);
			}

			if($value['parent_name']) {
				$rss[$i]['title'] = "Re: ".$value['parent_name'];
				$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_id'].".post";
			} else {
				$rss[$i]['title'] = $value['thread_name'];
				$rss[$i]['link'] = $e107->base_path.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$value['thread_id'];
			}
			$rss[$i]['description'] = $value['thread_thread'];
			$rss[$i]['datestamp'] = $value['thread_datestamp'];
			$i++;
		}
	break;
}

$eplug_rss_data[] = $rss;
//##### ------------------------------------------------------------------------------------

?>