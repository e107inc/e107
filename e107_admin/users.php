<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/users.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit;}

if(strstr(e_QUERY, "del")){
	$tmp = explode(".", e_QUERY);
	$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
	$row = $sql -> db_Fetch();
	$user_entended = unserialize($row[0]);
	unset($user_entended[$tmp[1]]);
	$tmp = addslashes(serialize($user_entended));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
	header("location:".e_SELF);
	exit;
}

require_once("auth.php");

if(IsSet($_POST['update_options'])){
	$pref['avatar_upload'] = $_POST['avatar_upload'];
	$pref['im_width'] = $_POST['im_width'];
	$pref['resize_method'] = $_POST['resize_method'];
	$pref['im_path'] = $_POST['im_path'];
	save_prefs();
	$message = USRLAN_1;
}

if(IsSet($_POST['add_field'])){
	extract($_POST);
	$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
	$row = $sql -> db_Fetch();
	$user_entended = unserialize($row[0]);
	$user_entended[] = $user_field;
	$tmp = addslashes(serialize($user_entended));
	if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
		$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
	}else{
		$sql -> db_Insert("core", "'user_entended', '$tmp' ");
	}
	$message = USRLAN_2;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(e_QUERY != ""){
	$qs = explode(".", e_QUERY);
	$from = $qs[0];
	$order = $qs[1];
	$ordert = $qs[2];
	$view = $qs[3];
	$action = $qs[4];
	$id = $qs[5];
}else{
	if(!$_POST['order'] ? $order="user_id" : $order = $_POST['order']);
	if(!$_POST['ordert'] ? $ordert="DESC" : $ordert = $_POST['ordert']);
	if(!$_POST['view'] ? $view="20" : $view = $_POST['view']);
	if(!$from){ $from = 0; }
}

if($action == "uta"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("user", "user_admin='1' WHERE user_id='$id' ");
	$message = $user_name." ".USRLAN_3." <a href=\"administrator.php\">".USRLAN_4."</a>.";
}

if($action == "utr"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$message = USRLAN_5;
	}else{
		$sql -> db_Update("user", "user_admin='0' WHERE user_id='$id' ");
		$message = $user_name." ".USRLAN_6;
	}
}


if($action == "ban"){
	$sql -> db_Select("user", "*", "user_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$message = USRLAN_7;
	}else{
		$sql -> db_Update("user", "user_ban='1' WHERE user_id='$id' ");
		$message = USRLAN_8;
	}
}

