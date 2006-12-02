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
|     $Source: /cvs_backup/e107_0.8/upload.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:10 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

if (!$pref['upload_enabled'] || $pref['upload_class'] == 255) {
	header("location: ".e_BASE."index.php");
	exit;
}

require_once(HEADERF);

if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

if (!check_class($pref['upload_class'])) {
	$text = "<div style='text-align:center'>".LAN_403."</div>";
	$ns->tablerender(LAN_20, $text);
	require_once(FOOTERF);
	exit;
}

if (isset($_POST['upload'])) {
	if (($_POST['file_email'] || USER == TRUE) && $_POST['file_name'] && $_POST['file_description']) {

		require_once(e_HANDLER."upload_handler.php");
		$uploaded = file_upload(e_FILE."public/", "unique");

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
				$_POST['file_description'] = $tp->toDB($_POST['file_description']);
				$file_time = time();
				$sql->db_Insert("upload", "0, '".$tp -> toDB($poster)."', '".$tp -> toDB(check_email($_POST['file_email']))."', '".$tp -> toDB($_POST['file_website'])."', '".$file_time."', '".$tp -> toDB($_POST['file_name'])."', '".$tp -> toDB($_POST['file_version'])."', '".$file."', '".$image."', '".$tp -> toDB($_POST['file_description'])."', '".$tp -> toDB($_POST['file_demo'])."', '".$filesize."', 0, '".$tp -> toDB($_POST['download_category'])."'");
                $edata_fu = array("upload_user" => $poster, "upload_email" => $_POST['file_email'], "upload_name" => $tp -> toDB($_POST['file_name']),"upload_file" => $file, "upload_version" => $_POST['file_version'], "upload_description" => $tp -> toDB($_POST['file_description']), "upload_size" => $filesize, "upload_category" => $tp -> toDB($_POST['download_category']), "upload_website" => $tp -> toDB($_POST['file_website']), "upload_image" => $image, "upload_demo" => $tp -> toDB($_POST['file_demo']), "upload_time" => $file_time);
				$e_event->trigger("fileupload", $edata_fu);
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
	<table style='".USER_WIDTH."' class='fborder'>
	<tr>
	<td style='width:20%' class='forumheader3'>".DOWLAN_11.":</td>
	<td style='width:80%' class='forumheader3'>";

	require_once(e_FILE."shortcode/batch/download_shortcodes.php");
	$dlparm = (isset($download_category)) ? $download_category : "";
	$text .= $tp->parseTemplate("{DOWNLOAD_CATEGORY_SELECT={$dlparm}}",true,$download_shortcodes);


$text .= "
	</td>
	</tr>

	<tr>
	<td style='text-align:center' colspan='2' class='forumheader3'>";

$text .= "<b>".LAN_406."</b><br />".LAN_419.":";

if (is_readable(e_ADMIN.'filetypes.php')) {
	$a_filetypes = trim(file_get_contents(e_ADMIN.'filetypes.php'));
	$a_filetypes = explode(',', $a_filetypes);
	foreach ($a_filetypes as $ftype) {
		$sa_filetypes[] = '.'.trim(str_replace('.', '', $ftype));
	}
	$allowed_filetypes = implode(' | ', $sa_filetypes);
}

$text .= " ".$allowed_filetypes."<br />".LAN_407."<br />
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
	<td style='width:70%' class='forumheader3'><input class='tbox' style='width:90%' name='file_website' type='text' size='50' maxlength='100' value='".(defined(USERURL) ? USERURL : "")."' /></td>
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
