if (defined("ICONPRINT") && file_exists(THEME."images/".ICONPRINT)) 
{
	$icon = THEME."images/".ICONPRINT;
}
else
{
	$icon = e_IMAGE."generic/printer_".IMODE.".png";
}
$parms = explode("^",$parm);
return "<a href='".e_BASE."print.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}'/></a>";
