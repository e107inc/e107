if(is_readable(THEME."images/e_logo.png")){
	$logo = THEME."images/e_logo.png";
}else{
	$logo = e_IMAGE."logo.png";
}

return "<img src='".$logo."' alt='Logo' />\n";