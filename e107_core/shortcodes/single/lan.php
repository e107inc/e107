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
	elseif(($term = e_parse::languageTerm($lan)) !== null)
	{
		return $term;
	}
	elseif(ADMIN)
	{
		return "<span class='alert alert-danger' style='padding:0'><strong>".$parm ."</strong> is not a defined language term</span>"; // debug info
	}
}
