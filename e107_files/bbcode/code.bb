$code_text = $tp -> e_emote -> filterEmotesRev($code_text);
$code_text = str_replace("&#036;","$",$code_text);
$code_text = str_replace("&#092;","\\",$code_text);
$code_text = str_replace("[E_NL]","\n",$code_text);
$highlighted_text = highlight_string($code_text,TRUE);
$divClass = ($parm) ? $parm : 'code_highlight';
return "<div class='{$divClass}'>{$highlighted_text}</div>";
