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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/featurebox/admin_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-15 03:04:40 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
	 exit;
}

require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$',"thumb_", 'index', 'null*');
$templatelist = $fl->get_files(e_PLUGIN."featurebox/templates/","",$rejecthumb);

if (e_QUERY) {
	list($action, $id) = explode(".", e_QUERY);
}
else
{
	$action = FALSE;
	$id = FALSE;
}

if(isset($_POST['createFB']))
{
	if ($_POST['fb_title'] && $_POST['fb_text']) {
		$fb_title = $tp -> toDB($_POST['fb_title']);
		$fb_text = $tp -> toDB($_POST['fb_text']);
		$fb_mode = $_POST['fb_mode'];
		$fb_class = $_POST['fb_class'];
		$fb_rendertype = $_POST['fb_rendertype'];
		$fb_template = $_POST['fb_template'];
		$sql->db_Insert("featurebox", "0, '$fb_title', '$fb_text', '$fb_mode', '$fb_class', '$fb_rendertype', '$fb_template'");
		$message = FBLAN_15;
	} else {
		$message = FBLAN_17;
	}
}

if(isset($_POST['updateFB']))
{
	if ($_POST['fb_title'] && $_POST['fb_text']) {
		$fb_title = $tp -> toDB($_POST['fb_title']);
		$fb_text = $tp -> toDB($_POST['fb_text']);
		$fb_mode = $_POST['fb_mode'];
		$fb_class = $_POST['fb_class'];
		$fb_rendertype = $_POST['fb_rendertype'];
		$fb_template = $_POST['fb_template'];
		$sql->db_Update("featurebox", "fb_title='$fb_title', fb_text='$fb_text', fb_mode='$fb_mode', fb_class='$fb_class', fb_rendertype='$fb_rendertype', fb_template='$fb_template' WHERE fb_id=".$_POST['fb_id']);
		$message = FBLAN_16;
	} else {
		$message = FBLAN_17;
	}
}

if($action == "delete") {
	$sql->db_Delete("featurebox", "fb_id=$id");
	$message = FBLAN_18;
}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if($headline_total = $sql->db_Select("featurebox "))
{
	$nfArray = $sql -> db_getList();

	$text = "<div style='text-align:center'>
	<table class='fborder' style='".ADMIN_WIDTH.";'>
	<tr>
	<td class='forumheader' style='width: 5%; text-align: center;'>ID</td>
	<td class='forumheader' style='width: 50%;'>".FBLAN_07."</td>
	<td class='forumheader' style='width: 10%; text-align: center;'>".FBLAN_19."</td>
	</tr>\n";

	foreach($nfArray as $entry)
	{
		$text .= "
		<tr>
		<td class='forumheader' style='width: 5%; text-align: center;'>".$entry['fb_id']."</td>
		<td class='forumheader' style='width: 50%;'>".$entry['fb_title']."</td>
		<td class='forumheader' style='width: 10%; text-align: center;'>
		<a href='".e_SELF."?edit.".$entry['fb_id']."'>".FBLAN_20."</a> - <a href='".e_SELF."?delete.".$entry['fb_id']."'>".FBLAN_21."</a>
		</td>
		</tr>
		";
	}



	$text .= "</table>\n</div>";
}
else
{
	$text = FBLAN_05;
}
$ns->tablerender(FBLAN_06, $text);

if($action == "edit")
{
	if($sql->db_Select("featurebox", "*", "fb_id=$id"))
	{
		$row = $sql->db_Fetch();
		extract($row);
	}
}
else
{
	unset($fb_name, $fb_text, $fb_mode, $fb_class);
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='".ADMIN_WIDTH."' class='fborder'>

<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_07."</td>
<td style='width:50%; text-align: left;' class='forumheader3'>
<input class='tbox' type='text' name='fb_title' style='width: 80%' value='$fb_title' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_08."</td>
<td style='width:50%; text-align: left;' class='forumheader3'>
<textarea class='tbox' name='fb_text' style='width: 90%'  rows='6'>$fb_text</textarea>
</td>
</tr>
	 
<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_09."</td>
<td style='width:50%; text-align: left;' class='forumheader3'>
".r_userclass("fb_class", $fb_class, "public, guests, nobody, member, admin, classes")."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_12."</td>
<td style='width:50%; text-align: left;' class='forumheader3'>
<input type='radio' name='fb_mode' value='0'".(!$fb_mode ? " checked='checked'" : "")." /> ".FBLAN_13."&nbsp;<br />
<input type='radio' name='fb_mode' value='1'".($fb_mode == 1 ? " checked='checked'" : "")." /> ".FBLAN_14."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_22."</td>
<td style='width:50%; text-align: left;' class='forumheader3'>
<input type='radio' name='fb_rendertype' value='0'".(!$fb_rendertype ? " checked='checked'" : "")." /> ".FBLAN_23."&nbsp;<br />
<input type='radio' name='fb_rendertype' value='1'".($fb_rendertype == 1 ? " checked='checked'" : "")." /> ".FBLAN_24."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".FBLAN_25."<br /><span class='smalltext'>".FBLAN_26."</span></td>
<td style='width:50%; text-align: left;' class='forumheader3'>
<select class='tbox' name='fb_template'>
";

foreach($templatelist as $value)
{
	$file = str_replace(".php", "", $value['fname']);
	$text .= "<option".($file == $fb_template ? " selected='selected'" : "").">$file</option>\n";
}

$text .= "</select>
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='".($action == "edit" ? "updateFB" : "createFB")."' value='".($action == "edit" ? FBLAN_11 : FBLAN_10)."' />
</td>
</tr>
	 
</table>
".($action == "edit" ? "<input type='hidden' name='fb_id' value='$fb_id' />" : "")."
</form>
</div>";

$caption = ($action == "edit" ? FBLAN_11 : FBLAN_10);

$ns->tablerender($caption, $text);

require_once(e_ADMIN."footer.php");
?>