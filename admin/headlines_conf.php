<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/plugin_conf/headlines_conf.php						|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1){
	header("location:../index.php");
}
require_once("auth.php");

if(IsSet($_POST['add_headline'])){
	$datestamp = time();
	$sql -> db_Insert("headlines", "0, '".$_POST['headline_url']."', '', '0', '".$_POST['description']."', '".$_POST['webmaster']."', '".$_POST['copyright']."', '".$_POST['tagline']."', '".$_POST['headline_image']."', '".$_POST['activate']."' ");
	$message = "Headline URL added to database.";
	unset($headline_url, $headline_button);
}

if(IsSet($_POST['update_headline'])){
	$sql -> db_Update("headlines", "headline_url='".$_POST['headline_url']."', headline_timestamp='0', headline_description='".$_POST['description']."', headline_webmaster='".$_POST['webmaster']."', headline_copyright='".$_POST['copyright']."', headline_tagline='".$_POST['tagline']."', headline_image='".$_POST['headline_image']."', headline_active='".$_POST['activate']."' WHERE headline_id='".$_POST['headline_id']."' ");
	$message = "Headline info updated in database.";
	unset($headline_url, $headline_image);
}

if(IsSet($_POST['confirm'])){
	$sql -> db_Delete("headlines", "headline_url='".$_POST['existing']."' ");
	$message = "Headline URL deleted.";
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("headlines", "*", "headline_url='".$_POST['existing']."' ");
	list($category_id, $category_name, $category_icon) = $sql-> db_Fetch();
	
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete the '$category_name' headline URL - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"$PHP_SELF\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Headline URL", $text);
	
	require_once("footer.php");
	exit;
}
if(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($_POST['edit'])){
	$sql -> db_Select("headlines", "*", "headline_url='".$_POST['existing']."' ");
	list($headline_id, $headline_url, $headline_data, $headline_timestamp, $headline_description, $headline_webmaster, $headline_copyright, $headline_tagline, $headline_image, $headline_active) = $sql-> db_Fetch();
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$headline_total = $sql -> db_Select("headlines");

if($headline_total == "0"){
	$text = "<div style=\"text-align:center\">No headline URL's set yet.</div>
	<br /><br />";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"$PHP_SELF\">
	
	Existing Headline URL's: 
	<select name=\"existing\" class=\"tbox\">";
	while(list($head_id, $head_url) = $sql-> db_Fetch()){
		$text .= "<option>".$head_url."</option>";
	}
	$text .= "</select> 
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	</div>
	<br />";
}


$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\">Backend URL: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"headline_url\" size=\"60\" value=\"$headline_url\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:30%\">Path to image: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"headline_image\" size=\"40\" value=\"$headline_image\" maxlength=\"200\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">Display Tagline?:</td>
<td style=\"width:80%\">";
if($headline_tagline == 1){
	$text .= "<input type=\"checkbox\" name=\"tagline\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"tagline\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr>
<td style=\"width:20%\">Display Description?:</td>
<td style=\"width:80%\">";
if($headline_description == 1){
	$text .= "<input type=\"checkbox\" name=\"description\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"description\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr>
<td style=\"width:20%\">Display Webmaster?:</td>
<td style=\"width:80%\">";
if($headline_webmaster == 1){
	$text .= "<input type=\"checkbox\" name=\"webmaster\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"webmaster\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr>
<td style=\"width:20%\">Display Copyright?:</td>
<td style=\"width:80%\">";
if($headline_copyright == 1){
	$text .= "<input type=\"checkbox\" name=\"copyright\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"copyright\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr>
<td style=\"width:20%\">Activate?:</td>
<td style=\"width:80%\">";
if($headline_active == 1){
	$text .= "<input type=\"checkbox\" name=\"activate\" value=\"1\" checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"activate\" value=\"1\">";
}
$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">";

if(IsSet($_POST['edit'])){

	$text .= "<input class=\"button\" type=\"submit\" name=\"update_headline\" value=\"Update Headline SIte\" />
<input type=\"hidden\" name=\"headline_id\" value=\"$headline_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"add_headline\" value=\"Add Headline Site\" />";
}
$text .= "</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Headline</div>", $text);

require_once("footer.php");
?>	