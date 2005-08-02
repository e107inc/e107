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
|     $Revision: 1.22 $
|     $Date: 2005-08-02 12:25:38 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_FILE."shortcode/batch/user_shortcodes.php");

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
	$user_data = $sql->db_Fetch();
	cachevars('userinfo_{$id}',$user_data);
	$text = renderuser($user_data);
	$ns->tablerender(LAN_402, $text);
	unset($text);
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
	$text .= "
	<table style='width:95%' class='fborder'>
	<tr>
	<td class='fcaption' style='width:2%'>&nbsp;</td>
	<td class='fcaption' style='width:20%'>".LAN_142."</td>
	<td class='fcaption' style='width:20%'>".LAN_112."</td>
	<td class='fcaption' style='width:20%'>".LAN_145."</td>
	</tr>";

	foreach ($userList as $row) {
		$text .= renderuser($row, "short");
	}

	$text .= "</table>\n</div>";

}

$ns->tablerender(LAN_140, $text);

require_once(e_HANDLER."np_class.php");
$ix = new nextprev("user.php", $from, $records, $users_total, LAN_138, $records.".".$order);

function renderuser($user_array, $mode = "verbose") {
	global $sql, $pref, $tp, $sc_style, $user_shortcodes;
	global $EXTENDED_START, $EXTENDED_TABLE, $EXTENDED_END, $USER_SHORT_TEMPLATE, $USER_FULL_TEMPLATE;
	global $user;
	$user = $user_array;

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