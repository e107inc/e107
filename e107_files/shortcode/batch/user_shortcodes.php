<?php
include_once(e_HANDLER.'shortcode_handler.php');
$user_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*
SC_BEGIN TOTAL_CHATPOSTS
global $sql;
if(!$chatposts = getcachedvars('total_chatposts'))
{
	$chatposts = $sql->db_Count("chatbox");
	cachevars('total_chatposts', $chatposts);
}
return $chatposts;
SC_END

SC_BEGIN TOTAL_COMMENTPOSTS
global $sql;
if(!$commentposts = getcachedvars('total_commentposts'))
{
	$commentposts = $sql->db_Count("comments");
	cachevars('total_commentposts', $commentposts);
}
return $commentposts;
SC_END

SC_BEGIN TOTAL_FORUMPOSTS
global $sql;
if(!$forumposts = getcachedvars('total_forumposts'))
{
	$forumposts = $sql->db_Count("forum_t");
	cachevars('total_forumposts', $forumposts);
}
return $forumposts;
SC_END

SC_BEGIN USER_COMMENTPOSTS
global $user;
return $user['user_comments'];
SC_END

SC_BEGIN USER_FORUMPOSTS
global $user;
return $user['user_forums'];
SC_END

SC_BEGIN USER_CHATPOSTS
global $user;
return $user['user_chats'];
SC_END

SC_BEGIN USER_CHATPER
global $sql, $user;
if(!$chatposts = getcachedvars('total_chatposts'))
{
	$chatposts = $sql->db_Count("chatbox");
	cachevars('total_chatposts', $chatposts);
}
return round(($user['user_chats']/$chatposts) * 100, 2);
SC_END

SC_BEGIN USER_COMMENTPER
global $sql, $user;
if(!$commentposts = getcachedvars('total_commentposts'))
{
	$commentposts = $sql->db_Count("comments");
	cachevars('total_commentposts', $commentposts);
}
return round(($user['user_comments']/$commentposts) * 100, 2);
SC_END

SC_BEGIN USER_FORUMPER
global $sql, $user;
if(!$forumposts = getcachedvars('total_forumposts'))
{
	$forumposts = $sql->db_Count("forum_t");
	cachevars('total_forumposts', $forumposts);
}
return round(($user['user_forums']/$forumposts) * 100, 2);
SC_END

SC_BEGIN USER_LEVEL
global $user;
require_once(e_HANDLER."level_handler.php");
$ldata = get_level($user['user_id'], $user['user_forums'], $user['user_comments'], $user['user_chats'], $user['user_visits'], $user['user_join'], $user['user_admin'], $user['user_perms'], $user['pref']);

if (strstr($ldata[0], "IMAGE_rank_main_admin_image")) {
	return LAN_417;
}
else if(strstr($ldata[0], "IMAGE")) {
	return LAN_418;
}
else
{
	return $USER_LEVEL = $ldata[1];
}
SC_END

SC_BEGIN USER_LASTVISIT
global $user;
$gen = new convert;
return $user['user_currentvisit'] ? $gen->convert_date($user['user_currentvisit'], "long") : "<i>".LAN_401."</i>";
SC_END

SC_BEGIN USER_LASTVISIT_LAPSE
global $user;
$gen = new convert;
return $user['user_currentvisit'] ? "( ".$gen -> computeLapse($user['user_currentvisit'])." ".LAN_426." )" : "<i>".LAN_401."</i>";
SC_END

SC_BEGIN USER_VISITS
global $user;
return $user['user_visits'];
SC_END

SC_BEGIN USER_JOIN
global $user;
$gen = new convert;
return $gen->convert_date($user['user_join'], "forum");
SC_END

SC_BEGIN USER_DAYSREGGED
global $user;
$gen = new convert;
return $gen -> computeLapse($user['user_join'])." ".LAN_426;
SC_END

SC_BEGIN USER_REALNAME_ICON
if(defined("USER_REALNAME_ICON"))
{
	return USER_REALNAME_ICON;
}
if(file_exists(THEME."generic/user_realname.png"))
{
	return "<img src='".THEME."generic/user_realname.png' alt='' style='border:0px;vertical-align:middle;' /> ";
}
return "<img src='".e_IMAGE."user_icons/user_realname_".IMODE.".png' alt='' style='border:0px;vertical-align:middle;' /> ";
SC_END

SC_BEGIN USER_REALNAME
global $user;
return $user['user_login'] ? $user['user_login'] : "<i>".LAN_401."</i>";
SC_END

