<?php


function glyph_shortcode($parm = '')
{
	$file = "icon-".$parm.".glyph";
	return e107::getParser()->toGlyph($file,false);
}
