<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/article.php														|
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
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");
$aj = new textparse;

If(IsSet($_POST['submit'])){
	if($_POST['content_content'] != "" && $_POST['content_content'] != ""){

		$content_heading = $aj -> tp($_POST['content_heading'], $mode="on");
		$content_subheading = $aj -> tp($_POST['content_subheading'], $mode="on");
		$content_content = $aj -> tp($_POST['content_content'], $mode="on");

        $content_parent = $_POST['parent_article'];

		 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '0', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '0', '".$_POST['content_type']."' ");
		unset($content_heading, $content_subheading, $content_content, $content_parent);

		if($_POST['content_type'] == 255){
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$sql -> db_Insert("links", "0, '".$content_heading."', 'article.php?".$content_id.".255', '', '', '1', '0', '0' ");
		}

		if($_POST['content_type'] == 254){
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$message = "To link to this content page use this url - article.php?".$content_id.".255.";
		}

	}else{
		$message = "Fields left blank.";
	}

}

If(IsSet($_POST['update'])){
	$content_heading = $aj -> tp($_POST['content_heading'], $mode="on");
	$content_subheading = $aj -> tp($_POST['content_subheading'], $mode="on");
	$content_content = $aj -> tp($_POST['content_content'], $mode="on");
    $content_parent = $_POST['parent_article'];
	$sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_page='".$_POST['content_page']."', content_comment='".$_POST['content_comment']."', content_parent='".$content_parent."', content_type='".$_POST['content_type']."' WHERE content_id='".$_POST['content_id']."' ");

	unset($content_heading, $content_subheading, $content_content, $content_parent);
	$message = "Article/content updated in database.";
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($content_id, $content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $content_comment, $content_parent, $content_type) = $sql-> db_Fetch();
}

If(IsSet($_POST['confirm'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($null, $content_heading, $null, $null, $content_page) = $sql-> db_Fetch();
	if($content_type == 255){
		$sql -> db_Delete("links", "link_name='".$content_heading."' ");
	}
	$sql -> db_Delete("content", "content_id='".$_POST['existing']."' ");
	$message = "Article deleted.";
	unset($content_heading, $content_page);
}

If(IsSet($_POST['delete'])){
	$sql -> db_Select("content", "content_id='".$_POST['existing']."' ");
	list($null, $content_heading_) = $sql-> db_Fetch();
	$text = "<div style=\"text-align:center\">
	<b>Please confirm you wish to delete this article $content_heading_ - once deleted it cannot be retrieved</b>
<br /><br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" />
<input class=\"button\" type=\"submit\" name=\"confirm\" value=\"Confirm Delete\" />
<input type=\"hidden\" name=\"existing\" value=\"".$_POST['existing']."\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Article", $text);

	require_once("footer.php");
	exit;
}
If(IsSet($_POST['cancel'])){
	$message = "Delete cancelled.";
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$article_total = $sql -> db_Select("content");

if($article_total == "0"){
	$text = "<div style=\"text-align:center\">
No articles yet.
<br />
	";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">

	Existing Articles:
	<select name=\"existing\" class=\"tbox\">";
	while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$content_id_\">".$content_heading_."</option>";
	}
	$text .= "</select>
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" />
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	</form>
	</div>
	<br />";
}

$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\" name=\"articlepostform\">\n
<table style=\"width:95%\">";


while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
    if (IsSet($content_parent) && $content_parent == $content_id_) {
	    $text .= "<option value=\"$content_id_\" selected>".$content_heading_."</option>";
    }
    else {
	    $text .= "<option value=\"$content_id_\">".$content_heading_."</option>";
    }
}
$text .= "</select></td></tr>";

$text .= "<tr>
<td style=\"width:20%; vertical-align:top\"><u>Heading</u>:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"content_heading\" size=\"60\" value=\"$content_heading\" maxlength=\"100\" />

</td>
</tr>
<tr>
<td style=\"width:20%\">Sub-Heading:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"content_subheading\" size=\"60\" value=\"$content_subheading\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:20%\"><u>Article</u>: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"content_content\" cols=\"70\" rows=\"30\">$content_content</textarea>
<br />
<input class=\"button\" type=\"button\" value=\"newpage\" onclick=\"addtext('[newpage]')\">
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link][/link]')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext('[b][/b]')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext('[i][/i]')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext('[u][/u]')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext('[img][/img]')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext('[center][/center]')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext('[left][/left]')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext('[right][/right]')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext('[blockquote][/blockquote]')\">
</td>
</tr>

<tr>
<td style=\"width:20%\">Allow comments?:</td>
<td style=\"width:80%\">";


if($content_comment == "0"){
	$text .= "On: <input type=\"radio\" name=\"content_comment\" value=\"1\">
	Off: <input type=\"radio\" name=\"content_comment\" value=\"0\" checked>";
}else{
	$text .= "On: <input type=\"radio\" name=\"content_comment\" value=\"1\" checked>
	Off: <input type=\"radio\" name=\"content_comment\" value=\"0\">";
}


$text .= "</td></tr><tr><td style=\"width:20%\">Turn off auto &lt;br />'s: </td>
<td style=\"width:80%\">";
if($content_page == 1){
	$text .= "<input type=\"checkbox\" name=\"content_page\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"content_page\" value=\"1\">";
}


$text .= " (check this if there are html tags in your article)</td></tr><tr>
<td style=\"width:20%\">Content Type:</td>
<td style=\"width:80%\">
<select name=\"content_type\" class=\"tbox\">";
if($content_type == 0){
	$text .= "<option value=\"0\" selected>Article</option>";
}else{
	$text .= "<option value=\"0\">Article</option>";
}

if($content_type == 255){
	$text .= "<option value=\"255\" selected>Normal Content with link</option>";
}else{
	$text .= "<option value=\"255\">Normal Content with link</aoption>";
}

if($content_type == 254){
	$text .= "<option value=\"254\" selected>Normal Content without link</option>";
}else{
	$text .= "<option value=\"254\">Normal Content without link</aoption>";
}

if($content_type == 3){
	$text .= "<option value=\"3\" selected>Review</option>";
}else{
	$text .= "<option value=\"3\">Review</aoption>";
}

$text .= "</td></tr>
<tr style=\"vertical-align:top\">
<td colspan=\"2\"  style=\"text-align:center\"><br />";


If(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update\" value=\"Update Article\" />
	<input type=\"hidden\" name=\"content_id\" value=\"$content_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit Article\" />";
}

$text .= "</td>
</tr>
<tr>
<td colspan=\"2\"  class=\"smalltext\">
<br />
Tags allowed: all. <u>Underlined</u> fields are required. Use [newpage] to seperate multi-page articles.
</td>
</tr>
</table>
</form>";


$ns -> tablerender("<div style=\"text-align:center\">Articles</div>", $text);

require_once("footer.php");
?>

<script type="text/javascript">
function addtext(sc){
	document.articlepostform.content_content.value += sc;
}
</script>