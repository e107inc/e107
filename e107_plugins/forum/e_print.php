<?php

class forum_print // plugin-folder + '_print'
{
	public function render($thread_id)
	{
		$tp 	= e107::getParser(); 
		$text	= '';

		include_once(e_PLUGIN.'forum/forum_class.php');
		$forum = new e107forum;
		
		// Get basic topic info 
		$thread_info = $forum->threadGet($thread_id);
		//print_a($thread_info);

		// Check if user is allowed to view this forum topic 
		if(!$forum->checkPerm($thread_info['thread_forum_id']))
		{
			return LAN_FORUM_0008;
		}

		// Get all posts in this topic
		$post_list = $forum->postGet($thread_id, 0, 9999);
		//print_a($post_list);

		// Set topic name
		$topic_name = e107::getParser()->toHTML($thread_info['thread_name'], true);

		// Display topic name
		$text .= "<h3>".$topic_name."</h3>";
		
		// Display initial (first) post in topic 
		$text .= "<em>
		".$post_list[0]['user_name'].", ".e107::getDate()->convert_date($post_list[0]['post_datestamp'], "forum")."
		</em><br /><br />
		".$tp->toHTML($post_list[0]['post_entry'], true);

		// Remove original post from $post_list array, so only replies are left
		unset($post_list['0']);

		// Loop through each reply
		foreach($post_list as $reply)
		{
			$text .= "<div style='padding:10px 0px; border-top:1px dashed #cccccc'>Re: <strong>".$topic_name."</strong><br />
			<em>".$reply['user_name'].", ".e107::getDate()->convert_date($reply['post_datestamp'], "forum")."</em><br /><br />
			".$tp->toHTML($reply['post_entry'], true).
			"</div>\n\n";
		}

		return $text;
	}	
}