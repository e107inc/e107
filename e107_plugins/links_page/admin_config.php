<?php
/*
+ ----------------------------------------------------------------------------+
|    e107 website system
|
|    ©Steve Dunstan 2001-2002
|    http://e107.org
|    jalist@e107.org
|
|    Released   under the   terms and   conditions of the
|    GNU    General Public  License (http://gnu.org).
|
|    $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/admin_config.php,v $
|    $Revision: 1.1 $
|    $Date: 2005-01-22 16:13:11 $
|    $Author: sweetas $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;
if(file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")){
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
}else{
	include_once(e_PLUGIN."links_page/languages/English.php");
}
require_once(e_ADMIN."auth.php");

require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$aj = new textparse;
$linkpost = new links;

$deltest = array_flip($_POST);

if (e_QUERY) {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if (preg_match("#(.*?)_delete_(\d+)#",$deltest[$etp->unentity(LCLAN_10)],$matches)) {
	$delete = $matches[1];
	$del_id = $matches[2];
}

if (preg_match("#create_sn_(\d+)#",$deltest[$etp->unentity(LCLAN_14)],$matches)) {
	$action='create';
	$sub_action='sn';
	$id=$matches[1];
}

if (IsSet($_POST['create_category'])) {
	$_POST['link_category_name'] = $aj  -> formtpa($_POST['link_category_name'], "admin");
	$sql -> db_Insert("links_page_cat",  " '0', '".$_POST['link_category_name']."', '".$_POST['link_category_description']."', '".$_POST['link_category_icon']."'");
	$linkpost -> show_message(LCLAN_51);
}

if (IsSet($_POST['update_category'])) {
	$_POST['category_name'] = $aj -> formtpa($_POST['category_name'], "admin");
	$sql -> db_Update("links_page_cat", "link_category_name ='".$_POST['link_category_name']."', link_category_description='".$_POST['link_category_description']."', link_category_icon='".$_POST['link_category_icon']."' WHERE link_category_id='".$_POST['link_category_id']."'");
	$linkpost -> show_message(LCLAN_52);
}

if (IsSet($_POST['update_order'])) {
	foreach ($_POST['link_order'] as $order_id) {
		$tmp = explode(".", $order_id);
		$sql -> db_Update("links_page", "link_order=".$tmp[1]." WHERE link_id=".$tmp[0]);
	}
	$linkpost -> show_message(LCLAN_6);
}
echo $pref['latest'];
if (IsSet($_POST['updateoptions'])) {
	$pref['linkpage_categories'] = $_POST['linkpage_categories'];
	$pref['link_submit'] = $_POST['link_submit'];
	$pref['link_submit_class'] = $_POST['link_submit_class'];
	save_prefs();
	$linkpost -> show_message(LCLAN_1);
}

if (IsSet($_POST['add_link'])) {
	$linkpost -> submit_link($sub_action, $id);
}

if (!e_QUERY) {
	$linkpost -> show_categories($sub_action, $id);
}

if (IsSet($_POST['inc'])) {
	$qs = explode(".", $_POST['inc']);
	$linkid = $qs[0];
	$link_order = $qs[1];
	$location = $qs[2];
	$sql -> db_Update("links_page", "link_order=link_order+1 WHERE link_order='".($link_order-1)."' AND link_category='$location'");
	$sql -> db_Update("links_page", "link_order=link_order-1 WHERE link_id='$linkid' AND link_category='$location'");
}

if (IsSet($_POST['dec'])) {
	$qs = explode(".", $_POST['dec']);
	$linkid = $qs[0];
	$link_order = $qs[1];
	$location = $qs[2];
	$sql -> db_Update("links_page", "link_order=link_order-1 WHERE link_order='".($link_order+1)."' AND link_category='$location'");
	$sql -> db_Update("links_page", "link_order=link_order+1 WHERE link_id='$linkid' AND link_category='$location'");
}

if ($delete == 'main') {
	$sql -> db_Select("links_page", "link_order", "link_id='".$del_id."'");
	$row = $sql -> db_Fetch();
	$sql2 = new db;
	$sql -> db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".$id."'");
	while ($row = $sql -> db_Fetch()) {
		$sql2 -> db_Update("links_page", "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
	}
	if ($sql -> db_Delete("links_page", "link_id='".$del_id."'")) {
		$linkpost -> show_message(LCLAN_53." #".$del_id." ".LCLAN_54);
	}
}

if ($delete == 'category') {
	if ($sql -> db_Delete("links_page_cat", "link_category_id='$del_id' ")) {
		$linkpost -> show_message(LCLAN_55." #".$del_id." ".LCLAN_54);
		unset($id);
	}
}

if ($delete == 'sn') {
	if ($sql -> db_Delete("tmp", "tmp_time='$del_id' ")) {
		$linkpost -> show_message(LCLAN_77);
	}
}

if ($action == 'cat') {
	$sql -> db_Select("links_page_cat", "link_category_id");
	while ($row = $sql -> db_Fetch()) {
		if (isset($_POST['view_cat_links_'.$row['link_category_id']])) {
			header("location:".e_SELF."?main.view.".$row['link_category_id']);
			exit;
		}
		if (isset($_POST['category_edit_'.$row['link_category_id']])) {
			header("location:".e_SELF."?cat.edit.".$row['link_category_id']);
			exit;
		}
	}
	$linkpost -> show_categories($sub_action, $id);
}

if ($action == 'main') {
	$linkpost -> show_existing_items($sub_action, $id);
}

if ($action == 'sn') {
	$linkpost -> show_submitted($sub_action, $id);
}

if ($action == 'create')   {
	$linkpost -> create_link($sub_action, $id);
}

if ($action == 'opt') {
	$linkpost -> show_pref_options();
}

require_once(e_ADMIN.'footer.php');

function headerjs(){
	global $etp;
	$headerjs = "<script type=\"text/javascript\">
	function addtext(sc){
		document.getElementById('linkform').link_button.value = sc;
	}
	function addtext2(sc){
		document.getElementById('linkform').link_category_icon.value = sc;
	}
	</script>\n";

	$headerjs   .= "<script type='text/javascript'>
	function confirm_(mode, link_id){
		if (mode == 'cat') {
			return confirm(\"".$etp->unentity(LCLAN_56)." \" + link_id);
		} else if   (mode == 'sn')  {
			return confirm(\"".$etp->unentity(LCLAN_57)." \" + link_id);
		} else {
			return confirm(\"".$etp->unentity(LCLAN_58)." \" + link_id);
		}
	}
	</script>";
	return $headerjs;
}

exit;

// End ---------------------------------------------------------------------------------------------------------


class links {
	function show_existing_items($subaction, $id){
		global $sql, $rs, $ns, $aj;
		if ($sql -> db_Select("links_page_cat"))    {
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				$cat[$link_category_id] = $link_category_name;
			}
		}

		if ($link_total = $sql -> db_Select("links_page", "*", "link_category=".$id." ORDER BY link_order, link_id ASC")) {
			$text = $rs -> form_open("post", e_SELF."?".e_QUERY,"myform_{$link_id}","","");
			$text .= "<div style='text-align:center'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='fcaption' style='width:5%'>".LCLAN_89."</td>
			<td class='fcaption' style='width:70%'>".LCLAN_90."</td>
			<td class='fcaption' style='width:5%'>".LCLAN_91."</td>
			<td class='fcaption' style='width:15%'>".LCLAN_60."</td>
			<td class='fcaption' style='width:5%'>".LCLAN_97."</td>
			</tr>";
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				$text .= "<tr><td class='forumheader3' style='width:5%; text-align: center; vertical-align: middle'>";
				if ($link_button) {
					$text .= (strstr($link_button, "/") ? "<img src='".e_BASE.$link_button."' alt='".$link_name."' />" : "<img src='".e_PLUGIN."links_page/link_images/".$link_button."' alt='' />");
				} else {
					$text .= "<img src='".e_PLUGIN."links_page/images/generic.png' alt='' />";
				}
				$text .= "</td><td style='width:70%' class='forumheader3'>".$link_name."</td>";
				$text .= "</td>";
				$text .= "<td style='width:5%; text-align:center; white-space: nowrap' class='forumheader3'>";
				$text .= "<input type='image' src='".e_IMAGE."generic/up.png' value='".$link_id.".".$link_order.".".$link_category."' name='inc' />";
				$text .= "<input type='image' src='".e_IMAGE."generic/down.png' value='".$link_id.".".$link_order.".".$link_category."' name='dec' />";
				$text .= "</td>";
				$text .= "<td style='width:15%; text-align:center; white-space: nowrap' class='forumheader3'>";
				$text .= $rs -> form_button("button", "main_edit_{$link_id}", LCLAN_9, "onclick=\"document.location='".e_SELF."?create.edit.$link_id'\"");
				$text .= $rs -> form_button("submit", "main_delete_".$link_id, LCLAN_10, "onclick=\"return confirm_('create','".$link_name."')\"");
				$text .= "</td>";
				$text .="<td style='width:5%; text-align:center' class='forumheader3'>";
				$text .= "<select name='link_order[]' class='tbox'>";
				for($a=1; $a<=$link_total; $a++){
					$text .= ($row['link_order'] == $a) ? "<option value='".$row['link_id'].".".$a."' selected='selected'>".$a."</option>" : "<option value='".$row['link_id'].".".$a."'>".$a."</option>";
				}
				$text .= "</select>";
				$text .= "</td>";
				$text .= "</tr>";
			}
			$text .= "<tr>
			<td class='forumheader' colspan='4'></td>
			<td class='forumheader' style='width:5%; text-align:center'><input class='button' type='submit' name='update_order' value='".LCLAN_94."' /></td>
			</tr>";
			$text .= "</table></div>";
			$text .= $rs -> form_close();
		} else {
			$text .= "<div style='text-align:center'>".LCLAN_61."</div>";
		}
		$ns -> tablerender(LCLAN_59.": ".$cat[$id], $text);
	}

	function show_message($message){
		global $ns;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function create_link($sub_action, $id){
		global $sql, $rs, $ns;
		if ($sub_action == 'edit' && !$_POST['submit']) {
			if ($sql -> db_Select("links_page", "*", "link_id='$id' ")) {
				$row    = $sql-> db_Fetch();
				extract($row);
			}
		}

		if ($sub_action == 'sn')    {
			if ($sql -> db_Select("tmp", "*", "tmp_time='$id'")) {
				$row    = $sql-> db_Fetch();
				extract($row);
				$submitted = explode("^", $tmp_info);
				$link_category  = $submitted[0];
				$link_name = $submitted[1];
				$link_url   = $submitted[2];
				$link_description = $submitted[3]."\n[i]".LCLAN_82." ".$submitted[5]."[/i]";
				$link_button =  $submitted[4];
			}
		}

		$handle=opendir(e_PLUGIN."links_page/link_images");
		while ($file =  readdir($handle)) {
			if ($file   != "." &&   $file != ".." && $file != "/") {
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' id='linkform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_12.": </td>
		<td style='width:70%' class='forumheader3'>";

		if (!$link_cats = $sql -> db_Select("links_page_cat")) {
			$text .= LCLAN_13."<br />";
		} else {
			$text .= "<select name='cat_id' class='tbox'>";
			while (list($cat_id, $cat_name, $cat_description)   = $sql-> db_Fetch()) {
				if ($link_category == $cat_id   || ($cat_id ==  $linkid && $action == "add"))   {
					$text .= "<option value='$cat_id' selected='selected'>".$cat_name."</option>";
				} else {
					$text .= "<option value='$cat_id'>".$cat_name."</option>";
				}
			}
			$text .= "</select>";
		}
		$text .= "</td></tr><tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_15.": </td>
		<td style='width:70%' class='forumheader3'>
		<input class='tbox' type='text' name='link_name' size='60' value='$link_name' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_16.": </td>
		<td style='width:70%' class='forumheader3'>
		<input class='tbox' type='text' name='link_url' size='60' value='$link_url' maxlength='200' />
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_17.": </td>
		<td style='width:70%' class='forumheader3'>
		<textarea   class='tbox' name='link_description' cols='59' rows='3'>$link_description</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_18.": </td>
		<td style='width:70%' class='forumheader3'>
		<input class='tbox' type='text' name='link_button' size='60' value='$link_button' maxlength='100' />

		<br />
		<input class='button' type ='button' style='cursor:hand' size='30' value='".LCLAN_39."' onclick='expandit(this)' />
		<div    style='display:none;{head}'>";

		foreach ($iconlist as $key => $icon) {
			$text .= "<a href='javascript:addtext(\"$icon\")'><img src='".e_PLUGIN."links_page/link_images/".$icon."' style='border:0' alt='' /></a> ";
		}
		//if (!$iconlist[1]) {
			$text .= '<br />'.LCLAN_84;
		//}

		// 0    = same window
		// 1    = _blank
		// 2    = _parent
		// 3    = _top
		// 4    = miniwindow

		$text .= "</div></td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_19.": </td>
		<td style='width:70%' class='forumheader3'>
		<select name='linkopentype' class='tbox'>".
		($link_open ==  0 ? "<option value='0' selected='selected'>".LCLAN_20."</option>" : "<option value='0'>".LCLAN_20."</option>").
		($link_open ==  1 ? "<option value='1' selected='selected'>".LCLAN_23."</option>" : "<option value='1'>".LCLAN_23."</option>").
		($link_open ==  4 ? "<option value='4' selected='selected'>".LCLAN_24."</option>" : "<option value='4'>".LCLAN_24."</option>")."
		</select>
		</td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_25.":<br /><span class='smalltext'>(".LCLAN_26.")</span></td>
		<td style='width:70%' class='forumheader3'>".r_userclass("link_class",$link_class,"off","public,guest,nobody,member,admin,classes")."
		</td></tr>

		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id && $sub_action == "edit") {
			$text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_27."' />\n<input type='hidden' name='link_id' value='$link_id'>";
		} else {
			$text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_28."' />";
		}
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(LCLAN_29, $text);
	}

	function submit_link($sub_action, $id){
		global $aj, $sql, $e107cache;
		$link_name = $aj -> formtpa($_POST['link_name'], "admin");
		$link_url   = $aj -> formtpa($_POST['link_url'], "admin");
		$link_description = $aj -> formtpa($_POST['link_description'], "admin");
		$link_button =  $aj -> formtpa($_POST['link_button'], "admin");
		$link_t =   $sql    -> db_Count("links_page", "(*)", "WHERE link_category='".$_POST['cat_id']."'");

		if ($id && $sub_action != "sn") {
			$sql -> db_Update("links_page", "link_name='$link_name', link_url='$link_url', link_description='$link_description', link_button= '$link_button',   link_category='".$_POST['cat_id']."', link_open='".$_POST['linkopentype']."', link_class='".$_POST['link_class']."' WHERE link_id='$id'");
			$e107cache->clear("sitelinks");
			$this->show_message(LCLAN_3);
		} else {
			$sql -> db_Insert("links_page", "0, '$link_name', '$link_url', '$link_description', '$link_button', '".$_POST['cat_id']."', '".($link_t+1)."', '0', '".$_POST['linkopentype']."', '".$_POST['link_class']."'");
			$e107cache->clear("sitelinks");
			$this->show_message(LCLAN_2);
		}
		if ($sub_action == "sn") {
			$sql -> db_Delete("tmp", "tmp_time='$id'    ");
		}
	}

	function show_categories($sub_action, $id){
		global $sql, $rs, $ns, $aj;
		$handle=opendir(e_PLUGIN."links_page/cat_images");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != "..") {
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		if ($sub_action == "edit") {
			if ($sql -> db_Select("links_page_cat", "*", "link_category_id='$id' ")) {
				$row = $sql -> db_Fetch(); extract($row);
			}
		}

		$text = "<div style='text-align:center'>
		".$rs -> form_open("post", e_SELF, "linkform")."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_71."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs    -> form_text("link_category_name",  50, $link_category_name,    200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_72."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs    -> form_text("link_category_description", 60, $link_category_description, 200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_73."</span></td>
		<td class='forumheader3' style='width:70%'>
		".$rs ->    form_text("link_category_icon", 60, $link_category_icon, 100)."
		<br />
		<input class='button' type ='button' style='cursor:hand' size='30' value='".LCLAN_80."' onclick='expandit(this)' />
		<div style='display:none'>";
		
		foreach ($iconlist as $key => $icon) {
			$text .= "<a href='javascript:addtext2(\"$icon\")'><img src='".e_PLUGIN."links_page/cat_images/".$icon."' style='border:0' alt='' /></a>";
		}
		//if (!$iconlist[1]) {
			$text .= '<br />'.LCLAN_85;
		//}
	
		$text .= "</div></td>
		</tr>

		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id) {
			$text .= "<input class='button' type='submit' name='update_category' value='".LCLAN_74."'>
			".$rs   -> form_button("submit",    "category_clear", LCLAN_81).
			$rs -> form_hidden("link_category_id", $id)."
			</td></tr>";
		} else {
			$text .= "<input class='button' type='submit' name='create_category' value='".LCLAN_75."' /></td></tr>";
		}
		$text .= "</table>
		".$rs -> form_close()."
		</div>";

		$ns -> tablerender(LCLAN_100, $text);
		

		if ($category_total = $sql -> db_Select("links_page_cat")) {
			$text = "<div style='text-align: center'>";
			$text .= $rs -> form_open("post", e_SELF."?cat","","","");
			$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td style='width:5%' class='fcaption'>".LCLAN_86."</td>
			<td style='width:75%' class='fcaption'>".LCLAN_59."</td>
			<td style='width:20%' class='fcaption'>".LCLAN_60."</td>
			</tr>";
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader3'>";
				if ($link_category_icon) {
					$text .= (strstr($link_category_icon, "/") ? "<img src='".e_BASE.$link_category_icon."' alt='' style='vertical-align:middle' />" : "<img src='".e_PLUGIN."links_page/cat_images/".$link_category_icon."' alt='' style='vertical-align:middle' />");
				} else {
					$text .= "&nbsp;";
				}
				$text .= "</td>
				<td style='width:75%' class='forumheader3'>$link_category_name<br /><span class='smalltext'>$link_category_description</span></td>
				<td style='width:20%; text-align:center; white-space: nowrap' class='forumheader3'>
				".$rs -> form_button("submit", "category_edit_{$link_category_id}", LCLAN_9)."
				".$rs -> form_button("submit", "category_delete_{$link_category_id}", LCLAN_10, "onclick=\"return confirm_('cat','".$link_category_name."')\"")."
				".$rs -> form_button("submit", "view_cat_links_{$link_category_id}", LCLAN_87)."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
			$text .= $rs -> form_close();
			$text .= "</div>";
		} else {
			$text = "<div style='text-align:center'>".LCLAN_69."</div>";
		}
		$ns -> tablerender(LCLAN_70,    $text);

		unset($link_category_name, $link_category_description, $link_category_icon);


	}

	function show_submitted($sub_action, $id){
		global $sql, $rs, $ns, $aj;
		$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
		if ($submitted_total = $sql -> db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
			$text .= "<table class='fborder' style='width:99%'>
			<tr>
			<td style='width:50%' class='fcaption'>".LCLAN_53."</td>
			<td style='width:30%' class='fcaption'>".LCLAN_45."</td>
			<td style='width:20%; text-align:center' class='fcaption'>".LCLAN_60."</td>
			</tr>";
			while ($row = $sql -> db_Fetch()) {
				extract($row);
				$submitted =    explode("^", $tmp_info);
				if (!strstr($submitted[2], "http")) { $submitted[2] = "http://".$submitted[2]; }
				$text .= "<tr>
				<td style='width:50%' class='forumheader3'><a href='".$submitted[2]."' rel='external'>".$submitted[2]."</a></td>
				<td style='width:30%' class='forumheader3'>".$submitted[5]."</td>
				<td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'><div>
				".$rs -> form_open("post", e_SELF."?sn","submitted_links")."
				".$rs -> form_button("submit", "create_sn_{$tmp_time}", LCLAN_14, "onclick=\"document.location='".e_SELF."?create.sn.$tmp_time'\"")."
				".$rs -> form_button("submit", "sn_delete_{$tmp_time}", LCLAN_10, "onclick=\"confirm_('sn', $tmp_time);\"")."
				</div>".$rs -> form_close()."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
		} else {
			$text .= "<div style='text-align:center'>".LCLAN_76."</div>";
		}
		$text .=    "</div>";
		$ns -> tablerender(LCLAN_66,    $text);
	}

	function show_pref_options(){
		global $pref,   $ns;
		$text = "<div   style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:70%' class='forumheader3'>
		".LCLAN_40."<br />
		<span class='smalltext'>".LCLAN_34."</span>
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>".
		($pref['linkpage_categories'] ? "<input type='checkbox' name='linkpage_categories' value='1' checked='checked' />" : "<input type='checkbox' name='linkpage_categories' value='1' />")."
		</td>
		</tr>

		<tr>
		<td style='width:70%' class='forumheader3'>
		".LCLAN_41."<br />
		<span class='smalltext'>".LCLAN_42."</span>
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>".
		($pref['link_submit'] ? "<input type='checkbox' name='link_submit' value='1' checked='checked' />"  : "<input   type='checkbox' name='link_submit'  value='1'   />")."
		</td>
		</tr>

		<tr>
		<td style='width:70%' class='forumheader3'>
		".LCLAN_43."<br />
		<span class='smalltext'>".LCLAN_44."</span>
		</td>
		<td class='forumheader3' style='width:30%;text-align:center'>".r_userclass("link_submit_class", $pref['link_submit_class'])."</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='updateoptions' value='".LCLAN_35."' />
		</td>
		</tr>

		</table>
		</form>
		</div>";
		$ns -> tablerender(LCLAN_36,    $text);
	}
}

?>