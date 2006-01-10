if(is_readable(THEME."images/logo.png")){
	$logo = THEME."images/logo.png";
}else{
	$logo = e_IMAGE."logo.png";
}

return "<img src='".$logo."' alt='Logo' />\n";