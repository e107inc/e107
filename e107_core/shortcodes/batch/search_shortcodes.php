<?php


if(!defined('e107_INIT'))
{
	exit;
}


class search_shortcodes extends e_shortcode
{
	function sc_search_input($parm=array())
	{
		if(empty($parm['size']))
		{
			$parm['size'] = 20;
		}

		if(!isset($parm['class']))
		{
			$parm['class'] = 'tbox search';
		}

		return e107::getForm()->text('q','', 50, $parm);
	}


	function sc_search_button($parm=array())
	{
		if(!isset($parm['class']))
		{
			$parm['class'] = 'btn-default btn-secondary button search';
		}

		$label = LAN_SEARCH;

		if(isset($parm['label']))
		{
			$opts = $parm;
			unset($opts['label'],$opts['class']);
			$label = (strpos($parm['label'], '.glyph')!==false) ? e107::getParser()->toGlyph($parm['label'], $opts) : LAN_SEARCH;
			unset($parm['label']);
		}

		return e107::getForm()->button('s','Search','submit', $label, $parm);
	}


}