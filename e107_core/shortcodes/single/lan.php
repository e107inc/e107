<?php


function lan_shortcode($parm = '')
{
	if(empty($parm))
	{
		return null;
	}

	$lan = trim($parm);
	if(defined($lan))
	{
		return constant($lan);
	}
	elseif(defined('LAN_'.$lan))
	{
		return constant('LAN_'.$lan);
	}
	elseif(ADMIN)
	{
		return "<span class='alert alert-danger' style='padding:0'><strong>".$parm ."</strong> is undefined</span>"; // debug info
	}
}
