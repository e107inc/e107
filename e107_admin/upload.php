<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	File Upload facility - administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/upload.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


/**
 *	e107 Upload handling - Admin
 *
 *	@package	e107
 *	@subpackage	admin
 *	@version 	$Id$;
 */

require_once('../class2.php');
if (!getperms('V')) 
{
  header('location:'.e_BASE.'index.php');
  exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'upload';


$action = 'list';			// Default action
if (e_QUERY) 
{
  $tmp = explode('.', e_QUERY);
  $action = $tmp[0];
  $id = varset($tmp[1],0);
}



if ($action == "dis" && isset($_POST['updelete']['upload_'.$id]) ) 
{
	$res = $sql -> db_Select("upload", "*", "upload_id='".intval($id)."'");
	$row = $sql -> db_Fetch();
	if (preg_match("#Binary (.*?)/#", $row['upload_file'], $match)) 
	{
		$sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
	} 
	else if ($row['upload_file'] && file_exists(e_UPLOAD.$row['upload_file'])) 
	{
		unlink(e_UPLOAD.$row['upload_file']);
	}
	if (preg_match("#Binary (.*?)/#", $row['upload_ss'], $match)) 
	{
		$sql -> db_Delete("rbinary", "binary_id='".$tp -> toDB($match[1])."'");
	} 
	else if ($row['upload_ss'] && file_exists(e_FILE."public/".$row['upload_ss'])) 
	{
		unlink(e_UPLOAD.$row['upload_ss']);
	}
	$message = ($sql->db_Delete("upload", "upload_id='".intval($id)."'")) ? UPLLAN_1 : LAN_DELETED_FAILED;
	$admin_log->log_event('UPLOAD_01',$row['upload_file'],E_LOG_INFORMATIVE,'');
}

if ($action == "dlm") 
{
  header("location: ".e_ADMIN."download.php?dlm.".$id);
  exit;
}

if ($action == "news") 
{
  header("location: ".e_ADMIN."newspost.php?create.upload.".$id);
  exit;
}


if ($action == "dl") 
{
	$id = str_replace("%20", " ", $id);

	if (preg_match("/Binary\s(.*?)\/.*/", $id, $result)) 
	{
		$bid = $result[1];
		$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
		$binary_data = @mysql_result($result, 0, "binary_data");
		$binary_filetype = @mysql_result($result, 0, "binary_filetype");
		$binary_name = @mysql_result($result, 0, "binary_name");
		header("Content-type: ".$binary_filetype);
		header("Content-length: ".$download_filesize);
		header("Content-Disposition: attachment; filename=".$binary_name);
		header("Content-Description: PHP Generated Data");
		echo $binary_data;
		exit;
	} 
	else 
	{
		header("location:".e_UPLOAD.str_replace("dl.", "", e_QUERY));
		exit;
	}
}

require_once(e_HANDLER.'upload_handler.php');
require_once("auth.php");
require_once(e_HANDLER.'userclass_class.php');
$gen = new convert;
require_once(e_HANDLER.'form_handler.php');
$rs = new form;


// Need the userclass object for class selectors
if (!is_object($e_userclass)) { $e_userclass = new user_class; }


if (isset($_POST['optionsubmit'])) 
{
	$temp = array();
	$temp['upload_storagetype'] = $_POST['upload_storagetype'];
	$temp['upload_maxfilesize'] = $_POST['upload_maxfilesize'];
	$temp['upload_class'] = $_POST['upload_class'];
	$temp['upload_enabled'] = (FILE_UPLOADS ? $_POST['upload_enabled'] : 0);
	if ($temp['upload_enabled'] && !$sql->db_Select("links", "*", "link_url='upload.php' ")) 
	{
	  $sql->db_Insert("links", "0, '".UPLLAN_44."', 'upload.php', '', '', 1,0,0,0,0");
	}

	if (!$temp['upload_enabled'] && $sql->db_Select("links", "*", "link_url='upload.php' ")) 
	{
		$sql->db_Delete("links", "link_url='upload.php' ");
	}

	if ($admin_log->logArrayDiffs($temp, $pref, 'UPLOAD_02'))
	{
		save_prefs();		// Only save if changes
		$message = UPLLAN_2;
	}
	else
	{
		$message = UPLLAN_4;
	}
}

if (isset($message)) 
{
  require_once(e_HANDLER.'message_handler.php');
  message_handler("ADMIN_MESSAGE", $message);
}

if (!FILE_UPLOADS) 
{
  message_handler("ADMIN_MESSAGE", UPLLAN_41);
}


switch ($action)
{
  case 'filetypes' :
	if(!getperms('0')) exit;

	$definition_source = UPLLAN_58;
	$source_file = '';
	$edit_upload_list = varset($_POST['upload_do_edit'],FALSE);

	if (isset($_POST['generate_filetypes_xml']))
	{  // Write back edited data to filetypes_.xml
	  $file_text = "<e107Filetypes>\n";
	  foreach ($_POST['file_class_select'] as $k => $c)
	  {
		if (!isset($_POST['file_line_delete_'.$c]) && varsettrue($_POST['file_type_list'][$k]))
		{
//		  echo "Key: {$k} Class: {$c}  Delete: {$_POST['file_line_delete'][$k]}  List: {$_POST['file_type_list'][$k]}  Size: {$_POST['file_maxupload'][$k]}<br />";
		  $file_text .= "    <class name='{$c}' type='{$_POST['file_type_list'][$k]}' maxupload='".varsettrue($_POST['file_maxupload'][$k],ini_get('upload_max_filesize'))."' />\n";
		}
	  }
	  $file_text .= "</e107Filetypes>\n";
	  if ((($handle = fopen(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,'wt')) == FALSE) 
	  || (fwrite($handle,$file_text) == FALSE) 
	  || (fclose($handle) == FALSE))
	  {
		$text = UPLLAN_61.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES;
	  }
	  else
	  {
		$text = '';
		$text .= '<br />'.UPLLAN_59.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES.'. '.UPLLAN_60.e_ADMIN.e_READ_FILETYPES.'<br />';
	  }
	  $ns->tablerender(UPLLAN_49, $text);
	}


    $current_perms = array();
    if (($edit_upload_list && is_readable(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES)) || (!$edit_upload_list && is_readable(e_ADMIN.e_READ_FILETYPES)))
	{
	  $xml = e107::getXml();
	  $source_file = $edit_upload_list ? e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES : e_ADMIN.e_READ_FILETYPES;
	  $temp_vars = $xml->loadXMLfile($source_file, true, false);
	  if ($temp_vars === FALSE)
	  {
	    echo "Error parsing XML file!";
	  }
	  else
	  {
//	    echo "<pre>";
//		var_dump($temp_vars);
//		echo "</pre>";
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
	{	// Set a default
	  $current_perms[e_UC_MEMBER] = array('type' => 'zip,tar,gz,jpg,png','maxupload' => '2M');
	}
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?filetypes'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:30%' />
		<col style='width:40%' />
		<col style='width:25%' />
		<col style='width:5%' />
		</colgroup>
	  <tr>
		<td class='forumheader3' colspan='4'><input type='hidden' name='upload_do_edit' value='1'>".
			str_replace(array('--SOURCE--', '--DEST--'),array(e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES,e_ADMIN.e_READ_FILETYPES),UPLLAN_52)."</td>
	  </tr>
	  <tr>
		<td class='forumheader3' colspan='4'>".UPLLAN_57.$source_file."</td>
	  </tr>
	  <tr>
		<td class='fcaption'>".UPLLAN_53."</td>
		<td class='fcaption'>".UPLLAN_54."</td>
		<td class='fcaption' style='text-align:center'>".UPLLAN_55."</td>
		<td class='fcaption' style='text-align:center'>".UPLAN_DELETE."</td>
	  </tr>";
	foreach ($current_perms as $uclass => $uinfo)
	{
	  $text .= "
		<tr>
		  <td class='forumheader3'><select name='file_class_select[]' class='tbox'>
		  ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), $uclass,'member,main,classes,admin')."
		  </select></td>
		  <td class='forumheader3'><input type='text' name='file_type_list[]' value='{$uinfo['type']}' class='tbox' size='40' /></td>
		  <td class='forumheader3' style='text-align:center'><input type='text' name='file_maxupload[]' value='{$uinfo['maxupload']}' class='tbox' size='10' /></td>
		  <td class='forumheader3'><input type='checkbox' value='1' name='file_line_delete_{$uclass}' /></td>
		</tr>";
	}
	// Now put up a box to add a new setting
	$text .= "
	  <tr>
		  <td class='forumheader3'><select name='file_class_select[]' class='tbox'>
		  ".$e_userclass->vetted_tree('file_class_select',array($e_userclass,'select'), '','member,main,classes,admin,blank')."
		  </select></td>
		  <td class='forumheader3'><input type='text' name='file_type_list[]' value='' class='tbox' size='40' /></td>
		  <td class='forumheader3' style='text-align:center'><input type='text' name='file_maxupload[]' value='".ini_get('upload_max_filesize')."' class='tbox' size='10' /></td>
		  <td class='forumheader3'>&nbsp;</td>
	  </tr>";
	$text .= "
	  <tr>
		<td class='forumheader3' style='text-align:center' colspan='4'>
				<input class='button' type='submit' name='generate_filetypes_xml' value='".UPLLAN_56."' />
		</td>
	  </tr>
	</table></form>
	</div>";

	$ns->tablerender(UPLLAN_49, $text);
    break;

  case 'options' :
	if(!getperms('0')) exit;
	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?options'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:50%' class='forumheader3'>".UPLLAN_25."<br />
	<span class='smalltext'>".UPLLAN_26."</span></td>
	<td style='width:50%' class='forumheader3'>". ($pref['upload_enabled'] == 1 ? $rs->form_radio("upload_enabled", 1, 1)." ".LAN_YES.$rs->form_radio("upload_enabled", 0)." ".LAN_NO : $rs->form_radio("upload_enabled", 1)." ".LAN_YES.$rs->form_radio("upload_enabled", 0, 1)." ".LAN_NO)."
	</td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".UPLLAN_33."<br />
	<span class='smalltext'>".UPLLAN_34." (upload_max_filesize = ".ini_get('upload_max_filesize').", post_max_size = ".ini_get('post_max_size')." )</span></td>
	<td style='width:30%' class='forumheader3'>". $rs->form_text("upload_maxfilesize", 10, $pref['upload_maxfilesize'], 10)."
	</td>
	</tr>

	<tr>
	<td style='width:70%' class='forumheader3'>".UPLLAN_37."<br />
	<span class='smalltext'>".UPLLAN_38."</span></td>
	<td style='width:30%' class='forumheader3'>".r_userclass("upload_class", $pref['upload_class'],"off","nobody,public,guest,member,admin,classes")."

	</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader' style='text-align:center'>". $rs->form_button("submit", "optionsubmit", UPLLAN_39)."
	</td>
	</tr>
	</table>". $rs->form_close()."
	</div>";

	$ns->tablerender(LAN_OPTIONS, $text);
    break;
	
  case 'view' :
	$sql->db_Select('upload', '*', "upload_id='{$id}'");
	$row = $sql->db_Fetch();
	 extract($row);

	$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
	$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
	$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
	$upload_datestamp = $gen->convert_date($upload_datestamp, "long");

	$text = "<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:30%' />
		<col style='width:70%' />
		</colgroup>

		<tr>
		<td class='forumheader3'>".UPLLAN_3."</td>
		<td class='forumheader3'>{$upload_id}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_DATE."</td>
		<td class='forumheader3'>{$upload_datestamp}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_5."</td>
		<td class='forumheader3'>{$poster}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_6."</td>
		<td class='forumheader3'><a href='mailto:{$upload_email}'>{$upload_email}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_7."</td>
		<td class='forumheader3'>".($upload_website ? "<a href='{$upload_website}'>{$upload_website}</a>" : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_8."</td>
		<td class='forumheader3'>".($upload_name ? $upload_name: " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_9."</td>
		<td class='forumheader3'>".($upload_version ? $upload_version : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_10."</td>
		<td class='forumheader3'>".(is_numeric($upload_file) ? "Binary file ID ".$upload_file : "<a href='".e_SELF."?dl.{$upload_file}'>$upload_file</a>")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_11."</td>
		<td class='forumheader3'>".$e107->parseMemorySize($upload_filesize)."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_12."</td>
		<td class='forumheader3'>".($upload_ss ? "<a href='".e_BASE."request.php?upload.".$upload_id."'>".$upload_ss."</a>" : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_13."</td>
		<td class='forumheader3'>{$upload_description}</td>
		</tr>

		<tr>
		<td class='forumheader3'>".UPLLAN_14."</td>
		<td class='forumheader3'>".($upload_demo ? $upload_demo : " - ")."</td>
		</tr>

		<tr>
		<td class='forumheader3'>".LAN_OPTIONS."</td>
		<td class='forumheader3'><a href='".e_SELF."?dlm.{$upload_id}'>".UPLAN_COPYTODLM."</a> | <a href='".e_SELF."?news.{$upload_id}'>".UPLLAN_16."</a> | <a href='".e_SELF."?dis.{$upload_id}'>".UPLLAN_17."</a></td>
		</tr>

		</table>
		</div>";

	$ns->tablerender(UPLLAN_18, $text);
	// Intentionally fall through into list mode

  case 'list' :
  default :
	$imgd = e_BASE.$IMAGES_DIRECTORY;
	$text = "<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:5%' />
		<col style='width:20%' />
		<col style='width:15%' />
		<col style='width:20%' />
		<col style='width:25%' />
		<col style='width:10%' />
		<col style='width:50px;white-space:nowrap' />
		</colgroup>
		<tr>
		<td class='fcaption'>".UPLLAN_22."</td>
		<td class='fcaption'>".LAN_DATE."</td>
		<td class='fcaption'>".UPLLAN_5."</td>
		<td class='fcaption'>".UPLLAN_23."</td>
		<td class='fcaption'>".UPLLAN_8."</td>
		<td class='fcaption'>".UPLLAN_35."</td>
		<td class='fcaption'>".UPLLAN_42."</td>
		</tr>";

	$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='6'>";

	if (!$active_uploads = $sql->db_Select("upload", "*", "upload_active=0 ORDER BY upload_id ASC")) 
	{
	  $text .= UPLLAN_19.".\n</td>\n</tr>";
	} 
	else 
	{
	  $activeUploads = $sql -> db_getList();

	  $text .= UPLLAN_20." ".($active_uploads == 1 ? UPLAN_IS : UPLAN_ARE).$active_uploads." ".($active_uploads == 1 ? UPLLAN_21 : UPLLAN_27)." ...";
	  $text .= "</td></tr>";

	  foreach($activeUploads as $row)
	  {
		extract($row);
		$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
		$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
		$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");
		$upload_datestamp = $gen->convert_date($upload_datestamp, "short");
		$text .= "<tr>
		<td class='forumheader3'>".$upload_id ."</td>
		<td class='forumheader3'>".$upload_datestamp."</td>
		<td class='forumheader3'>".$poster."</td>
		<td class='forumheader3'><a href='".e_SELF."?view.".$upload_id."'>".$upload_name ."</a></td>
		<td class='forumheader3'>".$upload_file ."</td>
		<td class='forumheader3'>".$e107->parseMemorySize($upload_filesize)."</td>
		<td class='forumheader3'>
		<form action='".e_SELF."?dis.{$upload_id}' id='uploadform_{$upload_id}' method='post'>
		<div><a href='".e_SELF."?dlm.{$upload_id}'><img src='".e_IMAGE."admin_images/downloads_16.png' alt='".UPLAN_COPYTODLS."' title='".UPLAN_COPYTODLS."' style='border:0' /></a>
		<a href='".e_SELF."?news.{$upload_id}'><img src='".e_IMAGE."admin_images/news_16.png' alt='".UPLLAN_16."' title='".UPLLAN_16."' style='border:0' /></a>
        <input type='image' title='".LAN_DELETE."' name='updelete[upload_{$upload_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(UPLLAN_45." [ {$upload_name} ]")."') \"/>
		</div></form></td>
		</tr>";
	  }
	}
	$text .= "</table>\n</div>";

	$ns->tablerender(UPLLAN_43, $text);
}		// end - switch($action)




function upload_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";

    $var['list']['text'] = UPLLAN_51;
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "V";

	if(getperms("0"))
	{
	  $var['filetypes']['text'] = UPLLAN_49;
	  $var['filetypes']['link'] = e_SELF."?filetypes";
   	  $var['filetypes']['perm'] = "0";

	  $var['options']['text'] = UPLLAN_50;
	  $var['options']['link'] = e_SELF."?options";
   	  $var['options']['perm'] = "0";
    }
	show_admin_menu(UPLLAN_43, $action, $var);
}



require_once("footer.php");
?>