<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/upload.php
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

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$id = $tmp[1];
}


if($action == "dis"){
	$sql -> db_Update("upload", "upload_active='1' WHERE upload_id='$id' ");
	$message = "Upload marked as not wanted.";
}

if($action == "dlm"){
	header("location: download.php?dlm.".$id);
	exit;
}

if($action == "news"){
	header("location: newspost.php?news.".$id);
	exit;
}



require_once("auth.php");
$gen = new convert;
require_once(e_BASE."classes/form_handler.php");
$rs = new form;

if(IsSet($_POST['optionsubmit'])){

	if($_POST['upload_class'][0] == 999){
		$u_class = 999;
	}else if(!$_POST['upload_class'][0]){
		$u_class = 0;
	}else{
		$count = 0;
		while($_POST['upload_class'][$count]){
			$u_class .= $_POST['upload_class'][$count]."|";
			$count++;
		}
		if(substr($u_class, -1) == "|"){
			$u_class = substr($u_class, 0, -1);
		}
	}
	$pref['upload_storagetype'][1] = $_POST['upload_storagetype'];
	$pref['upload_maxfilesize'][1] = $_POST['upload_maxfilesize'];
	$pref['upload_allowedfiletype'][1] = $_POST['upload_allowedfiletype'];
	$pref['upload_class'][1] = $u_class;
	$pref['upload_enabled'][1] = $_POST['upload_enabled'];

	save_prefs();
	$message = "Settings saved in database";
}

if(IsSet($message)){
	require_once(e_BASE."classes/message_handler.php");
	message_handler("ADMIN_MESSAGE", $message);
}

// view -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "view"){
	$sql -> db_Select("upload", "*", "upload_id='$id'");
	$row = $sql -> db_Fetch(); extract($row);

	$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
	$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
	$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
	$upload_datestamp = $gen->convert_date($upload_datestamp, "long");

	$text = "<div style='text-align:center'>
	<table style='width:85%' class='fborder'>

	<tr>
	<td style='width:30%' class='forumheader3'>Upload ID</td>
	<td style='width:70%' class='forumheader3'>$upload_id</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>Date</td>
	<td style='width:70%' class='forumheader3'>$upload_datestamp</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>Poster</td>
	<td style='width:70%' class='forumheader3'>$poster</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>Email</td>
	<td style='width:70%' class='forumheader3'><a href='mailto:$upload_email'>$upload_email</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>Website</td>
	<td style='width:70%' class='forumheader3'>".($upload_website ? "<a href='$upload_website'>$upload_website</a>" : " - ")."</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>File Name</td>
	<td style='width:70%' class='forumheader3'>".($upload_name ? $upload_name : " - ")."</td>
	</tr>
		
	<tr>
	<td style='width:30%' class='forumheader3'>Version</td>
	<td style='width:70%' class='forumheader3'>".($upload_version ? $upload_version : " - ")."</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>File</td>
	<td style='width:70%' class='forumheader3'>".(is_numeric($upload_file) ? "Binary file ID ".$upload_file : $upload_file)."</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>File Size</td>
	<td style='width:70%' class='forumheader3'>".parsesize($upload_filesize)."</td>
	</tr>
		
	<tr>
	<td style='width:30%' class='forumheader3'>Screenshot</td>
	<td style='width:70%' class='forumheader3'>".($upload_ss ? "<a href='".e_BASE."request.php?upload.".$upload_id."'>".$upload_ss."</a>" : " - ")."</td>
	</tr>
		
	<tr>
	<td style='width:30%' class='forumheader3'>Description</td>
	<td style='width:70%' class='forumheader3'>$upload_description</td>
	</tr>
		
	<tr>
	<td style='width:30%' class='forumheader3'>Demo</td>
	<td style='width:70%' class='forumheader3'>".($upload_demo ? $upload_demo : " - ")."</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>Options</td>
	<td style='width:70%' class='forumheader3'><a href='".e_SELF."?dlm.$upload_id'>copy to download manager</a> | <a href='".e_SELF."?news.$upload_id'>copy to newspost</a> | <a href='".e_SELF."?dis.$upload_id'>mark as not wanted</a></td>
	</tr>
	
	</table>
	</div>";

	$ns -> tablerender("View details", $text);

}


