<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/banner_menu/config.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-12 10:57:08 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("1")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

@include_once(e_PLUGIN."banner_menu/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."banner_menu/languages/English.php");

$lan_file = e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php";
include(file_exists($lan_file) ? $lan_file : e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_menus.php");

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if(IsSet($_POST['update_menu'])){
	foreach($_POST as $k => $v){
		if(preg_match("#^banner_#",$k)){
			$menu_pref[$k] = $v;
		}
	}

	if($_POST['catid']){
		$array_cat = explode("-", $_POST['catid']);
		for($i=0;$i<count($array_cat);$i++){
			$cat .= $array_cat[$i]."|";
		}
		$cat = substr($cat,0,-1);
		$menu_pref['banner_campaign'] = $cat;
	}

	$sysprefs->setArray('menu_pref');
	$ns -> tablerender("", "<div style='text-align:center'><b>".BANNER_MENU_L2."</b></div>");
}

if(!$menu_pref['banner_caption']){
        $menu_pref['banner2_caption'] = BANNER_MENU_L1;
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L3.": </td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='banner_caption' size='20' value='".$menu_pref['banner_caption']."' maxlength='100' />
</td>
</tr>";

$array_cat_in = explode("|", $menu_pref['banner_campaign']);

$c=0; $d=0;
$sql2 = new db;
$category_total = $sql2 -> db_Select("banner", "DISTINCT(SUBSTRING_INDEX(banner_campaign, '^', 1)) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
while($row = $sql2 -> db_Fetch()){
extract($row);
	if(in_array($banner_campaign, $array_cat_in)){
		$in_catname[$c] = $banner_campaign;
		$c++;
	}else{
		$out_catname[$d] = $banner_campaign;
		$d++;
	}
}

$text .= "
<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L6."</td>
<td style='width:60%' class='forumheader3'>

	<table style='width:90%'>
	<tr>
	<td style='width:45%; vertical-align:top'>".BANNER_MENU_L7."<br />
	<select class='tbox' id='catout' name='catout' size='10' style='width:180px' multiple='multiple' onchange='moveOver();'>";
	for($a=0; $a<=($d-1); $a++){
		$text .= "<option value='".$out_catname[$a]."'>".$out_catname[$a]."</option>";
	}
	$text .= "</select>
	</td>
	<td style='width:45%; vertical-align:top'>".BANNER_MENU_L8."<br />
	<select class='tbox' id='catin' name='catin' size='10' style='width:180px' multiple='multiple'>";
	for($a=0; $a<=($c-1); $a++){
		$catidvalues .= $in_catname[$a]."-";
		$text .= "<option value='".$in_catname[$a]."'>".$in_catname[$a]."</option>";
	}
	$catidvalues = substr($catidvalues,0,-1);
	$text .= "</select><br /><br />
	<input class='button' type='button' value='".BANNER_MENU_L9."' onclick='removeMe();' />
	<input type='hidden' name='catid' id='catid' value='".$catidvalues."'>
	</td>
	</tr>
	</table>

</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L10."</td>
<td style='width:60%' class='forumheader3'>
	<select class='tbox' id='banner_rendertype' name='banner_rendertype' size='1'  >
	".$rs -> form_option(BANNER_MENU_L11, (!$menu_pref['banner_rendertype'] || $menu_pref['banner_rendertype'] == "0" ? "1" : "0"), 0)."
	".$rs -> form_option("1 - ".BANNER_MENU_L12."", ($menu_pref['banner_rendertype'] == "1" ? "1" : "0"), 1)."
	".$rs -> form_option("2 - ".BANNER_MENU_L13."", ($menu_pref['banner_rendertype'] == "2" ? "1" : "0"), 2)."
	".$rs -> form_option("3 - ".BANNER_MENU_L14."", ($menu_pref['banner_rendertype'] == "3" ? "1" : "0"), 3)."
	".$rs -> form_select_close()."
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".BANNER_MENU_L15."<br /><span class='smalltext' style='font-style:italic;'>".BANNER_MENU_L16."</span></td>
<td style='width:60%' class='forumheader3'>
	<select class='tbox' id='banner_amount' name='banner_amount' size='1'  >
	".$rs -> form_option(BANNER_MENU_L17, (!$menu_pref['banner_amount'] ? "1" : "0"), 0);
	for($b=1; $b<6; $b++){
		$text .= $rs -> form_option($b, ($menu_pref['banner_amount'] == $b ? "1" : "0"), $b);
	}
	$text .= $rs -> form_select_close()."
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".BANNER_MENU_L18."' /></td>
</tr>

</table>
</form>
</div>";

$ns -> tablerender(BANNER_MENU_L5, $text);


require_once(e_ADMIN."footer.php");

function headerjs(){

$script_js= "<script type=\"text/javascript\">
//<!--
//<!-- Adapted from original:  Kathi O'Shea (Kathi.O'Shea@internet.com) -->

function moveOver(){
	var boxLength = document.getElementById('catin').length;
	var selectedItem = document.getElementById('catout').selectedIndex;
	var selectedText = document.getElementById('catout').options[selectedItem].text;
	var selectedValue = document.getElementById('catout').options[selectedItem].value;

	var i;
	var isNew = true;
	if (boxLength != 0) {
		for (i = 0; i < boxLength; i++) {
			thisitem = document.getElementById('catin').options[i].text;
			if (thisitem == selectedText) {
				isNew = false;
				break;
			}
		}
	}
	if (isNew) {
		newoption = new Option(selectedText, selectedValue, false, false);
		document.getElementById('catin').options[boxLength] = newoption;
		document.getElementById('catout').options[selectedItem].text = '';
	}
	document.getElementById('catout').selectedIndex=-1;
	
	saveMe();
}

function removeMe() {
	var boxLength = document.getElementById('catin').length;
	var boxLength2 = document.getElementById('catout').length;
	arrSelected = new Array();
	var count = 0;
	for (i = 0; i < boxLength; i++) {
		if (document.getElementById('catin').options[i].selected) {
			arrSelected[count] = document.getElementById('catin').options[i].value;
			var valname = document.getElementById('catin').options[i].text;
			for (j = 0; j < boxLength2; j++) {
				if(document.getElementById('catout').options[j].value == arrSelected[count]){
					document.getElementById('catout').options[j].text = valname;
				}
			}
		}
		count++;
	}
	var x;
	for (i = 0; i < boxLength; i++) {
		for (x = 0; x < arrSelected.length; x++) {
			if (document.getElementById('catin').options[i].value == arrSelected[x]) {
				document.getElementById('catin').options[i] = null;
			}
		}
		boxLength = document.getElementById('catin').length;
	}

	saveMe();
}

//function clearMe(clid){
//	location.href = document.location + \"?clear.\" + clid;
//}

function saveMe(clid) {
	var strValues = \"\";
	var boxLength = document.getElementById('catin').length;
	var count = 0;
	if (boxLength != 0) {
		for (i = 0; i < boxLength; i++) {
			if (count == 0) {
				strValues = document.getElementById('catin').options[i].value;
			}
			else {
				strValues = strValues + \"-\" + document.getElementById('catin').options[i].value;
			}
			count++;
		}
	}
	if (strValues.length == 0) {
		//alert(\"You have not made any selections\");
		document.getElementById('catid').value = \"\";
	}
	else {
		document.getElementById('catid').value = strValues;
	}
}

// -->
</script>\n";
 return $script_js;
}

?>