<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/download.php,v $
 * $Revision: 1.12 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT'))
{ 
	require_once("../../class2.php");
}

if (!e107::isInstalled('download'))
{
	header("location:".e_BASE."index.php"); 
}

include_lan(e_PLUGIN.'download/languages/'.e_LANGUAGE.'/download.php');
require_once(e_PLUGIN.'download/handlers/download_class.php');
require_once(e_PLUGIN.'download/handlers/category_class.php');
require_once(e_PLUGIN.'download/download_shortcodes.php');
require_once(e_HANDLER.'comment_class.php');

$dl = new download();
$cobj = new comment();
$dl_text = '';			// Output variable

if(!defined("USER_WIDTH")) { define("USER_WIDTH","width:100%"); }

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
	require_once(e_PLUGIN."download/templates/".$template_name);
  }
';

$order_options = array('download_id','download_datestamp','download_requested','download_name','download_author','download_requested');
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
	   $action = varset(preg_replace("#\W#", "", $tp->toDB($tmp[1])),'list');
	   $id = intval($tmp[2]);
	   $view = intval($tmp[3]);
	   $order = preg_replace("#\W#", "", $tp->toDB($tmp[4]));
	   $sort = preg_replace("#\W#", "", $tp->toDB($tmp[5]));
   }
   else
   {
	   $action = preg_replace("#\W#", "", $tp->toDB($tmp[0]));
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
	   	   $dlrow = $sql->db_Fetch();
	   	   extract($dlrow);
	   	   $type = $download_category_name;
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
	         case 1 :			// No permissions
	            if (strlen($pref['download_denied']) > 0) {
	   	         $errmsg = $tp->toHTML($pref['download_denied'],true);
	   	      } else {
	   	         $errmsg = LAN_dl_63;
	   	      }
	   	      break;
	   	   case 2 :			// Quota exceeded
	   	      $errmsg = LAN_dl_62;
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
	   if ($cacheData = $e107cache->retrieve("download_cat".$maincatval,720)) // expires every 12 hours. //TODO make this an option
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
      $dlcat = new downloadCategory(varset($pref['download_subsub'],1),USERCLASS_LIST,$maincatval,varset($pref['download_incinfo'],FALSE));

	   if ($dlcat->down_count == 0)
	   {
	      $ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_2."</div>");
	      require_once(FOOTERF);
	      exit;
	   }

	   $download_cat_table_string = "";
	   foreach($dlcat->cat_tree as $dlrow)
	   {  // Display main category headings, then sub-categories, optionally with sub-sub categories expanded
         $download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_PARENT_TABLE, TRUE, $download_shortcodes);
	      foreach($dlrow['subcats'] as $dlsubrow)
	      {
            $download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_CHILD_TABLE, TRUE, $download_shortcodes);
	         foreach($dlsubrow['subsubcats'] as $dlsubsubrow)
	         {
               $download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_SUBSUB_TABLE, TRUE, $download_shortcodes);
	         }
	      }
	   }
	   $dl_text  = $tp->parseTemplate($DOWNLOAD_CAT_TABLE_START, TRUE, $download_shortcodes);
	   $dl_text .= $download_cat_table_string;
	   $dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_END, TRUE, $download_shortcodes);
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18));
	   $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);

	   ob_start();
      $ns->tablerender($dl_title, $dl_text);
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
		$dlrow = $sql->db_Fetch();
		if ($dlrow['download_comment'] && (ANON === TRUE || USER === TRUE))
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
	if($sql->db_Select("download_category", "download_category_id", "download_category_parent='{$id}' "))
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
		$scArray = $sql->db_getList();
		$load_template = 'download_template';
		if (!isset($DOWNLOAD_CAT_PARENT_TABLE)) eval($template_load_core);
		if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	   $download_cat_table_string = "";
	   $dl_text  = $tp->parseTemplate($DOWNLOAD_CAT_TABLE_PRE, TRUE, $download_shortcodes);
	   $dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_START, TRUE, $download_shortcodes);
      foreach($scArray as $dlsubsubrow)
	   {
         $dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_SUBSUB_TABLE, TRUE, $download_shortcodes);
	   }
	   $dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_END, TRUE, $download_shortcodes);
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $type, DOWLAN_54));
	   $dl_title = $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
		$ns->tablerender($dl_title, $dl_text);
		$text = "";		   // If other files, show in a separate block
		$dl_title = "";   // Cancel title once displayed
	}  // End of subcategory display

   // Now display individual downloads
	if (!check_class($download_category_class))
	{
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	if ($total_downloads < $view) { $dl_from = 0; }

	$load_template = 'download_template';
	if (!isset($DOWNLOAD_LIST_TABLE)) eval($template_load_core);
   if (!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	require_once(e_HANDLER."rate_class.php");
	$dltdownloads = 0;

	// $dl_from - first entry to show  (note - can get reset due to reuse of query, even if values overridden this time)
	// $view - number of entries per page
	// $total_downloads - total number of entries matching search criteria
	$filetotal = $sql->db_Select("download", "*", "download_category='{$id}' AND download_active > 0 AND download_visible IN (".USERCLASS_LIST.") ORDER BY {$order} {$sort} LIMIT {$dl_from}, {$view}");
	if ($filetotal)
	{  // Only show list if some files in it
      $dl_text = $tp->parseTemplate($DOWNLOAD_LIST_TABLE_START, TRUE, $download_shortcodes);
   	$dlft = ($filetotal < $view ? $filetotal : $view);
   	while ($dlrow = $sql->db_Fetch())
   	{
	      $agreetext = $tp->toHTML($pref['agree_text'],TRUE,'DESCRIPTION');
	      $current_row = ($current_row) ? 0 : 1;  // Alternating CSS for each row.(backwards compatible)
	      $template = ($current_row == 1) ? $DOWNLOAD_LIST_TABLE : str_replace("forumheader3","forumheader3 forumheader3_alt",$DOWNLOAD_LIST_TABLE);
   		$dl_text .= $tp->parseTemplate($template,TRUE,$download_shortcodes);;
   		$dltdownloads += $dlrow['download_requested'];
   	}

      $dl_text .= $tp->parseTemplate($DOWNLOAD_LIST_TABLE_END, TRUE, $download_shortcodes);
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $type));
      $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
		$ns->tablerender($dl_title, $dl_text);
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
} // end of action=="list"

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

	if(!$sql->db_Select_gen($query)){
		require_once(HEADERF);
		$ns->tablerender(LAN_dl_18, "<div style='text-align:center'>".LAN_dl_3."</div>");
		require_once(FOOTERF);
		exit;
	}

	$dlrow = $sql->db_Fetch();

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

   $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $dlrow['download_category_name']=>e_SELF."?list.".$dlrow['download_category_id'], $dlrow['download_name']));
   $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
	$ns->tablerender($dl_title, $text);

	unset($text);

	if ($dlrow['download_comment']) {
		$cobj->compose_comment("download", "comment", $id, $width,$dlrow['download_name'], $showrate=FALSE);
	}

	require_once(FOOTERF);
	exit;

}

