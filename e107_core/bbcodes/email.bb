//<?php
$class = e107::getBB()->getClass('email');

global $pref;

$typedAddress = e107::getParser()->bbcodeTextAsTyped($parm ? $parm : $code_text);

if($pref['make_clickable'])
{

	$tp = e107::getParser();

	list($p1, $p2) = explode("@", $parm ? $parm : $code_text);

	// CHARSET is utf-8 - email.bb too
	$text = $parm ? $code_text : $p1.'©'.$p2;

	list($p1, $p2) = explode("@", $typedAddress);
	$address = $tp->toJsString($p1).'+"@"+'.$tp->toJsString(rawurlencode($p2));
	$href = $tp->toJsUrl('window.location="mai"+"lto:"+'.$address.';self.close();');

	return "<a rel='external' href='$href' onmouseover='window.status=\"mai\"+\"lto:\"+$address; return true;' onmouseout='window.status=\"\";return true;'>".$text."</a>";
}
// Old method that attracts SPAM.
return "<a class='{$class}' href='mailto:".e107::getParser()->toAttribute($typedAddress)."'>".$code_text."</a>";