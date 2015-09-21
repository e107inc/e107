<?php
 /**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * DIV block bbcode
 */

if (!defined('e107_INIT')) { exit; }

// This is a generic and 'responsive' video bbcode. Handles  Youtube and eventually html5 video tags. 

class bb_alert extends e_bb_base
{
	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		if(!empty($parm))
		{
			$ins = '='.$parm;
		}
		else
		{
			$ins = '';
		}

		return '[alert'.$ins.']'.$code_text.'[/alert]';
	}

	/**
	 *	Bootstrap Alert container.
	 * @param $code_text :
	 */
	function toHTML($code_text, $parm='')
	{

		if(!empty($parm))
		{
			$style = "alert alert-".$parm;
		}
		else
		{
			$style = "alert alert-info";
		}

		return "<div class='".$style."'>".$code_text."</div>";
	}
	
	
}







?>