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
|     $Source: /cvs_backup/e107_0.7/download.php,v $
|     $Revision: 1.10 $
|     $Date: 2005-02-13 21:08:22 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
unset($text);
$agreetext = $pref['agree_text'];
$cobj = new comment;
global $tp;
	
if (!e_QUERY) {
	require_once(HEADERF);
	// no qs - render categories ...
	 
	if (!$DOWNLOAD_CAT_PARENT_TABLE) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}
	 
	$sql = new db;
	 $sql2 = new db;
	if (!$sql->db_Select("download_category", "*", "download_category_parent='0' ")) {
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_2."</div>");
		require_once(FOOTERF);
		exit;
	} else {
		while ($row = $sql->db_Fetch()) {
			extract($row);
			 
			$download_cat_table_string .= parse_download_cat_parent_table($row);
			 
			if (!$categories = $sql2->db_Select("download_category", "*", "download_category_parent='".$download_category_id."' ")) {
				$text .= "<div class='forumheader3' style='text-align:center'>".LAN_dl_3."</div>";
			} else {
				while ($row = $sql2->db_Fetch()) {
					extract($row);
					$download_cat_table_string .= parse_download_cat_child_table($row);
				}
			}
			 
		}
	}
	 
	$download_cat_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_START);
	 
	$DOWNLOAD_CAT_NEWDOWNLOAD_TEXT = "<img src='".e_IMAGE."generic/new.png' alt='' style='vertical-align:middle' /> ".LAN_dl_36;
	$DOWNLOAD_CAT_SEARCH = "
		<form method='post' action='".e_BASE."search.php'>
		<p>
		<input class='tbox' type='text' name='searchquery' size='30' value='' maxlength='50' />
		<input class='button' type='submit' name='searchsubmit' value='".LAN_dl_41."' />
		<input type='hidden' name='searchtype' value='9' />
		</p>
		</form>";
	 
	$download_cat_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_END);
	$text .= $download_cat_table_start.$download_cat_table_string.$download_cat_table_end;
	 
	
	if($DOWNLOAD_CAT_TABLE_RENDERPLAIN) {
		echo $text;
	} else {
		$ns->tablerender(LAN_dl_18.$type, $text);
	}
	require_once(FOOTERF);
	exit;
}
	
	
$tmp = explode(".", e_QUERY);
if (is_numeric($tmp[0])) {
	$from = intval($tmp[0]);
	$action = preg_replace("#\W#", "", $tmp[1]);
	$id = intval($tmp[2]);
	$view = intval($tmp[3]);
	$order = intval($tmp[4]);
	$sort = preg_replace("#\W#", "", $tmp[5]);
} else {
	$action = preg_replace("#\W#", "", $tmp[0]);
	$id = intval($tmp[1]);
}
	
if (isset($_POST['commentsubmit'])) {
	if (!$sql->db_Select("download", "download_comment", "download_id='$id' ")) {
		header("location:".e_BASE."index.php");
		exit;
	} else {
		$row = $sql->db_Fetch();
		if ($row[0] && (ANON === TRUE || USER === TRUE)) {
			$cobj->enter_comment($_POST['author_name'], $_POST['comment'], "download", $id, $pid, $_POST['subject']);
			$e107cache->clear("comment.download.{$sub_action}");
		}
	}
}

