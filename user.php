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
|     $Source: /cvs_backup/e107_0.7/user.php,v $
|     $Revision: 1.16 $
|     $Date: 2005-03-19 03:03:00 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

if (isset($_POST['delp'])) {
	$tmp = explode(".", e_QUERY);
	if (USERID == $tmp[1] || (ADMIN && getperms("4"))) {
		$sql->db_Select("user", "user_sess", "user_id='". USERID."'");
		@unlink(e_FILE."public/avatars/".$row['user_sess']);
		$sql->db_Update("user", "user_sess='' WHERE user_id=".$tmp[1]);
		header("location:".e_SELF."?id.".$tmp[1]);
		exit;
	}
}

require_once(HEADERF);

if (!USER) {
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_416."</div>");
	require_once(FOOTERF);
	exit;
}

if (isset($_POST['records'])) {
	$records = $_POST['records'];
	$order = $_POST['order'];
	$from = 0;
}
else if(!e_QUERY) {
	$records = 20;
	$from = 0;
	$order = "DESC";
} else {
	$qs = explode(".", e_QUERY);
	if ($qs[0] == "id") {
		$id = $qs[1];
	} else {
		$qs = explode(".", e_QUERY);
		$from = $qs[0];
		$records = $qs[1];
		$order = $qs[2];
	}
}
if ($records > 30) {
	$records = 30;
}

//$pref['profile_comments'] = 1;
//$pref['profile_rate'] = 1;

if (isset($id)) {

	if ($id == 0) {
		$text = "<div style='text-align:center'>".LAN_137." ".SITENAME."</div>";
		$ns->tablerender(LAN_20, $text);
		require_once(FOOTERF);
		exit;
	}


	if (isset($_POST['commentsubmit']) && $pref['profile_comments'])
	{
		require_once(e_HANDLER."comment_class.php");
		$cobj = new comment;
		$cobj->enter_comment($_POST['author_name'], $_POST['comment'], 'profile', $id, $pid, $_POST['subject']);
	}

	$qry = "
	SELECT u.*, ue.* FROM #user AS u
	LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id
	WHERE u.user_id = {$id}
	";
	if (!$sql->db_Select_gen($qry))
	{
		$text = "<div style='text-align:center'>".LAN_400."</div>";
		$ns->tablerender(LAN_20, $text);
		require_once(FOOTERF);
		exit;
	}

	if($pref['profile_comments'])
	{
		include_once(e_HANDLER."comment_class.php");
	}
	if($pref['profile_rate'])
	{
		include_once(e_HANDLER."rate_class.php");
	}
	$user_data = $sql->db_Fetch();
	cachevars('userinfo_{$id}',$user_data);
	$text = renderuser($user_data);
	$ns->tablerender(LAN_402, $text);
	unset($text);
	if($pref['profile_comments'])
	{
		$cobj = new comment;
		if($pref['nested_comments'])
		{
			$query = "comment_item_id='$id' AND comment_type='profile' AND comment_pid='0' ORDER BY comment_datestamp";
		}
		else
		{
			$query = "comment_item_id='$id' AND comment_type='profile' ORDER BY comment_datestamp";
		}
		$comment_total = $sql->db_Select("comments", "*", "".$query);
		if ($comment_total)
		{
			$width = 0;
			while ($row = $sql->db_Fetch())
			{
				if ($pref['nested_comments'])
				{
					$text = $cobj->render_comment($row, "profile", "comment", $id, $width, $subject);
					$ns->tablerender(LAN_5, $text, TRUE);
				}
				else
				{
					$text .= $cobj->render_comment($row, "profile", "comment", $id, $width, $subject);
				}
			}
			if (!$pref['nested_comments'])
			{
				$ns->tablerender(LAN_5, $text, TRUE);
			}
			if(ADMIN == TRUE && $comment_total)
			{
				echo "<a href='".e_BASE.e_ADMIN."modcomment.php?profile.{$id}'>".LAN_314."</a>";
			}
		}
		$cobj->form_comment("comment", "profile", $id, $subject, $content_type, TRUE);
	}
	require_once(FOOTERF);
	exit;
}


$users_total = $sql->db_Count("user");
$text = "<div style='text-align:center'>
".LAN_138." ".$users_total."<br /><br />
<form method='post' action='".e_SELF."'>
<p>
".LAN_419.": ";