// list -------------------------------------------------------------------------------------------------------------------------------------------------------------------

$text = "<div style='text-align:center'>
<table style='width:85%' class='fborder'>
<tr><td class='forumheader' style='text-align:center' colspan='5'>";

if(!$active_uploads = $sql -> db_Select("upload", "*", "upload_active=0")){
	$text .= "There are no unmoderated public uploads.\n</td>\n</tr>";
}else{
	$text .= "There ".($active_uploads == 1 ? "is " : "are ").$active_uploads." unmoderated public upload".($active_uploads == 1 ? "" : "s")." ...";

	$text .= "</td></tr>";
	$text .= "<tr>
	<td style='width:5%' class='forumheader3'>ID</td>
	<td style='width:10%' class='forumheader3'>Date</td>
	<td style='width:20%' class='forumheader3'>Poster</td>
	<td style='width:20%' class='forumheader3'>Name</td>
	<td style='width:30%' class='forumheader3'>Filetype</td>
	</tr>";
	while($row = $sql -> db_Fetch()){
		extract($row);
		$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
		$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
		$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
		$upload_datestamp = $gen->convert_date($upload_datestamp, "short");
		$text .= "<tr>
		<td style='width:5%' class='forumheader3'>".$upload_id ."</td>
		<td style='width:20%' class='forumheader3'>".$upload_datestamp."</td>
		<td style='width:20%' class='forumheader3'>".$poster."</td>
		<td style='width:20%' class='forumheader3'><a href='".e_SELF."?view.".$upload_id."'>".$upload_name ."</a></td>
		<td style='width:20%' class='forumheader3'>".$upload_file ."</td>
		</tr>";
	}
}
$text .= "</table>\n</div>";

$ns -> tablerender("Uploads", $text);


// options -------------------------------------------------------------------------------------------------------------------------------------------------------------------

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>

<td style='width:50%' class='forumheader3'>Uploads Enabled?<br />
<span class='smalltext'>No public uploads will be permitted if disabled</span></td>
<td style='width:50%' class='forumheader3'>".
($pref['upload_enabled'][1] == 1 ? $rs -> form_radio("upload_enabled", 1, 1)." Yes".$rs -> form_radio("upload_enabled", 0)." No" : $rs -> form_radio("upload_enabled", 1)." Yes".$rs -> form_radio("upload_enabled", 0, 1)." No")."
</td>
</tr>


<td style='width:50%' class='forumheader3'>Storage type<br />
<span class='smalltext'>Choose how to store uploaded files, either as normal files on server or as binary info in database</span></td>
<td style='width:50%' class='forumheader3'>".
($pref['upload_storagetype'][1] == 1 ? $rs -> form_radio("upload_storagetype", 1, 1)." Flatfile".$rs -> form_radio("upload_storagetype", 2)." Database binary" : $rs -> form_radio("upload_storagetype", 1)." Flatfile".$rs -> form_radio("upload_storagetype", 2,1)." Database binary")."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Maximum file size<br />
<span class='smalltext'>Maximum upload size in bytes - leave blank to conform to php.ini setting ( php.ini setting is ".ini_get('upload_max_filesize')." )</span></td>
<td style='width:30%' class='forumheader3'>".
$rs -> form_text("upload_maxfilesize", 10, $pref['upload_maxfilesize'][1], 10)."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Allowed file types<br />
<span class='smalltext'>Please enter one type per line</span></td>
<td style='width:30%' class='forumheader3'>".
$rs -> form_textarea("upload_allowedfiletype", 20, 5, $pref['upload_allowedfiletype'][1])."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Permission<br />
<span class='smalltext'>Tick to allow only certain users to upload</span></td>
<td style='width:30%' class='forumheader3'>".

