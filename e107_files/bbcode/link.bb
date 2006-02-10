global $pref;

$external = $pref['links_new_window'] || $parm == 'external' ? ' rel="external"' : '';

if ($parm && $parm != 'external') {
	if (strpos($parm, "://") !== FALSE) {
		return "<a href='".$tp -> toAttribute($parm)."'".$external.">".$code_text."</a>";
	} else if (strpos($parm, ".") !== FALSE) {
		return "<a href='http://".$tp -> toAttribute($parm)."'".$external.">".$code_text."</a>";
	} else if (strpos($code_text, "://") !== FALSE) {
		return "<a href='".$tp -> toAttribute($code_text)."'".$external.">".$code_text."</a>";
	} else {
		return "<a href='http://".$tp -> toAttribute($code_text)."'".$external.">http://".$code_text."</a>";
	}
} else {
	if (strpos($code_text, "://") !== FALSE) {
		return "<a href='".$tp -> toAttribute($code_text)."'".$external.">".$code_text."</a>";
	} else {
		return "<a href='http://".$tp -> toAttribute($code_text)."'".$external.">http://".$code_text."</a>";
	}
}