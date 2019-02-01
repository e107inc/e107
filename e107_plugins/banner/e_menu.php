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


class banner_menu
{
	function __construct()
	{
		e107::lan('banner', true);
	}


	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{
		$renderTypes = array(BNRLAN_48,'1 - '.BNRLAN_45,'2 - '.BNRLAN_46, "3 - ".BNRLAN_47);

		$fields = array();
		$fields['banner_caption']       = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
		$fields['banner_campaign']      = array('title'=> BNRLAN_39, 'type'=>'method');
		$fields['banner_amount']        = array('title'=> BNRLAN_41, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*'));
		$fields['banner_width']         = array('title'=> LAN_WIDTH, 'type'=>'text', 'help'=>"In pixels", 'writeParms'=>array('pattern'=>'[0-9]*'));
		$fields['banner_rendertype']    = array('title'=> BNRLAN_43, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$renderTypes));

        return $fields;

	}


}

// optional
class banner_menu_form extends e_form
{

	public function banner_campaign($curVal)
	{
		$sql = e107::getDb();

		$sql->select("banner", "DISTINCT(banner_campaign) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");

		$text = '';

		while ($row = $sql -> fetch())
		{
			$checked = in_array($row['banner_campaign'],$curVal);
			$text .= $this->checkbox('banner_campaign[]',$row['banner_campaign'],$checked, array('label'=> $row['banner_campaign'],'class'=>'e-save')); // e-save class is required.
		}


		return $text;
	}

}