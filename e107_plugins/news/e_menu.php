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

class news_menu
{

	function __construct()
	{
		// e107::lan('news','admin', 'true');
	}

	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{
		$fields = array();
		$categories = array();

		$tmp =  e107::getDb()->retrieve('news_category','category_id,category_name',null, true);

		foreach($tmp as $val)
		{
			$id = $val['category_id'];
			$categories[$id] = $val['category_name'];
		}

		switch($menu)
		{
			case "latestnews":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>'blank'));
			break;


			case "news_categories":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*'));
				break;

			case "news_months":
					$fields['showarchive']  = array('title'=> "Display Archive Link", 'type'=>'boolean');
					$fields['year']         = array('title'=> "Year", 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
				break;

			case "other_news":
			case "other_news2":
					$fields['caption']   = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
				break;

		}

		 return $fields;




	}


}