SC_BEGIN USER_EMAIL_ICON
if(defined("USER_EMAIL_ICON"))
{
	return USER_EMAIL_ICON;
}
if(file_exists(THEME."generic/email.png"))
{
	return "<img src='".THEME."generic/email.png' alt='' style='vertical-align:middle;' /> ";
}
return "<img src='".e_IMAGE."generic/".IMODE."/email.png' alt='' style='vertical-align:middle;' /> ";
SC_END

SC_BEGIN USER_EMAIL_LINK
global $user, $tp;
return ($user['user_hideemail'] && !ADMIN) ? "<i>".LAN_143."</i>" : $tp->parseTemplate("{email={$user['user_email']}-link}");
SC_END

SC_BEGIN USER_EMAIL
global $user;
return ($user['user_hideemail'] && !ADMIN) ? "<i>".LAN_143."</i>" : "<a href='mailto:".$user['user_email']."'>".$user['user_email']."</a>";
SC_END

SC_BEGIN USER_ICON
if(defined("USER_ICON"))
{
	return USER_ICON;
}
if(file_exists(THEME."generic/user.png"))
{
	return "<img src='".THEME."generic/user.png' alt='' style='border:0px;vertical-align:middle;' /> ";
}
return "<img src='".e_IMAGE."user_icons/user_".IMODE.".png' alt='' style='border:0px;vertical-align:middle;' /> ";
SC_END

SC_BEGIN USER_ICON_LINK
global $user;
if(defined("USER_ICON"))
{
	$icon = USER_ICON;
}
else if(file_exists(THEME."generic/user.png"))
{
	$icon = "<img src='".THEME."generic/user.png' alt='' style='border:0px;vertical-align:middle;' /> ";
}
else
{
	$icon = "<img src='".e_IMAGE."user_icons/user_".IMODE.".png' alt='' style='border:0px;vertical-align:middle;' /> ";
}
return "<a href='".e_SELF."?id.{$user['user_id']}'>{$icon}</a>";
SC_END

SC_BEGIN USER_NAME_LINK
global $user;
return "<a href='".e_SELF."?id.{$user['user_id']}'>".$user['user_name']."</a>";
SC_END

SC_BEGIN USER_NAME
global $user;
return $user['user_name'];
SC_END

SC_BEGIN USER_ID
global $user;
return $user['user_id'];
SC_END

SC_BEGIN USER_BIRTHDAY_ICON
if(defined("USER_BIRTHDAY_ICON"))
{
	return USER_BIRTHDAY_ICON;
}
if(file_exists(THEME."generic/user_birthday.png"))
{
	return "<img src='".THEME."generic/user_birthday.png' alt='' style='vertical-align:middle;' /> ";
}
return "<img src='".e_IMAGE."user_icons/user_birthday_".IMODE.".png' alt='' style='vertical-align:middle;' /> ";
SC_END

SC_BEGIN USER_BIRTHDAY
global $user;
if ($user['user_birthday'] != "" && $user['user_birthday'] != "0000-00-00" && ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $user['user_birthday'], $regs))
{
	return "$regs[3].$regs[2].$regs[1]";
}
else
{
	return "<i>".LAN_401."</i>";
}
SC_END

SC_BEGIN USER_SIGNATURE
global $tp, $user;
return $user['user_signature'] ? $tp->toHTML($user['user_signature'], TRUE) : "";
SC_END

SC_BEGIN USER_COMMENTS_LINK
global $user;
return $user['user_comments'] ? "<a href='".e_BASE."userposts.php?0.comments.".$user['user_id']."'>".LAN_423."</a>" : "";
SC_END

SC_BEGIN USER_FORUM_LINK
global $user;
return $user['user_forums'] ? "<a href='".e_BASE."userposts.php?0.forums.".$user['user_id']."'>".LAN_424."</a>" : "";
SC_END

SC_BEGIN USER_SENDPM
global $tp, $user;
return $tp->parseTemplate("{SENDPM={$user['user_id']}}");
SC_END

SC_BEGIN USER_RATING
global $pref, $user;
if($pref['profile_rate'] && USER)
{
	include_once(e_HANDLER."rate_class.php");
	$rater = new rater;
	$ret = "<span>";
	if($rating = $rater->getrating('user', $user['user_id']))
	{
		$num = $rating[1];
		for($i=1; $i<= $num; $i++)
		{
			$ret .= "<img src='".e_IMAGE."user_icons/user_star_".IMODE.".png' style='border:0' alt='' />";
		}
	}
	if($rater->checkrated('user', $user['user_id']))
	{
		$ret .= " &nbsp; &nbsp;".$rater->rateselect('', 'user', $user['user_id']);
	}
	$ret .= "</span>";
	return $ret;
}
return "";
SC_END

