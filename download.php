<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/download.php,v $
|     $Revision: 1.28 $ 
|     $Date: 2009-07-14 05:31:57 $
|     $Author: e107coders $
|
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
require_once(e_HANDLER."comment_class.php");
require_once(e_FILE."shortcode/batch/download_shortcodes.php");

$cobj = new comment;
global $tp;

$dl_text = '';			// Output variable

if(!defined("USER_WIDTH")) { define("USER_WIDTH","width:100%"); }

// Following two now set in prefs
// To prevent display of sub-categories on the main display, change the value in the following line from '1' to '0'
//$pref['download_subsub'] = '1';
// To include file counts and sizes from subcategories and subsubcategories in superior categories, change the following to '1'
//$pref['download_incinfo'] = '1';

/* define images */
define("IMAGE_DOWNLOAD", (file_exists(THEME."images/download.png") ? THEME."images/download.png" : e_IMAGE."generic/download.png"));
define("IMAGE_NEW", (file_exists(THEME."images/new.png") ? THEME."images/new.png" : e_IMAGE."generic/new.png"));

$template_load_core = '
  $template_name = $load_template.".php";
  if (is_readable(THEME."templates/".$template_name))
  {
	require_once(THEME."templates/".$template_name);
  }
  elseif (is_readable(THEME.$template_name))
  {
	require_once(THEME.$template_name);
  }
  else
  {
	require_once(e_THEME."templates/".$template_name);
  }
';

$order_options = array('download_id','download_datestamp','download_requested','download_name','download_author');
$sort_options = array('ASC', 'DESC');


