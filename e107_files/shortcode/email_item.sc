if (defined("ICONMAIL") && file_exists(THEME."images/".ICONMAIL)) 
{
	$icon = THEME."images/".ICONMAIL;
}
else
{
	$icon = e_IMAGE."generic/friend.gif";
}
$parms = explode("^",$parm);
// message^source^other_parms
return "<a href='".e_BASE."email.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}'/></a>";
