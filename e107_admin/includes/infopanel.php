<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Info panel admin view
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT'))
{
	exit;
}
//require_once (e_HANDLER."message_handler.php");
$emessage = e107::getMessage();
// require_once (e_HANDLER."form_handler.php");
$frm = e107::getForm();
//enable inner tabindex counter

if (isset($_POST['submit-mye107']) || varset($_POST['submit-mymenus']))
{
	$user_pref['core-infopanel-mye107'] = $_POST['e-mye107'];
	
	save_prefs('user');
	
	$pref['core-infopanel-menus'] = $_POST['e-mymenus'];
	
	save_prefs();
}


//TODO LANs throughout.

// ---------------------- Start Panel --------------------------------

	$text = "<div >";
	if (getperms('0') && !vartrue($user_pref['core-infopanel-mye107'])) // Set default icons.
	{
		$user_pref['core-infopanel-mye107'] = $pref['core-infopanel-default'];
	}

	$iconlist = array_merge($array_functions_assoc, getPluginLinks(E_16_PLUGMANAGER, "array"));

	$text .= "
	<form method='post' action='".e_SELF."?".e_QUERY."'>";
	
	$tp->parseTemplate("{SETSTYLE=core-infopanel}");
	
	// Personalized Panel 
	
	$mainPanel = "
	<div id='core-infopanel_mye107' >
		<div>
			<div class='left' style='padding:32px'>";
		// Rendering the saved configuration.
		foreach ($iconlist as $key=>$val)
		{
			if (!vartrue($user_pref['core-infopanel-mye107']) || in_array($key, $user_pref['core-infopanel-mye107']))
			{
				$mainPanel .= render_links($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div-icon-only");
			}
		}

		$mainPanel .= "<div class='clear'>&nbsp;</div>
             </div>
         </div>
		</div>";

	$text .= $ns->tablerender(ucwords(USERNAME).ADLAN_CP_01."",$mainPanel,"core-infopanel_mye107",true);
	

//  ------------------------------- e107 News --------------------------------
	
	
	$panelRSS = "
	";
	
	// TODO Load with Ajax


	/*
	$xml = e107::getXml();
	 $vars = $xml->loadXMLfile('http://www.e107.org/e107_plugins/rss_menu/rss.php?1.2', true, true);
	 $text .= print_r($vars,TRUE);
	*/

	$panelRSS .= ADLAN_CP_03;
/*

		</div>
	</div>
	</div>
	*/

	$text .= $ns->tablerender(ADLAN_CP_02,$panelRSS,"core-infopanel_news",true);

// ---------------------Latest Stuff ---------------------------

	require_once (e_CORE."shortcodes/batch/admin_shortcodes.php");
	
	$text .= $ns->tablerender(ADLAN_LAT_1,$tp->parseTemplate("{ADMIN_LATEST=norender}"),"core-infopanel_latest",true);
	$text .= $ns->tablerender(LAN_STATUS,$tp->parseTemplate("{ADMIN_STATUS=norender}"),"core-infopanel_latest",true);


// ---------------------- Who's Online  ------------------------
// TODO Could use a new _menu item instead.

	$nOnline = e107::getDB()->db_Select('online', '*');

$panelOnline = "
	
		<table class='table adminlist'>
		<colgroup>
			<col style='width: 10%' />
            <col style='width: 25%' />
			<col style='width: 10%' />
			<col style='width: 40%' />
			<col style='width: auto' />
		</colgroup>
		<thead>
			<tr>
				<th>".ADLAN_ONLINE_01."</th>
				<th>".ADLAN_ONLINE_02."</th>
				<th>".ADLAN_ONLINE_03."</th>
				<th>".ADLAN_ONLINE_04."</th>
				<th>".ADLAN_ONLINE_05."</th>
			</tr>
		</thead>
		<tbody>";
	
	
		
		
	if (e107::getDB()->db_Select('online', '*',"online_ip !='' LIMIT 20"))
	{
		$newsarray = $e107->sql->db_getList();
		foreach ($newsarray as $key=>$val)
		{
			$panelOnline .= "<tr>
				<td class='nowrap'>".e107::getDateConvert()->convert_date($val['online_timestamp'],'%H:%M:%S')."</td>
					<td>".renderOnlineName($val['online_user_id'])."</td>
					<td>".e107::getIPHandler()->ipDecode($val['online_ip'])."</td>
					<td><a href='".$val['online_location']."' title='".$val['online_location']."'>".$tp->text_truncate($val['online_location'],50,'...')."</a></td>
					<td>".$tp->text_truncate(str_replace("/"," / ",$val['online_agent']),20,'...')."</td>
				</tr>
				";
		}
	}

	$panelOnline .= "</tbody></table>
	";
	
	$text .= $ns->tablerender(''.ADLAN_ONLINE_00.': '.$nOnline, $panelOnline,'core-infopanel_online',true);
	
// --------------------- User Selected Menus -------------------
	

	if (varset($pref['core-infopanel-menus']))
	{
		foreach ($pref['core-infopanel-menus'] as $val)
		{
			$id = $frm->name2id('core-infopanel_'.$val);			
			$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
			$text .= $inc;
			// $text .= $ns->tablerender("", $inc, $id,true);
		}
	}







$text .= "<div class='clear'>&nbsp;</div>";

$text .= render_infopanel_options();


$text .= "</form>";
$text .= "</div>";

if($_GET['mode'] != 'customize')
{
	// $ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);	
	echo $emessage->render().$text;
}
else
{
	echo render_infopanel_options(true);	
}



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


function render_infopanel_options($render = false)
{
	// $frm = e107::getSingleton('e_form');
	$frm = e107::getForm();
	$mes = e107::getMessage();
	$ns = e107::getRender();
	
	$start = "<div>
	To customize this page, please <a title = 'Customize Admin' href='".e_SELF."?mode=customize&amp;iframe=1' class='e-modal-iframe'>click here</a>.
	</div>
    ";
    
    if($render == false){ return ""; }
    
	$text2 = "<div id='customize_icons' class='forumheader3' style='border:0px;margin:0px'>
    <form method='post' id='e-modal-form' action='".e_SELF."'>";
    
	$text2 .= $ns->tablerender("Personalize Icons",render_infopanel_icons(),'personalize',true); 
	$text2 .= "<div class='clear'>&nbsp;</div>";
	$text2 .= $ns->tablerender("Personalize Menus",render_infopanel_menu_options(),'personalize',true); 
//	$text2 .= render_infopanel_icons();
	//$text2 .= "<div class='clear'>&nbsp;</div>";
//	$text2 .= "<h3>Menus</h3>";
//	$text2 .= render_infopanel_menu_options();
	$text2 .= "<div class='clear'>&nbsp;</div>";
	$text2 .= "<div id='button' class='buttons-bar center'>";
	$text2 .= $frm->admin_button('submit-mye107', 'Save', 'create');
	$text2 .= "</div></form>";
//	$text2 .= "</div>";
	
//	$end = "</div>";
		
	
	return $mes->render().$text2.$end;
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
	$text .= "<div class='clear'>&nbsp;</div>";
	return $text;
}


function render_infopanel_menu_options()
{
	if(!getperms('0'))
	{
		return;
	}
	global $pref;
	$frm = e107::getSingleton('e_form');

	$text = "";
	$menu_qry = 'SELECT * FROM #menus WHERE menu_id!= 0  GROUP BY menu_name ORDER BY menu_name';
	$settings = varset($pref['core-infopanel-menus'],array());

	if (e107::getDb()->db_Select_gen($menu_qry))
	{
		while ($row = e107::getDb()->db_Fetch())
		{
			// if(!is_numeric($row['menu_path']))
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
	$text .= "<div class='clear'>&nbsp;</div>";
	return $text;
}
?>
