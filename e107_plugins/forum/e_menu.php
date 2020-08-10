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
	
class forum_menu
{
	function __construct()
	{
		e107::lan('forum','menu',true); // English_menu.php or {LANGUAGE}_menu.php
	}

	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{

		$layouts = e107::getLayouts('forum','newforumposts_menu');

		$fields = array();
		$fields['caption']      = array('title'=> LAN_FORUM_MENU_004, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
		$fields['display']      = array('title'=> LAN_FORUM_MENU_005, 'type'=>'text',  'writeParms'=>array('size'=>'mini','pattern'=>'[0-9]*','default' => 10));
		$fields['maxage']       = array('title'=> LAN_FORUM_MENU_012, 'type'=>'text', 'help'=>LAN_FORUM_MENU_013, 'writeParms'=>array('size'=>'mini','pattern'=>'[0-9]*','default' => 0));
		$fields['characters']   = array('title'=> LAN_FORUM_MENU_006, 'type'=>'text', 'writeParms'=>array('size'=>'mini','pattern'=>'[0-9]*','default' => 120));
		$fields['postfix']      = array('title'=> LAN_FORUM_MENU_007, 'type'=>'text', 'writeParms'=>array('size'=>'mini','default' => '...'));
		$fields['scroll']        = array('title'=> LAN_FORUM_MENU_014, 'type'=>'text', 'writeParms'=>array('size'=>'mini','pattern'=>'[0-9]*'), 'help'=>LAN_FORUM_MENU_015);
		$fields['layout']       = array('title'=> LAN_TEMPLATE, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$layouts[0]));
		
        return $fields;

	}

}

// optional - for when using custom methods above.
/*
class forum_menu_form extends e_form
{


}
*/

