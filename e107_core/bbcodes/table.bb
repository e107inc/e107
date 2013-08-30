//<?


$class = e107::getBB()->getClass('table');
if($parm)
{
	 return "<table class='{$class}' {$parm}>".trim($code_text)."</table>";		
}

return "<table class='table table-striped table-bordered {$class}'>".trim($code_text)."</table>";
