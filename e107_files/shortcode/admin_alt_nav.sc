if (ADMIN) {
	global $sql, $pref;
	require(e_ADMIN.'ad_links.php');
	require_once(e_HANDLER.'admin_handler.php');
	function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		$text = "<a class='menuButton' href='".$cat_link."' style='background-image: url(".$cat_img."); background-repeat: no-repeat;  background-position: 3px 1px' ";
		if ($cat_id) { 
			$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onmouseover=\"buttonMouseover(event, '".$cat_id."');\"";
		}
		$text .= ">".$cat_title."</a>";
		return $text;
	}

	function adnav_main($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		$text = "<a class='menuItem' href='".$cat_link."' ";
		if ($cat_id) { 
			$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
		}
			$text .= "><span class='menuItemBuffer'>".$cat_img."</span><span class='menuItemText'>".$cat_title."</span>";
		if ($cat_id) { 
			$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
		}
			$text .= "</a>";
		return $text;
	}
	if (file_exists(THEME.'nav_menu.js')) {
		$text = "<script type='text/javascript' src='".THEME."nav_menu.js'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE."nav_menu.js'></script>";
	}
	
	$text .= "<div style='width: 100%'><table border='0' cellspacing='0' cellpadding='0' style='width: 100%'>
	<tr><td>
	<div class='menuBar' style='width:100%;'>";

	$text .= adnav_cat('Main', e_ADMIN.'admin.php', E_16_NAV_MAIN);

	for($i=1;$i<7;$i++){
		$ad_tmpi = 0;
		$ad_links_array = asortbyindex($array_functions, 1);
		$nav_main = adnav_cat($admin_cat['title'][$i], '', $admin_cat['img'][$i], $admin_cat['id'][$i]);
		$ad_texti = "<div id='".$admin_cat['id'][$i]."' class='menu' onmouseover=\"menuMouseover(event)\">";
		while(list($key, $nav_extract) = each($ad_links_array)){
			if($nav_extract[4]==$i){
				if(getperms($nav_extract[3])){
					$ad_texti .= adnav_main($nav_extract[1], $nav_extract[0], $nav_extract[5]);
					$ad_tmpi = 1;
				}
			}
		}
		$ad_texti .= "</div>";
		if ($ad_tmpi == 1) {
			$text .= $nav_main;
			$text .= $ad_texti;
		}
	}
	
	$render_plugins = TRUE;
	$plugin_text .= adnav_cat('Plugins', '', E_16_CAT_PLUG, 'plugMenu');
	$plugin_text .= "<div id='plugMenu' class='menu' onmouseover=\"menuMouseover(event)\">";
	if (getperms('Z')) {
		$plugin_text .= adnav_main(ADLAN_98, e_ADMIN.'plugin.php', E_16_PLUGMANAGER);
		$render_plugins = TRUE;
	}
	if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
		while($row = $sql -> db_Fetch()){
			if(getperms('P'.$row['plugin_id'])){
				include(e_PLUGIN.$row['plugin_path']."/plugin.php");
				if($eplug_conffile){
					$plugin_icon = $eplug_icon_small ? "<img src='".e_PLUGIN.$eplug_icon_small."' alt='".$eplug_caption."' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />" : E_16_PLUGIN;
					$plugin_array[ucfirst($eplug_name)] = adnav_main($eplug_name, e_PLUGIN.$row['plugin_path']."/".$eplug_conffile, $plugin_icon);
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
				$render_plugins = TRUE;
			}
		}
		ksort($plugin_array, SORT_STRING);
		foreach ($plugin_array as $plugin_compile) {
			$plugin_text .= $plugin_compile;
		}
		$plugin_text .= "</div>";
	}
	
	if ($render_plugins) {
		$text .= $plugin_text;
	}
	
	$text .= adnav_cat('Docs', '', E_16_NAV_DOCS, 'docsMenu');
	$text .= "<div id='docsMenu' class='menu' onmouseover=\"menuMouseover(event)\">";
	if (!$handle=opendir(e_DOCS.e_LANGUAGE."/")) {
		$handle=opendir(e_DOCS."English/");
	}
	$i=1;
	while ($file = readdir($handle)) {
		if ($file != "." && $file != "..") {
			$text .= adnav_main($file, e_ADMIN."docs.php?".$i, E_16_DOCS);
			$helplist[$i] = $file;
			$i++;
		}
	}
	closedir($handle);
	$text .= "</div>";


	$text .= "</div>
	</td>
	<td style='width: 160px; white-space: nowrap'>
	<div class='menuBar' style='width: 100%'>";
	
	$text .= adnav_cat(ADLAN_53, e_BASE.'index.php', E_16_NAV_LEAV);
	$text .= adnav_cat(ADLAN_46, e_ADMIN.'admin.php?logout', E_16_NAV_LGOT);
	
	$text .= "</div>
	</td>
	</tr>
	</table></div>";

	return $text;
}

