<?php
// $Id$
function user_avatar_shortcode($parm='')
{
	global $loop_uid;
	
	
	$height 	= e107::getPref("im_height");
	$width 		= e107::getPref("im_width");
	$tp 		= e107::getParser();
	
	if(intval($loop_uid) > 0 && trim($parm) == "")
	{
		$parm = $loop_uid;
	}
	
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
	elseif(USERIMAGE)
	{
		$image = USERIMAGE;
	}
	else
	{
		$image = "";	
	}
	
	
	if (vartrue($image)) 
	{
		$img = (strpos($image,"://")!==false) ? $image : $tp->thumbUrl(e_MEDIA."avatars/".$image,"aw=".$width."&ah=".$height);
		$text = "<img class='user-avatar e-tip' src='".$img."' alt='' style='width:".$width."px; height:".$height."px' />
		";
	}
	else
	{
		$img = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","aw=".$width."&ah=".$height);
		$text = "<img class='user-avatar' src='".$img."' alt='' />";
	}
	
	return $text;

}
?>