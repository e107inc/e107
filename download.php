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
|     $Revision: 1.42 $
|     $Date: 2005-07-01 13:48:37 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
unset($text);
$agreetext = $tp->toJS($pref['agree_text']);
$cobj = new comment;
global $tp;

/* define images */
define("IMAGE_DOWNLOAD", (file_exists(THEME."generic/download.png") ? THEME."generic/download.png" : e_IMAGE."generic/".IMODE."/download.png"));
define("IMAGE_NEW", (file_exists(THEME."generic/new.png") ? THEME."generic/new.png" : e_IMAGE."generic/".IMODE."/new.png"));

/* define image style */





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
    if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}





	$qry = "
	SELECT dc.*, SUM(d.download_filesize) AS d_size,
	COUNT(d.download_id) AS d_count,
	MAX(d.download_datestamp) as d_last,
	SUM(d.download_requested) as d_requests,
	COUNT(d2.download_id) AS d_subcount,
	SUM(d2.download_filesize) AS d_subsize,
	SUM(d2.download_requested) as d_subrequests
	FROM #download_category AS dc
	LEFT JOIN #download AS d ON dc.download_category_id = d.download_category AND d.download_active > 0 AND d.download_visible IN (".USERCLASS_LIST.")
	LEFT JOIN #download_category as dc2 ON dc2.download_category_parent=dc.download_category_id
	LEFT JOIN #download AS d2 ON dc2.download_category_id = d2.download_category AND d2.download_active > 0 AND d2.download_visible IN (".USERCLASS_LIST.")
	WHERE dc.download_category_class IN (".USERCLASS_LIST.")
	GROUP by dc.download_category_id ORDER by dc.download_category_order
	";
	if (!$sql->db_Select_gen($qry))
	{
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_2."</div>");
		require_once(FOOTERF);
		exit;
	}
	else
	{
		while($row = $sql->db_Fetch())
		{
			$catList[$row['download_category_parent']][] = $row;
		}
		foreach($catList[0] as $row)
		{

			$download_cat_table_string .= parse_download_cat_parent_table($row);
			foreach($catList[$row['download_category_id']] as $crow)
			{
				$download_cat_table_string .= parse_download_cat_child_table($crow, $catList[$crow['download_category_id']]);
			}
		}
	}

	$download_cat_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_START);

	$DOWNLOAD_CAT_NEWDOWNLOAD_TEXT = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' /> ".LAN_dl_36;
	$DOWNLOAD_CAT_SEARCH = "
		<form method='get' action='".e_BASE."search.php'>
		<p>
		<input class='tbox' type='text' name='q' size='30' value='' maxlength='50' />
		<input class='button' type='submit' name='s' value='".LAN_dl_41."' />
		<input type='hidden' name='r' value='0' />
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
	$order = preg_replace("#\W#","",$tmp[4]);
	$sort = preg_replace("#\W#", "", $tmp[5]);
} else {
	$action = preg_replace("#\W#", "", $tmp[0]);
	$id = intval($tmp[1]);
}

