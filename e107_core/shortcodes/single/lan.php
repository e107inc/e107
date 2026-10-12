<?php


function lan_shortcode($parm = '')
{
	if(empty($parm) || !is_string($parm))
	{
		return null;
	}

	$lan = trim($parm);

	if(defined('LAN_'.$lan))
	{
		return constant('LAN_'.$lan);
	}
	elseif(preg_match('/^[A-Z][A-Za-z0-9_]*$/', $lan) && preg_match('/LAN|\d$/', $lan) && defined($lan))
	{
		return constant($lan);
	}
	elseif(ADMIN)
	{
		return "<span class='alert alert-danger' style='padding:0'><strong>".$parm ."</strong> is not a defined language term</span>"; // debug info
	}
}
