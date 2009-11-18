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
 * $Source: /cvs_backup/e107_0.8/e107_admin/admin.php,v $
 * $Revision: 1.25 $
 * $Date: 2009-11-18 01:04:24 $
 * $Author: e107coders $
 */

require_once('../class2.php');

//TODO - marj prepare language reorganisation
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'main';
require_once('auth.php');
require_once(e_HANDLER.'admin_handler.php');
require_once(e_HANDLER.'upload_handler.php');

require_once (e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

if (!isset($pref['adminstyle'])) $pref['adminstyle'] = 'classis';		// Shouldn't be needed - but just in case


// --- check for htmlarea.
if (is_dir(e_ADMIN.'htmlarea') || is_dir(e_HANDLER.'htmlarea'))
{
	/*$text = ADLAN_ERR_2."<br /><br />
	<div style='text-align:center'>".$HANDLERS_DIRECTORY."htmlarea/<br />".$ADMIN_DIRECTORY."htmlarea/</div>";
	$ns -> tablerender(ADLAN_ERR_1, $text);*/
	$emessage->add($HANDLERS_DIRECTORY."htmlarea/<br />".$ADMIN_DIRECTORY."htmlarea/", E_MESSAGE_WARNING);
}

/* Not used in 0.8
// check for old modules.
if(getperms('0') && isset($pref['modules']) && $pref['modules'] && $sql->db_Field("plugin",5) == "plugin_addons")
{
	$mods=explode(",", $pref['modules']);
	$thef = "e_module.php";
	foreach ($mods as $mod)
	{
		if (is_readable(e_PLUGIN."{$mod}/module.php"))
		{
			$mod_found[] = e_PLUGIN."{$mod}/module.php";
		}
	}

	if($mod_found)
	{
    	$text = ADLAN_ERR_5." <b>".$thef."</b>:<br /><br /><ul>";
		foreach($mod_found as $val){
			$text .= "<li>".str_replace("../","",$val)."</li>\n";
		}
		$text .="</ul><br />
		<form method='post' action='".e_ADMIN."db.php' id='upd'>
		<a href='#' onclick=\"document.getElementById('upd').submit()\">".ADLAN_ERR_6."</a>
		<input type='hidden' name='plugin_scan' value='1' />
		</form>";
		$ns -> tablerender(ADLAN_ERR_4,$text);
	}
}
*/

// check for file-types;
$allowed_types = get_filetypes();			// Get allowed types according to filetypes.xml or filetypes.php
if (count($allowed_types) == 0)
{
	$allowed_types = array('zip' => 1, 'gz' => 1, 'jpg' => 1, 'png' => 1, 'gif' => 1);
	$emessage->add("Setting default filetypes: ".implode(', ',array_keys($allowed_types)), E_MESSAGE_INFO);

}

//echo "Allowed filetypes = ".implode(', ',array_keys($allowed_types)).'<br />';
// avatar check.
$public = array(e_UPLOAD, e_UPLOAD.'avatars');
$exceptions = array(".","..","/","CVS","avatars","Thumbs.db",".htaccess","php.ini",".cvsignore");

//TODO use $file-class to grab list and perform this check. 
foreach ($public as $dir)
{
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (is_dir($dir."/".$file) == FALSE && !in_array($file,$exceptions))
				{
					$fext = substr(strrchr($file, "."), 1);
					if (!array_key_exists(strtolower($fext),$allowed_types) )
					{
						if ($file == 'index.html' || $file == "null.txt")
						{
							if (filesize($dir.'/'.$file))
							{
								$potential[] = str_replace('../', '', $dir).'/'.$file;
							}
						}
						else
						{
							$potential[] = str_replace('../', '', $dir).'/'.$file;
						}
					}
				}
			}
			closedir($dh);
		}
	}
}

if (isset($potential))
{
	//$text = ADLAN_ERR_3."<br /><br />";
	$emessage->add(ADLAN_ERR_3, E_MESSAGE_WARNING);
	$text = '<ul>';
	foreach ($potential as $p_file)
	{
		$text .= '<li>'.$p_file.'</li>';
	}
	$emessage->add($text, E_MESSAGE_WARNING);
	//$ns -> tablerender(ADLAN_ERR_1, $text);
}


// ---------------------------------------------------------



// auto db update
if ('0' == ADMINPERMS)
{
	require_once(e_ADMIN.'update_routines.php');
	update_check();
}
// end auto db update

/*
if (e_QUERY == 'purge' && getperms('0'))
{
	$admin_log->purge_log_events(false);
}
*/

$td = 1;
if(!defined("ADLINK_COLS"))
{
	define("ADLINK_COLS",5);
}


