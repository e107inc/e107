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
require_once("auth.php");
$user = new users;
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if(IsSet($_POST['update_options'])){
	$pref['avatar_upload'] = (FILE_UPLOADS ? $_POST['avatar_upload'] : 0);
	$pref['im_width'] = $_POST['im_width'];
	$pref['resize_method'] = $_POST['resize_method'];
	$pref['im_path'] = $_POST['im_path'];
	$pref['photo_upload'] = (FILE_UPLOADS ? $_POST['photo_upload'] : 0);	
	save_prefs();
	$user -> show_message(USRLAN_1);
}

if(IsSet($_POST['prune'])){
	$sql2 = new db;
	$text = USRLAN_56." ";
	if($sql -> db_Select("user", "user_id, user_name", "user_ban=2")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			$text .= $user_name." ";
			$sql2 -> db_Delete("user", "user_id='$user_id' ");
		}
	}
	$ns -> tablerender(USRLAN_57, "<div style='text-align:center'><b>".$text."</b></div>");
	unset($text);
}

if(IsSet($_POST['adduser'])){
	require_once(e_HANDLER."message_handler.php");
	$_POST['name'] = trim(chop(str_replace("&nbsp;", "", $_POST['name'])));
	if($_POST['name'] == "Anonymous"){
		message_handler("P_ALERT", USRLAN_65);
		$error = TRUE;
	}
	if($sql -> db_Select("user", "*", "user_name='".$_POST['name']."' ")){
		message_handler("P_ALERT", USRLAN_66);
		$error = TRUE;
	}	
	if($_POST['password1'] != $_POST['password2']){
		message_handler("P_ALERT", USRLAN_67);
		$error = TRUE;
	}

	if($_POST['name'] == "" || $_POST['password1'] =="" || $_POST['password2'] = ""){
		message_handler("P_ALERT", USRLAN_68);
		$error = TRUE;
	}
    if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email'])){
	   message_handler("P_ALERT", USRLAN_69);
	   $error = TRUE;
	}
	if(!$error){
		if($sql -> db_Select("user", "*", "user_email='".$_POST['email']."' AND user_ban='1' ")){
			exit;
		}
		if($sql -> db_Select("banlist", "*", "banlist_ip='".$_POST['email']."'")){
			exit;
		}

		$username = strip_tags($_POST['name']);
		$ip = getip();

		$sql -> db_Insert("user", "0, '".$username."', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '1', '".time()."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");
		$user -> show_message(USRLAN_70);
	}
}

if($action == "ban"){
	$sql -> db_Select("user", "*", "user_id='$sub_action'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$user -> show_message(USRLAN_7);
	}else{
		$sql -> db_Update("user", "user_ban='1' WHERE user_id=$sub_action");
		$user -> show_message(USRLAN_8);
	}
	$action = "main";
	$sub_action = "user_id";
}

if($action == "unban"){
	$sql -> db_Update("user", "user_ban='0' WHERE user_id='$sub_action' ");
	$user -> show_message(USRLAN_9);
	$action = "main";
	$sub_action = "user_id";
}

if($action == "main" && $sub_action == "confirm"){
	if($sql -> db_Delete("user", "user_id=$id")){
		$user -> show_message(USRLAN_10);
	}
	$sub_action = "user_id";
	$id = "DESC";
}

if($action == "admin"){
	$sql -> db_Select("user", "*", "user_id='$sub_action'");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("user", "user_admin='1' WHERE user_id=$sub_action");
	$user -> show_message($user_name." ".USRLAN_3." <a href='".e_ADMIN."administrator.php?edit.$sub_action'>".USRLAN_4."</a>");
	$action = "main";
	$sub_action = "user_id";
	$id = "DESC";
}

