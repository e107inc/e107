

$image = (file_exists(THEME."generic/download.png") ? THEME."generic/download.png" : e_IMAGE."generic/".IMODE."/download.png");
return "<img src='$image' alt='' style='vertical-align: middle;' /> <a href='".e_FILE."downloads/".$parm."'>".$parm."</a>";

