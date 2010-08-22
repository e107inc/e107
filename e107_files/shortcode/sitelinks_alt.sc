/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

	global $sql, $pref;
	$params = explode('+', $parm);
	if (isset($params[0]) && $params[0] && $params[0] != 'no_icons' && $params[0] != 'default') {
		$icon = $params[0];
	} else {
		$icon = e_IMAGE."generic/".IMODE."/arrow.png";
	}

	function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id=FALSE, $cat_open=FALSE) {
		global $tp;

		$cat_link = (strpos($cat_link, '://') === FALSE && strpos($cat_link, 'mailto:') !== 0 ? e_HTTP.$cat_link : $cat_link);

		if ($cat_open == 4 || $cat_open == 5){
			$dimen = ($cat_open == 4) ? "600,400" : "800,600";
			$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
		} else {
			$href = "href='".$cat_link."'";
		}

		$text = "<a class='menuButton' ".$href." ";
		if ($cat_img != 'no_icons') {
			$text .= "style='background-image: url(".$cat_img."); background-repeat: no-repeat; background-position: 3px 1px; white-space: nowrap' ";
		}
		if ($cat_id) {
			$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onmouseover=\"buttonMouseover(event, '".$cat_id."');\"";
		}
		if ($cat_open == 1){
			$text .= " rel='external' ";
		}
		$text .= ">".$tp->toHTML($cat_title,"","defs, no_hook")."</a>";
		return $text;
	}

	function adnav_main($cat_title, $cat_link, $cat_img, $cat_id=FALSE, $params, $cat_open=FALSE) {
		global $tp;

		$cat_link = (strpos($cat_link, '://') === FALSE) ? e_HTTP.$cat_link : $cat_link;
		$cat_link = $tp->replaceConstants($cat_link, TRUE, TRUE);

		if ($cat_open == 4 || $cat_open == 5){
			$dimen = ($cat_open == 4) ? "600,400" : "800,600";
			$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
		} else {
			$href = "href='".$cat_link."'";
		}

		$text = "<a class='menuItem' ".$href." ";
		if ($cat_id) {
			if (isset($params[2]) && $params[2] == 'link') {
				$text .= "onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
			} else {
				$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
			}
		}
		if ($cat_open == 1){
			$text .= " rel='external' ";
		}
		$text .= ">";
		if ($cat_img != 'no_icons') {
			$text .= "<span class='menuItemBuffer'>".$cat_img."</span>";
		}
		$text .= "<span class='menuItemText'>".$tp->toHTML($cat_title,"","defs, no_hook")."</span>";
		if ($cat_id) {
			$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
		}
		$text .= "</a>";
		return $text;
	}

	$js_file = ($params[1] == 'noclick') ? 'nav_menu_alt.js' : 'nav_menu.js';
	if (file_exists(THEME.$js_file)) {
		$text = "<script type='text/javascript' src='".THEME_ABS.$js_file."'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE_ABS.$js_file."'></script>";
	}
	$text .= "<div class='menuBar' style='width:100%; white-space: nowrap'>";

	// Setup Parent/Child Arrays ---->

 	$link_total = $sql->db_Select("links", "*", "link_class IN (".USERCLASS_LIST.") AND link_category=1 ORDER BY link_order ASC");
	while ($row = $sql->db_Fetch()) {
		if($row['link_parent'] == 0){
			$linklist['head_menu'][] = $row;
			$parents[] = $row['link_id'];
		}else{
			$pid = $row['link_parent'];
			$linklist['sub_'.$pid][] = $row;
		}
	}


	// Loops thru parents.--------->
    global $tp;
    foreach ($linklist['head_menu'] as $lk) {
        $lk['link_url'] = $tp -> replaceConstants($lk['link_url'], TRUE, TRUE);
		if ($params[0] == 'no_icons') {
			$link_icon = 'no_icons';
		} else {
			$link_icon = $lk['link_button'] ? e_IMAGE.'icons/'.$lk['link_button'] : $icon;
		}

		$main_linkid = $lk['link_id'];
		if (isset($linklist['sub_'.$main_linkid])) {  // Has Children.

			$text .= adnav_cat($lk['link_name'], '', $link_icon, 'l_'.$main_linkid);
			$text .= render_sub($linklist, $main_linkid, $params, $icon);

		} else {

	  		// Display Parent only.

        	$text .= adnav_cat($lk['link_name'], $lk['link_url'], $link_icon, FALSE, $lk['link_open']);

		}
	}

	$text .= "</div>";

	return $text;

	function render_sub($linklist, $id, $params, $icon) {
		$text = "<div id='l_".$id."' class='menu' onmouseover=\"menuMouseover(event)\">";
			foreach ($linklist['sub_'.$id] as $sub) {
				// Filter title for backwards compatibility ---->

                if(substr($sub['link_name'],0,8) == "submenu."){
					$tmp = explode(".",$sub['link_name']);
					$subname = $tmp[2];
				}else{
            		$subname = $sub['link_name'];
				}

				// Setup Child Icon --------->

                if (!$sub['link_button'] && $params[0] == 'no_icons') {
					$sub_icon = 'no_icons';
				} else {
					$sub_icon = "<img src='";
					$sub_icon .= ($sub['link_button']) ? e_IMAGE.'icons/'.$sub['link_button'] : $icon;
					$sub_icon .= "' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />";
				}
				if (isset($linklist['sub_'.$sub['link_id']])) {  // Has Children.
					$sub_ids[] = $sub['link_id'];
					$text .= adnav_main($subname, $sub['link_url'], $sub_icon, 'l_'.$sub['link_id'], $params, $sub['link_open']);
				} else {
					$text .= adnav_main($subname, $sub['link_url'], $sub_icon, null, $params, $sub['link_open']);
				}

			}
			$text .= "</div>";

			if(isset($sub_ids) && is_array($sub_ids))
			{
				foreach ($sub_ids as $sub_id) {
					$text .= render_sub($linklist, $sub_id, $params, $icon);
				}
			}

			return $text;
	}