(!$pref['upload_class'][1] ? $rs -> form_checkbox("upload_class[]", 0, 1) : $rs -> form_checkbox("upload_class[]", 0))." Everyone<br /><span class='smalltext'>(ticking this box will override the classes below)</span><br />".

($pref['upload_class'][1] == 999 ? $rs -> form_checkbox("upload_class[]", 999, 1) : $rs -> form_checkbox("upload_class[]", 999))." Registered members only<br /><span class='smalltext'>(ticking this box will override the classes below)</span><br />";


if($sql -> db_Select("userclass_classes")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$text .= ($pref['upload_class'][1] && eregi($pref['upload_class'][1], $userclass_id) ? $rs -> form_checkbox("upload_class[]", $userclass_id, 1)." ".$userclass_name."<br />" : $rs -> form_checkbox("upload_class[]", $userclass_id)." ".$userclass_name."<br />");
	}
}

$text .= "</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'>".
$rs -> form_button("submit", "optionsubmit", "Submit")."
</td>
</tr>
</table>".
$rs -> form_close()."
</div>";

$ns -> tablerender("Options", $text);















function parsesize($size){
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if($size < $kb) {
		return $size." b";
	}else if($size < $mb) {
		return round($size/$kb,2)." kb";
	}else if($size < $gb) {
		return round($size/$mb,2)." mb";
	}else if($size < $tb) {
		return round($size/$gb,2)." gb";
	}else {
		return round($size/$tb,2)." tb";
	}
}



/*
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder'>";

if(!$sql -> db_Select("upload", "*", "upload_active=0")){
	$text .= "<tr><td class='forumheader3' style='text-align:center'>No public uploads.</td></tr>";
}else{
	while($row = $sql -> db_Fetch()){
		extract($row);

		$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
		$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));

		$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");

		$upload_datestamp = $gen->convert_date($upload_datestamp, "long");

		$text .= "<tr>
		<td style='width:30%' class='forumheader3'>Upload ID</td>
		<td style='width:70%' class='forumheader3'>$upload_id</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>Date</td>
		<td style='width:70%' class='forumheader3'>$upload_datestamp</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>Poster</td>
		<td style='width:70%' class='forumheader3'>$poster</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>Email</td>
		<td style='width:70%' class='forumheader3'><a href='mailto:$upload_email'>$upload_email</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>Website</td>
		<td style='width:70%' class='forumheader3'><a href='$upload_website'>$upload_website</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>File Name</td>
		<td style='width:70%' class='forumheader3'>$upload_filename</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>File Type</td>
		<td style='width:70%' class='forumheader3'>$upload_type</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>Version</td>
		<td style='width:70%' class='forumheader3'>".($upload_version ? $upload_version : "&nbsp;")."</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>File</td>
		<td style='width:70%' class='forumheader3'>$upload_file</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>Screenshot</td>
		<td style='width:70%' class='forumheader3'>".($upload_ss ? $upload_ss : "&nbsp;")."</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>Description</td>
		<td style='width:70%' class='forumheader3'>$upload_description</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>Demo</td>
		<td style='width:70%' class='forumheader3'>".($upload_demo ? $upload_demo : "&nbsp;")."</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>Options</td>
		<td style='width:70%' class='forumheader3'><a href='".e_SELF."?dlm.$upload_id'>copy to download manager</a> | <a href='".e_SELF."?news.$upload_id'>copy to newspost</a> | <a href='".e_SELF."?dis.$upload_id'>mark as not wanted</a></td>
		</tr>

		<tr>
		<td colspan='2'>&nbsp;</td>
		</tr>";
	}
	
	$text .= "</table>
	</div>";
	$ns -> tablerender("Public Uploads", $text);
}








*/
require_once("footer.php");
?>