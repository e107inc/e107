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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/download/admin_download.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-07-23 09:02:10 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

$eplug_admin = true;
define('DOWNLOAD_DEBUG',FALSE);

require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('download'))
{
	header("location:".e_BASE."index.php");
	exit() ;
}

include_lan(e_PLUGIN.'download/languages/'.e_LANGUAGE.'/download.php');
include_lan(e_PLUGIN.'download/languages/'.e_LANGUAGE.'/admin_download.php');
require_once(e_PLUGIN.'download/handlers/adminDownload_class.php');
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
$gen = new convert();

function headerjs()
{
   global $cal;
	return $cal->load_files()."\n\n<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>";
}

$e_sub_cat = 'download';
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."file_class.php");

$fl = new e_file;

// -------- Presets. ------------
require_once(e_HANDLER."preset_class.php");
$pst = new e_preset;
$pst->form = array("myform","dlform"); // form id of the form that will have it's values saved.
$pst->page = array("download.php?create","download.php?cat"); // display preset options on which page(s).
$pst->id = array("admin_downloads","admin_dl_cat");
// -------------------------------

$download = new download();
$adminDownload = new adminDownload();
require_once(e_ADMIN."auth.php");
$pst->save_preset();  // unique name(s) for the presets - comma separated.

 /*
One form example (no arrays needed)
$pst->form = "myform"; // form id of the form that will have it's values saved.
$pst->page = "download.php?create"; // display preset options on which page.
$pst->save_preset("admin_downloads");  // unique name for the preset
*/

$rs = new form;
$subAction = '';
if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$subAction = varset($tmp[1],'');
	$id = varset($tmp[2],'');
	$from = varset($tmp[3], 0);
	$maintPage = varset($tmp[4], '');
	unset($tmp);
}

$adminDownload->observer();

if(isset($_POST['download_filter_list']))
{
	echo $adminDownload->show_existing_items($action, $subAction, $id, 0, 10);
	exit;
}

if (isset($_POST['delete']))
{
	$tmp = array_keys($_POST['delete']);
	list($delete, $del_id) = explode("_", $tmp[0]);
	$del_id = intval($del_id);
	unset($_POST['searchquery']);
}

$from = ($from ? $from : 0);
$amount = varset($pref['download_view'], 50);

if (isset($_POST))
{
	$e107cache->clear("download_cat");
}

if (isset($_POST['add_category']))
{
	$adminDownload->create_category($subAction, $id);
}


if (isset($_POST['submit_download']))
{
	$adminDownload->submit_download($subAction, $id);
	$action = "main";
	unset($subAction, $id);
}


if (isset($_POST['update_catorder']))
{
	foreach($_POST['catorder'] as $key=>$order)
	{
		if (is_numeric($_POST['catorder'][$key]))
		{
			$sql -> db_Update("download_category", "download_category_order='".intval($order)."' WHERE download_category_id='".intval($key)."'");
		}
	}
	$admin_log->log_event('DOWNL_08',implode(',',array_keys($_POST['catorder'])),E_LOG_INFORMATIVE,'');
	$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}


if (isset($_POST['updateoptions']))
{
	unset($temp);
	$temp['download_php'] = $_POST['download_php'];
	$temp['download_view'] = $_POST['download_view'];
	$temp['download_sort'] = $_POST['download_sort'];
	$temp['download_order'] = $_POST['download_order'];
	$temp['mirror_order'] = $_POST['mirror_order'];
	$temp['recent_download_days'] = $_POST['recent_download_days'];
	$temp['agree_flag'] = $_POST['agree_flag'];
	$temp['download_email'] = $_POST['download_email'];
	$temp['agree_text'] = $tp->toDB($_POST['agree_text']);
	$temp['download_denied'] = $tp->toDB($_POST['download_denied']);
	$temp['download_reportbroken'] = $_POST['download_reportbroken'];
	if ($_POST['download_subsub']) $temp['download_subsub'] = '1'; else $temp['download_subsub'] = '0';
	if ($_POST['download_incinfo']) $temp['download_incinfo'] = '1'; else $temp['download_incinfo'] = '0';
	if ($admin_log->logArrayDiffs($temp, $pref, 'DOWNL_01'))
	{
		save_prefs();
		$message = DOWLAN_65;
	}
	else
	{
		$message = DOWLAN_8;
	}
}

