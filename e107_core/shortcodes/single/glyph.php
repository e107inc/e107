<?php


function glyph_shortcode($parm = '')
{
	if(!is_array($parm))
	{
		$file = $parm;
		$parm = null;
	}
	else
	{
		$file = vartrue($parm['type']);
		unset($parm['type']);	
	}
	
	return e107::getParser()->toGlyph($file,$parm);
}
