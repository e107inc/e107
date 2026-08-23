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
	 * Selectable categories, empty while the plugin is uninstalled.
	 *
	 * @return array {@see plugin_featurebox_category::address()} => fb_category_title
	 */
	private function categories()
	{
		if(!e107::isInstalled('featurebox'))
		{
			return array();
		}

		$rows = e107::getDb()->createQueryBuilder()
			->select('*')->from('featurebox_category')
			->orderBy('fb_category_id', 'ASC')
			->fetchAll();

		$categories = array();

		foreach((array) $rows as $row)
		{
			$categories[plugin_featurebox_category::address($row)] = $row['fb_category_title'];
		}

		unset($categories['unassigned']);

		return $categories;
	}

	/**
	 * Build the {FEATUREBOX} shortcode for one placement of featurebox_menu.php.
	 *
	 * The category becomes the modifier and everything else the query string of
	 * {@see featurebox_shortcodes::sc_featurebox()}.
	 *
	 * @param array|string $parm menu parameters: an array from Menu Manager, a query string from {@see menu_shortcode()} or {@see plugin_shortcode()}
	 * @param string $default category address to render when the placement names none; reaches the shortcode verbatim, so pass a trusted value
	 * @return string
	 */
	public static function shortcode($parm, $default)
	{
		if(is_string($parm))
		{
			parse_str($parm, $parms);
		}
		else
		{
			$parms = (array) $parm;
		}

		$category = isset($parms['category']) && is_scalar($parms['category'])
			? preg_replace('/[^\w-]/', '', (string) $parms['category'])
			: '';
		unset($parms['category'], $parms['path']);

		foreach(array('notablestyle', 'no_fill_empty') as $flag)
		{
			if(isset($parms[$flag]) && empty($parms[$flag]))
			{
				unset($parms[$flag]);
			}
		}

		if($category === '')
		{
			$category = $default;
		}

		$query = http_build_query($parms, '', '&');

		return '{FEATUREBOX|'.$category.($query === '' ? '' : '='.$query).'}';
	}
}
