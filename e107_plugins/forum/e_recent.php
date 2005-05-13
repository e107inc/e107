<?php

	global $sql;
	if(!$forum_install = $sql -> db_Select("plugin", "*", "plugin_path = 'forum' AND plugin_installflag = '1' ")){
		return;
	}

	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	$results = $sql->db_Select_gen("
	SELECT t.thread_id, t.thread_name, t.thread_datestamp, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_anon, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name
	FROM #forum_t AS t, #forum AS f
	LEFT JOIN #user AS u ON t.thread_user = u.user_id
	WHERE f.forum_id = t.thread_forum_id AND t.thread_parent =0 AND f.forum_class REGEXP '".e_CLASS_REGEXP."'	
	ORDER BY t.thread_lastpost DESC LIMIT 0, ".$arr[7]." ");

	$forumArray = $sql->db_getList();

	$path = e_PLUGIN."forum/";

	foreach($forumArray as $forumInfo) {
		extract($forumInfo);

		$r_id = substr($thread_lastuser, 0, strpos($thread_lastuser, "."));
		$r_name = substr($thread_lastuser, (strpos($thread_lastuser, ".")+1));
		if (strstr($thread_lastuser, chr(1))) {
			$tmp = explode(chr(1), $thread_lastuser);
			$r_name = $tmp[0];
		}

		if ($thread_anon) {
			$tmp = explode(chr(1), $thread_anon);
			$thread_user = $tmp[0];
			$thread_user_ip = $tmp[1];
		}

		$rowheading = $this -> parse_heading($thread_name, $mode);
		$HEADING = "<a href='".$path."forum_viewtopic.php?$thread_id' title='".$thread_name."'>".$rowheading."</a>";
		$AUTHOR = ($arr[3] ? ($thread_anon ? $thread_user : "<a href='".e_BASE."user.php?id.$thread_user'>$user_name</a>") : "");
		$CATEGORY = ($arr[4] ? "<a href='".$path."forum_viewforum.php?$forum_id'>$forum_name</a>" : "");
		$DATE = ($arr[5] ? $this -> getRecentDate($thread_lastpost, $mode) : "");
		$ICON = $bullet;

		$VIEWS = $thread_views;
		$REPLIES = $thread_total_replies;
		$LASTPOST = ($thread_total_replies ? ($r_id ? "<a href='".e_BASE."user.php?id.$r_id'>$r_name</a>" : $r_name) : " - ");

		$INFO = "[ ".RECENT_FORUM_1." ".$VIEWS.", ".RECENT_FORUM_2." ".$REPLIES.", ".RECENT_FORUM_3." ".$LASTPOST." ]";

		$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );

	}	


?>