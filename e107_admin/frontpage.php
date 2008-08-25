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
|     $Source: /cvs_backup/e107_0.8/e107_admin/frontpage.php,v $
|     $Revision: 1.6 $
|     $Date: 2008-08-25 15:25:12 $
|     $Author: e107steved $
|
+----------------------------------------------------------------------------+
*/

require_once('../class2.php');
if (!getperms('G')) 
{
  header('location:'.e_BASE.'index.php');
  exit;
}
$e_sub_cat = 'frontpage';
require_once('auth.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
require_once(e_HANDLER.'userclass_class.php');



// Get list of possible options for front page
$front_page['news'] = array('page' => 'news.php', 'title' => ADLAN_0);
$front_page['download'] = array('page' => 'download.php', 'title' => ADLAN_24);
$front_page['wmessage'] = array('page' => 'index.php', 'title' => ADLAN_28);


if ($sql -> db_Select("page", "*", "page_theme=''")) 
{
  $front_page['custom']['title'] = 'Custom Page';
  while ($row = $sql -> db_Fetch()) 
  {
	$front_page['custom']['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
  }
}

// Now let any plugins add to the options - must append to the $front_page array as above
foreach($pref['e_frontpage_list'] as $val)
{
  if (is_readable(e_PLUGIN.$val."/e_frontpage.php"))
  {
	require_once(e_PLUGIN.$val."/e_frontpage.php");
  }
}


// Now sort out list of rules for display (based on $pref data to start with)
$gotpub = FALSE;
if (is_array($pref['frontpage']))
{
  $i = 1;
  foreach ($pref['frontpage'] as $class => $val)
  {
	if ($class == 'all')
	{
	  $class = e_UC_PUBLIC;
	  $gotpub = TRUE;
	}
	if ($val)
	{	// Only add non-null pages
      $fp_settings[$i] = array('order' => $i, 'class' => $class, 'page' => $val,'force' => varset($pref['frontpage_force'][$class],''));
	  $i++;
	}
  }
}
else
{  // Legacy stuff to convert
  $fp_settings = array();
  $fp_settings[] = array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => varset($pref['frontpage'],'news.php'),'force' => '');
}

if (!$gotpub)
{	// Need a 'default' setting - usually 'all'
  $fp_settings[] = array('order' => $i, 'class' => e_UC_PUBLIC, 'page' => varset($pref['frontpage']['all'],'news.php'),'force' => '');
}

$fp_update_prefs = FALSE;


if (isset($_POST['fp_inc']))
{
  $mv = intval($_POST['fp_inc']);
  if (($mv > 1) && ($mv <= count($fp_settings)))
  {
    $temp = $fp_settings[$mv-1];
	$fp_settings[$mv-1] = $fp_settings[$mv];
	$fp_settings[$mv] = $temp;
	$fp_update_prefs = TRUE;
	frontpage_adminlog('01','Inc '.$mv);
  }
}
elseif (isset($_POST['fp_dec']))
{
  $mv = intval($_POST['fp_dec']);
  if (($mv > 0) && ($mv < count($fp_settings)))
  {
    $temp = $fp_settings[$mv+1];
	$fp_settings[$mv+1] = $fp_settings[$mv];
	$fp_settings[$mv] = $temp;
	$fp_update_prefs = TRUE;
	frontpage_adminlog('01','Dec '.$mv);
  }
}


// Edit an existing rule
if (isset($_POST['fp_edit_rule'])) 
{
	$_POST['type'] = (isset($_POST['edit']['all'])) ? 'all_users' : 'user_class';
	$_POST['class'] = key($_POST['edit']);
}


// Cancel Edit




if (isset($_POST['fp_save_new']))
{  // Add or edit an existing rule here.
	// fp_order - zero for a new rule, non-zero if editing an existing rule
	// class - user class for rule
	// frontpage - radio button option indicating type of page (for home page)
	// frontpage_multipage[] - the other information for custom pages and similar - array index matches value of 'frontpage' when selected
	// frontpage_other - URL for 'other' home page
	// fp_force_page - radio button option indicating type of page (for post-login page)
	// fp_force_page_multipage[] - the other information for custom pages and similar - array index matches value of 'frontpage' when selected
	// fp_force_page_other - URL for forced post-login 'other' page
	
	
	if ($_POST['frontpage'] == 'other') 
	{
	  $_POST['frontpage_other'] = trim($tp -> toForm($_POST['frontpage_other']));
	  $frontpage_value = $_POST['frontpage_other'] ? $_POST['frontpage_other'] : 'news.php';
	} 
	else 
	{
	  if (is_array($front_page[$_POST['frontpage']]['page'])) 
	  {
		$frontpage_value = $front_page[$_POST['frontpage']]['page'][$_POST['frontpage_multipage'][$_POST['frontpage']]]['page'];
	  } 
	  else 
	  {
		$frontpage_value = $front_page[$_POST['frontpage']]['page'];
	  }
	}

	if ($_POST['fp_force_page'] == 'other') 
	{
	  $_POST['fp_force_page_other'] = trim($tp -> toForm($_POST['fp_force_page_other']));
	  $forcepage_value = $_POST['fp_force_page_other'];		// A null value is allowable here
	} 
	else 
	{
	  if (is_array($front_page[$_POST['fp_force_page']]['page'])) 
	  {
		$forcepage_value = $front_page[$_POST['fp_force_page']]['page'][$_POST['fp_force_page_multipage'][$_POST['fp_force_page']]]['page'];
	  } 
	  else 
	  {
		$forcepage_value = $front_page[$_POST['fp_force_page']]['page'];
	  }
	}

	$temp = array('order' => intval($_POST['fp_order']), 'class' => $_POST['class'], 'page' => $frontpage_value,'force' => trim($forcepage_value));
	if ($temp['order'] == 0)
	{	// New index to add
	  $ind = 0;
	  for ($i = 1; $i <= count($fp_settings); $i++)
	  {
	    if ($fp_settings[$i]['class'] == $temp['class']) $ind = $i;
	  }
	  if ($ind)
	  {
	    unset($fp_settings[$ind]);		// Knock out duplicate definition for class
		echo "duplicate definition for class: ".$ind."<br />";
	  }
	  array_unshift($fp_settings,$temp);		// Deliberately add twice
	  array_unshift($fp_settings,$temp);		// ....because re-indexed from zero
	  unset($fp_settings[0]);					// Then knock out index zero
	  $fp_update_prefs = TRUE;
		frontpage_adminlog('02',"class => {$_POST['class']},[!br!]page => {$frontpage_value},[!br!]force => {$forcepage_value}");
	}
	elseif (array_key_exists($temp['order'],$fp_settings))
	{
	  $fp_settings[$temp['order']] = $temp;
	  $fp_update_prefs = TRUE;
		frontpage_adminlog('03',"posn => {$temp},[!br!]class => {$_POST['class']},[!br!]page => {$frontpage_value},[!br!]force => {$forcepage_value}");
	}
	else
	{  // Someone playing games
      $ns -> tablerender(LAN_UPDATED, "<div style='text-align:center'><b>"."Software error"."</b></div>");
	}
}

if (isset($_POST['fp_delete_rule']))
{
  if (isset($fp_settings[key($_POST['fp_delete_rule'])])) 
  {
    $rule_no = key($_POST['fp_delete_rule']);
	$array_size = count($fp_settings);
	frontpage_adminlog('04',"Rule {$rule_no},[!br!]class => {$fp_settings[$rule_no]['class']},[!br!]page => {$fp_settings[$rule_no]['page']},[!br!]force => {$fp_settings[$rule_no]['force']}");
    unset($fp_settings[$rule_no]);
	while ($rule_no < $array_size)
	{  // Move up and renumber any entries after the deleted rule
	  $fp_settings[$rule_no] = $fp_settings[$rule_no + 1];
	  $rule_no++;
      unset($fp_settings[$rule_no]);
	}
	$fp_update_prefs = TRUE;
  }
}


if ($fp_update_prefs)
{  // Save the two arrays
  $fp_list = array();
  $fp_force = array();
  for ($i = 1; $i <= count($fp_settings); $i++)
  {
    $fp_list[$fp_settings[$i]['class']] = $fp_settings[$i]['page'];
//	$fp_force[$fp_settings[$i]['class']] = intval($fp_settings[$i]['force']);
	$fp_force[$fp_settings[$i]['class']] = $fp_settings[$i]['force'];
  }
//  if (($fp_list != $pref['frontpage']) || ($fp_force != $pref['frontpage_force']))
//  {
    $pref['frontpage'] = $fp_list;
	$pref['frontpage_force'] = $fp_force;
    save_prefs();
    $ns -> tablerender(LAN_UPDATED, "<div style='text-align:center'><b>".FRTLAN_1."</b></div>");
//  }
//  else
//  {
//    $ns -> tablerender(LAN_UPDATED, "<div style='text-align:center'><b>".FRTLAN_45."</b></div>");
//  }
}


/* For reference:
define("e_UC_PUBLIC", 0);
define("e_UC_MAINADMIN", 250);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);
*/

$fp = new frontpage;

if (isset($_POST['fp_add_new']))
{
  $fp->edit_rule(array('order' => 0, 'class' => e_UC_PUBLIC, 'page' => 'news.php','force' => FALSE));	// Display edit form as well
  $fp -> select_class(FALSE);
}
elseif (isset($_POST['fp_edit_rule']))
{
  $fp->edit_rule($fp_settings[key($_POST['fp_edit_rule'])]);	// Display edit form as well
  $fp -> select_class(FALSE);
}
else
{	// Just show existing rules
  $fp -> select_class(TRUE);
}



class frontpage 
{
	function select_class($show_button = TRUE) 
	{	// Display existing data
	  global $fp_settings, $rs, $ns, $front_page, $imode;
		

// List of current settings
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:  5%' />
		<col style='width: 25%' />
		<col style='width: 30%' />
		<col style='width: 30%' />
		<col style='width: 10%' />
		</colgroup>
		<tr><td class='forumheader3' colspan='5' style='text-align:center'>".FRTLAN_38."<br />".FRTLAN_39."<br />".FRTLAN_41."</td></tr>
		<tr>
		<td class='fcaption'>".FRTLAN_40."</td>
		<td class='fcaption'>".FRTLAN_53."</td>
		<td class='fcaption'>".FRTLAN_49."</td>
		<td class='fcaption'>".FRTLAN_35."</td>
		<td class='fcaption' style='text-align:center'>".LAN_EDIT."</td>
		</tr>";

	  foreach ($fp_settings as $order => $current_value) 
	  {
		$title = r_userclass_name($current_value['class']);
		$text .= "<tr><td class='forumheader3'>".$order."</td>
				<td class='forumheader3'>".$title."</td>
				<td class='forumheader3'>".$this->lookup_path($current_value['page'])."</td>
				<td class='forumheader3'>".$this->lookup_path($current_value['force'])."</td>
				<td class='forumheader3' style='text-align:center'>
				<input type='image' src='".e_IMAGE."packs/".$imode."/admin_images/up.png' title='".FRTLAN_47."' value='".$order."' name='fp_inc' />
				<input type='image' src='".e_IMAGE."packs/".$imode."/admin_images/down.png' title='".FRTLAN_48."' value='".$order."' name='fp_dec' />
				<input type='image' title='".LAN_EDIT."' name='fp_edit_rule[".$order."]' src='".ADMIN_EDIT_ICON_PATH."' />
				<input type='image' title='".LAN_DELETE."' name='fp_delete_rule[".$order."]' src='".ADMIN_DELETE_ICON_PATH."' />
				</td>
				</tr>";
	  }
	  if ($show_button)
	  {
	    $text .= "<tr><td colspan='5' style='text-align: center' class='forumheader'>
		".$rs -> form_button('submit', 'fp_add_new', FRTLAN_42)."</td></tr>";
	  }
	  $text .= "</table></form></div>";

	  $ns -> tablerender(FRTLAN_33, $text);
	}
 
 


	function edit_rule($rule_info)
	{	// Display form to add/edit rules
	  global $front_page, $rs, $ns;
	  // $rule_info contains existing data as an array, or a set of defaults otherwise ('order', 'class', 'page', 'force')
	  
	  $is_other_home = TRUE;
	  $is_other_force = TRUE;
	  $force_checked = $rule_info['force'] ? " checked='checked'" : '';
	  $text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width: 4%' />
		<col style='width: 24%' />
		<col style='width: 24%' />
		<col style='width: 4%' />
		<col style='width: 4%' />
		<col style='width: 24%' />
		<col style='width: 24%' />
		</colgroup>

		<tr><td colspan='7' class='fcaption' style='text-align:center;'>".($rule_info['order'] ? FRTLAN_46 : FRTLAN_42)."</td></tr>
		<tr>
		<td class='forumheader3' style='text-align:center' colspan='7'>
		".FRTLAN_43.r_userclass('class', $rule_info['class'], 'off', 'public,guest,member,admin,main,classes')."</td>
		</tr><tr><td  colspan='3' class='fcaption' style='text-align:center;'>".FRTLAN_49."</td><td>&nbsp;</td>
		<td  colspan='3' class='fcaption' style='text-align:center;'>".FRTLAN_35."<br />".FRTLAN_50."</td></tr>";

		foreach ($front_page as $front_key => $front_value) 
		{
		  $type_selected = FALSE;
		  $text .= "<tr>".$this->show_front_val('frontpage',$front_key,$front_value,$is_other_home,$rule_info['page']);
  		  $text .= "<td>&nbsp;</td>";		// Spacer
		  $text .= $this->show_front_val('fp_force_page',$front_key,$front_value,$is_other_force,$rule_info['force'])."</tr>";
		}
		// Now add in the 'other' URL box
		$text .= "<tr>".$this->add_other('frontpage', $is_other_home, $rule_info['page'])."<td>&nbsp;</td>".
					$this->add_other('fp_force_page', $is_other_force, $rule_info['force'])."</tr>";

		// 'Save' and 'Cancel' buttons
		$text .= "<tr style='vertical-align:top'>
		<td colspan='7' style='text-align: center' class='forumheader'>";
		$text .= $rs -> form_hidden('fp_order', $rule_info['order']);
		$text .= $rs -> form_button('submit', 'fp_save_new', FRTLAN_12)."&nbsp;&nbsp;&nbsp;&nbsp;".$rs -> form_button('submit', 'fp_cancel', LAN_CANCEL);
		$text .= "</td>
		</tr>

		</table>
		</form>
		</div><br /><br />";

		$ns -> tablerender(FRTLAN_13, $text);
	}


	// Given a path string, returns the 'type' (title) for it
	function lookup_path($path)
	{
	  global $front_page;
	  foreach ($front_page as $front_key => $front_value) 
	  {
	    if (is_array($front_value['page'])) 
	    {  // Its a URL with multiple options
		  foreach ($front_value['page'] as $multipage) 
		  {
		    if ($path == $multipage['page']) 
		    {
//			  return $front_value['title'].":".$path;
			  return $front_value['title'].":".$multipage['title'];
		    }
		  }
	    } 
	    else 
	    {
		  if ($path == $front_value['page']) 
		  {
			return $front_value['title'];
		  }
	    }
	  }
	  if (strlen($path)) return FRTLAN_51.":".$path;		// 'Other'
	  else return FRTLAN_52;			// 'None'
	}
	
	
	
	function show_front_val($ob_name, $front_key, $front_value, &$is_other, $current_setting)
	{
	  global $rs;

	  $type_selected = FALSE;
	  $text = '';

	  if (is_array($front_value['page'])) 
	  {  // Its a URL with multiple options
		foreach ($front_value['page'] as $multipage) 
		{
		  if ($current_setting == $multipage['page']) 
		  {
			$type_selected = TRUE;
			$is_other = FALSE;
		  }
		}
	  } 
	  else 
	  {
		if ($current_setting == $front_value['page']) 
		{
		  $type_selected = TRUE;
		  $is_other = FALSE;
		}
	  }

	  $text .= "<td class='forumheader3'>";
	  $text .= $rs -> form_radio($ob_name, $front_key, $type_selected);
	  $text .= "</td>";

		  if (is_array($front_value['page'])) 
		  {  // Multiple options for same page name
			$text .= "<td class='forumheader3'>".$front_value['title']."</td>";
			$text .= "<td class='forumheader3'>";
			$text .= $rs -> form_select_open($ob_name.'_multipage['.$front_key.']');
			foreach ($front_value['page'] as $multipage_key => $multipage_value) 
			{
			  $sub_selected = ($current_setting == $multipage_value['page']) ? TRUE : FALSE;
			  $text .= $rs -> form_option($multipage_value['title'], $sub_selected, $multipage_key);
			}
			$text .= $rs -> form_select_close();
			$text .= "</td>";
		  } 
		  else 
		  {  // Single option for URL
			$text .= "<td colspan='2' class='forumheader3'>".$front_value['title']."</td>";
		  }
	  return $text;
	}


	function add_other($ob_name, $cur_val, $cur_page)
	{
	  global $rs;
	  return  "<td class='forumheader3'>".$rs -> form_radio($ob_name, 'other', $cur_val)."</td>
		<td class='forumheader3'>".FRTLAN_15."</td>
		<td class='forumheader3'>
		".$rs -> form_text($ob_name.'_other', 50, ($cur_val ? $cur_page : ''),150)."
		</td>";
	}
}

require_once('footer.php');


// Log event to admin log
function frontpage_adminlog($msg_num='00', $woffle='')
{
  global $pref, $admin_log;
  $admin_log->log_event('FRONTPG_'.$msg_num,$woffle,E_LOG_INFORMATIVE,'');
}


?>