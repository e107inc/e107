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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/fader_menu/admin_config.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:52:48 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
require_once("../../class2.php");
$lan_file = e_PLUGIN."fader_menu/languages/".e_LANGUAGE.".php";
if (file_exists($lan_file)) {
	require_once($lan_file);
} else {
	require_once(e_PLUGIN."fader_menu/languages/English.php");
}
if (!getperms("4")) {
	header("location:".e_BASE."index.php");
	 exit ;
}
require_once(e_ADMIN."auth.php");
	
if (isset($_POST['update_menu'])) {
	$aj = new textparse;
	$_POST['fader_height'] = ($_POST['fader_height'] ? $_POST['fader_height'] : 200);
	$_POST['fader_delay'] = ($_POST['fader_delay'] ? $_POST['fader_delay'] : 3000);
	while (list($key, $value) = each($_POST)) {
		if ($value != FADER_L15) {
			$menu_pref[$key] = str_replace("<br />", "", $aj->formtpa($value, "admin"));
		}
	}
	 
	$tmp = addslashes(serialize($menu_pref));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns->tablerender("", "<div style='text-align:center'><b>".FADER_L16."</b></div>");
}
	
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?".e_QUERY."' name='menu_conf_form'>
	<table style='width:85%' class='fborder' >
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".FADER_L6.": </td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='text' name='fader_caption' size='50' value='".$menu_pref['fader_caption']."' maxlength='100' />
	</td>
	</tr>
	";
	
for($a = 1; $a <= 10; $a++) {
	$var = "fader_message_$a";
	$text .= "<tr>
		<td style='width:30%' class='forumheader3'>".FADER_L7." ".$a." : </td>
		<td style='width:70%' class='forumheader3'>
		<textarea class='tbox' name='$var' cols='70' rows='4'>".$menu_pref[$var]."</textarea>
		</td>
		</tr>\n\n";
}
	
$text .= "
	 
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".FADER_L8.": </td>
	<td style='width:70%' class='forumheader3'>". ($menu_pref['fader_colour'] ? "<input name='fader_colour' type='radio' value='0'>".FADER_L9."&nbsp;&nbsp;<input name='fader_colour' type='radio' value='1' checked>".FADER_L10."" : "<input name='fader_colour' type='radio' value='0' checked>".FADER_L9."&nbsp;&nbsp;<input name='fader_colour' type='radio' value='1'>".FADER_L10."")."
	</td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".FADER_L11.": <br /><span class='smalltext'>".FADER_L12.": 200</td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='text' name='fader_height' size='10' value='".$menu_pref['fader_height']."' maxlength='3' />
	</td>
	</tr>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".FADER_L13.": <br /><span class='smalltext'>".FADER_L12.": 3000</td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='text' name='fader_delay' size='10' value='".$menu_pref['fader_delay']."' maxlength='4' />
	</td>
	</tr>
	 
	 
	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".FADER_L15."' /></td>
	</tr>
	</table>
	</form>
	</div>";
$ns->tablerender(FADER_L14, $text);
	
require_once(e_ADMIN."footer.php");
	
//        <input class='tbox' type='text' name='$var' size='80' value='".$menu_pref[$var]."' maxlength='300' />
	
?>