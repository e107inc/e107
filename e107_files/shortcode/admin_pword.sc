if (ADMIN && ADMINPERMS == "0") {
	global $ns;
	if ((ADMINPWCHANGE+2592000) < time()) {
		$text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN."updateadmin.php'>".ADLAN_103."</a></div>";
		return $ns -> tablerender(ADLAN_104, $text);
	}
}
