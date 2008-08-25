/*
* e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
* $Id: admin_nav.sc,v 1.2 2008-08-25 13:34:45 e107steved Exp $
*/
if (ADMIN) 
{
	global $ns, $pref, $e107_plug, $array_functions, $tp;
	if (strstr(e_SELF, "/admin.php")) 
	{
		$active_page = 'x';
	} 
	else 
	{
		$active_page = time();
	}
	$e107_var['x']['text']=ADLAN_52;
	$e107_var['x']['link']=e_ADMIN.'admin.php';
	$e107_var['y']['text']=ADLAN_53;
	$e107_var['y']['link']=e_BASE."index.php";

	$text .= show_admin_menu("",$active_page,$e107_var);

	unset($e107_var);
	foreach ($array_functions as $links_key => $links_value) 
	{
		$e107_var[$links_key]['text'] = $links_value[1];
		$e107_var[$links_key]['link'] = $links_value[0];
	}
	$text .= show_admin_menu(ADLAN_93, time(), $e107_var, FALSE, TRUE, TRUE);
	unset($e107_var);
	// Plugin links menu

	require_once(e_HANDLER.'xml_class.php');
	$xml = new xmlClass;				// We're going to have some plugins with plugin.xml files, surely? So create XML object now
	$xml->filter = array('name' => FALSE, 'administration' => FALSE);	// .. and they're all going to need the same filter

	$nav_sql = new db;
	if ($nav_sql -> db_Select("plugin", "*", "plugin_installflag=1")) 
	{
		//Link Plugin Manager
		$e107_var['x']['text'] = "<b>".ADLAN_98."</b>";
		$e107_var['x']['link'] = e_ADMIN."plugin.php";
		$e107_var['x']['perm'] = "P";

		while($rowplug = $nav_sql -> db_Fetch()) 
		{
			extract($rowplug);
			$e107_plug[$rowplug[1]] = $rowplug[3];
			if (is_readable(e_PLUGIN.$plugin_path."/plugin.xml"))
			{
				$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
				include_lan_admin(e_PLUGIN.$plugin_path.'/');
				$eplug_caption 		= $tp->toHTML($readFile['name'],FALSE,"defs, emotes_off");
				$eplug_conffile 	= $readFile['administration']['configFile'];
			}
			elseif (is_readable(e_PLUGIN.$plugin_path."/plugin.php"))
			{
				include(e_PLUGIN.$plugin_path."/plugin.php");
			}

			// Links Plugins
			if ($eplug_conffile) {
				$e107_var['x'.$plugin_id]['text'] = $eplug_caption;
				$e107_var['x'.$plugin_id]['link'] = e_PLUGIN.$plugin_path."/".$eplug_conffile;
				$e107_var['x'.$plugin_id]['perm'] = "P".$plugin_id;
			}
			unset($eplug_conffile, $eplug_name, $eplug_caption);
		}

		$text .= show_admin_menu(ADLAN_95, time(), $e107_var, FALSE, TRUE, TRUE);
		unset($e107_var);
	}
	unset($e107_var);
	$e107_var['x']['text']=ADLAN_46;
	$e107_var['x']['link']=e_ADMIN."admin.php?logout";
	$text .= show_admin_menu("",$act,$e107_var);
	return $ns -> tablerender(LAN_head_1, $text, array('id' => 'admin_nav', 'style' => 'button_menu'), TRUE);
}

