<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/

if (!defined('e107_INIT')) { exit; }

//v2.x Standard for extending menu configuration within Menu Manager. (replacement for v1.x config.php)

class featurebox_menu
{
	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='')
	{
		e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_admin_featurebox.php');

		$categories = $this->categories();

		$fields = array();
		$fields['category']     = empty($categories)
			? array('title'=> FBLAN_28, 'type'=>'text', 'help'=> FBLAN_29)
			: array('title'=> FBLAN_28, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>'blank'), 'help'=> FBLAN_29);
		$fields['notablestyle'] = array('title'=> FBLAN_22, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>array(0 => FBLAN_23, 1 => FBLAN_24)));

		return $fields;
	}

	/**
	 * Selectable category templates, empty while the plugin is uninstalled.
	 *
	 * @return array fb_category_template => fb_category_title
	 */
	private function categories()
	{
		if(!e107::isInstalled('featurebox'))
		{
			return array();
		}

		$rows = e107::getDb()->createQueryBuilder()
			->select('fb_category_template', 'fb_category_title')->from('featurebox_category')
			->orderBy('fb_category_id', 'ASC')
			->fetchAll();

		$categories = array();

		foreach((array) $rows as $row)
		{
			$categories[$row['fb_category_template']] = $row['fb_category_title'];
		}

		unset($categories['unassigned']);

		return $categories;
	}
}
