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
	 * Renders one icon name or path with {@see e_parse::toIcon()}, whitespace ignored, and nothing for any other text.
	 */
	function toHTML($code_text, $parm='')
	{
		$icon = preg_replace('#\s+#', '', str_replace(E_NL, '', $code_text));

		if(!preg_match('#^[\w\-./{}]+$#', $icon))
		{
			return '';
		}

		return e107::getParser()->toIcon($icon);
	}
	
	
}







?>