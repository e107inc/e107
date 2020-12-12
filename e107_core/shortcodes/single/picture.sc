//<?php
global $loop_uid;
if(empty($parm) && is_numeric($loop_uid))
{
	$parm = $loop_uid;
}
if(is_numeric($parm))
{
	if((int) $parm == USERID)
	{
		$image = USERPHOTO;
	}
	else
	{
		$row = e107::user($parm);
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

return e107::getParser()->parseTemplate("{USER_AVATAR=".$image."}",true);
