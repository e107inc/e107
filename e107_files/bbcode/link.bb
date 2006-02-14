global $pref;

$external = ($pref['links_new_window'] || strpos($parm, 'external') === 0) ? " rel='external'" : "";

if ($parm && $parm != 'external') {
		$parm = preg_replace('#^external#is', '', $parm);
		return "<a href='".$tp -> toAttribute($parm)."'".$external.">".$code_text."</a>";
} else {
		return "<a href='".$tp -> toAttribute($code_text)."'".$external.">".$code_text."</a>";
}