global $pref;
if($pref['smiley_activate'])
{
	$code_text = $tp -> e_emote -> filterEmotesRev($code_text);
}
$search = array('[E_NL]','&#092;','&#036;');
$replace = array("\r\n","\\",'$');
$code_text = str_replace($search,$replace,$code_text);

$pref['useGeshi'] = TRUE;

if($pref['useGeshi']) {
	require_once(e_PLUGIN."geshi/geshi.php");
	$geshi = new GeSHi($code_text, 'php', e_PLUGIN."geshi/geshi/");
	return $geshi->parse_code();
}

$highlighted_text = highlight_string(html_entity_decode($code_text,ENT_QUOTES,CHARSET),TRUE);
$divClass = ($parm) ? $parm : 'code_highlight';
return "<div class='{$divClass}'>{$highlighted_text}</div>";