if (isset($_POST['commentsubmit'])) {
	if (!$sql->db_Select("download", "download_comment", "download_id = '{$id}' ")) {
		header("location:".e_BASE."index.php");
		exit;
	} else {
		$row = $sql->db_Fetch();
		if ($row[0] && (ANON === TRUE || USER === TRUE)) {

			$clean_authorname = $_POST['author_name'];
			$clean_comment = $_POST['comment'];
			$clean_subject = $_POST['subject'];

			$cobj->enter_comment($clean_authorname, $clean_comment, "download", $id, $pid, $clean_subject);
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

	$total_downloads = $sql->db_Select("download", "*", "download_category = '{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.")");
	/*
	if (!$total_downloads) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}
	*/

	/* SHOW SUBCATS ... */












	if($sql -> db_Select("download_category", "download_category_id", "download_category_parent='{$id}' "))
	{
		/* there are subcats - display them ... */
		$qry = "
		SELECT dc.*, dc2.download_category_name AS parent_name, dc2.download_category_icon as parent_icon, SUM(d.download_filesize) AS d_size,
		COUNT(d.download_id) AS d_count,
		MAX(d.download_datestamp) as d_last,
		SUM(d.download_requested) as d_requests
		FROM #download_category AS dc
		LEFT JOIN #download AS d ON dc.download_category_id = d.download_category AND d.download_active > 0 AND d.download_visible IN (".USERCLASS_LIST.")
		LEFT JOIN #download_category as dc2 ON dc2.download_category_id='{$id}'
		WHERE dc.download_category_class IN (".USERCLASS_LIST.") AND dc.download_category_parent='{$id}'
		GROUP by dc.download_category_id ORDER by dc.download_category_order
		";
		$sql->db_Select_gen($qry);
		$scArray = $sql -> db_getList();
		if (!$DOWNLOAD_CAT_PARENT_TABLE) {
			if (file_exists(THEME."download_template.php"))	{
				require_once(THEME."download_template.php");
			} else {
				require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
			}
		}
		if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}
		foreach($scArray as $row)	{
			$download_cat_table_string .= parse_download_cat_child_table($row, FALSE);
		}
		require_once(HEADERF);
		if(strstr($row['parent_icon'], chr(1)))	{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $row['parent_icon']);
		}
		$DOWNLOAD_CAT_MAIN_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float-left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME = $row['parent_name'];
		$download_cat_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_START);
		$DOWNLOAD_CAT_NEWDOWNLOAD_TEXT = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' /> ".LAN_dl_36;
		$download_cat_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_END);
		$text = $download_cat_table_start.$download_cat_table_string.$download_cat_table_end;
		if($DOWNLOAD_CAT_TABLE_RENDERPLAIN) {
			echo $text;
		} else {
			$ns->tablerender(LAN_dl_18.$type, $text);
		}
		require_once(FOOTERF);
		exit;
	}

	$sql->db_Select("download_category", "*", "download_category_id='{$id}'");
	$row = $sql->db_Fetch();
	extract($row);

	$core_total = $sql->db_Count("download WHERE download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.")");
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

	if(strstr($download_category_icon, chr(1)))
	{
		list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
	}
	$DOWNLOAD_CATEGORY_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float-left' />" : "&nbsp;");

	$DOWNLOAD_CATEGORY = $download_category_name;
	$DOWNLOAD_CATEGORY_DESCRIPTION = $tp -> toHTML($download_category_description, TRUE);

	if (!$DOWNLOAD_LIST_TABLE) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}
    if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	$gen = new convert;
	require_once(e_HANDLER."rate_class.php");
	$rater = new rater;
	$sql = new db;
	 $sql2 = new db;

	$filetotal = $sql->db_Select("download", "*", "download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.") ORDER BY {$order} {$sort} LIMIT {$from}, {$view}");
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
	$ix = new nextprev("download.php", $from, $view, $total_downloads, "Downloads", "list.{$id}.{$view}.{$order}.{$sort}");
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
	if (!$sql->db_Select("download", "*", "download_id = {$id} AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.")")) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}

    if (!$DOWNLOAD_VIEW_TABLE) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}
    if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:0px");}



	$gen = new convert;
	$row = $sql->db_Fetch();
	 extract($row);
	$comments_enabled = $row['download_comment'];
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

    $DOWNLOAD_ADMIN_EDIT = (ADMIN && getperms('6')) ? "<a href='".e_ADMIN."download.php?create.edit.$download_id' title='edit'><img src='".e_IMAGE."generic/lite/edit.png' alt='' style='padding:0px;border:0px' /></a>" : "";


	$DOWNLOAD_REPORT_LINK = "<a href='".e_SELF."?report.{$download_id}'>".LAN_dl_45."</a>";
	$DOWNLOAD_CATEGORY = $download_category_name;
	$DOWNLOAD_CATEGORY_DESCRIPTION = $tp -> toHTML($download_category_description, TRUE);

	$DOWNLOAD_VIEW_NAME = $download_name;

	$DOWNLOAD_VIEW_NAME_LINKED = "<a href='".e_BASE."request.php?".$download_id."' title='".LAN_dl_46."'>{$download_name}</a>";

	$DOWNLOAD_VIEW_AUTHOR_LAN = LAN_dl_24;
	$DOWNLOAD_VIEW_AUTHOR = ($download_author ? $download_author : "&nbsp;");

	if ($download_author_email) {
		$DOWNLOAD_VIEW_AUTHOREMAIL_LAN = LAN_dl_30;
		$DOWNLOAD_VIEW_AUTHOREMAIL = $tp -> toHTML($download_author_email, TRUE);
	}

	if ($download_author_website) {
		$DOWNLOAD_VIEW_AUTHORWEBSITE_LAN = LAN_dl_31;
		$DOWNLOAD_VIEW_AUTHORWEBSITE = $tp -> toHTML($download_author_website, TRUE);
	}

	$DOWNLOAD_VIEW_DESCRIPTION_LAN = LAN_dl_7;
	$DOWNLOAD_VIEW_DESCRIPTION = $tp->toHTML(($download_description ? $download_description : "&nbsp;"), TRUE);

	$DOWNLOAD_VIEW_DATE_LAN = LAN_dl_22;
	$DOWNLOAD_VIEW_DATE_SHORT = $gen->convert_date($download_datestamp, "short");
	$DOWNLOAD_VIEW_DATE_LONG = $gen->convert_date($download_datestamp, "long");

	$DOWNLOAD_VIEW_IMAGE_LAN = LAN_dl_11;
	if ($download_thumb) {
		$DOWNLOAD_VIEW_IMAGE = ($download_image ? "<a href='".e_FILE."downloadimages/{$download_image}'><img src='".e_FILE."downloadthumbs/".$download_thumb."' alt='' style='".DL_IMAGESTYLE."' /></a>" : "<img src='".e_FILE."downloadthumbs/".$download_thumb."' alt='' style='".DL_IMAGESTYLE."' />");
	}
	else if($download_image) {
		$DOWNLOAD_VIEW_IMAGE = "<a href='".e_BASE."request.php?download.".$download_id."'>".LAN_dl_40."</a>";
	}
	else
	{
		$DOWNLOAD_VIEW_IMAGE = LAN_dl_75;
	}

	$DOWNLOAD_VIEW_IMAGEFULL = ($download_image) ? "<img src='".e_FILE."downloadimages/{$download_image}' alt='' style='".DL_IMAGESTYLE."' />" : "";

	if ($pref['agree_flag'] == 1) {
		$dnld_link = "<a href='request.php?{$download_id}' onclick= \"return confirm('{$agreetext}');\">";
	} else {
		$dnld_link = "<a href='request.php?{$download_id}'>";
	}

	$DOWNLOAD_VIEW_FILESIZE_LAN = LAN_dl_10;
	$DOWNLOAD_VIEW_FILESIZE = parsesize($download_filesize);
	$DOWNLOAD_VIEW_REQUESTED_LAN = LAN_dl_18;
	$DOWNLOAD_VIEW_REQUESTED = $download_requested;
	$DOWNLOAD_VIEW_LINK_LAN = LAN_dl_32;

	if($download_mirror)
	{
		if($download_mirror_type)
		{
			$DOWNLOAD_VIEW_LINK = "<a href='".e_SELF."?mirror.{$download_id}'>".LAN_dl_66."</a>";
		}
		else
		{
			$DOWNLOAD_VIEW_LINK = $dnld_link." <img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";
		}
	}
	else
	{
		$DOWNLOAD_VIEW_LINK = $dnld_link." <img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";
	}


	$DOWNLOAD_VIEW_RATING_LAN = LAN_dl_12;
	$DOWNLOAD_VIEW_RATING = "
		<table style='width:100%'>
		<tr>
		<td style='width:50%'>";

	if ($ratearray = $rater->getrating("download", $download_id)) {
		for($c = 1; $c <= $ratearray[1]; $c++) {
			$DOWNLOAD_VIEW_RATING .= "<img src='".e_IMAGE."rate/".IMODE."/star.png' alt=''>";
		}
		if ($ratearray[2]) {
			$DOWNLOAD_VIEW_RATING .= "<img src='".e_IMAGE."rate/".IMODE."/".$ratearray[2].".png'  alt=''>";
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



	$download_view_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE_START);
	$download_view_table_string = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE);
	$download_view_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_VIEW_TABLE_END);
	$text .= $download_view_table_start.$download_view_table_string.$download_view_table_end;

	$dl_id = $download_id;
	if ($sql->db_Select("download", "*", "download_category='{$download_category_id}' AND download_id < {$dl_id} AND download_active > 0 ORDER BY download_datestamp DESC")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$prev = "<a href='".e_SELF."?view.{$download_id}'>&lt;&lt; ".LAN_dl_33." [{$download_name}]</a>\n";
	} else {
		$prev = "&nbsp;";
	}
	if ($sql->db_Select("download", "*", "download_category='{$download_category_id}' AND download_id > {$dl_id} AND download_active > 0 ORDER BY download_datestamp ASC")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$next = "<a href='".e_SELF."?view.{$download_id}'>[{$download_name}] ".LAN_dl_34." &gt;&gt;</a>\n";
	} else {
		$next = "&nbsp;";
	}

	if ($prev || $next) {
		$text .= "
			<table style='width:100%'>
			<tr>
			<td style='width:40%;'>{$prev}</td>
			<td style='width:20%; text-align: center;'><a href='".e_SELF."?list.{$download_category}'>".LAN_dl_35."</a></td>
			<td style='width:40%; text-align: right;'>{$next}</td>
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
	if ($comments_enabled) {
		$cobj->compose_comment("download", "comment", $id, $width, $subject, $showrate=FALSE);
	}

	require_once(FOOTERF);
}

