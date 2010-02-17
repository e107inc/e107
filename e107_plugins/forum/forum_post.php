<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (C)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_post.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
$e_wysiwyg = "post";
include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_post.php');

if (IsSet($_POST['fjsubmit'])) {
	header("location:".e_BASE.$PLUGINS_DIRECTORY."forum/forum_viewforum.php?".$_POST['forumjump']);
	exit;
}
require_once(e_PLUGIN.'forum/forum_class.php');
$forum = new e107forum;

if (!e_QUERY) {
	header("Location:".e_PLUGIN."forum/forum.php");
	exit;
} else {
	$tmp = explode(".", e_QUERY);
	$action = preg_replace('#\W#', '', $tmp[0]);
	$id = intval($tmp[1]);
	$from = intval($tmp[2]);
}

// check if user can post to this forum ...
if ($action == 'rp')
{
	// reply to thread
	$thread_info = $forum->thread_get($id, 'last', 11);
	if (!is_array($thread_info) || !count($thread_info))
	{
	  $forum_info = FALSE;		// Someone fed us a dud forum id - should exist if replying
	}
	else
	{
	  $forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
	}
}
elseif ($action == 'nt')
{
	// New post
	$forum_info = $forum->forum_get($id);
}
elseif ($action == 'quote' || $action == 'edit')
{
	$thread_info = $forum->thread_get_postinfo($id, TRUE);
	$forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
	if($action == 'quote')
	{
		$id = $thread_info['head']['thread_id'];
	}
}

if (($forum_info === FALSE) || !check_class($forum_info['forum_postclass']) || !check_class($forum_info['parent_postclass'])) {
	require_once(HEADERF);
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
	require_once(FOOTERF);
	exit;
}
define("MODERATOR", check_class($forum_info['forum_moderators']));
//require_once(e_HANDLER.'forum_include.php');
require_once(e_PLUGIN."forum/forum_post_shortcodes.php");
require_once(e_PLUGIN."forum/forum_shortcodes.php");
require_once(e_HANDLER."ren_help.php");
$gen = new convert;
$fp = new floodprotect;
global $tp;

if ($sql->db_Select("tmp", "*", "tmp_ip='$ip' ")) {
	$row = $sql->db_Fetch();
	$tmp = explode("^", $row['tmp_info']);
	$action = $tmp[0];
	$anonname = $tmp[1];
	$subject = $tmp[2];
	$post = $tmp[3];
	$sql->db_Delete("tmp", "tmp_ip='$ip' ");
}

//Check to see if user had post rights
if (!check_class($forum_info['forum_postclass']))
{
	require_once(HEADERF);
	$text .= "<div style='text-align:center'>".LAN_399."</div>";
	$ns->tablerender(LAN_20, $text);
	require_once(FOOTERF);
	exit;
}

//if thread is not active and not new thread, show warning
if ($action != "nt" && !$thread_info['head']['thread_active'] && !MODERATOR)
{
	require_once(HEADERF);
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_397."</div>");
	require_once(FOOTERF);
	exit;
}

$forum_info['forum_name'] = $tp -> toHTML($forum_info['forum_name'], TRUE);

define("e_PAGETITLE", LAN_01." / ".$forum_info['forum_name']." / ".($action == "rp" ? LAN_02.$forum_info['thread_name'] : LAN_03));

// ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

if (is_readable(e_ADMIN.'filetypes.php')) {
	$a_filetypes = trim(file_get_contents(e_ADMIN.'filetypes.php'));
	$a_filetypes = explode(',', $a_filetypes);
	foreach ($a_filetypes as $ftype) {
		$sa_filetypes[] = '.'.trim(str_replace('.', '', $ftype));
	}
	$allowed_filetypes = implode(' | ', $sa_filetypes);
}

if (isset($_POST['submitpoll']))
{
	require_once(e_PLUGIN."poll/poll_class.php");
	$poll = new poll;

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
	echo $FORUMPOLLPOSTED;
	require_once(FOOTERF);
	exit;
}

