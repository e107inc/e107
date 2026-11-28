<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shortcode;

/**
 * A value in the array given to {@see \e_parse::parseTemplate()} that is rendered where the template reaches its shortcode, with that shortcode's parameters.
 */
interface DeferredValue
{
	/**
	 * @param array|string $parm   the shortcode's parameters: an array for the {CODE: a=1&b=2} form, the trimmed text for the {CODE=text} form with [[ and ]] read as braces, an empty array when it has none or its text is empty or '0'
	 * @param string       $mode   the shortcode's mode, from the {CODE|mode} form, '' when it has none
	 * @return string|null what the template prints in the shortcode's place; null prints nothing
	 */
	public function render($parm, $mode);
}
