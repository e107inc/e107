<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if(!defined('e107_INIT'))
{
	exit;
}



class hero_shortcodes extends e_shortcode
{
	public $override = false; // when set to true, existing core/plugin shortcodes matching methods below will be overridden. 
	private $active = false;

	function __construct()
	{
		parent::__construct();

		$visibility = e107::pref('hero', 'visibility');
		$this->active = check_class($visibility);

	}

	/**
	 * @param null $parm
	 * @example {HERO}
	 * @return string
	 */
	function sc_hero($parm = null)  // Naming:  "sc_" + [plugin-directory] + '_uniquename'
	{
		if(empty($this->active))
		{
			return null;
		}

		$text = "";

	//	if(!empty($parm))
	//	{
		//	$text .= print_a($parm,true); // e_menu.php form data.
	//	}

		$data = e107::getDb()->retrieve('hero','*',"hero_class IN(".USERCLASS_LIST.") ORDER BY hero_order",true);

		$sc = e107::getScBatch('hero', true, 'hero');

		$template = e107::getTemplate('hero','hero', varset($parm['template'],'default')); // todo use a table field to make layout dynamic.

		$tp = e107::getParser();

		$totalSlides = count($data);

		$default = array('hero_total_slides'=>$totalSlides);

		if(!is_object($sc))
		{
			return "Hero shortcodes failed to load";
		}

		$sc->setVars($default);

		$text = $tp->parseTemplate($template['header'],true, $sc);

		foreach($data as $k=>$row)
		{
			$bullet = e107::unserialize($row['hero_bullets']);
			$row['hero_bullets'] = $bullet;

			$button1 = e107::unserialize($row['hero_button1']);
			$row['hero_button1'] = $button1;

			$button2 = e107::unserialize($row['hero_button2']);
			$row['hero_button2'] = $button2;

			$row['hero_slide_active'] = ($k == 0) ? 'active' : '';
			$row['hero_total_slides'] = $totalSlides;

			$sc->setVars($row);

			$text .= $tp->parseTemplate($template['start'],true,$sc);

			foreach($row['hero_bullets'] as $cnt=>$row2)
			{
				if(empty($row2['text']))
				{
					continue;
				}

				$sc->count = $cnt;

				$text .= $tp->parseTemplate($template['item'],true,$sc);
			}

			$text .= $tp->parseTemplate($template['end'],true,$sc);

		}

		$text .= $tp->parseTemplate($template['footer'], true, $sc);

		return $text;

	}

}
