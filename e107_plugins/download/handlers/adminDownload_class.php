<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/handlers/adminDownload_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!e107::isInstalled('download')) { exit(); }

require_once(e_PLUGIN.'download/handlers/download_class.php');
require_once(e_HANDLER.'upload_handler.php');
require_once(e_HANDLER.'xml_class.php');

class adminDownload extends download
{
   var $searchField;
   var $advancedSearchFields;
   var $userclassOptions;

   function adminDownload()
   {
      global $pref;
      parent::download();
      $this->userclassOptions = 'blank,nobody,guest,public,main,admin,member,classes';

      // Save basic search string
      if (isset($_POST['download-search-text']))
      {
         $this->searchField = $_POST['download-search-text'];
      }

      // Save advanced search criteria
      if (isset($_POST['download_advanced_search_submit']))
      {
         $this->advancedSearchFields = $_POST['download_advanced_search'];
      }
   }
   
  /*
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
                 <table class='adminlist'>
                    <tr>
                       <td>".DOWLAN_198." ".$frm->text('download-search-text', $this->searchField, 50, array('size'=>50, 'class' => 'someclass'))."&nbsp;<a href='#download_search#download_advanced_search' class='e-swapit'>Switch to Advanced-Search</a></td>
                    </tr>
                 </table>
  
                 ";
                    // Filter should use ajax to filter the results automatically after typing.
  
  //			   $text .= "
     //         <div class='buttons-bar center'>
      //           <button type='submit' class='update' name='download_search_submit' value='no-value'><span>".DOWLAN_51."</span></button>
      //           <br/>
  
      //        </div>";
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
              <table class='adminform'>
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
  */
  
   
   
   /*
   
      
      function show_existing_items($action, $subAction, $id, $from, $amount)
      {
         global $sql, $rs, $ns, $tp, $mySQLdefaultdb, $pref, $user_pref;
         $frm = new e_form();
         $sortorder = $subAction ? $subAction : $pref['download_order'];
         $sortdirection = $id=="asc" ? "asc" : "desc";
          $amount = 10;
          if(!$sortorder)
          {
             $sortorder = "download_id";
          }
                 $sort_link = $sortdirection == 'asc' ? 'desc' : 'asc';
                  $columnInfo = array(
            "checkboxes"	   			=> array("title" => "", "forced"=> TRUE, "width" => "3%", "thclass" => "center first", "toggle" => "dl_selected"),
            "download_id"              => array("title"=>LAN_ID,  "type"=>"", "width"=>"auto", "thclass"=>"", "forced"=>true),
            "download_name"            => array("title"=>DOWLAN_12,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_url"             => array("title"=>DOWLAN_13,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_author"          => array("title"=>DOWLAN_15,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_author_email"    => array("title"=>DOWLAN_16,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_author_website"  => array("title"=>DOWLAN_17,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_description"     => array("title"=>DOWLAN_18,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_filesize"        => array("title"=>DOWLAN_66,  "type"=>"", "width"=>"auto", "thclass"=>"right"),
            "download_requested"       => array("title"=>DOWLAN_29,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
            "download_category"        => array("title"=>DOWLAN_11,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_active"          => array("title"=>DOWLAN_21,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
            "download_datestamp"       => array("title"=>DOWLAN_182, "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_thumb"           => array("title"=>DOWLAN_20,  "type"=>"", "width"=>"auto", "thclass"=>"center"),
            "download_image"           => array("title"=>DOWLAN_19,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_comment"         => array("title"=>DOWLAN_102, "type"=>"", "width"=>"auto", "thclass"=>"center"),
            "download_class"           => array("title"=>DOWLAN_113, "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_mirror"          => array("title"=>DOWLAN_128, "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_mirror_type"     => array("title"=>DOWLAN_195, "type"=>"", "width"=>"auto", "thclass"=>""),
            "download_visible"         => array("title"=>DOWLAN_43,  "type"=>"", "width"=>"auto", "thclass"=>""),
            "options"			        => array("title"=>LAN_OPTIONS, "width"=>"10%", "thclass"=>"center last", "forced"=>true)
           );
   
         $filterColumns = ($user_pref['admin_download_disp']) ? $user_pref['admin_download_disp'] : array("download_name","download_class");
         $query = "SELECT d.*, dc.* FROM `#download` AS d LEFT JOIN `#download_category` AS dc ON dc. download_category_id=d.download_category";
   
         if ($this->searchField) {
            $where = array();
            array_push($where, "download_name REGEXP('".$this->searchField."')");
            array_push($where, "download_description REGEXP('".$this->searchField."')");
            array_push($where, "download_author REGEXP('".$this->searchField."')");
            array_push($where, "download_author_email REGEXP('".$this->searchField."')");
            array_push($where, "download_author_website REGEXP('".$this->searchField."')");
            $where = " WHERE ".implode(" OR ", $where);
            $query .= "$where ORDER BY {$sortorder} {$sortdirection}";
         }
         else if ($this->advancedSearchFields) {
            $where = array();
            if (strlen($this->advancedSearchFields['name']) > 0) {
               array_push($where, "download_name REGEXP('".$this->advancedSearchFields['name']."')");
            }
            if (strlen($this->advancedSearchFields['url']) > 0) {
               array_push($where, "download_url REGEXP('".$this->advancedSearchFields['url']."')");
            }
            if (strlen($this->advancedSearchFields['author']) > 0) {
               array_push($where, "download_author REGEXP('".$this->advancedSearchFields['author']."')");
            }
            if (strlen($this->advancedSearchFields['description']) > 0) {
               array_push($where, "download_description REGEXP('".$this->advancedSearchFields['description']."')");
            }
            if (strlen($this->advancedSearchFields['category']) != 0) {
               array_push($where, "download_category=".$this->advancedSearchFields['category']);
            }
            if (strlen($this->advancedSearchFields['filesize']) > 0) {
               array_push($where, "download_filesize".$this->advancedSearchFields['filesize_condition'].($this->advancedSearchFields['filesize']*$this->advancedSearchFields['filesize_units']));
            }
            if ($this->advancedSearchFields['status'] != 99) {
               array_push($where, "download_active=".$this->advancedSearchFields['status']);
            }
            if (strlen($this->advancedSearchFields['date']) > 0) {
               switch ($this->advancedSearchFields['date_condition']) {
                  case "<=" :
                  {
                     array_push($where, "download_datestamp".$this->advancedSearchFields['date_condition'].($this->advancedSearchFields['date']+86400));
                     break;
                  }
                  case "=" :
                  {
                     array_push($where, "(download_datestamp>=".$this->advancedSearchFields['date']." AND download_datestamp<=".($this->advancedSearchFields['date']+86399).")");
                     break;
                  }
                  case ">=" :
                  {
                     array_push($where, "download_datestamp".$this->advancedSearchFields['date_condition'].$this->advancedSearchFields['date']);
                     break;
                  }
               }
            }
            if (strlen($this->advancedSearchFields['requested']) > 0) {
               array_push($where, "download_requested".$this->advancedSearchFields['requested_condition'].$this->advancedSearchFields['requested']);
            }
            if ($this->advancedSearchFields['visible']) {
               array_push($where, "download_visible=".$this->advancedSearchFields['visible']);
            }
            if ($this->advancedSearchFields['class']) {
               array_push($where, "download_class=".$this->advancedSearchFields['class']);
            }
            $where = (count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "");
   
            $query .= "$where ORDER BY {$sortorder} {$sortdirection}";
         }
         else
         {
            $query .= " ORDER BY ".($subAction ? $subAction : $sortorder)." ".($id ? $id : $sortdirection)."  LIMIT $from, $amount";
         }
   
         $text .= "<fieldset id='downloads-list'><legend class='e-hideme'>".DOWLAN_7."</legend>";
         if ($dl_count = $sql->db_Select_gen($query))
         {
            $text .= $rs->form_open("post", e_SELF."?".e_QUERY, "myform")."
               <table class='adminlist'>
                      ".$frm->colGroup($columnInfo,$filterColumns)
                      .$frm->thead($columnInfo,$filterColumns,"main.[FIELD].[ASC].[FROM]")."
                  <tbody>
               ";
   
            $rowStyle = "even";
   
            while ($row = $sql->db_Fetch())
            {
               $mirror = strlen($row['download_mirror']) > 0;
               $text .= "<tr>\n
                  <td class='center'>".$frm->checkbox("dl_selected[".$row["download_id"]."]", $row['download_id'])."</td>
               <td>".$row['download_id']."</td>\n";
   
               // Display Chosen options
   
               foreach($filterColumns as $disp)
               {
   
                  switch ($disp)
                  {
                     case "download_name" :
                        $text .= "<td>";
                        $text .= "<a href='".e_PLUGIN."download/download.php?view.".$row['download_id']."'>".$tp->toHTML($row['download_name'])."</a>";
                        break;
                     case "download_category" :
                        $text .= "<td>";
                        $text .= $tp->toHTML($row['download_category_name']);
                        break;
                     case "download_datestamp" :
                        global $gen;
                        $text .= "<td>";
                        $text .= ($row[$disp]) ? $gen->convert_date($row[$disp],'short') : "";
                        break;
                     case "download_class" :
                     case "download_visible" :
                        $text .= "<td>";
                        $text .= r_userclass_name($row[$disp])."&nbsp;";
                        break;
                     case "download_filesize" :
                        $text .= "<td class='right'>";
                        //$text .= ($row[$disp]) ? $this->e107->parseMemorySize(($row[$disp])) : "";
                        $text .= ($row[$disp]) ? (intval($row[$disp])) : "";
                        break;
                     case "download_thumb" :
                        $text .= "<td>";
                        $text .= ($row[$disp]) ? "<img src='".e_FILE."downloadthumbs/".$row[$disp]."' alt=''/>" : "";
                        break;
                     case "download_image" :
                        $text .= "<td>";
                        $text .= "<a rel='external' href='".e_FILE."downloadimages/".$row[$disp]."' >".$row[$disp]."</a>";
                        break;
                     case "download_description" :
                        $text .= "<td>";
                        $text .= $tp->toHTML($row[$disp],TRUE);
                        break;
                     case "download_active" :
                        $text .= "<td class='center'>";
                        if ($row[$disp]== 1)
                        {
                           $text .= "<img src='".ADMIN_TRUE_ICON_PATH."' title='".DOWLAN_123."' alt='' style='cursor:help'/>";
                        }
                        elseif ($row[$disp]== 2)
                        {
                           $text .= "<img src='".ADMIN_WAR/NING_ICON_PATH."' title='".DOWLAN_124."' alt='' style='cursor:help'/>";
                        }
                        else
                        {
                           $text .= "<img src='".ADMIN_FALSE_ICON_PATH."' title='".DOWLAN_122."' alt='' style='cursor:help'/>";
                        }
                        break;
                     case "download_comment" :
                        $text .= "<td class='center'>";
                        $text .= ($row[$disp]) ? ADMIN_TRUE_ICON : "";
                        break;
                     case "download_mirror" :
                        $text .= "<td>";
                        $mirrorArray = $this->makeMirrorArray($row[$disp], TRUE);
                        foreach($mirrorArray as $mirror) {
                           $title = DOWLAN_66." ".$mirror['filesize']."; ".DOWLAN_29." ".$mirror['requests'];
                           $text .= "<div><img src='".ADMIN_INFO_IC/ON_PATH."' title='".$title."' alt='' style='cursor:help'/> ";
                           $text .= $tp->toHTML($mirror['url']).'</div>';
                        }
                        break;
                     case "download_mirror_type" :
                        $text .= "<td class='center'>";
                        if ($mirror)
                        {
                           switch ($row[$disp])
                           {
                              case 1:
                                 $text .= DOWLAN_196;
                                 break;
                              default:
                                 $text .= DOWLAN_197;
                           }
                        }
                        break;
                     case "download_requested" :
                     case "download_active" :
                     case "download_thumb" :
                     case "download_comment" :
                        $text .= "<td class='center'>";
                        $text .= $tp->toHTML($row[$disp]);
                        break;
                     default :
                        $text .= "<td>";
                        $text .= $tp->toHTML($row[$disp]);
                  }
                  $text .= "</td>";
               }
   
               $text .= "
                     <td class='center'>
                        <a href='".e_SELF."?create.edit.".$row['download_id']."'>".ADMIN_EDIT_ICON."</a>
                        <input type='image' title='".LAN_DELETE."' name='delete[main_".$row['download_id']."]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(DOWLAN_33." [ID: ".$row['download_id']." ]")."') \"/>
                     </td>
                     </tr>";
            }
            $text .= "</tbody></table>";
       //     $text .= "";
         }
         else
         {   // 'No downloads yet'
           $text .= "<div style='text-align:center'>".DOWLAN_6."</div>";
         }
   
         // Next-Previous.
         $downloads = $sql->db_Count("download");
         if ($downloads > $amount && !$this->searchFields && !$this->advancedSearchFields)
         {
            $parms = "{$downloads},{$amount},{$from},".e_SELF."?".(e_QUERY ? "$action.$subAction.$id." : "main.{$sortorder}.{$sortdirection}.")."[FROM]";
            $text .= "<div class='buttons-bar center nextprev'>".$this->batch_options().
              $tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";
         }
   
         $text .= "</form></fieldset>";
   
         return $text;
      }
   */
   
// ---------------------------------------------------------------------------
  