if (!e_QUERY || $_GET['elan'])
{
  $action = 'maincats';		// List categories
  $maincatval = '';			// Show all main categories
}
else
{	// Get parameters from the query
  $maincatval = '';			// Show all main categories
  $tmp = explode(".", e_QUERY);
  if (is_numeric($tmp[0]))			// $tmp[0] at least must be valid
  {
	$dl_from = intval($tmp[0]);
	$action = varset(preg_replace("#\W#", "", $tp -> toDB($tmp[1])),'list');
	$id = intval($tmp[2]);
	$view = intval($tmp[3]);
	$order = preg_replace("#\W#", "", $tp -> toDB($tmp[4]));
	$sort = preg_replace("#\W#", "", $tp -> toDB($tmp[5]));
  }
  else
  {
	$action = preg_replace("#\W#", "", $tp -> toDB($tmp[0]));
	$id = intval($tmp[1]);
	$errnum = intval(varset($tmp[2],0));
  }
  switch ($action)
  {
    case 'list' :	// Category-based listing
	  if (isset($_POST['view'])) 
	  {
		$view = intval($_POST['view']);
		$sort = varset($_POST['sort'],'DESC');
		$order = varset($_POST['order'],'download_datestamp');
	  }
	  if (!isset($dl_from)) $dl_from = 0;

	  // Get category type, page title
	  if ($sql->db_Select("download_category", "download_category_name,download_category_description,download_category_parent,download_category_class", "(download_category_id='{$id}') AND (download_category_class IN (".USERCLASS_LIST."))") )
	  {
		$row = $sql->db_Fetch();
		extract($row);
		$type = "<span class='dnld_cname'>".$download_category_name."</span>";
		$type .= ($download_category_description) ? " <span class='dnld_cdesc'>[".$download_category_description."]</span>" : "";
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
	  if ($download_category_parent == 0)
	  {  // It's a main category - change the listing type required
	    $action = 'maincats';
		$maincatval = $id;
	  }
	  break;
	case 'view' :	// Details of individual download
	  break;
	case 'report' :
	  break;
	case 'mirror' :
	  break;
	case 'error' :		// Errors passed from request.php
	  define("e_PAGETITLE", PAGE_NAME);
	  require_once(HEADERF);
	  switch ($errnum)
	  {
	    case 1 : 
		  $errmsg = LAN_dl_63;			// No permissions
		  break;
		case 2 : 
		  $errmsg = LAN_dl_62;			// Quota exceeded
		  break;
		default:
		  $errmsg = LAN_dl_61." ".$errnum;		// Generic error - shouldn't happen
	  }
	  $ns->tablerender(LAN_dl_61, "<div style='text-align:center'>".$errmsg."</div>");
	  require_once(FOOTERF);
	  exit;
  }
}

if (isset($order) && !in_array($order,$order_options)) unset($order);
if (isset($sort)  && !in_array($sort,$sort_options)) unset($sort);

if (!isset($order))	$order = varset($pref['download_order'],'download_datestamp');
if (!isset($sort))	$sort =  varset($pref['download_sort'], 'DESC');
if (!isset($view))	$view =  varset($pref['download_view'], '10');


//--------------------------------------------------
//			GENERATE DISPLAY TEXT
//--------------------------------------------------
switch ($action)
{	// Displaying main category or categories
  case 'maincats' :
    require_once(HEADERF);
	if ($cacheData = $e107cache->retrieve("download_cat".$maincatval,720)) // expires every 12 hours.
	{
	  echo $cacheData;
	  require_once(FOOTERF);
	  exit;
	}

	// Load the theme
	$load_template = 'download_template';
	if (!isset($DOWNLOAD_CAT_PARENT_TABLE)) eval($template_load_core);

    if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	// Read in tree of categories which this user is allowed to see
    $dl = new down_cat_handler(varset($pref['download_subsub'],1),USERCLASS_LIST,$maincatval,varset($pref['download_incinfo'],FALSE));
	
	if ($dl->down_count == 0)
	{
	  $ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_2."</div>");
	  require_once(FOOTERF);
	  exit;
	}
	else
	{
	  $download_cat_table_string = "";    // Notice removal
	  foreach($dl->cat_tree as $row)
	  {  // Display main category headings, then sub-categories, optionally with sub-sub categories expanded
		$download_cat_table_string .= parse_download_cat_parent_table($row);
		foreach($row['subcats'] as $crow)
		{
		  $download_cat_table_string .= parse_download_cat_child_table($crow);
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
	$dl_text = $download_cat_table_start.$download_cat_table_string.$download_cat_table_end; 

	ob_start();

	if(isset($DOWNLOAD_CAT_TABLE_RENDERPLAIN) && $DOWNLOAD_CAT_TABLE_RENDERPLAIN)
	{
	  echo $dl_text;
	}
	else 
	{
	  $ns->tablerender(LAN_dl_18, $dl_text);
	}

	$cache_data = ob_get_flush();
	$e107cache->set("download_cat".$maincatval, $cache_data);

	require_once(FOOTERF);
	exit;
  // Add other 'cases' here
}  // End switch ($action)


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
//			$e107cache->clear("comment.download.{$sub_action}");	$sub_action not used here
			$e107cache->clear("comment.download");
		}
	}
}

//====================================================
//				LIST
//====================================================
if ($action == "list") 
{
  $total_downloads = $sql->db_Count("download", "(*)", "WHERE download_category = '{$id}' AND download_active > 0 AND download_visible REGEXP '".e_CLASS_REGEXP."'");

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
		$load_template = 'download_template';
		if (!isset($DOWNLOAD_CAT_PARENT_TABLE)) eval($template_load_core);
		if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

		foreach($scArray as $row)
		{
			$download_cat_table_string .= parse_download_cat_child_table($row, FALSE);
		}
		if(strstr($row['parent_icon'], chr(1)))	{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $row['parent_icon']);
		}
		$DOWNLOAD_CAT_MAIN_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME = $tp->toHTML($row['parent_name'], FALSE, 'USER_TITLE');
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
	// Next line looks unnecessary
