<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_post.php,v $
|     $Revision: 1.14 $
|     $Date: 2005-02-24 18:39:55 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
$WYSIWYG = TRUE;
$lan_file = e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_post.php';
include(file_exists($lan_file) ? $lan_file : e_PLUGIN.'forum/languages/English/lan_forum_post.php');

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
if ($action == 'rp') {
	// reply to thread
	$thread_info = $forum->thread_get($id, 0, 10);
	$forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
} elseif ($action == 'nt') {
	// New post
	$forum_info = $forum->forum_get($id);
} elseif ($action == 'quote' || $action == 'edit') {
	$thread_info = $forum->thread_get_postinfo($id, TRUE);
	$forum_info = $forum->forum_get($thread_info['head']['thread_forum_id']);
}

if (!check_class($forum_info['forum_class'])) {
	require_once(HEADERF);
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
	require_once(FOOTERF);
	exit;
}

//require_once(e_HANDLER.'forum_include.php');
require_once(e_PLUGIN."forum/forum_post_shortcodes.php");
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

//If anonymous posting is off and not logged it, show warning
if (ANON == FALSE && USER == FALSE) {
	require_once(HEADERF);
	$text .= "<div style='text-align:center'>".LAN_45." <a href='".e_SIGNUP."'>".LAN_411."</a> ".LAN_412."</div>";
	$ns->tablerender(LAN_20, $text);
	require_once(FOOTERF);
	exit;
}

//if readonly forum and not admin, show warning
if ($forum_info['forum_class'] == e_UC_READONLY && !ADMIN) {
	$text .= "<div style='text-align:center'>".LAN_398."</div>";
	$ns->tablerender(LAN_20, $text);
	require_once(FOOTERF);
	exit;
}

//if thread is not active and not new thread, show warning
if ($action != "nt" && !$thread_info['head']['thread_active']) {
	require_once(HEADERF);
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_397."</div>");
	require_once(FOOTERF);
	exit;
}

$forum_info['forum_name'] = $tp -> toHTML($forum_info['forum_name'], TRUE);

define("e_PAGETITLE", LAN_01." / ".$forum_info['forum_name']." / ".($action == "rp" ? LAN_02.$forum_info['thread_name'] : LAN_03));
require_once(HEADERF);

// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

if (IsSet($_POST['addoption']) && $_POST['option_count'] < 10) {
	$_POST['option_count']++;
	$anonname = $tp->post_toForm($_POST['anonname']);
	$subject = $tp->post_toForm($_POST['subject']);
	$post = $tp->post_toForm($_POST['post']);
}

if (IsSet($_POST['submitpoll'])) {
	require_once(e_HANDLER."poll_class.php");
	$poll = new poll;
	$poll->submit_poll(0, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], 0, $forum_id, "forum");

	require_once(HEADERF);
	if (!$FORUMPOST) {
		if (file_exists(THEME."forum_posted_template.php")) {
			require_once(THEME."forum_posted_template.php");
		} else {
			require_once(e_PLUGIN."forum/templates/forum_posted_template.php");
		}
	}

	echo $FORUMPOLLPOSTED;
	require_once(FOOTERF);
	exit;
}

if (IsSet($_POST['fpreview'])) {
	process_upload();	
	if (USER) {
		$poster = USERNAME;
	} else {
		$poster = ($_POST['anonname']) ? $_POST['anonname'] :
		 LAN_311;
	}
	$postdate = $gen->convert_date(time(), "forum");
	$tsubject = $tp->post_toHTML($_POST['subject'], FALSE);
	$tpost = $tp->post_toHTML($_POST['post']);

	if ($_POST['poll_title'] != "" && $pref['forum_poll']) {
		require_once(e_HANDLER."poll_class.php");
		$poll = new poll;
		$poll_text .= $poll->render_poll($_POST['existing'], $_POST['poll_title'], $_POST['poll_option'], array($votes), "preview", "forum");
		$count = 0;
		while ($_POST['poll_option'][$count]) {
			$_POST['poll_option'][$count] = $tp->post_toForm($_POST['poll_option'][$count]);
			$count++;
		}
		$_POST['poll_title'] = $tp->post_toForm($_POST['poll_title']);
	}


	if (!$FORUM_PREVIEW) {
		if (file_exists(THEME."forum_preview_template.php")) {
			require_once(THEME."forum_preview_template.php");
		} else {
			require_once(e_PLUGIN."forum/templates/forum_preview_template.php");
		}
	}

	$text = $FORUM_PREVIEW;

	if ($poll_text) {
		$ns->tablerender($_POST['poll_title'], $poll_text);
	}
	$ns->tablerender(LAN_323, $text);
	$anonname = $tp->post_toHTML($_POST['anonname'], FALSE);
	$post = $tp->post_toHTML($_POST['post'], FALSE);
	$subject = $tp->post_toHTML($_POST['subject'], FALSE);

	if ($action == "edit") {
		if ($_POST['subject']) {
			$action = "nt";
		} else {
			$action = "reply";
		}
		$eaction = TRUE;
	}
	else if($action == "quote") {
		$action = "reply";
		$eaction = FALSE;
	}
}


