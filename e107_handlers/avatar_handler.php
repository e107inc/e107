<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/avatar_handler.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-18 01:04:43 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT'))
{
	exit;
}

function avatar($avatar)
{
	global $tp;
	if (stristr($avatar, '-upload-') !== false)
	{
		return e_UPLOAD.'avatars/'.str_replace('-upload-', '', $avatar);
	}
	elseif (stristr($avatar, 'Binary') !== false)
	{
		$sqla = new db;
		preg_match("/Binary\s(.*?)\//", $avatar, $result);
		$sqla->db_Select('rbinary', '*', "binary_id='".$tp->toDB($result[1])."' ");
		$row = $sqla->db_Fetch();
		return $row['binary_data'];
	}
	elseif (strpos($avatar, 'http://') === false)
	{
		return e_IMAGE."avatars/".$avatar;
	}
	else
	{
		return $avatar;
	}
}

?>