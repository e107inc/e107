<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin//content.php
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
if(!getperms("J") && !getperms("K") && !getperms("L")){
	header("location:".e_HTTP."index.php");
}
require_once("auth.php");
$aj = new textparse;

If(IsSet($_POST['submit'])){
	if($_POST['content_content'] != "" && $_POST['content_content'] != ""){

		$content_heading = $aj -> tp($_POST['content_heading'], $mode="on");
		$content_subheading = $aj -> tp($_POST['content_subheading'], $mode="on");
		$content_content = $aj -> tp($_POST['content_content'], $mode="on");

		 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '0', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '0', '1' ");
		unset($content_heading, $content_subheading, $content_content, $content_parent);


		if($_POST['content_heading'] != ""){
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$sql -> db_Insert("links", "0, '".$content_heading."', 'article.php?".$content_id.".255', '', '', '1', '0', '0', '0' ");
			$message = "Content page added and link created in Main Navigation menu.";
		}else{
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$message = "Content page added without link - to link to this content page use this url - 'article.php?".$content_id.".255'.";
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
	$sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_page='".$_POST['content_page']."', content_comment='".$_POST['content_comment']."', content_parent='".$content_parent."' WHERE content_id='".$_POST['content_id']."' ");

	unset($content_heading, $content_subheading, $content_content, $content_parent);
	$message = "Content updated in database.";
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($content_id, $content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $content_comment, $content_parent, $content_type) = $sql-> db_Fetch();
	$content_content = $aj -> editparse($content_content);
}

If(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql = new db;
		$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
		$row = $sql -> db_Fetch(); extract($row);
		$sql -> db_Delete("links", "link_name='".$content_heading."' ");
		$sql -> db_Delete("content", "content_id='".$_POST['existing']."' ");
		$message = "Content page deleted.";
		unset($content_heading, $content_subheading, $content_content);
	}else{
		$message = "Please tick the confirm box to delete this content page";
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$article_total = $sql -> db_Select("content", "*", "content_type='254' OR content_type='255' OR content_type='1' ");

if($article_total == "0"){
	$text = "<div style=\"text-align:center\">
No content pages yet.
<br />
	";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".e_SELF."\">

	Existing Content Pages:
	<select name=\"existing\" class=\"tbox\">";
	while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$content_id_\">".$content_heading_."</option>";
	}
	$text .= "</select>
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" />
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> tick to confirm</span>
	</form>
	</div>
	<br />";
}

$text .= "
<form method=\"post\" action=\"".e_SELF."\" name=\"articlepostform\">\n
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
<td style=\"width:20%; vertical-align:top\">Link name:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"content_heading\" size=\"60\" value=\"$content_heading\" maxlength=\"100\" />

</td>
</tr>
<tr>
<td style=\"width:20%\">Page Heading:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"content_subheading\" size=\"60\" value=\"$content_subheading\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:20%\"><u>Content</u>: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"content_content\" cols=\"70\" rows=\"30\">$content_content</textarea>
<br />";
require_once("../classes/shortcuts.php");
$text .= shortcuts();
$text .= "</td>
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

$text .= "</td></tr>
<tr style=\"vertical-align:top\">
<td colspan=\"2\"  style=\"text-align:center\"><br />";


If(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update\" value=\"Update Content Page\" />
	<input type=\"hidden\" name=\"content_id\" value=\"$content_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit Content Page\" />";
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


$ns -> tablerender("<div style=\"text-align:center\">Content Pages</div>", $text);

?>
<script type="text/javascript">
function addtext(sc){
	document.articlepostform.content_content.value += sc;
}
</script>
<?php
require_once("footer.php");
?>