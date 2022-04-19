<?php


function lan_shortcode($parm = '')
{
	if(empty($parm))
	{
		return null;
	}

	$lan = trim($parm);

	if(defined('LAN_'.$lan))
	{
		return constant('LAN_'.$lan);
	}
	elseif(defined($lan))
	{
		return constant($lan);
	}
	elseif(ADMIN)
	{
		return "<span class='alert alert-danger' style='padding:0'><strong>".$parm ."</strong> is undefined</span>"; // debug info
	}
}
