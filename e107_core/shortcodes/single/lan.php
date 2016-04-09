<?php


function lan_shortcode($parm = '')
{
	$lan = trim($parm);
	if(defined($lan))
	{
		return constant($lan);
	}
}
