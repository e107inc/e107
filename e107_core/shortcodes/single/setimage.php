<?php
// Aloow theme templates to set the default Image Resizing of thumb.php

	function setimage_shortcode($parm, $mode)
	{
		parse_str($mode,$options);
		e107::getParser()->thumbWidth = vartrue($options['w'],100);
	}

?>
