

$image = (file_exists(THEME."images/download.png") ? THEME_ABS."images/download.png" : e_IMAGE_ABS.'generic/download.png');
return "<img src='$image' alt='' style='vertical-align: middle;' /> <a href='".e_FILE_ABS."downloads/".$parm."'>".$parm."</a>";