if ($action == "report") {

	if (!$sql->db_Select("download", "*", "download_id = {$id} AND download_active > 0")) {
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
			$report = LAN_dl_58.SITENAME." : ".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/")."download.php?{$download_id}\n".LAN_dl_59."{$user}\n{$report_add}";
			$subject = LAN_dl_60." ".SITENAME;
			sendemail(SITEADMINEMAIL, $subject, $report);
		}


		$report_add = $tp -> toDB($_POST['report_add']);
		$download_name = $tp -> toDB($_POST['report_download_name']);
		$user = $tp -> toDB($_POST['user']);

		$clean_report_id = intval($_POST['report_download_id']);

		$sql->db_Insert('generic', "0, 'Broken Download', ".time().",'".USERID."', '{$download_name}', {$clean_report_id}, '{$report_add}'");

		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_47);
		require_once(HEADERF);

		$text = LAN_dl_48."<br /><br /><a href='".e_BASE."download.php?view.".$download_id."'>".LAN_dl_49."</a";
		$ns->tablerender(LAN_dl_50, $text);

	} else {
		$number = $download_id;
		$thread_name = $thread_info['head']['thread_name'];
		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_51." ".$download_name);
		require_once(HEADERF);


		$text = "<form action='".e_SELF."?report.{$download_id}' method='post'>
		<table style='width:100%'>
			<tr>
			<td  style='width:50%' >
			".LAN_dl_32.": ".$download_name." <a href='".e_SELF."?view.{$download_id}'><span class='smalltext'>".LAN_dl_53." </span>
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
			<input type ='hidden' name='report_download_id' value='{$download_id}' />
			<input type ='hidden' name='report_download_name' value='{$download_name}' />
			<input class='button' type='submit' name='report_thread' value='".LAN_dl_56."' />
			</td>
			</tr>
			</table>";
		$ns->tablerender(LAN_dl_50, $text);
	}
	require_once(FOOTERF);
	exit;
}

