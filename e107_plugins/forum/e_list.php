<?php

	if(!$forum_install = $sql -> db_Select("plugin", "*", "plugin_path = 'forum' AND plugin_installflag = '1' ")){
		return;
	}

	$LIST_CAPTION = $arr[0];
	$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	if($mode == "new_page" || $mode == "new_menu" ){
		$lvisit = $this -> getlvisit();
		$qry = "
		SELECT tp.thread_name AS parent_name, f.forum_id, f.forum_name, f.forum_class, u.user_name, lp.user_name AS lp_name, t.thread_thread, t.thread_id, t.thread_name, tp.thread_parent, tp.thread_datestamp, tp.thread_user, tp.thread_views, tp.thread_lastpost, tp.thread_lastuser, tp.thread_total_replies 
		FROM #forum_t AS t 
		LEFT JOIN #forum_t AS tp ON t.thread_parent = tp.thread_id 
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id 
		LEFT JOIN #user AS u ON tp.thread_user = u.user_id 
		LEFT JOIN #user AS lp ON tp.thread_lastuser = lp.user_id 
		WHERE f.forum_class REGEXP '".e_CLASS_REGEXP."' 
		AND t.thread_datestamp > $lvisit 
		ORDER BY t.thread_datestamp DESC LIMIT 0,".$arr[7];
	}else{
		$qry = "
		SELECT t.thread_id, t.thread_name AS parent_name, t.thread_datestamp, t.thread_user, t.thread_views, t.thread_lastpost, t.thread_lastuser, t.thread_total_replies, f.forum_id, f.forum_name, f.forum_class, u.user_name, lp.user_name AS lp_name 
		FROM #forum_t AS t 
		LEFT JOIN #forum AS f ON f.forum_id = t.thread_forum_id 
		LEFT JOIN #user AS u ON t.thread_user = u.user_id 
		LEFT JOIN #user AS lp ON t.thread_lastuser = lp.user_id 
		WHERE t.thread_parent=0 AND f.forum_class REGEXP '".e_CLASS_REGEXP."' 
		ORDER BY t.thread_lastpost DESC LIMIT 0,".$arr[7];
	}

	if(!$results = $sql->db_Select_gen($qry)){
		$LIST_DATA = LIST_FORUM_2;
	}else{
		$forumArray = $sql->db_getList();

		$path = e_PLUGIN."forum/";

		foreach($forumArray as $forumInfo) {
			extract($forumInfo);

			
			//last user
			$r_id = substr($thread_lastuser, 0, strpos($thread_lastuser, "."));
			$r_name = substr($thread_lastuser, (strpos($thread_lastuser, ".")+1));
			if (strstr($thread_lastuser, chr(1))) {
				$tmp = explode(chr(1), $thread_lastuser);
				$r_name = $tmp[0];
			}
			$thread_lastuser = $r_id;

			//user
			$u_id = substr($thread_user, 0, strpos($thread_user, "."));
			$u_name = substr($thread_user, (strpos($thread_user, ".")+1));
			$thread_user = $u_id;

			if ($thread_anon) {
				$tmp = explode(chr(1), $thread_anon);
				$thread_user = $tmp[0];
				$thread_user_ip = $tmp[1];
			}
			
			$gen = new convert;
			$r_datestamp = $gen->convert_date($thread_lastpost, "forum");
			if($thread_total_replies)
			{
				$LASTPOST = "";
				if($lp_name)
				{
					$LASTPOST = "<a href='".e_BASE."user.php?id.{$thread_lastuser}'>$lp_name</a>";
				}
				else
				{
					if($thread_lastuser{0} == "0")
					{
						$LASTPOST = substr($thread_lastuser, 2);
					}
					else
					{
						//$LASTPOST = NFPM_L16;
					}
				}
				$LASTPOST .= " ".LIST_FORUM_6." <span class='smalltext'>$r_datestamp</span>";
			}
			else
			{
				$LASTPOST		= " - ";
				$LASTPOSTDATE	= "";
			}

			$rowheading	= $this -> parse_heading($parent_name, $mode);
			$HEADING	= "<a href='".$path."forum_viewtopic.php?$thread_id' title='".$parent_name."'>".$rowheading."</a>";
			$AUTHOR		= ($arr[3] ? ($thread_anon ? $thread_user : "<a href='".e_BASE."user.php?id.$thread_user'>$user_name</a>") : "");
			$CATEGORY	= ($arr[4] ? "<a href='".$path."forum_viewforum.php?$forum_id'>$forum_name</a>" : "");
			$DATE		= ($arr[5] ? $this -> getListDate($thread_lastpost, $mode) : "");
			$ICON		= $bullet;
			$VIEWS		= $thread_views;
			$REPLIES	= $thread_total_replies;
			$INFO		= "[ ".LIST_FORUM_3." ".$VIEWS.", ".LIST_FORUM_4." ".$REPLIES.", ".LIST_FORUM_5." ".$LASTPOST." ]";

			$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );

		}
	}


?>