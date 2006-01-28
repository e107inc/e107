<?php

require_once("class2.php");
require_once(e_HANDLER."ixr_library.php");

/*class e107_xmlrpc_server extends IXR_Server {
	function e107_xmlrpc_server() {
		$this->methods = array(
		'blogger.getUsersBlogs'  => 'this:blogger_getUsersBlogs',
		'blogger.getUserInfo'    => 'this:blogger_getUserInfo',
		'blogger.getPost'        => 'this:blogger_getPost',
		'blogger.getRecentPosts' => 'this:blogger_getRecentPosts',
		'blogger.getTemplate'    => 'this:blogger_getTemplate',
		'blogger.setTemplate'    => 'this:blogger_setTemplate',
		'blogger.newPost'        => 'this:blogger_newPost',
		'blogger.editPost'       => 'this:blogger_editPost',
		'blogger.deletePost'     => 'this:blogger_deletePost',
		);
		$this->IXR_Server($this->methods);
		//print_a($this);
	}

	function check_user($login, $password) {
		if (check_login($login, $password) == false) {
			$this->error = new IXR_Error(403, 'Bad User Login.');
			return false;
		}
		return true;
	}

	function blogger_getUsersBlogs($args) {
		if (!$this->check_user($args[1], $args[2])) {
			return $this->error;
		}
		$result = array(
			'isAdmin'  => true,
			'url'      => SITEURL,
			'blogid'   => 'Site News',
			'blogName' => SITENAME,
		);
		return array($result);
	}

	function blogger_getRecentPosts($args) {
		if (!$this->check_user($args[1], $args[2])) {
			return $this->error;
		}
		global $sql;
		$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
		  LEFT JOIN #user AS u ON n.news_author = u.user_id
		  LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		  ORDER BY n. news_datestamp DESC
		  LIMIT ".intval($args[4]);
		
		$sql->db_Select_gen($query);
		$news = $sql -> db_getList();
		if (!$news) {
			$this->error = new IXR_Error(500, 'There are no posts to retrieve.');
			return $this->error;
		}
		foreach ($news as $entry) {
			$post_date = date('Ymd\TH:i:s', $entry['news_datestamp']);
			$content  = '<title>'.$entry['news_title'].'</title>';
			$content .= '<category>Misc</category>';
			$content .= html_entity_decode($entry['news_body'], null, CHARSET);
			$content .= '<more_text>'.html_entity_decode($entry['news_extended'], null, CHARSET).'</more_text>';
			$struct[] = array(
				'userid' => $entry['user_name'],
				'dateCreated' => new IXR_Date($post_date),
				'content' => $content,
				'postid' => $entry['news_id'],
			);

		}
		$recent_posts = array();
		for ($j=0; $j < count($struct); $j++) {
			array_push($recent_posts, $struct[$j]);
		}
		return $recent_posts;
	}
}

function check_login($user, $password) {
	return true;
}

$xmlrpc_server = new e107_xmlrpc_server();
*/
