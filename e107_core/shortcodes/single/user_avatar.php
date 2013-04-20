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
		
		if(strpos($image,"://")!==false) // Remove Image
		{
			$img = $image;	
		}
		elseif(file_exists(e_AVATAR_DEFAULT.$image)) // Local Default Image
		{
			$img =	$tp->thumbUrl(e_AVATAR_DEFAULT.$image,"w=".$width."&h=".$height,true);	
		}
		elseif(file_exists(e_AVATAR_UPLOAD.$image))  // User-Uplaoded Image
		{
			$img =	$tp->thumbUrl(e_AVATAR_UPLOAD.$image,"w=".$width."&h=".$height,true);		
		}
		else // Image Missing. 
		{
			$img = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","w=".$width."&h=".$height,true);	
		}
	}
	else // No image provided - so send generic. 
	{
		$img = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","w=".$width."&h=".$height,true);
	}
	
	$text = "<img class='user-avatar e-tip' src='".$img."' alt='' style='width:".$width."px; height:".$height."px' />";
//	return $img;
	return $text;

}
?>