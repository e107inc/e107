//<?
$class = e107::getBB()->getClass('quote');
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_parser_functions.php");

return "<div class='indent {$class}'><em>$parm ".LAN_WROTE."</em> ...<br />$code_text</div>";
