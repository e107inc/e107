global $override;
if($override_logo = $override -> override_check('logo')){
	call_user_func($override_logo);
	return "";
} else {
	return "<img src='".e_IMAGE."logo.png' alt='Logo' />\n";
}
