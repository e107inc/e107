<?php


function lan_shortcode($parm = '')
{
	$lan = trim($parm);
	if(defined($lan))
	{
		return constant($lan);
	}
	elseif(ADMIN)
	{
		return "<span class='alert alert-danger'><strong>".$parm ."</strong> is undefined</span>"; // debug info
	}
}
