if (ADMIN) {
	if (!function_exists('admin_latest')) {
		function admin_latest() {
			global $sql, $ns, $pref;
			$active_uploads = $sql -> db_Select("upload", "*", "upload_active=0");
			$reported_posts = $sql -> db_Select("tmp", "*", "tmp_ip='reported_post' ");
			$submitted_news = $sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ");

			$text .= "<div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?sn'>".ADLAN_LAT_2.": $submitted_news</a>" : " ".ADLAN_LAT_2.": 0")."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS.($active_uploads ? " <a href='".e_ADMIN."upload.php'>".ADLAN_LAT_7.": $active_uploads</a>" : " ".ADLAN_LAT_7.": ".$active_uploads)."</div>";

			if ($pref['plug_latest']) {
				$lats = explode(",", $pref['plug_latest']);
				foreach($lats as $lat) {
					if (file_exists(e_PLUGIN.$lat."/latest.php")) {
						include(e_PLUGIN.$lat."/latest.php");
					}
				}
			}

			$messageTypes = array("Reported Forum Post", "Broken Download", "Dev Team Message");
			$queryString = "";
			foreach($messageTypes as $types) {
				$queryString .= " gen_type='$types' OR";
			}
			$queryString = substr($queryString, 0, -3);

			if($amount = $sql -> db_Select("generic", "*", $queryString)) {
				$text .= "<br /><b><a href='".e_ADMIN."message.php'>".ADLAN_LAT_8." [".$amount."]</a></b>";
			}
			return $ns -> tablerender(ADLAN_LAT_1, $text, '', TRUE);	
		}
	}
	
	if ($parm == 'request') {
		if (function_exists('latest_request')) {
			if (latest_request()) {
				return admin_latest();
			}
		}	
	} else {
		return admin_latest();
	}
}