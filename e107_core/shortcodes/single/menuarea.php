<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2019 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


if(!defined('e107_INIT'))
{
	exit();
}

/**
 * @example {MENUAREA=1}
 */

function menuarea_shortcode($parm, $mode='')
{

	return e107::getMenu()->renderArea($parm);

}