<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Paragraph bbcode
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Basic usage [p=CSS-className]text[/p]
 * Advanced usage [p=class=className&id=element-id&style=some: style; and: moresStyle]text[/p]
 * 'class' defaults to 'bbcode' (if left empty)
 */
class bb_p extends e_bb_base
{
	/**
	 * Called prior to save
	 * Sanitize and re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		$code_text = trim($code_text);
		if(empty($code_text)) return '';
		
		if($parm && !strpos($parm, '=')) $parm = 'class='.$parm;
		
		$parms = eHelper::scParams($parm);
		$safe = array();
		
		if(vartrue($parms['class']))
		{
			$safe['class'] = eHelper::secureClassAttr($parms['class']);
		}
		if(vartrue($parms['id']))
		{
			$safe['id'] = eHelper::secureIdAttr($parms['id']);
		}
		if(vartrue($parms['style']))
		{
			$safe['style'] = eHelper::secureStyleAttr($parms['style']);
		}
		if($safe)
		{
			return '[p='.eHelper::buildAttr($safe).']'.$code_text.'[/p]';
		}
		return '[p]'.$code_text.'[/p]';
	}



	/**
	 *	Translate to <p> tag
	 */
	function toHTML($code_text, $parm)
	{
		if($parm && !strpos($parm, '=')) $parm = 'class='.$parm;
		$code_text = trim($code_text);

		$parms = eHelper::scParams($parm);
				
		$class = " ".e107::getBB()->getClass('p'); // consistent classes across all themes. 
		
		$id = vartrue($parms['id']) ? ' id="'.eHelper::secureIdAttr($parms['id']).'"' : '';
		$style = vartrue($parms['style']) ? ' style="'.eHelper::secureStyleAttr($parms['style']).'"' : '';
				
		return "<p{$id}{$class}{$style}>".$code_text.'</p>';
	}
}
