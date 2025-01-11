<?php
// Aloow theme templates to set the default Image Resizing of thumb.php

	function setimage_shortcode($parm, $mode='')
	{
		if(isset($parm['default']))
		{
			$parm['w'] = 100;
			$parm['h'] = 0;
			$parm['crop'] = 0;
		}
		e107::getParser()->thumbWidth(varset($parm['w'],0));
		e107::getParser()->thumbHeight(varset($parm['h'],0));
		e107::getParser()->thumbCrop(varset($parm['crop'],0));
		
	}


