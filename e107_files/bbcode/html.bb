global $pref;
if($pref['smiley_activate'])
{
	$code_text = $tp -> e_emote -> filterEmotesRev($code_text);
}
$code_text = str_replace('&#039;',"'",$code_text);
$ret_text = htmlentities($code_text,ENT_QUOTES);
$divClass = ($parm) ? $parm : 'indent';
return "<div class='{$divClass}'>{$ret_text}</div>";