//  -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if ($action == "list") {
	 
	if (isset($_POST['view'])) {
		extract($_POST);
	}
	 
	if (!$from) {
		$from = 0;
	}
	if (!$order) {
		$order = ($pref['download_order'] ? $pref['download_order'] : "download_datestamp");
	}
	if (!$sort) {
		$sort = ($pref['download_sort'] ? $pref['download_sort'] : "DESC");
	}
	if (!$view) {
		$view = ($pref['download_view'] ? $pref['download_view'] : "10");
	}
	 
	$total_downloads = $sql->db_Select("download", "*", "download_category='".$id."' AND download_active='1'");
	if (!$total_downloads) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}
	 
	$sql = new db;
	$sql->db_Select("download_category", "*", "download_category_id='".$id."'");
	$row = $sql->db_Fetch();
	extract($row);
	$core_total = $sql->db_Count("download WHERE download_category='".$id."' AND download_active=1");
	$type = $download_category_name;
	
	$type .= ($download_category_description) ? " [ ".$download_category_description." ]" :
	 "";
	define("e_PAGETITLE", PAGE_NAME." / ".$download_category_name);
	 
	require_once(HEADERF);
	if (!check_class($download_category_class)) {
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	$DOWNLOAD_CATEGORY = $download_category_name;
	$DOWNLOAD_CATEGORY_DESCRIPTION = $tp -> toHTML($download_category_description, TRUE);
	 
	if (!$DOWNLOAD_LIST_TABLE) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}
	 
	$gen = new convert;
	require_once(e_HANDLER."rate_class.php");
	$rater = new rater;
	$sql = new db;
	 $sql2 = new db;
	 
	$filetotal = $sql->db_Select("download", "*", "download_category='".$id."' AND download_active='1' ORDER BY $order $sort LIMIT $from, $view");
	$ft = ($filetotal < $view ? $filetotal : $view);
	while ($row = $sql->db_Fetch()) {
		extract($row);
		$download_list_table_string .= parse_download_list_table($row);
		$tdownloads += $download_requested;
	}
	 
	$DOWNLOAD_LIST_TOTAL_AMOUNT = $tdownloads." ".LAN_dl_16;
	$DOWNLOAD_LIST_TOTAL_FILES = $ft." ".LAN_dl_17;
	 
	$download_list_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_LIST_TABLE_START);
	$download_list_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_LIST_TABLE_END);
	$text .= $download_list_table_start.$download_list_table_string.$download_list_table_end;


	if($DOWNLOAD_LIST_TABLE_RENDERPLAIN) {
		echo $text;
	} else {
		$ns->tablerender($type, $text);
	}
	
	 
	echo "<div style='text-align:center;margin-left:auto;margin-right:auto'><a href='".e_SELF."'>".LAN_dl_9."</a></div>";
	 
	require_once(e_HANDLER."np_class.php");
	$ix = new nextprev("download.php", $from, $view, $total_downloads, "Downloads", "list.".$id.".".$view.".".$order.".".$sort);
	require_once(FOOTERF);
	exit;
}
	
	
//  -------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
if ($action == "view") {
	 
	require_once(e_HANDLER."rate_class.php");
	$gen = new convert;
	$rater = new rater;
	$sql2 = new db;
	$highlight_search = FALSE;
	if (isset($_POST['highlight_search'])) {
		$highlight_search = TRUE;
	}
	 
	 
	$sql = new db;
	if (!$sql->db_Select("download", "*", "download_id = {$id} AND download_active = 1")) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}
	 
	$row = $sql->db_Fetch();
	 extract($row);
	$subject = $download_name;
	$sql2->db_Select("download_category", "*", "download_category_id='".$download_category."' ");
	$row = $sql2->db_Fetch();
	extract($row);
	$type = $download_category_name." [ ".$download_category_description." ]";
	define("e_PAGETITLE", PAGE_NAME." / ".$download_category_name." / ".$download_name);
	 
	require_once(HEADERF);
	if (!check_class($download_category_class)) {
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	$DOWNLOAD_REPORT_LINK = "<a href='".e_SELF."?report.$download_id'>".LAN_dl_45."</a>";
	$DOWNLOAD_CATEGORY = $download_category_name;
	$DOWNLOAD_CATEGORY_DESCRIPTION = $tp -> toHTML($download_category_description, TRUE);
	 
	$DOWNLOAD_VIEW_NAME = $download_name;

	$DOWNLOAD_VIEW_NAME_LINKED = "<a href='".e_BASE."request.php?".$download_id."' title='".LAN_dl_46."'>$download_name</a>";

	$DOWNLOAD_VIEW_AUTHOR_LAN = LAN_dl_24;
	$DOWNLOAD_VIEW_AUTHOR = ($download_author ? $download_author : "&nbsp;");
	 
	if ($download_author_email) {
		$DOWNLOAD_VIEW_AUTHOREMAIL_LAN = LAN_dl_30;
		$DOWNLOAD_VIEW_AUTHOREMAIL = str_replace("@", ".at.", $download_author_email);
	}
	 
	if ($download_author_website) {
		$DOWNLOAD_VIEW_AUTHORWEBSITE_LAN = LAN_dl_31;
		$DOWNLOAD_VIEW_AUTHORWEBSITE = $download_author_website;
	}
	 
	$DOWNLOAD_VIEW_DESCRIPTION_LAN = LAN_dl_7;
	$DOWNLOAD_VIEW_DESCRIPTION = $tp->toHTML(($download_description ? $download_description : "&nbsp;"), TRUE);
	 
	if ($download_thumb) {
		$DOWNLOAD_VIEW_IMAGE_LAN = LAN_dl_11;
		$DOWNLOAD_VIEW_IMAGE = ($download_image ? "<a href='".e_FILE."downloadimages/".$download_image."'><img src='".e_FILE."downloadthumbs/".$download_thumb."' alt='' style='border:0' /></a>" : "<img src='".e_FILE."downloadthumbs/".$download_thumb."' alt='' />");
	}
	else if($download_image) {
		$DOWNLOAD_VIEW_IMAGE_LAN = LAN_dl_11;
		$DOWNLOAD_VIEW_IMAGE = "<a href='".e_BASE."request.php?download.".$download_id."'>".LAN_dl_40."</a>";
	}
	 
	if ($pref['agree_flag'] == 1) {
		$dnld_link = "<a href='request.php?".$download_id."' onclick= \"return confirm('$agreetext');\">";
	} else {
		$dnld_link = "<a href='request.php?".$download_id."'>";
	}
	 
	$DOWNLOAD_VIEW_FILESIZE_LAN = LAN_dl_10;
	$DOWNLOAD_VIEW_FILESIZE = parsesize($download_filesize);
	$DOWNLOAD_VIEW_REQUESTED_LAN = LAN_dl_18;
	$DOWNLOAD_VIEW_REQUESTED = $download_requested;
	$DOWNLOAD_VIEW_LINK_LAN = LAN_dl_32;
	$DOWNLOAD_VIEW_LINK = $dnld_link." <img src='".e_IMAGE."generic/download.png' alt='' style='border:0' /></a>";
	$DOWNLOAD_VIEW_RATING_LAN = LAN_dl_12;
	$DOWNLOAD_VIEW_RATING = "
		<table style='width:100%'>
		<tr>
		<td style='width:50%'>";
	 
	if ($ratearray = $rater->getrating("download", $download_id)) {
		for($c = 1; $c <= $ratearray[1]; $c++) {
			$DOWNLOAD_VIEW_RATING .= "<img src='".e_IMAGE."rate/star.png' alt=''>";
		}
		if ($ratearray[2]) {
			$DOWNLOAD_VIEW_RATING .= "<img src='".e_IMAGE."rate/".$ratearray[2].".png'  alt=''>";
		}
		if ($ratearray[2] == "") {
			$ratearray[2] = 0;
		}
		$DOWNLOAD_VIEW_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
		$DOWNLOAD_VIEW_RATING .= ($ratearray[0] == 1 ? LAN_dl_43 : LAN_dl_44);
	} else {
		$DOWNLOAD_VIEW_RATING .= LAN_dl_13;
	}
	$DOWNLOAD_VIEW_RATING .= "</td><td style='width:50%; text-align:right'>";
	 
	if (!$rater->checkrated("download", $download_id) && USER) {
		$DOWNLOAD_VIEW_RATING .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_dl_14, "download", $download_id)."</b>";
	}
	else if(!USER) {
		$DOWNLOAD_VIEW_RATING .= "&nbsp;";
	} else {
		$DOWNLOAD_VIEW_RATING .= LAN_dl_15;
	}
	$DOWNLOAD_VIEW_RATING .= "</td></tr></table>";
	 
	if (!$DOWNLOAD_VIEW_TABLE) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}
	$download_view_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE_START);
	$download_view_table_string = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE);
	$download_view_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE_END);
	$text .= $download_view_table_start.$download_view_table_string.$download_view_table_end;
	 
	$dl_id = $download_id;
	if ($sql->db_Select("download", "*", "download_category='$download_category_id' AND download_id < $dl_id AND download_active = 1 ORDER BY download_datestamp DESC")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$prev = "<a href='".e_SELF."?view.$download_id'>&lt;&lt; ".LAN_dl_33." [$download_name]</a>\n";
	} else {
		$prev = "&nbsp;";
	}
	if ($sql->db_Select("download", "*", "download_category='$download_category_id' AND download_id > $dl_id AND download_active = 1 ORDER BY download_datestamp ASC")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$next = "<a href='".e_SELF."?view.$download_id'>[$download_name] ".LAN_dl_34." &gt;&gt;</a>\n";
	} else {
		$next = "&nbsp;";
	}
	 
	if ($prev || $next) {
		$text .= "
			<table style='width:100%'>
			<tr>
			<td style='width:40%;'>$prev</td>
			<td style='width:20%; text-align: center;'><a href='".e_SELF."?list.$download_category'>".LAN_dl_35."</a></td>
			<td style='width:40%; text-align: right;'>$next</td>
			</tr>
			</table>
			";
	}

	if($DOWNLOAD_VIEW_TABLE_RENDERPLAIN) {
		echo $text;
	} else {
		$ns->tablerender($type, $text);
	}
	unset($text);
	if ($download_comment) {
		$query = ($pref['nested_comments'] ? "comment_item_id='$id' AND comment_type='2' AND comment_pid='0' ORDER BY comment_datestamp" : "comment_item_id='$id' AND comment_type='2' ORDER BY comment_datestamp");
		$comment_total = $sql->db_Select("comments", "*", "".$query."");
		if ($comment_total) {
			$width = 0;
			while ($row = $sql->db_Fetch()) {
				if ($pref['nested_comments']) {
					$text = $cobj->render_comment($row, "download", "comment", $id, $width, $subject);
					$ns->tablerender(LAN_5, $text);
				} else {
					$text .= $cobj->render_comment($row, "download", "comment", $id, $width, $subject);
				}
			}
			if (!$pref['nested_comments']) {
				$ns->tablerender(LAN_5, $text);
			}
				if(ADMIN == TRUE && $comment_total)
				{
					echo "<a href='".e_BASE.e_ADMIN."modcomment.php?download.$dl_id'>".LAN_314."</a>";
				}
		}
		$cobj->form_comment("comment", "download", $id, $subject, $content_type);
	}
	 
	require_once(FOOTERF);
}
	
