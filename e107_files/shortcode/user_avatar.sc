if(is_numeric($parm))
{
	if($parm == USERID)
	{
		$image = USERIMAGE;
	}
	else
	{
		if(!is_object($sql2))
		{
			$sql2 = new db;
		}
		$sql2 -> db_Select("user","user_image","user_id = '{$parm}'");
		$row = $sql2 -> db_Fetch();
		$image=$row['user_image'];
	}
}
elseif($parm)
{
	$image=$parm;
}
else
{
	$image = USERIMAGE;
}
require_once(e_HANDLER."avatar_handler.php");
return "<div class='spacer'><img src='".avatar($image)."' alt='' /></div><br />";
