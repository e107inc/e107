//<?php
$class = e107::getBB()->getClass('email');

global $pref;


if($pref['make_clickable'])
{

	$tp = e107::getParser();

	list($p1, $p2) = explode("@", $parm ? $parm : $code_text);

	// CHARSET is utf-8 - email.bb too
	$text = $parm ? $code_text : $p1.'©'.$p2;

	$address = $tp->toJsString($p1).'+"@"+'.$tp->toJsString($p2);
	$href = str_replace('%', '%25', $address);

	return "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+$href;self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+$address; return true;' onmouseout='window.status=\"\";return true;'>".$text."</a>";
}
// Old method that attracts SPAM.
if ($parm) {
  	return "<a class='{$class}' href='mailto:".e107::getParser()->toAttribute($parm)."'>".$code_text."</a>";
} else {
  	return "<a class='{$class}' href='mailto:".e107::getParser()->toAttribute($code_text)."'>".$code_text."</a>";
}