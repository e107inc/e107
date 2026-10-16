<?php
namespace Test;

/**
 * List New Items preferences that render the chosen sections and no more.
 */
final class ListNewPrefs
{
	/**
	 * Every column of each section on each mode, sections in the order given.
	 *
	 * @param string[] $modes recent_page, new_page, recent_menu or new_menu
	 * @param string[] $sections section names, such as forum or download
	 * @return array
	 */
	public static function showing(array $modes, array $sections)
	{
		$prefs = array();

		foreach ($modes as $mode)
		{
			$prefs[$mode.'_welcometext'] = '';
			$prefs[$mode.'_colomn'] = '1';

			foreach (array_values($sections) as $order => $section)
			{
				$prefs[$section.'_'.$mode.'_caption'] = 'Fixture '.$section.' '.$mode;
				$prefs[$section.'_'.$mode.'_display'] = '1';
				$prefs[$section.'_'.$mode.'_open'] = '1';
				$prefs[$section.'_'.$mode.'_author'] = '1';
				$prefs[$section.'_'.$mode.'_category'] = '1';
				$prefs[$section.'_'.$mode.'_date'] = '1';
				$prefs[$section.'_'.$mode.'_icon'] = '';
				$prefs[$section.'_'.$mode.'_amount'] = '20';
				$prefs[$section.'_'.$mode.'_order'] = (string) ($order + 1);
			}
		}

		$prefs['new_page_timelapse'] = '0';

		return $prefs;
	}
}
