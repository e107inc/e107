global $pref;
$image = IMAGE_email;
if(is_numeric($parm))
{
	if(!$pref['emailusers']){return "";}
	return "<a href='".e_BASE."emailmember.php?id.{$parm}'>{$image}</a>";
}
else
{
	return "<a href='mailto:{$parm}'>{$image}</a>";
}
