if (ADMIN) {
	if (!function_exists('admin_status')) {
		function admin_status() {
			global $sql, $ns, $pref;
			$members = $sql -> db_Count("user");
			$unverified = $sql -> db_Count("user", "(*)", "WHERE user_ban=2");
			$banned = $sql -> db_Count("user", "(*)", "WHERE user_ban=1");
			$chatbox_posts = $sql -> db_Count("chatbox");
			$comments = $sql -> db_Count("comments");

			$text = "<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_110.": ".$members."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_111.": ".$unverified."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_BANLIST." ".ADLAN_112.": ".$banned."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_COMMENT." ".ADLAN_114.": ".$comments."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_CHAT." ".ADLAN_115.": ".$chatbox_posts."</div>";

			if ($pref['plug_status']) {
				$stas = explode(",", $pref['plug_status']);
				foreach($stas as $sta) {
					if (file_exists(e_PLUGIN.$sta."/status.php")) {
						include(e_PLUGIN.$sta."/status.php");
					}
				}
			}

			return $ns -> tablerender(ADLAN_134, $text, '', TRUE);	
		}
	}
	
	if ($parm == 'request') {
		if (function_exists('status_request')) {
			if (status_request()) {
				return admin_status();
			}
		}	
	} else {
		return admin_status();
	}
}