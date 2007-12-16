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
|     $Source: /cvs_backup/e107_0.8/e107_admin/banlist.php,v $
|     $Revision: 1.5 $
|     $Date: 2007-12-16 11:14:47 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

define('BAN_TIME_FORMAT',"%d-%m-%Y %H:%M");
define('BAN_REASON_COUNT',7);				// Update as more ban reasons added (max 10 supported)

define('BAN_TYPE_MANUAL',1);				// Manually entered bans
define('BAN_TYPE_IMPORTED',5);				// Imported bans
define('BAN_TYPE_TEMPORARY',9);				// Used during CSV import

define('BAN_TYPE_WHITELIST',100);			// Entry for whitelist

require_once("../class2.php");
if (!getperms("4")) 
{
  header("location:".e_BASE."index.php");
  exit;
}
$e_sub_cat = 'banlist';
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

$action = 'list';
if (e_QUERY) 
{
  $tmp = explode("-", e_QUERY);		// Use '-' instead of '.' to avoid confusion with IP addresses
  $action = $tmp[0];
  $sub_action = varset($tmp[1],'');
  if ($sub_action) $sub_action = preg_replace("/[^\w@\.]*/",'',urldecode($sub_action));
  $id = intval(varset($tmp[2],0));
  unset($tmp);
}


if (varsettrue($imode))
{
  $images_path = e_IMAGE.'packs/'.$imode.'/admin_images/';
}
else
{
  $images_path = e_IMAGE.'admin_images/';
}


if (isset($_POST['update_ban_prefs']))
{
  for ($i = 0; $i < BAN_REASON_COUNT; $i++)
  {
    $pref['ban_messages'][$i] = $tp->toDB(varset($_POST['ban_text'][$i],''));
    $pref['ban_durations'][$i] = intval(varset($_POST['ban_time'][$i],0));
  }
  save_prefs();
  $ns->tablerender(BANLAN_9, "<div style='text-align:center'>".BANLAN_33.'</div>');
}



if (isset($_POST['ban_ip']))
{
  $_POST['ban_ip'] = trim($_POST['ban_ip']);
  $new_ban_ip = preg_replace("/[^\w@\.\*]*/",'',urldecode($_POST['ban_ip']));
  if ($new_ban_ip != $_POST['ban_ip'])
  {
    $message = BANLAN_27.$new_ban_ip;
	$ns->tablerender(BANLAN_9, $message);
	$_POST['ban_ip'] = $new_ban_ip;
  }

  if (isset($_POST['entry_intent']) && (isset($_POST['add_ban']) || isset($_POST['update_ban'])) && $_POST['ban_ip'] != "" && strpos($_POST['ban_ip'], ' ') === false)
  {
/*	$_POST['entry_intent'] says why we're here:
		'edit' 	- Editing blacklist
		'add'	- Adding to blacklist
		'whedit' - Editing whitelist
		'whadd'	- Adding to whitelist
*/
    $new_vals = array('banlist_ip' => $_POST['ban_ip']);
	if (isset($_POST['add_ban']))
	{
	  $new_vals['banlist_datestamp'] = time();
	  if ($_POST['entry_intent'] == 'add') $new_vals['banlist_bantype'] = BAN_TYPE_MANUAL;				// Manual ban
	  if ($_POST['entry_intent'] == 'whadd') $new_vals['banlist_bantype'] = BAN_TYPE_WHITELIST;
	}
	$new_vals['banlist_admin'] = ADMINID;
	if (varsettrue($_POST['ban_reason'])) $new_vals['banlist_reason'] =$tp->toDB($_POST['ban_reason']);
	$new_vals['banlist_notes'] = $tp->toDB($_POST['ban_notes']);
	if (isset($_POST['ban_time']) && is_numeric($_POST['ban_time']) && ($_POST['entry_intent']== 'edit' || $_POST['entry_intent'] == 'add'))
	{
	  $bt = intval($_POST['ban_time']);
	  $new_vals['banlist_banexpires'] = $bt ? time() + ($bt*60*60) : 0;
	}
	if (isset($_POST['add_ban']))
	{  // Insert new value - can just pass an array
	  admin_update($sql -> db_Insert("banlist",$new_vals), 'insert');
	}
	else
	{  // Update existing value
	  $qry = '';
	  $spacer = '';
	  foreach ($new_vals as $k => $v)
	  {
	    $qry .= $spacer."`{$k}`='$v'";
	    $spacer = ', ';
	  }
	  admin_update($sql -> db_Update("banlist", $qry." WHERE banlist_ip='".$_POST['old_ip']."'"));
	}
  unset($ban_ip);
  }
}

