if (ADMIN) {
	if (!function_exists('admin_latest')) {
		function admin_latest() {
			global $sql, $ns, $pref;

	   		$active_uploads = $sql -> db_Count("upload", "(*)", "WHERE upload_active = '0' ");
			$submitted_news = $sql -> db_Count("submitnews", "(*)", "WHERE submitnews_auth = '0' ");

			$text = "<div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN_ABS."newspost.php?sn'>".ADLAN_LAT_2.": $submitted_news</a>" : " ".ADLAN_LAT_2.": 0")."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS.($active_uploads ? " <a href='".e_ADMIN_ABS."upload.php'>".ADLAN_LAT_7.": $active_uploads</a>" : " ".ADLAN_LAT_7.": ".$active_uploads)."</div>";

			foreach($pref['e_latest_list'] as $val)
			{
				if (is_readable(e_PLUGIN.$val."/e_latest.php"))
				{
				   		include_once(e_PLUGIN.$val."/e_latest.php");
				}
			}

			$messageTypes = array("Broken Download", "Dev Team Message");
			$queryString = "";
			foreach($messageTypes as $types) {
				$queryString .= " gen_type='$types' OR";
			}
			$queryString = substr($queryString, 0, -3);

			if($amount = $sql -> db_Select("generic", "*", $queryString)) {
				$text .= "<br /><b><a href='".e_ADMIN_ABS."message.php'>".ADLAN_LAT_8." [".$amount."]</a></b>";
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
