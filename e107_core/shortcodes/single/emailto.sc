global $pref;

if (!USER)
{
	return "";
}

$image = IMAGE_email;

if(is_numeric($parm))
{
	return "<a href='".e_BASE."emailmember.php?id.{$parm}'>{$image}</a>";
}
else
{
	return "<a href='mailto:{$parm}'>{$image}</a>";
}