  /*
  
   function batch_options()
	{
	   $frm = new e_form();
 		$classes = get_userclass_list();
	   return $frm->batchoptions(
	      array('delete_selected'=>LAN_DELETE),
	      array(
	         'userclass' =>array('Assign userclass...',$classes),
	         'visibility'=>array('Assign Visibility..',$classes)
	      )
	   );
	}


	function _observe_processBatch()
	{
		list($type,$tmp,$uclass) = explode("_",$_POST['execute_batch']);
		$method = "batch_".$type;
		if ((method_exists($this,$method) || $type='visibility') && isset ($_POST['dl_selected']))
		{
            	if($type=='userclass' || $type=='visibility')
				{
					$mode = ($type=='userclass') ? 'download_class' : 'download_visible';
                 	$this->batch_userclass($_POST['dl_selected'],$uclass,$mode);
				}
				else
				{
                	$this->$method($_POST['dl_selected']);
				}
		}
	}

	
	function batch_userclass($download_ids,$uclass,$mode='download_class')
		{
			$emessage = &eMessage::getInstance();
				 if(e107::getDb() -> db_Update("download", $mode." ='{$uclass}' WHERE download_id IN (".implode(",",$download_ids).") "))
			{
				$emessage->add("It Worked", E_MESSAGE_SUCCESS);
			}
			else
			{
				$emessage->add("It Failed", E_MESSAGE_ERROR);
			}
		}*/
	