// Remove a ban
if (($action == "remove" || $action == "whremove") && isset($_POST['ban_secure'])) 
//if ($action == "remove") 
{
  $sql -> db_Delete("generic", "gen_type='failed_login' AND gen_ip='{$sub_action}'");
  admin_update($sql -> db_Delete("banlist", "banlist_ip='{$sub_action}'"), 'delete');
  if ($action == "remove") $action = 'list'; else $action = 'white';
}


// Update the ban expiry time/date - timed from now (only done on banlist)
if ($action == 'newtime')
{
  $end_time = $id ? time() + ($id*60*60) : 0;
  admin_update($sql -> db_Update("banlist", "banlist_banexpires='".intval($end_time)."' WHERE banlist_ip='".$sub_action."'"));
  $action = 'list';
}


if ($action == "edit" || $action == "whedit") 
{
  $sql->db_Select("banlist", "*", "banlist_ip='{$sub_action}'");
  $row = $sql->db_Fetch();
  extract($row);
} 
else 
{
  unset($banlist_ip, $banlist_reason);
  if (e_QUERY && ($action == 'add' || $action == 'whadd') && strpos($_SERVER["HTTP_REFERER"], "userinfo")) 
  {
	$banlist_ip = $sub_action;
  }
}


function ban_time_dropdown($click_js = '', $zero_text=BANLAN_21, $curval=-1,$drop_name='ban_time')
{
  $intervals = array(0,1,2,3,6,8,12,24,36,48,72,96,120,168,336,672);
  $ret = "<select name='{$drop_name}' class='tbox' {$click_js}>\n";
  $ret .= "<option value=''>&nbsp;</option>\n";
  foreach ($intervals as $i)
  {
    $selected = ($curval == $i) ? " selected='selected'" : '';
	if ($i == 0)
	{
	  $words = $zero_text ? $zero_text : BANLAN_21;
	}
	elseif (($i % 24) == 0)
	{
	  $words = floor($i / 24).' '.BANLAN_23;
	}
	else
	{
	  $words = $i.' '.BANLAN_24;
	}
	$ret .= "<option value='{$i}'{$selected}>{$words}</option>\n";
  }
  $ret .= '</select>';
  return $ret;
}


// Character options for import & export
$separator_char = array(1 => ',', 2 => '|');
$quote_char = array(1 => '', 2 => "'", 3 => '"');


