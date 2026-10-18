global $sql, $e_hide_query, $e_hide_hidden, $e_hide_allowed, $e_hide_block, $e_hide_unseal;

if(empty($e_hide_block))
{
	return '[hide'.(($parm !== '') ? '='.htmlspecialchars($parm, ENT_QUOTES, 'UTF-8', false) : '').']'.$code_text.'[/hide]';
}

$sealed = trim($e_hide_block['content']);

if(preg_match('#^[\w-]+\.\.[\w-]+\.[\w-]+\.[\w-]+$#', $sealed))
{
	$text = is_callable($e_hide_unseal) ? call_user_func($e_hide_unseal, $sealed) : null;
	$shown = is_string($text);
	$e_hide_block['content'] = $shown ? $text : '';
}
else
{
	$shown = $e_hide_allowed && (!isset($e_hide_query) || $sql->gen($e_hide_query));
}

if(!$shown)
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