if ($records == 10) {
	$text .= "<select name='records' class='tbox'>
	<option value='10' selected='selected'>10</option>
	<option value='20'>20</option>
	<option value='30'>30</option>
	</select>  ";
}
else if($records == 20) {
	$text .= "<select name='records' class='tbox'>
	<option value='10'>10</option>
	<option value='20' selected='selected'>20</option>
	<option value='30'>30</option>
	</select>  ";
} else {
	$text .= "<select name='records' class='tbox'>
	<option value='10'>10</option>
	<option value='20'>20</option>
	<option value='30' selected='selected'>30</option>
	</select>  ";
}
$text .= LAN_139;

if ($order == "ASC") {
	$text .= "<select name='order' class='tbox'>
	<option value='DESC'>".LAN_420."</option>
	<option value='ASC' selected='selected'>".LAN_421."</option>
	</select>";
} else {
	$text .= "<select name='order' class='tbox'>
	<option value='DESC' selected='selected'>".LAN_420."</option>
	<option value='ASC'>".LAN_421."</option>
	</select>";
}

$text .= " <input class='button' type='submit' name='submit' value='".LAN_422."' />
<input type='hidden' name='from' value='$from' />
</p>
</form>\n\n<br /><br />";



if (!$sql->db_Select("user", "*", "ORDER BY user_id $order LIMIT $from,$records", $mode = "no_where")) {
	echo "<div style='text-align:center'><b>".LAN_141."</b></div>";
} else {
	$userList = $sql->db_getList();
	if ($sql->db_Select("core", " e107_value", " e107_name='user_entended'")) {
		$row = $sql->db_Fetch();
		$user_entended = unserialize($row[0]);
	}

	$text .= "
	<table style='width:95%' class='fborder'>
	<tr>
	<td class='fcaption' style='width:2%'>&nbsp;</td>
	<td class='fcaption' style='width:20%'>".LAN_142."</td>
	<td class='fcaption' style='width:20%'>".LAN_112."</td>
	<td class='fcaption' style='width:20%'>".LAN_145."</td>
	</tr>";

	foreach ($userList as $row) {
		$text .= renderuser($row, $user_entended, "short");
	}

	$text .= "</table>\n</div>";

}

$ns->tablerender(LAN_140, $text);

require_once(e_HANDLER."np_class.php");
$ix = new nextprev("user.php", $from, $records, $users_total, LAN_138, $records.".".$order);

