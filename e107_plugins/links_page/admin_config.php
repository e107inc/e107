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
|    $Revision: 1.10 $
|    $Date: 2005-06-16 09:41:59 $
|    $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
}

if (file_exists(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php")) {
	include_once(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");
} else {
	include_once(e_PLUGIN."links_page/languages/English.php");
}
require_once(e_ADMIN."auth.php");

require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_HANDLER."file_class.php");
$fl = new e_file;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass();

$linkspage_pref = $lc -> getLinksPagePref();

$linkpost = new links;

$deltest = array_flip($_POST);

if (e_QUERY) {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = (isset($tmp[1]) ? $tmp[1] : "");
	$id = (isset($tmp[2]) ? $tmp[2] : "");
	unset($tmp);
}

if (preg_match("#(.*?)_delete_(\d+)#", $deltest[$tp->toJS(LCLAN_10)], $matches)) {
	$delete = $matches[1];
	$del_id = $matches[2];
}

if (preg_match("#create_sn_(\d+)#", $deltest[$tp->toJS(LCLAN_14)], $matches)) {
	$action = 'create';
	$sub_action = 'sn';
	$id = $matches[1];
}

if (isset($_POST['create_category'])) {
	$_POST['link_category_name'] = $tp->toDB($_POST['link_category_name'], "admin");
	$sql->db_Insert("links_page_cat", " '0', '".$_POST['link_category_name']."', '".$_POST['link_category_description']."', '".$_POST['link_category_icon']."'");
	$linkpost->show_message(LCLAN_51);
}

if (isset($_POST['update_category'])) {
	$_POST['category_name'] = $tp->toDB($_POST['category_name'], "admin");
	$sql->db_Update("links_page_cat", "link_category_name ='".$_POST['link_category_name']."', link_category_description='".$_POST['link_category_description']."', link_category_icon='".$_POST['link_category_icon']."' WHERE link_category_id='".$_POST['link_category_id']."'");
	$linkpost->show_message(LCLAN_52);
}

if (isset($_POST['update_order'])) {
	foreach ($_POST['link_order'] as $order_id) {
		$tmp = explode(".", $order_id);
		$sql->db_Update("links_page", "link_order=".$tmp[1]." WHERE link_id=".$tmp[0]);
	}
	$linkpost->show_message(LCLAN_6);
}

if (isset($_POST['updateoptions'])) {
	$linkspage_pref = $lc -> UpdateLinksPagePref($_POST);
	$linkpost->show_message(LCLAN_1);
}

if (isset($_POST['add_link'])) {
	$linkpost->submit_link($sub_action, $id);
}

if(isset($_POST['uploadlinkicon'])){

	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");
	$pathicon = e_PLUGIN."links_page/link_images/";
	$uploaded = file_upload($pathicon);
	
	$icon = "";
	if($uploaded) {
		$icon = $uploaded[0]['name'];
		if($_POST['link_resize_value']){
			require_once(e_HANDLER."resize_handler.php");
			resize_image($pathicon.$icon, $pathicon.$icon, $_POST['link_resize_value'], "nocopy");
		}
	}
	$msg = "<div style='font-weight:bold; text-align:center;'>".($icon ? LCLAN_107 : LCLAN_108)."</div>";
	$ns->tablerender("", $msg);
}

if(isset($_POST['uploadcatlinkicon'])){

	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");
	$pathicon = e_PLUGIN."links_page/cat_images/";
	$uploaded = file_upload($pathicon);
	
	$icon = "";
	if($uploaded) {
		$icon = $uploaded[0]['name'];
		if($_POST['link_cat_resize_value']){
			require_once(e_HANDLER."resize_handler.php");
			resize_image($pathicon.$icon, $pathicon.$icon, $_POST['link_cat_resize_value'], "nocopy");
		}
	}
	$msg = "<div style='font-weight:bold; text-align:center;'>".($icon ? LCLAN_107 : LCLAN_108)."</div>";
	$ns->tablerender("", $msg);
}

if (!e_QUERY) {
	//$linkpost->show_categories($sub_action, $id);
	$linkpost->show_categories("", "");
}

if (isset($_POST['inc'])) {
	$qs = explode(".", $_POST['inc']);
	$linkid = $qs[0];
	$link_order = $qs[1];
	$location = $qs[2];
	$sql->db_Update("links_page", "link_order=link_order+1 WHERE link_order='".($link_order-1)."' AND link_category='$location'");
	$sql->db_Update("links_page", "link_order=link_order-1 WHERE link_id='$linkid' AND link_category='$location'");
}

if (isset($_POST['dec'])) {
	$qs = explode(".", $_POST['dec']);
	$linkid = $qs[0];
	$link_order = $qs[1];
	$location = $qs[2];
	$sql->db_Update("links_page", "link_order=link_order-1 WHERE link_order='".($link_order+1)."' AND link_category='$location'");
	$sql->db_Update("links_page", "link_order=link_order+1 WHERE link_id='$linkid' AND link_category='$location'");
}

if (isset($delete) && $delete == 'main') {
	$sql->db_Select("links_page", "link_order", "link_id='".$del_id."'");
	$row = $sql->db_Fetch();
	$sql2 = new db;
	$sql->db_Select("links_page", "link_id", "link_order>'".$row['link_order']."' && link_category='".$id."'");
	while ($row = $sql->db_Fetch()) {
		$sql2->db_Update("links_page", "link_order=link_order-1 WHERE link_id='".$row['link_id']."'");
	}
	if ($sql->db_Delete("links_page", "link_id='".$del_id."'")) {
		$linkpost->show_message(LCLAN_53." #".$del_id." ".LCLAN_54);
	}
}

if (isset($delete) && $delete == 'category') {
	if ($sql->db_Delete("links_page_cat", "link_category_id='$del_id' ")) {
		$linkpost->show_message(LCLAN_55." #".$del_id." ".LCLAN_54);
		unset($id);
	}
}

if (isset($delete) && $delete == 'sn') {
	if ($sql->db_Delete("tmp", "tmp_time='$del_id' ")) {
		$linkpost->show_message(LCLAN_77);
	}
}

if (isset($action) && $action == 'cat') {
	$sql->db_Select("links_page_cat", "link_category_id");
	while ($row = $sql->db_Fetch()) {
		if (isset($_POST['view_cat_links_'.$row['link_category_id']]))
		{
			echo "<script type='text/javascript'>document.location.href='".e_SELF."?main.view.".$row['link_category_id']."'</script>n";
			exit;
		}
		if (isset($_POST['category_edit_'.$row['link_category_id']]))
		{
			echo "<script type='text/javascript'>document.location.href='".e_SELF."?cat.edit.".$row['link_category_id']."'</script>n";
			exit;
		}
	}
	$linkpost->show_categories($sub_action, $id);
}

if (isset($action) && $action == 'main') {
	$linkpost->show_existing_items($sub_action, $id);
}

if (isset($action) && $action == 'sn') {
	$linkpost->show_submitted($sub_action, $id);
}

if (isset($action) && $action == 'create') {
	$linkpost->create_link($sub_action, $id);
}

if (isset($action) && $action == 'opt') {
	$linkpost->show_pref_options();
}

require_once(e_ADMIN.'footer.php');
exit;

// End ---------------------------------------------------------------------------------------------------------


class links {

	function show_existing_items($subaction, $id) {
		global $sql, $rs, $ns, $tp;
		if ($sql->db_Select("links_page_cat")) {
			while ($row = $sql->db_Fetch()) {
				$cat[$row['link_category_id']] = $row['link_category_name'];
			}
		}

		if ($link_total = $sql->db_Select("links_page", "*", "link_category=".$id." ORDER BY link_order, link_id ASC")) {
			$text = $rs->form_open("post", e_SELF."?".e_QUERY, "myform_{$row['link_id']}", "", "");
			$text .= "<div style='text-align:center'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td class='fcaption' style='width:5%'>".LCLAN_89."</td>
			<td class='fcaption' style='width:70%'>".LCLAN_90."</td>
			<td class='fcaption' style='width:5%'>".LCLAN_91."</td>
			<td class='fcaption' style='width:15%'>".LCLAN_60."</td>
			<td class='fcaption' style='width:5%'>".LCLAN_97."</td>
			</tr>";
			while ($row = $sql->db_Fetch()) {
				$linkid = $row['link_id'];
				$text .= "
				<tr>
				<td class='forumheader3' style='width:5%; text-align: center; vertical-align: middle'>";
				if ($row['link_button']) {
					$text .= (strstr($row['link_button'], "/") ? "<img src='".e_BASE.$row['link_button']."' alt='".$row['link_name']."' />" : "<img src='".e_PLUGIN."links_page/link_images/".$row['link_button']."' alt='' />");
				} else {
					$text .= "<img src='".e_PLUGIN."links_page/images/generic.png' alt='' />";
				}
				$text .= "
				</td>
				<td style='width:70%' class='forumheader3'>".$row['link_name']."</td>
				<td style='width:5%; text-align:center; white-space: nowrap' class='forumheader3'>
					<input type='image' src='".e_IMAGE."admin_images/up.png' value='".$linkid.".".$row['link_order'].".".$row['link_category']."' name='inc' />
					<input type='image' src='".e_IMAGE."admin_images/down.png' value='".$linkid.".".$row['link_order'].".".$row['link_category']."' name='dec' />
				</td>
				<td style='width:15%; text-align:center; white-space: nowrap' class='forumheader3'>
					".$rs->form_button("button", "main_edit_{$linkid}", LCLAN_9, "onclick=\"document.location='".e_SELF."?create.edit.$linkid'\"")."
					".$rs->form_button("submit", "main_delete_{$linkid}", LCLAN_10, "onclick=\"return jsconfirm('".$tp->toJS(LCLAN_58." [".$row['link_name']."]")."') \" ")."
				</td>
				<td style='width:5%; text-align:center' class='forumheader3'>
					".$rs -> form_select_open("link_order[]");
					for($a = 1; $a <= $link_total; $a++) {
						$text .= $rs -> form_option($a, ($row['link_order'] == $a ? "1" : "0"), $linkid, "");
					}
					$text .= $rs -> form_select_close()."
				</td>
				</tr>";
			}
			$text .= "
			<tr>
			<td class='forumheader' colspan='4'>&nbsp;</td>
			<td class='forumheader' style='width:5%; text-align:center'>
			".$rs->form_button("submit", "update_order", LCLAN_94)."
			</td>
			</tr>
			</table></div>
			".$rs->form_close();
		} else {
			$text .= "<div style='text-align:center'>".LCLAN_61."</div>";
		}
		$ns->tablerender(LCLAN_59.": ".$cat[$id], $text);
	}

	function show_message($message) {
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function create_link($sub_action, $id) {
		global $sql, $rs, $ns, $fl;
		
		$row['link_category']		= "";
		$row['link_name']			= "";
		$row['link_url']			= "";
		$row['link_description']	= "";
		$row['link_button']			= "";
		$row['link_open']			= "";
		$row['link_class']			= "";
		$link_resize_value			= (isset($linkspage_pref['link_resize_value']) && $linkspage_pref['link_resize_value'] ? $linkspage_pref['link_resize_value'] : "100");
		
		if ($sub_action == 'edit' && !$_POST['submit']) {
			if ($sql->db_Select("links_page", "*", "link_id='$id' ")) {
				$row = $sql->db_Fetch();
			}
		}

		if ($sub_action == 'sn') {
			if ($sql->db_Select("tmp", "*", "tmp_time='$id'")) {
				$row = $sql->db_Fetch();
				$submitted			= explode("^", $row['tmp_info']);
				$row['link_category']		= $submitted[0];
				$row['link_name']			= $submitted[1];
				$row['link_url']			= $submitted[2];
				$row['link_description']	= $submitted[3]."\n[i]".LCLAN_82." ".$submitted[5]."[/i]";
				$row['link_button']			= $submitted[4];
			}
		}

		if(isset($_POST['uploadlinkicon'])){
			$row['link_category']		= $_POST['cat_id'];
			$row['link_name']			= $_POST['link_name'];			
			$row['link_url']			= $_POST['link_url'];
			$row['link_description']	= $_POST['link_description'];
			$row['link_button']			= $_POST['link_button'];
			$row['link_open']			= $_POST['linkopentype'];
			$row['link_class']			= $_POST['link_class'];
			$link_resize_value			= (isset($_POST['link_resize_value']) && $_POST['link_resize_value'] ? $_POST['link_resize_value'] : $link_resize_value);
		}

		$text = "
		<div style='text-align:center'>
		".$rs -> form_open("post", e_SELF."?".e_QUERY, "linkform", "", "enctype='multipart/form-data'", "")."
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_12.":</td>
		<td style='width:70%' class='forumheader3'>";

		if (!$link_cats = $sql->db_Select("links_page_cat")) {
			$text .= LCLAN_13."<br />";
		} else {
			$text .= $rs -> form_select_open("cat_id");
			while (list($cat_id, $cat_name, $cat_description) = $sql->db_Fetch()) {
				if ( (isset($link_category) && $row['link_category'] == $cat_id) || (isset($row['linkid']) && $cat_id == $row['linkid'] && $action == "add") ) {
					$text .= $rs -> form_option($cat_name, "1", $cat_id, "");
				} else {
					$text .= $rs -> form_option($cat_name, "0", $cat_id, "");
				}
			}
			$text .= $rs -> form_select_close();
		}
		$text .= "
		</td>
		</tr>
		
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_15.":</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_text("link_name", 60, $row['link_name'], 100)."
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_16.":</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_text("link_url", 60, $row['link_url'], 200)."
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_17.":</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_textarea("link_description", '59', '3', $row['link_description'], "", "", "", "", "")."
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_106.":</td>
		<td style='width:70%' class='forumheader3'>
		<a href='javascript:void(0);' onclick=\"expandit('diviconnew')\">".LCLAN_109."</a><br />
		<div id='diviconnew' style='display:none'><br />";
			if(!FILE_UPLOADS){
				$text .= "<b>".LCLAN_113."</b>";
			}else{
				if(!is_writable(e_PLUGIN."links_page/link_images/")){
					$text .= "<b>".LCLAN_114." ".e_PLUGIN."links_page/link_images/ ".LCLAN_115."</b><br />";
				}
				$text .= "
				<input class='tbox' type='file' name='file_userfile[]'  size='58' /> 
				".LCLAN_110.": ".$rs -> form_text("link_resize_value", 3, $link_resize_value, 3)."&nbsp;px 
				".$rs -> form_button("submit", "uploadlinkicon", LCLAN_111, "", "", "");
			}
		$text .= "<br /></div>
		</td>
		</tr>";

		$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
		$iconlist = $fl->get_files(e_PLUGIN."links_page/link_images/","",$rejectlist);

		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_18.":</td>
		<td style='width:70%' class='forumheader3'>
		<a href='javascript:void(0);' onclick=\"expandit('diviconex')\">".LCLAN_39."</a><br />
		<div id='diviconex' style='display:none'><br />
			".$rs -> form_text("link_button", 60, $row['link_button'], 100)."
			".$rs -> form_button("button", '', LCLAN_39, "onclick=\"expandit('linkbut')\"")."
			<div id='linkbut' style='{head}; display:none'>";
			foreach($iconlist as $icon){
				$text .= "<a href=\"javascript:insertext('".$icon['fname']."','link_button','linkbut')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
			}
			//$text .= "<br />".LCLAN_84;
			$text .= "</div>";
		$text .= "<br /></div>";

		$text .= "
		</td>
		</tr>";

		// 0    = same window
		// 1    = _blank
		// 2    = _parent
		// 3    = _top
		// 4    = miniwindow
		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_19.":</td>
		<td style='width:70%' class='forumheader3'>
		".$rs -> form_select_open("linkopentype")."
		".$rs -> form_option(LCLAN_20, ($row['link_open'] == "0" ? "1" : "0"), "0", "")."
		".$rs -> form_option(LCLAN_23, ($row['link_open'] == "1" ? "1" : "0"), "1", "")."
		".$rs -> form_option(LCLAN_24, ($row['link_open'] == "4" ? "1" : "0"), "4", "")."
		".$rs -> form_select_close()."
		</td>
		</tr>
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_25.":<br /><span class='smalltext'>(".LCLAN_26.")</span></td>
		<td style='width:70%' class='forumheader3'>".r_userclass("link_class", $row['link_class'], "off", "public,guest,nobody,member,admin,classes")."
		</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id && $sub_action == "edit") {
			$text .= $rs -> form_button("submit", "add_link", LCLAN_27, "", "", "").$rs -> form_hidden("link_id", $row['link_id']);
		} else {
			$text .= $rs -> form_button("submit", "add_link", LCLAN_28, "", "", "");
		}
		$text .= "</td>
		</tr>

		</table>
		".$rs -> form_close()."
		</div>";
		$ns->tablerender(LCLAN_29, $text);
	}

	function submit_link($sub_action, $id) {
		global $tp, $sql, $e107cache;

		$link_name			= $tp->toDB($_POST['link_name'], "admin");
		$link_url			= $tp->toDB($_POST['link_url'], "admin");
		$link_description	= $tp->toDB($_POST['link_description'], "admin");
		$link_button		= $tp->toDB($_POST['link_button'], "admin");
		$link_t				= $sql->db_Count("links_page", "(*)", "WHERE link_category='".$_POST['cat_id']."'");

		if ($id && $sub_action != "sn") {
			$sql->db_Update("links_page", "link_name='$link_name', link_url='$link_url', link_description='$link_description', link_button= '$link_button',   link_category='".$_POST['cat_id']."', link_open='".$_POST['linkopentype']."', link_class='".$_POST['link_class']."' WHERE link_id='$id'");
			$e107cache->clear("sitelinks");
			$this->show_message(LCLAN_3);
		} else {
			$sql->db_Insert("links_page", "0, '$link_name', '$link_url', '$link_description', '$link_button', '".$_POST['cat_id']."', '".($link_t+1)."', '0', '".$_POST['linkopentype']."', '".$_POST['link_class']."'");
			$e107cache->clear("sitelinks");
			$this->show_message(LCLAN_2);
		}
		if ($sub_action == "sn") {
			$sql->db_Delete("tmp", "tmp_time='$id'    ");
		}
	}

	function show_categories($sub_action, $id) {
		global $sql, $rs, $ns, $tp, $fl;

		$row['link_category_name']			= "";
		$row['link_category_description']	= "";
		$row['link_category_icon']			= "";
		$link_cat_resize_value				= (isset($linkspage_pref['link_cat_resize_value']) && $linkspage_pref['link_cat_resize_value'] ? $linkspage_pref['link_cat_resize_value'] : "50");

		if(isset($_POST['uploadcatlinkicon'])){
			$row['link_category_name']			= $_POST['link_category_name'];			
			$row['link_category_description']	= $_POST['link_category_description'];
			$row['link_category_icon']			= $_POST['link_category_icon'];
			$link_cat_resize_value				= (isset($_POST['link_cat_resize_value']) && $_POST['link_cat_resize_value'] ? $_POST['link_cat_resize_value'] : $link_cat_resize_value);
		}

		if ($sub_action == "edit") {
			if ($sql->db_Select("links_page_cat", "*", "link_category_id='$id' ")) {
				$row = $sql->db_Fetch();
			}
		}

		$text = "<div style='text-align:center'>
		".$rs->form_open("post", e_SELF, "linkform", "", "enctype='multipart/form-data'", "")."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_71."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs->form_text("link_category_name", 50, $row['link_category_name'], 200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".LCLAN_72."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs->form_text("link_category_description", 60, $row['link_category_description'], 200)."</td>
		</tr>";

		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_152.":</td>
		<td style='width:70%' class='forumheader3'>
		<a href='javascript:void(0);' onclick=\"expandit('diviconnew')\">".LCLAN_109."</a><br />
		<div id='diviconnew' style='display:none'><br />";
			if(!FILE_UPLOADS){
				$text .= "<b>".LCLAN_113."</b>";
			}else{
				if(!is_writable(e_PLUGIN."links_page/cat_images/")){
					$text .= "<b>".LCLAN_114." ".e_PLUGIN."links_page/cat_images/ ".LCLAN_115."</b><br />";
				}
				$text .= "
				<input class='tbox' type='file' name='file_userfile[]'  size='58' /> 
				".LCLAN_110.": ".$rs -> form_text("link_cat_resize_value", 3, $link_cat_resize_value, 3)."&nbsp;px 
				".$rs -> form_button("submit", "uploadcatlinkicon", LCLAN_111, "", "", "");
			}
		$text .= "<br /></div>
		</td>
		</tr>";

		$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
		$iconlist = $fl->get_files(e_PLUGIN."links_page/cat_images/","",$rejectlist);

		$text .= "
		<tr>
		<td style='width:30%' class='forumheader3'>".LCLAN_73.":</td>
		<td style='width:70%' class='forumheader3'>
		<a href='javascript:void(0);' onclick=\"expandit('diviconex')\">".LCLAN_39."</a><br />
		<div id='diviconex' style='display:none'><br />
			".$rs -> form_text("link_category_icon", 60, $row['link_category_icon'], 100)."
			".$rs -> form_button("button", '', LCLAN_39, "onclick=\"expandit('catico')\"")."
			<div id='catico' style='{head}; display:none'>";
			foreach($iconlist as $icon){
				$text .= "<a href=\"javascript:insertext('".$icon['fname']."','link_category_icon','catico')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
			}
			//$text .= "<br />".LCLAN_85;
			$text .= "</div>";
		$text .= "<br /></div>";

		$text .= "
		</td>
		</tr>";

		$text .= "<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id) {
			$text .= $rs -> form_button("submit", "update_category", LCLAN_74, "", "", "")."
			".$rs->form_button("submit", "category_clear", LCLAN_81). $rs->form_hidden("link_category_id", $id)."
			</td></tr>";
		} else {
			$text .= $rs -> form_button("submit", "create_category", LCLAN_75, "", "", "")."</td></tr>";
		}
		$text .= "</table>
		".$rs->form_close()."
		</div>";

		$ns->tablerender(LCLAN_100, $text);

		if ($category_total = $sql->db_Select("links_page_cat")) {
			$text = "
			<div style='text-align: center'>
			".$rs->form_open("post", e_SELF."?cat", "", "", "")."
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td style='width:5%' class='fcaption'>".LCLAN_86."</td>
			<td style='width:75%' class='fcaption'>".LCLAN_59."</td>
			<td style='width:20%' class='fcaption'>".LCLAN_60."</td>
			</tr>";
			while ($row = $sql->db_Fetch()) {
				$linkcatid = $row['link_category_id'];
				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader3'>";
				if ($row['link_category_icon']) {
					$text .= (strstr($row['link_category_icon'], "/") ? "<img src='".e_BASE.$row['link_category_icon']."' alt='' style='vertical-align:middle' />" : "<img src='".e_PLUGIN."links_page/cat_images/".$row['link_category_icon']."' alt='' style='vertical-align:middle' />");
				} else {
					$text .= "&nbsp;";
				}
				$text .= "</td>
				<td style='width:75%' class='forumheader3'>".$row['link_category_name']."<br /><span class='smalltext'>".$row['link_category_description']."</span></td>
				<td style='width:20%; text-align:center; white-space: nowrap' class='forumheader3'>
				".$rs->form_button("submit", "category_edit_{$linkcatid}", LCLAN_9)."
				".$rs->form_button("submit", "category_delete_{$linkcatid}", LCLAN_10, "onclick=\"return jsconfirm('".$tp->toJS(LCLAN_56." [ ".$row['link_category_name']." ]")."') \"")."
				".$rs->form_button("submit", "view_cat_links_{$linkcatid}", LCLAN_87)."
				</td>
				</tr>\n";
			}
			$text .= "</table>
			".$rs->form_close()."
			</div>";
		} else {
			$text = "<div style='text-align:center'>".LCLAN_69."</div>";
		}
		$ns->tablerender(LCLAN_70, $text);

		unset($row['link_category_name'], $row['link_category_description'], $row['link_category_icon']);
	}

	function show_submitted($sub_action, $id) {
		global $sql, $rs, $ns, $tp;

		$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
		if ($submitted_total = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link' ")) {
			$text .= "<table class='fborder' style='width:99%'>
			<tr>
			<td style='width:50%' class='fcaption'>".LCLAN_53."</td>
			<td style='width:30%' class='fcaption'>".LCLAN_45."</td>
			<td style='width:20%; text-align:center' class='fcaption'>".LCLAN_60."</td>
			</tr>";
			while ($row = $sql->db_Fetch()) {
				$tmp_time = $row['tmp_time'];
				$submitted = explode("^", $row['tmp_info']);
				if (!strstr($submitted[2], "http")) {
					$submitted[2] = "http://".$submitted[2];
				}
				$text .= "<tr>
				<td style='width:50%' class='forumheader3'><a href='".$submitted[2]."' rel='external'>".$submitted[2]."</a></td>
				<td style='width:30%' class='forumheader3'>".$submitted[5]."</td>
				<td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'><div>
				".$rs->form_open("post", e_SELF."?sn", "submitted_links")."
				".$rs->form_button("button", "create_sn_{$tmp_time}", LCLAN_14, "onclick=\"document.location='".e_SELF."?create.sn.$tmp_time'\"")."
				".$rs->form_button("submit", "sn_delete_{$tmp_time}", LCLAN_10, "onclick=\"return jsconfirm('".$tp->toJS(LCLAN_57." [ ".$tmp_time." ]")."') \"")."
				</div>".$rs->form_close()."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
		} else {
			$text .= "<div style='text-align:center'>".LCLAN_76."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(LCLAN_66, $text);
	}

	function show_pref_options() {
		global $linkspage_pref, $ns, $rs, $pref;

		$TOPIC_ROW_NOEXPAND = "
		<tr>
			<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
			<td class='forumheader3'>{TOPIC_FIELD}</td>
		</tr>";

		$TOPIC_ROW = "
		<tr>
			<td class='forumheader3' style='width:20%; white-space:nowrap; vertical-align:top;'>{TOPIC_TOPIC}</td>
			<td class='forumheader3' style='vertical-align:top;'>
				<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>{TOPIC_HEADING}</a>
				<div style='display: none;'>
					<div class='smalltext'>{TOPIC_HELP}</div><br />
					{TOPIC_FIELD}
				</div>
			</td>
		</tr>";

		$TOPIC_TITLE_ROW = "<tr><td colspan='2' class='fcaption'>{TOPIC_CAPTION}</td></tr>";

		$text = "
		<div style='text-align:center'>
		".$rs -> form_open("post", e_SELF."?".e_QUERY, "", "", "", "")."
		<table style='".ADMIN_WIDTH."' class='fborder'>";

		$TOPIC_CAPTION = LCLAN_116;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

		$TOPIC_TOPIC = LCLAN_119;
		$TOPIC_HEADING = LCLAN_40;
		$TOPIC_HELP = LCLAN_34;
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_page_categories", "1", ($linkspage_pref['link_page_categories'] ? "1" : "0"), "", "").LCLAN_138." 
		".$rs -> form_radio("link_page_categories", "0", ($linkspage_pref['link_page_categories'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_41;
		$TOPIC_HEADING = LCLAN_42;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_submit", "1", ($linkspage_pref['link_submit'] ? "1" : "0"), "", "").LCLAN_138."
		".$rs -> form_radio("link_submit", "0", ($linkspage_pref['link_submit'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_43;
		$TOPIC_HEADING = LCLAN_44;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = r_userclass("link_submit_class", $linkspage_pref['link_submit_class']);
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_CAPTION = LCLAN_117;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

		$TOPIC_TOPIC = LCLAN_120;
		$TOPIC_HEADING = LCLAN_121;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_checkbox("link_cat_icon", 1, ($linkspage_pref['link_cat_icon'] ? "1" : "0"))." ".LCLAN_122."<br />
		".$rs -> form_checkbox("link_cat_desc", 1, ($linkspage_pref['link_cat_desc'] ? "1" : "0"))." ".LCLAN_123."<br />
		".$rs -> form_checkbox("link_cat_amount", 1, ($linkspage_pref['link_cat_amount'] ? "1" : "0"))." ".LCLAN_124."<br />
		".$rs -> form_checkbox("link_cat_total", 1, ($linkspage_pref['link_cat_total'] ? "1" : "0"))." ".LCLAN_125."<br />
		".$rs -> form_checkbox("link_cat_toprefer", 1, ($linkspage_pref['link_cat_toprefer'] ? "1" : "0"))." ".LCLAN_147."<br />
		".$rs -> form_checkbox("link_cat_toprated", 1, ($linkspage_pref['link_cat_toprated'] ? "1" : "0"))." ".LCLAN_148."<br />
		";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);
		
		$TOPIC_TOPIC = LCLAN_146;
		$TOPIC_HEADING = LCLAN_133;
		$TOPIC_HELP = "<br />
		<img src='".e_THEME.$pref['sitetheme']."/images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='vertical-align:middle;' /><br />
		".e_THEME.$pref['sitetheme']."/images/".(defined("BULLET") ? BULLET : "bullet2.gif");
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_cat_icon_empty", "1", ($linkspage_pref['link_cat_icon_empty'] ? "1" : "0"), "", "").LCLAN_138."
		".$rs -> form_radio("link_cat_icon_empty", "0", ($linkspage_pref['link_cat_icon_empty'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_126;
		$TOPIC_HEADING = LCLAN_127;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_cat_sort")."
		".$rs -> form_option("link category name", ($linkspage_pref['link_cat_sort'] == "link_category_name" ? "1" : "0"), "link_category_name", "")."
		".$rs -> form_option("link category id", ($linkspage_pref['link_cat_sort'] == "link_category_id" ? "1" : "0"), "link_category_id", "")."
		".$rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_128;
		$TOPIC_HEADING = LCLAN_129;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_cat_order")."
		".$rs -> form_option("ASC", ($linkspage_pref['link_cat_order'] == "ASC" ? "1" : "0"), "ASC", "")."
		".$rs -> form_option("DESC", ($linkspage_pref['link_cat_order'] == "DESC" ? "1" : "0"), "DESC", "")."
		".$rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_153;
		$TOPIC_HEADING = LCLAN_155;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = $rs -> form_text("link_cat_resize_value", "3", $linkspage_pref['link_cat_resize_value'], "3", "tbox", "", "", "")." px";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_CAPTION = LCLAN_118;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

		$TOPIC_TOPIC = LCLAN_120;
		$TOPIC_HEADING = LCLAN_121;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_checkbox("link_icon", 1, ($linkspage_pref['link_icon'] ? "1" : "0"))." ".LCLAN_122."<br />
		".$rs -> form_checkbox("link_referal", 1, ($linkspage_pref['link_referal'] ? "1" : "0"))." ".LCLAN_131."<br />
		".$rs -> form_checkbox("link_url", 1, ($linkspage_pref['link_url'] ? "1" : "0"))." ".LCLAN_130."<br />
		".$rs -> form_checkbox("link_desc", 1, ($linkspage_pref['link_desc'] ? "1" : "0"))." ".LCLAN_123."<br />
		";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_104;
		$TOPIC_HEADING = LCLAN_105;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_rating", "1", ($linkspage_pref['link_rating'] ? "1" : "0"), "", "").LCLAN_138."
		".$rs -> form_radio("link_rating", "0", ($linkspage_pref['link_rating'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_132;
		$TOPIC_HEADING = LCLAN_133;
		$TOPIC_HELP = "<br />
		<img style='border:0; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='' /><br />
		".e_PLUGIN."links_page/images/generic.png";
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_icon_empty", "1", ($linkspage_pref['link_icon_empty'] ? "1" : "0"), "", "").LCLAN_138."
		".$rs -> form_radio("link_icon_empty", "0", ($linkspage_pref['link_icon_empty'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_144;
		$TOPIC_HEADING = LCLAN_145;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_radio("link_sortorder", "1", ($linkspage_pref['link_sortorder'] ? "1" : "0"), "", "").LCLAN_138."
		".$rs -> form_radio("link_sortorder", "0", ($linkspage_pref['link_sortorder'] ? "0" : "1"), "", "").LCLAN_139;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_134;
		$TOPIC_HEADING = LCLAN_135;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_sort")."
		".$rs -> form_option("link_name", ($linkspage_pref['link_sort'] == "link_name" ? "1" : "0"), "link_name", "")."
		".$rs -> form_option("link_url", ($linkspage_pref['link_sort'] == "link_url" ? "1" : "0"), "link_url", "")."
		".$rs -> form_option("link_order", ($linkspage_pref['link_sort'] == "link_order" ? "1" : "0"), "link_order", "")."
		".$rs -> form_option("link_refer", ($linkspage_pref['link_sort'] == "link_refer" ? "1" : "0"), "link_refer", "")."
		".$rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_136;
		$TOPIC_HEADING = LCLAN_137;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_order")."
		".$rs -> form_option("ASC", ($linkspage_pref['link_order'] == "ASC" ? "1" : "0"), "ASC", "")."
		".$rs -> form_option("DESC", ($linkspage_pref['link_order'] == "DESC" ? "1" : "0"), "DESC", "")."
		".$rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		// 0    = same window
		// 1    = _blank
		// 2    = _parent
		// 3    = _top
		// 4    = miniwindow
		// 5	= use individual settings of each link
		$TOPIC_TOPIC = LCLAN_140;
		$TOPIC_HEADING = LCLAN_141;
		$TOPIC_HELP = LCLAN_142;
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_open_all")."
		".$rs -> form_option(LCLAN_143, ($linkspage_pref['link_open_all'] == "5" ? "1" : "0"), "5", "")."
		".$rs -> form_option(LCLAN_20, ($linkspage_pref['link_open_all'] == "0" ? "1" : "0"), "0", "")."
		".$rs -> form_option(LCLAN_23, ($linkspage_pref['link_open_all'] == "1" ? "1" : "0"), "1", "")."
		".$rs -> form_option(LCLAN_24, ($linkspage_pref['link_open_all'] == "4" ? "1" : "0"), "4", "")."
		".$rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_TOPIC = LCLAN_153;
		$TOPIC_HEADING = LCLAN_154;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = $rs -> form_text("link_resize_value", "3", $linkspage_pref['link_resize_value'], "3", "tbox", "", "", "")." px";
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$TOPIC_CAPTION = LCLAN_149;
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_TITLE_ROW);

		$TOPIC_TOPIC = LCLAN_150;
		$TOPIC_HEADING = LCLAN_151;
		$TOPIC_HELP = "";
		$TOPIC_FIELD = "
		".$rs -> form_select_open("link_pagenumber");
		for($i=2;$i<52;$i++){
			$TOPIC_FIELD .= $rs -> form_option($i, ($linkspage_pref['link_pagenumber'] == $i ? "1" : "0"), $i, "");
			$i++;
		}
		$TOPIC_FIELD .= $rs -> form_select_close();
		$text .= preg_replace("/\{(.*?)\}/e", '$\1', $TOPIC_ROW);

		$text .= "
		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>
		".$rs->form_button("submit", "updateoptions", LCLAN_35)."
		</td>
		</tr>

		</table>
		".$rs->form_close()."
		</div>";
		$ns->tablerender(LCLAN_36, $text);
	}
}

?>