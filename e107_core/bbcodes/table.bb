//<?


$class = e107::getBB()->getClass('table');
if($parm)
{
	 return "<table class='{$class}' {$parm}>".trim($code_text)."</table>";		
}

return "<table class='{$class}'>".trim($code_text)."</table>";
