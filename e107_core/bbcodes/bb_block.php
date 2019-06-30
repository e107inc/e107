<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * DIV block bbcode
 */

if (!defined('e107_INIT')) { exit; }

/**
 *	Div HTML blocks handling
 *
 * [block=class=xxx&style=xxx&id=xxx]$content[/block]
 * [block=xxx]$content[/block] equals to [block=class=xxx]$content[/block]
 * If $content is missing, HTML comment will be used - '<!-- -->'
 */
class bb_block extends e_bb_base
{
	/**
	 *	Called prior to save
	 *
	 */
	function toDB($code_text, $parm)
	{
		// just for now
		if(!ADMIN) return $code_text; // TODO - pref
		
		// transform to class, equal sign at 0 position is not well formed parm string
		if($parm && !strpos($parm, '=')) $parm = 'class='.$parm;
		$parms = eHelper::scParams($parm);
		$safe = array();
		
		if(vartrue($parms['class'])) $safe['class'] = eHelper::secureClassAttr($parms['class']);
		if(vartrue($parms['id'])) $safe['id'] = eHelper::secureIdAttr($parms['id']);
		if(vartrue($parms['style'])) $safe['style'] = eHelper::secureStyleAttr($parms['style']);
		if($safe)
		{
			return '[block='.eHelper::buildAttr($safe).']'.$code_text.'[/block]';
		}
		return '[block]'.$code_text.'[/block]';
	}

	/**
	 *	Translate youtube bbcode into the appropriate HTML
	 */
	function toHTML($code_text, $parm)
	{
		// transform to class, equal sign at 0 position is not well formed parm string
		if($parm && !strpos($parm, '=')) $parm = 'class='.$parm;
		$parms = eHelper::scParams($parm);
		
		// add auto-generated class name and parameter class if available
		$class = e107::getBB()->getClass('block').(varset($parms['class']) ? ' '.$parms['class'] : '');
		$class = ' class="'.$class.'"';
		
		$id = vartrue($parms['id']) ? ' id="'.eHelper::secureIdAttr($parms['id']).'"' : '';
		$style = vartrue($parms['style']) ? ' style="'.eHelper::secureStyleAttr($parms['style']).'"' : '';
		
		if(empty($code_text)) $code_text = '<!-- -->';
		return '<div'.$id.$class.$style.'>'.$code_text.'</div>';
	}
}
