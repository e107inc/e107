if ($pref['wmessage_sc'] && e_PAGE == $pref['frontpage'].'php') {
	global $sql, $pref, $tp, $ns;
	if (!defined("WMFLAG")) {
		$sql->db_Select("wmessage", "*", "ORDER BY wm_active ASC", "nowhere");
		while ($row = $sql->db_Fetch()) {
			if (check_class($row['wm_active'])) {
				$wmessage .= $tp->toHTML($row['wm_text'], TRUE)."<br />";
			}
		}
	 
		if ($wmessage) {
			if ($pref['wm_enclose']) {
				$ns->tablerender("", $wmessage, "wm");
			} else {
				echo $wmessage;
			}
		}
	}
}
