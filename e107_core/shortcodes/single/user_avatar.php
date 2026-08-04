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

}
