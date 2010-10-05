global $pref;
if(!$postID) { return ''; }
if($pref['php_bbcode'] == e_UC_NOBODY) return '';
if($postID == 'admin' || check_class($pref['php_bbcode'], '', $postID))
{
	$search = array("&quot;", "&#039;", "&#036;", '<br />', E_NL, "-&gt;");
	$replace = array('"', "'", "$", "\n", "\n", "->");
	$code_text = str_replace($search, $replace, $code_text);
	return eval($code_text);
}
return '';
