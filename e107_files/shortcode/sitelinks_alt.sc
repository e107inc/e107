	global $sql, $pref;
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
	
	function getLinks($extra='1') {
		global $sql;
		$ret=array();
		if ($sql -> db_Select('links','*',$extra)) {
			while($row = $sql -> db_Fetch()) {
				$ret[]=$row;
			}
		}
		return $ret;
	}
	
	if (file_exists(THEME.'admin_nav.js')) {
		$text = "<script type='text/javascript' src='".THEME."admin_nav.js'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE."admin_nav.js'></script>";
	}
	$text .= "<div class='menuBar' style='width:100%;'>";

	$main_links = getLinks("link_category='1' && link_name NOT REGEXP('submenu') ORDER BY link_order ASC");
	$sub_links = getLinks("link_category='1' && link_name REGEXP('submenu') ORDER BY link_order ASC");

	foreach ($sub_links as $sub) {
		if (check_class($sub['link_class'])) {
			$sub_array = explode('.', $sub['link_name']);
			$sub_comp[$sub_array[1]]['link_name'][] = $sub_array[2];
			$sub_comp[$sub_array[1]]['link_url'][] = $sub['link_url'];
			$sub_comp[$sub_array[1]]['link_button'][] = $sub['link_button'];
		}
	}

	foreach ($main_links as $links_exp) {
		$link_icon = $links_exp['link_button'] ? e_IMAGE.'link_icons/'.$links_exp['link_button'] : e_IMAGE.'e107_icon_16.png';
		if (check_class($links_exp['link_class'])) {
		if ($sub_comp[$links_exp['link_name']]) {
			$text .= adnav_cat($links_exp['link_name'], '', $link_icon, $links_exp['link_name']);
			$text .= "<div id='".$links_exp['link_name']."' class='menu' onmouseover=\"menuMouseover(event)\">";
			foreach ($sub_comp[$links_exp['link_name']]['link_name'] as $sub_comp_key => $sub_comp_value) {
				$sub_link_icon = $sub_comp[$links_exp['link_name']]['link_button'][$sub_comp_key] ? e_IMAGE.'link_icons/'.$sub_comp[$links_exp['link_name']]['link_button'][$sub_comp_key] : e_IMAGE.'e107_icon_16.png';
				$text .= adnav_main($sub_comp_value, $sub_comp[$links_exp['link_name']]['link_url'][$sub_comp_key], "<img src='".$sub_link_icon."' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />");
			}
			$text .= "</div>";
		} else {
			$text .= adnav_cat($links_exp['link_name'], $links_exp['link_url'], $link_icon);
		}
		}
	}

	$text .= "</div>";

	return $text;

