global $pref;
if($pref['smiley_activate'])
{
	$code_text = $tp -> e_emote -> filterEmotesRev($code_text);
}
$search = array('[E_NL]','&#092;','&#036;');
$replace = array("\r\n","\\",'$');
$code_text = str_replace($search,$replace,$code_text);

if($pref['useGeshi']) {
	require_once(e_PLUGIN."geshi/geshi.php");
	if($parm) {
		$geshi = new GeSHi($code_text, $parm, e_PLUGIN."geshi/geshi/");
	} else {
		$geshi = new GeSHi($code_text, ($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : 'php'), e_PLUGIN."geshi/geshi/");
	}
	$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);	
	$geshi->set_header_type(GESHI_HEADER_DIV);
	return $geshi->parse_code();
}

$highlighted_text = highlight_string(html_entity_decode($code_text,ENT_QUOTES,CHARSET),TRUE);
$divClass = ($parm) ? $parm : 'code_highlight';
return "<div class='{$divClass}'>{$highlighted_text}</div>";
