<?php
class e107forum {
	
	function showvar($var) {
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}


	function update_lastpost($type,$id) {
		global $sql;
		if($type == 'thread') {
			$id = intval($id);
			$thread_info = $this->thread_get_lastpost($id);
			if($thread_info) {
				$thread_lastuser = $thread_info['thread_user'].'.';
				if($thread_info['thread_user']) {
					$thread_lastuser .= $thread_info['user_name'];
				} else {
					$tmp = explode(chr(1),$thread_info['thread_anon']);
					$thread_lastuser .= $tmp[0];
				}
				$sql->db_Update('forum_t',"thread_lastpost = {$thread_info['thread_datestamp']}, thread_lastuser = '{$thread_lastuser}' WHERE thread_id = {$id}");
			}
			return $thread_info;
		}
		if($type == 'forum') {
			if($id == 'all') {
				if($sql -> db_Select('forum','forum_id','forum_parent != 0')) {
					while($row = $sql->db_Fetch()) {
						$parentList[] = $row['forum_id'];
					}
					foreach($parentList as $id) {
						echo "Updating forum #{$id}<br />";
						$this->update_lastpost('forum',$id);
					}
				}
			} else {
				$id = intval($id);
				$sql2 = new db;
				$current_lastpost = 0;
				$forum_lpinfo = "";
				if($sql2->db_Select('forum_t','thread_id',"thread_forum_id = $id AND thread_parent = 0")) {
					$cnt = 0;
					while($row = $sql2->db_Fetch()) {
						$cnt++;
						$lp_info = $this->update_lastpost('thread',$row['thread_id']);
						if($lp_info['thread_datestamp'] > $current_lastpost) {
							if($lp_info['thread_anon'] != '') {
								$anon = explode(chr(1),$lp_info['thread_anon']);
								$forum_lpinfo = "0.{$anon[0]}";
							} else {
								$forum_lpinfo = "{$lp_info['thread_user']}.{$lp_info['user_name']}";
							}
							$forum_lpinfo .= ".{$lp_info['thread_datestamp']}.{$row['thread_id']}";
							$current_lastpost = $lp_info['thread_datestamp'];
						}
					}
					$sql2->db_Update('forum',"forum_lastpost = '{$forum_lpinfo}' WHERE forum_id={$id}");
					echo "Forum $id lastpost info: $forum_lpinfo <br />";
					echo "Updated $cnt threads <br />";
				}
			}
		}
	}


	function forum_markasread($forum_id) {
		global $sql;
		if($forum_id != 'all') {
			$forum_id = intval($forum_id);
			$extra = " AND thread_forum_id='$forum_id' ";
		}
		$qry = "thread_lastpost > ".USERLV." AND thread_parent = 0 {$extra} ";
		if($sql->db_Select('forum_t','thread_id',$qry)) {
			while($row = $sql -> db_Fetch()) {
				$u_new .= ".".$row['thread_id'].".";
			}
			$u_new .= USERVIEWED;
			$sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
			header("location:".e_SELF);
			exit;
		}
	}

	function thread_markasread($thread_id) {
		global $sql;
		$thread_id = intval($thread_id);
		$u_new = USERVIEWED.".$thread_id";
		return $sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
	}

	function forum_getparents() {
		global $sql;
		if($sql->db_Select('forum', '*', "forum_parent='0' ORDER BY forum_order ASC")) {
			while($row=$sql->db_Fetch()) {
				$ret[]=$row;
			}
			return $ret;
		}
		return FALSE;
	}

	function forum_getforums($forum_id) {
		global $sql;
		$forum_id = intval($forum_id);
		if($sql->db_Select('forum', '*', "forum_parent='".$forum_id."' ORDER BY forum_order ASC ")) {
			while($row=$sql->db_Fetch()) {
				$ret[]=$row;
			}
			return $ret;
		}
		return FALSE;
	}
	
	function forum_newflag($forum_id) {
		global $sql;
		$forum_id = intval($forum_id);
		if($sql -> db_Select('forum_t', 'thread_id', "thread_forum_id='$forum_id' AND thread_parent=0 AND thread_lastpost > '".USERLV."' ")) {
			while($row = $sql->db_Fetch()) {
				if(!ereg("\.{$row['thread_id']}\.", USERVIEWED)) { 
					return TRUE; 
				}
			}
		}
		return FALSE;
	}

	function thread_user($post_info) {
		if(!$post_info['user_id']) {
			$tmp = explode(chr(1),$post_info['thread_anon']);
			$post_info['user_name'] = $tmp[0];
		}
		return $post_info['user_name'];
	}

