global $pref;
if (array_key_exists('forum_attach',$pref) && $pref['forum_attach'] && FILE_UPLOADS || ADMIN)
{
	$image = (file_exists(THEME."generic/file.png") ? THEME."generic/file.png" : e_IMAGE."generic/".IMODE."/file.png");
	list($fname, $uc) = explode("^", $parm);
	if(isset($uc))
	{
		if(!check_class($uc))
		{
			return;
		}
	}
	return "<br /><a href='".$fname."'><img src='".$image."' alt='' style='border:0; vertical-align:middle' /> ".$code_text."</a>";
}