function renderuser($row, $user_entended, $mode = "verbose") {
	global $sql, $id, $pref, $tp, $sc_style;
	extract($row);
	$gen = new convert;
	$pm_installed = ($pref['pm_title'] ? TRUE : FALSE);
	if ($mode != "verbose")
	{
		$datestamp = $gen->convert_date($user_join, "forum");
		return "
		<tr>
		<td class='forumheader3' style='width:2%'><a href='".e_SELF."?id.$user_id'><img src='".e_IMAGE."generic/user.png' alt='' style='border:0' /></a></td>
		<td class='forumheader' style='width:20%'>".$user_id.": <a href='".e_SELF."?id.$user_id'>".$user_name."</a></td>
		<td class='forumheader3' style='width:20%'>".($user_hideemail && !ADMIN ? "<i>".LAN_143."</i>" : "<a href='mailto:".$user_email."'>".$user_email."</a>")."</td>
		<td class='forumheader3' style='width:20%'>$datestamp</td>
		</tr>";
	}
	else
	{
		$user_data = $user_id.".".$user_name;
		$chatposts = $sql->db_Count("chatbox");
		$commentposts = $sql->db_Count("comments");
		$forumposts = $sql->db_Count("forum_t");
		$actual_forums = $sql->db_Count("forum_t", "(*)", "WHERE thread_user='$user_data'");
		$actual_chats = $sql->db_Count("chatbox", "(*)", "WHERE cb_nick='$user_data'");
		$actual_comments = $sql->db_Count("comments", "(*)", "WHERE comment_author='$user_data'");

		$chatper = round(($actual_chats/$chatposts) * 100, 2);
		$commentper = round(($actual_comments/$commentposts) * 100, 2);
		$forumper = round(($actual_forums/$forumposts) * 100, 2);
		require_once(e_HANDLER."level_handler.php");

		$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);

		if (strstr($ldata[0], "IMAGE_rank_main_admin_image")) {
			$level = LAN_417;
		}
		else if(strstr($ldata[0], "IMAGE")) {
			$level = LAN_418;
		} else {
			$level = $ldata[1];
		}

		$datestamp = $gen->convert_date($user_join, "long");
		$lastvisit = ($user_currentvisit ? $gen->convert_date($user_currentvisit, "long")."<br />( ".$gen -> computeLapse($user_currentvisit)." ".LAN_426." )" : "<i>".LAN_401."</i>");

		$daysregged = $gen -> computeLapse($user_join)." ".LAN_426;
		
		$str = "
		<div style='text-align:center'>
		<table style='width:95%' class='fborder'>
		<tr><td colspan='2' class='fcaption' style='text-align:center'>".LAN_142." ".$user_id.": ".$user_name."</td></tr>
		<tr><td rowspan='".($pm_installed && $id != USERID ? 10 : 9)."' class='forumheader3' style='width:20%; vertical-align:middle; text-align:center'>";

		if ($user_sess && file_exists(e_FILE."public/avatars/".$user_sess))
		{
			$str .= "<img src='".e_FILE."public/avatars/".$user_sess."' alt='' />";

			if (ADMIN && getperms("4"))
			{
				$str .= "<br /><span class='smalltext'>".$user_sess."</span>";
			}

			if (USERID == $user_id || (ADMIN && getperms("4")))
			{

				$str .= "<br /><br />
				<form method='post' action='".e_SELF."?".e_QUERY."'>
				<input class='button' type='submit' name='delp' value='".LAN_413."' />
				</form>
				";
			}
		}
		else
		{
			$str .= LAN_408;
		}

		$str .= "
		</td></tr>
		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'><img src='".e_IMAGE."generic/rname.png' alt='' style='vertical-align:middle' /> ".LAN_308."</td><td style='width:70%; text-align:right'>".($user_login ? $user_login : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'><img src='".e_IMAGE."generic/email.png' alt='' style='vertical-align:middle' /> ".LAN_112."</td><td style='width:70%; text-align:right'>".$tp->parseTemplate("{email=$user_email-link}")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/icq.png' alt=''  style='vertical-align:middle' /> ".LAN_115."</td><td style='width:70%; text-align:right'>".($user_icq ? $user_icq : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/aim.png' alt=''  style='vertical-align:middle' /> ".LAN_116."</td><td style='width:70%; text-align:right'>".($user_aim ? $tp->toHTML($user_aim) : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/msn.png' alt=''  style='vertical-align:middle' /> ".LAN_117."</td><td style='width:70%; text-align:right'>".($user_msn ? $tp->toHTML($user_msn) : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/hme.png' alt=''  style='vertical-align:middle' /> ".LAN_144."</td><td style='width:70%; text-align:right'>".($user_homepage ? "<a href='".$user_homepage."' rel='external'>$user_homepage</a>" : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>

		<tr>
		<td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/location.png' alt=''  style='vertical-align:middle' /> ".LAN_119."</td><td style='width:70%; text-align:right'>".($user_location ? $tp->toHTML($user_location) : "<i>".LAN_401."</i>")."</td></tr></table>
		</td></tr>";

		if ($user_birthday != "" && $user_birthday != "0000-00-00" && ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $user_birthday, $regs))
		{
			$user_birthday = "$regs[3].$regs[2].$regs[1]";
		}
		else
		{
			$user_birthday = "<i>".LAN_401."</i>";
		}

		$str .= "<tr><td style='width:80%' class='forumheader3'>
		<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/bday.png' alt=''  style='vertical-align:middle' /> ".LAN_118."</td><td style='width:70%; text-align:right'>$user_birthday</td></tr></table>
		</td></tr>";

		if ($pm_installed && $id != USERID)
		{
			$str .= "
			<tr>
			<td style='width:80%' class='forumheader3' colspan='2'>
			<table style='width:100%'><tr><td style='width:30%'> ".$tp->parseTemplate("{SENDPM={$id}}")." ".LAN_425."</td></tr></table>
			</td></tr>";
		}

		$str .= ($user_signature ? "<tr><td colspan='2' class='forumheader3' style='text-align:center'><i>".$tp->toHTML($user_signature, TRUE)."</i></td></tr>" : "");

		//        extended fields ...
		require_once(e_HANDLER."user_extended_class.php");
		$ue = new e107_user_extended;
		$ueList = $ue->user_extended_getStruct();
		if ($ueList)
		{
			$sc_style['EXTENDED_NAME']['pre'] = "<tr><td style='width:40%' class='forumheader3'>";
			$sc_style['EXTENDED_NAME']['post'] = "</td>";
			$sc_style['EXTENDED_VALUE']['pre'] = "<td style='width:60%' class='forumheader3'>";
			$sc_style['EXTENDED_VALUE']['post'] = "</td></tr>";
			$str .= "<tr><td colspan='2' class='forumheader'>".LAN_410."</td></tr>";
			foreach($ueList as $key => $ext)
			{
				$str .= $tp->parseTemplate("{EXTENDED_NAME={$key}.{$user_id}}", TRUE);
				$str .= $tp->parseTemplate("{EXTENDED_VALUE={$key}.{$user_id}}", TRUE);
			}
		}
		//        end extended fields

		$str .= "<tr><td colspan='2' class='forumheader'>".LAN_403."</td></tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_145."</td>
		<td style='width:70%' class='forumheader3'>$datestamp <br />( $daysregged )</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_147."</td>
		<td style='width:70%' class='forumheader3'>$user_chats ( ".$chatper."% )</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_148."</td>
		<td style='width:70%' class='forumheader3'>$user_comments ( ".$commentper."% )</td>
		</tr>";

		if ($user_comments) {
			$str .= "
			<tr>
			<td colspan='2' class='forumheader3'><a href='".e_BASE."userposts.php?0.comments.".$user_id."'>".LAN_423."</a></td>
			</tr>";
		}
		$str .= "

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_149."</td>
		<td style='width:70%' class='forumheader3'>$user_forums ( ".$forumper."% )</td>
		</tr>";

		if ($user_forums) {
			$str .= "
			<tr>
			<td colspan='2' class='forumheader3'><a href='".e_BASE."userposts.php?0.forums.".$user_id."'>".LAN_424."</a></td>
			</tr>";
		}
		$str .= "

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_146."</td>
		<td style='width:70%' class='forumheader3'>$user_visits</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_404."</td>
		<td style='width:70%' class='forumheader3'>$lastvisit</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LAN_406."</td>
		<td style='width:70%' class='forumheader3'>$level</td>
		</tr>";

		if($pref['profile_rate'] && USER)
		{
			$rater = new rater;
			$str .= "
			<tr>
			<td style='width:30%' class='forumheader3'>".USERLAN_1."</td>
			<td style='width:70%' class='forumheader3'>
			";

			$str .= "<span>";
			if($rating = $rater->getrating('user', $user_id))
			{
				$num = $rating[1];
				for($i=1; $i<= $num; $i++)
				{
					$str .= "<img src='".e_IMAGE."generic/star1.gif' style='border:0' />";
				}
				for($i=$num+1; $i<= 10; $i++)
				{
					$str .= "<img src='".e_IMAGE."generic/star2.gif' style='border:0' />";
				}
			}
			if($rater->checkrated('user', $user_id))
			{
				$str .= " &nbsp; &nbsp;".$rater->rateselect('', 'user', $user_id);
			}
			$str .= "</span>";
			$str .= "</td></tr>";
		}
		if (USERID == $user_id) {
			$str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'><a href='".e_BASE."usersettings.php'>".LAN_411."</a></td></tr>";
		}
		else if(ADMIN && getperms("4") && !$user_admin) {
			$str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'><a href='".e_BASE."usersettings.php?".$user_id."'>".LAN_412."</a></td></tr>";
		}

		$sql->db_Select("user", "user_id, user_name", "ORDER BY user_id ASC", "no-where");
		$c = 0;
		while ($row = $sql->db_Fetch()) {
			$array[$c]['id'] = $row['user_id'];
			$array[$c]['name'] = $row['user_name'];
			if ($row['user_id'] == $id) {
				$prevuser['id'] = $array[$c-1]['id'];
				$prevuser['name'] = $array[$c-1]['name'];
				$row = $sql->db_Fetch();
				$nextuser['id'] = $row['user_id'];
				$nextuser['name'] = $row['user_name'];
				break;
			}
			$c++;
		}

		$str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'>
		<table style='width:95%'>
		<tr>
		<td style='width:50%'>".($prevuser['id'] ? "&lt;&lt; ".LAN_414." [ <a href='".e_SELF."?id.".$prevuser['id']."'>".$prevuser['name']."</a> ]" : "&nbsp;")."</td>
		<td style='width:50%; text-align:right'>".($nextuser['id'] ? "[ <a href='".e_SELF."?id.".$nextuser['id']."'>".$nextuser['name']."</a> ] ".LAN_415." &gt;&gt;" : "&nbsp;")."</td>
		</tr>
		</table>
		</td>
		</tr>";

		$str .= "</table></div>";
		return $str;
	}
}

require_once(FOOTERF);
?>