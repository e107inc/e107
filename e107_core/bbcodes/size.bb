//<?
$class = e107::getBB()->getClass('size');

if(is_numeric($parm) && $parm > 0 && $parm < 38)
{
	return "<span class='{$class}' style='font-size:{$parm}px'>$code_text</span>";
}
else
{
	return $code_text;
}
