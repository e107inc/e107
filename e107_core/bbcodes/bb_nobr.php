<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Strip HTML new lines bbcode
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Does nothing when saving in DB
 * Removes new lines produced by nl2br when translating to HTML
 */
class bb_nobr extends e_bb_base
{
	private $_nobrRegEx = '#[^\w\s\-]#';

	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		return '[nobr]'.$code_text.'[/nobr]';
	}

	/**
	 *	Strip new lines
	 */
	function toHTML($code_text, $parm)
	{
		return str_replace(E_NL, "\n", trim($code_text));
	}
}
