if (ADMIN) {
	if (!function_exists('admin_latest')) {
		function admin_latest() {
			global $sql, $ns, $pref;
			$active_uploads = $sql -> db_Select("upload", "*", "upload_active=0");
			$reported_posts = $sql -> db_Select("tmp", "*", "tmp_ip='reported_post' ");
			$submitted_news = $sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ");
			$submitted_articles = $sql -> db_Select("content", "*", "content_type ='15' ");
			$submitted_reviews = $sql -> db_Select("content", "*", "content_type ='16' ");

			$text .= "<div style='padding-bottom: 2px;'>".E_16_NEWS.($submitted_news ? " <a href='".e_ADMIN."newspost.php?sn'>".ADLAN_LAT_2.": $submitted_news</a>" : " ".ADLAN_LAT_2.": 0")."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_ARTICLE.($submitted_articles ? " <a href='".e_ADMIN."article.php?sa'>".ADLAN_LAT_3.": $submitted_articles</a>" : " ".ADLAN_LAT_3.": ".$submitted_articles)."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_REVIEW.($submitted_reviews ? " <a href='".e_ADMIN."review.php?sa'>".ADLAN_LAT_4.": $submitted_reviews</a>" : " ".ADLAN_LAT_4.": ".$submitted_reviews)."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_UPLOADS.($active_uploads ? " <a href='".e_ADMIN."upload.php'>".ADLAN_LAT_7.": $active_uploads</a>" : " ".ADLAN_LAT_7.": ".$active_uploads)."</div>";

			if ($pref['plug_latest']) {
				$lats = explode(",", $pref['plug_latest']);
				foreach($lats as $lat) {
					if (file_exists(e_PLUGIN.$lat."/latest.php")) {
						include(e_PLUGIN.$lat."/latest.php");
					}
				}
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