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
	exit;
}
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

$aj = new textparse;

If(IsSet($_POST['submit'])){
	if($_POST['data'] != ""){
		$content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
		$content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
		$content_content = $aj -> formtpa($_POST['data'], "admin");
		 $sql -> db_Insert("content", "0, '".$content_heading."', '".$content_subheading."', '$content_content', '".$_POST['auto_line_breaks']."', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '', '1', 0, 0,  {$_POST['c_class']}");
		if($_POST['content_heading']){
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$sql -> db_Insert("links", "0, '".$content_heading."', 'content.php?content.$content_id', '', '', '1', '0', '0', '0', {$_POST['c_class']} ");
			$message = CNTLAN_24;
		}else{
			$sql -> db_Select("content", "*", "ORDER BY content_datestamp DESC LIMIT 0,1 ", $mode="no_where");
			list($content_id, $content_heading) = $sql-> db_Fetch();
			$message = CNTLAN_23." - 'article.php?".$content_id.".255'.";
		}
		clear_cache("content");
		unset($content_heading, $content_subheading, $content_content, $content_parent);
	}else{
		$message = CNTLAN_1;
	}
}

If(IsSet($_POST['update'])){
	$content_subheading = $aj -> formtpa($_POST['content_subheading'], "admin");
	$content_heading = $aj -> formtpa($_POST['content_heading'], "admin");
	$content_content = $aj -> formtpa($_POST['data'], "admin");
	$sql -> db_Update("content", " content_heading='$content_heading', content_subheading='$content_subheading', content_content='$content_content', content_parent='".$_POST['auto_line_breaks']."',  content_comment='".$_POST['content_comment']."', content_class='{$_POST['c_class']}' WHERE content_id='".$_POST['content_id']."'");
	$sql -> db_Update("links", "link_class='".$_POST['c_class']."' WHERE link_name='$content_heading' ");
	unset($content_heading, $content_subheading, $content_content, $content_parent);
	$message = CNTLAN_2;
	clear_cache("content");
}

if($action == "delete"){
	$sql = new db;
	$sql -> db_Select("content", "*", "content_id=$sub_action");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Delete("links", "link_name='".$content_heading."' ");
	$sql -> db_Delete("content", "content_id=$sub_action");
	$message = CNTLAN_20;
	unset($content_heading, $content_subheading, $content_content);
	clear_cache("content");
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>\n";
if(!$content_total = $sql -> db_Select("content", "*", "content_type='254' OR content_type='255' OR content_type='1' ORDER BY content_datestamp DESC")){
	$text .= "<div style='text-align:center'>".CNTLAN_4."</div>";
}else{
	$text .= "<table class='fborder' style='width:100%'>
	<tr>
	<td style='width:5%' class='forumheader2'>&nbsp;</td>
<td style='width:65%' class='forumheader2'>".CNTLAN_25."</td>
<td style='width:30%' class='forumheader2'>".CNTLAN_26."</td>


</tr>";

	while($row = $sql -> db_Fetch()){
		extract($row);
		$text .= "<td style='width:5%; text-align:center' class='forumheader3'>$content_id</td>
		<td style='width:65%' class='forumheader3'>$content_heading</td>
		<td style='width:30%; text-align:center' class='forumheader3'>
		".$rs -> form_button("submit", "main_edit", CNTLAN_6, "onClick=\"document.location='".e_SELF."?edit.$content_id'\"")." 
		".$rs -> form_button("submit", "main_delete", CNTLAN_7, "onClick=\"confirm_($content_id)\"")."
		
		</td>\n</tr>";
	}
	$text .= "</table>\n";
}
$text .= "</div>";

$ns -> tablerender(CNTLAN_5, $text);

unset($content_heading, $content_subheading, $content_content, $content_parent, $content_comment);

if($action == "edit"){
	if($sql -> db_Select("content", "*", "content_id=$sub_action")){
		$row = $sql -> db_Fetch(); extract($row);
	}
}else{
	$content_comment = TRUE;
}

$article_total = $sql -> db_Select("content", "*", "content_type='254' OR content_type='255' OR content_type='1' ");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>
<table style='width:80%' class='fborder'>
<tr>
<td colspan='2' style='text-align:center' class='forumheader2'>
<input class='button' type='button' onClick='openwindow()'  value='".CNTLAN_9."' />
</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".CNTLAN_10.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_heading' size='60' value='$content_heading' maxlength='100' />

</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_11.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_subheading' size='60' value='$content_subheading' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>".CNTLAN_12."</u>: </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='data' cols='70' rows='30' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$content_content</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='100' />
<br />";
require_once(e_HANDLER."ren_help.php");
$text .= ren_help()."
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_21."?:</td>
<td style='width:80%' class='forumheader3'>";

if($content_parent){
	$text .= CNTLAN_14.": <input type='radio' name='auto_line_breaks' value='0'>
	".CNTLAN_15.": <input type='radio' name='auto_line_breaks' value='1' checked>";
}else{
	$text .= CNTLAN_14.": <input type='radio' name='auto_line_breaks' value='0' checked>
	".CNTLAN_15.": <input type='radio' name='auto_line_breaks' value='1'>";
}
$text .= "<span class='smalltext'>".CNTLAN_22."</span>
</td></tr>
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_13."?:</td>
<td style='width:80%' class='forumheader3'>";


if(!$content_comment){
	$text .= CNTLAN_14.": <input type='radio' name='content_comment' value='1'>
	".CNTLAN_15.": <input type='radio' name='content_comment' value='0' checked>";
}else{
	$text .= CNTLAN_14.": <input type='radio' name='content_comment' value='1' checked>
	".CNTLAN_15.": <input type='radio' name='content_comment' value='0'>";
}


$text .= "
</td></tr>
";

$text.="
<tr>
<td style='width:20%' class='forumheader3'>".CNTLAN_19.":</td>
<td style='width:80%' class='forumheader3'>".r_userclass("c_class",$content_class)."
</td>
</tr>
<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";


if($action == "edit"){
	$text .= "<input class='button' type='submit' name='update' value='".CNTLAN_16."' />
	<input type='hidden' name='content_id' value='$content_id'>";
}else{
	$text .= "<input class='button' type='submit' name='submit' value='".CNTLAN_17."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style='text-align:center'>".CNTLAN_18."</div>", $text);

echo "<script type=\"text/javascript\">
function confirm_(content_id){
	var x=confirm(\"".CNTLAN_27." [ID: \" + content_id + \"]\");
if(x)
	window.location='".e_SELF."?delete.' + content_id;
}
</script>";

require_once("footer.php");
?>