SC_BEGIN USER_UPDATE_LINK
global $user;
if (USERID == $user['user_id']) {
	return "<a href='".e_BASE."usersettings.php'>".LAN_411."</a>";
}
else if(ADMIN && getperms("4") && !$user['user_admin']) {
	return "<a href='".e_BASE."usersettings.php?".$user['user_id']."'>".LAN_412."</a>";
}
SC_END

SC_BEGIN USER_JUMP_LINK
global $sql, $user;
if(!$userjump = getcachedvars('userjump'))
{
	$sql->db_Select("user", "user_id, user_name", "ORDER BY user_id ASC", "no-where");
	$c = 0;
	while ($row = $sql->db_Fetch())
	{
		$array[$c]['id'] = $row['user_id'];
		$array[$c]['name'] = $row['user_name'];
		if ($row['user_id'] == $user['user_id'])
		{
			$userjump['prev']['id'] = $array[$c-1]['id'];
			$userjump['prev']['name'] = $array[$c-1]['name'];
			$row = $sql->db_Fetch();
			$userjump['next']['id'] = $row['user_id'];
			$userjump['next']['name'] = $row['user_name'];
			break;
		}
		$c++;
	}
	cachevars('userjump', $userjump);
}
if($parm == 'prev')
{
	return $userjump['prev']['id'] ? "&lt;&lt; ".LAN_414." [ <a href='".e_SELF."?id.".$userjump['prev']['id']."'>".$userjump['prev']['name']."</a> ]" : "&nbsp;";
}
else
{
	return $userjump['next']['id'] ? "[ <a href='".e_SELF."?id.".$userjump['next']['id']."'>".$userjump['next']['name']."</a> ] ".LAN_415." &gt;&gt;" : "&nbsp;";
}
SC_END

SC_BEGIN USER_PICTURE
global $user;
if ($user['user_sess'] && file_exists(e_FILE."public/avatars/".$user['user_sess']))
{
	return "<img src='".e_FILE."public/avatars/".$user['user_sess']."' alt='' />";
}
else
{
	return LAN_408;
}
SC_END

SC_BEGIN USER_PICTURE_NAME
global $user;
if (ADMIN && getperms("4"))
{
	return $user['user_sess'];
}
SC_END

SC_BEGIN USER_PICTURE_DELETE
if (USERID == $user['user_id'] || (ADMIN && getperms("4")))
{
	return "
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	<input class='button' type='submit' name='delp' value='".LAN_413."' />
	</form>
	";
}
SC_END

SC_BEGIN USER_EXTENDED_ALL
global $user, $tp, $sql;
global $EXTENDED_CATEGORY_START, $EXTENDED_CATEGORY_END, $EXTENDED_CATEGORY_TABLE;
$qry = "
	SELECT f.*, c.user_extended_struct_name AS category_name, c.user_extended_struct_id AS category_id FROM #user_extended_struct as f
	LEFT JOIN #user_extended_struct as c ON f.user_extended_struct_parent = c.user_extended_struct_id
	ORDER BY c.user_extended_struct_order ASC, f.user_extended_struct_order ASC
";
require_once(e_HANDLER."user_extended_class.php");
$ue = new e107_user_extended;
$ueCatList = $ue->user_extended_get_categories();
$ueFieldList = $ue->user_extended_get_fields();
$ueCatList[0][0] = array('user_extended_struct_name' => LAN_410);

foreach($ueCatList as $catnum => $cat)
{
	$key = $cat[0]['user_extended_struct_name'];
	$cat_name = $tp->parseTemplate("{EXTENDED={$key}.text.{$user_id}}", TRUE);
	if($cat_name != FALSE)
	{
		$ret .= str_replace("{EXTENDED_NAME}", $key, $EXTENDED_CATEGORY_START);
		foreach($ueFieldList[$catnum] as $f)
		{
			$key = $f['user_extended_struct_name'];
			if($ue_name = $tp->parseTemplate("{EXTENDED={$key}.text.{$user['user_id']}}", TRUE))
			{
				$extended_record = str_replace("EXTENDED_ICON","EXTENDED={$key}.icon", $EXTENDED_CATEGORY_TABLE);
				$extended_record = str_replace("{EXTENDED_NAME}", $ue_name, $extended_record);
				$extended_record = str_replace("EXTENDED_VALUE","EXTENDED={$key}.value.{$user['user_id']}", $extended_record);
				$ret .= $tp->parseTemplate($extended_record, TRUE);
			}
		}
	}
	$ret .= $EXTENDED_CATEGORY_END;
}
return $ret;
SC_END


*/
?>