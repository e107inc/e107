global $pref;

if (!USER)
{
	return "";
}

if(empty($parm))
{
    return null;
}

$image = defset('IMAGE_email');

if(is_numeric($parm))
{
	return "<a href='".e_BASE."emailmember.php?id.{$parm}'>{$image}</a>";
}
else
{
	return "<a href='mailto:{$parm}'>{$image}</a>";
}