if ($action == "report") {

	if (!$sql->db_Select("download", "*", "download_id = $id AND download_active = 1")) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}

	$row = $sql->db_Fetch();
		extract($row);

	if (IsSet($_POST['report_thread'])) {

		

		if ($pref['reported_post_email']) {
			require_once(e_HANDLER."mail.php");
			$report_add = $tp->toDB($_POST['report_add']);
			$report = LAN_dl_58.SITENAME." : ".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/")."download.php?".$download_id."\n".LAN_dl_59.$user."\n".$report_add;
			$subject = LAN_dl_60." ".SITENAME;
			sendemail(SITEADMINEMAIL, $subject, $report);
		}
	

		$report_add = $tp -> toDB($_POST['report_add']);
		$download_name = $tp -> toDB($_POST['report_download_name']);
		$user = $tp -> toDB($_POST['user']);

		$sql->db_Insert('generic', "0, 'Broken Download', ".time().",'".USERID."', '$download_name', ".$_POST['report_download_id'].", '$report_add'");

		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_47);
		require_once(HEADERF);

		$text = LAN_dl_48."<br /><br /><a href='".e_BASE."download.php?view.".$download_id."'>".LAN_dl_49."</a";
		$ns->tablerender(LAN_dl_50, $text);

	} else {
		$number = $download_id;
		$thread_name = $thread_info['head']['thread_name'];
		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_51." ".$download_name);
		require_once(HEADERF);
		
		
		$text = "<form action='".e_SELF."?report.$download_id' method='post'>
		<table style='width:100%'>
			<tr>
			<td  style='width:50%' >
			".LAN_dl_32.": ".$download_name." <a href='".e_SELF."?view.$download_id'><span class='smalltext'>".LAN_dl_53." </span>
			</a>
			</td>
			<td style='text-align:center;width:50%'>
			</td>
			</tr>
			<tr>
			<td>".LAN_dl_54."<br />".LAN_dl_55."
			</td>
			<td style='text-align:center;'>
			<textarea cols='40' rows='10' class='tbox' name='report_add'></textarea>
			</td>
			</tr>
			<tr>
			<td colspan='2' style='text-align:center;'><br />
			<input type ='hidden' name='user' value='".(USER ? USERNAME : LAN_dl_52)."' />
			<input type ='hidden' name='report_download_id' value='$download_id' />
			<input type ='hidden' name='report_download_name' value='$download_name' />
			<input class='button' type='submit' name='report_thread' value='".LAN_dl_56."' />
			</td>
			</tr>
			</table>";
		$ns->tablerender(LAN_dl_50, $text);
	}
	require_once(FOOTERF);
	exit;
}
	
