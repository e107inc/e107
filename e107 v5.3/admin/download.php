<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/download.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");
$aj = new textparse;

$qs = explode(".", $_SERVER['QUERY_STRING']);
$action = $qs[0];
$id = $qs[1];

if($action == "from"){

	$sql -> db_Select("userfile", "*", "userfile_id='$id'");
	$row = $sql -> db_Fetch();
	extract($row);

	$download_name = $userfile_filename;
	$download_url = $userfile_file;
	$download_author = $userfile_sender;
	$download_author_email = $userfile_email;
	$download_author_website = $userfile_site;
	$download_description = $userfile_description;
	$download_image = $userfile_image;

}
//userfile_id  userfile_sender  userfile_email  userfile_site  userfile_datestamp  userfile_filename  userfile_file  userfile_image  userfile_description  userfile_authorized 


If(IsSet($_POST['submit'])){

	$download_description = $aj -> tp($_POST['download_description']);

	$sql -> db_Insert("download", "0, '".$_POST['download_name']."', '".$_POST['download_url']."', '".$_POST['download_author']."', '".$_POST['download_author_email']."', '".$_POST['download_author_website']."', '".$_POST['download_description']."', '".$_POST['download_filesize']."', '0', '".$_POST['download_type']."', '".$_POST['download_active']."', '".time()."', '".$_POST['download_thumb']."', '".$_POST['download_image']."' ");
	unset($download_name, $download_url, $download_author, $download_author_email, $download_author_website, $download_description, $download_filesize, $download_type);
	$message = "Download added to database.";
}

If(IsSet($_POST['update'])){

	$sql -> db_Update("download", "download_name='".$_POST['download_name']."', download_url='".$_POST['download_url']."', download_author='".$_POST['download_author']."', download_author_email='".$_POST['download_author_email']."', download_author_website='".$_POST['download_author_website']."', download_description='".$_POST['download_description']."', download_filesize='".$_POST['download_filesize']."', download_type='".$_POST['download_type']."', download_active='".$_POST['download_active']."', download_datestamp='".time()."', download_thumb='".$_POST['download_thumb']."', download_image='".$_POST['download_image']."' WHERE download_id='".$_POST['download_id']."' ");
	unset($download_name, $download_url, $download_author, $download_author_email, $download_author_website, $download_description, $download_filesize, $download_type);
	$message = "Article updated in database.";
}


If(IsSet($_POST['edit'])){
	$sql -> db_Select("download", "*", "download_id='".$_POST['existing']."' ");
	list($download_id, $download_name, $download_url, $download_author, $download_author_email, $download_author_website, $download_description, $download_filesize, $download_requested, $download_type, $download_active) = $sql-> db_Fetch();
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Delete("download", "download_id='".$_POST['existing']."' ");
	$message = "Download deleted.";
}

If(IsSet($_POST['delete'])){
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '".$_POST['existing']."' download - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"$PHP_SELF\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Download", $text);

	require_once("footer.php");
	exit;
}
If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$download_total = $sql -> db_Select("download");

if($download_total == "0"){
	$text = "<div style=\"text-align:center\" class=\"mediumtext\">
No downloads yet.</div>
<br />
	";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"$PHP_SELF\">

	Existing Downloads:
	<select name=\"existing\" class=\"tbox\">";
	while(list($download_id_, $download_name_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$download_id_\">".$download_name_."</option>";
	}
	$text .= "</select>
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" />
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	</div>
	<br />";
}

$text .= "
<form method=\"post\" action=\"$PHP_SELF\">\n
<table style=\"width:95%\">
<tr>
<td style=\"width:20%; vertical-align:top\"><u>Name</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_name\" size=\"60\" value=\"$download_name\" maxlength=\"100\" />
</td>
</tr>

<td style=\"width:20%; vertical-align:top\"><u>URL</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_url\" size=\"60\" value=\"$download_url\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Author:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_author\" size=\"60\" value=\"$download_author\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Author Email:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_author_email\" size=\"60\" value=\"$download_author_email\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Author Website:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_author_website\" size=\"60\" value=\"$download_author_website\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\"><u>Description</u>: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"download_description\" cols=\"70\" rows=\"5\">$download_description</textarea>
</td>
</tr>

<tr>
<td style=\"width:20%\">File size:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_filesize\" size=\"8\" value=\"$download_filesize\" maxlength=\"20\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Download Type:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_type\" size=\"8\" value=\"$download_type\" maxlength=\"20\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Thumbnail image:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_thumb\" size=\"60\" value=\"$download_thumb\" maxlength=\"150\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Main image:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"download_image\" size=\"60\" value=\"$download_image\" maxlength=\"150\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Active?:</td>
<td style=\"width:80%\">";


if($download_active == "0"){
	$text .= "Yes: <input type=\"radio\" name=\"download_active\" value=\"1\">
	No: <input type=\"radio\" name=\"download_active\" value=\"0\" checked>";
}else{
	$text .= "Yes: <input type=\"radio\" name=\"download_active\" value=\"1\" checked>
	No: <input type=\"radio\" name=\"download_active\" value=\"0\">";
}

$text .= "</td>
</tr>
<tr style=\"vertical-align:top\">
<td colspan=\"2\"  style=\"text-align:center\">";


If(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update\" value=\"Update Download\" />
	<input type=\"hidden\" name=\"download_id\" value=\"$download_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit Download\" />";
}

$text .= "</td>
</tr>
<tr>
<td colspan=\"2\"  class=\"smalltext\">
<br />
Tags allowed: all. <u>Underlined</u> fields are required.
</td>
</tr>
</table>
</form>";


$ns -> tablerender("<div style=\"text-align:center\">Downloads</div>", $text);

require_once("footer.php");
?>