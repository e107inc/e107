<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 * $URL$
 * $Id$
 * @DEPRECATED FILE
 */

if (!defined('e107_INIT'))
{
	exit;
}
/**
 * @deprecated
 * Use e107::getParser()->toAvatar() instead.
 */
function avatar($avatar)
{
	trigger_error('<b>'.__METHOD__.' is deprecated.</b> Use e107::getParser()->toAvatar() instead.', E_USER_DEPRECATED); // no LAN

	$data = array('user_image' => $avatar);

	return e107::getParser()->toAvatar($data, array('type'=>'url', 'w'=>100, 'h'=>100));

}