function select_box($name, $data, $curval = FALSE)
{
  $ret = "<select class='tbox' name='{$name}'>\n";
  foreach ($data as $k => $v)
  {
    $selected = '';
    if (($curval !== FALSE) && ($curval == $k)) $selected = " selected='selected'";
	$ret .= "<option value='{$k}'{$selected}>{$v}</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}

$text = "";


switch ($action)
{

  case 'options' :
	  if ((!isset($pref['ban_messages'])) || !is_array($pref['ban_messages']))
	  {
	    $pref['ban_messages'] = array_fill(0,BAN_REASON_COUNT-1,'');
	  }
	  if ((!isset($pref['ban_durations'])) || !is_array($pref['ban_durations']))
	  {
	    $pref['ban_durations'] = array_fill(0,BAN_REASON_COUNT-1,0);
	  }
	  $text = $rs->form_open("post", e_SELF.'?'.e_QUERY, "ban_options")."<div style='text-align:center'>";
	  if (!$ban_total = $sql->db_Select("banlist","*","ORDER BY banlist_ip","nowhere")) 
	  {
		$text .= "<div style='text-align:center'>".BANLAN_2."</div>";
	  } 
	  else 
	  {
		$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
			<colgroup>
			<col style='width:20%' />
			<col style='width:70%' />
			<col style='width:10%' />
			</colgroup>
			<tr>
			<td class='fcaption'>".BANLAN_28."</td>
			<td class='fcaption' style='text-align:center'>".BANLAN_29."<br /><span class='smallblacktext'>".BANLAN_31."</span></td>
			<td class='fcaption'>".BANLAN_30."</td>
			</tr>";
		for ($i = 0; $i < BAN_REASON_COUNT; $i++)
		{
		  $text .= "<tr>
		    <td class='forumheader3'><a title='".constant('BANLAN_11'.$i)."'>".constant('BANLAN_10'.$i)."</a></td>
		    <td class='forumheader3'>
			<textarea class='tbox' name='ban_text[]' cols='50' rows='4'>{$pref['ban_messages'][$i]}</textarea>
			</td>
		    <td class='forumheader3'>".ban_time_dropdown('',BANLAN_32,$pref['ban_durations'][$i],'ban_time[]')."</td>
			";
		}
		$text .= "<tr><td class='forumheader3' colspan='3' style='text-align:center'><input class='button' type='submit' name='update_ban_prefs' value='".LAN_UPDATE."' /></td></tr>
			</table>\n";
	  }
	  $text .= "</div>".$rs->form_close();
	  $ns->tablerender(BANLAN_3, $text);
    break;

  case 'edit' :
  case 'add' :
  case 'whedit' :
  case 'whadd' :
    $page_title = array('edit' => BANLAN_60, 'add' => BANLAN_9, 'whedit' => BANLAN_59, 'whadd' => BANLAN_58);
	$rdns_warn = varsettrue($pref['enable_rdns']) ? '' : '<br />'.BANLAN_12;
	$next = ($action == 'whedit' || $action == 'whadd') ? '?white' : '?list';
	// Edit/add form first
	$text .= "<div style='text-align:center'>
		<form method='post' action='".e_SELF.$next."'>
		<input type='hidden' name='entry_intent' value='{$action}' />
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		  <td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
		  <td style='width:70%' class='forumheader3'>
		  <input class='tbox' type='text' name='ban_ip' size='40' value='".$banlist_ip."' maxlength='200' />{$rdns_warn}
		  </td>
		</tr>";

	if (($action == 'add') || ($action == 'whadd') || ($banlist_bantype <= 1) || ($banlist_bantype >= BAN_TYPE_WHITELIST))
	{	// Its a manual or unknown entry - only allow edit of reason on those
	  $text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='ban_reason' cols='50' rows='4'>{$banlist_reason}</textarea>
		</td>
		</tr>";
	}
	elseif ($action == 'edit')
	{
	  $text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
		<td style='width:80%' class='forumheader3'>{$banlist_reason}</td>
		</tr>";
	}

	if ($action == 'edit')
	{
	  $text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_28.": </td>
		<td style='width:80%' class='forumheader3'>".constant('BANLAN_10'.$banlist_bantype)." - ".constant('BANLAN_11'.$banlist_bantype)."</td>
		</tr>";
	}

	$text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_19.": </td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='ban_notes' cols='50' rows='4'>{$banlist_notes}</textarea>
		</td>
		</tr>";

	if ($action == 'edit' || $action == 'add')
	{
	  $text .= "<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_18.": </td>
		<td style='width:80%' class='forumheader3'>".ban_time_dropdown().
		(($action == 'edit') ? '&nbsp;&nbsp;&nbsp;('.BANLAN_26.($banlist_banexpires ? strftime(BAN_TIME_FORMAT,$banlist_banexpires) : BANLAN_21).')' : '').
		"</td>
		</tr>";
	}

	$text .= "
		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
	if ($action == "edit" || $action == "whedit")
	{
	  $text .= "<input type='hidden' name='old_ip' value='{$banlist_ip}' /><input class='button' type='submit' name='update_ban' value='".LAN_UPDATE."' />";
	}
	else
	{
	  $text .= "<input class='button' type='submit' name='add_ban' value='".($action == 'add' ? BANLAN_8 : BANLAN_53)."' />";
	}
	$text .= "</td>
	</tr>
	</table>
	</form>
	</div>";

	$text .= "<div style='text-align:center'><br />".BANLAN_13."<a href='".e_ADMIN."users.php'><img src='".$images_path."users_16.png' alt='' /></a></div>";
	if(!varsettrue($pref['enable_rdns']))
	{
	  $text .= "<div style='text-align:center'><br />".BANLAN_12."</div>";
	}
	$ns->tablerender($page_title[$action], $text);
    break;			// End of 'Add' and 'Edit'


  case 'transfer' :
    $message = '';
    if (isset($_POST['ban_import']))
	{  // Got a file to import
	  require_once(e_HANDLER.'upload_handler.php');
	  if (($files = process_uploaded_files(e_FILE."public/",FALSE,array('overwrite'=>TRUE, 'max_file_count' => 1, 'file_mask'=> 'csv'))) === FALSE)
	  {  // Invalid file
	    $message = BANLAN_47;
	  }
	  if (!$message && $files[0]['error']) $message = $files[0]['message'];
	  if (!$message)
	  {  // Got a file of some sort
		$message = process_csv(e_FILE."public/".$files[0]['name'],
								intval(varset($_POST['ban_over_import'],0)),
								intval(varset($_POST['ban_over_expiry'],0)),
								$separator_char[intval(varset($_POST['ban_separator'],1))],
								$quote_char[intval(varset($_POST['ban_quote'],3))]);
	  }
	  
	}
	if ($message) $ns->tablerender(BANLAN_48, "<div style='text-align:center; font-weight:bold'>{$message}</div>");

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_ADMIN."banlist_export.php' name='ban_export_form' >
		<div><table>
		<colgroup>
		<col style='width:70%' />
		<col style='width:30%' />
		</colgroup>
		<tr><td class='fcaption'>".BANLAN_36."</td><td class='fcaption'>".BANLAN_15."</td></tr>";
	$text .= "<tr><td class='forumheader3' rowspan='3'>\n";
	$spacer = '';
	for ($i = 0;  $i < BAN_REASON_COUNT; $i++)
	{
	  $text .= $spacer."<input type='checkbox' name='ban_types[{$i}]' value='".($i)."'>&nbsp;".constant('BANLAN_10'.$i)." - ".constant('BANLAN_11'.$i);
	  $spacer = "<br />\n";
	}
	$text .= "</td><td class='forumheader3'>".select_box('ban_separator',$separator_char).' '.BANLAN_37;
	$text .= "</td></tr><tr><td class='forumheader3'>".select_box('ban_quote',$quote_char).' '.BANLAN_38."</td></tr><tr><td class='forumheader3' style='text-align:right'>";
	$text .= "<input class='button' type='submit' name='ban_export' value='".BANLAN_39."' />
	</td></tr>";
	$text .= "</table></form><br /><br /></div>";
	$ns->tablerender(BANLAN_40, $text);
	
	// Now do the import options
	$text = "<div style='text-align:center'>
		<form enctype=\"multipart/form-data\" method='post' action='".e_SELF."?transfer' name='ban_import_form' >
		<div><table>
		<colgroup>
		<col style='width:70%' />
		<col style='width:30%' />
		</colgroup>
		<tr><td class='fcaption'>".BANLAN_42."</td><td class='fcaption'>".BANLAN_15."</td></tr>";
	$text .= "<tr><td class='forumheader3' rowspan='2'>\n";
	$text .= "<input type='checkbox' name='ban_over_import' value='1'>&nbsp;".BANLAN_43.'<br />';
	$text .= "<input type='checkbox' name='ban_over_expiry' value='1'>&nbsp;".BANLAN_44;

	$text .= "</td><td class='forumheader3'>".select_box('ban_separator',$separator_char).' '.BANLAN_37;
	$text .= "</td></tr><tr><td class='forumheader3'>".select_box('ban_quote',$quote_char).' '.BANLAN_38."</td></tr>
		<tr><td class='forumheader3'><input class='tbox' type='file' name='file_userfile[]' style='width:90%' size='50' /></td>
		<td class='forumheader3' style='text-align:right'>";
	$text .= "<input class='button' type='submit' name='ban_import' value='".BANLAN_45."' />
	</td></tr>";
	$text .= "</table></form><br /><br /></div>";
	$ns->tablerender(BANLAN_41, $text);
    break;


  case 'list' :
  case 'white' :
  default :
	  if (($action != 'list') && ($action != 'white')) $action = 'list';
	  $edit_action = ($action == 'list' ? 'edit' : 'whedit');
	  $del_action = ($action == 'list' ? 'remove' : 'whremove');
	  $col_widths = array('list' => array(10,5,35,30,10,10), 'white' => array(15,40,35,10));
	  $col_titles = array('list' => array(BANLAN_17,BANLAN_20,BANLAN_10,BANLAN_19,BANLAN_18,LAN_OPTIONS),
							'white' => array(BANLAN_55,BANLAN_56,BANLAN_19,LAN_OPTIONS));
	  $no_values = array('list' => BANLAN_2, 'white' => BANLAN_54);
	  $col_defs = array('list' => array('banlist_datestamp'=>0,'banlist_bantype'=>0,'ip_reason'=>BANLAN_7,'banlist_notes'=>0,'banlist_banexpires'=>0,'ban_options'=>0),
						'white' => array('banlist_datestamp'=>0,'ip_reason'=>BANLAN_57,'banlist_notes'=>0,'ban_options'=>0));
							
	  $text = $rs->form_open("post", e_SELF.'?'.$action, "ban_form")."<div style='text-align:center'>".$rs->form_hidden("ban_secure", "1");
	  $filter = ($action == 'white') ? 'banlist_bantype='.BAN_TYPE_WHITELIST : 'banlist_bantype!='.BAN_TYPE_WHITELIST ;
	  if (!$ban_total = $sql->db_Select("banlist","*",$filter." ORDER BY banlist_ip")) 
	  {
		$text .= "<div style='text-align:center'>".$no_values[$action]."</div>";
	  } 
	  else 
	  {
		$text .= "<table class='fborder' style='".ADMIN_WIDTH."'><colgroup>";
		foreach($col_widths[$action] as $fw) $text .= "<col style='width:{$fw}%' />\n";
		$text .= "</colgroup>\n<tr>";
		foreach ($col_titles[$action] as $ct) $text .= "<td class='fcaption'>{$ct}</td>";
		$text .= "</tr>";
		while ($row = $sql->db_Fetch()) 
		{
		  extract($row);
		  $banlist_reason = str_replace("LAN_LOGIN_18", BANLAN_11, $banlist_reason);
		  $text .= "<tr>";
		  foreach ($col_defs[$action] as $cd => $fv)
		  {
		    switch ($cd)
			{
			  case 'banlist_datestamp' :
			    $val = ($banlist_datestamp ? strftime(BAN_TIME_FORMAT,$banlist_datestamp) : BANLAN_22 );
			    break;
			  case 'banlist_bantype' :
				$val = "<a title='".constant('BANLAN_11'.$banlist_bantype)."'>".constant('BANLAN_10'.$banlist_bantype)."</a>";
				break;
			  case 'ip_reason' :
			    $val = $banlist_ip."<br />".$fv.": ".$banlist_reason;
			    break;
			  case 'banlist_banexpires' :
			    $val = ($banlist_banexpires ? strftime(BAN_TIME_FORMAT,$banlist_banexpires).(($banlist_banexpires < time()) ? ' ('.BANLAN_34.')' : '') 
					: BANLAN_21)."<br />".ban_time_dropdown("onchange=\"urljump('".e_SELF."?newtime-{$banlist_ip}-'+this.value)\"");
			    break;
			  case 'ban_options' :
			    $val = "<a href='".e_SELF."?{$edit_action}-{$banlist_ip}'><img src='".$images_path."edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' /></a>
					<input name='delete_ban_entry' type='image' src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' 
					onclick=\" var r = jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$banlist_ip."]")."');
					if (r) { document.getElementById('ban_form').action='".e_SELF."?{$del_action}-{$banlist_ip}'; } return r; \" />";
				break;
			  case 'banlist_notes' :
			  default : 
			    $val = $row[$cd];
			}
			$text .= "<td class='forumheader3'>{$val}</td>";
		  }
		}
		$text .= "</table>\n";
	  }
	  $text .= "</div>".$rs->form_close();
	  $ns->tablerender(($action == 'list' ? BANLAN_3 : BANLAN_61), $text);
	  // End of case 'list' and the default case
}		// End switch ($action)


require_once("footer.php");


function banlist_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";

    $var['list']['text'] = BANLAN_14;			// List existing bans
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "4";

    $var['add']['text'] = BANLAN_25;			// Add a new ban
	$var['add']['link'] = e_SELF."?add";
	$var['add']['perm'] = "4";

    $var['white']['text'] = BANLAN_52;			// List existing whitelist entries
	$var['white']['link'] = e_SELF."?white";
	$var['white']['perm'] = "4";

    $var['whadd']['text'] = BANLAN_53;			// Add a new whitelist entry
	$var['whadd']['link'] = e_SELF."?whadd";
	$var['whadd']['perm'] = "4";

	$var['transfer']['text'] = BANLAN_35;
	$var['transfer']['link'] = e_SELF."?transfer";
   	$var['transfer']['perm'] = "4";

	if(getperms("0"))
	{
	  $var['options']['text'] = BANLAN_15;
	  $var['options']['link'] = e_SELF."?options";
   	  $var['options']['perm'] = "0";
    }
	show_admin_menu(BANLAN_16, $action, $var);
}



