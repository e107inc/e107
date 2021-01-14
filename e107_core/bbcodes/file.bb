//<?


global $pref, $fromadmin;


$class = e107::getBB()->getClass('file');

if(is_numeric($parm)) // Media-Manager file. 
{	
	return "<a class='".$class."' href='".e_HTTP."request.php?file={$parm}'>".$code_text."</a>";		
}



if ((e_PAGE === 'page.php') || (array_key_exists('forum_attach', $pref) && $pref['forum_attach'] && FILE_UPLOADS || ADMIN || $fromadmin))
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
		$image = THEME_ABS.'images/'.$ext.'.png';
	}
	elseif(is_readable(e_IMAGE.'/generic/'.$ext.'.png'))
	{
		$image = e_IMAGE_ABS.'generic/'.$ext.'.png';
	
	}
	elseif(is_readable(THEME.'images/file.png'))
	{
		$image = THEME_ABS.'images/file.png';
	
	}
	else
	{
		$image = e_IMAGE_ABS.'generic/lite/file.png';
	}
	if (strpos($fname, '{e_BASE}') === 0)
	{
		$fname = str_replace('{e_BASE}', SITEURL, $fname);			// Translate into an absolute URL
	}
	return "<a class='{$class}' href='".e107::getParser()->toAttribute($fname)."'><img src='".$image."' alt='' style='border:0; vertical-align:middle' /></a> <a href='".e107::getParser()->toAttribute($fname)."'>".$code_text."</a>";
}
