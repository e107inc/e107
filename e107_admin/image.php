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
|     $Source: /cvs_backup/e107_0.7/e107_admin/image.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-01-27 19:52:24 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("5")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'image';
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
$rs = new form;
	
if (strstr(e_QUERY, "delp")) {
	if (!e_REFERER_SELF) {
		exit;
	}
	$tmp = explode("-", e_QUERY);
	$image = $tmp[1];
	@unlink(e_FILE."public/avatars/".$image);
	$sql->db_Update("user", "user_image='' WHERE user_image='-upload-$image'");
	$sql->db_Update("user", "user_sess='' WHERE user_sess='$image'");
	$message = $image." ".IMALAN_28;
}
	
if (e_QUERY == "del") {
	if (!e_REFERER_SELF) {
		exit;
	}
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && $file != "index.html" && $file != "/") {
			$dirlist[] = $file;
		}
	}
	closedir($handle);
	$count = 0;
	while (list($key, $image_name) = each($dirlist)) {
		if (!$sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'")) {
			unlink(e_FILE."public/avatars/".$image_name);
			$count ++;
		}
	}
	$message = $count." ".IMALAN_26;
}
	
	
if (isset($_POST['update_options'])) {
	$pref['image_post'] = $_POST['image_post'];
	$pref['resize_method'] = $_POST['resize_method'];
	$pref['im_path'] = $_POST['im_path'];
	$pref['image_post_class'] = $_POST['image_post_class'];
	$pref['image_post_disabled_method'] = $_POST['image_post_disabled_method'];
	save_prefs();
	$message = IMALAN_9;
}
	
if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	
	
if (isset($_POST['show_avatars'])) {
	 
	$handle = opendir(e_FILE."public/avatars/");
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && $file != "index.html" && $file != "/") {
			$dirlist[] = $file;
		}
	}
	closedir($handle);
	 
	$text = "<div style='text-align:center'>\n";
	 
	if (!is_array($dirlist)) {
		$text .= IMALAN_29;
	} else {
		 
		 
		 
		while (list($key, $image_name) = each($dirlist)) {
			$users = IMALAN_21." | ";
			if ($sql->db_Select("user", "*", "user_image='-upload-$image_name' OR user_sess='$image_name'")) {
				while ($row = $sql->db_Fetch()) {
					extract($row);
					$users .= "<a href='".e_BASE."user.php?id.$user_id'>$user_name</a> <span class='smalltext'>(".($user_sess == $image_name ? IMALAN_24 : IMALAN_23).")</span> | ";
				}
			} else {
				$users = IMALAN_22;
			}
			 
			$text .= "<div class='spacer'>
				<table style='".ADMIN_WIDTH."' class='fborder'>
				<tr>
				<td class='fcaption'>$image_name</td>
				</tr>
				<tr>
				<td class='forumheader3'><img src='".e_FILE."public/avatars/".$image_name."' alt='' /><br />[ <a href='".e_SELF."?delp-$image_name'>".IMALAN_27." ]</a></td>
				</tr>
				<tr>
				<td class='forumheader3'>$users</td>
				</tr>
				</table>
				</div>";
		}
		 
		$text .= "<div class='spacer'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='forumheader'><a href='".e_SELF."?del'>".IMALAN_25."</a></td>
			</tr>
			</table>";
		 
	}
	 
	$text .= "</div>";
	 
	$ns->tablerender(IMALAN_18, $text);
}
	
	
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	 
	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_1."<br />
	<span class='smalltext'>".IMALAN_2."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >". ($pref['image_post'] ? "<input type='checkbox' name='image_post' value='1' checked='checked' />" : "<input type='checkbox' name='image_post' value='1' />")."
	</td>
	</tr>
	 
	 
	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_10."<br />
	<span class='smalltext'>".IMALAN_11."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	 
	 
	<select class='tbox' name='image_post_class'>
	<option value='".e_UC_PUBLIC."'".($pref['image_post_class'] == e_UC_PUBLIC ? " selected='selected'" : "").">".IMALAN_30."</option>
	<option value='".e_UC_GUEST."'".($pref['image_post_class'] == e_UC_GUEST ? " selected='selected'" : "").">".IMALAN_31."</option>
	<option value='".e_UC_MEMBER."'".($pref['image_post_class'] == e_UC_MEMBER ? " selected='selected'" : "").">".IMALAN_32."</option>
	<option value='".e_UC_ADMIN."'".($pref['image_post_class'] == e_UC_ADMIN ? " selected='selected'" : "").">".IMALAN_33."</option>\n";
	
	
if ($sql->db_Select("userclass_classes")) {
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$text .= "<option value='".$userclass_id."'".($pref['image_post_class'] == $userclass_id ? " selected='selected'" : "").">$userclass_name</option>\n";
	}
}
$text .= "</select>
	 
	</td>
	</tr>
	 
	<tr>
	<td style='width:75%' class='forumheader3'>
	".IMALAN_12."<br />
	<span class='smalltext'>".IMALAN_13."</span>
	</td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<select name='image_post_disabled_method' class='tbox'>". ($pref['image_post_disabled_method'] == "0" ? "<option value='1' selected='selected'>".IMALAN_14."</option>" : "<option value='0'>".IMALAN_14."</option>"). ($pref['image_post_disabled_method'] == "1" ? "<option value='1' selected='selected'>".IMALAN_15."</option>" : "<option value='1'>".IMALAN_15."</option>")."
	</select></td>
	</tr>
	 
	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_3."<br /><span class='smalltext'>".IMALAN_4."</span></td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<select name='resize_method' class='tbox'>". ($pref['resize_method'] == "gd1" ? "<option selected='selected'>gd1</option>" : "<option>gd1</option>"). ($pref['resize_method'] == "gd2" ? "<option selected='selected'>gd2</option>" : "<option>gd2</option>"). ($pref['resize_method'] == "ImageMagick" ? "<option selected='selected'>ImageMagick</option>" : "<option>ImageMagick</option>")."
	</select>
	</td>
	</tr>
	 
	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_5."<br /><span class='smalltext'>".IMALAN_6."</span></td>
	<td style='width:25%;text-align:center' class='forumheader3' >
	<input class='tbox' type='text' name='im_path' size='40' value='".$pref['im_path']."' maxlength='200' />
	</td></tr>
	 
	<tr>
	<td style='width:75%' class='forumheader3'>".IMALAN_16."</td>
	<td style='width:25%;text-align:center' class='forumheader3'  >
	<input class='button' type='submit' name='show_avatars' value='".IMALAN_17."' />
	</td></tr>
	 
	<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='update_options' value='".IMALAN_8."' />
	</td>
	</tr>
	 
	</table></form></div>";
$ns->tablerender(IMALAN_7, $text);
	
	
require_once("footer.php");
	
	
	
$pref['resize_method'] = $_POST['resize_method'];
$pref['im_path'] = $_POST['im_path'];
	
	
?>