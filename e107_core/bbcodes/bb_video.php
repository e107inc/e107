<?php
 /**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * DIV block bbcode
 */

if (!defined('e107_INIT')) { exit; }

// This is a generic and 'responsive' video bbcode. Handles  Youtube and eventually html5 video tags. 

class bb_video extends e_bb_base
{
	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		return '[video]'.$code_text.'[/video]';
	}

	/**
	 * Renders a video file or a bare YouTube id with {@see e_parse::toVideo()}, and nothing for any other YouTube text.
	 */
	function toHTML($code_text, $parm='')
	{
		$file = trim($code_text);
		$type = pathinfo($file, PATHINFO_EXTENSION);

		if(($type === 'youtube' || $type === 'youtubepl') && !preg_match('#^[\w\-]+\.'.$type.'$#', $file))
		{
			return '';
		}

		return e107::getParser()->toVideo($file);
	}
	
	
}







?>