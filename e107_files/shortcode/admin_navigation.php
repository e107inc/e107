<?php
 /*
 * e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
 * $Id: admin_navigation.php,v 1.1 2009-01-09 17:25:50 secretr Exp $
 */


function admin_navigation_shortcode($parm)
{
	if (!ADMIN) return '';

	global $admin_cat, $array_functions, $array_sub_functions, $pref;

	$e107 = &e107::getInstance();
	$sql = &$e107->sql;

	parse_str($parm, $parms);
	$tmpl = strtoupper(varset($parms['tmpl'], 'E_ADMIN_NAVIGATION'));
	global $$tmpl;

	require(e_ADMIN.'ad_links.php');
	require_once(e_HANDLER.'admin_handler.php');




	// MAIN LINK
	$menu_vars = array();
	$menu_vars['main']['text'] = ADLAN_151;
	$menu_vars['main']['link'] = e_ADMIN_ABS.'admin.php';
	$menu_vars['main']['image'] = "<img src='".E_16_NAV_MAIN."' alt='".ADLAN_151."' class='icon S16' />";
	$menu_vars['main']['image_src'] = ADLAN_151;
	$menu_vars['main']['perm'] = '';

	//ALL OTHER ROOT LINKS - temporary data transformation - data structure will be changed in the future and this block will be removed
	$cnt = count($admin_cat['id']);
	for ($i = 1; $i <= $cnt; $i++)
	{
		$id = $admin_cat['id'][$i];
		$menu_vars[$id]['text'] = $admin_cat['title'][$i];
		$menu_vars[$id]['description'] = $admin_cat['title'][$i];
		$menu_vars[$id]['link'] = '#';
		$menu_vars[$id]['image'] = "<img src='".$admin_cat['img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S16' />";
		$menu_vars[$id]['image_large'] = "<img src='".$admin_cat['lrg_img'][$i]."' alt='".$admin_cat['title'][$i]."' class='icon S32' />";
		$menu_vars[$id]['image_src'] = $admin_cat['img'][$i];
		$menu_vars[$id]['image_large_src'] = $admin_cat['lrg_img'][$i];
		$menu_vars[$id]['perm'] = '';
		$menu_vars[$id]['sort'] = $admin_cat['sort'][$i];
	}

	//CORE SUBLINKS
	foreach ($array_functions as $key => $subitem)
	{
			$catid = $admin_cat['id'][$subitem[4]];
			$tmp = array();

			$tmp['text'] = $subitem[1];
			$tmp['description'] = $subitem[2];
			$tmp['link'] = $subitem[0];
			$tmp['image'] = $subitem[5];
			$tmp['image_large'] = $subitem[6];
			$tmp['image_src'] = '';
			$tmp['image_large_src'] = '';
			$tmp['perm'] = $subitem[3];
			$tmp['sub_class'] = '';
			$tmp['sort'] = false;

			if($pref['admin_slidedown_subs'] && varsettrue($array_sub_functions[$key]))
			{
				$tmp['sub_class'] = 'sub';
				foreach ($array_sub_functions[$key] as $subkey => $subsubitem)
				{
					$subid = $key.'_'.$subkey;
					$tmp['sub'][$subid]['text'] = $subsubitem[1];
					$tmp['sub'][$subid]['description'] = $subsubitem[2];
					$tmp['sub'][$subid]['link'] = $subsubitem[0];
					$tmp['sub'][$subid]['image'] = $subsubitem[5];
					$tmp['sub'][$subid]['image_large'] = $subsubitem[6];
					$tmp['sub'][$subid]['image_src'] = '';
					$tmp['sub'][$subid]['image_large_src'] = '';
					$tmp['sub'][$subid]['perm'] = $subsubitem[3];
				}
			}

			if($tmp) $menu_vars[$catid]['sub'][$key] = $tmp;
	}

	//PLUGINS
	require_once(e_HANDLER.'plugin_class.php');
	$plug = new e107plugin;
	$tmp = array();

	if($sql->db_Select("plugin", "*", "plugin_installflag=1 ORDER BY plugin_path"))
	{
		while($row = $sql->db_Fetch())
		{
			//if(getperms('P'.$row['plugin_id']))
			//{
				if($plug->parse_plugin($row['plugin_path']))
				{
					$plug_vars = $plug->plug_vars;
					loadLanFiles($row['plugin_path'], 'admin');
					if($plug_vars['administration']['configFile'])
					{
						$plugpath = isset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/';
						$icon_src = isset($plug_vars['administration']['iconSmall']) ? $plugpath.'/'.$plug_vars['administration']['iconSmall'] : '';
						$icon_src_lrg = isset($plug_vars['administration']['icon']) ? $plugpath.'/'.$plug_vars['administration']['iconSmall'] : '';
						$id = 'plugnav-'.$row['plugin_path'];


						$tmp[$id]['text'] = $e107->tp->toHTML($plug_vars['@attributes']['name'], FALSE, "defs");
						$tmp[$id]['description'] = $plug_vars['description'];
						$tmp[$id]['link'] = e_PLUGIN_ABS.$row['plugin_path'].'/'.$plug_vars['administration']['configFile'];
						$tmp[$id]['image'] = $icon_src ? "<img src='{$icon_src}' alt='{$tmp['text']}' class='icon S16' />" : E_16_PLUGIN;
						$tmp[$id]['image_large'] = $icon_src_lrg ? "<img src='{$icon_src_lrg}' alt='{$tmp['text']}' class='icon S32' />" : $icon_src_lrg;
						$tmp[$id]['image_src'] = $icon_src;
						$tmp[$id]['image_large_src'] = $icon_src_lrg;
						$tmp[$id]['perm'] = 'P'.$row['plugin_id'];
						$tmp[$id]['sub_class'] = '';

						//TODO plugins sublinks support
					}
				}
			//}
		}
		$menu_vars['plugMenu']['sub'] += multiarray_sort($tmp, 'text');

	}

	return e_admin_menu('', '', $menu_vars, $$tmpl, false, false);

}