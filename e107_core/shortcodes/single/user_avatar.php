<?php
// $Id$
function user_avatar_shortcode($parm=null)
{
	$data = null;

	if(!empty($parm))
	{
		if(is_numeric($parm))
		{
			$id = intval($parm);
			$data = e107::user($id);
			$parm = null;
		}
		if(is_string($parm))
		{
			$data = array('user_image'=>$parm);
		}
		elseif(is_array($parm))
		{
			if(isset($parm['user_image']))
			{
				$data = $parm;
			}
			else
			{
				$data = null;
			}

		}
	}


	return e107::getParser()->toAvatar($data, $parm);
/*
	global $loop_uid;

	$tp 		= e107::getParser();
	$width 		= $tp->thumbWidth;
	$height 	= ($tp->thumbHeight !== 0) ? $tp->thumbHeight : "";
	
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
			$row = e107::user($parm);
			$image=$row['user_image'];
		}
	}
	elseif(!empty($parm))
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
	

	$genericImg = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","w=".$width."&h=".$height,true);	
	
	if (vartrue($image)) 
	{
		
		if(strpos($image,"://")!==false) // Remove Image
		{
			$img = $image;	

			
			//$height 	= e107::getPref("im_height",100); // these prefs are too limiting for local images.  
			//$width 		= e107::getPref("im_width",100);
		}
		elseif(substr($image,0,8) == "-upload-")
		{
			
			$image = substr($image,8); // strip the -upload- from the beginning. 
			if(file_exists(e_AVATAR_UPLOAD.$image)) // Local Default Image
			{
				$img =	$tp->thumbUrl(e_AVATAR_UPLOAD.$image,"w=".$width."&h=".$height);	
			}	
			else 
			{
				
				$img = $genericImg;
			}	
		}
		elseif(file_exists(e_AVATAR_DEFAULT.$image))  // User-Uplaoded Image
		{
			$img =	$tp->thumbUrl(e_AVATAR_DEFAULT.$image,"w=".$width."&h=".$height);		
		}
		else // Image Missing. 
		{
			
			$img = $genericImg;
		}
	}
	else // No image provided - so send generic. 
	{
		$img = $genericImg;
	}
	
	$title = (ADMIN) ? $image : "";
	
	$text = "<img class='img-rounded user-avatar e-tip' title='".$title."' src='".$img."' alt='' style='width:".$width."px; height:".$height."px' />";
//	return $img;
	return $text;
*/

}
