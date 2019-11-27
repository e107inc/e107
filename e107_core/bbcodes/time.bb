//<?

$class = e107::getBB()->getClass('time');

include_once(e_HANDLER."date_handler.php");
return "<span class='{$class}'>".convert::convert_date($code_text, $parm)."</span>"; 