if($action == "mirror")
{
	require_once(HEADERF);

	if (!$DOWNLOAD_MIRROR_START) {
		if (file_exists(THEME."download_template.php")) {
			require_once(THEME."download_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
		}
	}

	$sql -> db_Select("download_mirror");
	$mirrorList = $sql -> db_getList("ALL", 0, 200, "mirror_id");

	if($sql -> db_Select("download", "*", "download_id = {$id}"))
	{
		$row = $sql->db_Fetch();

		extract($row);
		$array = explode(chr(1), $download_mirror);

		$c = (count($array)-1);
		for ($i=1; $i<$c; $i++) {
			$d = mt_rand(0, $i);
			$tmp = $array[$i];
			$array[$i] = $array[$d];
			$array[$d] = $tmp;
		}

		$download_mirror = "";
		foreach($array as $mirrorstring)
		{
			if($mirrorstring)
			{
				$download_mirror .= parse_download_mirror_table($row, $mirrorstring, $mirrorList);
			}
		}

		$DOWNLOAD_MIRROR_HOST_LAN = LAN_dl_68;
		$DOWNLOAD_MIRROR_GET_LAN = LAN_dl_69;
		$DOWNLOAD_MIRROR_LOCATION_LAN = LAN_dl_70;
		$DOWNLOAD_MIRROR_DESCRIPTION_LAN = LAN_dl_71;
		$DOWNLOAD_MIRROR_REQUEST = LAN_dl_72."'".$download_name."'";



		$download_mirror_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_MIRROR_START);
		$download_mirror_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_MIRROR_END);

		$text = $download_mirror_start.$download_mirror.$download_mirror_end;

		if($DOWNLOAD_MIRROR_RENDERPLAIN) {
			echo $text;
		} else {
			$ns->tablerender(LAN_dl_67, $text);
		}

		require_once(FOOTERF);
	}
}