if (isset($_POST['fpreview']))
{
	process_upload();
	require_once(HEADERF);
	if (USER)
	{
		$poster = USERNAME;
	}
	else
	{
		$poster = ($_POST['anonname']) ? $_POST['anonname'] : LAN_311;
	}
	$postdate = $gen->convert_date(time(), "forum");
	$tsubject = $tp->post_toHTML($_POST['subject'], true);
	$tpost = $tp->post_toHTML($_POST['post'], true);

	if ($_POST['poll_title'] != "" && $pref['forum_poll'])
	{
		require_once(e_PLUGIN."poll/poll_class.php");
		$poll = new poll;
		$poll->render_poll($_POST, "forum", "notvoted");
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
	$ns->tablerender(LAN_323, $text);
	$anonname = $tp->post_toHTML($_POST['anonname'], FALSE);

  	$post = $tp->post_toForm($_POST['post']); 
	$subject = $tp->post_toHTML($_POST['subject'], false);

	if ($action == "edit")
	{
		if ($_POST['subject'])
		{
			$action = "edit";
		}
		else
		{
			$action = "reply";
		}
		$eaction = TRUE;
	}
	else if($action == "quote")
	{
		$action = "reply";
		$eaction = FALSE;
	}
}

if (isset($_POST['newthread']) || isset($_POST['reply']))
{
	$poster = array();
	if ((isset($_POST['newthread']) && trim($_POST['subject']) == "") || trim($_POST['post']) == "")
	{
		message_handler("ALERT", 5);
	}
	else
	{
		if ($fp->flood("forum_t", "thread_datestamp") == FALSE && !ADMIN)
		{
			echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php'</script>\n";
		}
		if (USER)
		{
			$poster['post_userid'] = USERID;
			$poster['post_user_name'] = USERNAME;
		}
		else
		{
			$poster = getuser($_POST['anonname']);
			if ($poster == -1)
			{
				require_once(HEADERF);
				$ns->tablerender(LAN_20, LAN_310);
				if (isset($_POST['reply']))
				{
					$tmpdata = "reply.".$tp -> toDB($_POST['anonname']).".".$tp -> toDB($_POST['subject']).".".$tp -> toDB($_POST['post']);
				}
				else
				{
					$tmpdata = "newthread^".$tp -> toDB($_POST['anonname'])."^".$tp -> toDB($_POST['subject'])."^".$tp -> toDB($_POST['post']);
				}
				$sql->db_Insert("tmp", "'$ip', '".time()."', '$tmpdata' ");
				loginf();
				require_once(FOOTERF);
				exit;
			}
		}
		process_upload();

		$post = $tp->toDB($_POST['post']);
		$subject = $tp->toDB($_POST['subject']);
		$email_notify = ($_POST['email_notify'] ? 99 : 1);
		if ($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != "")
		{
			$subject = "[".LAN_402."] ".$subject;
		}

		$threadtype = (MODERATOR ? intval($_POST['threadtype']) : 0);
		if (isset($_POST['reply']))
		{
			$parent = $id;
			$forum_id = $thread_info['head']['thread_forum_id'];
		}
		else
		{
			$parent = 0;
			$forum_id = $id;
		}

		$iid = $forum->thread_insert($subject, $post, $forum_id, $parent, $poster, $email_notify, $threadtype, $forum_info['forum_sub']);
		if($iid === -1)
		{
			require_once(HEADERF);
			$ns->tablerender("", LAN_FORUM_2);
			require_once(FOOTERF);
			exit;
		}
		if (isset($_POST['reply'])) {
			$iid = $parent;
		}

		if ($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != "" && isset($_POST['newthread'])) {
			require_once(e_PLUGIN."poll/poll_class.php");
			$_POST['iid'] = $iid;
			$poll = new poll;
			$poll -> submit_poll(2);
		}

		$e107cache->clear("newforumposts");
		if ($pref['forum_redirect'])
		{
			redirect(e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last");
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

			echo (isset($_POST['newthread']) ? $FORUMTHREADPOSTED : $FORUMREPLYPOSTED);
			require_once(FOOTERF);
			exit;
		}
	}
}
require_once(HEADERF);

if (isset($_POST['update_thread']))
{
	if (!$_POST['subject'] || !$_POST['post'])
	{
		$error = "<div style='text-align:center'>".LAN_27."</div>";
	}
	else
	{
		if (!isAuthor())
		{
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}

		$newvals['thread_edit_datestamp'] = time();
		$newvals['thread_thread'] = $_POST['post'];
		$newvals['thread_name'] = $_POST['subject'];
		$newvals['thread_active'] = (isset($_POST['email_notify'])) ? '99' : '1';	// Always set in case it's changed
		if (isset($_POST['threadtype']) && MODERATOR)
		{
			$newvals['thread_s'] = $_POST['threadtype'];
		}
		$forum->thread_update($id, $newvals);
		$e107cache->clear("newforumposts");
		$url = e_PLUGIN."forum/forum_viewtopic.php?{$thread_info['head']['thread_id']}.0";
		echo "<script type='text/javascript'>document.location.href='".$url."'</script>\n";
	}
}

if (isset($_POST['update_reply']))
{
	if (!$_POST['post'])
	{
		$error = "<div style='text-align:center'>".LAN_27."</div>";
	}
	else
	{
		if (!isAuthor())
		{
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}
		$url = e_PLUGIN."forum/forum_viewtopic.php?{$id}.post";
		echo "<script type='text/javascript'>document.location.href='".$url."'</script>\n";
		$newvals['thread_edit_datestamp'] = time();
		$newvals['thread_thread'] = $_POST['post'];
		$forum->thread_update($id, $newvals);
		$e107cache->clear("newforumposts");
		$url = e_PLUGIN."forum/forum_viewtopic.php?{$id}.post";
		echo "<script type='text/javascript'>document.location.href='".$url."'</script>\n";
	}
}

if ($error)
{
	$ns->tablerender(LAN_20, $error);
}


if ($action == 'edit' || $action == 'quote')
{
 	if ($action == "edit")
	{
		if (!isAuthor())
		{
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}
	}

	if(!is_array($thread_info[0]))
	{
		 $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_96."</div>");
		 require_once(FOOTERF);
		exit;
	}

	$thread_info[0]['user_name'] = $forum->thread_user($thread_info[0]);
	if (!isset($_POST['fpreview']))
	{
		$subject = $thread_info['0']['thread_name'];
		$post = $tp->toForm($thread_info[0]['thread_thread']);
	}
	$post = preg_replace("/&lt;span class=&#39;smallblacktext&#39;.*\span\>/", "", $post);

	if ($action == 'quote') {
		$post = preg_replace("#\[hide].*?\[/hide]#s", "", $post);
		$tmp = explode(chr(1), $thread_info[0]['user_name']);
		$timeStamp = time();
		$post = "[quote{$timeStamp}={$tmp[0]}]\n".$post."\n[/quote{$timeStamp}]\n";
		$eaction = FALSE;
		$action = 'reply';
	} else {
		$eaction = TRUE;
		if ($thread_info['0']['thread_parent']) {
			$action = "reply";
		} else {
			$action = "nt";
			$sact = "canc";	// added to override the bugtracker query below
		}
	}
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Load forumpost template

if (!$FORUMPOST)
{
  if (is_readable(THEME."forum_post_template.php"))
  {
    include_once(THEME."forum_post_template.php");
  }
  else
  {
  include_once(e_PLUGIN."forum/templates/forum_post_template.php");
  }
}
/* check post access (bugtracker #1424) */

if($action == "rp" && !$sql -> db_Select("forum_t", "*", "thread_id='{$id}'"))
{
	$ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
	 require_once(FOOTERF);
	exit;
}
elseif($action == "nt")
{
  if (!$sact && !$sql -> db_Select("forum", "*", "forum_id='{$id}'"))
  {
	$ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
	require_once(FOOTERF);
	exit;
  }
}
else
{
  // DB access should pass - after all, the thread should exist
	$sql->db_Select_gen("SELECT t.*, p.forum_postclass FROM #forum_t AS t 
	LEFT JOIN #forum AS p ON t.thread_forum_id=p.forum_id WHERE thread_id='{$id}'");
	$fpr = $sql -> db_Fetch();
	if(!check_class($fpr['forum_postclass']))
	{
		$ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
		require_once(FOOTERF);
		exit;
	}
}

if($action == 'rp')
{
	$FORUMPOST = $FORUMPOST_REPLY;
}
$text = $tp->parseTemplate($FORUMPOST, FALSE, $forum_post_shortcodes);

// -------------------------------------------------------------------------------------------------------------------------------------------------------------

if ($pref['forum_enclose'])
{
	$ns->tablerender($pref['forum_title'], $text);
}
else
{
	echo $text;
}

function isAuthor()
{
	global $thread_info;
	$tmp = explode(".", $thread_info[0]['thread_user'], 2);
	return ($tmp[0] == USERID || MODERATOR);
}

function getuser($name)
{
	global $tp, $sql, $e107;
	$ret = array();
	$ip = $e107->getip();
	$name = str_replace("'", "", $name);
	if (!$name)
	{
		// anonymous guest
//		$name = "0.".LAN_311.chr(1).$ip;
		$ret['post_userid'] = "0";
		$ret['post_user_name'] = LAN_311;
		return $ret;
	}
	else
	{
		if ($sql->db_Select("user", "user_id, user_ip", "user_name='".$tp -> toDB($name)."'"))
		{
			$row = $sql->db_Fetch();
			if ($row['user_ip'] == $ip)
			{
				$ret['post_userid'] = $row['user_id'];
				$ret['post_user_name'] = $name;
			}
			else
			{
				return -1;
			}
		}
		else
		{
//			$name = "0.".substr($tp->toDB($name), 0, 20).chr(1).$ip;
			$ret['post_userid'] = "0";
			$ret['post_user_name'] = $tp->toDB($name);
		}
	}
	return $ret;
}

function loginf() {
	$text .= "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'><p>
		".LAN_16."<br />
		<input class='tbox' type='text' name='username' size='15' value='' maxlength='20' />\n
		<br />
		".LAN_17."
		<br />
		<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n
		<br />
		<input class='button' type='submit' name='userlogin' value='".LAN_10."' />\n
		<br />
		<input type='checkbox' name='autologin' value='1' /> ".LAN_11."
		<br /><br />
		[ <a href='".e_SIGNUP."'>".LAN_174."</a> ]<br />[ <a href='".e_BASE."fpw.php'>".LAN_212."</a> ]
		</p>
		</form>
		</div>";
	$ns = new e107table;
	$ns->tablerender(LAN_175, $text);
}

function forumjump()
{
	global $forum;
	$jumpList = $forum->forum_get_allowed();
	$text = "<form method='post' action='".e_SELF."'><p>".LAN_401.": <select name='forumjump' class='tbox'>";
	foreach($jumpList as $key => $val)
	{
		$text .= "\n<option value='".$key."'>".$val."</option>";
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_387."' /></p></form>";
	return $text;
}

function redirect($url)
{
	echo "<script type='text/javascript'>document.location.href='".$url."'</script>\n";
}

function process_upload()
{
	global $pref, $forum_info, $thread_info, $admin_log;

	if(isset($thread_info['head']['thread_id']))
	{
		$tid = $thread_info['head']['thread_id'];
	}
	else
	{
		$tid = 0;
	}

	if (isset($_FILES['file_userfile']['error']))
	{
		require_once(e_HANDLER."upload_handler.php");
		if ($uploaded = file_upload('/'.e_FILE."public/", "attachment", "FT{$tid}_"))
		{
			foreach($uploaded as $upload)
			{
			  if ($upload['error'] == 0)
			  {
				$fpath = "{e_FILE}public/";
				if(strstr($upload['type'], "image"))
				{
					if(isset($pref['forum_maxwidth']) && $pref['forum_maxwidth'] > 0)
					{
						require_once(e_HANDLER."resize_handler.php");
						$orig_file = $upload['name'];
						$p = strrpos($orig_file,'.');
						$new_file = substr($orig_file, 0 , $p)."_".substr($orig_file, $p);
						if(resize_image(e_FILE.'public/'.$orig_file, e_FILE.'public/'.$new_file, $pref['forum_maxwidth']))
						{
							if($pref['forum_linkimg'])
							{
								$parms = image_getsize(e_FILE.'public/'.$new_file);
								$_POST['post'] .= "[br][link=".$fpath.$orig_file."][img{$parms}]".$fpath.$new_file."[/img][/link][br]";
								//show resized, link to fullsize
							}
							else
							{
								@unlink(e_FILE.'public/'.$orig_file);
								//show resized
								$parms = image_getsize(e_FILE.'public/'.$new_file);
								$_POST['post'] .= "[br][img{$parms}]".$fpath.$new_file."[/img][br]";
							}
						}
						else
						{	//resize failed, show original
							$parms = image_getsize(e_FILE.'public/'.$upload['name']);
							$_POST['post'] .= "[br][img{$parms}]".$fpath.$upload['name']."[/img]";
						}
					}
					else
					{	//resizing disabled, show original
						$parms = image_getsize(e_FILE.'public/'.$upload['name']);
						//resizing disabled, show original
						$_POST['post'] .= "[br]<div class='spacer'>[img{$parms}]".$fpath.$upload['name']."[/img]</div>\n";
					}
				}
				else
				{
					//upload was not an image, link to file
					$_POST['post'] .= "[br][file=".$fpath.$upload['name']."]".(isset($upload['rawname']) ? $upload['rawname'] : $upload['name'])."[/file]";
				}
			  }
			  else
			  {  // Error in uploaded file
			    echo "Error in uploaded file: ".(isset($upload['rawname']) ? $upload['rawname'] : $upload['name'])."<br />";
			  }
			}
		}
	}
}

function image_getsize($fname)
{
	if($imginfo = getimagesize($fname))
	{
		return ":width={$imginfo[0]}&height={$imginfo[1]}";
	}
	else
	{
		return "";
	}
}


require_once(FOOTERF);

?>