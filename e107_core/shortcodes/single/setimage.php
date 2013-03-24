<?php
// Aloow theme templates to set the default Image Resizing of thumb.php

	function setimage_shortcode($parm, $mode='')
	{
		e107::getParser()->thumbWidth = vartrue($parm['w'],100);
		e107::getParser()->thumbHeight = vartrue($parm['h'],0);
		e107::getParser()->thumbCrop = vartrue($parm['crop'],0);
		
	}

?>
