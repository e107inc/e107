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
|     $Source: /cvs_backup/e107_0.7/upload.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-01-27 19:51:38 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
	
if (!$pref['upload_enabled'] || $pref['upload_class'] == 255) {
	header("location: ".e_BASE."index.php");
	exit;
}
	
require_once(HEADERF);
	
/*
if ($pref['upload_class'] == 254 && !USER){
$text = "<div style='text-align:center'>".LAN_402."</div>";
$ns->tablerender(LAN_20, $text);
require_once(FOOTERF);
exit;
}else if(!check_class($pref['upload_class']) && $pref['upload_class']){
*/
if (!check_class($pref['upload_class'])) {
	$text = "<div style='text-align:center'>".LAN_403."</div>";
	$ns->tablerender(LAN_20, $text);
	require_once(FOOTERF);
	exit;
}
	
if (isset($_POST['upload'])) {
	if (($_POST['file_email'] || USER == TRUE) && $_POST['file_name'] && $_POST['file_description']) {
		 
		require_once(e_HANDLER."upload_handler.php");
		$uploaded = file_upload(e_FILE."public/");
		 
		$file = $uploaded[0]['name'];
		$filetype = $uploaded[0]['type'];
		$filesize = $uploaded[0]['size'];
		$image = $uploaded[1]['name'];
		$imagetype = $uploaded[1]['type'];
		 
		if (!$pref['upload_maxfilesize']) {
			$pref['upload_maxfilesize'] = ini_get('upload_max_filesize') * 1048576;
		}
		 
		 
		if ($filesize > $pref['upload_maxfilesize']) {
			$message = LAN_405;
		} else {
			if (is_array($uploaded)) {
				$poster = (USER ? USERID.".".USERNAME : "0".$_POST['file_poster']);
				$_POST['file_email'] = ($_POST['file_email'] ? $_POST['file_email'] : USEREMAIL);
				$_POST['file_description'] = $aj->formtpa($_POST['file_description'], "public");
				$sql->db_Insert("upload", "0, '$poster', '".$_POST['file_email']."', '".$_POST['file_website']."', '".time()."', '".$_POST['file_name']."', '".$_POST['file_version']."', '".$file."', '".$image."', '".$_POST['file_description']."', '".$_POST['file_demo']."', '".$filesize."', 0, '".$_POST['download_category']."'");
				$message .= "<br />".LAN_404;
			}
		}
		 
	} else {
		require_once(e_HANDLER."message_handler.php");
		message_handler("ALERT", 5);
	}
}
	
if (isset($message)) {
	$ns->tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
	require_once(FOOTERF);
	exit;
}
	
$text = "<div style='text-align:center'>
	<form enctype='multipart/form-data' method='post' action='".e_SELF."'>
	<table style='width:97%' class='fborder'>
	<tr>
	<td style='width:20%' class='forumheader3'>".DOWLAN_11.":</td>
	<td style='width:80%' class='forumheader3'>";
	
$sql->db_Select("download_category", "*", "download_category_parent !=0");
$text .= "<select name='download_category' class='tbox'>\n";
while ($row = $sql->db_Fetch()) {
	extract($row);
	if ($download_category_id == $download_category) {
		$text .= "<option value='$download_category_id' selected>".$download_category_name."</option>\n";
	} else {
		$text .= "<option value='$download_category_id'>".$download_category_name."</option>\n";
	}
}
$text .= "</select>
	</td>
	</tr>
	 
	<tr>
	<td style='text-align:center' colspan='2' class='forumheader3'>";
	
$text .= "<b>".LAN_406."</b><br />".LAN_419.":";
	
$text .= " ".str_replace("\n", " | ", $pref['upload_allowedfiletype'])."<br />".LAN_407."<br />
	".LAN_418.($pref['upload_maxfilesize'] ? $pref['upload_maxfilesize'] : ini_get('upload_max_filesize'))."<br />";
	
$text .= "<span style='text-decoration:underline'>".LAN_408."</span> ".LAN_420."</td>
	</tr>";
	
if (!USER) {
	$text .= "<tr>
		<td style='width:30%' class='forumheader3'>".LAN_61."</td>
		<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_poster' type='text' size='50' maxlength='100' /></td>
		</tr>
		 
		<tr>
		<td style='width:30%' class='forumheader3'><span style='text-decoration:underline'>".LAN_112."</span></td>
		<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_email' type='text' size='50' maxlength='100' value='".USEREMAIL."' /></td>
		</tr>";
}
	
$text .= "
	<tr>
	<td style='width:30%' class='forumheader3'><span style='text-decoration:underline'>".LAN_409."</span></td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%'  name='file_name' type='text' size='50' maxlength='100' /></td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_410."</td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_version' type='text' size='10' maxlength='10' /></td>
	</tr>
	 
	 
	<tr>
	<td style='width:30%' class='forumheader3'><span style='text-decoration:underline'>".LAN_411."</span></td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%'  name='file_userfile[]' type='file' size='47' /></td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_412."</td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_userfile[]' type='file' size='47' /></td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'><span style='text-decoration:underline'>".LAN_413."</span></td>
	<td style='width:70%' class='forumheader3'><textarea class='tbox' style='width:90%' name='file_description' cols='59' rows='6'></textarea></td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_144."</td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_website' type='text' size='50' maxlength='100 value=".(defined(USERURL) ? USERURL : "")."' /></td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".LAN_414."<br /><span class='smalltext'>".LAN_415."</span></td>
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_demo' type='text' size='50' maxlength='100' /></td>
	</tr>
	 
	<tr>
	<td style='text-align:center' colspan='2' class='forumheader'><input class='button' type='submit' name='upload' value='".LAN_416."' /></td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender(LAN_417, $text);
	
require_once(FOOTERF);
?>