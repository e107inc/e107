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
|     $Source: /cvs_backup/e107_0.8/e107_admin/includes/infopanel.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-08-05 14:20:41 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT'))
{
	exit;
}
require_once (e_HANDLER."message_handler.php");
$emessage = & eMessage :: getInstance();
require_once (e_HANDLER."form_handler.php");
$frm = new e_form(true);
//enable inner tabindex counter
if (isset ($_POST['submit-mye107']))
{
	$user_pref['core-infopanel-mye107'] = $_POST['e-mye107'];
	save_prefs('user');
}
// $text = "<div style='text-align:center'>";

/*$text .="
	<div class='admintabs' id='tab-container'>
<ul class='e-tabs e-hideme' id='core-emote-tabs'>
<li id='tab-infopanel_mye107'><a href='#core-infopanel_mye107'>My Admin Panel</a></li>
<li id='tab-infopanel_news'><a href='#core-infopanel_news'>e107 News</a></li>
<li id='tab-infopanel_latest'><a href='#core-infopanel_latest'>Info</a></li>
<li id='tab-infopanel_online'><a href='#core-infopanel_online'>Who's Online</a></li>
<li id='tab-infopanel_customize'><a href='#core-infopanel_customize'>Customize</a></li>
<li id='tab-infopanel_add'><a href='#core-infopanel_add'>+</a></li>
</ul>";
*/
$text = "<div style='text-align:center'>";
// My E107
// Info about attributes

/*
attribute 1 = link
attribute 2 = title
attribute 3 = description
attribute 4 = perms
attribute 5 = category
1 - settings
2 - users
3 - content
4 - tools
5 - plugins
6 - about
attribute 6 = 16 x 16 image
attribute 7 = 32 x 32 image
*/

/*          $buts = "";

		    while (list($key, $funcinfo) = each($array_functions_assoc))
{
$iconlist[$key] = array("title"=>$funcinfo[1],"icon"=>$funcinfo[5]); // , $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[6], "classis");
$buts .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[6], "classis");
}*/





$iconlist = array_merge($array_functions_assoc,getPluginLinks(E_16_PLUGMANAGER,"array"));
$text .= "

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

foreach ($iconlist as $key => $val)
{
	if (!isset($user_pref['core-infopanel-mye107']) || in_array($key,$user_pref['core-infopanel-mye107']) )
	{
		$text .= render_links($val['link'],$val['title'],$val['caption'],$val['perms'],$val['icon_32'],"div");
	}
}
$text .= "<div class='clear'>&nbsp;</div>
             </div>

         <div class='left block-text' >
			To customize the icons that appear in this panel, please
         <a href='#customize_icons' class='e-expandit'>click here</a>.
		 <form method='post' action='".e_SELF."?".e_QUERY."'>
	<div id='customize_icons' class='e-hideme block-text'>
    <div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>Customize Icons</b></div>
    <div class='left block-text' >";

foreach ($iconlist as $key => $icon)
{
	if(getperms($icon['perms']))
	{
		$checked = (varset($user_pref['core-infopanel-mye107']) && in_array($key,$user_pref['core-infopanel-mye107'])) ? true : false;
		$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
                        ".$icon['icon'].$frm->checkbox('e-mye107[]',$key,$checked).$icon['title']."</div>";
	}
}


if (is_array($pluglist))
{
	foreach ($pluglist as $key => $icon)
	{
    	if(getperms($icon['perms']))
		{
			$checked = (in_array('p-'.$key,$user_pref['core-infopanel-mye107'])) ? true : false;
			$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
	                         ".$icon['icon'].$frm->checkbox('e-mye107[]',$key,$checked).$icon['title']."</div>";
		}
	}
}
$text .= "<div class='clear'>&nbsp;</div>";
$text .= "<div id='button' class='buttons-bar center'>";
// has issues with the checkboxes.
$text .= $frm->admin_button('submit-mye107','Save','Save');
$text .= "</div></div></div></div></form>


         </div>


	</div>
	</div>
	";



// e107 News  ------------------------------------------------------------------
$text .= "
	<div id='core-infopanel_news' class='f-left' style='width:49%'>
	<div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>e107 News</b></div>
	<div class='left block-text'>";

/*
require_once(e_HANDLER.'xml_class.php');
$xml = new xmlClass;
$vars = $xml->loadXMLfile('http://www.e107.org/e107_plugins/rss_menu/rss.php?1.2', true, true);
$text .= print_r($vars,TRUE);*/

$text .= "
    RSS News feed from e107.org goes here.
	</div>
	</div>
	</div>
	";



// e107 latest
$text .= "
	<div id='core-infopanel_latest' class='f-left' style='width:49%' >
	<div style='border:1px solid silver;margin:10px'>
	<table cellspacing='0' cellpadding='0'>
	<tr>
	<td style='padding:0px'>
";
require_once (e_FILE."shortcode/batch/admin_shortcodes.php");
$text .= $tp->parseTemplate("{ADMIN_LATEST}");
$text .= "</td><td style='padding:0px'>";
$text .= $tp->parseTemplate("{ADMIN_STATUS}");
$text .= "</td></tr></table>


	</div>
	</div>
	";


// Who's Online ---------------------------------
$text .= "
	<div id='core-infopanel_online' class='f-left' style='width:49%'>
	<div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>Who's Online</b></div>
	<div class='left block-text'>
  <form action='".e_SELF."' id='onlineform' method='post'>

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
				<th>Location</th>
			</tr>
		</thead>
		<tbody>";


if ($e107->sql->db_Select('online','*'))
{
	$newsarray = $e107->sql->db_getList();
	foreach ($newsarray as $key => $val)
	{
		$text .= "<tr>
			<td>".$val['online_timestamp']."</td>
				<td>".$val['online_user_id']."</td>
				<td>".$val['online_location']."</td>
			</tr>
			";
	}
}
$text .= "</tbody></table></form></div>
	</div>
	</div>
	";



// Customizer   ------------------------------------------
 /*
$text .= "
	<div id='core-infopanel_add' class='f-left' style='width:49%'>
	<div style='border:1px solid silver;margin:10px'>
	<div class='main_caption bevel left'><b>Add</b></div>
	<div class='left block-text'>
    Here we configure additional tabs. A list is shown of plugins that have their own 'infopanel' tab. e_infopanel.php ? ;-)
	</div>
	</div>
	</div>
	";

*/



$text .= "<div class='clear'>&nbsp;</div>";
$text .= "</div>";
//$text .= "</div>";
$ns->tablerender(ADLAN_47." ".ADMINNAME,$emessage->render().$text);
?>
