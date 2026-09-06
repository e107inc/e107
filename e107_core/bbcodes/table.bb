//<?


$class = e107::getBB()->getClass('table');

$allowedAttributes = array('align', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'class', 'dir', 'height', 'id', 'lang', 'style', 'summary', 'title', 'width');

$renderedParm = html_entity_decode((string) $parm, ENT_QUOTES, 'UTF-8');

preg_match_all('#([a-z][a-z0-9-]*)\s*=\s*(?|"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+(?:\s+(?![a-z][a-z0-9-]*\s*=)[^\s"\'>]+)*))#i', $renderedParm, $pairs, PREG_SET_ORDER);

$attributes = array();

foreach($pairs as $pair)
{
	$name = strtolower($pair[1]);

	if(in_array($name, $allowedAttributes, true))
	{
		$attributes[$name] = $pair[2];
	}
}

if(!$attributes)
{
	return "<table class='table table-striped table-bordered {$class}'>".trim($code_text)."</table>";
}

$attributes = array('class' => trim($class.' '.varset($attributes['class']))) + $attributes;

$renderedAttributes = '';

foreach($attributes as $name => $value)
{
	$value = str_replace(array('&', '<', '>', '"', "'"), array('&#38;', '&#60;', '&#62;', '&#34;', '&#x27;'), $value);
	$renderedAttributes .= " {$name}='{$value}'";
}

return "<table{$renderedAttributes}>".trim($code_text)."</table>";
