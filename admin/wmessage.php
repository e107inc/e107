<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/banlist.php														|
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
if(!getperms("M")){ header("location:../index.php"); }
require_once("auth.php");
$aj = new textparse;
if(IsSet($_POST['submit'])){
	if($_POST['message'] != "" || $_POST['wm_active'] == 0){
		
		$message = $aj -> tp($_POST['message'], $mode = "on");

		if($sql -> db_Select("wmessage")){
			$sql -> db_Update("wmessage", "wm_text ='$message', wm_active='".$_POST['wm_active']."' ");
		}else{
			$sql -> db_Insert("wmessage", " '$message', '".$_POST['wm_active']."' ");
		}
		$message = "Welcome message set";
		if($_POST['wm_active']){
			$message .= " and made active.";
		}else{
			$message .= ".";
		}
	}else{
		$message = "Field left blank";
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$sql -> db_Select("wmessage");
list($wm_text, $wm_active) = $sql-> db_Fetch();


$wm_text = $aj -> editparse($wm_text);


$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"  name=\"wmform\">\n
<table style=\"width:95%\">";
$text .= "<tr> 
<td style=\"width:20%\">Message: </td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"message\" cols=\"70\" rows=\"10\">$wm_text</textarea>
<br />
<input class=\"helpbox\" type=\"text\" name=\"helpb\" size=\"100\" />
<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"addtext('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>
</tr>

<tr>
<td style=\"width:20%\">Activate?: </td>
<td style=\"width:80%\">";
if($wm_active == 1){
	$text .= "<input type=\"checkbox\" name=\"wm_active\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"wm_active\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Submit\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Set Welcome Message", $text);

?>
<script type="text/javascript">
function addtext(sc){
	document.wmform.message.value += sc;
}
function fclear(){
	document.newspostform.message.value = "";
}
function help(help){
	document.wmform.helpb.value = help;
}
</script>
<?php


require_once("footer.php");
?>	