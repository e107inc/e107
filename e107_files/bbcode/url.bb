global $pref;

$parm     = trim($parm);
$external = ($pref['links_new_window'] || strpos($parm, 'external') === 0) ? " rel='external'" : "";

if ($parm && $parm != 'external' && strpos($parm, ' ') === FALSE)
{
	$parm = preg_replace('#^external.#is', '', $parm);
	if (strtolower(substr($parm,0,11)) == 'javascript:') return '';
	return "<a href='".$tp -> toAttribute($parm)."'".$external.">".$code_text."</a>";
}
else
{
  if (strtolower(substr($code_text,0,11)) == 'javascript:') return '';
	return "<a href='".$tp -> toAttribute($code_text)."'".$external.">".$code_text."</a>";
}
