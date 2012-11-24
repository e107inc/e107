//<?php
global $loop_uid;
if($parm == "" && is_numeric($loop_uid))
{
	$parm = $loop_uid;
}
if(is_numeric($parm))
{
	if(intval($parm) == USERID)
	{
		$image = USERPHOTO;
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
	$image = USERPHOTO;
}
if($image && file_exists(e_MEDIA_AVATAR.$image))
{
	 return "<img src='".e_MEDIA_AVATAR_ABS."{$image}' alt='' />";
}
else
{
	return "";
}
