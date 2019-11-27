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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/admin_download.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

$eplug_admin = true;
define('DOWNLOAD_DEBUG',FALSE);

require_once("../../class2.php");
if (!getperms("P") || !e107::isInstalled('download'))
{
	e107::redirect('admin');
	exit() ;
}


e107::lan('download','download'); // e_PLUGIN.'download/languages/'.e_LANGUAGE.'/download.php'
e107::lan('download', 'admin', true); // e_PLUGIN.'download/languages/'.e_LANGUAGE.'/admin_download.php'



// require_once(e_PLUGIN.'download/handlers/adminDownload_class.php');
require_once(e_PLUGIN.'download/handlers/download_class.php');
require_once(e_HANDLER.'upload_handler.php');
require_once(e_HANDLER.'xml_class.php');
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."ren_help.php");
//require_once(e_HANDLER."calendar/calendar_class.ph_");
//$cal = new DHTML_Calendar(true);
//$gen = new convert();



$e_sub_cat = 'download';
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."file_class.php");

$fl = new e_file;
$pref = e107::getPref(); // legacy, remove all globals
$download = new download();
// $adminDownload = new adminDownload();


/*

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

// $adminDownload->observer();

require_once (e_HANDLER.'message_handler.php');
$emessage = &eMessage::getInstance();



$from = ($from ? $from : 0);
$amount = varset($pref['download_view'], 50);

if (isset($_POST))
{
	$e107cache->clear("download_cat");
}*/



/*
if (isset($_POST['submit_download']))
{
	$adminDownload->submit_download($subAction, $id);
	$action = "main";
	unset($subAction, $id);
}
*/

if (isset($_POST['update_catorder']))
{
	foreach($_POST['catorder'] as $key=>$order)
	{
		if (is_numeric($_POST['catorder'][$key]))
		{
			$sql -> db_Update("download_category", "download_category_order='".intval($order)."' WHERE download_category_id='".intval($key)."'");
		}
	}
	e107::getLog()->add('DOWNL_08',implode(',',array_keys($_POST['catorder'])),E_LOG_INFORMATIVE,'');
	$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}
/*

if (isset($_POST['updatedownlaodoptions']))
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

		// e107::getMessage()->add(DOWLAN_65);

	}
	else
	{
		// e107::getMessage()->add(DOWLAN_8);
	}
}

*/

