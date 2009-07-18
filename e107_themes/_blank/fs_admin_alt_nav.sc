/*
* e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
* $Id: fs_admin_alt_nav.sc,v 1.5 2009-07-18 11:47:50 marj_nl_fr Exp $
*/

if (!ADMIN) return '';

	global $sql, $pref, $tp;
	parse_str($parm);
	require(e_ADMIN.'ad_links.php');
	require_once(e_HANDLER.'admin_handler.php');
	function adnav_cat_fs($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		$cat_link = ($cat_link ? $cat_link : "javascript:void(0);");
		$text = '<a class="menuButton" href="'.$cat_link.'" style="background-image: url('.$cat_img.'); background-repeat: no-repeat;  background-position: 10px 50%" ';
		if ($cat_id) {
			//$text .= 'onclick="return buttonClick(event, \''.$cat_id.'\');" onmouseover="buttonMouseover(event, \''.$cat_id.'\');"';
		}
		$text .= '>'.$cat_title.'</a>';
		return $text;
	}

	function adnav_main_fs($cat_title, $cat_link, $cat_img, $cat_id=FALSE, $cat_highlight='') {
		$text = "<a class='menuItem ".$cat_highlight."' href='".$cat_link."' ";
		if ($cat_id) {
			//$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
		}
		$text .= ">".$cat_img.$cat_title;
		if ($cat_id) {
			$text .= "";
		}

		$text .= "</a>";
		return $text;
	}
/*	if (file_exists(THEME.'nav_menu.js')) {
		$text = "<script type='text/javascript' src='".THEME_ABS."nav_menu.js'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE_ABS."nav_menu.js'></script>";
	} */
