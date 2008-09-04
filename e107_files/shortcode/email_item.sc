if (defined("ICONMAIL") && file_exists(THEME."images/".ICONMAIL)) 
{
	$icon = THEME_ABS."images/".ICONMAIL;
}
else
{
	$icon = e_IMAGE_ABS."generic/".IMODE."/email.png";
}
$parms = explode("^",$parm);
// message^source^other_parms
return "<a href='".e_HTTP."email.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}'/></a>";
