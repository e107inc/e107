$search = array("&quot;", "&#039;", "&#036;", "<br />");
$replace = array('"', "'", "$", "\n");
$code_text = str_replace($search, $replace, $code_text);
return eval($code_text);