//	$core_total = $sql->db_Count("download WHERE download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.")");
	if (!check_class($download_category_class))
	{
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	if ($total_downloads < $view) { $dl_from = 0; }

	if(strstr($download_category_icon, chr(1)))
	{
		list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
	}
	$DOWNLOAD_CATEGORY_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");

	$DOWNLOAD_CATEGORY = $tp->toHTML($download_category_name,FALSE,'USER_TITLE');
	$DOWNLOAD_CATEGORY_DESCRIPTION = $tp -> toHTML($download_category_description, TRUE,'DESCRIPTION');

	$load_template = 'download_template';
	if (!isset($DOWNLOAD_LIST_TABLE)) eval($template_load_core);
    if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	$gen = new convert;
	require_once(e_HANDLER."rate_class.php");
	$rater = new rater;
	$tdownloads = 0;

	// $dl_from - first entry to show  (note - can get reset due to reuse of query, even if values overridden this time)
	// $view - number of entries per page  
	// $total_downloads - total number of entries matching search criteria
	$filetotal = $sql->db_Select("download", "*", "download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.") ORDER BY {$order} {$sort} LIMIT {$dl_from}, {$view}");
	$ft = ($filetotal < $view ? $filetotal : $view);
	while ($row = $sql->db_Fetch()) 
	{
		extract($row);
		$download_list_table_string .= parse_download_list_table($row);
		$tdownloads += $download_requested;
	}

	$DOWNLOAD_LIST_TOTAL_AMOUNT = $tdownloads." ".LAN_dl_16;
	$DOWNLOAD_LIST_TOTAL_FILES = $ft." ".LAN_dl_17;

	$download_list_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_LIST_TABLE_START);
	$download_list_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_LIST_TABLE_END);
	$text .= $download_list_table_start.$download_list_table_string.$download_list_table_end;


	if ($filetotal)
	{  // Only show list if some files in it
	  if($DOWNLOAD_LIST_TABLE_RENDERPLAIN) 
	  {
		echo $text;
	  } 
	  else 
 	  {
		$ns->tablerender($type, $text);
	  }
	}

	if(!isset($DOWNLOAD_LIST_NEXTPREV))
	{
        $sc_style['DOWNLOAD_LIST_NEXTPREV']['pre'] = "<div class='nextprev'>";
		$sc_style['DOWNLOAD_LIST_NEXTPREV']['post'] = "</div>";

    	$DOWNLOAD_LIST_NEXTPREV = "
			<div style='text-align:center;margin-left:auto;margin-right:auto'>{DOWNLOAD_BACK_TO_CATEGORY_LIST}<br /><br />
            {DOWNLOAD_LIST_NEXTPREV}
			</div>";
    }

	$nextprev_parms = $total_downloads.",".$view.",".$dl_from.",".e_SELF."?[FROM].list.{$id}.{$view}.{$order}.{$sort}.";
    echo $tp->parseTemplate($DOWNLOAD_LIST_NEXTPREV, TRUE, $download_shortcodes);

	require_once(FOOTERF);
	exit;
}    // end of action=="list"