function parsesize($size) {
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if ($size < $kb) {
		return $size." b";
	}
	else if($size < $mb) {
		return round($size/$kb, 2)." kb";
	}
	else if($size < $gb) {
		return round($size/$mb, 2)." mb";
	}
	else if($size < $tb) {
		return round($size/$gb, 2)." gb";
	} else {
		return round($size/$tb, 2)." tb";
	}
}
	
function parse_download_cat_parent_table($row) {
	global $DOWNLOAD_CAT_PARENT_TABLE;
	extract($row);
	 
	if (check_class($download_category_class)) {
		$parent_status == "open";
		$DOWNLOAD_CAT_MAIN_ICON .= ($download_category_icon ? "<img src='".e_IMAGE."download_icons/".$download_category_icon."' alt='' style='float-left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME .= $download_category_name;
	} else {
		$parent_status == "closed";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_PARENT_TABLE));
}
	
function parse_download_cat_child_table($row) {
	global $DOWNLOAD_CAT_CHILD_TABLE, $sql;
	extract($row);
	 
	$sql2 = new db;
	$sql3 = new db;
	$sql4 = new db;
	 $sql5 = new db;
	 
	$total_filesize = 0;
	 $total_downloadcount = 0;
	if ($filecount = $sql3->db_Select("download", "*", "download_category='".$download_category_id."' AND download_active='1'")) {
		while ($row = $sql3->db_Fetch()) {
			extract($row);
			$total_filesize += $download_filesize;
			$total_downloadcount += $download_requested;
		}
		$total_filesize = parsesize($total_filesize);
	}
	$new = (USER && $sql3->db_Count("download", "(*)", "WHERE download_category='".$download_category_id."' AND download_datestamp>".USERLV) ? "<img src='".e_IMAGE."generic/new.png' alt='' style='vertical-align:middle' />" : "");
	 
	if (check_class($download_category_class)) {
		$DOWNLOAD_CAT_SUB_ICON = ($download_category_icon ? "<img src='".e_IMAGE."download_icons/".$download_category_icon."' alt='' style='float-left' />" : "&nbsp;");
		$DOWNLOAD_CAT_SUB_NEW_ICON = $new;
		$DOWNLOAD_CAT_SUB_NAME = ($filecount ? "<a href='".e_SELF."?list.".$download_category_id."'>".$download_category_name."</a>" : $download_category_name);
		$DOWNLOAD_CAT_SUB_DESCRIPTION = $download_category_description;
		$DOWNLOAD_CAT_SUB_COUNT = $filecount;
		$DOWNLOAD_CAT_SUB_SIZE = $total_filesize;
		$DOWNLOAD_CAT_SUB_DOWNLOADED = $total_downloadcount;
		 
		// check for subsub cats ...
		if ($sql5->db_Select("download_category", "*", "download_category_parent='".$download_category_id."'")) {
			$DOWNLOAD_CAT_SUBSUB_LAN = LAN_dl_42;
			while ($row = $sql5->db_Fetch()) {
				extract($row);
				$new = (USER && $sql4->db_Count("download", "(*)", "WHERE download_category='".$download_category_id."' AND download_datestamp>".USERLV) ? "<img src='".e_IMAGE."generic/new.png' alt='' style='vertical-align:middle' />" : "");
				if ($sql4->db_Select("download", "*", "download_category='".$download_category_id."' ")) {
					$DOWNLOAD_CAT_SUBSUB_NAME .= "<a href='".e_SELF."?list.".$download_category_id."'>".$download_category_name."</a> ";
				} else {
					$DOWNLOAD_CAT_SUBSUB_NAME .= " ".$new.$download_category_name." ";
				}
			}
		}
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_CHILD_TABLE));
}
	
	
	
	
function parse_download_list_table($row) {
	global $DOWNLOAD_LIST_TABLE, $rater, $pref, $gen, $agreetext;
	extract($row);
	 
	$gen = new convert;
	$rater = new rater;
	 
	$DOWNLOAD_LIST_NEWICON = (USER && $download_datestamp > USERLV ? "<img src='".e_IMAGE."generic/new.png' alt='' style='vertical-align:middle' />" : "");
	 
	$DOWNLOAD_LIST_DATESTAMP = $gen->convert_date($download_datestamp, "short");
	$DOWNLOAD_LIST_FILESIZE = parsesize($download_filesize);
	$ratearray = $rater->getrating("download", $download_id);
	if (!$ratearray[0]) {
		$DOWNLOAD_LIST_RATING = LAN_dl_13;
	} else {
		$DOWNLOAD_LIST_RATING = ($ratearray[2] ? $ratearray[1].".".$ratearray[2]."/".$ratearray[0] : $ratearray[1]."/".$ratearray[0]);
	}
	 
	if ($pref['agree_flag'] == 1) {
		$DOWNLOAD_LIST_LINK = "<a href='request.php?".$download_id."' onclick= \"return confirm('".$agreetext."');\">";
	} else {
		$DOWNLOAD_LIST_LINK = "<a href='request.php?".$download_id."'>";
	}
	 
	$DOWNLOAD_LIST_NAME = "<a href='".e_SELF."?view.".$download_id."'>".$download_name."</a>";
	$DOWNLOAD_LIST_AUTHOR = $download_author;
	$DOWNLOAD_LIST_REQUESTED = $download_requested;
	$DOWNLOAD_LIST_ICON = "<img src='".e_IMAGE."generic/download.png' alt='' style='border:0' />";
	 
	return(preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_LIST_TABLE));
}
	
?>