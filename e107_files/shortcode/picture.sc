if(is_numeric($parm))
{
	if($m[2] == USERID)
	{
		$image = USERSESS;
	}
	else
	{
		$sql2 = new db;
		$sql2 -> db_Select("user","user_sess","user_id = '{$m[2]}'");
		$row = $sql2 -> db_Fetch();
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