   // Given the string which is stored in the DB, turns it into an array of mirror entries
   // If $byID is true, the array index is the mirror ID. Otherwise its a simple array
   /*
   function makeMirrorArray($source, $byID = FALSE)
   {
      $ret = array();
      if ($source)
      {
         $mirrorTArray = explode(chr(1), $source);

         $count = 0;
         foreach($mirrorTArray as $mirror)
         {
            if ($mirror)
            {
               list($mid, $murl, $mreq, $msize) = explode(",", $mirror);
               $ret[$byID ? $mid : $count] = array('id' => $mid, 'url' => $murl, 'requests' => $mreq, 'filesize' => $msize);
               $count++;
            }
         }
      }
      return $ret;
   }
	*/

/*
   // Turn the array into a string which can be stored in the DB
   function compressMirrorArray($source)
   {
      if (!is_array($source) || !count($source)) return '';
      $inter = array();
      foreach ($source as $s)
      {
         $inter[] = $s['id'].','.$s['url'].','.$s['requests'].','.$s['filesize'];
      }
      return implode(chr(1),$inter);
   }*/



/*
   function create_download($subAction='', $id='')
   {
      global $download, $e107, $cal, $tp, $sql, $fl, $rs, $ns, $file_array, $image_array, $thumb_array;
      require_once(e_PLUGIN.'download/download_shortcodes.php');
      require_once(e_HANDLER."form_handler.php");

      if ($file_array = $fl->get_files(e_DOWNLOAD, "","standard",5))
      {
            sort($file_array);
      }
      if ($public_array = $fl->get_files(e_UPLOAD))
      {
         foreach($public_array as $key=>$val)
         {
             $file_array[] = str_replace(e_UPLOAD,"",$val);
         }
      }
 */
/*      if ($sql->db_Select("rbinary")) //TODO Remove me.
      {
         while ($row = $sql->db_Fetch())
         {
            extract($row);
            $file_array[] = "Binary ".$binary_id."/".$binary_name;
         }
      }
*/
/*
      if ($image_array = $fl->get_files(e_FILE.'downloadimages/', '\.gif$|\.jpg$|\.png$|\.GIF$|\.JPG$|\.PNG$','standard',2))
      {
         sort($image_array);
      }
      if ($thumb_array = $fl->get_files(e_FILE.'downloadthumbs/', '\.gif$|\.jpg$|\.png$|\.GIF$|\.JPG$|\.PNG$','standard',2))
      {
         sort($thumb_array);
      }

      $frm = new e_form();
      $mirrorArray = array();

      $download_status[0] = DOWLAN_122;
      $download_status[1] = DOWLAN_123;
      $download_status[2] = DOWLAN_124;


      if (!$sql->db_Select("download_category"))
      {
         $ns->tablerender(ADLAN_24, "<div style='text-align:center'>".DOWLAN_5."</div>");
         return;
      }
      $download_active = 1;
      if ($_GET['action'] == "edit" && !$_POST['submit'])
      {
         if ($sql->db_Select("download", "*", "download_id=".intval($_GET['id'])))
         {
            $row = $sql->db_Fetch();
            extract($row);

            $mirrorArray = $this->makeMirrorArray($row['download_mirror']);
         }
      }

      if ($subAction == "dlm" && !$_POST['submit'])
      {
         require_once(e_PLUGIN.'download/download_shortcodes.php');
         if ($sql->db_Select("upload", "*", "upload_id=".$id))
         {
            $row = $sql->db_Fetch();

            $download_category = $row['upload_category'];
            $download_name = $row['upload_name'].($row['upload_version'] ? " v" . $row['upload_version'] : "");
            $download_url = $row['upload_file'];
            $download_author_email = $row['upload_email'];
            $download_author_website = $row['upload_website'];
            $download_description = $row['upload_description'];
            $download_image = $row['upload_ss'];
            $download_filesize = $row['upload_filesize'];
            $image_array[] = array("path" => "", "fname" => $row['upload_ss']);
            $download_author = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1));
         }
      }


      $text = "
      <div class='admintabs' id='tab-container'>
         <ul class='e-taXXbs' e-hideme' id='core-download-tabs'>
            <li id='tab-general'><a href='#download-create'>".DOWLAN_175."</a></li>
            <li id='tab-external'><a href='#download-edit-external'>".DOWLAN_176."</a></li>
            <li id='tab-mirror'><a href='#download-edit-mirror'>".DOWLAN_128."</a></li>
         </ul>
         <div>
            <form method='post' action='".e_SELF."?".e_QUERY."' id='myform'>
               <fieldset id='download-create'>
                  <table style='".ADMIN_WIDTH."' class='adminlist'>
                     <tr>
                        <td style='width:20%;'>".DOWLAN_13."</td>
                        <td style='width:80%'>
                           <div>".DOWLAN_131."&nbsp;&nbsp;
                              <select name='download_url' class='tbox'>
                                 <option value=''>&nbsp;</option>
         ";

      $counter = 0;
      while (isset($file_array[$counter]))
      {
         $fpath = str_replace(e_DOWNLOAD,"",$file_array[$counter]['path']).$file_array[$counter]['fname'];
         $selected = '';
         if (stristr($fpath, $download_url) !== FALSE)
         {
            $selected = " selected='selected'";
            $found = 1;
         }

         $text .= "<option value='".$fpath."' $selected>".$fpath."</option>\n";
         $counter++;
      }

      if (preg_match("/http:|https:|ftp:/", $download_url))
      {
         $download_url_external = $download_url;
         $download_url = '';
      }

      $etext = " - (".DOWLAN_68.")";
      if (file_exists(e_UPLOAD.$download_url))
      {
         $etext = "";
      }

      if (!$found && $download_url)
      {
         $text .= "<option value='".$download_url."' selected='selected'>".$download_url.$etext."</option>\n";
      }

      $text .= "             </select>
                        </div>
                     </td>
                  </tr>
               </table>
            </fieldset>
            <fieldset id='download-edit-external'>
               <table style='".ADMIN_WIDTH."' class='adminlist'>
                  <tr>
                       <td style='width:20%;'>".DOWLAN_149."</td>
                       <td style='width:80%;'>
                          <input class='tbox' type='text' name='download_url_external' size='70' value='{$download_url_external}' maxlength='255'/>
                       </td>
                    </tr>
                    <tr>
                       <td>".DOWLAN_66."</td>
                       <td>
                          <input class='tbox' type='text' name='download_filesize_external' size='8' value='{$download_filesize}' maxlength='10'/>
                       </td>
                  </tr>
               </table>
            </fieldset>
            <fieldset id='download-edit-mirror'>
               <table style='".ADMIN_WIDTH."' class='adminlist'>
                  <tr>
                     <td style='width:20%'><span title='".DOWLAN_129."' style='cursor:help'>".DOWLAN_128."</span></td>
                     <td style='width:80%'>";

      // See if any mirrors to display
      if (!$sql -> db_Select("download_mirror"))
      {   // No mirrors defined here
         $text .= DOWLAN_144."</td></tr>";
      }
      else
      {
         $text .= DOWLAN_132."<div id='mirrorsection'>";
         $mirrorList = $sql -> db_getList();         // Get the list of possible mirrors
         $m_count = (count($mirrorArray) ? count($mirrorArray) : 1);      // Count of mirrors actually in use (or count of 1 if none defined yet)
         for($count = 1; $count <= $m_count; $count++)
         {
            $opt = ($count==1) ? "id='mirror'" : "";
            $text .="
                        <div {$opt}>
                           <select name='download_mirror_name[]' class='tbox'>
                              <option value=''>&nbsp;</option>";

            foreach ($mirrorList as $mirror)
            {
               extract($mirror);
               $text .= "<option value='{$mirror_id}'".($mirror_id == $mirrorArray[($count-1)]['id'] ? " selected='selected'" : "").">{$mirror_name}</option>\n";
            }

            $text .= "</select>
                           <input  class='tbox' type='text' name='download_mirror[]' style='width: 60%;' value=\"".$mirrorArray[($count-1)]['url']."\" maxlength='200'/>
                           <input  class='tbox' type='text' name='download_mirror_size[]' style='width: 15%;' value=\"".$mirrorArray[($count-1)]['filesize']."\" maxlength='10'/>";
            if (DOWNLOAD_DEBUG)
            {
               if ($id)
               {
                  $text .= '('.$mirrorArray[($count-1)]['requests'].')';
               }
               else
               {
               $text .= "<input  class='tbox' type='text' name='download_mirror_requests[]' style='width: 10%;' value=\"".$mirrorArray[($count-1)]['requests']."\" maxlength='10'/>";
               }
            }
            $text .= "  </div>";
         }
         $text .="      </div>
                        <input class='btn button' type='button' name='addoption' value='".DOWLAN_130."' onclick=\"duplicateHTML('mirror','mirrorsection')\"/>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%' ><span style='cursor:help' title='".DOWLAN_154."'>".DOWLAN_155."</span></td>
                     <td style='width:80%'>
                        <input type='radio' name='download_mirror_type' value='1'".($download_mirror_type ? " checked='checked'" : "")."/> ".DOWLAN_156."<br/>
                        <input type='radio' name='download_mirror_type' value='0'".(!$download_mirror_type ? " checked='checked'" : "")."/> ".DOWLAN_157."
                     </td>
                  </tr>";
      }      // End of mirror-related stuff

      $download_author = $subAction != "edit" && $download_author == "" ? USERNAME : $download_author;//TODO what if editing an no author specified
      $download_author_email = $subAction != "edit" && $download_author_email == "" ? USEREMAIL : $download_author_email;
      $text .= "
               </table>
            </fieldset>
            <fieldset id='download-edit-therest'>
               <table style='".ADMIN_WIDTH."' class='adminlist'>
                  <tr>
                     <td style='width:20%'>".DOWLAN_11."</td>
                     <td style='width:80%'>";
      $text .= $this->getCategorySelectList($download_category);
      $text .= "     </td>
                  </tr>
                  <tr>
                     <td style='width:20%;'>".DOWLAN_12."</td>
                     <td style='width:80%'>
                        <input class='tbox' type='text' name='download_name' size='60' value=\"".$tp->toForm($download_name)."\" maxlength='200'/>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_15."</td>
                     <td style='width:80%'>
                        <input class='tbox' type='text' name='download_author' size='60' value='$download_author' maxlength='100'/>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_16."</td>
                     <td style='width:80%'>
                        <input class='tbox' type='text' name='download_author_email' size='60' value='$download_author_email' maxlength='100'/>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_17."</td>
                     <td style='width:80%'>
                        <input class='tbox' type='text' name='download_author_website' size='60' value='$download_author_website' maxlength='100'/>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_18."</td>
                     <td style='width:80%'>
      ";
      $text .= $frm->bbarea('download_description',$download_description);
      $text .= "     </td>
                  </tr>
                  <tr>
                     <td>
                        Activation between
                     </td>
                     <td>
                         // TODO
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_19."</td>
                     <td style='width:80%'>
                        <select name='download_image' class='tbox'>
                           <option value=''>&nbsp;</option>";
      foreach($image_array as $img)
      {
         $fpath = str_replace(e_FILE."downloadimages/","",$img['path'].$img['fname']);
           $sel = ($download_image == $fpath) ? " selected='selected'" : "";
           $text .= "<option value='".$fpath."' $sel>".$fpath."</option>\n";
      }

      $text .= "     </select>";
      if ($subAction == "dlm" && $download_image)
      {
         $text .= "
         <input type='hidden' name='move_image' value='1'/>\n";
      }
      $text .= "     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_20."</td>
                     <td style='width:80%'>
                        <select name='download_thumb' class='tbox'>
                           <option value=''>&nbsp;</option>";
      foreach($thumb_array as $thm){
         $tpath = str_replace(e_FILE."downloadthumbs/","",$thm['path'].$thm['fname']);
         $sel = ($download_thumb == $tpath) ? " selected='selected'" : "";
         $text .= "<option value='".$tpath."' $sel>".$tpath."</option>\n";
      }

      $text .= "        </select>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".LAN_DATESTAMP."</td>
                     <td style='width:80%'>";
      if (!$download_datestamp){
           $download_datestamp = time();
      }
      $cal_options['showsTime'] = false;
      $cal_options['showOthers'] = false;
      $cal_options['weekNumbers'] = false;
      $cal_options['ifFormat'] = "%d/%m/%Y %H:%M:%S";
      $cal_options['timeFormat'] = "24";
      $cal_attrib['class'] = "tbox";
      $cal_attrib['size'] = "22";
      $cal_attrib['name'] = "download_datestamp";
      $cal_attrib['value'] = date("d/m/Y H:i:s", $download_datestamp);
      $text .= $cal->make_input_field($cal_options, $cal_attrib);
      $update_checked = ($_POST['update_datestamp']) ? "checked='checked'" : "";
      $text .= "        &nbsp;&nbsp;<span><input type='checkbox' value='1' name='update_datestamp' $update_checked/>".DOWLAN_148."</span>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_21."</td>
                     <td style='width:80%'>
                        <select name='download_active' class='tbox'>";
      foreach($download_status as $key => $val){
         $sel = ($download_active == $key) ? " selected = 'selected' " : "";
           $text .= "<option value='{$key}' {$sel}>{$val}</option>\n";
      }
      $text .= "        </select>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_102."</td>
                     <td style='width:80%'>";
      if ($download_comment == "0") {
         $text .= LAN_YES.": <input type='radio' name='download_comment' value='1'/>
            ".LAN_NO.": <input type='radio' name='download_comment' value='0' checked='checked'/>";
      } else {
         $text .= LAN_YES.": <input type='radio' name='download_comment' value='1' checked='checked'/>
            ".LAN_NO.": <input type='radio' name='download_comment' value='0'/>";
      }
      $text .= "     </td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_145."</td>
                     <td style='width:80%'>".r_userclass('download_visible', $download_visible, 'off', 'public, nobody, member, admin, classes, language')."</td>
                  </tr>
                  <tr>
                     <td style='width:20%'>".DOWLAN_106."</td>
                     <td style='width:80%'>".r_userclass('download_class', $download_class, 'off', 'public, nobody, member, admin, classes, language')."</td>
                  </tr>";
      if ($subAction == "dlm") {
         $text .= "
                  <tr>
                     <td style='width:30%'>".DOWLAN_153."</td>
                     <td style='width:70%'>
                        <select name='move_file' class='tbox'>
                           <option value=''>".LAN_NO."</option>";
           $dl_dirlist = $fl->get_dirs(e_DOWNLOAD);
           if ($dl_dirlist){
            sort($dl_dirlist);
            $text .= "<option value='".e_DOWNLOAD."'>/</option>\n";
            foreach($dl_dirlist as $dirs)
            {
                 $text .= "<option value='". e_DOWNLOAD.$dirs."/'>".$dirs."/</option>\n";
            }
         }
         else
         {
              $text .= "<option value='".e_DOWNLOAD."'>".LAN_YES."</option>\n";
         }
         $text .= "     </select>
                     </td>
                  </tr>
                  <tr>
                     <td style='width:30%'>".DOWLAN_103."</td>
                     <td style='width:70%'>
                        <input type='checkbox' name='remove_upload' value='1'/>
                        <input type='hidden' name='remove_id' value='$id'/>
                     </td>
                  </tr>";
      }

      //triggerHook
      $data = array('method'=>'form', 'table'=>'download', 'id'=>$id, 'plugin'=>'download', 'function'=>'create_download');
      $hooks = $e107->e_event->triggerHook($data);


      $text .= "  <tr style=''>
                     <td colspan='2' style='text-align:center'>";
      if ($id && $subAction == "edit") {
         $text .= "<input class='btn button' type='submit' name='submit_download' value='".DOWLAN_24."'/> ";
      } else {
         $text .= "<input class='btn button' type='submit' name='submit_download' value='".DOWLAN_25."'/>";
      }

      $text .= "
                     </td>
                  </tr>
               </table>
            </fieldset>
         </form>
         </div>
         </div>";
      $ns->tablerender(ADLAN_24, $text);
   }
*/

// -----------------------------------------------------------------------------
/*

   function show_message($message) {
      global $ns;
      $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
   }
*/

// -----------------------------------------------------------------------------


