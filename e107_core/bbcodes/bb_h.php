<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Heading bb code
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Basic usage [h=2]text[/h]
 * The same [h]text[/h] as heading number defaults to '2'
 * Advanced usage [h=2|class=className&id=element-id&style=some: style; and: moresStyle]text[/h]
 * 'class' defaults to 'bbcode' (if left empty)
 */
class bb_h extends e_bb_base
{
	/**
	 * Called prior to save
	 * Sanitize and re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		$code_text = trim($code_text);
		if(empty($code_text)) return '';

		$bparms = eHelper::scDualParams($parm);
		
		$h = $bparms[1] ? intval($bparms[1]) : 2;
		$parms = $bparms[2];
		unset($bparms);
		
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
			return '[h='.$h.'|'.eHelper::buildAttr($safe).']'.$code_text.'[/h]';
		}
		return '[h='.$h.']'.$code_text.'[/h]';
	}



	/**
	 *	Translate to <h*> tag
	 */
	function toHTML($code_text, $parm)
	{
		$code_text = trim($code_text);
		if(empty($code_text)) return '';
		$bparms = eHelper::scDualParams($parm);
		
		$h = 'h'.($bparms[1] ? intval($bparms[1]) : 2);
		$parms = $bparms[2];
		unset($bparms);

		$class = varsettrue($parms['class']) ? ' class="'.eHelper::secureClassAttr($parms['class']).'"' : '';
		if(!$class) $class = ' class="bbcode"';
		
		$id = varsettrue($parms['id']) ? ' id='.eHelper::secureIdAttr($parms['id']) : '';
		$style = varsettrue($parms['style']) ? ' style="'.eHelper::secureStyleAttr($parms['style']).'"' : '';
				
		return "<{$h}{$id}{$class}{$style}>".$code_text."</{$h}>";
	}
}
