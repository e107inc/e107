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
if(IsSet($_POST['wmsubmit'])){

	$guestmessage = $aj -> tp($_POST['guestmessage'], $mode = "on");
	$membermessage = $aj -> tp($_POST['membermessage'], $mode = "on");
	$adminmessage = $aj -> tp($_POST['adminmessage'], $mode = "on");
	$sql -> db_Update("wmessage", "wm_text ='$guestmessage', wm_active='".$_POST['wm_active1']."' WHERE wm_id='1' ");
	$sql -> db_Update("wmessage", "wm_text ='$membermessage', wm_active='".$_POST['wm_active2']."' WHERE wm_id='2' ");
	$sql -> db_Update("wmessage", "wm_text ='$adminmessage', wm_active='".$_POST['wm_active3']."' WHERE wm_id='3' ");
}




/*
	if($_POST['message'] != "" || $_POST['wm_active1'] == 0){
		
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
*/


if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

$sql -> db_Select("wmessage");
list($id, $guestmessage, $wm_active1) = $sql-> db_Fetch();
list($id, $membermessage, $wm_active2) = $sql-> db_Fetch();	
list($id, $adminmessage, $wm_active3) = $sql-> db_Fetch();


$guestmessage = $aj -> editparse($guestmessage);
$membermessage = $aj -> editparse($membermessage);
$adminmessage = $aj -> editparse($adminmessage);

$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"  name=\"wmform\">

<table style=\"width:95%\">
<tr>";

/*
<td style=\"width:20%\">Activate?: </td>
<td style=\"width:60%\">";

if($wm_active == 1){
	$text .= "Yes : <input type=\"radio\" name=\"wm_active\" checked=\"true\" onclick=\"this.form.message.disabled=0\">No :<input type=\"radio\" name=\"wm_active\" onclick=\"this.form.message.disabled=1\">";
}else{
	$text .= "Yes : <input type=\"radio\" name=\"wm_active\" onclick=\"this.form.message.disabled=0\">No :<input type=\"radio\" name=\"wm_active\" checked=\"true\" onclick=\"this.form.message.disabled=1\">";
}
*/
$text .= "

<td style=\"width:20%\">Message for Guests: <br />
Activate?:";
if($wm_active1){
	$text .= "<input type=\"checkbox\" name=\"wm_active1\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"wm_active1\" value=\"1\">";
}
$text .= "</td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"guestmessage\" cols=\"70\" rows=\"10\">$guestmessage</textarea>
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
<td style=\"width:20%\">Message for Members: <br />
Activate?:";
if($wm_active2){
	$text .= "<input type=\"checkbox\" name=\"wm_active2\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"wm_active2\" value=\"1\">";
}
$text .= "</td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"membermessage\" cols=\"70\" rows=\"10\">$membermessage</textarea>
<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext2('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext2('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext2('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext2('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext2('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext2('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext2('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext2('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"addtext2('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>


<tr>
<td style=\"width:20%\">Message for Administrators: <br />
Activate?: ";

if($wm_active3){
	$text .= "<input type=\"checkbox\" name=\"wm_active3\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"wm_active3\" value=\"1\">";
}

$text .= "</td>
<td style=\"width:60%\">
<textarea class=\"tbox\" name=\"adminmessage\" cols=\"70\" rows=\"10\">$adminmessage</textarea>
<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext3('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext3('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext3('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext3('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext3('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext3('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext3('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext3('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"addtext3('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>

</tr>







<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:60%\">
<input class=\"button\" type=\"submit\" name=\"wmsubmit\" value=\"Submit\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Set Welcome Message", $text);

?>
<script type="text/javascript">
function addtext(sc){
	document.wmform.guestmessage.value += sc;
}
function addtext2(sc){
	document.wmform.membermessage.value += sc;
}
function addtext3(sc){
	document.wmform.adminmessage.value += sc;
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