 /*  // Actually save a new or edited download to the DB
   function submit_download($subAction, $id)
   {
      global $e107, $tp, $sql, $DOWNLOADS_DIRECTORY, $e_event;

      $dlInfo = array();
      $dlMirrors = array();

      if ($subAction == 'edit')
      {
         if ($_POST['download_url_external'] == '')
         {
            $_POST['download_filesize_external'] = FALSE;
         }
      }

      if ($_POST['download_url_external'] && $_POST['download_url'] == '')
      {
         $dlInfo['download_url'] = $tp->toDB($_POST['download_url_external']);
         $filesize = intval($_POST['download_filesize_external']);
      }
      else
      {
         $dlInfo['download_url'] = $tp->toDB($_POST['download_url']);
         if ($_POST['download_filesize_external'])
         {
            $filesize = intval($_POST['download_filesize_external']);
         }
         else
         {
            if (strpos($DOWNLOADS_DIRECTORY, "/") === 0 || strpos($DOWNLOADS_DIRECTORY, ":") >= 1)
            {
               $filesize = filesize($DOWNLOADS_DIRECTORY.$dlInfo['download_url']);
            }
            else
            {
               $filesize = filesize(e_BASE.$DOWNLOADS_DIRECTORY.$dlInfo['download_url']);
            }
         }
      }

      if (!$filesize)
      {
         if ($sql->db_Select("upload", "upload_filesize", "upload_file='{$dlInfo['download_url']}'"))
         {
            $row = $sql->db_Fetch();
            $filesize = $row['upload_filesize'];
         }
      }
      $dlInfo['download_filesize'] = $filesize;


      //  ----   Move Images and Files ------------
      if ($_POST['move_image'])
      {
         if ($_POST['download_thumb'])
         {
            $oldname = e_UPLOAD.$_POST['download_thumb'];
            $newname = e_FILE."downloadthumbs/".$_POST['download_thumb'];
            if (!$this -> move_file($oldname,$newname))
            {
                  return;
            }
         }
         if ($_POST['download_image'])
         {
            $oldname = e_UPLOAD.$_POST['download_image'];
            $newname = e_FILE."downloadimages/".$_POST['download_image'];
            if (!$this -> move_file($oldname,$newname))
            {
                  return;
            }
         }
      }

        if ($_POST['move_file'] && $_POST['download_url'])
      {
           $oldname = e_UPLOAD.$_POST['download_url'];
         $newname = $_POST['move_file'].$_POST['download_url'];
         if (!$this -> move_file($oldname,$newname))
         {
               return;
         }
            $dlInfo['download_url'] = str_replace(e_DOWNLOAD,"",$newname);
      }


       // ------------------------------------------


      $dlInfo['download_description'] = $tp->toDB($_POST['download_description']);
      $dlInfo['download_name'] = $tp->toDB($_POST['download_name']);
      $dlInfo['download_author'] = $tp->toDB($_POST['download_author']);
      $dlInfo['download_author_email'] = $tp->toDB($_POST['download_author_email']);
      $dlInfo['download_author_website'] = $tp->toDB($_POST['download_author_website']);
      $dlInfo['download_category'] = intval($_POST['download_category']);
      $dlInfo['download_active']  = intval($_POST['download_active']);
      $dlInfo['download_thumb'] = $tp->toDB($_POST['download_thumb']);
      $dlInfo['download_image'] = $tp->toDB($_POST['download_image']);
      $dlInfo['download_comment'] = $tp->toDB($_POST['download_comment']);
      $dlInfo['download_class'] = intval($_POST['download_class']);
      $dlInfo['download_visible'] =intval($_POST['download_visible']);

      if (preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['download_datestamp'], $matches))
      {
         $dlInfo['download_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
      }
      else
      {
           $dlInfo['download_datestamp'] = time();
      }

      if ($_POST['update_datestamp'])
      {
         $dlInfo['download_datestamp'] = time();
      }

      $mirrorStr = "";
      $mirrorFlag = FALSE;

      // See if any mirrors defined
      // Need to check all the possible mirror names - might have deleted the first one if we're in edit mode
      foreach ($_POST['download_mirror_name'] as $mn)
      {
         if ($mn)
         {
            $mirrorFlag = TRUE;
            break;
         }
      }
      if ($mirrorFlag)
      {
         $mirrors = count($_POST['download_mirror_name']);
         $mirrorArray = array();
         $newMirrorArray = array();
         if ($id && $sql->db_Select('download','download_mirror', 'download_id = '.$id))      // Get existing download stats
         {
            if ($row = $sql->db_Fetch())
            {
               $mirrorArray = $this->makeMirrorArray($row['download_mirror'], TRUE);
            }
         }
         for($a=0; $a<$mirrors; $a++)
         {
            $mid = trim($_POST['download_mirror_name'][$a]);
            $murl = trim($_POST['download_mirror'][$a]);
            $msize = trim($_POST['download_mirror_size'][$a]);
            if ($mid && $murl)
            {
               $newMirrorArray[$mid] = array('id' => $mid, 'url' => $murl, 'requests' => 0, 'filesize' => $msize);
               if (DOWNLOAD_DEBUG && !$id)
               {
                  $newMirrorArray[$mid]['requests'] = intval($_POST['download_mirror_requests'][$a]);
               }
            }
         }
         // Now copy across any existing usage figures
         foreach ($newMirrorArray as $k => $m)
         {
            if (isset($mirrorArray[$k]))
            {
               $newMirrorArray[$k]['requests'] = $mirrorArray[$k]['requests'];
            }
         }
         $mirrorStr = $this->compressMirrorArray($newMirrorArray);
      }

      $dlMirrors['download_mirror']=$mirrorStr;
      $dlMirrors['download_mirror_type']=intval($_POST['download_mirror_type']);

      if ($id)
      {  // Its an edit
         // Process triggers before calling admin_update so trigger messages can be shown
         $data = array('method'=>'update', 'table'=>'download', 'id'=>$id, 'plugin'=>'download', 'function'=>'update_download');
         $hooks = $e107->e_event->triggerHook($data);
         require_once(e_HANDLER."message_handler.php");
         $emessage = &eMessage::getInstance();
         $emessage->add($hooks, E_MESSAGE_SUCCESS);

         admin_update($sql->db_UpdateArray('download',array_merge($dlInfo,$dlMirrors),'WHERE download_id='.intval($id)), 'update', DOWLAN_2." (<a href='".e_PLUGIN."download/download.php?view.".$id."'>".$_POST['download_name']."</a>)");
         $dlInfo['download_id'] = $id;
         $this->downloadLog('DOWNL_06',$dlInfo,$dlMirrors);
         $dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
         unset($dlInfo['download_class']);         // Also replicating 0.7
         $e_event->trigger('dlupdate', $dlInfo);
      }
      else
      {
         if ($download_id = $sql->db_Insert('download',array_merge($dlInfo,$dlMirrors)))
         {
            // Process triggers before calling admin_update so trigger messages can be shown
            $data = array('method'=>'create', 'table'=>'download', 'id'=>$download_id, 'plugin'=>'download', 'function'=>'create_download');
            $hooks = $e107->e_event->triggerHook($data);
            require_once(e_HANDLER."message_handler.php");
            $emessage = &eMessage::getInstance();
            $emessage->add($hooks, E_MESSAGE_SUCCESS);

            admin_update($download_id, 'insert', DOWLAN_1." (<a href='".e_PLUGIN."download/download.php?view.".$download_id."'>".$_POST['download_name']."</a>)");

            $dlInfo['download_id'] = $download_id;
            $this->downloadLog('DOWNL_05',$dlInfo,$dlMirrors);
            $dlInfo['download_datestamp'] = $time;      // This is what 0.7 did, regardless of settings
            unset($dlInfo['download_class']);         // Also replicating 0.7
            $e_event->trigger("dlpost", $dlInfo);

            if ($_POST['remove_upload'])
            {
               $sql->db_Update("upload", "upload_active='1' WHERE upload_id='".$_POST['remove_id']."'");
               $mes = "<br/>".$_POST['download_name']." ".DOWLAN_104;
               $mes .= "<br/><br/><a href='".e_ADMIN."upload.php'>".DOWLAN_105."</a>";
               $this->show_message($mes);
            }
         }
      }
   }

*/
/*
   function downloadLog($aText, &$dlInfo, &$mirrorInfo=NULL)
   {
      global $admin_log;
      $logString = DOWLAN_9;
      foreach ($dlInfo as $k => $v)
      {
         $logString .= '[!br!]'.$k.'=>'.$v;
      }
      if ($mirrorInfo != NULL)
      {
         foreach ($mirrorInfo as $k => $v)
         {
            $logString .= '[!br!]'.$k.'=>'.$v;
         }
      }
      e107::getLog()->add($aText,$logString,E_LOG_INFORMATIVE,'');
   }
*/
// -----------------------------------------------------------------------------
/*

   function show_categories($subAction, $id)
   {
      global $download, $sql, $sql2, $rs, $ns, $tp;

      require_once(e_HANDLER."form_handler.php");
      $frm = new e_form();

      $text = $rs->form_open("post", e_SELF."?".e_QUERY, "myform");
      $text .= "<div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>";

      $qry = "
      SELECT dc.*, COUNT(d.download_id) AS filecount FROM #download_category AS dc
      LEFT JOIN #download AS d ON d.download_category = dc.download_category_id
      GROUP BY dc.download_category_id
      ORDER BY dc.download_category_order
      ";
      if ($sql->db_Select_gen($qry))
      {
         $categories = $sql->db_getList();
         foreach($categories as $cat)
         {
            $cat_array[$cat['download_category_parent']][] = $cat;
         }
         $text .= "
         <table class='adminlist' id='core-admin-categories'>
            <colgroup>
               <col style='width:5%;'/>
               <col style='width:55%;'/>
               <col style='width:10%;'/>
               <col style='width:10%;'/>
               <col style='width:20%;'/>
            </colgroup>
            <thead>
				<tr>
               <th colspan='2'>".DOWLAN_11."</th>
               <th>".DOWLAN_52."</th>
               <th>".LAN_ORDER."</th>
               <th>".LAN_OPTIONS."</th>
			   </tr>
            </thead>
			<tbody>";


         //Start displaying parent categories
         foreach($cat_array[0] as $parent)
         {
            if (strstr($parent['download_category_icon'], chr(1)))
            {
               list($parent['download_category_icon'], $parent['download_category_icon_empty']) = explode(chr(1), $parent['download_category_icon']);
            }

            $text .= "<tr>
               <td style='text-align:center'>".($parent['download_category_icon'] ? "<img src='".e_IMAGE."icons/{$parent['download_category_icon']}' style='vertical-align:middle; border:0' alt=''/>" : "&nbsp;")."</td>
               <td>
                  <a href='".e_PLUGIN."download/download.php?list.{$parent['download_category_id']}'>";
                  $text .= $tp->toHTML($parent['download_category_name']);
                  $text .= "</a><br/>
                  <span class='smalltext'>";
            $text .= $tp->toHTML($parent['download_category_description']);
            $text .= "</span>
               </td>
               <td>
               </td>
               <td>
                  <input class='tbox' type='text' name='catorder[{$parent['download_category_id']}]' value='{$parent['download_category_order']}' size='3'/>
               </td>
               <td style='text-align:left;padding-left:12px'>
                  <a href='".e_SELF."?cat.edit.{$parent['download_category_id']}'>".ADMIN_EDIT_ICON."</a>
               ";
               if (!is_array($cat_array[$parent['download_category_id']]))
               {
                  $text .= "<input type='image' title='".LAN_DELETE."' name='delete[category_{$parent['download_category_id']}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(DOWLAN_34." [ID: {$parent['download_category_id']} ]")."') \"/>";
               }
            $text .= "
                  </td>
               </tr>
               ";

            //Show sub categories
            if (is_array($cat_array[$parent['download_category_id']]))
            {
               foreach($cat_array[$parent['download_category_id']] as $subcat)
               {

                  if (strstr($subcat['download_category_icon'], chr(1)))
                  {
                     list($subcat['download_category_icon'], $subcat['download_category_icon_empty']) = explode(chr(1), $subcat['download_category_icon']);
                  }
                  $text .= "
                  <tr>
                     <td style='text-align:center'>".($subcat['download_category_icon'] ? "<img src='".e_IMAGE."icons/{$subcat['download_category_icon']}' style='vertical-align:middle; border:0' alt=''/>" : "&nbsp;")."</td>
                     <td>
                        <a href='".e_PLUGIN."download/download.php?list.{$subcat['download_category_id']}'>";
                  $text .= $tp->toHTML($subcat['download_category_name']);
                  $text .= "</a>
                        <br/>
                        <span class='smalltext'>";
                  $text .= $tp->toHTML($subcat['download_category_description']);
                  $text .= "</span>
                     </td>
                     <td>{$subcat['filecount']}</td>
                     <td>
                        <input class='tbox' type='text' name='catorder[{$subcat['download_category_id']}]' value='{$subcat['download_category_order']}' size='3'/>
                     </td>
                     <td style='text-align:left;padding-left:12px'>
                        <a href='".e_SELF."?cat.edit.{$subcat['download_category_id']}'>".ADMIN_EDIT_ICON."</a>";
                  if (!is_array($cat_array[$subcat['download_category_id']]) && !$subcat['filecount'])
                  {
                     $text .= "<input type='image' title='".LAN_DELETE."' name='delete[category_{$subcat['download_category_id']}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(DOWLAN_34." [ID: {$subcat['download_category_id']} ]")."') \"/>";
                  }
                  $text .= "
                     </td>
                  </tr>";

                  //Show sub-sub categories
                  if (is_array($cat_array[$subcat['download_category_id']]))
                  {
                     foreach($cat_array[$subcat['download_category_id']] as $subsubcat)
                     {

                        if (strstr($subsubcat['download_category_icon'], chr(1)))
                        {
                           list($subsubcat['download_category_icon'], $subsubcat['download_category_icon_empty']) = explode(chr(1), $subsubcat['download_category_icon']);
                        }
                        $text .= "<tr>
                           <td style='text-align:center'>".($subsubcat['download_category_icon'] ? "<img src='".e_IMAGE."icons/{$subsubcat['download_category_icon']}' style='vertical-align:middle; border:0' alt=''/>" : "&nbsp;")."</td>
                           <td>
                              &nbsp;&nbsp;&nbsp;&nbsp;<a href='".e_PLUGIN."download/download.php?list.{$subsubcat['download_category_id']}'>";
                        $text .= $tp->toHTML($subsubcat['download_category_name']);
                        $text .= "</a>
                              <br/>
                              &nbsp;&nbsp;&nbsp;&nbsp;<span class='smalltext'>";
                        $text .= $tp->toHTML($subsubcat['download_category_description']);
                        $text .= "</span>
                           </td>
                           <td>{$subsubcat['filecount']}</td>
                           <td>
                              <input class='tbox' type='text' name='catorder[{$subsubcat['download_category_id']}]' value='{$subsubcat['download_category_order']}' size='3'/>
                           </td>
                           <td style='text-align:left;padding-left:12px'>
                           <a href='".e_SELF."?cat.edit.{$subsubcat['download_category_id']}'>".ADMIN_EDIT_ICON."</a>
                           ";
                        if (!$subsubcat['filecount'])
                        {
                           $text .= "<input type='image' title='".LAN_DELETE."' name='delete[category_{$subsubcat['download_category_id']}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(DOWLAN_34." [ID: {$subsubcat['download_category_id']} ]")."') \"/>";
                        }
                        $text .= "
                           </td>
                           </tr>";
                     }
                  }
               }
            }

         }

         $text .= "</tbody></table></div>";
         $text .= "<div style='text-align:center'>
            <input class='btn button' type='submit' name='update_catorder' value='".LAN_UPDATE."'/>
            </div>";
      }
      else
      {
         $text .= "<div style='text-align:center'>".DOWLAN_38."</div>";
      }
      $text .= "</form>";
      $ns->tablerender(DOWLAN_37, $text);

      unset($download_category_id, $download_category_name, $download_category_description, $download_category_parent, $download_category_icon, $download_category_class);

      $handle = opendir(e_IMAGE."icons");
      while ($file = readdir($handle)) {
         if ($file != "." && $file != ".." && $file != "/" && $file != "CVS") {
            $iconlist[] = $file;
         }
      }
      closedir($handle);

      if ($subAction == "edit" && !$_POST['add_category']) {
         if ($sql->db_Select("download_category", "*", "download_category_id=$id")) {
            $row = $sql->db_Fetch();
             extract($row);
            $main_category_parent = $download_category_parent;
            if (strstr($download_category_icon, chr(1)))
            {
               list($download_category_icon, $download_category_icon_empty) = explode(chr(1), $download_category_icon);
            }
            else
            {
               $download_category_icon_empty = "";
            }
         }
      }



      $frm_action = (isset($_POST['add_category'])) ? e_SELF."?cat" : e_SELF."?".e_QUERY;
      $text = "<div>
         <form method='post' action='{$frm_action}' id='dlform'>
 		<table cellpadding='0' cellspacing='0' class='adminlist'>
	 		<colgroup span='2'>
	 			<col class='col-label' />
	 			<col class='col-control' />
	 		</colgroup>
         <tbody>
         <tr>
         	<td>".DOWLAN_37.": </td>
         	<td>". $this->getCategorySelectList($main_category_parent, false, false, DOWLAN_40)."</td>
		 </tr>

		 <tr>
         	<td>".DOWLAN_12.": </td>
         	<td><input class='tbox' type='text' name='download_category_name' size='40' value='$download_category_name' maxlength='100'/></td>
         </tr>

         <tr>
         	<td>".DOWLAN_18.": </td>
         	<td>".$frm->bbarea('download_category_description',$download_category_description)."</td>
         </tr>

         <tr>
        	 <td>".DOWLAN_41.": </td>
         	<td>".$frm->iconpicker('download_category_icon', $download_category_icon, DOWLAN_42) ."</td>
         </tr>

         <tr>
         	<td>".DOWLAN_147.": </td>
         	<td>".$frm->iconpicker('download_category_icon_empty', $download_category_icon_empty, DOWLAN_42) ."</td>
		 </tr>

         <tr>
         	<td>".DOWLAN_43.":<br/><span class='smalltext'>(".DOWLAN_44.")</span></td>
         	<td>".r_userclass("download_category_class", $download_category_class, 'off', 'public, nobody, member, admin, classes, language')."</td>
		 </tr>

		 </tbody>
         </table>

         <div class='buttons-bar center'>";

	      if ($id && $subAction == "edit" && !isset($_POST['add_category'])) {
	         $text .= "<input class='btn button' type='submit' name='add_category' value='".DOWLAN_46."'/> ";
	      } else {
	         $text .= "<input class='btn button' type='submit' name='add_category' value='".DOWLAN_45."'/>";
	      }

      $text .= "</div>
         </form>
         </div>";

      $ns->tablerender(DOWLAN_39, $text);
   }

*/
   /*
   function show_download_options() {
          global $pref, $ns;
                  require_once(e_HANDLER."form_handler.php");
           $frm = new e_form(true); //enable inner tabindex counter
                 $agree_flag = $pref['agree_flag'];
          $agree_text = $pref['agree_text'];
         $c = $pref['download_php'] ? " checked = 'checked' " : "";
         $sacc = (varset($pref['download_incinfo'],0) == '1') ? " checked = 'checked' " : "";
         $order_options = array(
            "download_id"        => "Id No.",
            "download_datestamp" => LAN_DATE,
            "download_requested" => ADLAN_24,
            "download_name"      => DOWLAN_59,
            "download_author"    => DOWLAN_15
         );
         $sort_options = array(
            "ASC"    => DOWLAN_62,
            "DESC"   => DOWLAN_63
         );
                 $text = "
              <div class='admintabs' id='tab-container'>
                  <ul class='e-tabXXs e-hideme' id='download-option-tabs'>
                      <li id='tab-download1'><a href='#core-download-download1'>".LAN_DL_DOWNLOAD_OPT_GENERAL."</a></li>
                      <li id='tab-download2'><a href='#core-download-download2'>".LAN_DL_DOWNLOAD_OPT_BROKEN."</a></li>
                      <li id='tab-download3'><a href='#core-download-download3'>".LAN_DL_DOWNLOAD_OPT_AGREE."</a></li>
                      <li id='tab-download4'><a href='#core-download-download4'>".LAN_DL_UPLOAD."</a></li>
                  </ul>
                          <form method='post' action='".e_SELF."?".e_QUERY."'>\n
                      <fieldset id='core-download-download1'>
                      <div>
                          <table style='".ADMIN_WIDTH."' class='adminlist'>
                             <colgroup>
                                <col style='width:30%'/>
                                <col style='width:70%'/>
                             </colgroup>
                             <tr>
                                <td>".LAN_DL_USE_PHP."</td>
                                <td>"
                                   .$frm->checkbox('download_php', '1', $pref['download_php'])
                                   .$frm->label(LAN_DL_USE_PHP_INFO, 'download_php', '1')
                                ."</td>
                             </tr>
                             <tr>
                                <td>".LAN_DL_SUBSUB_CAT."</td>
                                <td>"
                                   .$frm->checkbox('download_subsub', '1', $pref['download_subsub'])
                                   .$frm->label(LAN_DL_SUBSUB_CAT_INFO, 'download_subsub', '1')
                                ."</td>
                             </tr>
                             <tr>
                                <td>".LAN_DL_SUBSUB_COUNT."</td>
                                <td>"
                                   .$frm->checkbox('download_incinfo', '1', $pref['download_incinfo'])
                                   .$frm->label(LAN_DL_SUBSUB_COUNT_INFO, 'download_incinfo', '1')
                                ."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_55."</td>
                                <td>".$frm->text('download_view', $pref['download_view'], '4', array('size'=>'4'))."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_56."</td>
                                <td>".$frm->select('download_order', $order_options, $pref['download_order'])."</td>
                             </tr>
                             <tr>
                                <td>".LAN_ORDER."</td>
                                 <td>".$frm->select('download_sort', $sort_options, $pref['download_sort'])."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_160."</td>
                                <td>
                                   <select name='mirror_order' class='tbox'>".
                                      ($pref['mirror_order'] == "0" ? "<option value='0' selected='selected'>".DOWLAN_161."</option>" : "<option value='0'>".DOWLAN_161."</option>").
                                    ($pref['mirror_order'] == "1" ? "<option value='1' selected='selected'>".LAN_ID."</option>" : "<option value='1'>".LAN_ID."</option>").
                                    ($pref['mirror_order'] == "2" ? "<option value='2' selected='selected'>".DOWLAN_163."</option>" : "<option value='2'>".DOWLAN_12."</option>")."
                                   </select>
                                </td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_164."</td>
                                <td><input name='recent_download_days' class='tbox' value='".$pref['recent_download_days']."' size='3' maxlength='3'/>
                                </td>
                             </tr>
                          </table>
                       </div>
                      </fieldset>
                      <fieldset id='core-download-download2'>
                      <div>
                          <table style='".ADMIN_WIDTH."' class='adminlist'>
                             <colgroup>
                                <col style='width:30%'/>
                                <col style='width:70%'/>
                             </colgroup>
                             <tr>
                                <td>".DOWLAN_151."</td>
                                <td>". r_userclass("download_reportbroken", $pref['download_reportbroken'])."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_150."</td>
                                <td>". ($pref['download_email'] ? "<input type='checkbox' name='download_email' value='1' checked='checked'/>" : "<input type='checkbox' name='download_email' value='1'/>")."</td>
                             </tr>
                          </table>
                       </div>
                      </fieldset>
                      <fieldset id='core-download-download3'>
                      <div>
                          <table style='".ADMIN_WIDTH."' class='adminlist'>
                             <colgroup>
                                <col style='width:30%'/>
                                <col style='width:70%'/>
                             </colgroup>
                             <tr>
                                <td>".DOWLAN_100."</td>
                                <td>". ($agree_flag ? "<input type='checkbox' name='agree_flag' value='1' checked='checked'/>" : "<input type='checkbox' name='agree_flag' value='1'/>")."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_101."</td>
                                <td>".$frm->bbarea('agree_text',$agree_text)."</td>
                             </tr>
                             <tr>
                                <td>".DOWLAN_146."</td>
                                <td>".$frm->bbarea('download_denied',$pref['download_denied'])."</td>
                             </tr>
                          </table>
                       </div>
                      </fieldset>
                      <fieldset id='core-download-download4'>
                      <div>
                          <table style='".ADMIN_WIDTH."' class='adminlist'>
                             <colgroup>
                                <col style='width:30%'/>
                                <col style='width:70%'/>
                             </colgroup>
                             <tr>
                                <td>".DOWLAN_XXX."</td>
                                <td>//TODO</td>
                             </tr>
                          </table>
                       </div>
                      </fieldset>
                      <div class='buttons-bar center'>
                     <input class='btn button' type='submit' name='updatedownlaodoptions' value='".DOWLAN_64."'/>
                  </div>
                 </form>
              </div>
         ";
         $ns->tablerender(LAN_DL_OPTIONS, $text);
      }
    * 
    */
   

   
/*
   
   
   
   function show_upload_list() {
      global $ns, $sql, $gen, $e107, $tp;

      $frm = new e_form(true); //enable inner tabindex counter
      $imgd = e_BASE.$IMAGES_DIRECTORY;
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
            $poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
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







   function show_upload_filetypes() {
      global $ns;

      //TODO is there an e107:: copy of this
      if (!is_object($e_userclass))
      {
         $e_userclass = new user_class;
      }

      if(!getperms("0")) exit; //TODO still needed?

      $definition_source = DOWLAN_71;
      $source_file = '';
      $edit_upload_list = varset($_POST['upload_do_edit'], false);

      if (isset($_POST['generate_filetypes_xml']))
      {  // Write back edited data to filetypes_.xml
         $file_text = "<e107Filetypes>\n";
         foreach ($_POST['file_class_select'] as $k => $c)
         {
            if (!isset($_POST['file_line_delete_'.$c]) && varsettrue($_POST['file_type_list'][$k]))
            {
               $file_text .= "   <class name='{$c}' type='{$_POST['file_type_list'][$k]}' maxupload='".varsettrue($_POST['file_maxupload'][$k],ini_get('upload_max_filesize'))."'/>\n";
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
                     str_replace(array('--SOURCE--', '--DEST--'),array(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,e_ADMIN.e_READ_FILETYPES),DOWLAN_85)
                     ."</p><p>".
                     DOWLAN_72.$source_file."
                  </p></div>
                  <table style='".ADMIN_WIDTH."' class='adminlist'>"
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
               <input class='btn button' type='submit' name='generate_filetypes_xml' value='".DOWLAN_77."'/>
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
                  <table style='".ADMIN_WIDTH."' class='adminlist'>
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
               <input class='btn button' type='submit' name='updateuploadoptions' value='".DOWLAN_64."'/>
            </div>
           </form>
      ";
   	$ns->tablerender(LAN_DL_OPTIONS, $text);
   }*/

/*

   function create_category($subAction, $id)
   {
      global $sql, $tp, $admin_log;
      $download_category_name = $tp->toDB($_POST['download_category_name']);
      $download_category_description = $tp->toDB($_POST['download_category_description']);
        $download_category_icon = $tp->toDB($_POST['download_category_icon']);
      $download_category_class = $tp->toDB($_POST['download_category_class']);
      $download_categoory_parent = intval($_POST['download_category_parent']);

      if (isset($_POST['download_category_icon_empty']) && $_POST['download_category_icon_empty'] != "")
      {
        $download_category_icon .= trim(chr(1).$tp->toDB($_POST['download_category_icon_empty']));
      }

      if ($id)
      {
         admin_update($sql->db_Update("download_category", "download_category_name='{$download_category_name}', download_category_description='{$download_category_description}', download_category_icon ='{$download_category_icon}', download_category_parent= '{$download_categoory_parent}', download_category_class='{$download_category_class}' WHERE download_category_id='{$id}'"), 'update', DOWLAN_48);
         e107::getLog()->add('DOWNL_03',$download_category_name.'[!br!]'.$download_category_description,E_LOG_INFORMATIVE,'');
      }
      else
      {
         admin_update($sql->db_Insert("download_category", "0, '{$download_category_name}', '{$download_category_description}', '{$download_category_icon}', '{$download_categoory_parent}', '{$download_category_class}', 0 "), 'insert', DOWLAN_47);
         e107::getLog()->add('DOWNL_02',$download_category_name.'[!br!]'.$download_category_description,E_LOG_INFORMATIVE,'');
      }
      if ($subAction == "sn")
      {
         $sql->db_Delete("tmp", "tmp_time='{$id}' ");
      }
   }

*/
/*
   function show_existing_mirrors()
   {
      global $sql, $ns, $tp, $subAction, $id, $delete, $del_id, $admin_log;

      require_once(e_HANDLER."form_handler.php");
      $frm = new e_form();
      if ($delete == "mirror")
      {
         admin_update($sql -> db_Delete("download_mirror", "mirror_id=".$del_id), delete, DOWLAN_135);
         e107::getLog()->add('DOWNL_14','ID: '.$del_id,E_LOG_INFORMATIVE,'');
      }


      if (!$sql -> db_Select("download_mirror"))
      {
         $text = "<div style='text-align:center;'>".DOWLAN_144."</div>"; // No mirrors defined yet
      }
      else
      {

         $text = "<div>
         <form method='post' action='".e_SELF."?".e_QUERY."'>
         <table style='".ADMIN_WIDTH."' class='adminlist'>
         <tr>
         <td style='width: 10%; text-align: center;' class='forumheader'>ID</td>
         <td style='width: 30%;' class='forumheader'>".DOWLAN_12."</td>
         <td style='width: 30%;' class='forumheader'>".DOWLAN_136."</td>
         <td style='width: 30%; text-align: center;' class='forumheader'>".LAN_OPTIONS."</td>
         </tr>
         ";

         $mirrorList = $sql -> db_getList();

         foreach($mirrorList as $mirror)
         {
            extract($mirror);
            $text .= "

            <tr>
            <td style='width: 10%; text-align: center;'>$mirror_id</td>
            <td style='width: 30%;'>".$tp -> toHTML($mirror_name)."</td>
            <td style='width: 30%;'>".($mirror_image ? "<img src='".e_FILE."downloadimages/".$mirror_image."' alt=''/>" : LAN_NONE)."</td>
            <td style='width: 30%; text-align: center;'>
            <a href='".e_SELF."?mirror.edit.{$mirror_id}'>".ADMIN_EDIT_ICON."</a>
            <input type='image' title='".LAN_DELETE."' name='delete[mirror_{$mirror_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".DOWLAN_137." [ID: $mirror_id ]')\"/>
            </td>
            </tr>
            ";
         }
         $text .= "</table></form></div>";

      }

      $ns -> tablerender(DOWLAN_138, $text);

      require_once(e_HANDLER."file_class.php");
      $fl = new e_file;
      $imagelist = $fl->get_files(e_FILE.'downloadimages/');

      if ($subAction == "edit" && !defined("SUBMITTED"))
      {
         $sql -> db_Select("download_mirror", "*", "mirror_id='".intval($id)."' ");
         $mirror = $sql -> db_Fetch();
         extract($mirror);
         $edit = TRUE;
      }
      else
      {
         unset($mirror_name, $mirror_url, $mirror_image, $mirror_location, $mirror_description);
         $edit = FALSE;
      }

      $text = "<div>
      <form method='post' action='".e_SELF."?".e_QUERY."' id='dataform'>\n
      <table style='".ADMIN_WIDTH."' class='adminlist'>

      <tr>
      <td style='width: 30%;'>".DOWLAN_12."</td>
      <td style='width: 70%;'>
      <input class='tbox' type='text' name='mirror_name' size='60' value='{$mirror_name}' maxlength='200'/>
      </td>
      </tr>

      <tr>
      <td style='width: 30%;'>".DOWLAN_139."</td>
      <td style='width: 70%;'>
      <input class='tbox' type='text' name='mirror_url' size='70' value='{$mirror_url}' maxlength='255'/>
      </td>
      </tr>

      <tr>
      <td style='width: 30%;'>".DOWLAN_136."</td>
      <td style='width: 70%;'>
      <input class='tbox' type='text' id='mirror_image' name='mirror_image' size='60' value='{$mirror_image}' maxlength='200'/>


      <br /><input class='btn button' type ='button' style='cursor:pointer' size='30' value='".DOWLAN_42."' onclick='expandit(this)'/>
      <div id='imagefile' style='display:none;{head}'>";

      $text .= DOWLAN_140."<br/>";
      foreach($imagelist as $file)
      {
         $text .= "<a href=\"javascript:insertext('".$file['fname']."','mirror_image','imagefile')\"><img src='".e_FILE."downloadimages/".$file['fname']."' alt=''/></a> ";
      }

      $text .= "</div>
      </td>
      </tr>

      <tr>
      <td style='width: 30%;'>".DOWLAN_141."</td>
      <td style='width: 70%;'>
      <input class='tbox' type='text' name='mirror_location' size='60' value='$mirror_location' maxlength='200'/>
      </td>
      </tr>

      <tr>
      <td style='width: 30%;'>".DOWLAN_18."</td>
      <td style='width: 70%;'>";
      $text .= $frm->bbarea('mirror_description',$mirror_description);
      $text .= "</td>
      </tr>

      <tr>
      <td colspan='2' class='forumheader' style='text-align:center;'>
      ".($edit ? "<input class='btn button' type='submit' name='submit_mirror' value='".DOWLAN_142."'/><input type='hidden' name='id' value='{$mirror_id}'/>" : "<input class='btn button' type='submit' name='submit_mirror' value='".DOWLAN_143."'/>")."
      </td>
      </tr>

      </table>
      </form>
      </div>";

      $caption = ($edit ? DOWLAN_142 : DOWLAN_143);

      $ns -> tablerender($caption, $text);
   }

   function submit_mirror()
   {
      global $tp, $sql, $admin_log;
      define("SUBMITTED", TRUE);
      if (isset($_POST['mirror_name']) && isset($_POST['mirror_url']))
      {
         $name = $tp -> toDB($_POST['mirror_name']);
         $url = $tp -> toDB($_POST['mirror_url']);
         $location = $tp -> toDB($_POST['mirror_location']);
         $description = $tp -> toDB($_POST['mirror_description']);

         $logString = $name.'[!br!]'.$url.'[!br!]'.$location.'[!br!]'.$description;

         if (isset($_POST['id']))
         {
            admin_update($sql -> db_Update("download_mirror", "mirror_name='{$name}', mirror_url='{$url}', mirror_image='".$tp->toDB($_POST['mirror_image'])."', mirror_location='{$location}', mirror_description='{$description}' WHERE mirror_id=".intval($_POST['id'])), 'update', DOWLAN_133);
            e107::getLog()->add('DOWNL_13','ID: '.intval($_POST['id']).'[!br!]'.$logString,E_LOG_INFORMATIVE,'');
         }
         else
         {
            admin_update($sql -> db_Insert("download_mirror", "0, '{$name}', '{$url}', '".$tp->toDB($_POST['mirror_image'])."', '{$location}', '{$description}', 0"), 'insert', DOWLAN_134);
            e107::getLog()->add('DOWNL_12',$logString,E_LOG_INFORMATIVE,'');
         }
      }
   }*/


