global $pref;

$parm     = trim($parm);
$external = ($pref['links_new_window'] || strpos($parm, 'external') === 0) ? " rel='external'" : "";

if(substr($parm,0,6) == "mailto")
{
	list($pre,$email) = explode(":",$parm);
	list($p1,$p2) = explode("@",$email);
	return "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"$p1\"+\"@\"+\"$p2\"; return true;' onmouseout='window.status=\"\";return true;'>".$code_text."</a>";
}

if ($parm && $parm != 'external' && strpos($parm, ' ') === FALSE)
{
	$parm = preg_replace('#^external.#is', '', $parm);
	return "<a href='".$tp -> toAttribute($parm)."'".$external.">".$code_text."</a>";
}

else
{
	return "<a href='".$tp -> toAttribute($code_text)."'".$external.">".$code_text."</a>";
}