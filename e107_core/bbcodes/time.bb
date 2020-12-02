//<?

$class = e107::getBB()->getClass('time');

include_once(e_HANDLER."date_handler.php");
return "<span class='{$class}'>".e107::getDate()->convert_date($code_text, $parm)."</span>";
