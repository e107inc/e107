if (ADMIN) {
	if ($pref['multilanguage']) {
		global $sql;
		$ret = " <b>".ADLAN_132.":</b> ";
		$ret .= ($sql->mySQLlanguage) ? $sql->mySQLlanguage : ADLAN_133;
		return $ret;
	}
}