function parse_download_mirror_table($row, $mirrorstring, $mirrorList)
{

	global $DOWNLOAD_MIRROR;
	list($mirrorHost_id, $mirrorHost_url, $mirrorRequests) = explode(",", $mirrorstring);

	extract($mirrorList[$mirrorHost_id]);

	$DOWNLOAD_MIRROR_NAME = "<a href='{$mirror_url}' rel='external'>{$mirror_name}</a>";
	$DOWNLOAD_MIRROR_IMAGE = ($mirror_image ? "<a href='{$mirror_url}' rel='external'><img src='".e_FILE."downloadimages/".$mirror_image."' alt='' style='border:0' /></a>" : "");
	$DOWNLOAD_MIRROR_LOCATION = ($mirror_location ? $mirror_location : "");
	$DOWNLOAD_MIRROR_DESCRIPTION = ($mirror_description ? $mirror_description : "");

	$DOWNLOAD_MIRROR_FILESIZE = parsesize($row['download_filesize']);
	$DOWNLOAD_MIRROR_LINK = "<a href='".e_BASE."request.php?mirror.{$row['download_id']}.{$mirrorHost_id}'><img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";

	$DOWNLOAD_MIRROR_REQUESTS = (ADMIN ? LAN_dl_73.$mirrorRequests : "");
	$DOWNLOAD_TOTAL_MIRROR_REQUESTS = (ADMIN ? LAN_dl_74.$mirror_count : "");

	return(preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_MIRROR));
}




function parsesize($size) {
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if(!$size)
	{
		return '0';
	}
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
	global $current_row,$DOWNLOAD_CAT_PARENT_TABLE;
	extract($row);
	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)


	$template = ($current_row == 1) ? $DOWNLOAD_CAT_PARENT_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_PARENT_TABLE);

	if (check_class($download_category_class)) {
		$parent_status == "open";
		if(strstr($download_category_icon, chr(1)))
		{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
		}
		$DOWNLOAD_CAT_MAIN_ICON .= ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float-left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME .= $download_category_name;
	} else {
		$parent_status == "closed";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}

