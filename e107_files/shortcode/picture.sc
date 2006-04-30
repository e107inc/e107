global $loop_uid;
if($parm == "" && is_numeric($loop_uid))
{
	$parm = $loop_uid;
}
if(is_numeric($parm))
{
	if(intval($parm) == USERID)
	{
		$image = USERSESS;
	}
	else
	{
		$row = get_user_data(intval($parm));
		$image=$row['user_sess'];
	}
}
elseif($parm)
{
	$image=$parm;
}
else
{
	$image = USERSESS;
}
if($image && file_exists(e_FILE."public/avatars/".$image))
{
	 return "<img src='".e_FILE."public/avatars/{$image}' alt='' />";
}
else
{
	return "";
}