	function untrack($thread_id,$from) {
		$thread_id = intval($thread_id);
		global $sql;
		$tmp = ereg_replace("-".$thread_id."-", "", USERREALM);
		return $sql -> db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	}
	
	function track($thread_id,$from) {
		$thread_id = intval($thread_id);
		global $sql;
		return $sql -> db_Update("user", "user_realm='".USERREALM."-".$thread_id."-' WHERE user_id='".USERID."' ");
	}

	function forum_get($forum_id) {
		$forum_id = intval($forum_id);
		global $sql;
		if($sql->db_Select('forum','*',"forum_id = $forum_id")) {
			return $sql->db_Fetch();
		}
		return FALSE;
	}

	function thread_update($thread_id,$newvals) {
		global $sql;
		foreach($newvals as $var => $val) {
			$newvalArray[] = "{$var} = '{$val}'";
		}
		$newString = implode(', ',$newvalArray)." WHERE thread_id={$thread_id}";
		return $sql->db_Update('forum_t',$newString);
	}

	function forum_get_topics($forum_id,$from,$view) {
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		$utab = MPREFIX.'user';
		$qry = "
		SELECT * from {$ftab} 
		LEFT JOIN {$utab}
		ON {$ftab}.thread_user = {$utab}.user_id
		WHERE {$ftab}.thread_forum_id = $forum_id  
		AND {$ftab}.thread_parent = 0 
		ORDER BY 
		thread_s DESC,
		thread_lastpost DESC,
		thread_datestamp DESC  
		LIMIT {$from},{$view}
		";
//		echo $qry.'<br />';
		$ret = array();
		if($sql->db_Select_gen($qry)) {
			while($row = $sql->db_Fetch()){
				$ret[] = $row;
			}
		}
		return $ret;
	}
	
	function thread_get_lastpost($forum_id){
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		$utab = MPREFIX.'user';
		if($sql->db_Count('forum_t','(*)',"WHERE thread_parent = {$forum_id} ")) {
			$where = "WHERE {$ftab}.thread_parent = $forum_id ";
		} else {
			$where = "WHERE {$ftab}.thread_id = $forum_id ";
		}
		$qry = "
		SELECT {$ftab}.thread_user,{$ftab}.thread_anon,{$ftab}.thread_datestamp, {$utab}.user_name
		from {$ftab} 
		LEFT JOIN {$utab}
		ON {$ftab}.thread_user = {$utab}.user_id
		{$where}
		ORDER BY 
		thread_datestamp DESC  
		LIMIT 0,1
		";
		if($sql->db_Select_gen($qry)) {
			return $sql->db_Fetch();
		}
		return FALSE;
	}

	function forum_get_topic_count($forum_id) {
		global $sql;
		return $sql -> db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");
	}

	function thread_getnext($thread_id,$forum_id,$from=0,$limit=100) {
		global $sql;
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		while(!$found) {
			$qry = "
			SELECT thread_id from {$ftab} 
			WHERE thread_forum_id = $forum_id  
			AND thread_parent = 0 
			ORDER BY 
			thread_s DESC,
			thread_lastpost DESC,
			thread_datestamp DESC  
			LIMIT {$from}, {$limit}
			";
			echo $qry.'<br />';
			if($sql->db_Select_gen($qry)) {
				$i=0;
				while($row = $sql->db_Fetch()) {
					$threadList[$i++] = $row['thread_id'];
				}
				$this->showvar($threadList);
				$id = array_search($thread_id,$threadList);
				if($id) {
					if($id != 99) {
						return $threadList[$id+1];
					} else {
						return $this->thread_getnext($thread_id,$forum_id,$from+99,2);
					}
				}
			} else {
				return FALSE;
			}
			$from+=100;
		}
	}		
					
