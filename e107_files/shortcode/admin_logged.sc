if (ADMIN) {
	$str = str_replace(".", "", ADMINPERMS);
	if (ADMINPERMS == "0") {
		return "<b>".ADLAN_48.":</b> ".ADMINNAME." (".ADLAN_49.") ".( defined('e_DBLANGUAGE') ? "<b>".LAN_head_5."</b> ".e_DBLANGUAGE : "" ) ;
	} else {
		return "<b>".ADLAN_48.":</b>: ".ADMINNAME." (".ADLAN_50.":  $str) ".( defined('e_DBLANGUAGE') ? "<b>".LAN_head_5."</b> ".e_DBLANGUAGE : "" ) ;
	}
} else {
	return ADLAN_51." ...";
}