if($action == "unadmin"){
	$sql -> db_Select("user", "*", "user_id='$sub_action'");
	$row = $sql -> db_Fetch(); extract($row);
	if($user_perms == "0"){
		$user -> show_message(USRLAN_5);
	}else{
		$sql -> db_Update("user", "user_admin='0' WHERE user_id=$sub_action");
		$user -> show_message($user_name." ".USRLAN_6);
		$action = "main";
		$sub_action = "user_id";
		$id = "DESC";
	}
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

if($action == "delext"){
	$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
	$row = $sql -> db_Fetch();
	$user_entended = unserialize($row[0]);
	unset($user_entended[$sub_action]);
	$tmp = addslashes(serialize($user_entended));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
	$user -> show_message(USRLAN_83);
	$action = "extended";
}

if($action == "verify"){
	if($sql -> db_Update("user", "user_ban='0' WHERE user_id=$sub_action")){
		$user -> show_message(USRLAN_86);
		$action = "main";
		$sub_action = "user_id";
		$id = "DESC";
	}
}

if($action == "main" && is_numeric($sub_action)){
	$user -> show_message(USRLAN_87);
}

if($action == "cu" && is_numeric($sub_action)){
	$user -> show_message(USRLAN_88);
	$action = "main";
}

if(!e_QUERY || $action == "main"){
	$user -> show_existing_users($sub_action, $id);
}

if($action == "options"){
	$user -> show_prefs();
}

if($action == "extended"){
	$user -> show_extended();
}

if($action == "prune"){
	$user -> show_prune();
}

if($action == "create"){
	$user -> add_user();
}

$user -> show_options($action);
require_once("footer.php");

echo "<script type=\"text/javascript\">
function confirm_(mode, user_id, user_name){
	if(mode == 'cat'){
		var x=confirm(\"".NWSLAN_37." [ID: \" + user_id + \"]\");
	}else if(mode == 'sn'){
		var x=confirm(\"".NWSLAN_38." [ID: \" + user_id + \"]\");
	}else{
		var x=confirm(\"".USRLAN_82." [".USRLAN_61.": \" + user_name + \"]\");
	}
if(x)
	if(mode == 'cat'){
		window.location='".e_SELF."?cat.confirm.' + user_id;
	}else if(mode == 'sn'){
		window.location='".e_SELF."?sn.confirm.' + user_id;
	}else{
		window.location='".e_SELF."?main.confirm.' + user_id;
	}
}
</script>";

class users{

	function show_existing_users($sub_action, $id){
		// ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------

		global $sql, $rs, $ns, $aj;
		$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";
		$query = "ORDER BY ".($sub_action ? $sub_action : "user_id")." ".($id ? $id : "DESC");
		if($sql -> db_Select("user", "*", $query, "nowhere")){
			$text .= "<table class='fborder' style='width:100%'>
			<tr>
			<td style='width:8%' class='forumheader2'><a href='".e_SELF."?main.user_id.".($id == "desc" ? "asc" : "desc")."'>ID</a></td>
			<td style='width:10%' class='forumheader2'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'>".USRLAN_79."</a></td>
			<td style='width:20%' class='forumheader2'><a href='".e_SELF."?main.user_name.".($id == "desc" ? "asc" : "desc")."'>".USRLAN_78."</a></td>
			<td style='width:62%' class='forumheader2'>".USRLAN_75."</td>
			</tr>";
			while($row = $sql -> db_Fetch()){
				extract($row);
				$text .= "<tr>
				<td style='width:8%' class='forumheader3'>$user_id</td>
				<td style='width:10%' class='forumheader3'>";
				
				if($user_perms == "0"){
					$text .= "<img src='".e_IMAGE."generic/mainadmin.gif' alt='' style='vertical-align:middle' />";
				}else if($user_admin){
					$text .= "<a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'><img src='".e_IMAGE."generic/admin.gif' alt='' style='vertical-align:middle; border:0' /></a>";
				}else if($user_ban == 1){
					$text .= "<a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'><img src='".e_IMAGE."generic/banned.gif' alt='' style='vertical-align:middle; border:0' /></a>";
				}else if($user_ban == 2){
					$text .= "<img src='".e_IMAGE."generic/not_verified.gif' alt='' style='vertical-align:middle' />";
				}else{
					$text .= "&nbsp;";
				}
				
				$text .= "</td>
				<td style='width:20%' class='forumheader3'>$user_name</td>
				<td style='width:62%; text-align:center' class='forumheader3'>";

				if($user_perms != "0"){

					$text .= $rs -> form_button("submit", "main_1", USRLAN_80, "onClick=\"document.location='".e_ADMIN."userinfo.php?$user_ip'\"").
					$rs -> form_button("submit", "main_2", USRLAN_81, "onClick=\"document.location='".e_BASE."usersettings.php?$user_id'\"").
					$rs -> form_button("submit", "main_3", USRLAN_29, "onClick=\"confirm_('main', '$user_id', '$user_name');\"");
					

					if($user_ban == 1){
						$text .= $rs -> form_button("submit", "main_4", USRLAN_33, "onClick=\"document.location='".e_SELF."?unban.$user_id'\"");
					}else if($user_ban == 2){
						$text .= $rs -> form_button("submit", "main_4", USRLAN_30, "onClick=\"document.location='".e_SELF."?ban.$user_id'\"").
						$rs -> form_button("submit", "main_4", USRLAN_32, "onClick=\"document.location='".e_SELF."?verify.$user_id'\"");
					}else{
						$text .= $rs -> form_button("submit", "main_4", USRLAN_30, "onClick=\"document.location='".e_SELF."?ban.$user_id'\"");
					}
					
					if(!$user_admin && !$user_ban && $user_ban != 2){
						$text .= $rs -> form_button("submit", "main_5", USRLAN_35, "onClick=\"document.location='".e_SELF."?admin.$user_id'\"");
					}else if ($user_admin){
						$text .= $rs -> form_button("submit", "main_5", USRLAN_34, "onClick=\"document.location='".e_SELF."?unadmin.$user_id'\"");
					}
				}
				if(ADMINPERMS == "0"){
					$text .= $rs -> form_button("submit", "main_6", USRLAN_36, "onClick=\"document.location='".e_ADMIN."userclass.php?$user_id'\"");
				}else{
					$text .= "&nbsp;";
				}
			}
			$text .= "</td>\n</tr>";
			$text .= "</table>";
		}else{
			$text .= "<div style='text-align:center'>You shouldn't be seeing this - database is reporting no users yet you're still logged on as admin??? Weird, I'd contact Mulder and Skully if I were you ...<br />( Defective query was <b>$query</b> )</div>";
		}
		$text .= "</div>";
		$ns -> tablerender(USRLAN_77, $text);
	}

	function show_options($action){
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns;
		$text = "<div style='text-align:center'>";
		if(e_QUERY && $action != "main"){
			$text .= "<a href='".e_SELF."'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".USRLAN_71."</div></div></a>";
		}
		if($action != "create"){
			$text .= "<a href='".e_SELF."?create'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".USRLAN_72."</div></div></a>";
		}
		if($action != "prune"){
			$text .= "<a href='".e_SELF."?prune'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".USRLAN_73."</div></div></a>";
		}
		if($action != "extended"){
			$text .= "<a href='".e_SELF."?extended'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".USRLAN_74."</div></div></a>";
		}
		if($action != "options"){
			$text .= "<a href='".e_SELF."?options'><div class='border'><div class='forumheader'><img src='".e_IMAGE."generic/location.png' style='vertical-align:middle; border:0' alt='' /> ".USRLAN_75."</div></div></a>";
		}
		$text .= "</div>";
		$ns -> tablerender(USRLAN_76, $text);
	}

	function show_prefs(){
		global $ns, $pref;
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:85%' class='fborder'>

		<tr>
		<td style='width:50%' class='forumheader3'>".USRLAN_44.":</td>
		<td style='width:50%' class='forumheader3'>".
		($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked>".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0'>".USRLAN_46 : "<input name='avatar_upload' type='radio' value='1'>".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked>".USRLAN_46).
		(!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
		</td>
		</tr>

		<tr>
		<td style='width:50%' class='forumheader3'>".USRLAN_53.":</td>
		<td style='width:50%' class='forumheader3'>".
		($pref['photo_upload'] ? "<input name='photo_upload' type='radio' value='1' checked>".USRLAN_45."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0'>".USRLAN_46 : "<input name='photo_upload' type='radio' value='1'>".USRLAN_45."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' checked>".USRLAN_46).
		(!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
		</td>
		</tr>

		<tr>
		<td style='width:50%' class='forumheader3'>".USRLAN_47.":</td>
		<td style='width:50%' class='forumheader3'>
		<input class='tbox' type='text' name='im_width' size='10' value='".$pref['im_width']."' maxlength='5' /> (".USRLAN_48.")
		</tr>

		<tr> 
		<td colspan='2' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='update_options' value='".USRLAN_51."' />
		</td>
		</tr>

		</table></form></div>";
		$ns -> tablerender(USRLAN_52, $text);
	}

	function show_message($message){
		global $ns;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_extended(){
		global $sql, $ns;

		$sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
		$row = $sql -> db_Fetch();
		$user_entended = unserialize($row[0]);

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
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
					<td colspan='2' class='forumheader3' style='text-align:center'>".$u_entended."&nbsp;&nbsp;&nbsp;[ <a href='".e_SELF."?delext.$key'>".USRLAN_29."</a> ]
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
	}

	function show_prune(){
		global $ns, $sql;

		$unactive = $sql -> db_Select("user", "*", "user_ban=2");
		$text = "<div style='text-align:center'>".USRLAN_84." ".$unactive." ".USRLAN_85."<br /><br />
		<form method='post' action='".e_SELF."'>
		<table style='width:85%' class='fborder'>
		<tr>
		<td class='forumheader3' style='text-align:center'>
		<input class='button' type='submit' name='prune' value='".USRLAN_54."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(USRLAN_55, $text);
	}

	function add_user(){
		global $rs, $ns;
		$text = "<div style='text-align:center'>".
		$rs -> form_open("post", e_SELF, "adduserform")."
		<table style='width:85%' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'>".USRLAN_61."</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_text("name", 40, "", 30)."
		</td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".USRLAN_62."</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_password("password1", 40, "", 20)."
		</td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".USRLAN_63."</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_password("password2", 40, "", 20)."
		</td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".USRLAN_64."</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_text("email", 60, "", 100)."
		</td>
		</tr>
		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='adduser' value='".USRLAN_60."' />
		</td>
		</tr>
		</table>
		</form>
		</div>
		";

		$ns -> tablerender(USRLAN_59, $text);
	}

}