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
|   $Revision: 1.20 $
|   $Date: 2005-01-29 03:53:16 $
|   $Author: mcfly_e107 $
+---------------------------------------------------------------+
	
*/
require_once("../class2.php");
if (!is_object($tp)) $tp = new e_parse;
	
if ($pref['htmlarea']) {
	require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
	$htmlarea_js = (eregi("MSIE", $_SERVER['HTTP_USER_AGENT']))? htmlarea("data,news_extended"):
	 htmlarea("data");
}
if (!getperms("H")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'news';
	
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."form_handler.php");
	
$rs = new form;
$ix = new news;
$newspost = new newspost;
	
$deltest = array_flip($_POST);
if (e_QUERY) {
	list($action, $sub_action, $id, $from) = explode(".", e_QUERY);
	unset($tmp);
}
	
$from = ($from ? $from : 0);
$amount = 50;
	
// ##### Main loop -----------------------------------------------------------------------------------------------------------------------
	
if (preg_match("#(.*?)_delete_(\d+)#", $deltest[$tp->toJS(NWSLAN_8)], $matches)) {
	$delete = $matches[1];
	$del_id = $matches[2];
}
	
if ($delete == "main" && $del_id) {
	if ($sql->db_Delete("news", "news_id='$del_id' ")) {
		$e_event->trigger("newsdel", $del_id);
		$newspost->show_message(NWSLAN_31." #".$del_id." ".NWSLAN_32);
		$e107cache->clear("news.php");
		$ix->create_rss();
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
	$uploaded = file_upload(($_POST['uploadtype'] == NWSLAN_67 ? e_IMAGE."newspost_images/" : e_FILE."downloads/"));
	if ($_POST['uploadtype'] == "Image" && $_POST['imagecrethumb']) {
		require_once(e_HANDLER."resize_handler.php");
		resize_image(e_IMAGE."newspost_images/".$uploaded[0]['name'], e_IMAGE."newspost_images/".$uploaded[0]['name'], 250, "copy");
	}
}
	
$_POST['news_title'] = $tp->toDB($_POST['news_title']);
// required.
if (IsSet($_POST['preview'])) {
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
			$handle = opendir(e_IMAGE."newsicons");
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
	 
	 
	save_prefs();
	$e107cache->clear("news.php");
	$newspost->show_message("Settings Saved");
}
	
	
	
	
if (!e_QUERY || $action == "main") {
	$newspost->show_existing_items($action, $sub_action, $id, $from, $amount);
}
	
if ($action == "create") {
	if ($sub_action == "edit" && !$_POST['preview'] && !$_POST['submit']) {
		if ($sql->db_Select("news", "*", "news_id='$id' ")) {
			$row = $sql->db_Fetch();
			extract($row);
			$_POST['news_title'] = $news_title;
			$_POST['data'] = $news_body;
			$_POST['news_extended'] = $news_extended;
			$_POST['news_allow_comments'] = $news_allow_comments;
			$_POST['news_class'] = $news_class;
			$_POST['cat_id'] = $news_category;
			if ($news_start) {
				$tmp = getdate($news_start);
				$_POST['startmonth'] = $tmp['mon'];
				$_POST['startday'] = $tmp['mday'];
				$_POST['startyear'] = $tmp['year'];
			}
			if ($news_end) {
				$tmp = getdate($news_end);
				$_POST['endmonth'] = $tmp['mon'];
				$_POST['endday'] = $tmp['mday'];
				$_POST['endyear'] = $tmp['year'];
			}
			$_POST['comment_total'] = $sql->db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
			$_POST['news_rendertype'] = $news_render_type;
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
	
// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
// ##### Display options --------------------------------------------------------------------------------------------------------------------------------------------------------
	
//$newspost->show_options($action);
	
// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
print "<script type=\"text/javascript\">
	 
	 
	function addtext3(str){
	document.getElementById('dataform').category_button.value = str;
	}
	function help2(help){
	document.getElementById('dataform').help_ext.value = help;
	}
	function fclear(){
	document.getElementById('dataform').data.value = \"\";
	document.getElementById('dataform').news_extended.value = \"\";
	}
	 
	</script>\n";
	
echo "<script type=\"text/javascript\">
	function confirm_(mode, news_id){
	if (mode == 'cat'){
	return confirm(\"".$tp->toJS(NWSLAN_37)." [ID: \" + news_id + \"]\");
	}else if(mode == 'sn'){
	return confirm(\"".$tp->toJS(NWSLAN_38)." [ID: \" + news_id + \"]\");
	}else{
	return confirm(\"".$tp->toJS(NWSLAN_39)." [ID: \" + news_id + \"]\");
	}
	}
	</script>";
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
		 
		if ($sql->db_Select("news", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere"))) {
			$text .= "<table class='fborder' style='width:99%'>
				<tr>
				<td style='width:5%' class='fcaption'><a href='".e_SELF."?main.news_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></td>
				<td style='width:5%' class='fcaption'><a href='".e_SELF."?main.news_title.".($id == "desc" ? "asc" : "desc").".$from'>".NWSLAN_40."</a></td>
				<td style='width:45%' class='fcaption'>".NWSLAN_41."</td>
				</tr>";
			while ($row = $sql->db_Fetch()) {
				extract($row);
				 
				// Note: To fix the alignment bug. Put both buttons inside the Form.
				// But make EDIT a 'button' and DELETE 'submit'
				 
				$text .= "<tr>
					<td style='width:5%' class='forumheader3'>$news_id</td>
					<td style='width:75%' class='forumheader3'><a href='".e_BASE."comment.php?comment.news.$news_id'>".($news_title ? $tp->toHTML($news_title) : "[".NWSLAN_42."]")."</a></td>
					<td style='width:20%; text-align:center' class='forumheader3'>
					".$rs->form_open("post", e_SELF, "myform__{$news_id}", "", "", " onsubmit=\"return confirm_('create',$news_id)\"")."
					<div>".$rs->form_button("button", "main_edit_{$news_id}", NWSLAN_7, "onclick=\"document.location='".e_SELF."?create.edit.$news_id'\"")."
					".$rs->form_button("submit", "main_delete_{$news_id}", NWSLAN_8)."
					</div>".$rs->form_close()."
					</td>
					</tr>";
			}
			$text .= "</table>";
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
		 
		 
		 
		$ns->tablerender(NWSLAN_43, $text);
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
	 
	function create_item($sub_action, $id) {
		// ##### Display creation form ---------------------------------------------------------------------------------------------------------
		/* 08-08-2004 - unknown - fixed `Insert Image' display to use $IMAGES_DIRECTORY */
		global $sql, $rs, $ns, $pref, $IMAGES_DIRECTORY, $tp;
		 
		$handle = opendir(e_IMAGE."newspost_images");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && $file != "/" && $file != "index.html" && $file != "null.txt" && $file != "CVS") {
				if (!strstr($file, "thumb_")) {
					$imagelist[] = $file;
				} else {
					$thumblist[] = $file;
				}
			}
		}
		closedir($handle);
		 
		$sql->db_Select("download");
		$c = 0;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$filelist[$c][0] = $download_id;
			$filelist[$c][1] = $download_url;
			$c++;
		}
		 
		 
		$handle = opendir(e_FILE."downloads");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && $file != "/" && $file != "index.html" && $file != "null.txt" && $file != "CVS") {
				$filelist[$c][0] = "";
				$filelist[$c][1] = $file;
				$c++;
			}
		}
		closedir($handle);
		 
		if ($sub_action == "sn" && !$_POST['preview']) {
			if ($sql->db_Select("submitnews", "*", "submitnews_id=$id", TRUE)) {
				list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['data'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->db_Fetch();
				 
				if ($pref['htmlarea']) {
					$_POST['data'] .= "<br /><b>".NWSLAN_49." ".$submitnews_name."</b>";
					$_POST['data'] .= ($submitnews_file)? "<br /><br /><img src='".e_IMAGE."newspost_images/$submitnews_file' style='float:right; margin-left:5px;margin-right:5px;margin-top:5px;margin-bottom:5px; border:1px solid' />":
					"";
				} else {
					$_POST['data'] .= "\n[[b]".NWSLAN_49." ".$submitnews_name."[/b]]";
					$_POST['data'] .= ($submitnews_file)?"\n\n[img]".e_IMAGE."newspost_images/".$submitnews_file." [/img]":
					"";
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
			<input class='tbox' type='text' name='news_title' size='80' value='".$_POST['news_title']."' maxlength='200' />
			</td>
			</tr>
			<tr>
			<td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
			<td style='width:80%' class='forumheader3'>";
		 
		$insertjs = (!$pref['htmlarea'])?"rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'":
		"rows='25' style='width:100%' ";
		$_POST['data'] = $tp->toForm($_POST['data']);
		$text .= "<textarea class='tbox' id='data' name='data'  cols='80'   $insertjs>".(strstr($_POST['data'], "[img]http") ? $_POST['data'] : str_replace("[img]../", "[img]", $_POST['data']))."</textarea>
			";
		//Main news body textarea
		if (!$pref['htmlarea']) {
			$text .= "
				<input class='helpbox' type='text' name='helpb' size='100' />
				<br />". ren_help()."
				<select class='tbox' name='thumbps' onChange=\"addtext('[link=".e_IMAGE."newspost_images/' + this.form.thumbps.options[this.form.thumbps.selectedIndex].value + '][img]".e_IMAGE."newspost_images/thumb_' + this.form.thumbps.options[this.form.thumbps.selectedIndex].value + '[/img][/link]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_50."')\" onMouseOut=\"help('')\">
				<option>".NWSLAN_80." ...</option>\n";
			while (list($key, $image) = each($thumblist)) {
				$image2 = str_replace("thumb_", "", $image);
				$text .= "<option value='".$image2."'>thumb_".$image2."</option>\n";
			}
			$text .= "</select>
				 
				<select class='tbox' name='imageps' onChange=\"addtext('[img]' + this.form.imageps.options[this.form.imageps.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_110."')\" onMouseOut=\"help('')\">
				<option>".NWSLAN_81." ...</option>\n";
			while (list($key, $image) = each($imagelist)) {
				$text .= "<option value='".SITEURL.$IMAGES_DIRECTORY."newspost_images/".$image."'>".$image."</option>\n";
			}
			$text .= "</select>
				 
				<select class='tbox' name='fileps' onChange=\"addtext('[file=request.php?' + this.form.fileps.options[this.form.fileps.selectedIndex].value + ']' + this.form.fileps.options[this.form.fileps.selectedIndex].value + '[/file]');this.selectedIndex=0;\" onMouseOver=\"help('".NWSLAN_64."')\" onMouseOut=\"help('')\">
				<option>".NWSLAN_82." ...</option>\n";
			while (list($key, $file) = each($filelist)) {
				$text .= "<option value='".$file[1]."'>".$file[1]."</option>\n";
			}
			$text .= "</select>";
			 
		} // end of htmlarea check.
		//Extended news form textarea
		$text .= "
			</td>
			</tr>
			<tr>
			<td style='width:20%' class='forumheader3'>".NWSLAN_14.":</td>
			<td style='width:80%' class='forumheader3'>
			<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_83."</a>
			<div style='display: none;'>
			<textarea class='tbox' id='news_extended' name='news_extended' cols='80' rows='15' style='width:100%;height:50px' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".(strstr($_POST['news_extended'], "[img]http") ? $_POST['news_extended'] : str_replace("[img]../", "[img]", $_POST['news_extended']))."</textarea>
			";
		if ($pref['htmlarea'] && ($pref['htmlarea'] && !eregi("MSIE", $_SERVER['HTTP_USER_AGENT']))) {
			$text .= "<br />
				<input class='helpbox' type='text' name='help_ext' size='100' />
				<br />
				".ren_help("", "addtext", "help2")."
				<select class='tbox' name='imageps2' onChange=\"addtext('[img]' + this.form.imageps2.options[this.form.imageps2.selectedIndex].value + '[/img]');this.selectedIndex=0;\" onMouseOver=\"help2('".NWSLAN_110."')\" onMouseOut=\"help2('')\">
				<option>".NWSLAN_81." ...</option>\n";
			reset($imagelist);
			while (list($key, $image) = each($imagelist)) {
				$text .= "<option value='".SITEURL.$IMAGES_DIRECTORY."newspost_images/".$image."'>".$image."</option>\n";
			}
			$text .= "</select>";
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
		 
		if (!FILE_UPLOADS) {
			$text .= "<b>".NWSLAN_78."</b>";
		} else {
			 
			if (!is_writable(e_FILE."downloads")) {
				$text .= "<b>".NWSLAN_70."</b><br />";
			}
			if (!is_writable(e_IMAGE."newspost_images")) {
				$text .= "<b>".NWSLAN_71."</b><br />";
			}
			 
			$text .= "<input class='tbox' type='file' name='file_userfile[]' size='50' />
				<select class='tbox' name='uploadtype'>
				<option>".NWSLAN_67."</option>
				<option>".NWSLAN_68."</option>
				</select>
				<br />
				<input type='checkbox' name='imagecrethumb' value='1' /><span class='smalltext'>".NWSLAN_65."</span>&nbsp;&nbsp;
				<input class='button' type='submit' name='submitupload' value='".NWSLAN_66."' />\n";
		}
		$text .= "</div>
			</td>
			</tr>
			 
			 
			 
			 
			<tr>
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
			<div style='display: none;'>".  
		(!$_POST['news_rendertype'] ? "<input name='news_rendertype' type='radio' value='0' checked='checked' />" : "<input name='news_rendertype' type='radio' value='0' />").NWSLAN_75."<br />". ($_POST['news_rendertype'] == 1 ? "<input name='news_rendertype' type='radio' value='1' checked='checked' />" : "<input name='news_rendertype' type='radio' value='1' />").NWSLAN_76."<br />". ($_POST['news_rendertype'] == 2 ? "<input name='news_rendertype' type='radio' value='2' checked='checked' />" : "<input name='news_rendertype' type='radio' value='2' />").NWSLAN_77."
			 
			</div>
			</td>
			</tr>
			 
			 
			<tr>
			<td style='width:20%' class='forumheader3'>".NWSLAN_19.":</td>
			<td style='width:80%' class='forumheader3'>
			 
			<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_72."</a>
			<div style='display: none;'>
			 
			<br />
			 
			 
			".NWSLAN_21.": <select name='startday' class='tbox'><option selected='selected'> </option>";
		for($a = 1; $a <= 31; $a++) {
			$text .= ($a == $_POST['startday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='startmonth' class='tbox'><option selected='selected'> </option>";
		for($a = 1; $a <= 12; $a++) {
			$text .= ($a == $_POST['startmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='startyear' class='tbox'><option selected='selected'> </option>";
		for($a = 2003; $a <= 2010; $a++) {
			$text .= ($a == $_POST['startyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> ".NWSLAN_83." <select name='endday' class='tbox'><option selected='selected'> </option>";
		for($a = 1; $a <= 31; $a++) {
			$text .= ($a == $_POST['endday'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='endmonth' class='tbox'><option selected='selected'> </option>";
		for($a = 1; $a <= 12; $a++) {
			$text .= ($a == $_POST['endmonth'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		$text .= "</select> <select name='endyear' class='tbox'><option selected='selected'> </option>";
		for($a = 2003; $a <= 2010; $a++) {
			$text .= ($a == $_POST['endyear'] ? "<option selected='selected'>".$a."</option>" : "<option>".$a."</option>");
		}
		 
		$text .= "</select>
			</div>
			</td>
			</tr>
			 
			<tr>
			<td class='forumheader3'>
			".NWSLAN_22.":
			</td>
			<td class='forumheader3'>
			 
			<a style='cursor: pointer; cursor: hand' onclick='expandit(this);'>".NWSLAN_84."</a>
			<div style='display: none;'>
			".r_userclass("news_class", $_POST['news_class'])."
			</div>
			</td></tr>
			 
			<tr style='vertical-align: top;'>
			<td colspan='2'  style='text-align:center' class='forumheader'>";
		 
		if (IsSet($_POST['preview'])) {
			$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> ";
			if ($id && $sub_action != "sn" && $sub_action != "upload") {
				$text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_25."' /> ";
				$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
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
		global $tp, $sql, $ix;
		$_POST['news_id'] = $id;
		$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$sql->db_Select("news_category", "*", "category_id='".$_POST['cat_id']."' ");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql->db_Fetch();
		$_POST['admin_id'] = USERID;
		$_POST['admin_name'] = USERNAME;
		$_POST['comment_total'] = $comment_total;
		$_POST['news_datestamp'] = time();
		$_PR = $_POST;
		$_PR['news_title'] = $tp->post_toHTML($_PR['news_title']);
		$_PR['data'] = $tp->post_toHTML($_PR['data']);
		$_PR['news_extended'] = $tp->post_toHTML($_PR['news_extended']);
		$_POST['news_title'] = $tp->post_toForm($_POST['news_title']);
		$_POST['data'] = $tp->post_toForm($_POST['data']);
		$_POST['news_extended'] = $tp->post_toForm($_POST['news_extended']);
		$_PR['news_body'] = (strstr($_PR['data'], "[img]http") ? $_PR['data'] : str_replace("[img]", "[img]../", $_PR['data']));
		$ix->render_newsitem($_PR);
		$_POST['data'] = $tp->toForm($_POST['data'], TRUE);
		$_POST['news_title'] = $tp->toFORM($_POST['news_title']);
		$_POST['news_extended'] = $tp->toFORM($_POST['news_extended']);
	}
	 
	function submit_item($sub_action, $id) {
		// ##### Format and submit item ---------------------------------------------------------------------------------------------------------
		global $tp, $ix, $sql;
		$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$_POST['admin_id'] = USERID;
		$_POST['admin_name'] = USERNAME;
		$_POST['news_datestamp'] = time();
		if ($id && $sub_action != "sn" && $sub_action != "upload") {
			$_POST['news_id'] = $id;
		} else {
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
			$text .= "<table class='fborder' style='width:99%'>
				<tr>
				<td style='width:5%' class='fcaption'>&nbsp;</td>
				<td style='width:75%' class='fcaption'>".NWSLAN_6."</td>
				<td style='width:20%; text-align:center' class='fcaption'>".NWSLAN_41."</td>
				</tr>";
			while ($row = $sql->db_Fetch()) {
				extract($row);
				 
				if ($category_icon) {
					$icon = (strstr($category_icon, "images/") ? THEME."$category_icon" : e_IMAGE."newsicons/$category_icon");
				}
				 
				$text .= "<tr>
					<td style='width:5%; text-align:center' class='forumheader3'><img src='$icon' alt='' style='vertical-align:middle' /></td>
					<td style='width:75%' class='forumheader3'>$category_name</td>
					<td style='width:20%; text-align:center' class='forumheader3'>
					".$rs->form_open("post", e_SELF."?cat", "myform__{$category_id}", "", "", " onsubmit=\"return confirm_('cat',$category_id)\"")."
					<div>".$rs->form_button("button", "category_edit_{$category_id}", NWSLAN_7, "onclick=\"document.location='".e_SELF."?cat.edit.$category_id'\"")."
					".$rs->form_button("submit", "category_delete_{$category_id}", NWSLAN_8)."
					</div>".$rs->form_close()."
					 
					 
					</td>
					</tr>\n";
			}
			$text .= "</table>";
		} else {
			$text .= "<div style='text-align:center'><div style='vertical-align:center'>".NWSLAN_10."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(NWSLAN_51, $text);
		 
		$handle = opendir(e_IMAGE."newsicons");
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
			<div style='display:none'>";
		while (list($key, $icon) = each($iconlist)) {
			$text .= "<a href='javascript:addtext3(\"$icon\")'><img src='".e_IMAGE."newsicons/".$icon."' style='border:0' alt='' /></a>\n ";
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
			<select class='tbox' name='newsposts'>
			<option value='1' ".($pref['newsposts'] == 1 ? "selected='selected'" : "").">1</option>
			<option value='2' ".($pref['newsposts'] == 2 ? "selected='selected'" : "").">2</option>
			<option value='3' ".($pref['newsposts'] == 3 ? "selected='selected'" : "").">3</option>
			<option value='5' ".($pref['newsposts'] == 5 ? "selected='selected'" : "").">5</option>
			<option value='10' ".($pref['newsposts'] == 10 ? "selected='selected'" : "").">10</option>
			<option value='15' ".($pref['newsposts'] == 15 ? "selected='selected'" : "").">15</option>
			<option value='20' ".($pref['newsposts'] == 20 ? "selected='selected'" : "").">20</option>
			</select></td>
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
			<td class='forumheader3' style='width:60%'><span class='defaulttext'>News posts to display in archive ?</span><br />
			<span class='defaulttext'><i>First update the preferences with the changed display per page setting, then update again after setting the newsarchive preference. (0 is un-activated)</i></span>
			</td>
			<td class='forumheader3' style='width:40%'>
			<select class='tbox' name='newsposts_archive'>";
		for($i = 0; $i < $pref['newsposts']; $i++) {
			$text .= ($i == $pref['newsposts_archive'] ? "<option value='".$i."' selected='selected'>".$i."</option>" : " <option value='".$i."'>".$i."</option>");
		}
		$text .= "</select></td>
			</tr>
			 
			<tr>
			<td class='forumheader3' style='width:60%'><span class='defaulttext'>set the title for the newsarchive</span></td>
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
				<td style='width:25%; text-align:center' class='fcaption'>".NWSLAN_41."</td>
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
				$text .= $rs->form_open("post", e_SELF."?sn", "myform__{$submitnews_id}", "", "", " onsubmit=\"return confirm_('sn',$submitnews_id)\"")
				."<div>".$rs->form_button("button", "category_edit_{$submitnews_id}", $buttext, "onclick=\"document.location='".e_SELF."?create.sn.$submitnews_id'\"")."
					".$rs->form_button("submit", "sn_delete_{$submitnews_id}", NWSLAN_8)."
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