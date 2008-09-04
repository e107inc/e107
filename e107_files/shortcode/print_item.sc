if (defined("ICONPRINT") && file_exists(THEME."images/".ICONPRINT))
{
	$icon = THEME_ABS."images/".ICONPRINT;
}
else
{
	$icon = e_IMAGE_ABS."generic/".IMODE."/printer.png";
}
$parms = explode("^",$parm);
return "<a href='".e_HTTP."print.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";