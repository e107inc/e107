<?php
/*
+---------------------------------------------------------------+
| e107 website system
| /classes/emailprint_class.php
|
| ©Steve Dunstan 2001-2002
| http://e107.org
| jalist@e107.org
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
	
class banner {
	function getBanner($bannerquery) {
		global $sql, $menu_pref;
		 
		$query = " (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased) ".$bannerquery." ";
		 
		$sql->db_Select("banner", "*", $query);
		while ($row = $sql->db_Fetch()) {
			extract($row);
			 
			if (!$menu_pref['banner_visibilitytype'] || $menu_pref['banner_visibilitytype'] == "1") {
				//use individual banner visibility
				if (preg_match("#\^#", $banner_campaign)) {
					$campaignsplit = explode("^", $banner_campaign);
					$banner_campaign = $campaignsplit[0];
					$banner_pagescheck = $campaignsplit[1];
				} else {
					$banner_pagescheck = "";
				}
				$checkshowbannervalue = $banner_pagescheck;
				 
			} elseif($menu_pref['banner_visibilitytype'] == "2") {
				//use campaign visibility
				if (preg_match("#\^#", $banner_campaign)) {
					$campaignsplit = explode("^", $banner_campaign);
					$banner_campaign = $campaignsplit[0];
				}
				$pagescheck = "banner_pages-".$banner_campaign;
				$checkshowbannervalue = ($menu_pref[$pagescheck] ? $menu_pref[$pagescheck] : "");
				 
			}
			 
			$classcheck = "banner_class-".$banner_campaign;
			if (check_class($menu_pref[$classcheck])) {
				//class check for campaign
				if (check_class($banner_active)) {
					//class check for banner
					$showbanneronthispage = $this->checkShowBanner($checkshowbannervalue);
					if ($showbanneronthispage == TRUE) {
						$array_bannerid[] = $banner_id;
					}
				}
			}
		}
		return $array_bannerid;
	}
	 
	function checkShowBanner($pagescheck) {
		 
		$show_menu = TRUE;
		if ($pagescheck && $pagescheck != "") {
			list($listtype, $listpages) = explode("-", $pagescheck);
			$pagelist = explode("|", $listpages);
			$check_url = e_SELF."?".e_QUERY;
			if ($listtype == '1') {
				//show menu
				$show_menu = FALSE;
				foreach($pagelist as $p) {
					if (strpos($check_url, $p)) {
						$show_menu = TRUE;
					} else {
						if (strpos($p, "?")) {
							$tmpp = explode("?", $p);
							$tmpc = explode("?", $check_url);
							if (e_PAGE == $tmpp[0]) {
								if (strpos($tmpc[1], $tmpp[1])) {
									$show_menu = TRUE;
								}
							}
						}
					}
				}
			}
			if ($listtype == '2') {
				//hide menu
				$show_menu = TRUE;
				foreach($pagelist as $p) {
					if (strpos($check_url, $p)) {
						$show_menu = FALSE;
					}
				}
			}
		}
		return $show_menu;
	}
}
	
?>