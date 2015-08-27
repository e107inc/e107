<?php
 /**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * DIV block bbcode
 */

if (!defined('e107_INIT')) { exit; }

// This is a generic glyph bbcode. 

class bb_glyph extends e_bb_base
{
	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		return '[glyph]'.$code_text.'[/glyph]';
	}

	/**
	 *	Glyph tag. 
	 * @param $code_text : xxxxxxx.youtube or xxxxxxx.mp4
	 */
	function toHTML($code_text, $parm='')
	{
		return e107::getParser()->toIcon($code_text);
	}
	
	
}







?>