if (isset($_POST['newthread']) || isset($_POST['reply'])) {
	if ((isset($_POST['newthread']) && trim(chop($_POST['subject'])) == "") || trim(chop($_POST['post'])) == "") {
		message_handler("ALERT", 5);
	} else {
		if ($fp->flood("forum_t", "thread_datestamp") == FALSE) {
			header("location: ".e_BASE."index.php");
			exit;
		}
		if (USER) {
			$user = USERID;
		} else {
			$user = getuser($_POST['anonname']);
			if (intval($user)) {
				require_once(HEADERF);
				$ns->tablerender(LAN_20, LAN_310);
				if (isset($_POST['reply'])) {
					$tmpdata = "reply.".$_POST['anonname'].".".$_POST['subject'].".".$_POST['post'];
				} else {
					$tmpdata = "newthread^".$_POST['anonname']."^".$_POST['subject']."^".$_POST['post'];
				}
				$sql->db_Insert("tmp", "'$ip', '".time()."', '$tmpdata' ");
				loginf();
				require_once(FOOTERF);
				exit;
			} else {
				list($user, $anon_info) = explode('.', $user, 2);
			}
		}
		process_upload();

		$post = $tp->toDB($_POST['post']);
		$subject = $tp->toDB($_POST['subject']);
		$email_notify = ($_POST['email_notify'] ? 99 : 1);
		if ($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != "") {
			$subject = "[".LAN_402."] ".$subject;
		}

		if ($_POST['threadtype'] == 2) {
			$subject = "[".LAN_403."] ".$subject;
		} elseif($_POST['threadtype'] == 1) {
			$subject = "[".LAN_404."] ".$subject;
		}

		$threadtype = intval($_POST['threadtype']);
		if (isset($_POST['reply'])) {
			$parent = $thread_info['head']['thread_id'];
			$forum_id = $thread_info['head']['thread_forum_id'];
		} else {
			$parent = 0;
			$forum_id = $id;
		}
		$iid = $forum->thread_insert($subject, $post, $forum_id, $parent, $user, $email_notify, $threadtype, $anon_info);
		if (isset($_POST['reply'])) {
			$iid = $parent;
		}

		if ($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != "" && isset($_POST['newthread'])) {
			require_once(e_HANDLER."poll_class.php");
			$poll = new poll;
			$poll->submit_poll(0, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], 0, $iid, "forum");
		}

		if ($pref['forum_redirect']) {
			redirect(e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last");
		} else {
			require_once(HEADERF);
			if (!$FORUMPOST) {
				if (file_exists(THEME."forum_posted_template.php")) {
					require_once(THEME."forum_posted_template.php");
				} else {
					require_once(e_PLUGIN."forum/templates/forum_posted_template.php");
				}
			}

			echo (isset($_POST['newthread']) ? $FORUMTHREADPOSTED : $FORUMREPLYPOSTED);
			$e107cache->clear("newforumposts");
			require_once(FOOTERF);
			exit;
		}
	}
}


if (isset($_POST['update_thread'])) {
	if (!$_POST['subject'] || !$_POST['post']) {
		$error = "<div style='text-align:center'>".LAN_27."</div>";
	} else {
		if (!isAuthor($id)) {
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}
		$newvals['thread_edit_datestamp'] = time();
		$newvals['thread_thread'] = $tp->toDB($_POST['post']);
		$newvals['thread_name'] = $tp->toDB($_POST['subject']);
		$newvals['thread_active'] = ($_POST['email_notify']) ? '99' :
		 '1';
		if (isset($_POST['threadtype'])) {
			$newvals['thread_s'] = $_POST['threadtype'];
		}
		$forum->thread_update($id, $newvals);
		$e107cache->clear("newforumposts");
		header("location:".e_PLUGIN."forum/forum_viewtopic.php?{$thread_info['head']['thread_id']}.0");
		exit;
	}
}

if (IsSet($_POST['update_reply'])) {
	if (!$_POST['post']) {
		$error = "<div style='text-align:center'>".LAN_27."</div>";
	} else {
		if (!isAuthor($id)) {
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}
		$newvals['thread_edit_datestamp'] = time();
		$newvals['thread_thread'] = $tp->toDB($_POST['post']);
		$forum->thread_update($id, $newvals);
		$e107cache->clear("newforumposts");
		header("location:".e_PLUGIN."forum/forum_viewtopic.php?{$thread_info['head']['thread_id']}.{$from}");
		exit;
	}
}

