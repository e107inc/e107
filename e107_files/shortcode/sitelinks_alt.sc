/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/sitelinks_alt.sc,v $
|     $Revision: 1.22 $
|     $Date: 2005-08-11 22:12:20 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

	global $sql, $pref;
	if (isset($parm) && $parm && $parm != 'no_icons') {
		$icon = $parm;
	} else {
		$icon = e_IMAGE."generic/".IMODE."/arrow.png";
	}

	function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		global $tp;
		
		$link_server = (preg_match('#(http:|mailto:|ftp:|irc:)#', $cat_link)) ? '' : e_HTTP;

		$text = "<a class='menuButton' href='".$link_server.$cat_link."' ";
		if ($cat_img != 'no_icons') {
			$text .= "style='background-image: url(".$cat_img."); background-repeat: no-repeat; background-position: 3px 1px; white-space: nowrap' ";
		}
		if ($cat_id) {
			$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onmouseover=\"buttonMouseover(event, '".$cat_id."');\"";
		}
		$text .= ">".$tp->toHTML($cat_title,"","defs")."</a>";
		return $text;
	}

	function adnav_main($cat_title, $cat_link, $cat_img, $cat_id=FALSE) {
		global $tp;
		
		$link_server = (preg_match('#(http:|mailto:|ftp:|irc:)#', $cat_link)) ? '' : e_HTTP;
		
		$text = "<a class='menuItem' href='".$link_server.$cat_link."' ";
		if ($cat_id) {
			$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
		}
		$text .= ">";
		if ($cat_img != 'no_icons') {
			$text .= "<span class='menuItemBuffer'>".$cat_img."</span>";
		}
		$text .= "<span class='menuItemText'>".$tp->toHTML($cat_title,"","defs")."</span>";
		if ($cat_id) {
			$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
		}
		$text .= "</a>";
		return $text;
	}


	if (file_exists(THEME.'nav_menu.js')) {
		$text = "<script type='text/javascript' src='".THEME_ABS."nav_menu.js'></script>";
	} else {
		$text = "<script type='text/javascript' src='".e_FILE_ABS."nav_menu.js'></script>";
	}
	$text .= "<div class='menuBar' style='width:100%; white-space: nowrap'>";

// Setup Parent/Child Arrays ---->

 	$link_total = $sql->db_Select("links", "*", "link_class IN (".USERCLASS_LIST.") AND link_category=1 ORDER BY link_order ASC");
	while ($row = $sql->db_Fetch()) {
		if($row['link_parent'] ==0){
			$linklist['head_menu'][] = $row;
			$parents[] = $row['link_id'];
		}else{
			$pid = $row['link_parent'];
			$linklist['sub_'.$pid][] = $row;
		}
	}

// Loops thru parents.--------->

    foreach ($linklist['head_menu'] as $lk) {

		if ($parm == 'no_icons') {
			$link_icon = 'no_icons';
		} else {
			$link_icon = $lk['link_button'] ? e_IMAGE.'icons/'.$lk['link_button'] : $icon;
		}

		$main_linkid = $lk['link_id'];
		if($linklist['sub_'.$main_linkid]){  // Has Children.

			$text .= adnav_cat($lk['link_name'], '', $link_icon, 'l_'.$main_linkid);
			$text .= "<div id='l_".$main_linkid."' class='menu' onmouseover=\"menuMouseover(event)\">";
			foreach ($linklist['sub_'.$main_linkid] as $sub) {

	// Filter title for backwards compatibility ---->

                if(substr($sub['link_name'],0,8) == "submenu."){
					$tmp = explode(".",$sub['link_name']);
					$subname = $tmp[2];
				}else{
            		$subname = $sub['link_name'];
				}

	// Setup Child Icon --------->

                if (!$sub['link_button'] && $parm == 'no_icons') {
					$sub_icon = 'no_icons';
				} else {
					$sub_icon = "<img src='";
					$sub_icon .= ($sub['link_button']) ? e_IMAGE.'icons/'.$sub['link_button'] : $icon;
					$sub_icon .= "' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />";
				}

				$text .= adnav_main($subname, $sub['link_url'], $sub_icon);
			}
			$text .= "</div>";

		}else{

	  // Display Parent only.

        	$text .= adnav_cat($lk['link_name'], $lk['link_url'], $link_icon);

		}
	}

	$text .= "</div>";

	return $text;
