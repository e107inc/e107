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
	
class social_menu
{
	function __construct()
	{
		// e107::lan('_blank','menu',true); // English_menu.php or {LANGUAGE}_menu.php
	}

	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{
		if($menu !== 'xurl')
		{
			return null;
		}

		$templates = e107::getLayouts('social','social_xurl', 'front', null, false, false);
	//	$sizes = array('2x'=> '2x', '3x'=>'3x', '4x'=>'4x', '5x'=>'5x');

		$fields = array();
		$fields['caption']      = array('title'=> "Caption", 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
		$fields['template']     = array('title'=> LAN_TEMPLATE, 'type'=>'dropdown', 'tab'=>0, 'writeParms'=>array('optArray'=>$templates));
	//	$fields['size']     = array('title'=> "Size", 'type'=>'dropdown', 'tab'=>0, 'writeParms'=>array('optArray'=>$sizes));


        return $fields;

	}

}

// optional - for when using custom methods above.

class social_menu_form extends e_form
{
/*
	function blankCustom($curVal)
	{

		$frm = e107::getForm();
		$opts = array(1,2,3,4);
		$frm->select('blankCustom', $opts, $curVal);


	}*/


}