function parse_download_cat_child_table($row, $subList)
{

	global $current_row, $DOWNLOAD_CAT_CHILD_TABLE, $DOWNLOAD_CAT_SUBSUB_TABLE;

	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_CAT_CHILD_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_CHILD_TABLE);


	if(USER && $row['d_last'] > USERLV){
		$new = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
	}else{
		$new = "";
	}

	if(strstr($row['download_category_icon'], chr(1)))
	{
		list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $row['download_category_icon']);
	}
	else
	{
		$download_category_icon = $row['download_category_icon'];
		$download_category_icon_empty = $row['download_category_icon'];
	}


	$download_icon = ((!$row['d_count'] || !$row['d_subcount']) && $download_category_icon_empty) ? "<img src='".e_IMAGE."icons/{$download_category_icon_empty}' alt='' style='float-left' />" : "<img src='".e_IMAGE."icons/".$row['download_category_icon']."' alt='' style='float-left' />" ;

	$DOWNLOAD_CAT_SUB_ICON = ($row['download_category_icon'] ? $download_icon : "&nbsp;");
	$DOWNLOAD_CAT_SUB_NEW_ICON = $new;
	$DOWNLOAD_CAT_SUB_NAME = ($row['d_count'] ? "<a href='".e_SELF."?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>" : $row['download_category_name']);
	$DOWNLOAD_CAT_SUB_NAME_LINKED = "<a href='".e_SELF."?list.".$row['download_category_id']."'>".$row['download_category_name']."</a>";
	$DOWNLOAD_CAT_SUB_DESCRIPTION = $row['download_category_description'];
	$DOWNLOAD_CAT_SUB_COUNT = ($row['d_subcount'] ? $row['d_subcount'] : $row['d_count']);
	$DOWNLOAD_CAT_SUB_SIZE = parsesize(($row['d_subsize'] ? $row['d_subsize'] : $row['d_size']));
	$DOWNLOAD_CAT_SUB_DOWNLOADED = intval(($row['d_subrequests'] ? $row['d_subrequests'] : $row['d_requests']));
	$DOWNLOAD_CAT_SUBSUB = "";
	// check for subsub cats ...
	if($subList != FALSE)
	{
		foreach($subList as $subrow){
			$DOWNLOAD_CAT_SUBSUB_ICON = ($subrow['download_category_icon'] ? "<img src='".e_IMAGE."icons/".$subrow['download_category_icon']."' alt='' style='float-left' />" : "&nbsp;");
			$DOWNLOAD_CAT_SUBSUB_DESCRIPTION = $subrow['download_category_description'];
			$DOWNLOAD_CAT_SUBSUB_COUNT = intval($subrow['d_count']);
			$DOWNLOAD_CAT_SUBSUB_SIZE = parsesize($subrow['d_size']);
			$DOWNLOAD_CAT_SUBSUB_DOWNLOADED = intval($subrow['d_requests']);

			if(USER && $subrow['d_last'] > USERLV)	{
				$new = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
			}else {
				$new = "";
			}
			$DOWNLOAD_CAT_SUBSUB_NEW_ICON = $new;
			$DOWNLOAD_CAT_SUBSUB_NAME = $new.($subrow['d_count'] ? "<a href='".e_SELF."?list.".$subrow['download_category_id']."'>".$subrow['download_category_name']."</a>" : $subrow['download_category_name']);
			$DOWNLOAD_CAT_SUBSUB .= preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_SUBSUB_TABLE);
		}
	}

	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}

function parse_download_list_table($row) {
	global $current_row,$DOWNLOAD_LIST_TABLE, $rater, $pref, $gen, $agreetext;
	extract($row);

	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_LIST_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_LIST_TABLE);

	$gen = new convert;
	$rater = new rater;

	$DOWNLOAD_LIST_NEWICON = (USER && $download_datestamp > USERLV ? "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />" : "");

	$DOWNLOAD_LIST_DATESTAMP = $gen->convert_date($download_datestamp, "short");
	$DOWNLOAD_LIST_FILESIZE = parsesize($download_filesize);
	$ratearray = $rater->getrating("download", $download_id);
	if (!$ratearray[0]) {
		$DOWNLOAD_LIST_RATING = LAN_dl_13;
	} else {
		$DOWNLOAD_LIST_RATING = ($ratearray[2] ? "{$ratearray[1]}.{$ratearray[2]}/{$ratearray[0]}" : "{$ratearray[1]}/{$ratearray[0]}");
	}


	if($download_mirror_type)
	{
		$DOWNLOAD_LIST_LINK = ($pref['agree_flag'] ? "<a href='".e_SELF."?mirror.{$download_id}' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_SELF."?mirror.{$download_id}'>");
	}
	else
	{
		$DOWNLOAD_LIST_LINK = ($pref['agree_flag'] ? "<a href='".e_BASE."request.php?{$download_id}' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_BASE."request.php?{$download_id}'>");
	}

	$DOWNLOAD_LIST_NAME = "<a href='".e_SELF."?view.{$download_id}'>{$download_name}</a>";
	$DOWNLOAD_LIST_AUTHOR = $download_author;
	$DOWNLOAD_LIST_REQUESTED = $download_requested;
	$DOWNLOAD_LIST_ICON = "<img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";
	$DOWNLOAD_LIST_THUMB = "<img src='".e_FILE."downloadthumbs/".$download_thumb."' alt='' style='".DL_IMAGESTYLE."' />";
	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}

?>