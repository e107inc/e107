global $pref;
if($pref['smiley_activate'])
{
	$code_text = $tp -> e_emote -> filterEmotesRev($code_text);
}
$search = array('&#039');
$replace = array("'");
$code_text = str_replace($search,$replace,$code_text);
$code_text = htmlentities($code_text,ENT_QUOTES,CHARSET);
$divClass = ($parm) ? $parm : 'indent';
return "<div class='{$divClass}'>{$code_text}</div>";