if($action == "unban"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$id' ");
	$message = USRLAN_9;
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Select("user", "*", "user_id='".$_POST['id']."' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$sql -> db_Delete("user", "user_id='".$_POST['id']."' ");
	$message = USRLAN_10;
}

If(IsSet($_POST['cancel'])){
	$message = USRLAN_11;
}

if($action == "remuser"){
	$sql -> db_Select("user", "*", "user_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$text = "<div style=\"text-align:center\">";
	if($user_admin == 1 && $user_perms == "0"){
		$message = USRLAN_12."</b></div>";
	}else{
		$text .= "<b>".USRLAN_13." ($user_name) - ".USRLAN_14."</b>
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"".USRLAN_15."\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"".USRLAN_16."\" />
<input type=\"hidden\" name=\"id\" value=\"".$id."\">
</form>
</div>";
	$ns -> tablerender(USRLAN_17, $text);
	require_once("footer.php");
	exit;
	}
}

if($action == "act"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$id' ");
	$message = USRLAN_18;
}

$searchquery = $_POST['searchquery'];

$text .= "<div style='text-align:center'>
<table style='width:95%' class=\"fborder\">
<tr>
<td style=\"text-align:center\" colspan=\"2\">";
if($message){ $text .= $message."<br /><br />"; }
$text .= "<form method=\"post\" action=\"".e_SELF."\">
".USRLAN_19." <input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"$searchquery\" maxlength=\"50\" />
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"".USRLAN_19."\" />
&nbsp;&nbsp;
".USRLAN_20." <select name=\"order\" class=\"tbox\">";
$text .= ($order == "user_id" ? "<option value=\"user_id\" selected>".USRLAN_21."</option>" : "<option value=\"user_id\">".USRLAN_21."</option>");
$text .= ($order == "user_name" ? "<option value=\"user_name\" selected>".USRLAN_22."</option>" : "<option value=\"user_name\">".USRLAN_22."</option>");
$text .= ($order == "user_visits" ? "<option value=\"user_visits\" selected>".USRLAN_23."</option>" : "<option value=\"user_visits\">".USRLAN_23."</option>");
$text .= ($order == "user_admin" ? "<option value=\"user_admin\" selected>".USRLAN_24."</option>" : "<option value=\"user_admin\">".USRLAN_24."</option>");
$text .= ($order == "user_ban" ? "<option value=\"user_ban\" selected>".USRLAN_25."</option>" : "<option value=\"user_ban\">".USRLAN_25."</option>");
$text .= "</select>
<select name=\"ordert\" class=\"tbox\">";
$text .= ($ordert == "ASC" ? "<option value=\"DESC\" selected>".USRLAN_26."</option>" : "<option value=\"DESC\">".USRLAN_26."</option>");
$text .= ($ordert == "ASC" ? "<option value=\"ASC\" selected>".USRLAN_27."</option>" : "<option value=\"ASC\">".USRLAN_27."</option>");
$text .= "</select>
View
<select name=\"view\" class=\"tbox\">";
$text .= ($view == "10" ? "<option value=\"10\" selected>10</option>" : "<option value=\"10\">10</option>");
$text .= ($view == "25" ? "<option value=\"25\" selected>25</option>" : "<option value=\"25\">25</option>");
$text .= ($view == "50" ? "<option value=\"50\" selected>50</option>" : "<option value=\"50\">50</option>");
$text .= ($view == "75" ? "<option value=\"75\" selected>75</option>" : "<option value=\"75\">75</option>");
$text .= ($view == "100" ? "<option value=\"100\" selected>100</option>" : "<option value=\"100\">100</option>");
$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"sortsubmit\" value=\"".USRLAN_28."\" />
</form>
</td>";

$total = $sql -> db_Count("user");
if(IsSet($_POST['searchsubmit'])){
	$results = $sql -> db_Select("user", "*", "user_name REGEXP('".$searchquery."')");
}else{
	$sql -> db_Select("user", "*", "ORDER BY $order $ordert LIMIT $from, $view", "nowhere");
}
while($row = $sql -> db_Fetch()){
	extract($row);
	$text .= "<tr class=\"border\"><td class=\"fcaption\" style=\"width:40%\">";
	if($user_admin){ $text .= "[Admin] "; }
	$text .= $user_id.".".$user_name."&nbsp;&nbsp;
	</td>
	<td class=\"forumtable2\" style=\"width:60%; text-align:center\">
	[<a href=\"userinfo.php?".$user_ip."\">Info</a>] [<a href=\"../usersettings.php?$user_id\">Edit</a>] [<a href=\"users.php?$from.$order.$ordert.$view.remuser.$user_id\">".USRLAN_29."</a>]";
	if($user_ban == 0){
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.ban.$user_id\">".USRLAN_30."</a>]";
	}else if($user_ban == 2){
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.ban.$user_id\">".USRLAN_31."</a>]";
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.act.$user_id\">".USRLAN_32."</a>]"; 
	}else{
		$text .= " [<a href=\"users.php?$from.$order.$ordert.$view.unban.$user_id\">".USRLAN_33."</a>]";
	}
	if($user_admin == 1 ? $text .= " [<a href=\"users.php?$from.$order.$ordert.$view.utr.$user_id\">".USRLAN_34."</a>]" : $text .= " [<a href=\"users.php?$from.$order.$ordert.$view.uta.".$user_id.".".e_QUERY."\">".USRLAN_35."</a>]");
	$text .= "[<a href=\"userclass.php?".$user_id."\">".USRLAN_36."</a>]</td>
	</tr>";
}
$text .= "</table>
</div>";
$ns -> tablerender(USRLAN_37, $text);

if(IsSet($_POST['searchsubmit'])){
	echo "<div style='text-align:center'>".USRLAN_38." ".$results." ".USRLAN_39.".</div>";
}else{
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("users.php", $from, $view, $total, "Users", $order.".".$ordert.".".$view);
}

echo "<br />";

$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
$row = $sql -> db_Fetch();
$user_entended = unserialize($row[0]);

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>\n";

if(!$row[0]){
	$text .= "<tr>
	<td colspan='2' class='forumheader3' style='text-align:center'>".USRLAN_40."</td>
	</tr>";
}else{
	$c=0;

	while(list($key, $u_entended) = each($user_entended)){
		if($u_entended){
			$text .= "<tr>
			<td colspan='2' class='forumheader3' style='text-align:center'>".$u_entended."&nbsp;&nbsp;&nbsp;[ <a href='".e_SELF."?del.$key'>".USRLAN_29."</a> ]
			</td>
			</tr>";
			$c++;
		}
	}
}


$text .= "<tr>
<td style='width:30%' class='forumheader3'>".USRLAN_41.":</td>
<td style='width:70%' class='forumheader3'><input class='tbox' type='text' name='user_field' size='40' value='' maxlength='50' /></td>
</tr>

<tr> 
<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='add_field' value='".USRLAN_42."' />
</td>
</tr>

</table></form></div>";
$ns -> tablerender(USRLAN_43, $text);


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>

<tr>
<td style='width:50%' class='forumheader3'>".USRLAN_44.":</td>
<td style='width:50%' class='forumheader3'>".
($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked>".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0'>".USRLAN_46 : "<input name='avatar_upload' type='radio' value='1'>".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked>".USRLAN_46)."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".USRLAN_47.":</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='im_width' size='10' value='".$pref['im_width']."' maxlength='5' /> (".USRLAN_48.")

<tr>
<td style='width:50%' class='forumheader3'>".USRLAN_49.":</td>
<td style='width:50%' class='forumheader3'>
<select name='resize_method' class='tbox'>".
($pref['resize_method'] == "gd1" ? "<option selected>gd1</option>" : "<option>gd1</option>").
($pref['resize_method'] == "gd2" ? "<option selected>gd2</option>" : "<option>gd2</option>").
($pref['resize_method'] == "ImageMagick" ? "<option selected>ImageMagick</option>" : "<option>ImageMagick</option>")."
</select>
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".USRLAN_50.":</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='im_path' size='40' value='".$pref['im_path']."' maxlength='200' />

<tr> 
<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='update_options' value='".USRLAN_51."' />
</td>
</tr>

</table></form></div>";
$ns -> tablerender(USRLAN_52, $text);

require_once("footer.php");
?>