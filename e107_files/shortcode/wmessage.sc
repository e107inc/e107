if ($pref['wmessage_sc']) {
	global $e107;
	if (strpos($pref['frontpage'], "http")!==FALSE) {
		$front_url = $pref['frontpage'];
	} else {
		$front_url = $e107->HTTPPath.$pref['frontpage'];
	}
	if (e_SELF == $front_url) {
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
}
