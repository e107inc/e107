<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/article.php
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
if(!getperms("J") && !getperms("K") && !getperms("L")){header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

$obj = new convert;
$aj = new textparse;


If(IsSet($_POST['submit'])){
	if($_POST['data']){
		if($_POST['add_icons']){
			if(!strstr($_POST['data'], "EMAILPRINT")){
				$_POST['data'] .= "{EMAILPRINT}";
			}
		}else{
			$_POST['data'] = str_replace("{EMAILPRINT}", "", $_POST['data']);
		}
		$_POST['content_heading'] = $aj -> formtpa($_POST['content_heading'], "admin");
		$_POST['content_subheading'] = $aj -> formtpa($_POST['content_subheading'], "admin");
		$_POST['data'] = article_preserve_html($aj -> formtpa($_POST['data'], "admin"));
		$_POST['content_summary'] = $aj -> formtpa($_POST['content_summary'], "admin");
		$sql -> db_Insert("content", "0, '".$_POST['content_heading']."', '".$_POST['content_subheading']."', '".$_POST['data']."', '{$_POST['content_page']}', '".time()."', '".ADMINID."', '".$_POST['content_comment']."', '".$_POST['content_summary']."', '0' ,'{$_POST['a_class']}' ");
		unset($_POST['content_heading'], $_POST['content_subheading'], $_POST['data'], $_POST['content_comment'], $_POST['content_summary']);
		$message = ARLAN_0;		
	}else{
		$message = ARLAN_1;
	}
}

if(IsSet($_POST['preview'])){

	$datestamp = $obj->convert_date(time(), "long");	
	$content_heading = $aj -> tpa($_POST['content_heading']);
	$content_subheading = $aj -> tpa($_POST['content_subheading']);
	$data = article_preserve_html($aj -> tpa($_POST['data']));
	$content_summary= $aj -> tpa($_POST['content_summary']);

	$text = "<i>by ".ADMINNAME."</i><br /><span class='smalltext'>".$datestamp."</span><br /><br />Subheading: $content_subheading<br />Summary: $content_summary<br /><br />$data";
	$ns -> tablerender($content_heading, $text);
	echo "<br /><br />";

	// make form friendly ...
	$_POST['content_heading'] = $aj -> formtpa($_POST['content_heading'], "admin");
	$_POST['content_subheading'] = $aj -> formtpa($_POST['content_subheading'], "admin");
	$_POST['data'] = $aj -> formtpa($_POST['data'], "admin");
	$_POST['content_summary'] = $aj -> formtpa($_POST['content_summary'], "admin");

	If(IsSet($_POST['edit'])){
		$edit = TRUE;
		unset($_POST['edit']);
	}
}

If(IsSet($_POST['update'])){
	if($_POST['content_heading'] && $_POST['data']){
		$sql = new db;

		if($_POST['add_icons']){
			if(!strstr($_POST['data'], "EMAILPRINT")){
				$_POST['data'] .= "{EMAILPRINT}";
			}
		}else{
			$_POST['data'] = str_replace("{EMAILPRINT}", "", $_POST['data']);
		}

		$_POST['content_heading'] = $aj -> formtpa($_POST['content_heading'], "admin");
		$_POST['content_subheading'] = $aj -> formtpa($_POST['content_subheading'], "admin");
		$_POST['data'] = article_preserve_html($aj -> formtpa($_POST['data'], "admin"));
		$_POST['content_summary'] = $aj -> formtpa($_POST['content_summary'], "admin");

		if(!$content_id){ $content_id = $_POST['content_id']; }
		$sql -> db_Update("content", " content_heading='".$_POST['content_heading']."', content_subheading='".$_POST['content_subheading']."', content_content='".$_POST['data']."', content_comment='".$_POST['content_comment']."', content_type='".$_POST['content_type']."', content_summary='".$_POST['content_summary']."', content_page='".$_POST['content_page']."', content_class='{$_POST['a_class']}' WHERE content_id='".$_POST['content_id']."' ");
		unset($_POST['content_page'], $_POST['content_heading'], $_POST['content_subheading'], $_POST['data'], $_POST['content_comment'], $_POST['content_summary'], $_POST['edit']);
		$message = ARLAN_2;
	}else{
		$message = ARLAN_1;
	}
}

If(IsSet($_POST['edit'])){
	article_edit();
}

If(IsSet($_POST['delete'])){
	$message = article_delete();
}

If(IsSet($_POST['psubmit'])){
	$_POST['parent'] = $aj -> formtpa($_POST['parent'], "admin");
	$_POST['parent_summary'] = $aj -> formtpa($_POST['parent_summary'], "admin");
	$sql -> db_Insert("content", "0, '".$_POST['parent']."', '".$_POST['parent_summary']."', '', '0', '".time()."', '".ADMINID."', '', '', '6', '".$_POST['p_class']."' ");
	unset($parent);
	unset($parent_summary);
	$message = ARLAN_3;
}

If(IsSet($_POST['pupdate'])){
	$_POST['parent'] = $aj -> formtpa($_POST['parent'], "admin");
	$_POST['parent_summary'] = $aj -> formtpa($_POST['parent_summary'], "admin");
	$sql -> db_Update("content", "content_class='{$_POST['p_class']}',content_heading='".$_POST['parent']."', content_subheading='$parent_summary' WHERE content_id='".$_POST['pexisting']."' ");
	unset($parent);
	unset($parent_summary);
	$message = ARLAN_4;
}

If(IsSet($_POST['pedit'])){
	$sql -> db_Select("content", "*", "content_id='".$_POST['pexisting']."' ");
	$row = $sql-> db_Fetch();
	extract($row);
	$parent = stripslashes($content_heading);
	$parent_summary = stripslashes($content_subheading);
	$parent_class = $content_class;
}

If(IsSet($_POST['pdelete'])){
	if($_POST['confirm']){
		$sql -> db_Select("content", "content_id, content_heading", "content_id='".$_POST['pexisting']."' ");
		$row = $sql -> db_Fetch();
		extract($row);
		$sql -> db_Delete("content", "content_id='".$_POST['pexisting']."' ");
		$sql -> db_Update("content", "content_page='0' WHERE content_page='".$_POST['pexisting']."' AND content_type='0' ");
		$message = ARLAN_5;
	}else{
		$message = ARLAN_6;
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$ns -> tablerender("<div style='text-align:center'>".ARLAN_7."</div>", "");

////////////  Article parents //////////////////////////////////////////////
require_once(e_HANDLER."userclass_class.php");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:80%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

$article_parent_total = $sql -> db_Select("content", "*", "content_type='6' ");
if($article_parent_total == 0){
	$text .= "<span class='defaulttext'>".ARLAN_8."</span>";
}else{
	$text .= "<span class='defaulttext'>".ARLAN_9.": </span>
<select name='pexisting' class='tbox'>";
	$c=0;
	while($row = $sql-> db_Fetch()){
		extract($row);
		$parents[$c] = $content_heading;
		$parents_id[$c] = $content_id;
		($_POST['pexisting']==$content_id) ? $s=" Selected" : $s="";
		$text .= "<option value='".$parents_id[$c]."'".$s.">".$parents[$c]."\n";
		$c++;
	}
	$text .= "</select>
<input class='button' type='submit' name='pedit' value='".ADLAN_78."' /> 
<input class='button' type='submit' name='pdelete' value='".ADLAN_79."' />
<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> ".ADLAN_80."</span>
";
}
$text .= "
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>".ARLAN_10."</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='parent' size='60' value='$parent' maxlength='250' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".ARLAN_11.":</td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='parent_summary' cols='70' rows='3'>".$parent_summary."</textarea>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".ARLAN_55.":</td>
<td style='width:80%' class='forumheader3'>
".r_userclass("p_class",$parent_class)."
</td>
</tr>

";

$text .= "<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>";


if(IsSet($_POST['pedit'])){
	$text .= "<input class='button' type='submit' name='pupdate' value='".ADLAN_81." ".ARLAN_12."' />";
}else{
	$text .= "<input class='button' type='submit' name='psubmit' value='".ADLAN_82." ".ARLAN_12."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(ARLAN_13, $text);
/////////////////////////////////////////////////



$article_total = $sql -> db_Select("content", "*", "content_type='0' ");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>
<table style='width:80%' class='fborder'>
<tr>
<td style='text-align:center' colspan='2' class='forumheader'>";

if($article_total == "0"){
	$text .= ARLAN_14;
}else{
	$text .= "
	<span class='defaulttext'>".ADLAN_83.ARLAN_15.":</span>
	<select name='existing' class='tbox'>";
	while($row = $sql-> db_Fetch()){
		extract($row);
		$text .= "<option value='".$content_id."'>".$content_heading."</option>";
	}
	$text .= "</select>
	<input class='button' type='submit' name='edit' value='".ADLAN_78."' /> 
	<input class='button' type='submit' name='delete' value='".ADLAN_79."' />
	<input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".ADLAN_80."</span>
	</td></tr>";
}


$text .= "
<tr>
<td colspan='2' style='text-align:center' class='forumheader2'>
<input class='button' type='button' onClick='openwindow()'  value='".ADLAN_84."' />
</td>
</tr>

<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".ARLAN_12.":</td>
<td style='width:80%; vertical-align:top' class='forumheader3'>
<select name='content_page' class='tbox'>
";
$sel="";
if($parent_id == '0'){$sel = " SELECTED";}
$text.="<option value='0' ".$sel.">".ARLAN_16;
for($i=0;$i<count($parents_id);$i++){
	$sel="";
	if($_POST['content_page'] == $parents_id[$i]){$sel = " SELECTED";}
	$text.="<option value='".$parents_id[$i]."' ".$sel.">".$parents[$i];
}
$text.="
</select>
</td>
</tr>
";
	
$text.="
<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'><u>".ARLAN_17."</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_heading' size='60' value='".stripslashes($_POST['content_heading'])."' maxlength='100' />

</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'>".ARLAN_18.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='content_subheading' size='60' value='".stripslashes($_POST['content_subheading'])."' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".ARLAN_19.":</td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='content_summary' cols='70' rows='5'>".stripslashes($_POST['content_summary'])."</textarea>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'><u>".ARLAN_20."</u>: </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='data' cols='70' rows='30'>".stripslashes($_POST['data'])."</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='100' />
<br />";

require_once(e_HANDLER."ren_help.php");
$text .= ren_help("addtext", TRUE)."
</td>
</tr>

<tr>
<td colspan='2' class='forumheader3'>".ARLAN_21.":&nbsp;&nbsp;";
if(!$_POST['content_comment']){
	$text .= ARLAN_22.": <input type='radio' name='content_comment' value='1'>
	".ARLAN_23.": <input type='radio' name='content_comment' value='0' checked>";
}else{
	$text .= ARLAN_22.": <input type='radio' name='content_comment' value='1' checked>
	".ARLAN_23.": <input type='radio' name='content_comment' value='0'>";
}

$text .= "</td>
</tr>
<tr>
<td colspan='2' class='forumheader3'>".ARLAN_24.":&nbsp;&nbsp;
";
if(strstr($_POST['data'], "{EMAILPRINT}") || $_POST['add_icons']){
	$text .= ARLAN_25.": <input type='radio' name='add_icons' value='1' checked>
	".ARLAN_26.": <input type='radio' name='add_icons' value='0'>";
}else{
	$text .= ARLAN_25.": <input type='radio' name='add_icons' value='1'>
	".ARLAN_26.": <input type='radio' name='add_icons' value='0' checked>";
}

$text .= "</td></tr>

<tr>
<td style='width:20%' class='forumheader3'>".ARLAN_55.":</td>
<td style='width:80%' class='forumheader3'>
".r_userclass("a_class",$_POST['a_class'])."
</td>
</tr>


<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";


If(IsSet($_POST['preview'])){
	$text .= "<input class='button' type='submit' name='preview' value='".ARLAN_27."' /> ";
}else{
	$text .= "<input class='button' type='submit' name='preview' value='".ARLAN_28."' /> ";
}
If(IsSet($_POST['edit']) || $edit == TRUE){
	$text .= "<input class='button' type='submit' name='update' value='".ADLAN_81." ".ARLAN_20."' />
	<input type='hidden' name='edit' value='".$_POST['edit']."'>";
	$text .= "<input type='hidden' name='content_id' value='".$_POST['content_id']."'>";

}else{
	$text .= "<input class='button' type='submit' name='submit' value='".ADLAN_85." ".ARLAN_20."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";


$ns -> tablerender("<div style='text-align:center'>".ARLAN_15."</div>", $text);

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

function article_delete(){
	if($_POST['confirm']){
		$sql = new db;
		$sql -> db_Delete("content", "content_id='".$_POST['existing']."' ");
		return ARLAN_30;
	}else{
		return ARLAN_29;
	}
}
function article_edit(){
	$aj = new textparse;
	$sql = new db;
	$sql -> db_Select("content", "*", "content_id='".$_POST['existing']."' ");
	list($_POST['content_id'], $_POST['content_heading'], $_POST['content_subheading'], $_POST['data'], $_POST['content_page'], $content_datestamp, $content_author, $_POST['content_comment'], $_POST['content_summary'], $content_type, $_POST['a_class']) = $sql-> db_Fetch();
	$_POST['content_heading'] = $aj -> editparse($_POST['content_heading']);
	$_POST['content_subheading'] = $aj -> editparse($_POST['content_subheading']);
	$_POST['data'] = $aj -> editparse($_POST['data']);
	$_POST['content_summary'] = $aj -> editparse($_POST['content_summary']);
	if($_POST['data'] = str_replace("{EMAILPRINT}", "", $_POST['data'])){ $_POST['add_icons'] = 1; }

}
function article_preserve_html($string){
	$search = array("#<#", "#>#"); 
	$replace = array("&lt;", "&gt;");
	$match_count = preg_match_all("#\[preserve\](.*?)\[/preserve\]#si", $string, $result); 
	for ($a = 0; $a < $match_count; $a++) { 
		$before_replace = $result[1][$a]; 
		$after_replace = $result[1][$a]; 
		$after_replace = preg_replace($search, $replace, $after_replace); 
		$str_to_match = "[preserve]" . $before_replace . "[/preserve]"; 
		$replacement = $code_start_html; 
		$replacement .= $after_replace; 
		$replacement .= $code_end_html; 
		$string= str_replace($str_to_match, $replacement, $string);
	}
	return $string;
}
	

?>