<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|   $Source: /cvs_backup/e107_0.7/e107_admin/newspost.php,v $
|   $Revision: 1.81 $
|   $Date: 2005-05-13 01:03:34 $
|   $Author: mcfly_e107 $
+---------------------------------------------------------------+

*/
require_once("../class2.php");

if (!getperms("H")) {
	header("location:".e_BASE."index.php");
	exit;
}
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
function headerjs()
{
	global $cal;
	return $cal->load_files();
}
$e_sub_cat = 'news';
$e_wysiwyg = "data,news_extended";

// -------- Presets. ------------  // always load before auth.php
require_once(e_HANDLER."preset_class.php");
$pst = new e_preset;
$pst->form = "dataform"; // form id of the form that will have it's values saved.
$pst->page = "newspost.php?create"; // display preset options on which page(s).
$pst->id = "admin_newspost";
// ------------------------------

$newspost = new newspost;
require_once("auth.php");
$pst->save_preset(); // save and render result using unique name

require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."file_class.php");

$fl = new e_file;
$rs = new form;
$ix = new news;

$deltest = array_flip($_POST);
if (e_QUERY) {
	list($action, $sub_action, $id, $from) = explode(".", e_QUERY);
	$id = intval($id);
	$from = intval($from);
	unset($tmp);
}

$from = ($from ? $from : 0);
$amount = 50;

// ##### Main loop -----------------------------------------------------------------------------------------------------------------------

$_POST['news_class'] = implode(",", array_keys($_POST['news_userclass']));

if (preg_match("#(.*?)_delete_(\d+)#", $deltest[$tp->toJS(LAN_DELETE)], $matches)) {
	$delete = $matches[1];
	$del_id = $matches[2];
}

if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
}

if ($delete == "main" && $del_id)
{
	if ($sql->db_Count('news','(*)',"WHERE news_id = '{$del_id}'"))
	{
		$e_event->trigger("newsdel", $del_id);
		if($sql->db_Delete("news", "news_id='$del_id' "))
		{
			$newspost->show_message(NWSLAN_31." #".$del_id." ".NWSLAN_32);
			$e107cache->clear("news.php");
		}
	}
	unset($delete, $del);
}

if ($delete == "category" && $del_id) {
	if ($sql->db_Delete("news_category", "category_id='$del_id' ")) {
		$newspost->show_message(NWSLAN_33." #".$del_id." ".NWSLAN_32);
		unset($delete, $del_id);
	}
}

if ($delete == "sn" && $del_id) {
	if ($sql->db_Delete("submitnews", "submitnews_id='$del_id' ")) {
		$newspost->show_message(NWSLAN_34." #".$del_id." ".NWSLAN_32);
		$e107cache->clear("news.php");
		unset($delete, $del_id);
	}
}

if (IsSet($_POST['submitupload'])) {
	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");

	$uploaded = file_upload(e_IMAGE."newspost_images/");

	foreach($_POST['uploadtype'] as $key=>$uploadtype){
		if($uploadtype == "thumb"){
			rename(e_IMAGE."newspost_images/".$uploaded[$key]['name'],e_IMAGE."newspost_images/thumb_".$uploaded[$key]['name']);
		}

		if($uploadtype == "file"){
			rename(e_IMAGE."newspost_images/".$uploaded[$key]['name'],e_FILE."downloads/".$uploaded[$key]['name']);
		}

		if ($uploadtype == "resize" && $_POST['resize_value'][$key]) {
			require_once(e_HANDLER."resize_handler.php");
			resize_image(e_IMAGE."newspost_images/".$uploaded[$key]['name'], e_IMAGE."newspost_images/".$uploaded[$key]['name'], $_POST['resize_value'], "copy");
		}
	}
}

// required.
if (isset($_POST['preview'])) {
	$_POST['news_title'] = $tp->toDB($_POST['news_title']);
	$_POST['news_summary'] = $tp->toDB($_POST['news_summary']);
	$newspost->preview_item($id);
}

if (IsSet($_POST['submit'])) {
	$newspost->submit_item($sub_action, $id);
	$action = "main";
	unset($sub_action, $id);
}

if (IsSet($_POST['create_category'])) {
	if ($_POST['category_name']) {
		if (empty($_POST['category_button'])) {
			$handle = opendir(e_IMAGE."icons");
			while ($file = readdir($handle)) {
				if ($file != "." && $file != ".." && $file != "/" && $file != "null.txt" && $file != "CVS") {
					$iconlist[] = $file;
				}
			}
			closedir($handle);
			$_POST['category_button'] = $iconlist[0];
		}
		$_POST['category_name'] = $tp->toDB($_POST['category_name'], TRUE);
		$sql->db_Insert("news_category", " '0', '".$_POST['category_name']."', '".$_POST['category_button']."'");
		$newspost->show_message(NWSLAN_35);
	}
}

if (IsSet($_POST['update_category'])) {
	if ($_POST['category_name']) {
		$category_button = ($_POST['category_button'] ? $_POST['category_button'] : "");
		$_POST['category_name'] = $tp->toDB($_POST['category_name'], TRUE);
		$sql->db_Update("news_category", "category_name='".$_POST['category_name']."', category_icon='".$category_button."' WHERE category_id='".$_POST['category_id']."'");
		$newspost->show_message(NWSLAN_36);
	}
}

