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
	header("location:".e_BASE."index.php");
	exit;
}
require_once("auth.php");
$aj = new textparse;

If(IsSet($_POST['submit'])){
	if($_POST['data'] != ""){

		$content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
		$content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
		$content_content = $aj -> formtpa($_POST['data'], "admin");

		 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '".$_POST['content_rating']."', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '3' ,".$_POST['r_class']);
		unset($content_heading, $content_subheading, $data, $content_parent);
		$message = REVLAN_1;
	}else{
		$message = REVLAN_2;
	}
}

If(IsSet($_POST['update'])){
	$content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
	$content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
	$content_content = $aj -> formtpa($_POST['data'], "admin");

	$sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_page='".$_POST['content_rating']."', content_comment='".$_POST['content_comment']."', content_summary='".$_POST['content_summary']."', content_class='{$_POST['r_class']}' WHERE content_id='".$_POST['content_id']."' ");

	unset($content_heading, $content_subheading, $data, $content_parent);
	$message = REVLAN_3;
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($content_id, $content_heading, $content_subheading, $data, $content_rating, $content_datestamp, $content_author, $content_comment, $content_summary, $content_type,$content_class) = $sql-> db_Fetch();
}

If(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
		list($null, $content_heading, $null, $null, $content_page) = $sql-> db_Fetch();
		if($content_type == 255){
			$sql -> db_Delete("links", "link_name='".$content_heading."' ");
		}
		$sql -> db_Delete("content", "content_id='".$_POST['existing']."' ");
		$message = REVLAN_4;
		unset($content_heading, $content_page);
	}else{
		$message = REVLAN_5;
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

require_once(e_HANDLER."userclass_class.php");

$article_total = $sql -> db_Select("content", "*", "content_type='3' ");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>\n
<table style='width:80%' class='fborder'>
<tr>
<td class='forumheader' style='text-align:center' colspan='2'>";

if($article_total == "0"){
	$text .= REVLAN_6;
}else{
	$text .= "<span class='defaulttext'>".REVLAN_7.":</span>
	<select name='existing' class='tbox'>";
	while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
		$text .= "<option value='$content_id_'>".$content_heading_."</option>";
	}
	$text .= "</select>
	<input class='button' type='submit' name='edit' value='".REVLAN_8."' />
	<input class='button' type='submit' name='delete' value='".REVLAN_9."' />
	<input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".REVLAN_10."</span>";
}

while(list($content_id_, $content_heading_) = $sql-> db_Fetch()){
    if (IsSet($content_parent) && $content_parent == $content_id_) {
	    $text .= "<option value='$content_id_' selected>".$content_heading_."</option>";
    }
    else {
	    $text .= "<option value='$content_id_'>".$content_heading_."</option>";
    }
}
$text .= "</select></td></tr>

<tr>
<td colspan='2' style='text-align:center' class='forumheader2'>
<input class='button' type='button' onClick='openwindow()'  value='".REVLAN_11."' />
</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'><u>".REVLAN_12."</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />

</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'>".REVLAN_13.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".REVLAN_14.":</td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='content_summary' cols='70' rows='5'>$content_summary</textarea>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'><u>".REVLAN_15."</u>: </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='data' cols='70' rows='30'>$data</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='100' />
<br />";
require_once(e_HANDLER."ren_help.php");
$text .= ren_help("addtext", TRUE)."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".REVLAN_16.":</td>
<td style='width:80%' class='forumheader3'>
<select name='content_rating' class='tbox'>
<option value='0'>".REVLAN_17." ...</option>";
for($a=1; $a<=100; $a++){
	$text .= ($content_rating == $a ? "<option value='$a' selected>$a</option>" : "<option value='$a'>$a</option>");
}
$text .= "</select>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".REVLAN_18.":</td>
<td style='width:80%' class='forumheader3'>
";


if($content_comment == "0"){
	$text .= REVLAN_19.": <input type='radio' name='content_comment' value='1'>
	".REVLAN_20.": <input type='radio' name='content_comment' value='0' checked>";
}else{
	$text .= REVLAN_19.": <input type='radio' name='content_comment' value='1' checked>
	".REVLAN_20.": <input type='radio' name='content_comment' value='0'>";
}

$text .= "</td></tr>";

$text.="
<td style='width:20%' class='forumheader3'>".REVLAN_21.":</td>
<td style='width:80%' class='forumheader3'>".r_userclass("r_class",$content_class)."
";

$text.="
<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>
";


If(IsSet($_POST['edit'])){
	$text .= "<input class='button' type='submit' name='update' value='".REVLAN_22."' />
	<input type='hidden' name='content_id' value='$content_id'>";
}else{
	$text .= "<input class='button' type='submit' name='submit' value='".REVLAN_23."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style='text-align:center'>".REVLAN_24."</div>", $text);

?>
<script type="text/javascript">
function addtext(sc){
	document.dataform.data.value += sc;
}
function help(help){
	document.dataform.helpb.value = help;
}
</script>
<?php

require_once("footer.php");
?>