//====================================================
//				VIEW
//====================================================
if ($action == "view") 
{
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

$comment_edit_query = 'comment.download.'.$id;

	$load_template = 'download_template';
	if (!isset($DOWNLOAD_VIEW_TABLE)) eval($template_load_core);
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

//====================================================
//				REPORT BROKEN LINKS
//====================================================
if ($action == "report" && check_class($pref['download_reportbroken'])) 
{
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


//====================================================
//				MIRRORS
//====================================================
if($action == "mirror")
{
	require_once(HEADERF);

	$load_template = 'download_template';
	if (!isset($DOWNLOAD_MIRROR_START)) eval($template_load_core);

	$sql -> db_Select("download_mirror");
	$mirrorList = $sql -> db_getList("ALL", 0, 200, "mirror_id");

	if($sql -> db_Select("download", "*", "download_id = {$id}"))
	{
		$row = $sql->db_Fetch();

		extract($row);
		$array = explode(chr(1), $download_mirror);

		// Shuffle the mirror list into a random order
		$c = count($array) -1;		// Will always be an empty entry at the end
		for ($i=1; $i<$c; $i++) 
		{
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

	$DOWNLOAD_MIRROR_FILESIZE = $e107->parseMemorySize($row['download_filesize']);
	$DOWNLOAD_MIRROR_LINK = "<a href='".e_BASE."request.php?mirror.{$row['download_id']}.{$mirrorHost_id}' title='".LAN_dl_32."'><img src='".IMAGE_DOWNLOAD."' alt='' style='border:0' /></a>";

	$DOWNLOAD_MIRROR_REQUESTS = (ADMIN ? LAN_dl_73.$mirrorRequests : "");
	$DOWNLOAD_TOTAL_MIRROR_REQUESTS = (ADMIN ? LAN_dl_74.$mirror_count : "");

	return(preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_MIRROR));
}




function parse_download_cat_parent_table($row) 
{
	global $tp,$e107,$current_row,$DOWNLOAD_CAT_PARENT_TABLE;
	extract($row);
	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)

	$template = ($current_row == 1) ? $DOWNLOAD_CAT_PARENT_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_PARENT_TABLE);

	$DOWNLOAD_CAT_MAIN_ICON = '';
	$DOWNLOAD_CAT_MAIN_NAME = '';
	$DOWNLOAD_CAT_MAIN_DESCRIPTION = '';
	
	if (check_class($download_category_class)) 
	{
		if(strstr($download_category_icon, chr(1)))
		{
			list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
		}
		$DOWNLOAD_CAT_MAIN_ICON = ($download_category_icon ? "<img src='".e_IMAGE."icons/".$download_category_icon."' alt='' style='float: left' />" : "&nbsp;");
		$DOWNLOAD_CAT_MAIN_NAME = $tp->toHTML($download_category_name,FALSE,'USER_TITLE');
		$DOWNLOAD_CAT_MAIN_DESCRIPTION = $tp->toHTML($row['download_category_description'],TRUE,'DESCRIPTION');
	}
	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}


	  function get_cat_icons($source, $count)
	  {
	    if (!$source) return "&nbsp;";
	    list($ret[TRUE],$ret[FALSE]) = explode(chr(1), $source.chr(1));
	    if (!$ret[FALSE]) $ret[FALSE] = $ret[TRUE];
		return 	"<img src='".e_IMAGE."icons/{$ret[($count!=0)]}' alt='' style='float: left' />";
	  }
	
	  function check_new_download($last_val)
	  {
		if(USER && ($last_val > USERLV))
		{
		  return "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
		}
		else
		{
		  return "";
		}
	  }


function parse_download_cat_child_table($row)
{
	global $tp,$e107,$current_row, $DOWNLOAD_CAT_CHILD_TABLE, $DOWNLOAD_CAT_SUBSUB_TABLE;

	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_CAT_CHILD_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_CAT_CHILD_TABLE);
	
	$DOWNLOAD_CAT_SUB_ICON = get_cat_icons($row['download_category_icon'],$row['d_count']);
	$DOWNLOAD_CAT_SUB_NEW_ICON = check_new_download($row['d_last_subs']);
	$dcatname=$tp->toHTML($row['download_category_name'],FALSE,'USER_TITLE');
	$DOWNLOAD_CAT_SUB_NAME = ($row['d_count'] ? "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$dcatname."</a>" : $dcatname);
	$DOWNLOAD_CAT_SUB_NAME_LINKED = "<a href='".e_BASE."download.php?list.".$row['download_category_id']."'>".$dcatname."</a>";
	$DOWNLOAD_CAT_SUB_DESCRIPTION = $tp->toHTML($row['download_category_description'],TRUE,'DESCRIPTION');
	$DOWNLOAD_CAT_SUB_COUNT = $row['d_count'];
	$DOWNLOAD_CAT_SUB_SIZE = $e107->parseMemorySize($row['d_size']);
	$DOWNLOAD_CAT_SUB_DOWNLOADED = intval( $row['d_requests']);
	$DOWNLOAD_CAT_SUBSUB = "";
	// check for subsub cats ...
	foreach($row['subsubcats'] as $subrow)
	{
	  $DOWNLOAD_CAT_SUBSUB_ICON = get_cat_icons($subrow['download_category_icon'],$subrow['d_count']);
	  $DOWNLOAD_CAT_SUBSUB_DESCRIPTION = $tp->toHTML($subrow['download_category_description'],TRUE,'DESCRIPTION');
	  $DOWNLOAD_CAT_SUBSUB_COUNT = intval($subrow['d_count']);
	  $DOWNLOAD_CAT_SUBSUB_SIZE = $e107->parseMemorySize($subrow['d_size']);
	  $DOWNLOAD_CAT_SUBSUB_DOWNLOADED = intval($subrow['d_requests']);

	  $DOWNLOAD_CAT_SUBSUB_NEW_ICON = check_new_download($subrow['d_last']);
	  $DOWNLOAD_CAT_SUBSUB_NAME = ($subrow['d_count'] ? "<a href='".e_BASE."download.php?list.".$subrow['download_category_id']."'>".$tp->toHTML($subrow['download_category_name'], FALSE, 'USER_TITLE')."</a>" : $tp->toHTML($subrow['download_category_name'],FALSE,'USER_TITLE'));
	  $DOWNLOAD_CAT_SUBSUB .= preg_replace("/\{(.*?)\}/e", '$\1', $DOWNLOAD_CAT_SUBSUB_TABLE);
	}

	return(preg_replace("/\{(.*?)\}/e", '$\1', $template));
}


function parse_download_list_table($row) 
{
// ***** $agreetext may not need to be global
	global $download_shortcodes,$tp,$current_row,$DOWNLOAD_LIST_TABLE, $rater, $pref, $gen, $agreetext;

	$agreetext = $tp->toHTML($pref['agree_text'],TRUE,'DESCRIPTION');
	$current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	$template = ($current_row == 1) ? $DOWNLOAD_LIST_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_LIST_TABLE);

	return $tp->parseTemplate($template,TRUE,$download_shortcodes);

}