if (IsSet($_POST['save_prefs'])) {
	$pref['newsposts'] = $_POST['newsposts'];

	// ##### ADDED FOR NEWSARCHIVE --------------------------------------------------------------------
	$pref['newsposts_archive'] = $_POST['newsposts_archive'];
	$pref['newsposts_archive_title'] = $_POST['newsposts_archive_title'];
	// ##### END --------------------------------------------------------------------------------------

	$pref['news_cats'] = $_POST['news_cats'];
	$pref['nbr_cols'] = $_POST['nbr_cols'];
	$pref['subnews_attach'] = $_POST['subnews_attach'];
	$pref['subnews_resize'] = $_POST['subnews_resize'];
	$pref['subnews_class'] = $_POST['subnews_class'];
	$pref['subnews_htmlarea'] = $_POST['subnews_htmlarea'];
	$pref['subnews_hide_news'] = $_POST['subnews_hide_news'];

	/*
	changes by jalist 22/01/2005:
	added pref to render new date header
	*/
	$pref['news_newdateheader'] = $_POST['news_newdateheader'];
	$pref['news_unstemplate'] = $_POST['news_unstemplate'];

	save_prefs();
	$e107cache->clear("news.php");
	$newspost->show_message(NWSLAN_119);
}

if (!e_QUERY || $action == "main") {
	$newspost->show_existing_items($action, $sub_action, $id, $from, $amount);
}

if ($action == "create") {
	$preset = $pst->read_preset("admin_newspost");  //only works here because $_POST is used.

	if ($sub_action == "edit" && !$_POST['preview'] && !$_POST['submit']) {
		if ($sql->db_Select("news", "*", "news_id='$id' ")) {
			$row = $sql->db_Fetch();
			extract($row);
			$_POST['news_title'] = $news_title;
			$_POST['data'] = $news_body;
			$_POST['news_extended'] = $news_extended;
			$_POST['news_allow_comments'] = $news_allow_comments;
			$_POST['news_class'] = $news_class;
			$_POST['news_summary'] = $news_summary;
			$_POST['news_sticky'] = $news_sticky;
			$_POST['news_datestamp'] = $news_datestamp;

			$_POST['cat_id'] = $news_category;
			$_POST['news_start'] = $news_start;
			$_POST['news_end'] = $news_end;
			$_POST['comment_total'] = $sql->db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
			$_POST['news_rendertype'] = $news_render_type;
			$_POST['news_thumbnail'] = $news_thumbnail;
		}
	}
	$newspost->create_item($sub_action, $id);
}

if ($action == "cat") {
	$newspost->show_categories($sub_action, $id);
}

if ($action == "sn") {
	$newspost->submitted_news($sub_action, $id);
}

if ($action == "pref") {
	$newspost->show_news_prefs($sub_action, $id);
}

echo "
<script type=\"text/javascript\">
function fclear() {
	document.getElementById('dataform').data.value = \"\";
	document.getElementById('dataform').news_extended.value = \"\";
}
</script>\n";

require_once("footer.php");
exit;

class newspost {