/*
	$text .= "<div style='width: 100%'><table border='0' cellspacing='0' cellpadding='0' style='width: 100%'>
	<tr><td>
	<div class='menuBar' style='width: 100%'>";
*/

	$text .= '
		<div id="nav">
	';
	if ($exit != 'off') {
		$text .= '<ul style="float: right"><li>'.adnav_cat_fs(ADLAN_53, e_BASE.'index.php', E_16_NAV_LEAV).'</li>';
		$text .= '<li>'.adnav_cat_fs(ADLAN_46, e_ADMIN.'admin.php?logout', E_16_NAV_LGOT).'</li></ul>';
	}

	$text .= '
			<ul class="level1" id="nav-links">
	';

	if (defined('FS_ADMIN_START_SEPARATOR') && FS_ADMIN_START_SEPARATOR != false) {
		$text .= "
		<li class='fs-linkSep'>".FS_ADMIN_START_SEPARATOR."</li>";
	}

	$text .= '
		<li>'.adnav_cat_fs(ADLAN_151, e_ADMIN."admin.php", E_16_NAV_MAIN).'</li>
	';

	if (defined('FS_ADMIN_LINK_SEPARATOR')) {
		$text .= "
		<li class='fs-linkSep'>".FS_ADMIN_LINK_SEPARATOR."</li>";
	}

	$sepBr = 1;
	for ($i = 1; $i < 5; $i++) {
		$ad_tmpi = 0;
		$ad_links_array = asortbyindex($array_functions, 1);
		$text .= '<li>';
		$nav_main = adnav_cat_fs($admin_cat['title'][$i], '', $admin_cat['img'][$i], $admin_cat['id'][$i]);
		$ad_texti = '<ul id="'.$admin_cat["id"][$i].'" class="menu">';
		while(list($key, $nav_extract) = each($ad_links_array))
		{
			if($nav_extract[4]==$i)
			{
				if(getperms($nav_extract[3]))
				{
					$ad_texti .= '<li>'.adnav_main_fs($nav_extract[1], $nav_extract[0], $nav_extract[5]).'</li>';
					$ad_tmpi = 1;
				}
			}
		}
		$ad_texti .= '</ul>';
		if ($ad_tmpi == 1)
		{
			$text .= $nav_main;
			$text .= $ad_texti;
		}
		$text .='</li>';

		if (defined('FS_ADMIN_LINK_SEPARATOR')) {
			if ($sepBr < 4 ) {
				$text .= "
				<li class='fs-linkSep'>".FS_ADMIN_LINK_SEPARATOR."</li>";
			}
		}
		$sepBr++;
	}

	$render_plugins = FALSE;
	include_once(e_HANDLER.'plugin_class.php');
	$plug = new e107plugin;
	if($sql -> db_Select("plugin", "*", "plugin_installflag=1 ORDER BY plugin_path"))
	{
		while($row = $sql -> db_Fetch())
		{
			if(getperms('P'.$row['plugin_id']))
			{
				if($plug->parse_plugin($row['plugin_path']))
				{
					$plug_vars = $plug->plug_vars;
					loadLanFiles($row['plugin_path'], 'admin');
					if($plug_vars['administration']['configFile'])
					{
						$plug_vars['@attributes']['name'] = $tp->toHTML($plug_vars['@attributes']['name'], FALSE, "defs");
						$icon_src = (isset($plug_vars['plugin_php']) ? e_PLUGIN_ABS : e_PLUGIN_ABS.$row['plugin_path'].'/') .$plug_vars['administration']['iconSmall'];
						$plugin_icon = $plug_vars['administration']['iconSmall'] ? "<img src='{$icon_src}' alt='".$plug_vars['administration']['caption']."' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />" : E_16_PLUGIN;
						$plugin_array[ucfirst($plug_vars['@attributes']['name'])] = adnav_main_fs($plug_vars['@attributes']['name'], e_PLUGIN.$row['plugin_path']."/".$plug_vars['administration']['configFile'], $plugin_icon);
					}
					$render_plugins = TRUE;
					$active_plugs = TRUE;
				}
			}
		}
		ksort($plugin_array, SORT_STRING);
		$plugs_text = '';
		foreach ($plugin_array as $plugin_compile)
		{
			$plugs_text .= $plugin_compile;
		}
	}

	if (getperms('Z'))
	{
		$pclass_extended = $active_plugs ? 'header' : '';
		$plugin_text = adnav_main_fs(ADLAN_98, e_ADMIN.'plugin.php', E_16_PLUGMANAGER, FALSE, $pclass_extended);
		$render_plugins = TRUE;
	}

	if ($render_plugins) {

		if (defined('FS_ADMIN_LINK_SEPARATOR')) {
			$text .= "
			<li class='fs-linkSep'>".FS_ADMIN_LINK_SEPARATOR."</li>";
		}

		$text .= '<li>';
		$text .= adnav_cat_fs(ADLAN_CL_7, '', E_16_CAT_PLUG, 'plugMenu');
		$text .= "<ul id='plugMenu' class='menu'>";
		$text .= '<li>'.$plugin_text.$plugs_text.'</li>';
		$text .= "</ul>";
		$text .='</li>';
	}

	if (defined('FS_ADMIN_LINK_SEPARATOR')) {
		$text .= "
		<li class='fs-linkSep'>".FS_ADMIN_LINK_SEPARATOR."</li>";
	}

	$text .= '<li>';
	$text .= adnav_cat_fs(ADLAN_CL_8, '', E_16_CAT_ABOUT, 'docsMenu'); //E_16_NAV_DOCS
	$text .= "<ul id='docsMenu' class='menu'>";
	if (!$handle=opendir(e_DOCS.e_LANGUAGE."/"))
	{
		$handle=opendir(e_DOCS."English/");
	}
	$i=1;
	while ($file = readdir($handle))
	{
		if ($file != "." && $file != ".." && $file != "CVS")
		{
			$text .= '<li>'.adnav_main_fs(str_replace("_", " ", $file), e_ADMIN."docs.php?".$i, E_16_DOCS).'</li>';
			$i++;
		}
	}
	closedir($handle);
	$text .= "</ul>";
	$text .='</li>
		</ul>
	';
	$text .= '
		</div>
		<div class="clear"><!-- --></div>
	';

	return $text;
