<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
	 exit ;
}
require_once(e_ADMIN."auth.php");
include_lan(e_PLUGIN."linkwords/languages/".e_LANGUAGE.".php");

$lw_context_areas = array(
			'title' => LWLAN_33,
			'summary' => LWLAN_34,
			'body' => LWLAN_35,
			'description' => LWLAN_36,
			'user_title' => LWLAN_40,
			'user_body'  => LWLAN_41
			// Don't do the next three - linkwords are meaningless on them
//			'olddefault' => LWLAN_37,
//			'linktext' => LWLAN_38,
//			'rawtext' => LWLAN_39'
		);
		
$deltest = array_flip($_POST);

if(isset($deltest[LWLAN_17]))
{
	$delete_id = str_replace("delete_", "", $deltest[LWLAN_17]);

	if ($sql->db_Count("linkwords", "(*)", "WHERE linkword_id = ".$delete_id))
	{
		$sql->db_Delete("linkwords", "linkword_id=".$delete_id);
		$message = LWLAN_19;
	}
}

if(e_QUERY)
{
  $lw_qs = explode(".", e_QUERY);
  if (!isset($lw_qs[0])) $lw_qs[0] = 'words';
  if (!isset($lw_qs[1])) $lw_qs[1] = -1;
  $action = $lw_qs[0];
  $id = $lw_qs[1];
}
if (!isset($action)) $action = 'words';

if (isset($_POST['saveopts_linkword']))
{  // Save options page
  // Array of context flags
	$pref['lw_context_visibility'] = array(			
			'olddefault' => FALSE,
			'title' => FALSE,
			'summary' => FALSE,
			'body' => FALSE,
			'description' => FALSE,
			'linktext' => FALSE,
			'rawtext' => FALSE
			);
  foreach ($_POST['lw_visibility_area'] as $can_see)
  {
    if (key_exists($can_see,$lw_context_areas))
	{
	  $pref['lw_context_visibility'][$can_see] = TRUE;
	}
  }
  // Text area for 'exclude' pages - use same method as for menus
  $pagelist = explode("\r\n", $_POST['linkword_omit_pages']);
  for ($i = 0 ; $i < count($pagelist) ; $i++) 
  {
	$pagelist[$i] = trim($pagelist[$i]);
  }
  $pref['lw_page_visibility'] = '2-'.implode("|", $pagelist);		// '2' for 'hide on specified pages'
  save_prefs();
}


if (isset($_POST['submit_linkword']))
{
	if(!$_POST['linkwords_word'] && $_POST['linkwords_url'])
	{
		$message = LWLAN_1;
	}
	else
	{
		$word = $tp -> toDB($_POST['linkword_word']);
		$link = $tp -> toDB($_POST['linkword_link']);
		$active = intval($_POST['linkword_active']);
		$sql -> db_Insert("linkwords", "0, {$active}, '{$word}', '{$link}' ");
		$message = LWLAN_2;
	}
}

if (isset($_POST['update_linkword']))
{
	if(!$_POST['linkwords_word'] && $_POST['linkwords_url'])
	{
		$message = LWLAN_1;
	}
	else
	{
		$id = $_POST['id'];
		$word = $tp -> toDB($_POST['linkword_word']);
		$link = $tp -> toDB($_POST['linkword_link']);
		$active = $_POST['linkword_active'];
		$sql -> db_Update("linkwords", "linkword_active=$active, linkword_word='$word', linkword_link='$link' WHERE linkword_id=".$id);
		$message = LWLAN_3;
	}
}


if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



if($action == "edit")
{
  if($sql -> db_Select("linkwords", "*", "linkword_id=".$id))
  {
	$row = $sql -> db_Fetch();
	extract($row);
	define("LW_EDIT", TRUE);
  }
}
else
{
  unset($linkword_word, $linkword_link, $linkword_active);
}



if (($action == 'words') || ($action == 'edit'))
{
$text = "
<div class='center'>
<form method='post' action='".e_SELF."?words'>
<table style='".ADMIN_WIDTH."' class='fborder'>

<tr>
<td style='width:50%' class='forumheader3'>".LWLAN_21."</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='linkword_word' size='40' value='".$linkword_word."' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".LWLAN_6."</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='linkword_link' size='60' value='".$linkword_link."' maxlength='150' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".LWLAN_22."</td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input type='radio' name='linkword_active' value='0'".(!$linkword_active ? " checked='checked'" : "")." /> ".LWLAN_9."&nbsp;&nbsp;
<input type='radio' name='linkword_active' value='1'".($linkword_active ? " checked='checked'" : "")." /> ".LWLAN_10."
</td>
</tr>

<tr>
<td colspan='2' style='text-align:center' class='forumheader'>".
(defined("LW_EDIT") ? "<input class='button' type='submit' name='update_linkword' value='".LWLAN_15."' /><input type='hidden' name='id' value='$id' />" : "<input class='button' type='submit' name='submit_linkword' value='".LWLAN_14."' />")."
</td>
</tr>
</table>
</form>
</div>\n";

$ns -> tablerender(LWLAN_31, $text);
}


