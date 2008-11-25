// $Id: url.sc,v 1.3 2008-11-25 16:26:02 mcfly_e107 Exp $
$e107 = e107::getInstance();
list($section, $type, $parms) = explode('|', $parm, 3);
if(strpos($parms, '=') !== false)
{
	parse_str($parms, $p);
}
else
{
	$p[$parms] = 1;
}
return $e107->url->getURL($section, $type, $p);