if (isset($_POST['updateuploadoptions']))
{
	unset($temp);
	$temp['upload_enabled'] = intval($_POST['upload_enabled']);
	$temp['upload_maxfilesize'] = $_POST['upload_maxfilesize'];
	$temp['upload_class'] = intval($_POST['upload_class']);
	if ($admin_log->logArrayDiffs($temp, $pref, 'DOWNL_02'))
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

if (!empty($_POST['addlimit']))
{
	if ($sql->select('generic','gen_id',"gen_type = 'download_limit' AND gen_datestamp = ".intval($_POST['newlimit_class'])))
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
		if ($sql->insert('generic',$vals))
		{
			$message = DOWLAN_117;
			e107::getLog()->add('DOWNL_09',$valString,E_LOG_INFORMATIVE,'');
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
	
	//if ($pref['download_limits'] != $_POST['download_limits'])
	{
		$tmp = ($_POST['download_limits'] == 'on') ? 1 : 0;
		if ($pref['download_limits'] != $tmp)
		{
			$pref['download_limits'] = $tmp;
			e107::getConfig()->set('download_limits', $tmp)->save(false);
			$message .= DOWLAN_126."<br/>";
		}
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
				e107::getLog()->add('DOWNL_11','ID: '.$idLim,E_LOG_INFORMATIVE,'');
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
			$vals['WHERE'] = "gen_id = ".$idLim;

			$sql->update('generic',$vals);
			$valString = implode(',',$vals);
			e107::getLog()->add('DOWNL_10',$idLim.', '.$valString,E_LOG_INFORMATIVE,'');
			$message .= $idLim." - ".DOWLAN_121."<br/>";
			unset($vals);
		}
	}
}

new plugin_download_admin();
require_once(e_ADMIN."auth.php");
//download/includes/admin.php is auto-loaded. 
 e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");
exit;




/*


if ($action == "mirror")
{
	//$adminDownload->show_existing_mirrors();
}


if ($action == "dlm")
{
	$action = "create";
	$id = $subAction;
	$subAction = "dlm";
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
*/


/*

if (!e_QUERY || $action == "main")
{
	//$text = $emessage->render();
	//$text .= $adminDownload->show_filter_form($action, $subAction, $id, $from, $amount);
	//$text .= $adminDownload->show_existing_items($action, $subAction, $id, $from, $amount);
	//$ns->tablerender(DOWLAN_7, $text);
}




if ($action == "opt")
{
	// $adminDownload->show_download_options();
}




if ($action == "ulist")
{
	$adminDownload->show_upload_list();
}

if ($action == "filetypes")
{
	$adminDownload->show_upload_filetypes();
}

if ($action == "uopt")
{
	$adminDownload->show_upload_options();
}
*/






// UNUSED


   
   
   
   function show_upload_list() {
      global $ns, $sql, $gen, $e107, $tp;

      $frm = new e_form(true); //enable inner tabindex counter

      $columnInfo = array(
         "checkboxes"         => array("title" => "", "forced"=> TRUE, "width" => "3%", "thclass" => "center first", "toggle" => "dl_selected"),
         "upload_id"          => array("title"=>LAN_ID,  "type"=>"", "width"=>"auto", "thclass"=>"", "forced"=>true),
         "upload_date"        => array("title"=>DOWLAN_78,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "upload_uploader"    => array("title"=>DOWLAN_79,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "upload_name"        => array("title"=>DOWLAN_12,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "upload_file_name"   => array("title"=>DOWLAN_59,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "upload_size"        => array("title"=>DOWLAN_66,  "type"=>"", "width"=>"auto", "thclass"=>"right"),
         "options"            => array("title"=>LAN_OPTIONS,"width"=>"15%", "thclass"=>"center last", "forced"=>true)
      );
      //TODO $filterColumns = ($user_pref['admin_download_disp'] ? $user_pref['admin_download_disp'] : array("download_name","download_class"));
      $filterColumns = array("upload_id","upload_date","upload_uploader","upload_name","upload_file_name","upload_size");
      $text = "
            <fieldset id='core-download-upload1'>
               <div>
                  <table style='".ADMIN_WIDTH."' class='adminlist'>"
                     .$frm->colGroup($columnInfo,$filterColumns)
                     .$frm->thead($columnInfo,$filterColumns,"main.[FIELD].[ASC].[FROM]")."
                     <tbody>
                     <tr>
                        <td class='center' colspan='".(count($filterColumns)+2)."'>";

      if (!$active_uploads = $sql->db_Select("upload", "*", "upload_active=0 ORDER BY upload_id ASC"))
      {
         $text .= DOWLAN_19.".</td></tr>";
      }
      else
      {
         $activeUploads = $sql -> db_getList();

         $text .= DOWLAN_80." ".($active_uploads == 1 ? DOWLAN_81 : DOWLAN_82)." ".$active_uploads." ".($active_uploads == 1 ? DOWLAN_83 : DOWLAN_84);
         $text .= "</td></tr>";

         foreach($activeUploads as $row)
         {
            $post_author_id = substr($row['upload_poster'], 0, strpos($row['upload_poster'], "."));
            $post_author_name = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1)); 
            // $poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
            $uparams = array('id' => $post_author_id, 'name' => $post_author_name);
            $link = e107::getUrl()->create('user/profile/view', $uparams);
            $userlink = "<a href='".$link."'><b>".$post_author_name."</b></a>";
            $poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : $userlink);
            $upload_datestamp = $gen->convert_date($row['upload_datestamp'], "short");
            $text .= "
            <tr>
                 <td class='center'>".$frm->checkbox("dl_selected[".$row["upload_id"]."]", $row['upload_id'])."</td>
               <td class='center'>".$row['upload_id']."</td>
               <td>".$upload_datestamp."</td>
               <td>".$poster."</td>
               <td><a href='".e_SELF."?ulist.".$row['upload_id']."'>".$row['upload_name']."</a></td>
               <td>".$row['upload_file']."</td>
               <td class='right'>".$e107->parseMemorySize($row['upload_filesize'])."</td>
               <td class='center'>
                  <form action='".e_SELF."?dis.{$upload_id}' id='uploadform_{$upload_id}' method='post'>
                     <div>
                        <a href='".e_SELF."?dlm.".$row['upload_id']."'><img src='".e_IMAGE."admin_images/downloads_32.png' alt='".DOWLAN_91."' title='".DOWLAN_91."' style='border:0'/></a>
                        <a href='".e_ADMIN."newspost.php?create.upload.1.".$row['upload_id']."'><img src='".e_IMAGE."admin_images/news_32.png' alt='".DOWLAN_162."' title='".DOWLAN_162."' style='border:0'/></a>
                        <input type='image' title='".LAN_DELETE."' name='updelete[upload_".$row['upload_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(" [ ".$row['upload_name']." ] ".DOWLAN_33)."') \"/>
                     </div>
                  </form>
               </td>
            </tr>";
         }
      }
      $text .= "</tbody></table></div></fieldset>";

      $ns->tablerender(DOWLAN_22, $text);
   }






 function show_filter_form($action, $subAction, $id, $from, $amount)
   {
      global $e107, $mySQLdefaultdb, $pref, $user_pref;
      $frm = new e_form();

      $filterColumns = ($user_pref['admin_download_disp'] ? $user_pref['admin_download_disp'] : array("download_name","download_class"));
	//   $url = $e107->url->getUrl('forum', 'thread', array('func' => 'view', 'id' => 123));
	   $url = "admin_download.php";

      // Search field
      $text .= "
		   <script type='text/javascript'>
		   </script>
         <form method='post' action='".e_SELF."' class='e-show-if-js e-filter-form' id='jstarget-downloads-list'>
            <div id='download_search'>
            <fieldset>
               <legend class='e-hideme'>".DOWLAN_194."</legend>
               <table class='table adminform'>
                  <tr>
                     <td>".DOWLAN_198." ".$frm->text('download-search-text', $this->searchField, 50, array('size'=>50, 'class' => 'someclass'))."&nbsp;<a href='#download_search#download_advanced_search' class='e-swapit'>Switch to Advanced-Search</a></td>
                  </tr>
               </table>

               ";

			// Filter should use ajax to filter the results automatically after typing.

/*			   $text .= "
            <div class='buttons-bar center'>
               <button type='submit' class='update' name='download_search_submit' value='no-value'><span>".DOWLAN_51."</span></button>
               <br/>

            </div>";*/

			$text.= "
            </fieldset>
            </div>
         </form>
         ";
      // Advanced search fields
      $text .= "
         <form method='post' action='".e_SELF."'>
            <div id='download_advanced_search' class='e-hideme'>
            <fieldset>
            <legend class='e-hideme'>".DOWLAN_183."</legend>
            <table class='table adminform'>
               <colgroup>
                  <col style='width:15%;'/>
                  <col style='width:35%;'/>
                  <col style='width:15%;'/>
                  <col style='width:35%;'/>
               </colgroup>
               <tr>
                  <td>".DOWLAN_12."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[name]' size='30' value='{$this->advancedSearchFields['name']}' maxlength='50'/></td>
                  <td>".DOWLAN_18."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[description]' size='50' value='{$this->advancedSearchFields['description']}' maxlength='50'/></td>
               </tr>
               <tr>
                  <td>".DOWLAN_11."</td>
                  <td>".$this->getCategorySelectList($this->advancedSearchFields['category'], true, false, '&nbsp;', 'download_advanced_search[category]');
      $text .= "  </td>
                  <td>".DOWLAN_149."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[url]' size='50' value='{$this->advancedSearchFields['url']}' maxlength='50'/></td>
               </tr>
               <tr>
                  <td>".DOWLAN_182."</td>
                  <td>
         ";
      $text .= $this->_getConditionList('download_advanced_search[date_condition]', $this->advancedSearchFields['date_condition']);
//TODO      $text .= $frm->datepicker('download_advanced_search[date]', $this->advancedSearchFields['date']);
      $text .= "//TODO";
      $text .= "
                  </td>
                  <td>".DOWLAN_21."</td>
                  <td>
                     <select name='download_advanced_search[status]' class='tbox'>";
      $text .= $this->_getStatusList('download_advanced_search[status]', $this->advancedSearchFields['status']);
      $text .= "     </select>
                  </td>
               </tr>
               <tr>
                  <td>".DOWLAN_66."</td>
                  <td>
         ";
      $text .= $this->_getConditionList('download_advanced_search[filesize_condition]', $this->advancedSearchFields['filesize_condition']);
      $text .= "
                     <input class='tbox' type='text' name='download_advanced_search[filesize]' size='10' value='{$this->advancedSearchFields['filesize']}'/>
                     <select name='download_advanced_search[filesize_units]' class='tbox'>
                        <option value='1' ".($this->advancedSearchFields['filesize_units'] == '' ? " selected='selected' " : "")." >b</option>
                        <option value='1024' ".($this->advancedSearchFields['filesize_units'] == '1024' ? " selected='selected' " : "")." >Kb</option>
                        <option value='1048576' ".($this->advancedSearchFields['filesize_units'] == '1048576' ? " selected='selected' " : "")." >Mb</option>
                     </select>
                  </td>
                  <td>".DOWLAN_43."</td>
                  <td>".$frm->uc_select('download_advanced_search[visible]', $this->advancedSearchFields['visible'], $this->userclassOptions)."</td>
               </tr>
               <tr>
                  <td>".DOWLAN_29."</td>
                  <td>
         ";
      $text .= $this->_getConditionList('download_advanced_search[requested_condition]', $this->advancedSearchFields['requested_condition']);
      $text .= "     <input class='tbox' type='text' name='download_advanced_search[requested]' size='6' value='{$this->advancedSearchFields['requested']}' maxlength='6'/> times
                  </td>
                  <td>".DOWLAN_113."</td>
                  <td>
                  ";
      $text .= $frm->uc_select('download_advanced_search[class]', $this->advancedSearchFields['class'], $this->userclassOptions);
      $text .= "
                  </td>
               </tr>
               <tr>
                  <td>".DOWLAN_15."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[author]' size='30' value='{$this->advancedSearchFields['author']}' maxlength='50'/></td>
                  <td>".DOWLAN_16."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[author_email]' size='30' value='{$this->advancedSearchFields['author']}' maxlength='50'/></td>
               </tr>
               <tr>
                  <td>".DOWLAN_17."</td>
                  <td><input class='tbox' type='text' name='download_advanced_search[author_website]' size='30' value='{$this->advancedSearchFields['author']}' maxlength='50'/></td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
               </tr>
            </table>
            <div class='buttons-bar center'>
			      <span  class='e-show-if-js f-left'><a href='#download_advanced_search#download_search' class='e-swapit'>Simple search</a></span>
               <button type='submit' class='update' name='download_advanced_search_submit' value='no-value'><span>".DOWLAN_51."</span></button>
            </div>
            </fieldset>
			</div>
         </form>";

      return $text;
   }


	/**
	 *
	 */
	function show_upload_filetypes() {


      $ns           = e107::getRender();
      $e_userclass  = e107::getUserClass();

      if(!getperms("0")) exit; //TODO still needed?

      $definition_source = DOWLAN_71;
      $source_file = '';
      $edit_upload_list = varset($_POST['upload_do_edit'], false);

      if (isset($_POST['generate_filetypes_xml']))
      {  // Write back edited data to filetypes_.xml
         $file_text = "<e107Filetypes>\n";
         foreach ($_POST['file_class_select'] as $k => $c)
         {
            if (!isset($_POST['file_line_delete_'.$c]) && vartrue($_POST['file_type_list'][$k]))
            {
               $file_text .= "   <class name='{$c}' type='{$_POST['file_type_list'][$k]}' maxupload='".vartrue($_POST['file_maxupload'][$k],ini_get('upload_max_filesize'))."'/>\n";
            }
         }
         $file_text .= "</e107Filetypes>";
         if ((($handle = fopen(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,'wt')) == FALSE)
         || (fwrite($handle,$file_text) == FALSE)
         || (fclose($handle) == FALSE))
         {
            $text = DOWLAN_88.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES;
         }
         else
         {
            $text = DOWLAN_86.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES.'<br/>'.DOWLAN_87.e_ADMIN.e_READ_FILETYPES.'<br/>';
         }
         $ns->tablerender(DOWLAN_49, $text);
      }

      $current_perms = array();
      if (($edit_upload_list && is_readable(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES)) || (!$edit_upload_list && is_readable(e_ADMIN.e_READ_FILETYPES)))
      {
         require_once(e_HANDLER.'xml_class.php');
         $xml = new xmlClass;
         $xml->setOptArrayTags('class');
         $source_file = $edit_upload_list ? e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES : e_ADMIN.e_READ_FILETYPES;
         $temp_vars = $xml->loadXMLfile($source_file, true, false);
         if ($temp_vars === FALSE)
         {
            echo "Error parsing XML file!";
         }
         else
         {
            foreach ($temp_vars['class'] as $v1)
            {
               $v = $v1['@attributes'];
               $current_perms[$v['name']] = array('type' => $v['type'],'maxupload' => $v['maxupload']);
            }
         }
      }
      elseif (is_readable(e_ADMIN.'filetypes.php'))
      {
         $source_file = 'filetypes.php';
         $current_perms[e_UC_MEMBER] = array('type' => implode(',',array_keys(get_allowed_filetypes('filetypes.php', ''))),'maxupload' => '2M');
         if (is_readable(e_ADMIN.'admin_filetypes.php'))
         {
            $current_perms[e_UC_ADMIN] = array('type' => implode(',',array_keys(get_allowed_filetypes('admin_filetypes.php', ''))),'maxupload' => '2M');
            $source_file .= ' + admin_filetypes.php';
         }
      }
      else
      {   // Set a default
        $current_perms[e_UC_MEMBER] = array('type' => 'zip,tar,gz,jpg,png','maxupload' => '2M');
      }

      $frm = new e_form(true); //enable inner tabindex counter
      $columnInfo = array(
         "ftypes_userclass"   => array("title"=>DOWLAN_73,  "type"=>"", "width"=>"auto", "thclass"=>"", "forced"=>true),
         "ftypes_extension"   => array("title"=>DOWLAN_74,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "ftypes_max_size"    => array("title"=>DOWLAN_75,  "type"=>"", "width"=>"auto", "thclass"=>""),
         "ftypes_confirm_del" => array("title"=>DOWLAN_76,  "type"=>"", "width"=>"auto", "thclass"=>"last"),
      );
      $filterColumns = array("ftypes_userclass", "ftypes_extension", "ftypes_max_size", "ftypes_confirm_del");
      $text = "
         <form method='post' action='".e_SELF."?filetypes'>
            <fieldset id='core-download-upload1'>
               <div>
                  <div>
                     <input type='hidden' name='upload_do_edit' value='1'/><p>".
                     str_replace(array('[x]', '[y]'),array(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,e_ADMIN.e_READ_FILETYPES),DOWLAN_85)
                     ."</p><p>".
                     DOWLAN_72.$source_file."
                  </p></div>
                  <table class='table adminform'>"
                     .$frm->colGroup($columnInfo)
                     .$frm->thead($columnInfo,$filterColumns)."
                     <tbody>
      ";
      foreach ($current_perms as $uclass => $uinfo)
      {
         $text .= "
            <tr>
               <td>
                  <select name='file_class_select[]' class='tbox'>
                     ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), $uclass,'member,main,classes,admin, no-excludes')."
                  </select>
               </td>
               <td><input type='text' name='file_type_list[]' value='{$uinfo['type']}' class='tbox' size='40'/></td>
               <td><input type='text' name='file_maxupload[]' value='{$uinfo['maxupload']}' class='tbox' size='10'/></td>
               <td><input type='checkbox' value='1' name='file_line_delete_{$uclass}'/></td>
            </tr>
         ";
      }
      // Now put up a box to add a new setting
      $text .= "
                        <tr>
                           <td colspan='".count($columnInfo)."'>".DOWLAN_90."</td>
                        </tr>
                        <tr>
                           <td><select name='file_class_select[]' class='tbox'>
                           ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), '','member,main,classes,admin,blank, no-excludes')."
                           </select></td>
                           <td><input type='text' name='file_type_list[]' value='' class='tbox' size='40'/></td>
                           <td colspan='2'><input type='text' name='file_maxupload[]' value='".ini_get('upload_max_filesize')."' class='tbox' size='10'/></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </fieldset>
            <div class='buttons-bar center'>
               <input class='btn btn-default btn-secondary button' type='submit' name='generate_filetypes_xml' value='".DOWLAN_77."'/>
               </div>
        		</form>
      ";

      $ns->tablerender(DOWLAN_23, $text);
   }
   function show_upload_options() {
      global $pref, $ns;

      require_once(e_HANDLER."form_handler.php");
      $frm = new e_form(true); //enable inner tabindex counter

      $text = "
           <form method='post' action='".e_SELF."?".e_QUERY."'>
            <fieldset id='core-download-upload1'>
               <div>
                  <table class='table adminform'>
                     <colgroup>
                        <col style='width:30%'/>
                        <col style='width:70%'/>
                     </colgroup>
                     <tr>
                        <td>".DOWLAN_26."</td>
                        <td>"
                           .$frm->radio_switch('upload_enabled', $pref['upload_enabled'])
                           ."<div class='field-help'>"
                           .$frm->label(DOWLAN_51, 'upload_enabled', '1')
                           ."</div>"
                        ."</td>
                     </tr>
                     <tr>
                        <td>".DOWLAN_35."</td>
                        <td>"
                           .$frm->text('upload_maxfilesize', $pref['upload_maxfilesize'], '4', array('size'=>'10'))
                           ."<div class='field-help'>"
                           .$frm->label(str_replace(array("%1", "%2"), array(ini_get('upload_max_filesize'), ini_get('post_max_size')), DOWLAN_58), 'upload_maxfilesize', '1')
                           ."</div>"
                        ."</td>
                     </tr>
                     <tr>
                        <td>".DOWLAN_61."</td>
                        <td>"
                           .r_userclass("upload_class", $pref['upload_class'])
                           ."<div class='field-help'>"
                           .$frm->label(DOWLAN_60, 'upload_class', '1')
                           ."</div>"
                        ."</td>
                     </tr>
                  </table>
        	</div>
            </fieldset>
            <div class='buttons-bar center'>
               <input class='btn btn-default btn-secondary button' type='submit' name='updateuploadoptions' value='".DOWLAN_64."'/>
            </div>
           </form>
      ";
   	$ns->tablerender(LAN_DL_OPTIONS, $text);
   }




   /**
    *
    * @private
    */
   function _getConditionList($name, $value) {
      $text .= "
         <select name='{$name}' class='tbox'>
            <option value='>=' ".($value == '>=' ? " selected='selected' " : "")." >&gt;=</option>
            <option value='=' ".($value == '=' ? " selected='selected' " : "")." >==</option>
            <option value='<=' ".($value == '<=' ? " selected='selected' " : "")." >&lt;=</option>
         </select>
         ";
      return $text;
   }
   /**
    *
    * @private
    */
   function _getStatusList($name, $value) {
      $download_status[99]= '&nbsp;';
      $download_status[0] = DOWLAN_122;
      $download_status[1] = DOWLAN_123;
      $download_status[2] = DOWLAN_124;
      $text = "";
      foreach($download_status as $key=>$val){
         $sel = ($value == $key && $value != null) ? " selected='selected'" : "";
           $text .= "<option value='{$key}'{$sel}>{$val}</option>\n";
      }
      return $text;
   }






/*
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
	e107::getNav()->admin(DOWLAN_32, $action, $var);

   unset($var);
	$var['ulist']['text'] = DOWLAN_22;
	$var['ulist']['link'] = e_SELF."?ulist";;
	$var['filetypes']['text'] = DOWLAN_23;
	$var['filetypes']['link'] = e_SELF."?filetypes";
	$var['uopt']['text'] = LAN_OPTIONS;
	$var['uopt']['link'] = e_SELF."?uopt";
	e107::getNav()->admin(DOWLAN_10, $action, $var);
}
 */

?>