	function thread_getprev($thread_id,$forum_id,$from=0,$limit=100) {
		global $sql;
		$forum_id = intval($forum_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		while(!$found) {
			$qry = "
			SELECT thread_id from {$ftab} 
			WHERE thread_forum_id = $forum_id  
			AND thread_parent = 0 
			ORDER BY 
			thread_s DESC,
			thread_lastpost DESC,
			thread_datestamp DESC  
			LIMIT {$from}, {$limit}
			";
			if($sql->db_Select_gen($qry)) {
				$i=0;
				while($row = $sql->db_Fetch()) {
					$threadList[$i++] = $row['thread_id'];
				}
				$id = array_search($thread_id,$threadList);
				if($id !== FALSE) {
					if($id != 0) {
						return $threadList[$id-1];
					} else {
						if($from == 0) {
							return FALSE;
						}
						return $this->thread_getprev($thread_id,$forum_id,$from-1,2);
					}
				}
			} else {
				return FALSE;
			}
			$from+=100;
		}
	}		

	function thread_get($thread_id,$start=0,$limit=10) {
		$thread_id = intval($thread_id);
		global $sql;
		$ftab = MPREFIX.'forum_t';
		$utab = MPREFIX.'user';
				
		if($start === "last") {
			$tcount = $this->thread_count($thread_id);
			$start = max(0,$tcount-$limit);
		}
		if($start != 0) {
			$limit--;
			$array_start = 0;
		} else {
			$array_start = 1;
		}
		
		$qry = "
		SELECT * from {$ftab} 
		LEFT JOIN {$utab}
		ON {$ftab}.thread_user = {$utab}.user_id
		WHERE {$ftab}.thread_parent = $thread_id 
		ORDER by thread_datestamp ASC 
		LIMIT {$start},".($limit-1);
		$ret = array();
		if($sql->db_Select_gen($qry)) {
			$i=$array_start;
			while($row = $sql->db_Fetch()){
				$ret[$i] = $row;
				$i++;
			}
		}
		$qry = "
		SELECT * from {$ftab} 
		LEFT JOIN {$utab}
		ON {$ftab}.thread_user = {$utab}.user_id
		WHERE {$ftab}.thread_id = $thread_id
		LIMIT 0,1
		";
		if($sql->db_Select_gen($qry)) {
			$row = $sql->db_Fetch();
			$ret['head'] = $row;
			if(!array_key_exists(0,$ret)) {
				$ret[0]=$row;
			}
		}
		return $ret;
	}

	function thread_count($thread_id) {
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Count('forum_t','(*)',"WHERE thread_parent = $thread_id")+1;
	}

	function thread_count_list($thread_list) {
		global $sql;
		$ftab = MPREFIX.'forum_t';
		$qry = "
		SELECT thread_parent, COUNT(*) as thread_replies
		FROM {$ftab}
		WHERE thread_parent
		IN {$thread_list}
		GROUP BY thread_parent
		";
		if($sql->db_Select_gen($qry)) {
			while($row = $sql->db_Fetch()) {
				$ret[$row['thread_parent']] = $row['thread_replies'];
			}
		}
		return $ret;
	}

	function thread_incview($thread_id) {
		$thread_id = intval($thread_id);
		global $sql;
		return $sql->db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");
	}


	function thread_get_postinfo($thread_id,$head=FALSE) {
		$thread_id = intval($thread_id);
		global $sql;
		$ret = array();
		$ttab = MPREFIX.'forum_t';
		$utab = MPREFIX.'user';
		$qry = "
		SELECT * from {$ttab} 
		LEFT JOIN {$utab}
		ON {$ttab}.thread_user = {$utab}.user_id
		WHERE {$ttab}.thread_id = $thread_id 
		LIMIT 0,1
		";
		if($sql->db_Select_gen($qry)) {
			$ret[0] =  $sql->db_Fetch();
		} else {
			return FALSE;
		}
		if($head == FALSE) {
			return $ret;
		}
		$parent_id = $ret[0]['thread_parent'];
		if($parent_id == 0) {
			$ret['head'] = $ret[0];
		} else {
			$qry = "
			SELECT * from {$ttab} 
			LEFT JOIN {$utab}
			ON {$ttab}.thread_user = {$utab}.user_id
			WHERE {$ttab}.thread_id = {$parent_id}
			LIMIT 0,1
			";
			if($sql->db_Select_gen($qry)) {
				$row = $sql->db_Fetch();
				$ret['head'] = $row;
			}
		}
		return $ret;
	}

	function thread_insert($thread_name, $thread_thread, $thread_forum_id, $thread_parent, $thread_user, $thread_active, $thread_s, $thread_anon) {
		$post_time = time();
		global $sql;
		//Check for duplicate post
		if($sql->db_Count('forum_t','(*)',"WHERE thread_thread='$thread_thread'")) {
			return -1;
		}
		if($thread_anon) {
			$tmp = explode(chr(1), $thread_anon);
			$lastpost = $tmp[0].".".$post_time;
			$lastuser = '0.'.$tmp[0];
		} else {
			$lastpost = $thread_user.".".$post_time;
			$lastuser = $thread_user.'.'.USERNAME;
		}
		//Add post to thread
		if($thread_parent) {
			$lp_info = "";
		} else {
			$lp_info = $lastuser;
		}
		$vals = "0,'$thread_name','$thread_thread','$thread_forum_id',$post_time,'$thread_parent','$thread_user',0,$thread_active,$post_time,$thread_s,'{$thread_anon}',0,'{$lp_info}'";
		$newthread_id = $sql->db_Insert('forum_t',$vals);
		if(USER) {
			$sql -> db_Update('user', "user_forums=user_forums+1, user_viewed='".USERVIEWED.".{$newthread_id}.' WHERE user_id='".USERID."' ");
		}
		//If post is a reply
		if($thread_parent) {
			$gen = new convert;
			$sql -> db_Update('forum', "forum_replies=forum_replies+1, forum_lastpost='{$lastuser}.{$post_time}.{$thread_parent}' WHERE forum_id='$thread_forum_id' ");
			$sql->db_Update('forum_t',"thread_lastpost={$post_time},thread_lastuser='{$lastuser}' WHERE thread_id = {$thread_parent}");
			$parent_thread = $this->thread_get_postinfo($thread_parent);
			global $PLUGINS_DIRECTORY;
			$datestamp = $gen->convert_date($post_time, "long");
			$mail_post = stripslashes($_POST['post']);
			$mail_link = SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewtopic.php?".$thread_parent;

//			Send email to orinator of flagged			
			if($thread_parent['active'] == 99) {
				$gen = new convert;
				$email_name = $parent_thread['user_name'];
				$message = 
				LAN_384.SITENAME.".\n\n".
				LAN_382.$datestamp."\n".
				LAN_94.": ".$lastuser."\n\n".
				LAN_385.$email_post."\n\n".
				LAN_383."\n\n".$mail_link;
				sendemail($parent_thread['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
			}
			
//			Send email to all users tracking thread
			if($sql -> db_Select("user", "*", "user_realm REGEXP('-".$thread_parent."-') ")) {
				include_once(e_HANDLER.'mail.php');
				while($row = $sql->db_Fetch()) {
					$message = 
					LAN_385.SITENAME.".\n\n".
					LAN_382.$gen->convert_date(time(), "long")."\n".
					LAN_94.": ".$lastuser."\n\n".
					LAN_385.stripslashes($_POST['post'])."\n\n".
					LAN_383."\n\n".$mail_link;
					if($row['user_email']) {
						sendemail($row['user_email'], $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
					}
				}
			}
		} else {
			//post is a new thread
			$sql -> db_Update('forum', "forum_threads=forum_threads+1, forum_lastpost='{$lastuser}.{$post_time}.{$newthread_id}' WHERE forum_id='$thread_forum_id' ");
		}
			
		return $newthread_id;
	}
}


/**
* @return string	path to and filename of forum icon image 
* 
* @param string $filename		filename of forum image
* @param string $eMLANG_folder	if specified, indicates its a multilanguage image being processed and 
*							gives the subfolder of the image path to the eMLANG_path() function, 
*							default = FALSE
* @param string $eMLANG_pref		if specified, indicates that $filename may be overridden by the 
*							$pref with $eMLANG_pref as its key if that pref is TRUE, default = FALSE
* 
* @desc	checks for the existence of a forum icon image in the themes forum folder and if it is found 
*		returns the path and filename of that file, otherwise it returns the path and filename of the 
*		default forum icon image in e_IMAGES. The additional $eMLANG args if specfied switch the process 
*		to the sister multi-language function eMLANG_path().
* 
* @access public
*/
function img_path($filename, $eMLANG_folder=FALSE, $eMLANG_pref=FALSE) {
	global $pref;
	if ($eMLANG_folder) {
		return eMLANG_path($filename, $eMLANG_folder);
	} else {
		return file_exists(THEME.'forum/'.$filename) ? THEME.'forum/'.$filename : e_IMAGE.'forum/'.$filename;
	}
}

function eMLANG_path($file_name,$sub_folder) {
	if (file_exists(THEME.$sub_folder."/".e_LANGUAGE."/".$file_name)) {
		return THEME.$sub_folder."/".e_LANGUAGE."/".$file_name;
	} 
	if (file_exists(THEME.$sub_folder."/".$file_name)) {
		return THEME.$sub_folder."/".$file_name;
	} 
	if (file_exists(e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name)) {
		return e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name;
	} 
	return e_IMAGE.$sub_folder."/".$file_name;
}


if (file_exists(THEME.'forum/forum_icons_template.php')) {
	require_once(THEME.'forum/forum_icons_template.php');
} else {
	require_once(e_PLUGIN.'forum/templates/forum_icons_template.php');
}



?>
		