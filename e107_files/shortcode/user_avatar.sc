if(is_numeric($parm))
{
	if($parm == USERID)
	{
		$image = USERIMAGE;
	}
	else
	{
		$row = get_user_data(intval($parm));
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
