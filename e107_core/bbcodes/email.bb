//<?php
$class = e107::getBB()->getClass('email');

global $pref;


if($pref['make_clickable'])
{

	if($parm)
	{
		list($p1,$p2) = explode("@",$parm);
		return "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"".$p1."\"+\"@\"+\"".$p2."\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"".$p1."\"+\"@\"+\"".$p2."\"; return true;' onmouseout='window.status=\"\";return true;'>".$code_text."</a>";
	}
	else
	{
		list($p1, $p2) = explode("@", $code_text);
		
		// CHARSET is utf-8 - email.bb too
		$email_text = $p1.'©'.$p2;
		return "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"".$p1."\"+\"@\"+\"".$p2."\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"".$p1."\"+\"@\"+\"".$p2."\"; return true;' onmouseout='window.status=\"\";return true;'>".$email_text."</a>";
	}
}
// Old method that attracts SPAM.
if ($parm) {
  	return "<a class='{$class}' href='mailto:".e107::getParser()->toAttribute($parm)."'>".$code_text."</a>";
} else {
  	return "<a class='{$class}' href='mailto:".e107::getParser()->toAttribute($code_text)."'>".$code_text."</a>";
}