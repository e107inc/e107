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
|     $Revision: 1.3 $
|     $Date: 2007-12-09 16:42:22 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

define('BAN_TIME_FORMAT',"%d-%m-%Y %H:%M");
define('BAN_REASON_COUNT',7);				// Update as more ban reasons added (max 10 supported)

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
  $new_ban_ip = preg_replace("/[^\w@\.]*/",'',urldecode($_POST['ban_ip']));
  if ($new_ban_ip != $_POST['ban_ip'])
  {
    $message = BANLAN_27.$new_ban_ip;
	$ns->tablerender(BANLAN_9, $message);
	$_POST['ban_ip'] = $new_ban_ip;
  }

  if ((isset($_POST['add_ban']) || isset($_POST['update_ban'])) && $_POST['ban_ip'] != "" && strpos($_POST['ban_ip'], ' ') === false)
  {
    $new_vals = array('banlist_ip' => $_POST['ban_ip']);
	if (isset($_POST['add_ban']))
	{
	  $new_vals['banlist_datestamp'] = time();
	  $new_vals['banlist_bantype'] = 1;				// Manual ban
	}
	$new_vals['banlist_admin'] = ADMINID;
	if (varsettrue($_POST['ban_reason'])) $new_vals['banlist_reason'] =$tp->toDB($_POST['ban_reason']);
	$new_vals['banlist_notes'] = $tp->toDB($_POST['ban_notes']);
	if (isset($_POST['ban_time']) && is_numeric($_POST['ban_time']))
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
if ($action == "remove" && isset($_POST['ban_secure'])) 
//if ($action == "remove") 
{
  $sql -> db_Delete("generic", "gen_type='failed_login' AND gen_ip='{$sub_action}'");
  admin_update($sql -> db_Delete("banlist", "banlist_ip='{$sub_action}'"), 'delete');
}


// Update the ban expiry time/date - timed from now
if ($action == 'newtime')
{
  $end_time = $id ? time() + ($id*60*60) : 0;
  admin_update($sql -> db_Update("banlist", "banlist_banexpires='".intval($end_time)."' WHERE banlist_ip='".$sub_action."'"));
  $action = 'list';
}


if ($action == "edit") 
{
  $sql->db_Select("banlist", "*", "banlist_ip='{$sub_action}'");
  $row = $sql->db_Fetch();
  extract($row);
} 
else 
{
  unset($banlist_ip, $banlist_reason);
  if (e_QUERY && ($action == 'add') && strpos($_SERVER["HTTP_REFERER"], "userinfo")) 
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
	$rdns_warn = varsettrue($pref['enable_rdns']) ? '' : '<br />'.BANLAN_12;
	// Edit/add form first
	$text .= "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		  <td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
		  <td style='width:70%' class='forumheader3'>
		  <input class='tbox' type='text' name='ban_ip' size='40' value='".$banlist_ip."' maxlength='200' />{$rdns_warn}
		  </td>
		</tr>";

	if (($action == 'add') || ($banlist_bantype <= 1))
	{	// Its a manual or unknown entry - only allow edit of reason on those
	  $text .= "
		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
		<td style='width:80%' class='forumheader3'>
		<textarea class='tbox' name='ban_reason' cols='50' rows='4'>{$banlist_reason}</textarea>
		</td>
		</tr>";
	}
	else
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
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".BANLAN_18.": </td>
		<td style='width:80%' class='forumheader3'>".ban_time_dropdown().
		(($action == 'edit') ? '&nbsp;&nbsp;&nbsp;('.BANLAN_26.($banlist_banexpires ? strftime(BAN_TIME_FORMAT,$banlist_banexpires) : BANLAN_21).')' : '').
		"</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>".
	($action == "edit" ? "<input type='hidden' name='old_ip' value='{$banlist_ip}' /><input class='button' type='submit' name='update_ban' value='".LAN_UPDATE."' />" : "<input class='button' type='submit' name='add_ban' value='".BANLAN_8."' />")."

	</td>
	</tr>
	</table>
	</form>
	</div>";

	$text .= "<div style='text-align:center'><br />".BANLAN_13."<a href='".e_ADMIN."users.php'><img src='".$images_path."users_16.png' alt='' /></a></div>";
	if(!varsettrue($pref['enable_rdns']))
	{
	  $text .= "<div style='text-align:center'><br />".BANLAN_12."</div>";
	}
	$ns->tablerender(BANLAN_9, $text);
    break;			// End of 'Add' and 'Edit'

  case 'list' :
  default :
	  $text = $rs->form_open("post", e_SELF, "ban_form")."<div style='text-align:center'>".$rs->form_hidden("ban_secure", "1");
	  if (!$ban_total = $sql->db_Select("banlist","*","ORDER BY banlist_ip","nowhere")) 
	  {
		$text .= "<div style='text-align:center'>".BANLAN_2."</div>";
	  } 
	  else 
	  {
		$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
			<colgroup>
			<col style='width:10%' />
			<col style='width:5%' />
			<col style='width:35%' />
			<col style='width:30%' />
			<col style='width:10%' />
			<col style='width:10%' />
			</colgroup>
			<tr>
			<td class='fcaption'>".BANLAN_17."</td>
			<td class='fcaption'>".BANLAN_20."</td>
			<td class='fcaption'>".BANLAN_10."</td>
			<td class='fcaption'>".BANLAN_19."</td>
			<td class='fcaption'>".BANLAN_18."</td>
			<td class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
		$count = 0;
		while ($row = $sql->db_Fetch()) 
		{
		  extract($row);
		  $banlist_reason = str_replace("LAN_LOGIN_18", BANLAN_11, $banlist_reason);
		  $text .= "<tr>
		    <td class='forumheader3'>".($banlist_datestamp ? strftime(BAN_TIME_FORMAT,$banlist_datestamp) : BANLAN_22 )."</td>
		    <td class='forumheader3'><a title='".constant('BANLAN_11'.$banlist_bantype)."'>".constant('BANLAN_10'.$banlist_bantype)."</a></td>
		    <td class='forumheader3'>{$banlist_ip}<br />".BANLAN_7.": {$banlist_reason}</td>
		    <td class='forumheader3'>{$banlist_notes}</td>
		    <td class='forumheader3'>".($banlist_banexpires ? strftime(BAN_TIME_FORMAT,$banlist_banexpires).(($banlist_banexpires < time()) ? ' ('.BANLAN_34.')' : '') 
			: BANLAN_21)."<br />
			".ban_time_dropdown("onchange=\"urljump('".e_SELF."?newtime-{$banlist_ip}-'+this.value)\"")."</td>
			<td style='width:30%; text-align:center' class='forumheader3'>
			<a href='".e_SELF."?edit-{$banlist_ip}'><img src='".$images_path."edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' /></a>
			<input name='delete_ban_entry' type='image' src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' 
			onclick=\" var r = jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$banlist_ip."]")."');
			if (r) { document.getElementById('ban_form').action='".e_SELF."?remove-{$banlist_ip}'; } return r; \" /></td>";
			$count++;
		}
		$text .= "</table>\n";
	  }
	  $text .= "</div>".$rs->form_close();
	  $ns->tablerender(BANLAN_3, $text);
	  // End of case 'list' and the default case
}		// End switch ($action)


require_once("footer.php");


function banlist_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";

    $var['list']['text'] = BANLAN_14;			// List existing bans
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "W";

    $var['add']['text'] = BANLAN_25;			// Add a new ban
	$var['add']['link'] = e_SELF."?add";
	$var['add']['perm'] = "W";

	if(getperms("0"))
	{
	  $var['options']['text'] = BANLAN_15;
	  $var['options']['link'] = e_SELF."?options";
   	  $var['options']['perm'] = "0";
    }
	show_admin_menu(BANLAN_16, $action, $var);
}


?>
