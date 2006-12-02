<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$forum_post_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN LATESTPOSTS
global $thread_info, $action, $gen, $tp, $forum_shortcodes, $post_info;
global $LATESTPOSTS_START, $LATESTPOSTS_END, $LATESTPOSTS_POST;
$txt = $tp->parseTemplate($LATESTPOSTS_START, TRUE, $forum_shortcodes);
for($i = count($thread_info)-2; $i>0; $i--)
{
	$post_info = $thread_info[$i];
	$txt .= $tp->parseTemplate($LATESTPOSTS_POST, TRUE, $forum_shortcodes);
}
$txt .= $tp->parseTemplate($LATESTPOSTS_END, TRUE, $forum_shortcodes);
return $txt;
SC_END

SC_BEGIN THREADTOPIC
global $thread_info, $action, $gen, $tp, $post_info, $forum_shortcodes, $THREADTOPIC_REPLY;
$post_info = $thread_info['head'];
return $tp->parseTemplate($THREADTOPIC_REPLY, TRUE, $forum_shortcodes);
SC_END

SC_BEGIN FORMSTART
return "<form enctype='multipart/form-data' method='post' action='".e_SELF."?".e_QUERY."' id='dataform'>";
SC_END

SC_BEGIN FORMEND
return "</form>";
SC_END

SC_BEGIN FORUMJUMP
return forumjump();
SC_END

SC_BEGIN USERBOX
global $userbox;
return (USER == FALSE ? $userbox : "");
SC_END

SC_BEGIN SUBJECTBOX
global $subjectbox, $action;
return ($action == "nt" ? $subjectbox : "");
SC_END

SC_BEGIN POSTTYPE
global $action;
return ($action == "nt" ? LAN_63 : LAN_73);
SC_END

SC_BEGIN POSTBOX
global $post, $pref;
$rows = (e_WYSIWYG) ? 15 : 10;
$ret = "<textarea class='tbox' id='post' name='post' cols='70' rows='{$rows}' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$post</textarea>\n<br />\n";
if(!e_WYSIWYG)
{
	$ret .= display_help('helpb', 'forum');
}
return $ret;
SC_END

SC_BEGIN BUTTONS
global $action, $eaction;
$ret = "<input class='button' type='submit' name='fpreview' value='".LAN_323."' /> ";
if ($action != "nt")
{
	$ret .= ($eaction ? "<input class='button' type='submit' name='update_reply' value='".LAN_78."' />" : "<input class='button' type='submit' name='reply' value='".LAN_74."' />");
}
else
{
	$ret .= ($eaction ? "<input class='button' type='submit' name='update_thread' value='".LAN_77."' />" : "<input class='button' type='submit' name='newthread' value='".LAN_64."' />");
}
return $ret;
SC_END

SC_BEGIN FILEATTACH
global $pref, $fileattach, $fileattach_alert;

if ($pref['forum_attach'] && strpos(e_QUERY, "edit") === FALSE && (check_class($pref['upload_class']) || getperms('0')))
{
	if (is_writable(e_FILE."public"))
	{
		return $fileattach;
	}
	else
	{
		$FILEATTACH = "";
		if(ADMIN)
		{
			if(!$fileattach_alert)
			{
				$fileattach_alert = "<tr><td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_390 : LAN_416)."</td></tr><tr><td colspan='2' class='forumheader3'>".LAN_FORUM_1."</td></tr>\n";
			}
			return $fileattach_alert;
		}
	}
}
SC_END

SC_BEGIN POSTTHREADAS
global $action, $thread_info;
if (MODERATOR && $action == "nt")
{
	$thread_s = (isset($_POST['threadtype']) ? $_POST['threadtype'] : $thread_info['head']['thread_s']);
	return "<br /><span class='defaulttext'>".LAN_400."<input name='threadtype' type='radio' value='0' ".(!$thread_s ? "checked='checked' " : "")." />".LAN_1."&nbsp;<input name='threadtype' type='radio' value='1' ".($thread_s == 1 ? "checked='checked' " : "")." />".LAN_2."&nbsp;<input name='threadtype' type='radio' value='2' ".($thread_s == 2 ? "checked='checked' " : "")." />".LAN_3."</span>";
}
return "";
SC_END

SC_BEGIN BACKLINK
global $forum, $thread_info,$eaction, $action,$BREADCRUMB;
$forum->set_crumb(TRUE,($action == "nt" ? ($eaction ? LAN_77 : LAN_60) : ($eaction ? LAN_78 : LAN_406." ".$thread_info['head']['thread_name'])));
return $BREADCRUMB;
SC_END

SC_BEGIN EMAILNOTIFY
global $pref, $thread_info, $action;
if ($pref['email_notify'] && $action == "nt")
{
	if(isset($_POST['fpreview']))
	{
		$chk = ($_POST['email_notify'] ? "checked = 'checked'" : "");
	}
	else
	{
		if(isset($thread_info))
		{
			$chk = ($thread_info['head']['thread_active'] == 99 ? "checked='checked'" : "");
		}
		else
		{
			$chk = ($pref['email_notify_on'] ? "checked='checked'" : "");
		}
	}
	return "<span class='defaulttext'>".LAN_380."</span><input type='checkbox' name='email_notify' value='1' {$chk} />";
}
return "";
SC_END

SC_BEGIN POLL
global $poll_form, $action, $pref;
if ($action == "nt" && $pref['forum_poll'] && strpos(e_QUERY, "edit") === FALSE)
{
	return $poll_form;
}
return "";
SC_END

*/
?>