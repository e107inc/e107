<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/

if (!defined('e107_INIT')) { exit; }

//v2.x Standard for extending menu configuration within Menu Manager. (replacement for v1.x config.php)


class tagcloud_menu
{
	function __construct()
	{
	//	e107::lan('banner','admin', 'true');
	}


	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{
		$fields = array();
		$fields['tagcloud_caption']       = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
		$fields['tagcloud_limit']       = array('title'=> LAN_LIMIT, 'type'=>'number');
        return $fields;

	}


}

// optional
class tagcloud_menu_form extends e_form
{


}


?>