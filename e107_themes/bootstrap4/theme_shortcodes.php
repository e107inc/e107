<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Bootstrap Theme Shortcodes. 
 *
*/


class theme_shortcodes extends e_shortcode
{
	function sc_theme_bs4_glyphs()
	{

		$tp = e107::getParser();
		$ns = e107::getRender();
		$mes = e107::getMessage();

		$mes->addSuccess("Message");

		$text = "<h4>Hardcoded</h4>";

		$text .= '<i class="fab fa-3x fa-teamspeak"></i>&nbsp;
			<i class="fas fa-3x fa-air-freshener"></i>&nbsp;
			<i class="fas fa-igloo"></i>
		';

		$text .= "<h4>toGlyph()</h4>";

		$arr = array(
			'fa-edit',
			'fa-check',
			'fa-cog',
			'fa-mailchimp'
		);

		foreach($arr as $f)
		{
			$text .= $tp->toGlyph($f, array('size'=>'3x'));
			$text .= "&nbsp;";
		}

		return $ns->tablerender("Glyphs", $mes->render(). $text);

	}


	
}






