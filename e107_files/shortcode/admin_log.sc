if (ADMIN) {
	if (!function_exists('admin_log')) {
	function admin_log() {
		global $sql, $ns;
		$text = E_16_ADMINLOG." <a style='cursor: pointer; cursor: hand' onclick=\"expandit('adminlog')\">".ADLAN_116."</a>\n";
		if (e_QUERY == "logall") {
			$text .= "<div id='adminlog'>";
			$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC");
		} else {
			$text .= "<div style='display: none;' id='adminlog'>";
			$sql -> db_Select("tmp", "*", "tmp_ip='adminlog' ORDER BY tmp_time DESC LIMIT 0,10");
		}
		$text .= '<ul>';
		$gen = new convert;
		while ($row = $sql -> db_Fetch()) {
			$datestamp = $gen->convert_date($row['tmp_time'], 'short');
			$text .= '<li>'.$datestamp.$row['tmp_info'].'</li>';
		}
		$text .= '</ul>';

		$text .= "[ <a href='".e_SELF."?logall'>".ADLAN_117."</a> ][ <a href='".e_SELF."?purge'>".ADLAN_118."</a> ]\n</div>";

		return $ns -> tablerender(ADLAN_135, $text, '', TRUE);	
	}
	}
	
	if ($parm == 'request') {
		if (function_exists('log_request')) {
			if (log_request()) {
				return admin_log();
			}
		}	
	} else {
		return admin_log();
	}
}