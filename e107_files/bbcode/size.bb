if($parm => 0 && $parm <= 36)
{
	return "<span style='font-size:{$parm}px'>$code_text</span>";
}
else
{
	return $code_text;
}