	function show_existing_items($action, $sub_action, $id, $from, $amount) {
		// ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns, $tp;
		$text = "<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; height : 300px; overflow : auto; margin-left: auto; margin-right: auto;'>";

		if (IsSet($_POST['searchquery'])) {
			$query = "news_title REGEXP('".$_POST['searchquery']."') OR news_body REGEXP('".$_POST['searchquery']."') OR news_extended REGEXP('".$_POST['searchquery']."') ORDER BY news_datestamp DESC";
			} else {
			$query = "ORDER BY ".($sub_action ? $sub_action : "news_datestamp")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
		}

		if ($sql->db_Select("news", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere")))
		{

			$newsarray = $sql -> db_getList();
			$text .= "
			<form action='".e_SELF."' id='newsform' method='post'>
			<table class='fborder' style='width:96%'>
			<tr>
			<td style='width:5%' class='fcaption'><a href='".e_SELF."?main.news_id.".($id == "desc" ? "asc" : "desc").".$from'>".LAN_NEWS_45."</a></td>
			<td style='width:55%' class='fcaption'><a href='".e_SELF."?main.news_title.".($id == "desc" ? "asc" : "desc").".$from'>".NWSLAN_40."</a></td>
			<td style='width:15%' class='fcaption'>Render-type</td>
			<td style='width:15%' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
			$ren_type = array("default","title","other-news","other-news 2");
			foreach($newsarray as $row)
			{
				extract($row);

				// Note: To fix the alignment bug. Put both buttons inside the Form.
				// But make EDIT a 'button' and DELETE 'submit'

				$text .= "<tr>
				<td style='width:5%' class='forumheader3'>$news_id</td>
				<td style='width:55%' class='forumheader3'><a href='".e_BASE."comment.php?comment.news.$news_id'>".($news_title ? $tp->toHTML($news_title) : "[".NWSLAN_42."]")."</a></td>
				<td style='20%' class='forumheader3'>";
				$text .= $ren_type[$news_render_type];
				if($news_sticky)
				{
					$sicon = (file_exists(THEME."generic/sticky.png") ? THEME."generic/sticky.png" : e_IMAGE."generic/".IMODE."/sticky.png");
					$text .= " <img src='".$sicon."' alt='' />";
				}
				$text .= "
				</td>

				<td style='width:15%; text-align:center' class='forumheader3'>
				<a href='".e_SELF."?create.edit.{$news_id}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[main_{$news_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".NWSLAN_39." [ID: $news_id ]')\"/>
				</td>
				</tr>";
			}
			$text .= "</table></form>";
			} else {
			$text .= "<div style='text-align:center'>".NWSLAN_43."</div>";
		}
		$text .= "</div>";

		$newsposts = $sql->db_Count("news");

		if ($newsposts > $amount && !$_POST['searchquery']) {
			$a = $newsposts/$amount;
			$r = explode(".", $a);
			if ($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
			if ($pages) {
				$current = ($from/$amount)+1;
				$text .= "<br />".NWSLAN_62." ";
				for($a = 1; $a <= $pages; $a++) {
					$text .= ($current == $a ? " <b>[$a]</b>" : " [<a href='".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.news_datestamp.desc.").(($a-1) * $amount)."'>$a</a>] ");
				}
				$text .= "<br />";
			}
		}

		$text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".NWSLAN_63."' />\n</p>\n</form>\n</div>";



		$ns->tablerender(NWSLAN_4, $text);
	}

	function show_options($action) {
		global $sql;

		if ($action == "") {
			$action = "main";
		}
		$var['main']['text'] = NWSLAN_44;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = NWSLAN_45;
		$var['create']['link'] = e_SELF."?create";

		$var['cat']['text'] = NWSLAN_46;
		$var['cat']['link'] = e_SELF."?cat";
		$var['cat']['perm'] = "7";

		$var['pref']['text'] = NWSLAN_90;
		$var['pref']['link'] = e_SELF."?pref";
		$var['pref']['perm'] = "N";
		if ($sql->db_Select("submitnews", "*", "submitnews_auth ='0' ")) {
			$var['sn']['text'] = NWSLAN_47;
			$var['sn']['link'] = e_SELF."?sn";
			$var['sn']['perm'] = "N";
		}

		show_admin_menu(NWSLAN_48, $action, $var);

	}

	function create_item($sub_action, $id)
	{
		global $cal, $IMAGES_DIRECTORY;
		// ##### Display creation form ---------------------------------------------------------------------------------------------------------
		/* 08-08-2004 - unknown - fixed `Insert Image' display to use $IMAGES_DIRECTORY */
		global $sql, $rs, $ns, $pref, $fl, $IMAGES_DIRECTORY, $tp, $pst, $e107;
		$thumblist = $fl->get_files(e_IMAGE."newspost_images/", 'thumb_');

		$rejecthumb = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
		$imagelist = $fl->get_files(e_IMAGE."newspost_images/","",$rejecthumb);

		$filelist = array();
		$downloadList = array();

		$sql->db_Select("download", "*", "download_class != ".e_UC_NOBODY);
		while ($row = $sql->db_Fetch()) {
			extract($row);
			if($download_url)
			{
				$filelist[] = array("id" => $download_id, "name" => $download_name, "url" => $download_url, "class" => $download_class);
				$downloadList[] = $download_url;
			}
		}

		$tmp = $fl->get_files(e_FILE."downloads/","",$rejecthumb);
		foreach($tmp as $value)
		{
			if(!in_array($value['fname'], $downloadList))
			{
				$filelist[] = array("id" => 0, "name" => $value['fname'], "url" => $value['fname']);
			}
		}

		if ($sub_action == "sn" && !$_POST['preview']) {
			if ($sql->db_Select("submitnews", "*", "submitnews_id=$id", TRUE)) {
				list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['data'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->db_Fetch();

				if ($pref['wysiwyg'])
				{
					$_POST['data'] .= "<br /><b>".NWSLAN_49." ".$submitnews_name."</b>";
					$_POST['data'] .= ($submitnews_file)? "<br /><br /><img src='".e_IMAGE."newspost_images/$submitnews_file' style='float:right; margin-left:5px;margin-right:5px;margin-top:5px;margin-bottom:5px; border:1px solid' />":	"";
				}
				else
				{
					$_POST['data'] .= "\n[[b]".NWSLAN_49." ".$submitnews_name."[/b]]";
					$_POST['data'] .= ($submitnews_file)?"\n\n[img]".e_IMAGE."newspost_images/".$submitnews_file." [/img]": 	"";
				}
				$_POST['cat_id'] = $submitnews_category;
			}
		}

		if ($sub_action == "upload" && !$_POST['preview']) {
			if ($sql->db_Select("upload", "*", "upload_id=$id")) {
				$row = $sql->db_Fetch();
				extract($row);

				$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
				$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));

				$_POST['news_title'] = NWSLAN_66.": ".$upload_name;
				$_POST['data'] = $upload_description."\n[b]".NWSLAN_49." <a href='user.php?id.".$post_author_id."'>".$post_author_name."</a>[/b]\n\n[file=request.php?".$id."]".$upload_name."[/file]\n";
			}
		}

		$text = "<div style='text-align:center'>
		<form ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : "")." method='post' action='".e_SELF."?".e_QUERY."' id='dataform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_6.": </td>
		<td style='width:80%' class='forumheader3'>";

		if (!$sql->db_Select("news_category")) {
			$text .= NWSLAN_10;
			} else {

			$text .= "
			<select name='cat_id' class='tbox'>";

			while (list($cat_id, $cat_name, $cat_icon) = $sql->db_Fetch()) {
				$text .= ($_POST['cat_id'] == $cat_id ? "<option value='$cat_id' selected='selected'>".$cat_name."</option>" : "<option value='$cat_id'>".$cat_name."</option>");
			}
			$text .= "</select>";
		}
		$text .= "</td>
		</tr>
		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_12.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='news_title' size='80' value='".$_POST['news_title']."' maxlength='200' style='width:95%'/>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".LAN_NEWS_27.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='news_summary' size='80' value='".$tp->toForm($_POST['news_summary'])."' maxlength='250' style='width:95%'/>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
		<td style='width:80%;margin-left:auto' class='forumheader3'>";

		$insertjs = (!$pref['wysiwyg'])?"rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'": "rows='25' style='width:100%'  ";
		$_POST['data'] = $tp->toForm($_POST['data']);
		$text .= "<textarea class='tbox' id='data' name='data'  cols='80'  style='width:95%' $insertjs>".(strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]../", "[img]", $_POST['data']))."</textarea>
		";

		//Main news body textarea
		if (!$pref['wysiwyg']) {
			$text .= "<input id='helpb' class='helpbox' type='text' name='helpb' size='100' style='width:95%'/>
			<br />". display_help("helpb");
		} // end of htmlarea check.

		//Extended news form textarea
		if($pref['wysiwyg']){ $ff_expand = "tinyMCE.execCommand('mceResetDesignMode')";  } // Fixes Firefox issue with hidden wysiwyg textarea.
		$text .= "
		</td>
		</tr>
		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_14.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick=\"expandit(this);$ff_expand\">".NWSLAN_83."</a>
		<div style='display:none'>
		<textarea class='tbox' id='news_extended' name='news_extended' cols='80' style='width:95%' $insertjs>".(strstr($_POST['news_extended'], "[img]http") ? $_POST['news_extended'] : str_replace("[img]../", "[img]", $tp->toForm($_POST['news_extended'])))."</textarea>";
		if (!$pref['wysiwyg']) {
			$text .="<br />". display_help("helpb");
		}
		$text .= "
		</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_66."</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_69."</a>
		<div style='display: none;'>";

		if (!FILE_UPLOADS)
		{
			$text .= "<b>".NWSLAN_78."</b>";
		}
		else
		{
			if (!is_writable(e_FILE."downloads"))
			{
				$text .= "<b>".NWSLAN_70."</b><br /><br />";
			}
			if (!is_writable(e_IMAGE."newspost_images"))
			{
				$text .= "<b>".NWSLAN_71."</b><br /><br />";
			}

			$up_name = array(LAN_NEWS_24,NWSLAN_67,LAN_NEWS_22,NWSLAN_68);
			$up_value = array("resize","image","thumb","file");

			$text .= "<div id='up_container' >
			<span id='upline' style='white-space:nowrap'>
			<input class='tbox' type='file' name='file_userfile[]' size='40' />
			<select class='tbox' name='uploadtype[]'>";
			for ($i=0; $i<count($up_value); $i++)
			{
				$selected = ($_POST['uploadtype'] == $up_value[$i]) ? "selected='selected'" : "";
				$text .= "<option value='".$up_value[$i]."' $selected>".$up_name[$i]."</option>\n";
			};

			$text .="</select>&nbsp;</span>

			</div>
			<table style='width:100%'>
			<tr><td><input type='button' class='button' value='".LAN_NEWS_26."' onclick=\"duplicateHTML('upline','up_container');\"  /></td>
			<td><span class='smalltext'>".LAN_NEWS_25."</span>&nbsp;<input class='tbox' type='text' name='resize_value' value='".$_POST['resize_value']."' size='3' />&nbsp;px</td>
			<td><input class='button' type='submit' name='submitupload' value='".NWSLAN_66."' /></td>
			</tr></table>";

		}
		$text .= "</div>
		</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_NEWS_41."</td>
		<td class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_23."</a>
		<div style='display: none;'>

		(".LAN_NEWS_38.")<br />
		<input class='tbox' type='text' id='news_thumbnail' name='news_thumbnail' size='60' value='".$_POST['news_thumbnail']."' maxlength='100' />
		<input class='button' type ='button' style='cursor:hand' size='30' value='".NWSLAN_118."' onclick='expandit(this)' />
		<div id='newsicn' style='display:none;{head}'>";

		foreach($thumblist as $icon){
			$text .= "<a href=\"javascript:insertext('".$icon['fname']."','news_thumbnail','newsicn')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
		}

		$text .= "</div>
		</td>
		</tr>
		";

		if (!$pref['wysiwyg'])
		{

			$text .= "<tr>
			<td class='forumheader3'>".LAN_NEWS_42."</td>
			<td class='forumheader3'>
			<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_40."</a>
			<div style='display: none;'>
			<br />
			";
			if(!count($filelist))
			{
				$text .= LAN_NEWS_43;
			}
			else
			{
				$text .= LAN_NEWS_39."<br /><br />";
				foreach($filelist as $file)
				{

					if(isset($file['class']))
					{
						$ucinfo = "^".$file['class'];
						$ucname = r_userclass_name($file['class']);
					}
					else
					{
						$ucinfo = "";
						$ucname = r_userclass_name(0);
					}

					if($file['id'])
					{
						$text .= "<a href='javascript:addtext(\"[file=request.php?".$file['id']."{$cinfo}]".$file['name']."[/file]\");'><img src='".e_IMAGE."generic/".IMODE."/file.png' alt='' style='border:0px;vertical-align:middle;' /> ".$file['name']."</a> - $ucname<br />";
					}
					else
					{
						$text .= "<a href='javascript:addtext(\"[file=request.php?".$file['url']."{$cinfo}]".$file['name']."[/file]\");'><img src='".e_IMAGE."generic/".IMODE."/file.png' alt='' style='border:0px;vertical-align:middle;' /> ".$file['name']."</a> - $ucname<br />";
					}
				}
			}

			$text .= "</div>
			</td>
			</tr>\n";

			$text .= "<tr>
			<td class='forumheader3'>Images</td>
			<td class='forumheader3'>
			<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_38."</a>
			<div style='display: none;'>
			<br />
			";
			if(!count($imagelist))
			{
				$text .= LAN_NEWS_43;
			}
			else
			{
				$text .= LAN_NEWS_39."<br /><br />";
				foreach($imagelist as $image)
				{
					if(strstr($image['fname'], "thumb"))
					{
						$fi = str_replace("thumb_", "", $image['fname']);
						if(file_exists(e_IMAGE."newspost_images/".$fi))
						{
							// thumb and main image found
							$text .= "<a href='javascript:addtext(\"[link=".$IMAGES_DIRECTORY."newspost_images/".$fi."][img]{E_IMAGE}newspost_images/".$image['fname']."[/img][/link]\");'><img src='".e_IMAGE."generic/".IMODE."/image.png' alt='' style='border:0px;vertical-align:middle;' /> ".$image['fname']."</a> (link to full image will be generated)<br />
							";
						}
						else
						{
							$text .= "<a href='javascript:addtext(\"[img]{E_IMAGE}newspost_images/".$image['fname']."[/img]\");'><img src='".e_IMAGE."generic/".IMODE."/image.png' alt='' style='border:0px;vertical-align:middle;' /> ".$image['fname']."</a><br />
							";
						}
					}
					else
					{
						$text .= "<a href='javascript:addtext(\"[img]{E_IMAGE}newspost_images/".$image['fname']."[/img]\");'><img src='".e_IMAGE."generic/".IMODE."/image.png' alt='' style='border:0px;vertical-align:middle;' /> ".$image['fname']."</a><br />
						";
					}
				}
			}

			$text .= "</div>
			</td>
			</tr>\n";


			/*
			<input class='tbox' type='text' name='news_image' size='60' value='".$_POST['news_image']."' maxlength='100' />
			<input class='button' type ='button' style='cursor:hand' size='30' value='View images' onclick='expandit(this)' />
			<div id='imagefile' style='display:none;{head}'>";

			$text .= "Multiple images can be added. Once an image has been selected, type {NEWSIMAGE=imagenumber} into your text to display it, eg {NEWSIMAGE=1}, {NEWSIMAGE=2).<br /><br />";

			foreach($imagelist as $file){
			$text .= "<a href=\"javascript:appendtext('".$file['fname']."|','news_image','null')\">".$file['fname']."</a><br />";
			}
			*/


			$text .= "</div>
			</td>
			</tr>\n";
		}

		$text .= "<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_15."</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_18."</a>
		<div style='display: none;'>

		". ($_POST['news_allow_comments'] ? "<input name='news_allow_comments' type='radio' value='0' />".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' checked='checked' />".NWSLAN_17 : "<input name='news_allow_comments' type='radio' value='0' checked='checked' />".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' />".NWSLAN_17)."
		</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_73.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_74."</a>
		<div style='display: none;'>";
		$ren_type = array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77." 2");
		foreach($ren_type as $key=>$value) {
			$checked = ($_POST['news_rendertype'] == $key) ? "checked='checked'" : "";
			$text .= "<input name='news_rendertype' type='radio' value='$key' $checked />";
			$text .= $value."<br />";
		}

		$text .="</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_19.":</td>
		<td style='width:80%' class='forumheader3'>

		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_72."</a>
		<div style='display: none;'>

		<br />
		".NWSLAN_21.":<br />";

		$_startdate = ($_POST['news_start'] > 0) ? date("d/m/Y", $_POST['news_start']) : "";

		$cal_options['firstDay'] = 0;
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_start";
		$cal_attrib['value'] = $_startdate;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);