//=============================================
//		DOWNLOAD CATEGORY CLASS
//=============================================

class down_cat_handler
{
  var $cat_tree;			// Initialised with all categories in a tree structure
  var $cat_count;			// Count visible subcats and subsubcats
  var $down_count;			// Counts total downloads
  
  function down_cat_handler($nest_level = 1, $load_class = USERCLASS_LIST, $main_cat_load = '', $accum = FALSE)
  {  // Constructor - make a copy of the tree for re-use
     // $nest_level = 0 merges subsubcats with subcats. >0 creates full tree.
	 // If load-class non-null, assumed to be a 'class set' such as USERCLASS_LIST
	 // If $accum is TRUE, include file counts and sizes in superior categories
	define("SUB_PREFIX","-->");				// Added in front of sub categories
	define("SUBSUB_PREFIX","---->");		// Added in front of sub-sub categories
    $this->cat_tree = $this->down_cat_tree($nest_level,$load_class, $main_cat_load, $accum);
  }
  
  
// Function returns a 'tree' of download categories, subcategories, and sub-sub-categories.
// Returns empty array if nothing defined
// Within the 'main category' level of the nesting, array 'subcats' has the next level's info
// Within the 'sub-category' level of the nesting, array 'subsubcats' has the next level's info
// If $main_cat_load is numeric, and the value of a 'main' category, only that main category is displayed.
//		(Unpredictable if $main_cat_load is some other category)
	function down_cat_tree($nest_level = 1, $load_cat_class = USERCLASS_LIST, $main_cat_load = '', $accum = FALSE)
	{
	  global $sql2;

	  $catlist = array();
	  $this->cat_count = 0;
	  $this->down_count = 0;
	  $temp2 = "";
	  $temp1 = "";
	  if ($load_cat_class != "")
	  {
		$temp1 = " WHERE dc.download_category_class IN ({$load_cat_class}) ";
		$temp2 = "AND d.download_visible IN ({$load_cat_class}) ";
	  }
	  
	  $qry = "
	  SELECT dc.*, 
	  dc1.download_category_parent AS d_parent1, dc1.download_category_order,
	  SUM(d.download_filesize) AS d_size, 
	  COUNT(d.download_id) AS d_count,
	  MAX(d.download_datestamp) as d_last,
	  SUM(d.download_requested) as d_requests
	  FROM #download_category as dc 
	  LEFT JOIN #download_category as dc1 ON dc1.download_category_id=dc.download_category_parent 
	  LEFT JOIN #download_category as dc2 ON dc2.download_category_id=dc1.download_category_parent 
	  LEFT JOIN #download AS d on d.download_category = dc.download_category_id AND d.download_active > 0 {$temp2}
	  {$temp1}
	  GROUP by dc.download_category_id
	  ORDER by dc2.download_category_order, dc1.download_category_order, dc.download_category_order";   // This puts main categories first, then sub-cats, then sub-sub cats

  	  if (!$sql2->db_Select_gen($qry)) return $catlist;
	  
	  while ($row = $sql2->db_Fetch()) 
	  {
	    $tmp = $row['download_category_parent'];
	    if ($tmp == '0')
	    {  // Its a main category
		  if (!is_numeric($main_cat_load) || ($main_cat_load == $row['download_category_id']))
		  {
		    $row['subcats'] = array();
	        $catlist[$row['download_category_id']] = $row;
		  }
	    }
	    else
	    {
	      if (isset($catlist[$tmp]))
		  {  // Sub-Category
		    $this->cat_count++;
			$this->down_count += $row['d_count'];
		    $catlist[$tmp]['subcats'][$row['download_category_id']] = $row;
		    $catlist[$tmp]['subcats'][$row['download_category_id']]['subsubcats'] = array();
		    $catlist[$tmp]['subcats'][$row['download_category_id']]['d_last_subs'] = 
					$catlist[$tmp]['subcats'][$row['download_category_id']]['d_last'];
		  }
		  else
		  {  // Its a sub-sub category
		    if (isset($catlist[$row['d_parent1']]['subcats'][$tmp]))
			{
		      $this->cat_count++;
			  $this->down_count += $row['d_count'];
			  if ($accum || ($nest_level == 0))
			  {  // Add the counts into the subcategory values
				$catlist[$row['d_parent1']]['subcats'][$tmp]['d_size'] += $row['d_size'];
				$catlist[$row['d_parent1']]['subcats'][$tmp]['d_count'] += $row['d_count'];
				$catlist[$row['d_parent1']]['subcats'][$tmp]['d_requests'] += $row['d_requests'];
			  }
		      if ($nest_level == 0)
			  {  // Reflect subcat dates in category
				if ($catlist[$row['d_parent1']]['subcats'][$tmp]['d_last'] < $row['d_last'])
				    $catlist[$row['d_parent1']]['subcats'][$tmp]['d_last'] = $row['d_last'];
			  }
			  else
		      {
		        $catlist[$row['d_parent1']]['subcats'][$tmp]['subsubcats'][$row['download_category_id']] = $row;
		      }
		       // Separately accumulate 'last update' for subcat plus associated subsubcats
			  if ($catlist[$row['d_parent1']]['subcats'][$tmp]['d_last_subs'] < $row['d_last'])
				    $catlist[$row['d_parent1']]['subcats'][$tmp]['d_last_subs'] = $row['d_last'];
			}
	      }
	    }
	  }
	  return $catlist;
	}

	
// Rest of the class isn't actually used normally, but print_tree() might help with debug	

