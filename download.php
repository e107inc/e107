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
|     $Source: /cvs_backup/e107_0.8/download.php,v $
|     $Revision: 1.2 $ - with modifications
|     $Date: 2006-12-07 12:48:42 $
|     $Author: mrpete $
|
| Modifications by steved:
|	1. Can display sub-categories which contain sub-sub categories and files
|	2. New $pref['download_subsub'] - if defined and '0', doesn't display sub-sub categories
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
require_once(e_FILE."shortcode/batch/download_shortcodes.php");
unset($text);
$agreetext = $tp->toHTML($pref['agree_text'],TRUE,"parse_sc");
$cobj = new comment;
global $tp;

if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:100%"); }

// To prevent display of sub-categories on the main display, un-comment the following line
//$pref['download_subsub'] = '0';


/* define images */
define("IMAGE_DOWNLOAD", (file_exists(THEME."images/download.png") ? THEME."images/download.png" : e_IMAGE."generic/".IMODE."/download.png"));
define("IMAGE_NEW", (file_exists(THEME."images/new.png") ? THEME."images/new.png" : e_IMAGE."generic/".IMODE."/new.png"));

/* define image style */

if (!e_QUERY || $_GET['elan'])
{
	require_once(HEADERF);
	// no qs - render categories ...

	if($cacheData = $e107cache->retrieve("download_cat",720)) // expires every 12 hours.
	{
		echo $cacheData;
		require_once(FOOTERF);
		exit;
	}


	if (!$DOWNLOAD_CAT_PARENT_TABLE)
	{
		if (is_readable(THEME."templates/download_template.php"))
		{
			require_once(THEME."templates/download_template.php");
		}
		elseif (is_readable(THEME."download_template.php"))
		{
			require_once(THEME."download_template.php");
		}
		else
		{
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
		{  // Display main category headings, then sub-categories, optionally with sub-sub categories expanded
			$download_cat_table_string .= parse_download_cat_parent_table($row);
			foreach($catList[$row['download_category_id']] as $crow)
			{
			  if (isset($pref['download_subsub']) && ($pref['download_subsub'] == '0'))
			  {  // Don't display sub-sub categories here
				$download_cat_table_string .= parse_download_cat_child_table($crow, FALSE);
			  }
			  else
			  {		// Display sub-sub categories
				$download_cat_table_string .= parse_download_cat_child_table($crow, $catList[$crow['download_category_id']]);
			  }
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


	ob_start();

	if($DOWNLOAD_CAT_TABLE_RENDERPLAIN) {
		echo $text;
	} else {
		$ns->tablerender(LAN_dl_18.$type, $text);
	}

	$cache_data = ob_get_flush();
	$e107cache->set("download_cat", $cache_data);

	require_once(FOOTERF);
	exit;
}


// Got a query string from now on
$tmp = explode(".", e_QUERY);
if (is_numeric($tmp[0]))
{
	$from = intval($tmp[0]);
	$action = preg_replace("#\W#", "", $tp -> toDB($tmp[1]));
	$id = intval($tmp[2]);
	$view = intval($tmp[3]);
	$order = preg_replace("#\W#", "", $tp -> toDB($tmp[4]));
	$sort = preg_replace("#\W#", "", $tp -> toDB($tmp[5]));
}
 else
{
	$action = preg_replace("#\W#", "", $tp -> toDB($tmp[0]));
	$id = intval($tmp[1]);
}

if (isset($_POST['commentsubmit']))
{
	if (!$sql->db_Select("download", "download_comment", "download_id = '{$id}' "))
	{
		header("location:".e_BASE."index.php");
		exit;
	}
	else
	{
		$row = $sql->db_Fetch();
		if ($row['download_comment'] && (ANON === TRUE || USER === TRUE))
		{
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

	$total_downloads = $sql->db_Count("download", "(*)", "WHERE download_category = '{$id}' AND download_active > 0 AND download_visible REGEXP '".e_CLASS_REGEXP."'");

// Next three lines extract page title
	if ($sql->db_Select("download_category", "*", "(download_category_id='{$id}') AND (download_category_class IN (".USERCLASS_LIST."))") )
	{
	$row = $sql->db_Fetch();
	extract($row);

	$type = $download_category_name;

	$type .= ($download_category_description) ? " [ ".$download_category_description." ]" : "";
	define("e_PAGETITLE", PAGE_NAME." / ".$download_category_name);
	}
	else
	{  // No access to this category
	  define("e_PAGETITLE", PAGE_NAME);
	  require_once(HEADERF);
	  $ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
	  require_once(FOOTERF);
	  exit;
	}

	require_once(HEADERF);

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
		if (!$DOWNLOAD_CAT_PARENT_TABLE)
		{
			if (file_exists(THEME."download_template.php"))
			{
				require_once(THEME."download_template.php");
			}
			else
			{
				require_once(e_BASE.$THEMES_DIRECTORY."templates/download_template.php");
			}
		}
		if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}
		foreach($scArray as $row)
		{
			$download_cat_table_string .= parse_download_cat_child_table($row, FALSE);
		}
		if(strstr($row['parent_icon'], chr(1)))	{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $row['parent_icon']);
		}
		$DOWNLOAD_CAT_MAIN_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME = $tp->toHTML($row['parent_name']);
		$download_cat_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_START);
		$DOWNLOAD_CAT_NEWDOWNLOAD_TEXT = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' /> ".LAN_dl_36;
		$download_cat_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_TABLE_END);
		$text = $download_cat_table_start.$download_cat_table_string.$download_cat_table_end;
		if($DOWNLOAD_CAT_TABLE_RENDERPLAIN)
		{
			echo $text;
		}
		else
		{
			$ns->tablerender($type, $text);
		}
		$text = "";		// If other files, show in a separate block
		$type = "";   	// Cancel title once displayed
	}  // End of subcategory display

// Now display individual downloads
	$core_total = $sql->db_Count("download WHERE download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.")");
	if (!check_class($download_category_class))
	{

		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	if(strstr($download_category_icon, chr(1)))
	{
		list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
	}
	$DOWNLOAD_CATEGORY_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");

	$DOWNLOAD_CATEGORY = $tp->toHTML($download_category_name,FALSE,"emotes_off, no_make_clickable");
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


	echo "<div style='text-align:center;margin-left:auto;margin-right:auto'><a href='".e_SELF."'>".LAN_dl_9."</a><br /><br />";
	$parms = $total_downloads.",".$view.",".$from.",".e_SELF."?[FROM].list.{$id}.{$view}.{$order}.{$sort}.";
	echo ($total_downloads > $view) ? "<div class='nextprev'>&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>" : "";
    echo "</div>";

	require_once(FOOTERF);
	exit;
}    // end of action=="list"


//  ---------------- View Mode ---------------------------------------------------------------------------------------------------------------------------------------------------

if ($action == "view") {

	$gen = new convert;

	$highlight_search = FALSE;
	if (isset($_POST['highlight_search'])) {
		$highlight_search = TRUE;
	}

    $query = "
		SELECT d.*, dc.* FROM #download AS d
		LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
		WHERE d.download_id = {$id} AND d.download_active > 0
		AND d.download_visible IN (".USERCLASS_LIST.")
		AND dc.download_category_class IN (".USERCLASS_LIST.")
		LIMIT 1";

	if(!$sql -> db_Select_gen($query)){
		require_once(HEADERF);
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	$dl = $sql -> db_Fetch();

	if (!isset($DOWNLOAD_VIEW_TABLE) && is_readable(THEME."download_template.php"))
	{
		include_once(THEME."download_template.php");
 	}
	else
	{
        include_once(e_THEME."templates/download_template.php");
	}

	if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:0px");}
    if(!isset($DL_VIEW_PAGETITLE))
	{
    	$DL_VIEW_PAGETITLE = PAGE_NAME." / {DOWNLOAD_CATEGORY} / {DOWNLOAD_VIEW_NAME}";
	}

    $DL_TITLE = $tp->parseTemplate($DL_VIEW_PAGETITLE, TRUE, $download_shortcodes);

	define("e_PAGETITLE", $DL_TITLE);

	require_once(HEADERF);
	$DL_TEMPLATE = $DOWNLOAD_VIEW_TABLE_START.$DOWNLOAD_VIEW_TABLE.$DOWNLOAD_VIEW_TABLE_END;
	$text = $tp->parseTemplate($DL_TEMPLATE, TRUE, $download_shortcodes);

	if(!isset($DL_VIEW_CAPTION))
	{
		$DL_VIEW_CAPTION = "{DOWNLOAD_VIEW_CAPTION}";
	}

	if(!isset($DL_VIEW_NEXTPREV))
	{
    	$DL_VIEW_NEXTPREV = "
		<div style='text-align:center'>
			<table style='".USER_WIDTH."'>
			<tr>
			<td style='width:40%;'>{DOWNLOAD_VIEW_PREV}</td>
			<td style='width:20%; text-align: center;'>{DOWNLOAD_BACK_TO_LIST}</td>
			<td style='width:40%; text-align: right;'>{DOWNLOAD_VIEW_NEXT}</td>
			</tr>
			</table>
			</div>
			";
    }
		// ------- Next/Prev -----------
    $text .= $tp->parseTemplate($DL_VIEW_NEXTPREV,TRUE,$download_shortcodes);
	$caption = $tp->parseTemplate($DL_VIEW_CAPTION,TRUE,$download_shortcodes);

	if($DOWNLOAD_VIEW_TABLE_RENDERPLAIN) {
		echo $text;
	} else {

		$ns->tablerender($caption, $text);
	}

	unset($text);

	if ($dl['download_comment']) {
		$cobj->compose_comment("download", "comment", $id, $width,$dl['download_name'], $showrate=FALSE);
	}

	require_once(FOOTERF);
	exit;

}

//  ---------------- Report Broken Link Mode ---------------------------------------------------------------------------------------------------------------------------------------------------

if ($action == "report" && check_class($pref['download_reportbroken'])) {
	if (!$sql->db_Select("download", "*", "download_id = {$id} AND download_active > 0")) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}

	$row = $sql -> db_Fetch();
	extract($row);

	if (isset($_POST['report_download'])) {
		$report_add = $tp -> toDB($_POST['report_add']);
		$download_name = $tp -> toDB($download_name);
		$user = USER ? USERNAME : LAN_dl_52;

		if ($pref['download_email']) {    // this needs to be moved into the NOTIFY, with an event.
			require_once(e_HANDLER."mail.php");
			$subject = LAN_dl_60." ".SITENAME;
			$report = LAN_dl_58." ".SITENAME.":\n".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/")."download.php?view.".$download_id."\n
			".LAN_dl_59." ".$user."\n".$report_add;
			sendemail(SITEADMINEMAIL, $subject, $report);
		}

		$sql->db_Insert('generic', "0, 'Broken Download', ".time().",'".USERID."', '{$download_name}', {$id}, '{$report_add}'");

		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_47);
		require_once(HEADERF);

		$text = LAN_dl_48."<br /><br /><a href='".e_BASE."download.php?view.".$download_id."'>".LAN_dl_49."</a";
		$ns->tablerender(LAN_dl_50, $text);

	} else {
		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_51." ".$download_name);
		require_once(HEADERF);

		$text = "<form action='".e_SELF."?report.{$download_id}' method='post'>
		<table style='".USER_WIDTH."'>
			<tr>
			<td  style='width:50%' >
			".LAN_dl_32.": ".$download_name."<br />
			<a href='".e_SELF."?view.{$download_id}'>
			<span class='smalltext'>".LAN_dl_53."</span>
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
			<input class='button' type='submit' name='report_download' value=\"".LAN_dl_45."\" />
			</td>
			</tr>
			</table>";
		$ns->tablerender(LAN_dl_50, $text);
	}
	require_once(FOOTERF);
	exit;
}

