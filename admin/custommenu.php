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
if(!getperms("I")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");
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
			
		//echo "result1: '".stripslashes($result[1][0])."'<br />result2: '".$result[1][1]."'";

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
<table style=\"width:95%\">
<tr>

<td style=\"text-align:center\" colspan=\"2\">Existing Menus: 
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
<br /><br />
</td>
</tr>


<tr>
<td style=\"width:30%\">Menu Filename: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" size=\"30\" maxlength=\"25\" name=\"menu_name\" value=\"".$_POST['menu_name']."\">
</td>
</tr>

<tr>
<td style=\"width:30%\">Menu Caption Title: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" size=\"60\" maxlength=\"25\" name=\"menu_caption\" value=\"".$_POST['menu_caption']."\">
</td>
</tr>

<tr>
<td style=\"width:30%\">Menu Text: </td>
<td style=\"width:70%\">
<textarea class=\"tbox\" name=\"menu_text\" cols=\"59\" rows=\"10\">".$_POST['menu_text']."</textarea>
</td>
</tr>

<tr>
<td></td>
<td>
<input class=\"helpbox\" type=\"text\" name=\"helpb\" size=\"100\" />";
$text .= ren_help("addtext");

$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\"><br />";
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
</form>";

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

function ren_help($func){
	$str ="<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"$func('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"$func('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"$func('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"$func('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"$func('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"$func('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"$func('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"$func('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"$func('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"$func('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">";	
	return $str;
}

require_once("footer.php");
?>	