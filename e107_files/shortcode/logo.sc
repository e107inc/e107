if(function_exists("theme_logo"))
{
	call_user_func("theme_logo");
	return "";
}
else
{
	return "<img src='".e_IMAGE."logo.png' alt='Logo' />\n";
}