// Now display existing linkwords
if (($action == 'words') || ($action == 'edit'))
{
  $text = "<div class='center'>\n";
  if(!$sql -> db_Select("linkwords"))
  {
	$text .= LWLAN_4;
  }
  else
  {
	$text = "
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='forumheader' style='width: 20%;'>".LWLAN_5."</td>
	<td class='forumheader' style='width: 40%;'>".LWLAN_6."</td>
	<td class='forumheader' style='width: 10%; text-align: center;'>".LWLAN_7."</td>
	<td class='forumheader' style='width: 30%; text-align: center;'>".LWLAN_8."</td>
	</tr>\n";

	while($row = $sql -> db_Fetch())
	{
		extract($row);
		$text .= "
		
		<form action='".e_SELF."' method='post' id='myform_$linkword_id'  onsubmit=\"return jsconfirm('".LWLAN_18." [ID: $linkword_id ]')\">
		<tr>
		<td class='forumheader' style='width: 20%;'>$linkword_word</td>
		<td class='forumheader' style='width: 40%;'>$linkword_link</td>
		<td class='forumheader' style='width: 10%; text-align: center;'>".(!$linkword_active ? LWLAN_12 : LWLAN_13)."</td>
		<td class='forumheader' style='width: 30%; text-align: center;'>
		<input class='button' type='button' onclick=\"document.location='".e_SELF."?edit.$linkword_id'\" value='".LWLAN_16."' id='edit_$linkword_id' name='edit_linkword_id' />
		<input class='button' type='submit'  value='".LWLAN_17."' id='delete_$linkword_id' name='delete_$linkword_id' />
		</td>
		</tr>
		</form>\n";
	}
	$text .= "</table>";
  }

  $text .= "</div>";
  $ns -> tablerender(LWLAN_11, $text);
}



if ($action=='options')
{
  $menu_pages = substr($pref['lw_page_visibility'],2);    // Knock off the 'show/hide' flag
  $menu_pages = str_replace("|", "\n", $menu_pages);
  $text = "
  <div class='center'>
  <form method='post' action='".e_SELF."?options'>
  <table style='".ADMIN_WIDTH."' class='fborder'>
	<colgroup>
	<col style='width: 30%; vertical-align:top;' />
	<col style='width: 40%; vertical-align:top;' />
	<col style='width: 30%; vertical-align:top;' />
	</colgroup>
  <tr>
  <td class='forumheader3'>".LWLAN_26."</td>
  <td class='forumheader3'>";
  foreach ($lw_context_areas as $lw_key=>$lw_desc)
  {
    $checked = $pref['lw_context_visibility'][$lw_key] ? 'checked=checked' : '';
	$text .= "<input type='checkbox' name='lw_visibility_area[]' value={$lw_key} {$checked} />{$lw_desc}<br />";
  }
  $text .= "</td>
  <td class='forumheader3'>".LWLAN_27."
  </tr>

  <tr>
  <td class='forumheader3'>".LWLAN_28."</td>
  <td class='forumheader3'><textarea rows='5' cols='60' class='tbox' name='linkword_omit_pages' >".$menu_pages."</textarea>
  </td>
  <td class='forumheader3'>".LWLAN_29."
  </tr>

<tr>
<td colspan='3' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='saveopts_linkword' value='".LWLAN_30."' />
</td>
</tr>
</table>
</form>
</div>\n";

$ns -> tablerender(LWLAN_32, $text);
}



function admin_config_adminmenu()
{
  if (e_QUERY) 
  {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
  }
  if (!isset($action) || ($action == ""))
  {
	$action = "words";
  }
  $var['words']['text'] = LWLAN_24;
  $var['words']['link'] = "admin_config.php";
	
  $var['options']['text'] = LWLAN_25;
  $var['options']['link'] ="admin_config.php?options";
	
  show_admin_menu(LWLAN_23, $action, $var);
}

	
require_once(e_ADMIN."footer.php");
?>