 // ---------------------------------------------------------------------------

/*
    function move_file($oldname,$newname)
   {
      global $ns;
      if (file_exists($newname))
      {
           return TRUE;
      }

      if (!file_exists($oldname) || is_dir($oldname))
      {
         $ns -> tablerender(LAN_ERROR,DOWLAN_68 . " : ".$oldname);
           return FALSE;
      }

      $directory = dirname($newname);
      if (is_writable($directory))
      {
         if (!rename($oldname,$newname))
         {
            $ns -> tablerender(LAN_ERROR,DOWLAN_152." ".$oldname ." -> ".$newname);
            return FALSE;
         }
         else
         {
            return TRUE;
         }
      }
      else
      {
            $ns -> tablerender(LAN_ERROR,$directory ." ".LAN_NOTWRITABLE);
         return FALSE;
      }
   }
 */
/*

   /**
    *
    * @private
    */
 /*
   function _getConditionList($name, $value) {
       $text .= "
          <select name='{$name}' class='tbox'>
             <option value='>=' ".($value == '>=' ? " selected='selected' " : "")." >&gt;=</option>
             <option value='=' ".($value == '=' ? " selected='selected' " : "")." >==</option>
             <option value='<=' ".($value == '<=' ? " selected='selected' " : "")." >&lt;=</option>
          </select>
          ";
       return $text;
    }*/
 
