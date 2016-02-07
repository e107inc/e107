<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/

if (!defined('e107_INIT')) { exit; }

//v2.x Standard for extending menu configuration within Menu Manager. (replacement for v1.x config.php)
//TODO Configure for news menus. 

class newsTODO_menu
{
	function __construct()
	{
		e107::lan('news','admin', 'true');
	}


	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config()
	{
		$renderTypes = array(BNRLAN_48,'1 - '.BNRLAN_45,'2 - '.BNRLAN_46, "3 - ".BNRLAN_47);

		$fields = array();
		$fields['caption']       = array('title'=> BNRLAN_37, 'type'=>'text', 'writeParms'=>array('size'=>'xxlarge'));
		$fields['count']      = array('title'=> BNRLAN_39, 'type'=>'method');
		$fields['order']        = array('title'=> BNRLAN_41, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*'));
		$fields['category']    = array('title'=> BNRLAN_43, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$renderTypes));

        return $fields;

	}


}