if ($error) {
	$ns->tablerender(LAN_20, $error);
}

if ($action == 'edit' || $action == 'quote') {
	if ($action == "edit") {
		if ((!$thread_info[0]['thread_user'] || $thread_info[0]['thread_user'] != USERID) && !ADMIN) {
			$ns->tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
			require_once(FOOTERF);
			exit;
		}
	}

	$thread_info[0]['user_name'] = $forum->thread_user($thread_info[0]);
	$subject = $thread_info['0']['thread_name'];
	$post = $tp->toForm($thread_info[0]['thread_thread']);
	$post = ereg_replace("&lt;span class=&#39;smallblacktext&#39;.*\span\>", "", $post);
	if ($action == 'quote') {
		$timeStamp = time();
		$post = "[quote{$timeStamp}={$thread_info[0]['user_name']}]\n".$post."\n[/quote{$timeStamp}]\n";
		$eaction = FALSE;
		$action = 'reply';
	} else {
		$eaction = TRUE;
		if ($thread_info['0']['thread_parent']) {
			$action = "reply";
		} else {
			$action = "nt";
		}
	}
}

// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//Load forumpost template

if (!$FORUMPOST) {
	if (file_exists(THEME."forum_post_template.php")) {
		require_once(THEME."forum_post_template.php");
	} else {
		require_once(e_PLUGIN."forum/templates/forum_post_template.php");
	}
}

// template definitions ...

$FORMSTART = "<form enctype='multipart/form-data' method='post' action='".e_SELF."?".e_QUERY."' name='dataform'>";

$BACKLINK = "<a class='forumlink' href='".e_PLUGIN."forum/forum.php'>".LAN_405."</a>-><a class='forumlink' href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_info['forum_id']."'>".$forum_info['forum_name']."</a>->".
($action == "nt" ? ($eaction ? LAN_77 : LAN_60) : ($eaction ? LAN_78 : LAN_406." ".$thread_info['head']['thread_name']));
$USERBOX = (ANON == TRUE && USER == FALSE ? $userbox : "");
$SUBJECTBOX = ($action == "nt" ? $subjectbox : "");
$POSTTYPE = ($action == "nt" ? LAN_63 : LAN_73);
$POSTBOX = "<textarea class='tbox' name='post' cols='70' rows='10' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$post</textarea>\n<br />\n";
if(!check_class($pref['wysiwyg'])){
	$POSTBOX .= ren_help(2);
	require_once(e_HANDLER."emote.php");
	$EMOTES = r_emote();
}




$emailnotify = "";
if ($pref['email_notify'] && $action == "nt")
{
	$emailnotify = "<span class='defaulttext'>".LAN_380."</span><input type='checkbox' name='email_notify' value='1' ".($_POST['email_notify'] || $thread_info['head']['thread_active'] == 99 ? "checked = 'checked'" : "").">";
}
$EMAILNOTIFY = $emailnotify;

$postthreadas = "";
if (ADMIN && getperms("5") && $action == "nt")
{
	$thread_s = (isset($_POST['threadtype']) ? $_POST['threadtype'] : $thread_info['head']['thread_s']);
	$postthreadas = "<br /><span class='defaulttext'>".LAN_400."<input name='threadtype' type='radio' value='0' ".(!$thread_s ? "checked='checked' " : "").">".LAN_1."&nbsp;<input name='threadtype' type='radio' value='1' ".($thread_s == 1 ? "checked='checked' " : "").">".LAN_2."&nbsp;<input name='threadtype' type='radio' value='2' ".($thread_s == 2 ? "checked='checked' " : "").">".LAN_3."</span>";
}
$POSTTHREADAS = $postthreadas;

if ($action == "nt" && $pref['forum_poll'] && !eregi("edit", e_QUERY))
{
	$POLL = $poll;
}

if ($pref['forum_attach'] && !eregi("edit", e_QUERY) && (check_class($pref['upload_class']) || getperms('0')))
{
	$FILEATTACH = $fileattach;
}

$buttons = "<input class='button' type='submit' name='fpreview' value='".LAN_323."' /> ";
if ($action != "nt") {
	$buttons .= ($eaction ? "<input class='button' type='submit' name='update_reply' value='".LAN_78."' />" : "<input class='button' type='submit' name='reply' value='".LAN_74."' />");
} else {
	$buttons .= ($eaction ? "<input class='button' type='submit' name='update_thread' value='".LAN_77."' />" : "<input class='button' type='submit' name='newthread' value='".LAN_64."' />");
}

$BUTTONS = $buttons;
$FORMEND = "</form>";
$FORUMJUMP = forumjump();

$text = preg_replace("/\{(.*?)\}/e", '$\1', $FORUMPOST);



// -------------------------------------------------------------------------------------------------------------------------------------------------------------