function render_links($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE)
{
	global $td,$tp;
	$text = '';
	if (getperms($perms))
	{
		$description = strip_tags($description);
		if ($mode == 'adminb')
		{
			$text = "<tr><td class='forumheader3'>
				<div class='td' style='text-align:left; vertical-align:top; width:100%'
				onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">
				".$icon." <b>".$title."</b> ".($description ? "[ <span class='field-help'>".$description."</span> ]" : "")."</div></td></tr>";
		}
		else
		{

			if($mode != "div")
			{
				if ($td == (ADLINK_COLS+1))
				{
					$text .= '</tr>';
					$td = 1;
				}
				if ($td == 1)
				{
					$text .= '<tr>';
				}
			}
			if ($mode == 'default')
			{
				$text .= "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap'
					onmouseover=\"eover(this, 'forumheader5')\" onmouseout=\"eover(this, 'td')\" onclick=\"document.location.href='".$link."'\">".$icon." ".$tp->toHTML($title,FALSE,"defs, emotes_off")."</td>";
			}
			elseif ($mode == 'classis')
			{
				$text .= "<td style='text-align:center; vertical-align:top; width:20%'><a class='core-mainpanel-link-icon' href='".$link."' title='{$description}'>".$icon."</a><br />
					<a class='core-mainpanel-link-text' href='".$link."' title='{$description}'><b>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</b></a><br /><br /></td>";
			}
			elseif ($mode == 'beginner')
			{
                $text .= "<td style='text-align:center; vertical-align:top; width:20%' ><a class='core-mainpanel-link-icon' href='".$link."' >".$icon."</a>
					<div style='padding:5px'>
					<a class='core-mainpanel-link-text' href='".$link."' title='".$description."'><b>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</b></a></div><br /><br /><br /></td>";
			}
			elseif($mode == "div")
			{
                $text .= "<div class='core-mainpanel-block'><a class='core-mainpanel-link-icon' href='".$link."' title='{$description}'>".$icon."</a><br />
					<a class='core-mainpanel-link-text' href='".$link."' title='{$description}'>".$tp->toHTML($title,FALSE,"defs, emotes_off")."</a>
					</div>";
			}
			$td++;
		}
	}
	return $text;
}


function render_clean()
{
	global $td;
	$text = "";
	while ($td <= ADLINK_COLS)
	{
		$text .= "<td class='td' style='width:20%;'></td>";
		$td++;
	}
	$text .= "</tr>";
	$td = 1;
	return $text;
}


$newarray = asortbyindex($array_functions, 1);
$array_functions_assoc = convert_core_icons($newarray);



function convert_core_icons($newarray)  // Put core button array in the same format as plugin button array.
{
    foreach($newarray as $key=>$val)
	{
		if(varset($val[0]))
		{
			$key = "e-".basename($val[0],".php");
			$val['icon'] = $val[5];
			$val['icon_32'] = $val[6];
			$val['title'] = $val[1];
			$val['link'] = $val[0];
			$val['caption'] = $val['2'];
			$val['perms'] = $val['3'];
			$array_functions_assoc[$key] = $val;
		}
	}

    return $array_functions_assoc;
}



require_once(e_ADMIN.'includes/'.$pref['adminstyle'].'.php');

function admin_info()
{
	global $tp;

	$width = (getperms('0')) ? "33%" : "50%";

	$ADMIN_INFO_TEMPLATE = "
	<div style='text-align:center'>
		<table style='width: 100%; border-collapse:collapse; border-spacing:0px;'>
		<tr>
			<td style='width: ".$width."; vertical-align: top'>
			{ADMIN_STATUS}
			</td>
			<td style='width:".$width."; vertical-align: top'>
			{ADMIN_LATEST}
			</td>";

    	if(getperms('0'))
		{
			$ADMIN_INFO_TEMPLATE .= "
			<td style='width:".$width."; vertical-align: top'>{ADMIN_LOG}</td>";
    	}

   	$ADMIN_INFO_TEMPLATE .= "
		</tr></table></div>";

	return $tp->parseTemplate($ADMIN_INFO_TEMPLATE);
}

function status_request()
{
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade' || $pref['adminstyle'] == 'beginner' || $pref['adminstyle'] == 'tabbed') {
		return TRUE;
	} else {
		return FALSE;
	}
}


function latest_request()
{
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade' || $pref['adminstyle'] == 'beginner' || $pref['adminstyle'] == 'tabbed') {
		return TRUE;
	} else {
		return FALSE;
	}
}

function log_request()
{
	global $pref;
	if ($pref['adminstyle'] == 'classis' || $pref['adminstyle'] == 'cascade'|| $pref['adminstyle'] == 'beginner' || $pref['adminstyle'] == 'tabbed') {
		return TRUE;
	} else {
		return FALSE;
	}
}


