// $Id: admin_latest.sc 11836 2010-09-30 21:43:10Z e107coders $
//<?

if (ADMIN) {
	global $sql, $pref, $tp;
	parse_str($parm);
	require(e_ADMIN.'ad_links.php');
	require_once(e_HANDLER.'admin_handler.php');
	function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		list($cat_link, $anchor) = explode('#', $cat_link, 2);
		
		$cat_link = ($cat_link ? $cat_link : "#");
		
		$text = "<a class='menuButton' href='".$cat_link."' style='background-image: url(".$cat_img."); background-repeat: no-repeat;  background-position: 0px 5px' ";
		if($cat_id == 'leaveMenu')
		{
			$text .= "onmouseover=\"return buttonClick(event, '".$cat_id."');\" ";
		}
		elseif ($cat_id) {
			// $text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onclick=\"buttonMouseover(event, '".$cat_id."');\"";
			$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" ";
		}
		$text .= ">".$cat_title."</a>";
		return $text;
	}

	function adnav_main($cat_title, $cat_link, $cat_img, $cat_id=FALSE, $cat_highlight='') {
		list($cat_link, $anchor) = explode('#', $cat_link, 2);
		$text = "<a class='menuItem ".$cat_highlight."' href='".$cat_link."' ";
		if ($cat_id) {
			$text .= "onclick=\"return false;\" onclick=\"menuItemMouseover(event, '".$cat_id."');\"";
		}
			$text .= "><span class='menuItemBuffer'>".$cat_img."</span><span class='menuItemText'>".$cat_title."</span>";
		if ($cat_id) {
			$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
		}
			$text .= "</a>\n";
		return $text;
	}
	if (file_exists(THEME.'nav_menu.js')) {
		$text = "<script type='text/javascript' src='".THEME_ABS."nav_menu.js'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE_ABS."nav_menu.js'></script>";
	}

	$text .= "<div style='width: 100%'><table border='0' cellspacing='0' cellpadding='0' style='width: 100%'>
	<tr><td>
	<div class='menuBar' style='width: 100%'>";

	$text .= adnav_cat(ADLAN_151, e_ADMIN.'admin.php', E_16_NAV_MAIN);

	for ($i = 1; $i < 5; $i++) {
		$ad_tmpi = 0;
		$ad_links_array = asortbyindex($array_functions, 1);
		$nav_main = adnav_cat($admin_cat['title'][$i], '', $admin_cat['img'][$i], $admin_cat['id'][$i]);
		$ad_texti = "<div id='".$admin_cat['id'][$i]."' class='menu' onclick=\"menuMouseover(event)\">";
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

	$render_plugins = FALSE;
	if($sql -> db_Select("plugin", "*", "plugin_installflag=1 ORDER BY plugin_path")){
		while($row = $sql -> db_Fetch()){
			if(getperms('P'.$row['plugin_id'])){
				include_once(e_PLUGIN.$row['plugin_path']."/plugin.php");
				if($eplug_conffile){
					$eplug_name = $tp->toHTML($eplug_name,FALSE,"defs");
					$plugin_icon = $eplug_icon_small ? "<img src='".e_PLUGIN_ABS.$eplug_icon_small."' alt='".$eplug_caption."' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />" : E_16_PLUGIN;
					$plugin_array[ucfirst($eplug_name)] = adnav_main($eplug_name, e_PLUGIN.$row['plugin_path']."/".$eplug_conffile, $plugin_icon);
				}
				unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon_small);
				$render_plugins = TRUE;
				$active_plugs = TRUE;
			}
		}
		ksort($plugin_array, SORT_STRING);
		$plugs_text = '';
		foreach ($plugin_array as $plugin_compile) {
			$plugs_text .= $plugin_compile;
		}
	}

	if (getperms('Z')) {
		$pclass_extended = $active_plugs ? 'header' : '';
		$plugin_text = adnav_main(ADLAN_98, e_ADMIN.'plugin.php', E_16_PLUGMANAGER, FALSE, $pclass_extended);
		$render_plugins = TRUE;
	}

	if ($render_plugins) {
		$text .= adnav_cat(ADLAN_CL_7, '', E_16_CAT_PLUG, 'plugMenu');
		$text .= "<div id='plugMenu' class='menu' onclick=\"menuMouseover(event)\">";
		$text .= $plugin_text.$plugs_text;
		$text .= "</div>";
	}

	$text .= adnav_cat(ADLAN_CL_8, '', E_16_NAV_DOCS, 'docsMenu');
	$text .= "<div id='docsMenu' class='menu' onclick=\"menuMouseover(event)\">";
	if (!$handle=opendir(e_DOCS.e_LANGUAGE."/")) {
		$handle=opendir(e_DOCS."English/");
	}
	$i=1;
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && $file != "CVS" && $file != ".svn") {
			$text .= adnav_main(str_replace("_", " ", $file), e_ADMIN_ABS."docs.php?".$i, E_16_DOCS);
			$i++;
		}
	}
	closedir($handle);
	$text .= "</div>";


	$text .= "</div>
	</td>";
	
	require_once(e_HANDLER."sitelinks_class.php");
	$slinks = new sitelinks;
	$slinks->getlinks(1);


	if ($exit != 'off') {
		$text .= "<td style='width: 260px; white-space: nowrap; text-align: right;'>
		<div class='menuBar' style='width: 100%'>";
		
		$text .= adnav_cat(ADLAN_53, SITEURL, E_16_NAV_LEAV, 'leaveMenu');
		$text .= "<div id='leaveMenu' class='menu' onclick=\"menuMouseover(event)\" style='text-align:left'>";
		foreach($slinks->eLinkList['head_menu'] as $k=>$lk)
		{
			$link = (substr($lk['link_url'],0,1)!="/" && substr($lk['link_url'],0,3)!="{e_" && substr($lk['link_url'],0,4)!='http') ? "{e_BASE}".$lk['link_url'] : $lk['link_url'];
			$link = $tp->parseTemplate($link, TRUE); // dynamic URL support via Shortcodes. 
			$img = (substr($lk['link_button'],0,3)=='{e_' || trim($lk['link_button'])=='') ? $lk['link_button'] : "{e_IMAGE}icons/".$lk['link_button'];
			$imgTag = ($img) ? "<img src='".$img."' alt='".$tp->toAttribute($lk['link_name'])."' style='border: 0px none; vertical-align: bottom; width: 16px; height: 16px;' />" : "";
			$text .= adnav_main($tp->toHtml($lk['link_name'],'','defs'), $tp->replaceConstants($link,'full'), $tp->replaceConstants($imgTag,'full'));	
		}

		$text .= "</div>";

	//	$text .= adnav_cat(ADLAN_53, e_HTTP.'index.php', E_16_NAV_LEAV);
		$text .= adnav_cat(ADLAN_46, e_ADMIN_ABS.'admin.php?logout', E_16_NAV_LGOT);

		$text .= "</div>
		</td>";
	}

	$text .= "</tr>
	</table>
	</div>";

	return $text;
}