		$text .= " - ";

		$_enddate = ($_POST['news_end'] > 0) ? date("d/m/Y", $_POST['news_end']) : "";

		unset($cal_options);
		unset($cal_attrib);
		$cal_options['firstDay'] = 0;
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_end";
		$cal_attrib['value'] = $_enddate;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);

		$text .= "</select>
		</div>
		</td>
		</tr>";
		$text .="<tr>
		<td class='forumheader3'>
		".LAN_NEWS_32.":
		</td>
		<td class='forumheader3'>
		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".LAN_NEWS_33."</a>
		<div style='display: none;'>";
		$update_checked = ($_POST['update_datestamp']) ? "checked='checked'" : "";

		$_update_datestamp = ($_POST['news_datestamp'] > 0) ? date("d/m/Y H:i:s", $_POST['news_datestamp']) : "";
		unset($cal_options);
		unset($cal_attrib);
		$cal_options['firstDay'] = 0;
		$cal_options['showsTime'] = true;
		$cal_options['showOthers'] = true;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y %H:%M:%S";
		$cal_options['timeFormat'] = "24";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['name'] = "news_datestamp";
		$cal_attrib['value'] = $_update_datestamp;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);

		$text .= "<br />
		<input type='checkbox' value='1' name='update_datestamp' $update_checked />".NWSLAN_105."
		</div>
		</td></tr>";

		// -------- end of datestamp ---------------------

		$text .="	<tr>
		<td class='forumheader3'>
		".NWSLAN_22.":
		</td>
		<td class='forumheader3'>

		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_84."</a>
		<div style='display: none;'>
		".r_userclass_check("news_userclass", $_POST['news_class'], "nobody,public,guest,member,admin,classes")."
		</div>
		</td></tr>


		<tr>
		<td class='forumheader3'>
		".LAN_NEWS_28.":
		</td>
		<td class='forumheader3'>

		<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".LAN_NEWS_29."</a>
		<div style='display: none;'>
		";
		if($_POST['news_sticky'])
		{
			$sel = " checked='checked' ";
		}
		else
		{
			$sel = "";
		}
		$text .= "<input type='checkbox' {$sel} name='news_sticky' value='1' /> ".LAN_NEWS_30."\n</div>\n</td>\n</tr>\n";

		if($pref['trackbackEnabled']){
			$text .= "<tr>
			<td class='forumheader3'>".LAN_NEWS_34.":</td>
			<td class='forumheader3'><a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".LAN_NEWS_35."</a>
			<div style='display: none;'>
			<span class='smalltext'>";
			/* pingback */
			//	<input type='checkbox' name='pingback_urls' ".($_POST['pingback_urls'] ? " checked='checked'" : "")." />".LAN_NEWS_36."<br />
			$text .= LAN_NEWS_37."</span><br />
			<textarea class='tbox' name='trackback_urls' style='width:95%;height:100px'>".$_POST['trackback_urls']."</textarea>
			</div>
			</td>
			</tr>\n";
		}


		$text .= "<tr style='vertical-align: top;'>
		<td colspan='2'  style='text-align:center' class='forumheader'>";

		if (isset($_POST['preview'])) {
			$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> ";
			if ($id && $sub_action != "sn" && $sub_action != "upload") {
				$text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_25."' /> ";
				//	$text .= "<br /><span class='smalltext'><input type='checkbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
				} else {
				$text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_26."' /> ";
			}
			} else {
			$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_27."' />";
		}
		$text .= "<input type='hidden' name='news_id' value='$news_id' />  \n</td>
		</tr>
		</table>

		</form>
		</div>";
		$ns->tablerender(NWSLAN_29, $text);
	}


	function preview_item($id) {
		// ##### Display news preview ---------------------------------------------------------------------------------------------------------
		global $tp, $sql, $ix, $IMAGES_DIRECTORY;
		$_POST['news_id'] = $id;
		print_a($_POST);
//		print_a($_POST['news_userclass']);

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		$sql->db_Select("news_category", "*", "category_id='".$_POST['cat_id']."' ");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql->db_Fetch();
		$_POST['user_id'] = USERID;
		$_POST['user_name'] = USERNAME;
		$_POST['comment_total'] = $comment_total;
		$_PR = $_POST;

		$_PR['data'] = str_replace($IMAGES_DIRECTORY,"../".$IMAGES_DIRECTORY,$_PR['data']);
		$_PR['news_extended'] = str_replace($IMAGES_DIRECTORY,"../".$IMAGES_DIRECTORY,$_PR['news_extended']);
		$_PR['news_title'] = $tp->post_toHTML($_PR['news_title']);
		$_PR['news_summary'] = $tp->post_toHTML($_PR['news_summary']);
		$_PR['data'] = $tp->post_toHTML($_PR['data'], FALSE);
		$_PR['news_extended'] = $tp->post_toHTML($_PR['news_extended']);
		$_PR['news_body'] = (strstr($_PR['data'], "[img]http") ? $_PR['data'] : str_replace("[img]", "[img]../", $_PR['data']));

		$_PR['news_file'] = $_POST['news_file'];
		$_PR['news_image'] = $_POST['news_image'];

		$ix -> render_newsitem($_PR);
		// echo $ix -> news_info();
		echo $tp -> parseTemplate('{NEWSINFO}', FALSE, $news_shortcodes);
	}

	function submit_item($sub_action, $id) {
		// ##### Format and submit item ---------------------------------------------------------------------------------------------------------
		global $tp, $ix, $sql;
		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$_POST['update_datestamp'] = 0;

		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}
		$_POST['admin_id'] = USERID;
		$_POST['admin_name'] = USERNAME;

		if ($id && $sub_action != "sn" && $sub_action != "upload")
		{
			$_POST['news_id'] = $id;
		}
		else
		{
			$sql->db_Update("submitnews", "submitnews_auth='1' WHERE submitnews_id ='".$id."' ");
		}
		if (!$_POST['cat_id']) {
			$_POST['cat_id'] = 1;
		}
		$this->show_message($ix->submit_item($_POST));
		unset($_POST['news_title'], $_POST['cat_id'], $_POST['data'], $_POST['news_extended'], $_POST['news_allow_comments'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['news_id'], $_POST['news_class']);
	}

	function show_message($message) {
		// ##### Display comfort ---------------------------------------------------------------------------------------------------------
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_categories($sub_action, $id) {
		// ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------
		global $sql, $rs, $ns, $tp;
		$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
		if ($category_total = $sql->db_Select("news_category")) {
			$text .= "
			<form action='".e_SELF."?cat' id='newscatform' method='post'>
			<table class='fborder' style='width:99%'>
			<tr>
			<td style='width:5%' class='fcaption'>".LAN_NEWS_45."</td>
			<td style='width:5%' class='fcaption'>&nbsp;</td>
			<td style='width:70%' class='fcaption'>".NWSLAN_6."</td>
			<td style='width:20%; text-align:center' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
			while ($row = $sql->db_Fetch()) {
				extract($row);

				if ($category_icon) {
					$icon = (strstr($category_icon, "images/") ? THEME."$category_icon" : e_IMAGE."icons/$category_icon");
				}

				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader3'>{$category_id}</td>
				<td style='width:5%; text-align:center' class='forumheader3'><img src='$icon' alt='' style='vertical-align:middle' /></td>
				<td style='width:70%' class='forumheader3'>$category_name</td>
				<td style='width:20%; text-align:center' class='forumheader3'>
				<a href='".e_SELF."?cat.edit.{$category_id}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[category_{$category_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(NWSLAN_37." [ID: $category_id ]")."') \"/>
				</td>
				</tr>\n";
			}
			$text .= "</table></form>";
			} else {
			$text .= "<div style='text-align:center'><div style='vertical-align:center'>".NWSLAN_10."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(NWSLAN_51, $text);

		$handle = opendir(e_IMAGE."icons");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && $file != "/" && $file != "null.txt" && $file != "CVS") {
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		unset($category_name, $category_icon);

		if ($sub_action == "edit") {
			if ($sql->db_Select("news_category", "*", "category_id='$id' ")) {
				$row = $sql->db_Fetch();
				extract($row);
			}
		}

		$text = "<div style='text-align:center'>
		".$rs->form_open("post", e_SELF."?cat", "dataform")."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_52."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs->form_text("category_name", 30, $category_name, 200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_53."</span></td>
		<td class='forumheader3' style='width:70%'>
		".$rs->form_text("category_button", 60, $category_icon, 100)."
		<br />
		<input class='button' type ='button' style='cursor:hand' size='30' value='".NWSLAN_54."' onclick='expandit(this)' />
		<div id='caticn' style='display:none'>";
		while (list($key, $icon) = each($iconlist)) {
			$text .= "<a href=\"javascript:insertext('$icon','category_button','caticn')\"><img src='".e_IMAGE."icons/".$icon."' style='border:0' alt='' /></a>\n ";
		}
		$text .= "</div></td>
		</tr>

		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id) {
			$text .= "<input class='button' type='submit' name='update_category' value='".NWSLAN_55."' />
			".$rs->form_button("submit", "category_clear", NWSLAN_79). $rs->form_hidden("category_id", $id)."
			</td></tr>";
			} else {
			$text .= "<input class='button' type='submit' name='create_category' value='".NWSLAN_56."' /></td></tr>";
		}
		$text .= "</table>
		".$rs->form_close()."
		</div>";

		$ns->tablerender(NWSLAN_56, $text);
	}

	function show_news_prefs() {
		global $sql, $rs, $ns, $pref;

		$text = "<div style='text-align:center'>
		".$rs->form_open("post", e_SELF."?pref", "dataform")."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_86."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_cats' value='1' ".($pref['news_cats'] == 1 ? " checked='checked'" : "")." />
		</td>

		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_87."</span></td>
		<td class='forumheader3' style='width:40%'>
		<select class='tbox' name='nbr_cols'>
		<option value='1' ".($pref['nbr_cols'] == 1 ? "selected='selected'>" : "").">1</option>
		<option value='2' ".($pref['nbr_cols'] == 2 ? "selected='selected'>" : "").">2</option>
		<option value='3' ".($pref['nbr_cols'] == 3 ? "selected='selected'>" : "").">3</option>
		<option value='4' ".($pref['nbr_cols'] == 4 ? "selected='selected'>" : "").">4</option>
		<option value='5' ".($pref['nbr_cols'] == 5 ? "selected='selected'>" : "").">5</option>
		<option value='6' ".($pref['nbr_cols'] == 6 ? "selected='selected'>" : "").">6</option>
		</select></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_88."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:30px' name='newsposts' value='".$pref['newsposts']."' />
		</td>
		</tr>";

		//			<tr>
		//			<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_108."</span><br /><i>".NWSLAN_109."</i></td>
		//			<td class='forumheader3' style='width:40%'>
		//			<input type='checkbox' name='subnews_hide_news' value='1' ".($pref['subnews_hide_news'] == 1 ? " checked='checked'" : "")." />
		//			</td>
		//			</tr>";





		// ##### ADDED FOR NEWSARCHIVE --------------------------------------------------------------------
		// the possible archive values are from "0" to "< $pref['newsposts']"
		// this should really be made as an onchange event on the selectbox for $pref['newsposts'] ...
		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_115."</span><br />
		<span class='defaulttext'><i>".NWSLAN_116."</i></span>
		</td>
		<td class='forumheader3' style='width:40%'>
		<select class='tbox' name='newsposts_archive'>";
		for($i = 0; $i < $pref['newsposts']; $i++) {
			$text .= ($i == $pref['newsposts_archive'] ? "<option value='".$i."' selected='selected'>".$i."</option>" : " <option value='".$i."'>".$i."</option>");
		}
		$text .= "</select></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_117."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:150px' name='newsposts_archive_title' value='".$pref['newsposts_archive_title']."' />
		</td>
		</tr>
		";
		// ##### END --------------------------------------------------------------------------------------


		require_once(e_HANDLER."userclass_class.php");
		$text .= " <tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_106."</span></td>
		<td class='forumheader3' style='width:40%'>
		".r_userclass("subnews_class", $pref['subnews_class']). "</td></tr>";


		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_107."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='subnews_htmlarea' value='1' ".($pref['subnews_htmlarea'] == 1 ? " checked='checked'" : "")." />
		</td>
		</tr>";


		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_100."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='subnews_attach' value='1' ".($pref['subnews_attach'] == 1 ? " checked='checked'" : "")." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_101."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:50px' name='subnews_resize' value='".$pref['subnews_resize']."' />
		<span class='smalltext'>".NWSLAN_102."</span></td>
		</tr>


		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_111."</span><br /><i>".NWSLAN_112."</i></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_newdateheader' value='1' ".($pref['news_newdateheader'] == 1 ? " checked='checked'" : "")." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_113."</span><br /><i>".NWSLAN_114."</i></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_unstemplate' value='1' ".($pref['news_unstemplate'] == 1 ? " checked='checked'" : "")." />
		</td>
		</tr>


		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		$text .= "<input class='button' type='submit' name='save_prefs' value='".NWSLAN_89."' /></td></tr>";

		$text .= "</table>
		".$rs->form_close()."
		</div>";

		$ns->tablerender(NWSLAN_90, $text);
	}




	function submitted_news($sub_action, $id) {
		global $sql, $rs, $ns, $tp;
		$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 300px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
		if ($category_total = $sql->db_Select("submitnews", "*", "submitnews_id !='' ORDER BY submitnews_id DESC")) {
			$text .= "<table class='fborder' style='width:99%'>
			<tr>
			<td style='width:5%' class='fcaption'>ID</td>
			<td style='width:70%' class='fcaption'>".NWSLAN_57."</td>
			<td style='width:25%; text-align:center' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
			while ($row = $sql->db_Fetch()) {
				extract($row);
				$text .= "<tr>
				<td style='width:5%; text-align:center; vertical-align:top' class='forumheader3'>$submitnews_id</td>
				<td style='width:70%' class='forumheader3'>";
				$text .= ($submitnews_auth == 0)? "<b>".$tp->toHTML($submitnews_title)."</b>":
				$tp->toHTML($submitnews_title);
				$text .= " [ ".NWSLAN_104." $submitnews_name on ".date("D dS M y, g:ia", $submitnews_datestamp)."]<br />".$tp->toHTML($submitnews_item)."</td>
				<td style='width:25%; text-align:right; vertical-align:top' class='forumheader3'>";
				$buttext = ($submitnews_auth == 0)? NWSLAN_58 :
				NWSLAN_103;
				$text .= $rs->form_open("post", e_SELF."?sn", "myform__{$submitnews_id}", "", "", " onsubmit=\"return jsconfirm('".$tp->toJS(NWSLAN_38." [ID: $submitnews_id ]")."')\"   ")
				."<div>".$rs->form_button("button", "category_edit_{$submitnews_id}", $buttext, "onclick=\"document.location='".e_SELF."?create.sn.$submitnews_id'\"")."
				".$rs->form_button("submit", "sn_delete_{$submitnews_id}", LAN_DELETE)."
				</div>".$rs->form_close()."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
			} else {
			$text .= "<div style='text-align:center'>".NWSLAN_59."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(NWSLAN_60, $text);

	}

}

function newspost_adminmenu() {
	global $newspost;
	global $action;
	$newspost->show_options($action);
}

?>