   /**
    *
    * @private
    */
  /*
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
  */
 






	function observer()
	{
		//Required on create & savepreset action triggers
//		if(isset($_POST['news_userclass']) && is_array($_POST['news_userclass']))
//		{
//			$_POST['news_class'] = implode(",", $_POST['news_userclass']);
//			unset($_POST['news_userclass']);
//		}
//
//		if(isset($_POST['delete']) && is_array($_POST['delete']))
//		{
//			$this->_observe_delete();
//		}
//		elseif(isset($_POST['submit_news']))
//		{
//			$this->_observe_submit_item($this->getSubAction(), $this->getId());
//		}
//		elseif(isset($_POST['create_category']))
//		{
//			$this->_observe_create_category();
//		}
//		elseif(isset($_POST['update_category']))
//		{
//			$this->_observe_update_category();
//		}
//		elseif(isset($_POST['save_prefs']))
//		{
//			$this->_observe_save_prefs();
//		}
//		elseif(isset($_POST['submitupload']))
//		{
//			$this->_observe_upload();
//		}
//		elseif(isset($_POST['news_comments_recalc']))
//		{
//			$this->_observe_newsCommentsRecalc();
//		}
		if(isset($_POST['etrigger_ecolumns']))
		{
       // 	$this->_observe_saveColumns();
		}
	}
/*
	function _observe_saveColumns()
	{
		global $user_pref,$admin_log;
		$user_pref['admin_download_disp'] = $_POST['e-columns'];
		save_prefs('user');
	}
 */
 
}