if ($action == 'rp') {
	$tmp_template = "
		<div style='text-align:center'>
		{THREADTOPIC}
		{LATESTPOSTS}
		</div>
		";
	$text .= $tp->parseTemplate($tmp_template, FALSE, $forum_post_shortcodes);
}

if ($pref['forum_enclose']) {
	$ns->tablerender($pref['forum_title'], $text);
} else {
	echo $text;
}

function isAuthor($thread) {
	global $sql;
	$sql->db_Select("forum_t", "thread_user", "thread_id='".$thread."' ");
	$row = $sql->db_Fetch();
	return ($row['thread_user'] == USERID || ADMIN === TRUE);
}

function getuser($name) {
	global $tp;
	global $sql;
	$name = preg_replace("#\'#", "", $name);
	if (!$name) {
		// anonymous guest
		$name = "0.".LAN_311.chr(1).$ip;
	} else {
		if ($sql->db_Select("user", "user_id, user_ip", "user_name='$name' ")) {
			$row = $sql->db_Fetch();
			if ($row['user_ip'] == $ip) {
				$name = $row['user_id'];
			} else {
				return FALSE;
			}
		} else {
			$ip = getip();
			$name = "0.".substr($tp->toDB($name), 0, 20).chr(1).$ip;
		}
	}
	return $name;
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
		[ <a href='".e_BASE."signup.php'>".LAN_174."</a> ]<br />[ <a href='fpw.php'>".LAN_212."</a> ]
		</p>
		</form>
		</div>";
	$ns = new e107table;
	$ns->tablerender(LAN_175, $text);
}
function forumjump() {
	global $sql;
	$sql->db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
	$text .= "<form method='post' action='".e_SELF."'><p>".LAN_401.": <select name='forumjump' class='tbox'>";
	while ($row = $sql->db_Fetch()) {
		extract($row);
		// if(($forum_class && check_class($forum_class)) || ($forum_class == 254 && USER) || !$forum_class){
		if (check_class($forum_class)) {
			$text .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
		}
	}
	$text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_387."' /></p></form>";
	return $text;
}
function redirect($url) {
	global $ns;
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) {
		header('Refresh: 0; URL=' .$url);
		$text = "<div style='text-align:center'>".LAN_408."<a href='".$url."'> ".LAN_409." </a>".LAN_410."</div>";
		$ns->tablerender(LAN_407, $text);
		require_once(FOOTERF);
		exit;
	}

	header('Location: ' . $url);
	exit;
}

function process_upload()
{
	global $pref, $forum_info, $thread_info;
	
	if(isset($thread_info['head']['thread_id']))
	{
		$tid = $thread_info['head']['thread_id'];
	}
	else
	{
		$tid = 0;
	}
	
	if (isset($_FILES['file_userfile']['error']) && $_FILES['file_userfile']['error'] != 4) {
		require_once(e_HANDLER."upload_handler.php");
		if ($uploaded = file_upload('/'.e_FILE."public/", "attachment", "FT{$tid}_")) {
			if(strstr($uploaded[0]['type'], "image"))
			{
				if(isset($pref['forum_maxwidth']) && $pref['forum_maxwidth'] > 0)
				{
					require_once(e_HANDLER."resize_handler.php");
					$orig_file = $uploaded[0]['name'];
					$p = strrpos($orig_file,'.');
					$new_file = substr($orig_file, 0 , $p)."_".substr($orig_file, $p);
					$fpath = e_FILE."public/";
					if(resize_image($fpath.$orig_file, $fpath.$new_file, $pref['forum_maxwidth']))
					{
						if($pref['forum_linkimg'])
						{
							$parms = image_getsize($fpath.$new_file);
							$_POST['post'] .= "[link=".$fpath.$orig_file."][img{$parms}]".$fpath.$new_file."[/img][/link]";
							//show resized, link to fullsize
						}
						else
						{
							@unlink($fpath.$orig_file);
							//show resized
							$parms = image_getsize($fpath.$new_file);
							$_POST['post'] .= "[img{$parms}]".$fpath.$new_file."[/img] \n";
						}
					}
					else
					{
						//resize failed, show original 
						$parms = image_getsize(e_FILE."public/".$uploaded[0]['name']);
						$_POST['post'] .= "[img{$parms}]".e_FILE."public/".$uploaded[0]['name']."[/img] \n";
					}
				}
				else
				{
					$parms = image_getsize(e_FILE."public/".$uploaded[0]['name']);
					//resizing disabled, show original
					$_POST['post'] .= "[img{$parms}]".e_FILE."public/".$uploaded[0]['name']."[/img]";
				}
			}
			else
			{
				//upload was not an image, link to file
				$_POST['post'] .= "[file=".e_FILE."public/".$uploaded[0]['name']."]".$uploaded[0]['name']."[/file]";
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