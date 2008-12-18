
/*
* e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
* $Id: admin_nav.sc,v 1.7 2008-12-18 16:55:46 secretr Exp $
*/
if (ADMIN)
{
	global $ns, $pref, $array_functions, $tp;
	$e107_var = array();

	if (strstr(e_SELF, "/admin.php"))
	{
		$active_page = 'x';
	}
	else
	{
		$active_page = time();
	}
	$e107_var['x']['text'] = ADLAN_52;
	$e107_var['x']['link'] = e_ADMIN.'admin.php';
	$e107_var['y']['text'] = ADLAN_53;
	$e107_var['y']['link'] = e_BASE."index.php";

	//$text .= show_admin_menu("",$active_page,$e107_var);
	$e107_var['afuncs']['text'] = ADLAN_93;
	$e107_var['afuncs']['link'] = '';

	/* SUBLINKS */
	$tmp = array();
	foreach ($array_functions as $links_key => $links_value)
	{
		$tmp[$links_key]['text'] = $links_value[1];
		$tmp[$links_key]['link'] = $links_value[0];
	}
	$e107_var['afuncs']['sub'] = $tmp;
	/* SUBLINKS END */

	// Plugin links menu
	require_once(e_HANDLER.'xml_class.php');
	$xml = new xmlClass;				// We're going to have some plugins with plugin.xml files, surely? So create XML object now
	$xml->filter = array('@attributes' => FALSE, 'administration' => FALSE);	// .. and they're all going to need the same filter

	$nav_sql = new db;
	if ($nav_sql -> db_Select("plugin", "*", "plugin_installflag=1"))
	{
		$tmp = array();
		$e107_var['plugs']['text'] = ADLAN_95;
		$e107_var['plugs']['link'] = '';

		/* SUBLINKS */
		//Link Plugin Manager
		$tmp['plugm']['text'] = "<strong>".ADLAN_98."</strong>";
		$tmp['plugm']['link'] = e_ADMIN."plugin.php";
		$tmp['plugm']['perm'] = "P";

		while($rowplug = $nav_sql -> db_Fetch())
		{
			$plugin_id = $rowplug['plugin_id'];
			$plugin_path = $rowplug['plugin_path'];
			if (is_readable(e_PLUGIN.$plugin_path."/plugin.xml"))
			{
				$readFile = $xml->loadXMLfile(e_PLUGIN.$plugin_path.'/plugin.xml', true, true);
				loadLanFiles($plugin_path, 'admin');
				$eplug_caption 	= $tp->toHTML($readFile['@attributes']['name'],FALSE,"defs, emotes_off");
				$eplug_conffile = $readFile['administration']['configFile'];
			}
			elseif (is_readable(e_PLUGIN.$plugin_path."/plugin.php"))
			{
				include(e_PLUGIN.$plugin_path."/plugin.php");
			}

			// Links Plugins
			if ($eplug_conffile)
			{
				$tmp['plug_'.$plugin_id]['text'] = $eplug_caption;
				$tmp['plug_'.$plugin_id]['link'] = e_PLUGIN.$plugin_path."/".$eplug_conffile;
				$tmp['plug_'.$plugin_id]['perm'] = "P".$plugin_id;
			}
			unset($eplug_conffile, $eplug_name, $eplug_caption);
		}
		$e107_var['plugm']['sub'] = $tmp;
		$e107_var['plugm']['sort'] = true;
		/* SUBLINKS END */
		//$text .= show_admin_menu(ADLAN_95, time(), $e107_var, FALSE, TRUE, TRUE);
		unset($tmp);
	}

	$e107_var['lout']['text']=ADLAN_46;
	$e107_var['lout']['link']=e_ADMIN."admin.php?logout";

	$text = e_admin_menu('', '', $e107_var);
	return $ns -> tablerender(LAN_head_1, $text, array('id' => 'admin_nav', 'style' => 'button_menu'), TRUE);
}

