<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/menu_conf/articles_conf.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
|	Based on code by Edwin van der Wal (evdwal@xs4all.nl)
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit ;}
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['update_menu'])){
	$aj = new textparse;
	$_POST['fader_height'] = ($_POST['fader_height'] ? $_POST['fader_height'] : 200);
	$_POST['fader_delay'] = ($_POST['fader_delay'] ? $_POST['fader_delay'] : 3000);
	while(list($key, $value) = each($_POST)){
		if($value != "Update Menu Settings"){ 
			$menu_pref[$key] = str_replace("<br />", "", $aj -> formtpa($value, "admin")); 
		}
	}

	$tmp = addslashes(serialize($menu_pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns -> tablerender("", "<div style='text-align:center'><b>Fader menu configuration saved</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
<table style='width:85%' class='fborder' >

<tr>
<td style='width:30%' class='forumheader3'>Caption: </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='fader_caption' size='50' value='".$menu_pref['fader_caption']."' maxlength='100' />
</td>
</tr>
";

for($a=1; $a<=10; $a++){
	$var = "fader_message_$a";
	$text .= "<tr>
<td style='width:30%' class='forumheader3'>Message $a: </td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' name='$var' cols='70' rows='4'>".$menu_pref[$var]."</textarea>
</td>
</tr>\n\n";
}

$text .= "


<tr>
<td style='width:30%' class='forumheader3'>Fade Colour: </td>
<td style='width:70%' class='forumheader3'>".
($menu_pref['fader_colour'] ? "<input name='fader_colour' type='radio' value='0'>Black to white&nbsp;&nbsp;<input name='fader_colour' type='radio' value='1' checked>White to black" : "<input name='fader_colour' type='radio' value='0' checked>Black to white&nbsp;&nbsp;<input name='fader_colour' type='radio' value='1'>White to black")."
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Default layer height in pixels: <br /><span class='smalltext'>default: 200</td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='fader_height' size='10' value='".$menu_pref['fader_height']."' maxlength='3' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>Fade delay in milliseconds: <br /><span class='smalltext'>default: 3000</td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='fader_delay' size='10' value='".$menu_pref['fader_delay']."' maxlength='4' />
</td>
</tr>


<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='Update Menu Settings' /></td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("Fader Menu Configuration", $text);

require_once(e_ADMIN."footer.php");

//	<input class='tbox' type='text' name='$var' size='80' value='".$menu_pref[$var]."' maxlength='300' />

?>