// Function renders all the plugin links according to the required icon size and layout style
// - common to the various admin layouts.
function getPluginLinks($iconSize = E_16_PLUGMANAGER, $linkStyle = 'adminb')
{

	global $sql, $tp;
	
	$plug_id = array();
	e107::getDb()->db_Select("plugin", "*", "plugin_installflag = 1"); // Grab plugin IDs. 
	while ($row = e107::getDb()->db_Fetch())
	{
		$pth = $row['plugin_path'];
		$plug_id[$pth] = $row['plugin_id'];
	}
	
	$pref = e107::getConfig('core')->getPref();
	
	$text = render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", $iconSize, $linkStyle);

	$plugs = e107::getObject('e107plugin');

	foreach($pref['plug_installed'] as $plug=>$vers)
	{
		$plugs->parse_plugin($plug);

		$plugin_path = $plug;
		$name = $plugs->plug_vars['@attributes']['name'];
		
/*		echo "<h1>".$name." ($plug)</h1>";
		print_a($plugs->plug_vars);*/

		foreach($plugs->plug_vars['adminLinks']['link'] as $tag)
		{
			if(varset($tag['@attributes']['primary']) !='true')
			{
				continue;
			}
			loadLanFiles($plugin_path, 'admin');
			
			$att = $tag['@attributes'];

	
			$eplug_name 		= $tp->toHTML($name,FALSE,"defs, emotes_off");
			$eplug_conffile 	= $att['url'];
			$eplug_icon_small 	= $plugin_path.'/'.$att['iconSmall'];
			$eplug_icon 		= $plugin_path.'/'.$att['icon'];
			$eplug_caption 		= str_replace("'", '', $tp->toHTML($att['description'], FALSE, 'defs, emotes_off'));
			
			if (varset($eplug_conffile))
			{
				$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
				$plugin_icon = $eplug_icon_small ? "<img class='icon S16' src='".e_PLUGIN.$eplug_icon_small."' alt=''  />" : E_16_PLUGIN;
				$plugin_icon_32 = $eplug_icon ? "<img class='icon S32' src='".e_PLUGIN.$eplug_icon."' alt=''  />" : E_32_PLUGIN;
				$plugin_array['p-'.$plugin_path] = array('link' => e_PLUGIN.$plugin_path."/".$eplug_conffile, 'title' => $eplug_name, 'caption' => $eplug_caption, 'perms' => "P".$plug_id[$plugin_path], 'icon' => $plugin_icon, 'icon_32' => $plugin_icon_32);
			}
		}
	}	

	
//	print_a($plugs->plug_vars['adminLinks']['link']);
	
	


/*	echo "hello there";

 	$xml = e107::getXml();
	$xml->filter = array('@attributes' => FALSE,'description'=>FALSE,'administration' => FALSE);	// .. and they're all going to need the same filter

	if ($sql->db_Select("plugin", "*", "plugin_installflag=1"))
	{
		while ($row = $sql->db_Fetch())
		{
			extract($row);		//  plugin_id int(10) unsigned NOT NULL auto_increment,
								//	plugin_name varchar(100) NOT NULL default '',
								//	plugin_version varchar(10) NOT NULL default '',
								//	plugin_path varchar(100) NOT NULL default '',
								//	plugin_installflag tinyint(1) unsigned NOT NULL default '0',
								//	plugin_addons text NOT NULL,

			if (is_readable(e_PLUGIN.$plugin_path."/plugin.xml"))
			{
				$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
				if ($readFile === FALSE)
				{
					echo 'Error in file: '.e_PLUGIN.$plugin_path.'/plugin.xml'.'<br />';
				}
				else
				{
					loadLanFiles($plugin_path, 'admin');
					$eplug_name 		= $tp->toHTML($readFile['@attributes']['name'],FALSE,"defs, emotes_off");
					$eplug_conffile 	= $readFile['administration']['configFile'];
					$eplug_icon_small 	= $plugin_path.'/'.$readFile['administration']['iconSmall'];
					$eplug_icon 		= $plugin_path.'/'.$readFile['administration']['icon'];
					$eplug_caption 		= str_replace("'", '', $tp->toHTML($readFile['description'], FALSE, 'defs, emotes_off'));
				}
			}
			elseif (is_readable(e_PLUGIN.$plugin_path."/plugin.php"))
			{
				include(e_PLUGIN.$plugin_path."/plugin.php");
			}
			if (varset($eplug_conffile))
			{
				$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs, emotes_off");
				$plugin_icon = $eplug_icon_small ? "<img class='icon S16' src='".e_PLUGIN.$eplug_icon_small."' alt=''  />" : E_16_PLUGIN;
				$plugin_icon_32 = $eplug_icon ? "<img class='icon S32' src='".e_PLUGIN.$eplug_icon."' alt=''  />" : E_32_PLUGIN;

				$plugin_array['p-'.$plugin_path] = array('link' => e_PLUGIN.$plugin_path."/".$eplug_conffile, 'title' => $eplug_name, 'caption' => $eplug_caption, 'perms' => "P".$plugin_id, 'icon' => $plugin_icon, 'icon_32' => $plugin_icon_32);
			}
			unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
		}
	}
	else
	{
		$plugin_array = array();	
	}
*/
	ksort($plugin_array, SORT_STRING);  // To FIX, without changing the current key format, sort by 'title'

	if($linkStyle == "array")
	{
       	return $plugin_array;
	}

	foreach ($plugin_array as $plug_key => $plug_value)
	{
		$the_icon =  ($iconSize == E_16_PLUGMANAGER) ?  $plug_value['icon'] : $plug_value['icon_32'];
		$text .= render_links($plug_value['link'], $plug_value['title'], $plug_value['caption'], $plug_value['perms'], $the_icon, $linkStyle);
	}
	return $text;
}


require_once("footer.php");

?>
