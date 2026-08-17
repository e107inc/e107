//<?


$class = e107::getBB()->getClass('table');

$allowedAttributes = array('align', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'class', 'dir', 'height', 'id', 'lang', 'style', 'summary', 'title', 'width');

$renderedParm = html_entity_decode((string) $parm, ENT_QUOTES, 'UTF-8');

preg_match_all('#(?:^|[\s/"\'])([a-z][a-z0-9-]*)\s*=#i', $renderedParm, $attributesUsed);

if(strpos($renderedParm, '>') !== false || array_diff(array_map('strtolower', $attributesUsed[1]), $allowedAttributes))
{
	$parm = '';
}

if($parm)
{
	 return "<table class='{$class}' {$parm}>".trim($code_text)."</table>";
}

return "<table class='table table-striped table-bordered {$class}'>".trim($code_text)."</table>";
