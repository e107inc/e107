//<?
// no longer used - see e_parse_class. 
$code_text = str_replace("\r\n", " ", $code_text);
$code_text = html_entity_decode($code_text, ENT_QUOTES, CHARSET);
//return "<!-- bbcode-html -->".$code_text."<!-- bbcode-html-end -->";