//  ---------------- Mirror Mode ---------------------------------------------------------------------------------------------------------------------------------------------------


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
		$DOWNLOAD_MIRROR_GET_LAN = LAN_dl_32;
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
	$DOWNLOAD_MIRROR_LINK = "<a href='".e_BASE."request.php?mirror.{$row['download_id']}.{$mirrorHost_id}' title='".LAN_dl_32."'><img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";

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
	global $tp,$current_row,$DOWNLOAD_CAT_PARENT_TABLE;
	extract($row);
	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)


	$template = ($current_row == 1) ? $DOWNLOAD_CAT_PARENT_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_PARENT_TABLE);

	if (check_class($download_category_class)) {
		$parent_status == "open";
		if(strstr($download_category_icon, chr(1)))
		{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
		}
		$DOWNLOAD_CAT_MAIN_ICON .= ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME .= $tp->toHTML($download_category_name,FALSE,"emotes_off, no_make_clickable");
	} else {
		$parent_status == "closed";
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}

function parse_download_cat_child_table($row, $subList)
{

	global $tp,$current_row, $DOWNLOAD_CAT_CHILD_TABLE, $DOWNLOAD_CAT_SUBSUB_TABLE;

	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_CAT_CHILD_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_CHILD_TABLE);


	if(USER && $row['d_last'] > USERLV){
		$new = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
	}else{
		$new = "";
	}

	list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $row['download_category_icon']);
	if (!$download_category_icon_empty)
	{
		$download_category_icon_empty = $download_category_icon;
	}

	if(!$row['d_count'] && !$row['d_subcount'])
	{
		$download_icon = "<img src='".e_IMAGE."icons/{$download_category_icon_empty}' alt='' style='float: left' />";
	}
	else
	{
		$download_icon = "<img src='".e_IMAGE."icons/{$download_category_icon}' alt='' style='float: left' />";
	}

	$DOWNLOAD_CAT_SUB_ICON = ($row['download_category_icon'] ? $download_icon : "&nbsp;");
	$DOWNLOAD_CAT_SUB_NEW_ICON = $new;
	$dcatname=$tp->toHTML($row['download_category_name'],FALSE,"emotes_off, no_make_clickable");
	$DOWNLOAD_CAT_SUB_NAME = ($row['d_count'] ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$dcatname."</a>" : $dcatname);
	$DOWNLOAD_CAT_SUB_NAME_LINKED = "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$dcatname."</a>";
	$DOWNLOAD_CAT_SUB_DESCRIPTION = $tp->toHTML($row['download_category_description']);
	$DOWNLOAD_CAT_SUB_COUNT = ($row['d_subcount'] ? $row['d_subcount'] : $row['d_count']);
	$DOWNLOAD_CAT_SUB_SIZE = parsesize(($row['d_subsize'] ? $row['d_subsize'] : $row['d_size']));
	$DOWNLOAD_CAT_SUB_DOWNLOADED = intval(($row['d_subrequests'] ? $row['d_subrequests'] : $row['d_requests']));
	$DOWNLOAD_CAT_SUBSUB = "";
	// check for subsub cats ...
	if($subList != FALSE)
	{
		foreach($subList as $subrow)
		{
			list($sub_download_category_icon, $sub_download_category_icon_empty) = explode(chr(1), $subrow['download_category_icon']);
			if (!$sub_download_category_icon_empty)
			{
				$sub_download_category_icon_empty = $sub_download_category_icon;
			}

			if(!$row['d_count'] && !$row['d_subcount'])
			{
				$sub_download_icon = "<img src='".e_IMAGE."icons/{$sub_download_category_icon_empty}' alt='' style='float: left' />";
			}
			else
			{
				$sub_download_icon = "<img src='".e_IMAGE."icons/{$sub_download_category_icon}' alt='' style='float: left' />";
			}

			$DOWNLOAD_CAT_SUBSUB_ICON = ($subrow['download_category_icon'] ? "$sub_download_icon" : "&nbsp;");
			$DOWNLOAD_CAT_SUBSUB_DESCRIPTION = $tp->toHTML($subrow['download_category_description']);
			$DOWNLOAD_CAT_SUBSUB_COUNT = intval($subrow['d_count']);
			$DOWNLOAD_CAT_SUBSUB_SIZE = parsesize($subrow['d_size']);
			$DOWNLOAD_CAT_SUBSUB_DOWNLOADED = intval($subrow['d_requests']);

			if(USER && $subrow['d_last'] > USERLV)	{
				$subsub_new = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
			}else {
				$subsub_new = "";
			}
			$DOWNLOAD_CAT_SUBSUB_NEW_ICON = $subsub_new;
			$DOWNLOAD_CAT_SUBSUB_NAME = ($subrow['d_count'] ? "<a href='".e_BASE."download.php?list.".$subrow['download_category_id']."'>".$tp->toHTML($subrow['download_category_name'])."</a>" : $tp->toHTML($subrow['download_category_name'],FALSE,"emotes_off, no_make_clickable"));
			$DOWNLOAD_CAT_SUBSUB .= preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_SUBSUB_TABLE);
		}
	}

	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}


function parse_download_list_table($row) {
	global $download_shortcodes,$tp,$current_row,$DOWNLOAD_LIST_TABLE, $rater, $pref, $gen, $agreetext;

	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_LIST_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_LIST_TABLE);

	return $tp->parseTemplate($template,TRUE,$download_shortcodes);

}

?>
