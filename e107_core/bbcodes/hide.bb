global $sql, $e_hide_query, $e_hide_hidden, $e_hide_allowed;

if(!$e_hide_allowed || !isset($e_hide_query) || !$sql->gen($e_hide_query))
{
	if(defined('HIDE_TEXT_HIDDEN'))
	{
		return "<div class='".HIDE_TEXT_HIDDEN."'>{$e_hide_hidden}</div>";
	}
	else
	{
		return "<div style='border:solid 1px;padding:5px'>{$e_hide_hidden}</div>";
	}
}
else
{
	if(defined('HIDE_TEXT_SHOWN'))
	{
		return "<div class='".HIDE_TEXT_SHOWN."'>$code_text</div>";
	}
	else
	{
		return "<div style='border:solid 1px;padding:5px'>$code_text</div>";
	}
}
