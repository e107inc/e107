// $Id: url.sc,v 1.2 2008-11-24 18:18:47 mcfly_e107 Exp $
$e107 = e107::getInstance();
list($part, $section, $type, $parms) = explode('::', $parm, 4);
if(strpos($parms, '=') !== false)
{
	parse_str($parms, $p);
}
else
{
	$p[$parms] = 1;
}
$e107->url->core = ($part == 'core');
if($e107->url->createURL($section, $type, $p))
{
	return $e107->url->link;
}
return 'Failed';
