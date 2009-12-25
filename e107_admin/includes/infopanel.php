<?php
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     Copyright (C) 2008-2009 e107 Inc (e107.org)
 |     http://e107.org
 |
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_admin/includes/infopanel.php,v $
 |     $Revision: 1.20 $
 |     $Date: 2009-12-25 23:32:18 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */
if (!defined('e107_INIT'))
{
	exit;
}
require_once (e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();
require_once (e_HANDLER."form_handler.php");
$frm = new e_form(true);
//enable inner tabindex counter

if (isset($_POST['submit-mye107']) || varset($_POST['submit-mymenus']))
{
	$user_pref['core-infopanel-mye107'] = $_POST['e-mye107'];
	$user_pref['core-infopanel-menus'] = $_POST['e-mymenus'];
	save_prefs('user');
}


//TODO LANs throughout. 

// ---------------------- Start Panel --------------------------------

	$text = "<div style='text-align:center'>";
	if (getperms('0') && !vartrue($user_pref['core-infopanel-mye107'])) // Set default icons.
	{
		$user_pref['core-infopanel-mye107'] = $pref['core-infopanel-default'];
	}
		
	$iconlist = array_merge($array_functions_assoc, getPluginLinks(E_16_PLUGMANAGER, "array"));

	$text .= "
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	<div id='core-infopanel_mye107' class='f-left' style='width:49%'>
		<div style='border:1px solid silver;margin:10px'>
			<div class='main_caption bevel left'><b>Welcome to your e107 Content Management System</b></div>

			<div class='left block-text' >
            	<h1>".ucwords(USERNAME)."'s Admin Panel</h1>
				Welcome to your Website Content Manager
				<br />

            </div>

			<div class='left' style='padding:25px'>";
		// Rendering the saved configuration.
		foreach ($iconlist as $key=>$val)
		{
			if (!vartrue($user_pref['core-infopanel-mye107']) || in_array($key, $user_pref['core-infopanel-mye107']))
			{
				$text .= render_links($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div");
			}
		}
		
		$text .= "<div class='clear'>&nbsp;</div>
             </div>
         </div>
		</div>";
	
	
//  ------------------------------- e107 News --------------------------------
	$text .= "
	<div id='core-infopanel_news' class='f-left' style='width:49%'>
	<div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>e107 News</b></div>
	<div class='left block-text'>";
	// TODO Load with Ajax
	
	
	/*
	$xml = e107::getXml();
	 $vars = $xml->loadXMLfile('http://www.e107.org/e107_plugins/rss_menu/rss.php?1.2', true, true);
	 $text .= print_r($vars,TRUE);
	*/
	
	$text .= "
    RSS News feed from e107.org goes here.
	</div>
	</div>
	</div>
	";
	
// ---------------------Latest Stuff ---------------------------
$text .= "
	<div id='core-infopanel_latest' class='f-left' style='width:49%' >
	<div style='border:1px solid silver;margin:10px'>
	<table cellspacing='0' cellpadding='0'>
	<tr>
	<td style='padding:0px'>";
	require_once (e_FILE."shortcode/batch/admin_shortcodes.php");
	$text .= $tp->parseTemplate("{ADMIN_LATEST}");
	$text .= "</td><td style='padding:0px'>";
	$text .= $tp->parseTemplate("{ADMIN_STATUS}");
	$text .= "</td></tr></table>
	</div>
	</div>
	";
	
// ---------------------- Who's Online  ------------------------
// TODO Could use a new _menu item instead. 
$text .= "
	<div id='core-infopanel_online' class='f-left' style='width:49%'>
	<div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>Who's Online</b></div>
	<div class='left block-text'>
  

		<table cellpadding='0' cellspacing='0' class='adminlist'>
		<colgroup span='3'>
			<col style='width: 5%'></col>
            <col style='width: 35%'></col>
			<col style='width: 45%'></col>
		</colgroup>
		<thead>
			<tr>
				<th>Timestamp</th>
				<th>Username</th>
				<th>IP</th>
				<th>Page</th>
				<th>Agent</th>
			</tr>
		</thead>
		<tbody>";
	if (e107::getDB()->db_Select('online', '*'))
	{
		$newsarray = $e107->sql->db_getList();
		foreach ($newsarray as $key=>$val)
		{
			$text .= "<tr>
				<td class='nowrap'>".e107::getDateConvert()->convert_date($val['online_timestamp'],'short')."</td>
					<td>".renderOnlineName($val['online_user_id'])."</td>
					<td>".e107::ipDecode($val['online_ip'])."</td>
					<td class='nowrap'><a href='".$val['online_location']."'>".$tp->text_truncate($val['online_location'],50)."</a></td>
					<td class='nowrap'>".$tp->text_truncate($val['online_agent'],20,'...')."</td>
				</tr>
				";
		}
	}
	
	$text .= "</tbody></table></div>
	</div>
	</div>
	";
// --------------------- User Selected Menus -------------------

	if (varset($user_pref['core-infopanel-menus']))
	{

		foreach ($user_pref['core-infopanel-menus'] as $val)
		{
			$id = $frm->name2id('core-infopanel_'.$val);
			$text .= "
			<div id='".$id."' class='f-left' style='width:49%' >
			<div class='left' style='border:1px solid silver;margin:10px'>
			";
			$text .= $tp->parseTemplate("{PLUGIN=$val|TRUE}");
			$text .= "
			</div>
			</div>
			";
		}
	}







$text .= "<div class='clear'>&nbsp;</div>";

$text .= render_infopanel_options();


$text .= "</form>";
$text .= "</div>";
$ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);



function renderOnlineName($val)
{
	if($val==0)
	{
		return "Guest";
	}
	return $val;	
}

function render_info_panel($caption, $text)
{
	return "<div class='main_caption bevel left'><b>".$caption."</b></div>
    <div class='left block-text' >".$text."</div>";
}
// ------------------


function render_infopanel_options()
{
	$frm = e107::getSingleton('e_form');
	$start = "<div>To customize this page, please <a href='#customize_icons' class='e-expandit'>click here</a>.</div>
    <div class='e-hideme' id='customize_icons' style='border:1px solid silver;margin:10px'>";
	$text2 = "<h2>Icons</h2>";
	$text2 .= render_infopanel_icons();
	$text2 .= "<div class='clear'>&nbsp;</div>";
	$text2 .= "<h2>Menus</h2>";
	$text2 .= render_infopanel_menu_options();
	$text2 .= "<div class='clear'>&nbsp;</div>";
	$text2 .= "<div id='button' class='buttons-bar center'>";
	$text2 .= $frm->admin_button('submit-mye107', 'Save', 'Save');
	$text2 .= "</div>";
	$end = "</div>";

	return $start.render_info_panel("Customize", $text2).$end;
}


function render_infopanel_icons()
{
	$frm = e107::getSingleton('e_form');
	global $iconlist,$pluglist, $user_pref;
	$text = "";
	

	foreach ($iconlist as $key=>$icon)
	{
		if (getperms($icon['perms']))
		{
			$checked = (varset($user_pref['core-infopanel-mye107']) && in_array($key, $user_pref['core-infopanel-mye107'])) ? true : false;
			$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
	                        ".$icon['icon'].' '.$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
		}
	}
	if (is_array($pluglist))
	{
		foreach ($pluglist as $key=>$icon)
		{
			if (getperms($icon['perms']))
			{
				$checked = (in_array('p-'.$key, $user_pref['core-infopanel-mye107'])) ? true : false;
				$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
		                         ".$icon['icon'].$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
			}
		}
	}
	return $text;
}


function render_infopanel_menu_options()
{
	global $user_pref;
	$frm = e107::getSingleton('e_form');
	$text = "";
	$menu_qry = 'SELECT * FROM #menus WHERE menu_id!= 0  GROUP BY menu_name ORDER BY menu_name';
	$settings = varset($user_pref['core-infopanel-menus'],array());
	
	if (e107::getDb()->db_Select_gen($menu_qry))
	{
		while ($row = e107::getDb()->db_Fetch())
		{
			if(!is_numeric($row['menu_path']))
			{
				$label = str_replace("_menu","",$row['menu_name']);
				$path_to_menu = $row['menu_path'].$row['menu_name'];
				$checked = ($settings && in_array($path_to_menu, $settings)) ? true : false;
				$text .= "\n<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>";
				$text .= $frm->checkbox_label($label, "e-mymenus[]",$path_to_menu, $checked);
				$text .= "</div>";
			}
		}
	}
	return $text;
}
?>
