<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/links.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
|	16/02/2003
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("I")){ header("location:".e_HTTP."index.php"); exit; }
require_once("auth.php");
require_once(e_BASE."classes/ren_help.php");
$aj = new textparse;

if(IsSet($_POST['add_menu'])){

	if(!$_POST['menu_name'] || !$_POST['menu_caption'] || !$_POST['menu_text']){
		$message = "You left required fields blank";
	}else{
		
		$caption = 
		$menu_caption = $aj -> tp($_POST['menu_caption'], $mode="on", 0);
		$menu_text = $aj -> tp($_POST['menu_text'], $mode="on", 0);
		$menu_text = stripslashes(nl2br(str_replace("\"", "'", $menu_text)));

		$data = chr(60)."?php\n".
chr(47)."*\n+---------------------------------------------------------------+\n|	e107 website system\n|	/menus/custom_".$_POST['menu_name'].".php\n|\n|	©Steve Dunstan 2001-2002\n|	http://e107.org\n|	jalist@e107.org\n|\n|	Released under the terms and conditions of the\n|	GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated admin/custommenu.php.\n\n*".
chr(47)."\n\n".
chr(36)."caption = ".chr(34).$menu_caption.chr(34).";\n".
chr(36)."text = ".chr(34).$menu_text.chr(34).";\n".
chr(36)."ns -> tablerender(".chr(36)."caption, ".chr(36)."text);\n?".chr(62);

		$fp = @fopen("../menus/custom_".$_POST['menu_name'].".php","w");
		if(!@fwrite($fp, $data)){
			$message = "Unable to create custom menu - pleas ensure your menus directory is CHMODDed to 777.";
		}else{
			fclose($fp);
			$message = "Custom menu successfully created. To activate go to your <a href=\"menus.php\">menus screen</a>.";
			unset($_POST['menu_name'], $_POST['menu_caption'], $_POST['menu_text']);
		}
	}
}

if(IsSet($_POST['preview'])){
	$_POST['menu_name'] = stripslashes($_POST['menu_name']);
	$_POST['menu_caption'] = stripslashes($aj -> tp($_POST['menu_caption'], $mode="on", 0));
	$_POST['menu_text'] = stripslashes($aj -> tp($_POST['menu_text'], $mode="on", 0));
	echo "<div style=\"text-align:center\">
	<table style=\"width:200px\">
	<tr>
	<td>";
	$ns -> tablerender($_POST['menu_caption'], nl2br($_POST['menu_text']));
	echo "</td></tr></table></div><br /><br />";

	$_POST['menu_caption'] = $aj -> editparse($_POST['menu_caption']);
	$_POST['menu_text'] = $aj -> editparse($_POST['menu_text']);

}

if(IsSet($_POST['edit'])){
	$menu = "../menus/".$_POST['existing'];
	if($fp = @fopen($menu,"r")){
		$buffer = str_replace("\n", "", fread($fp, filesize($menu)));
		fclose($fp);

		preg_match_all("/\"(.*?)\"/", $buffer, $result);

		$result[1][1] = str_replace("'", "\"", $result[1][1]);
		$_POST['menu_caption'] = stripslashes($aj -> editparse($result[1][0]));
		$_POST['menu_text'] = stripslashes($aj -> editparse($result[1][1]));
		$_POST['menu_name'] = eregi_replace("../menus/custom_|.php", "", $menu);
	}else{
		$message = "Unable to open menu '".$_POST['existing']."' for reading";
	}
} 

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\" name=\"menupostform\">
<table style=\"width:85%\" class='fborder'>
<tr>

<td style=\"text-align:center\" colspan=\"2\" class='forumheader'><span class=\"defaulttext\">Existing Menus:</span> 
<select name=\"existing\" class=\"tbox\">";

$handle=opendir(e_BASE."menus/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && eregi("custom_", $file)){
		$text .= "<option>".$file."</option>";
	}
}
closedir($handle);

$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
</td>
</tr>


<tr>
<td style=\"width:30%\" class='forumheader3'>Menu Filename: </td>
<td style=\"width:70%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"menu_name\" value=\"".$_POST['menu_name']."\">
</td>
</tr>

<tr>
<td style=\"width:30%\" class='forumheader3'>Menu Caption Title: </td>
<td style=\"width:70%\" class='forumheader3'>
<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"25\" name=\"menu_caption\" value=\"".$_POST['menu_caption']."\">
</td>
</tr>

<tr>
<td style=\"width:30%\" class='forumheader3'>Menu Text: </td>
<td style=\"width:70%\" class='forumheader3'>
<textarea class=\"tbox\" name=\"menu_text\" cols=\"59\" rows=\"10\">".$_POST['menu_text']."</textarea>
</td>
</tr>

<tr>
<td class='forumheader3'>&nbsp;</td>
<td class='forumheader3'>
<input class=\"helpbox\" type=\"text\" name=\"helpb\" size=\"100\" />
<br />";
$text .= ren_help("addtext");

$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\" class='forumheader'>";
if(IsSet($_POST['preview'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview Again\" /> ";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview\" /> ";
}

if(IsSet($_POST['edit'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"update_menu\" value=\"Update Custom Menu\" />";
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"add_menu\" value=\"Create Custom Menu\" />";
}
$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Custom Menus</div>", $text);





?>
<script type="text/javascript">
function addtext(sc){
	document.menupostform.menu_text.value += sc;
}
function fclear(){
	document.menupostform.menu_text.value = "";
}
function help(help){
	document.menupostform.helpb.value = help;
}
</script>
<?php
require_once("footer.php");
?>	