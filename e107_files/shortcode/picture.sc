if(is_numeric($parm))
{
	if($parm == USERID)
	{
		$image = USERSESS;
	}
	else
	{
		if(!is_object($sql2)){
			$sql2 = new db;
		}
		$sql2 -> db_Select("user","user_sess","user_id = '{$parm}'");
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
