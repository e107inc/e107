<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/ugflag.php
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
if(!getperms("9")){ header("location:".e_BASE."index.php"); exit;}

if(IsSet($_POST['updatesettings'])){
	$aj = new textparse;
	$pref['maintainance_flag'] = $_POST['maintainance_flag'];
	$pref['maintainance_text'] = $aj -> formtpa($_POST['maintainance_text']);
	save_prefs();
	header("location:".e_SELF."?u");
	exit;
}

require_once("auth.php");

if(e_QUERY == "u"){
	$ns -> tablerender("", "<div style='text-align:center'><b>".UGFLAN_1.".</b></div>");
}

$maintainance_flag = $pref['maintainance_flag'];

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:30%' class='forumheader3'>".UGFLAN_2.": </td>
<td style='width:70%' class='forumheader3'>";


if($maintainance_flag == 1){
	$text .= "<input type='checkbox' name='maintainance_flag' value='1'  checked>";
}else{
	$text .= "<input type='checkbox' name='maintainance_flag' value='1'>";
}

$text .= "</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".UGFLAN_5."<br /><span class='smalltext'>".UGFLAN_6."</span></td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' name='maintainance_text' cols='59' rows='10'>".$pref['maintainance_text']."</textarea>
</td>
</tr>

<tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='".UGFLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".UGFLAN_4."</div>", $text);
require_once("footer.php");

?>