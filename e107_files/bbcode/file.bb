global $pref;
if (array_key_exists('forum_attach',$pref) && $pref['forum_attach'] && FILE_UPLOADS || ADMIN) {
	return '<a href="'.$parm.'"><img src="'.e_IMAGE.'generic/attach1.png" alt="" style="border:0; vertical-align:middle" />'.$code_text.'</a>';
}