if($parm > 0 && $parm < 30)
{
	return "<span style='font-size:{$parm}px;'>$code_text</span>";
}
else
{
	return $code_text;
}
