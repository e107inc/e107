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
 * @DEPRECATED 
 * Use e107::getParser()->parseTemplate("{USER_AVATAR=".$avatar."}",true); instead. 
 */
function avatar($avatar)
{
	$data = array('user_image' => $avatar);

	return e107::getParser()->toAvatar($data, array('type'=>'url'));
//	return e107::getParser()->parseTemplate("{USER_AVATAR=".$avatar."}",true);
	
	/*
	global $tp;
	if (stristr($avatar, '-upload-') !== false)
	{
		return e_AVATAR_UPLOAD.str_replace('-upload-', '', $avatar);
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
		return SITEURLBASE.e_IMAGE_ABS."avatars/".$avatar;
	}
	else
	{
		return $avatar;
	}
 */
}