    function print_cat($cat, $prefix,$postfix)
	{
	  $text = "<tr><td>".$cat['download_category_id']."</td><td>".$cat['download_category_parent']."</td><td>";
	  $text .= $prefix.htmlspecialchars($cat['download_category_name']).$postfix."</td><td>".$cat['d_size']."</td>";
	  $text .= "<td>".$cat['d_count']."</td><td>".$cat['d_requests']."</td><td>".strftime('%H:%M %d-%m-%Y',$cat['d_last'])."</td>";
	  $text .= "</tr>";
	  return $text;
	}

	function print_tree()
	{ 
	  echo "<table><tr><th>ID</th><th>Parent</th><th>Name</th><th>Bytes</th><th>Files</th><th>Requests</th><th>Last Download</th><tr>";
	  foreach ($this->cat_tree as $thiscat)
	  {  // Main categories
		$scprefix = SUB_PREFIX;
        echo $this->print_cat($thiscat,'<strong>','</strong>');
	    foreach ($thiscat['subcats'] as $sc)
	    {  // Sub-categories
		  $sscprefix = SUBSUB_PREFIX;
		  echo $this->print_cat($sc,$scprefix,'');
		  foreach ($sc['subsubcats'] as $ssc)
		  {  // Sub-sub categories
		    echo $this->print_cat($ssc,$sscprefix,'');
		  }
		}
	  }
	  echo "</table>";
	return;
    }
	
}




?>