// Parse the date string used by the import/export - YYYYMMDD_HHMMSS
function parse_date($instr)
{
  if (strlen($instr) != 15) return 0;
  return mktime(substr($instr,9,2),substr($instr,11,2),substr($instr,13,2),substr($instr,4,2),substr($instr,6,2),substr($instr,0,4));
}


// Process the imported CSV file, update the database, delete the file.
// Return a message
function process_csv($filename, $override_imports, $override_expiry, $separator = ',', $quote = '"')
{
  global $sql, $pref;
//  echo "Read CSV: {$filename} separator: {$separator}, quote: {$quote}  override imports: {$override_imports}  override expiry: {$override_expiry}<br />";
  // Renumber imported bans
  if ($override_imports) $sql->db_Update('banlist', "`banlist_bantype`=".BAN_TYPE_TEMPORARY." WHERE `banlist_bantype` = ".BAN_TYPE_IMPORTED);
  $temp = file($filename);
  $line_num = 0;
  foreach ($temp as $line)
  {  // Process one entry
    $line = trim($line);
	$line_num++;
	if ($line)
	{
	  $fields = explode($separator,$line);
	  $field_num = 0;
	  $field_list = array('banlist_bantype' => BAN_TYPE_IMPORTED);
	  foreach ($fields as $f)
	  {
	    $f = trim($f);
	    if (substr($f,0,1) == $quote)
		{
		  if (substr($f,-1,1) == $quote)
		  {  // Strip quotes
		    $f = substr($f,1,-1);		// Strip off the quotes
		  }
		  else
		  {
		    return BANLAN_49.$line_num;
		  }
		}
		// Now handle the field
		$field_num++;
		switch ($field_num)
		{
		  case 1 :		// IP address
			$field_list['banlist_ip'] = $f;
		    break;
		  case 2 :		// Original date of ban
			$field_list['banlist_datestamp'] = parse_date($f);
			break;
		  case 3 :		// Expiry of ban - depends on $override_expiry
		    if ($override_expiry)
			{
			$field_list['banlist_banexpires'] = parse_date($f);
			}
			else
			{	// Use default ban time from now
			  $field_list['banlist_banexpires'] = $pref['ban_durations'][BAN_TYPE_IMPORTED] ? time() + (60*60*$pref['ban_durations'][BAN_TYPE_IMPORTED]) : 0;
			}
			break;
		  case 4 :		// Original ban type - we always ignore this and force to 'imported'
			break;
		  case 5 :		// Ban reason originally generated by E107
			$field_list['banlist_reason'] = $f;
			break;
		  case 6 :		// Any user notes added
			$field_list['banlist_notes'] = $f;
			break;
		  default :		// Just ignore any others
		}
	  }
	  $qry = "REPLACE INTO `#banlist` (".implode(',',array_keys($field_list)).") values ('".implode("', '",$field_list)."')";
//	  echo count($field_list)." elements, query: ".$qry."<br />";
	  if (!$sql->db_Select_gen($qry))
	  {
	    return BANLAN_50.$line_num;
	  }
	}
  }
  // Success here - may need to delete old imported bans
  if ($override_imports) $sql->db_Delete('banlist', "`banlist_bantype` = ".BAN_TYPE_TEMPORARY);
  @unlink($filename);			// Delete file once done
  return str_replace('--NUM--',$line_num, BANLAN_51).$filename;
}

?>
