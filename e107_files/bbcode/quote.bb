

$code_text = str_replace("[E_NL]", "", $code_text);

if(strstr($code_text, "[quote"))
{
	global $tp;
	$code_text = $tp -> toHTML($code_text, TRUE);
}

return "<div class='indent'><em>Originally posted by $parm</em> ...<br />\"$code_text\"</div>";
