<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/review.php
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
	if($_POST['data'] != ""){

		$content_heading = $aj -> tp($_POST['content_heading'], $mode="on");
		$content_subheading = $aj -> tp($_POST['content_subheading'], $mode="on");
		$data = $aj -> tp($_POST['data'], $mode="on");

        $content_parent = $_POST['parent_article'];

		 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$data', '0', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '3' ");
		unset($content_heading, $content_subheading, $data, $content_parent);
		$message = "Review added to database.";
	}else{
		$message = "Fields left blank.";
	}
}

If(IsSet($_POST['update'])){
	$content_heading = $aj -> tp($_POST['content_heading'], $mode="on");
	$content_subheading = $aj -> tp($_POST['content_subheading'], $mode="on");
	$data = $aj -> tp($_POST['data'], $mode="on");
    $content_parent = $_POST['parent_article'];
	$sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$data', content_page='".$_POST['content_page']."', content_comment='".$_POST['content_comment']."', content_summary='".$_POST['content_summary']."' WHERE content_id='".$_POST['content_id']."' ");

	unset($content_heading, $content_subheading, $data, $content_parent);
	$message = "Review updated in database.";
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($content_id, $content_heading, $content_subheading, $data, $content_page, $content_datestamp, $content_author, $content_comment, $content_summary, $content_type) = $sql-> db_Fetch();
	$data = $aj -> editparse($data);
}

If(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
		list($null, $content_heading, $null, $null, $content_page) = $sql-> db_Fetch();
		if($content_type == 255){
			$sql -> db_Delete("links", "link_name='".$content_heading."' ");
		}
		$sql -> db_Delete("content", "content_id='".$_POST['existing']."' ");
		$message = "Review deleted.";
		unset($content_heading, $content_page);
	}else{
		$message = "Please tick the confirm box to delete this review";
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$article_total = $sql -> db_Select("content", "*", "content_type='3' ");

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\" name=\"dataform\">\n
<table style=\"width:80%\" class=\"fborder\">
<tr>
<td class=\"forumheader\" style=\"text-align:center\" colspan=\"2\">";

if($article_total == "0"){
	$text .= "No reviews yet.";
}else{
	$text .= "<span class=\"defaulttext\">Existing Reviews:</span>
	<select name=\"existing\" class=\"tbox\">";
	while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$content_id_\">".$content_heading_."</option>";
	}
	$text .= "</select>
	<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" />
	<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
	<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> tick to confirm</span>";
}




while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
    if (IsSet($content_parent) && $content_parent == $content_id_) {
	    $text .= "<option value=\"$content_id_\" selected>".$content_heading_."</option>";
    }
    else {
	    $text .= "<option value=\"$content_id_\">".$content_heading_."</option>";
    }
}
$text .= "</select></td></tr>

<tr>
<td colspan=\"2\" style=\"text-align:center\" class=\"forumheader2\">
<input class=\"button\" type=\"button\" onClick=\"openwindow()\"  value=\"Open HTML Editor\" />
</td>
</tr>

<tr>
<td style=\"width:20%; vertical-align:top\" class=\"forumheader3\"><u>Heading</u>:</td>
<td style=\"width:80%\" class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" name=\"content_heading\" size=\"60\" value=\"$content_heading\" maxlength=\"100\" />

</td>
</tr>
<tr>
<td style=\"width:20%\" class=\"forumheader3\">Sub-Heading:</td>
<td style=\"width:80%\" class=\"forumheader3\">
<input class=\"tbox\" type=\"text\" name=\"content_subheading\" size=\"60\" value=\"$content_subheading\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\" class=\"forumheader3\">Summary:</td>
<td style=\"width:80%\" class=\"forumheader3\">
<textarea class=\"tbox\" name=\"content_summary\" cols=\"70\" rows=\"5\">$content_summary</textarea>
</td>
</tr>

<tr>
<td style=\"width:20%\" class=\"forumheader3\"><u>Review</u>: </td>
<td style=\"width:80%\" class=\"forumheader3\">
<textarea class=\"tbox\" name=\"data\" cols=\"70\" rows=\"30\">$data</textarea>
<br />";
require_once("../classes/shortcuts.php");
$text .= shortcuts("review");
$text .="</td>
</tr>

<tr>
<td style=\"width:20%\" class=\"forumheader3\">Allow comments?:</td>
<td style=\"width:80%\" class=\"forumheader3\">";


if($content_comment == "0"){
	$text .= "On: <input type=\"radio\" name=\"content_comment\" value=\"1\">
	Off: <input type=\"radio\" name=\"content_comment\" value=\"0\" checked>";
}else{
	$text .= "On: <input type=\"radio\" name=\"content_comment\" value=\"1\" checked>
	Off: <input type=\"radio\" name=\"content_comment\" value=\"0\">";
}

$text .= "</td></tr>
<tr style=\"vertical-align:top\">
<td colspan=\"2\" style=\"text-align:center\" class=\"forumheader\">";


If(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update\" value=\"Update Review\" />
	<input type=\"hidden\" name=\"content_id\" value=\"$content_id\">";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit Review\" />";
}

$text .= "</td>
</tr>
<tr>
<td colspan=\"2\" class=\"forumheader2\" style=\"text-align:right\">
<span class=\"smalltext\">
Tags allowed: all. <u>Underlined</u> fields are required.
</span>
</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style=\"text-align:center\">Reviews</div>", $text);

?>
<script type="text/javascript">
function addtext(sc){
	document.dataform.data.value += sc;
}
</script>
<?php

require_once("footer.php");
?>