$targetFields = array('gen_datestamp', 'gen_user_id', 'gen_ip', 'gen_intdata', 'gen_chardata');		// Fields for download limits

if (isset($_POST['addlimit']))
{
	if ($sql->db_Select('generic','gen_id',"gen_type = 'download_limit' AND gen_datestamp = {$_POST['newlimit_class']}"))
	{
		$message = DOWLAN_116;
	}
	else
	{
		$vals = array();
		$vals['gen_type'] = 'download_limit';
		foreach(array('newlimit_class','new_bw_num','new_bw_days','new_count_num','new_count_days') as $k => $lName)
		{
			$vals[$targetFields[$k]] = intval($_POST[$lName]);
		}
		$valString = implode(',',$vals);
		if ($sql->db_Insert('generic',$vals))
		{
			$message = DOWLAN_117;
			$admin_log->log_event('DOWNL_09',$valString,E_LOG_INFORMATIVE,'');
		}
		else
		{
			$message = DOWLAN_118;
		}
		unset($vals);
	}
}


if (isset($_POST['updatelimits']))
{

	if ($pref['download_limits'] != $_POST['download_limits'])
	{
		$pref['download_limits'] = ($_POST['download_limits'] == 'on') ? 1 : 0;
		save_prefs();
		$message .= DOWLAN_126."<br/>";
	}
	foreach(array_keys($_POST['count_num']) as $idLim)
	{
		$idLim = intval($idLim);
		if (!$_POST['count_num'][$idLim] && !$_POST['count_days'][$idLim] && !$_POST['bw_num'][$idLim] && !$_POST['bw_days'][$idLim])
		{
			//All entries empty - Remove record
			if ($sql->db_Delete('generic',"gen_id = {$idLim}"))
			{
				$message .= $idLim." - ".DOWLAN_119."<br/>";
				$admin_log->log_event('DOWNL_11','ID: '.$idLim,E_LOG_INFORMATIVE,'');
			}
			else
			{
				$message .= $idLim." - ".DOWLAN_120."<br/>";
			}
		}
		else
		{
			$vals = array();
			foreach(array('bw_num','bw_days','count_num','count_days') as $k => $lName)
			{
				$vals[$targetFields[$k+1]] = intval($_POST[$lName][$idLim]);
			}
			$valString = implode(',',$vals);
			$sql->db_UpdateArray('generic',$vals," WHERE gen_id = {$idLim}");
			$admin_log->log_event('DOWNL_10',$idLim.', '.$valString,E_LOG_INFORMATIVE,'');
			$message .= $idLim." - ".DOWLAN_121."<br/>";
			unset($vals);
		}
	}
}


if (isset($_POST['submit_mirror']))
{
	$adminDownload->submit_mirror($subAction, $id);
}


if ($action == "mirror")
{
	$adminDownload->show_existing_mirrors();
}


if ($action == "dlm")
{
	$action = "create";
	$id = $subAction;
	$subAction = "dlm";
}


if ($action == "create")
{
	$adminDownload->create_download($subAction, $id);
}


if (isset($delete) && $delete == 'category')
{
  if (admin_update($sql->db_Delete('download_category', 'download_category_id='.$del_id), 'delete', DOWLAN_49." #".$del_id." ".DOWLAN_36))
  {
		$sql->db_Delete('download_category', 'download_category_parent='.$del_id);
		$admin_log->log_event('DOWNL_04',$del_id,E_LOG_INFORMATIVE,'');
	}
}


if ($action == 'cat')
{
	$adminDownload->show_categories($subAction, $id);
}


