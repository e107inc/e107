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
|     $Revision: 1.17 $
|     $Date: 2005-03-21 04:25:37 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once("user_shortcodes.php");

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

if (file_exists(THEME."user_template.php"))
{
	require_once(THEME."user_template.php");
}
else
{
	require_once(e_BASE.$THEMES_DIRECTORY."templates/user_template.php");
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
//require_once(e_BASE."user_shortcodes.php");
//echo "<pre>".print_r($user_shortcodes, TRUE)."</pre>";

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
//	if($pref['profile_rate'])
//	{
//		include_once(e_HANDLER."rate_class.php");
//	}
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

function renderuser($user_array, $user_entended, $mode = "verbose") {
	//	echo "<pre>".print_r($user, TRUE)."</pre>";
	global $sql, $pref, $tp, $sc_style, $user_shortcodes;
	global $EXTENDED_START, $EXTENDED_TABLE, $EXTENDED_END, $USER_SHORT_TEMPLATE, $USER_FULL_TEMPLATE;
	global $user;
	$user = $user_array;

	//        extended fields ...

/*
	require_once(e_HANDLER."user_extended_class.php");
	$ue = new e107_user_extended;
	$ueList = $ue->user_extended_getStruct();

	if ($ueList)
	{
		$USER_EXTENDED = $EXTENDED_START;
		foreach($ueList as $key => $ext)
		{
			if($ue_name = $tp->parseTemplate("{EXTENDED={$key}.name.{$user_id}}", TRUE))
			{
				$extended_record = str_replace("EXTENDED_ICON","EXTENDED={$key}.icon", $EXTENDED_TABLE);
				$extended_record = str_replace("{EXTENDED_NAME}", $ue_name, $extended_record);
				$extended_record = str_replace("EXTENDED_VALUE","EXTENDED={$key}.value.{$user_id}", $extended_record);
				$USER_EXTENDED .= $tp->parseTemplate($extended_record, TRUE);

			}
		}
		$USER_EXTENDED .= $EXTENDED_END;
	}
*/
	//        end extended fields

	if($mode == 'verbose')
	{
		return $tp->parseTemplate($USER_FULL_TEMPLATE, FALSE, $user_shortcodes);
	}
	else
	{
		return $tp->parseTemplate($USER_SHORT_TEMPLATE, FALSE, $user_shortcodes);
	}
}

require_once(FOOTERF);


?>