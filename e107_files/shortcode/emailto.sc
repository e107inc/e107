global $pref;
$image = (file_exists(THEME."forum/email.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='' style='border:0' />";
if(is_numeric($parm))
{
	if(!$pref['emailusers']){return "";}
	return "<a href='".e_BASE."emailmember.php?id.{$parm}'>{$image}</a>";
}
else
{
	return "<a href='mailto:{$parm}'>{$image}</a>";
}
