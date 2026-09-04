//<?
 
/* Tag: [textarea name=name&style=style&rows=rows&cols=cols]value[/textarea] */

$class = e107::getBB()->getClass('textarea');
$tastr = "";

$allowedAttributes = array('accesskey', 'autocomplete', 'class', 'cols', 'dir', 'disabled', 'id', 'lang', 'maxlength',
	'minlength', 'name', 'placeholder', 'readonly', 'rows', 'spellcheck', 'style', 'tabindex', 'title', 'wrap');

$guarded = array('id' => 'secureIdAttr', 'style' => 'secureStyleAttr');

parse_str($parm, $tmp);

foreach($tmp as $key => $p)
{
  $lower = strtolower($key);

  if(!is_scalar($p) || !in_array($lower, $allowedAttributes, true))
  {
    continue;
  }

  if(isset($guarded[$lower]))
  {
    $guard = $guarded[$lower];
    $p = eHelper::$guard($p);
  }

  $tastr .= e107::getParser()->toAttribute($key)." = '".e107::getParser()->toAttribute($p)."' ";
}
return "<textarea class='{$class}' $tastr>$code_text</textarea>";
