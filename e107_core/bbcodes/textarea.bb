//<?
 
/* Tag: [textarea name=name&style=style&rows=rows&cols=cols]value[/textarea] */

$class = e107::getBB()->getClass('textarea');
$tastr = "";

$allowedAttributes = array('accesskey', 'autocomplete', 'class', 'cols', 'dir', 'disabled', 'id', 'lang', 'maxlength',
	'minlength', 'name', 'placeholder', 'readonly', 'rows', 'spellcheck', 'style', 'tabindex', 'title', 'wrap');

parse_str($parm, $tmp);

foreach($tmp as $key => $p)
{
  if(!is_scalar($p) || !in_array(strtolower($key), $allowedAttributes, true))
  {
    continue;
  }

  $tastr .= e107::getParser()->toAttribute($key)." = '".e107::getParser()->toAttribute($p)."' ";
}
return "<textarea class='{$class}' $tastr>$code_text</textarea>";