if (isset($delete) && $delete == 'main')
{
	$result = $sql->db_Delete('download', 'download_id='.$del_id);
	if ($result)
	{
      // Process triggers before calling admin_update so trigger messages can be shown
      $data = array('method'=>'delete', 'table'=>'download', 'id'=>$del_id, 'plugin'=>'download', 'function'=>'delete_download');
      $hooks = $e107->e_event->triggerHook($data);
      require_once(e_HANDLER."message_handler.php");
      $emessage = &eMessage::getInstance();
      $emessage->add($hooks, E_MESSAGE_SUCCESS);

	   admin_update($result, 'delete', DOWLAN_27." #".$del_id." ".DOWLAN_36);

		$admin_log->log_event('DOWNL_07',$del_id,E_LOG_INFORMATIVE,'');
		admin_purge_related('download', $del_id);
		$e_event->trigger('dldelete', $del_id);
	}
	unset($subAction, $id);
}

if (isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if ($from === "maint" && isset($_POST['submit_download']))
{ // Return to one of the maintanence pages after submitting the create/edit form
   $action = $from;
   $subAction = $maintPage;
}

if (!e_QUERY || $action == "main")
{
	$text = $adminDownload->show_filter_form($action, $subAction, $id, $from, $amount);
	$text .= $adminDownload->show_existing_items($action, $subAction, $id, $from, $amount);
	$ns->tablerender(DOWLAN_7, $text);
}




if ($action == "opt")
{
	$adminDownload->show_options();
}

if ($action == 'maint')
{
	global $pref, $ns;
   if (isset($_POST['dl_maint'])) {
      switch ($_POST['dl_maint'])
      {
         case 'duplicates':
         {
            $title = DOWLAN_166;
            $query = 'SELECT GROUP_CONCAT(d.download_id SEPARATOR ",") as gc, d.download_id, d.download_name, d.download_url, dc.download_category_name
                      FROM #download as d
                      LEFT JOIN #download_category AS dc ON dc.download_category_id=d.download_category
                      GROUP BY d.download_url
                      HAVING COUNT(d.download_id) > 1
               ';
            $text = "";
            $count = $sql->db_Select_gen($query);
            $foundSome = false;
            if ($count) {
               $currentURL = "";
               while($row = $sql->db_Fetch()) {
                  if (!$foundSome) {
   		            $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                     $text .= '<table class="adminlist" style="width:100%">';
                     $text .= '<tr>';
                     $text .= '<th>'.DOWLAN_13.'</th>';
                     $text .= '<th>'.DOWLAN_67.'</th>';
                     $text .= '<th>'.DOWLAN_27.'</th>';
                     $text .= '<th>'.DOWLAN_11.'</th>';
                     $text .= '<th>'.LAN_OPTIONS.'</th>';
                     $text .= '</tr>';
                     $foundSome = true;
                  }
                  $query = "SELECT d.*, dc.* FROM `#download` AS d
                     LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category
                     WHERE download_id IN (".$row['gc'].")
                     ORDER BY download_id ASC";
                  $count = $sql2->db_Select_gen($query);
                  while($row = $sql2->db_Fetch()) {
                     $text .= '<tr>';
                     if ($currentURL != $row['download_url']) {
                        $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
                        $currentURL = $row['download_url'];
                     } else {
                        $text .= '<td>*</td>';
                     }
                     $text .= '<td>'.$row['download_id'].'</td>';
                     $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
                     $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
                     $text .= '<td>
                                 <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.duplicates">'.ADMIN_EDIT_ICON.'</a>
   				                  <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
   				               </td>';
                     $text .= '</tr>';
                  }
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_172;
            }
            break;
         }
         case 'orphans':
         {
            $title = DOWLAN_167;
            $text = "";
            require_once(e_HANDLER."file_class.php");
            $efile = new e_file();
            $files = $efile->get_files(e_DOWNLOAD);
            $foundSome = false;
            foreach($files as $file) {
               if (0 == $sql->db_Count('download', '(*)', " WHERE download_url='".$file['fname']."'")) {
                  if (!$foundSome) {
   		            $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                     $text .= '<table class="adminlist" style="width:100%">';
                     $text .= '<tr>';
                     $text .= '<th>'.DOWLAN_13.'</th>';
                     $text .= '<th>'.DOWLAN_182.'</th>';
                     $text .= '<th>'.DOWLAN_170.'</th>';
                     $text .= '<th>'.LAN_OPTIONS.'</th>';
                     $text .= '</tr>';
                     $foundSome = true;
                  }
                  $filesize = (is_readable(e_DOWNLOAD.$row['download_url']) ? $e107->parseMemorySize(filesize(e_DOWNLOAD.$file['fname'])) : DOWLAN_181);
                  $filets   = (is_readable(e_DOWNLOAD.$row['download_url']) ? $gen->convert_date(filectime(e_DOWNLOAD.$file['fname']), "long") : DOWLAN_181);
                  $text .= '<tr>';
                  $text .= '<td>'.$e107->tp->toHTML($file['fname']).'</td>';
                  $text .= '<td>'.$filets.'</td>';
                  $text .= '<td>'.$filesize.'</td>';
   //TODO               $text .= '<td>
   //TODO                           <a href="'.e_SELF.'?create.add.'. urlencode($file["fname"]).'">'.E_16_CREATE.'</a>
   //TODO					            <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$file["fname"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_173.' [ '.$file["fname"].' ]').'") \'/>
   //TODO					         </td>';
                  $text .= '</tr>';
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_174;
            }
            break;
         }
         case 'missing':
         {
            $title = DOWLAN_168;
            $text = "";
            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category";
            $count = $sql->db_Select_gen($query);
            $foundSome = false;
            if ($count) {
               while($row = $sql->db_Fetch()) {
                  if (!is_readable(e_DOWNLOAD.$row['download_url'])) {
                     if (!$foundSome) {
   		               $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                        $text .= '<table class="adminlist" style="width:100%">';
                        $text .= '<tr>';
                        $text .= '<th>'.DOWLAN_67.'</th>';
                        $text .= '<th>'.DOWLAN_27.'</th>';
                        $text .= '<th>'.DOWLAN_11.'</th>';
                        $text .= '<th>'.DOWLAN_13.'</th>';
                        $text .= '<th>'.LAN_OPTIONS.'</th>';
                        $text .= '</tr>';
                        $foundSome = true;
                     }
                     $text .= '<tr>';
                     $text .= '<td>'.$row['download_id'].'</td>';
                     $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
                     $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
                     $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
                     $text .= '<td>
                                 <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.missing">'.ADMIN_EDIT_ICON.'</a>
   					               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
   					            </td>';
                     $text .= '</tr>';
                  }
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_172;
            }
            break;
         }
         case 'inactive':
         {
            $title = DOWLAN_169;
            $text = "";
            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category WHERE download_active=0";
            $count = $sql->db_Select_gen($query);
            $foundSome = false;
            if ($count) {
               while($row = $sql->db_Fetch()) {
                  if (!$foundSome) {
   		            $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                     $text .= '<table class="adminlist" style="width:100%">';
                     $text .= '<tr>';
                     $text .= '<th>'.DOWLAN_67.'</th>';
                     $text .= '<th>'.DOWLAN_27.'</th>';
                     $text .= '<th>'.DOWLAN_11.'</th>';
                     $text .= '<th>'.DOWLAN_13.'</th>';
                     $text .= '<th>'.LAN_OPTIONS.'</th>';
                     $text .= '</tr>';
                     $foundSome = true;
                  }
                  $text .= '<tr>';
                  $text .= '<td>'.$row['download_id'].'</td>';
                  $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
                  $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
                  if (strlen($row['download_url']) > 0) {
                     $text .= '<td>'.$row['download_url'].'</td>';
                  } else {
   					   $mirrorArray = download::makeMirrorArray($row['download_mirror'], TRUE);
                     $text .= '<td>';
                     foreach($mirrorArray as $mirror) {
                        $text .= $mirror['url'].'<br/>';
                     }
                     $text .= '</td>';
                  }
                  $text .= '<td>
                              <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.inactive">'.ADMIN_EDIT_ICON.'</a>
   				               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
   				            </td>';
                  $text .= '</tr>';
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_172;
            }
            break;
         }
         case 'nocategory':
         {
            $title = DOWLAN_178;
            $text = "";
            $query = "SELECT * FROM `#download` WHERE download_category=0";
            $count = $sql->db_Select_gen($query);
            $foundSome = false;
            if ($count) {
               while($row = $sql->db_Fetch()) {
                  if (!$foundSome) {
   		            $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                     $text .= '<table class="adminlist">';
                     $text .= '<tr>';
                     $text .= '<th>'.DOWLAN_67.'</th>';
                     $text .= '<th>'.DOWLAN_27.'</th>';
                     $text .= '<th>'.DOWLAN_13.'</th>';
                     $text .= '<th>'.LAN_OPTIONS.'</th>';
                     $text .= '</tr>';
                     $foundSome = true;
                  }
                  $text .= '<tr>';
                  $text .= '<td>'.$row['download_id'].'</td>';
                  $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
                  if (strlen($row['download_url']) > 0) {
                     $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
                  } else {
   					   $mirrorArray = download::makeMirrorArray($row['download_mirror'], TRUE);
                     $text .= '<td>';
                     foreach($mirrorArray as $mirror) {
                        $text .= $mirror['url'].'<br/>';
                     }
                     $text .= '</td>';
                  }
                  $text .= '<td>
                              <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.nocategory">'.ADMIN_EDIT_ICON.'</a>
   				               <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
   				            </td>';
                  $text .= '</tr>';
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_172;
            }
            break;
         }
         case 'filesize':
         {
            $title = DOWLAN_170;
            $text = "";
            $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc.download_category_id=d.download_category WHERE d.download_url<>''";
            $count = $sql->db_Select_gen($query);
            $foundSome = false;
            if ($count) {
               while($row = $sql->db_Fetch()) {
                  if (is_readable(e_DOWNLOAD.$row['download_url'])) {
                     $filesize = filesize(e_DOWNLOAD.$row['download_url']);
                     if ($filesize <> $row['download_filesize']) {
                        if (!$foundSome) {
   		                  $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
                           $text .= '<table class="adminlist" style="width:100%">';
                           $text .= '<tr>';
                           $text .= '<th>'.DOWLAN_67.'</th>';
                           $text .= '<th>'.DOWLAN_27.'</th>';
                           $text .= '<th>'.DOWLAN_11.'</th>';
                           $text .= '<th>'.DOWLAN_13.'</th>';
                           $text .= '<th>'.DOWLAN_180.'</th>';
                           $text .= '<th>'.LAN_OPTIONS.'</th>';
                           $text .= '</tr>';
                           $foundSome = true;
                        }
                        $text .= '<tr>';
                        $text .= '<td>'.$row['download_id'].'</td>';
                        $text .= "<td><a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$e107->tp->toHTML($row['download_name']).'</a></td>';
                        $text .= '<td>'.$e107->tp->toHTML($row['download_category_name']).'</td>';
                        $text .= '<td>'.$e107->tp->toHTML($row['download_url']).'</td>';
                        $text .= '<td>'.$row['download_filesize'].' / ';
                        $text .= $filesize;
                        $text .= '</td>';
                        $text .= '<td>
                                    <a href="'.e_SELF.'?create.edit.'.$row["download_id"].'.maint.filesize">'.ADMIN_EDIT_ICON.'</a>
   					                  <input type="image" title="'.LAN_DELETE.'" name="delete[main_'.$row["download_id"].']" src="'.ADMIN_DELETE_ICON_PATH.'" onclick=\'return jsconfirm("'.$tp->toJS(DOWLAN_33.' [ID: '.$row["download_id"].' ]').'") \'/>
   					               </td>';
                        $text .= '</tr>';
                     }
                  }
               }
            }
            if ($foundSome) {
               $text .= '</table></form>';
            }
            else
            {
               $text = DOWLAN_172;
            }
            break;
         }
         case 'log':
         {
            $text = "log - view manage download history log";
            header('location: '.e_ADMIN.'admin_log.php?downlog');
            exit();
            break;
         }
      }
   }
   else {
      $title = DOWLAN_193;
      $text = DOWLAN_179;
      $eform = new e_form();
      $text = "
      	<form method='post' action='".e_SELF."?maint' id='core-db-main-form'>
      		<fieldset id='core-db-plugin-scan'>
      		<legend class='e-hideme'>".DOWLAN_10."</legend>
      			<table cellpadding='0' cellspacing='0' class='adminlist'>
      			<colgroup span='2'>
      				<col style='width: 40%'></col>
      				<col style='width: 60%'></col>
      			</colgroup>
      			<tbody>
      				<tr>
      					<td>".DOWLAN_166."</td>
      					<td>
      						".$eform->radio('dl_maint', 'duplicates').$eform->label(DOWLAN_185, 'dl_maint', 'duplicates')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_167."</td>
      					<td>
      						".$eform->radio('dl_maint', 'orphans').$eform->label(DOWLAN_186, 'dl_maint', 'orphans')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_168."</td>
      					<td>
      						".$eform->radio('dl_maint', 'missing').$eform->label(DOWLAN_187, 'dl_maint', 'missing')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_169."</td>
      					<td>
      						".$eform->radio('dl_maint', 'inactive').$eform->label(DOWLAN_188, 'dl_maint', 'inactive')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_178."</td>
      					<td>
      						".$eform->radio('dl_maint', 'nocategory').$eform->label(DOWLAN_189, 'dl_maint', 'nocategory')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_170."</td>
      					<td>
      						".$eform->radio('dl_maint', 'filesize').$eform->label(DOWLAN_190, 'dl_maint', 'filesize')."
      					</td>
      				</tr>
      				<tr>
      					<td>".DOWLAN_171."</td>
      					<td>
      						".$eform->radio('dl_maint', 'log').$eform->label(DOWLAN_191, 'dl_maint', 'log')."
      					</td>
      				</tr>

      				</tbody>
      			</table>
      			<div class='buttons-bar center'>
      				".$eform->admin_button('trigger_db_execute', DOWLAN_192, 'execute')."
      			</div>
      		</fieldset>
      	</form>
      	";
   }
	$ns->tablerender(DOWLAN_165.$title, $text);
}


if ($action == 'limits')
{
	if ($sql->db_Select('userclass_classes','userclass_id, userclass_name'))
	{
		$classList = $sql->db_getList();
	}
	if ($sql->db_Select("generic", "gen_id as limit_id, gen_datestamp as limit_classnum, gen_user_id as limit_bw_num, gen_ip as limit_bw_days, gen_intdata as limit_count_num, gen_chardata as limit_count_days", "gen_type = 'download_limit'"))
	{
		while($row = $sql->db_Fetch())
		{
			$limitList[$row['limit_classnum']] = $row;
		}
	}
	$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table class='fborder' style='width:100%'>
		<tr>
			<td colspan='4' style='text-align:left'>
		";
		if ($pref['download_limits'] == 1)
		{
			$chk = "checked = 'checked'";
		}
		else
		{
			$chk = "";
		}

		$txt .= "
			<input type='checkbox' name='download_limits' {$chk}/> ".DOWLAN_125."
			</td>
		</tr>
		<tr>
			<td class='fcaption'>".DOWLAN_67."</td>
			<td class='fcaption'>".DOWLAN_113."</td>
			<td class='fcaption'>".DOWLAN_107."</td>
			<td class='fcaption'>".DOWLAN_108."</td>
		</tr>
	";

	foreach($limitList as $row)
	{
		$txt .= "
		<tr>
		<td>".$row['limit_id']."</td>
		<td>".r_userclass_name($row['limit_classnum'])."</td>
		<td>
			<input type='text' class='tbox' size='5' name='count_num[{$row['limit_id']}]' value='".($row['limit_count_num'] ? $row['limit_count_num'] : "")."'/> ".DOWLAN_109."
			<input type='text' class='tbox' size='5' name='count_days[{$row['limit_id']}]' value='".($row['limit_count_days'] ? $row['limit_count_days'] : "")."'/> ".DOWLAN_110."
		</td>
		<td>
			<input type='text' class='tbox' size='5' name='bw_num[{$row['limit_id']}]' value='".($row['limit_bw_num'] ? $row['limit_bw_num'] : "")."'/> ".DOWLAN_111." ".DOWLAN_109."
			<input type='text' class='tbox' size='5' name='bw_days[{$row['limit_id']}]' value='".($row['limit_bw_days'] ? $row['limit_bw_days'] : "")."'/> ".DOWLAN_110."
		</td>
		</tr>
		";
	}

	$txt .= "
	<tr>
	<td class='forumheader' colspan='4' style='text-align:center'>
	<input type='submit' class='button' name='updatelimits' value='".DOWLAN_115."'/>
	</td>
	</tr>
	<tr>
	<td colspan='4'><br/><br/></td>
	</tr>
	<tr>
	<td colspan='2'>".r_userclass("newlimit_class", 0, "off", "guest, member, admin, classes, language")."</td>
	<td>
		<input type='text' class='tbox' size='5' name='new_count_num' value=''/> ".DOWLAN_109."
		<input type='text' class='tbox' size='5' name='new_count_days' value=''/> ".DOWLAN_110."
	</td>
	<td>
		<input type='text' class='tbox' size='5' name='new_bw_num' value=''/> ".DOWLAN_111." ".DOWLAN_109."
		<input type='text' class='tbox' size='5' name='new_bw_days' value=''/> ".DOWLAN_110."
	</td>
	</tr>
	<tr>
	<td class='forumheader' colspan='4' style='text-align:center'>
	<input type='submit' class='button' name='addlimit' value='".DOWLAN_114."'/>
	</td>
	</tr>
	";

	$txt .= "</table></form>";

	$ns->tablerender(DOWLAN_112, $txt);
	require_once(e_ADMIN.'footer.php');
	exit;
}

require_once(e_ADMIN."footer.php");
exit;

function admin_download_adminmenu($parms)
{
	global $action,$subAction;
	if ($action == "") {
		$action = "main";
	}
	$var['main']['text'] = DOWLAN_29;
	$var['main']['link'] = e_SELF;
	$var['create']['text'] = DOWLAN_30;
	$var['create']['link'] = e_SELF."?create";
	$var['cat']['text'] = DOWLAN_31;
	$var['cat']['link'] = e_SELF."?cat";
	$var['cat']['perm'] = "Q";
	$var['opt']['text'] = LAN_OPTIONS;
	$var['opt']['link'] = e_SELF."?opt";
	$var['maint']['text'] = DOWLAN_165;
	$var['maint']['link'] = e_SELF."?maint";
	$var['limits']['text'] = DOWLAN_112;
	$var['limits']['link'] = e_SELF."?limits";
	$var['mirror']['text'] = DOWLAN_128;
	$var['mirror']['link'] = e_SELF."?mirror";
	e_admin_menu(DOWLAN_32, $action, $var);

   unset($var);
	if ($action == "" || $action == "main") {
	   $var['1']['text'] = "//TODO";
	   $var['1']['link'] = "";
	   e_admin_menu(DOWLAN_184, $subAction, $var);
	}
}
?>