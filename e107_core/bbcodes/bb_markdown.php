<?php
 /**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * DIV block bbcode
 */

if (!defined('e107_INIT')) { exit; }


class bb_markdown extends e_bb_base
{
	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		return '[markdown]'.$code_text.'[/markdown]';
	}

	/**
	 *	Convert Markdown 
	 */
	function toHTML($code_text, $parm)
	{
		require_once(e_HANDLER."markdown.php");
		return Markdown($code_text);
	}
	
	
}







?>