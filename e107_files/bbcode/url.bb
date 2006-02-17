global $pref;

$external = ($pref['links_new_window'] || strpos($parm, "external") !== FALSE) ? " rel='external'" : "";
$parm     = preg_replace("#^external.?#si", "", $parm);

if (strpos($parm,".") === FALSE)
{
  $parm = $code_text;
}

if (strpos($parm,"://") === FALSE)
{
  $parm = "http://".preg_replace("#http:\/\/#si", "",$parm);
}

return "<a href='".$tp->toAttribute($parm)."'$external>".$code_text."</a>";
