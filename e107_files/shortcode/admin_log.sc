if (getperms('0'))
{
	if (!function_exists('admin_log')) {
		function admin_log() {
			global $sql, $ns;
			$text = E_16_ADMINLOG." <a style='cursor: pointer' onclick=\"expandit('adminlog')\">".ADLAN_116."</a>\n";
			if (e_QUERY == "logall") {
				$text .= "<div id='adminlog'>";
				$cnt = $sql -> db_Select("dblog", "*", "ORDER BY `dblog_datestamp` DESC", "no_where");
			} else {
				$text .= "<div style='display: none;' id='adminlog'>";
				$cnt = $sql -> db_Select("dblog", "*", "ORDER BY `dblog_datestamp` DESC LIMIT 0,10", "no_where");
			}
			$text .= ($cnt) ? "<ul>" : "";
			$gen = new convert;
			while ($row = $sql -> db_Fetch()) {
				$datestamp = $gen->convert_date($row['dblog_datestamp'], 'short');
				$text .= "<li>{$datestamp} - {$row['dblog_title']}</li>";
			}
			$text .= ($cnt ? "</ul>" : "");
			$text .= "[ <a href='".e_ADMIN_ABS."admin_log.php?logall'>".ADLAN_117."</a> ]";

			$text .= "<br />[ <a href='".e_ADMIN_ABS."admin_log.php?purge' onclick=\"return jsconfirm('".LAN_CONFIRMDEL."')\">".ADLAN_118."</a> ]\n";

			$text .= "</div>";

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