//====================================================
//				REPORT BROKEN LINKS
//====================================================
if ($action == "report" && check_class($pref['download_reportbroken']))
{
    $query = "
		SELECT d.*, dc.* FROM #download AS d
		LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
		WHERE d.download_id = {$id}
		  AND download_active > 0
		LIMIT 1";

	if(!$sql->db_Select_gen($query))
	{
	//if (!$sql->db_Select("download", "*", "download_id = {$id} AND download_active > 0")) {
		require_once(HEADERF);
		require_once(FOOTERF);
		exit;
	}

	$dlrow = $sql->db_Fetch();
	extract($dlrow);

	if (isset($_POST['report_download'])) {
		$report_add = $tp->toDB($_POST['report_add']);
		$download_name = $tp->toDB($download_name);
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

		$text = LAN_dl_48."<br /><br /><a href='".e_PLUGIN."download/download.php?view.".$download_id."'>".LAN_dl_49."</a";
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $dlrow['download_category_name']=>e_SELF."?list.".$dlrow['download_category_id'], $dlrow['download_name']=>e_SELF."?view.".$dlrow['download_id'], LAN_dl_50));
      $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
		$ns->tablerender($dl_title, $text);
	} else {
		define("e_PAGETITLE", PAGE_NAME." / ".LAN_dl_51." ".$download_name);
		require_once(HEADERF);

		$text = "<form action='".e_SELF."?report.{$download_id}' method='post'>
		   <table style='".USER_WIDTH."'>
		   	<tr>
		   	   <td  style='width:50%' >
		   	      ".LAN_dl_32.": ".$download_name."<br />
		   	      <a href='".e_PLUGIN."download/download?view.{$download_id}'>
		   	      <span class='smalltext'>".LAN_dl_53."</span>
		   	      </a>
		   	   </td>
		   	   <td style='text-align:center;width:50%'>
		   	   </td>
		   	</tr>
		   	<tr>
		   	   <td>".LAN_dl_54."<br />".LAN_dl_55."</td>
		   	   <td style='text-align:center;'>
   		   	   <textarea cols='40' rows='10' class='tbox' name='report_add'></textarea>
		   	   </td>
		   	</tr>
		   	<tr>
		   	   <td colspan='2' style='text-align:center;'><br />
	   	   	   <input class='button' type='submit' name='report_download' value=\"".LAN_dl_45."\" />
		   	   </td>
		   	</tr>
			</table>
	   </form>";
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $dlrow['download_category_name']=>e_SELF."?list.".$dlrow['download_category_id'], $dlrow['download_name']=>e_SELF."?view.".$dlrow['download_id'], LAN_dl_50));
      $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
		$ns->tablerender($dl_title, $text);
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

	$sql->db_Select("download_mirror");
	$mirrorList = $sql->db_getList("ALL", 0, 200, "mirror_id");

    $query = "
		SELECT d.*, dc.* FROM #download AS d
		LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
		WHERE d.download_id = {$id}
		LIMIT 1";

	if($sql->db_Select_gen($query))
	{
		$dlrow = $sql->db_Fetch();
		$array = explode(chr(1), $dlrow['download_mirror']);
      if (2 == varset($pref['mirror_order']))
      {
         // Order by name, sort array manually
         usort($array, "sort_download_mirror_order");
      }
      //elseif (1 == varset($pref['mirror_order']))
      //{
      //   // Order by ID  - do nothing order is as stored in DB
      //}
      elseif (0 == varset($pref['mirror_order'], 0))
      {
		   // Shuffle the mirror list into a random order
		   $c = count($array);
		   for ($i=1; $i<$c; $i++)
		   {
		     $d = mt_rand(0, $i);
		     $tmp = $array[$i];
		     $array[$i] = $array[$d];
		     $array[$d] = $tmp;
		   }
		}

   	$dl_text = $tp->parseTemplate($DOWNLOAD_MIRROR_START, TRUE, $download_shortcodes);
		$download_mirror = 1;
		foreach($array as $mirrorstring)
		{
		   if($mirrorstring)
		   {
	         $dlmirrorfile = explode(",", $mirrorstring);
	         $dlmirror = $mirrorList[$dlmirrorfile[0]];
      	   $dl_text .= $tp->parseTemplate($DOWNLOAD_MIRROR, TRUE, $download_shortcodes);
		   }
		}
	   $dl_text .= $tp->parseTemplate($DOWNLOAD_MIRROR_END, TRUE, $download_shortcodes);
      $dlbreadcrumb = $dl->getBreadcrumb(array(LAN_dl_18=>e_SELF, $dlrow['download_category_name']=>e_SELF."?list.".$dlrow['download_category_id'], $dlrow['download_name']=>e_SELF."?view.".$dlrow['download_id'], LAN_dl_67));
      $dl_title .= $tp->parseTemplate("{BREADCRUMB=dlbreadcrumb}", TRUE, $download_shortcodes);
   	$ns->tablerender($dl_title, $dl_text);
		require_once(FOOTERF);
	}
}

function sort_download_mirror_order($a, $b)
{
   $a = explode(",", $a);
   $b = explode(",", $b);
   if ($a[1] == $b[1]) {
      return 0;
   }
   return ($a[1] < $b[1]) ? -1 : 1;
}
?>