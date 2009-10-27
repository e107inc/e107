global $pref, $fromadmin;

if ((e_PAGE == 'page.php') || (array_key_exists('forum_attach', $pref) && $pref['forum_attach'] && FILE_UPLOADS || ADMIN || $fromadmin))
{
	list($fname, $uc) = explode("^", $parm."^");
	if($uc)
	{
		if(!check_class($uc))
		{
			return;
		}
	}

	$ext = substr($fname, strrpos($fname, '.')+1);

	if(is_readable(THEME.'images/'.$ext.'.png'))
	{
		$image = THEME.'images/'.$ext.'.png';
	}
	elseif(is_readable(e_IMAGE.'/generic/'.$ext.'.png'))
	{
		$image = e_IMAGE.'generic/'.$ext.'.png';
	
	}
	elseif(is_readable(THEME.'images/file.png'))
	{
		$image = THEME.'images/file.png';
	
	}
	else
	{
		$image = e_IMAGE.'generic/lite/file.png';
	}

	return "<a href='".$tp -> toAttribute($fname)."'><img src='".$image."' alt='' style='border:0; vertical-align:middle' /></a> <a href='".$tp -> toAttribute($fname)."'>".$code_text."</a>";
}
