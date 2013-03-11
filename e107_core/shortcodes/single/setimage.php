<?php
// Aloow theme templates to set the default Image Resizing of thumb.php

	function setimage_shortcode($parm, $mode='')
	{
		e107::getParser()->thumbWidth = vartrue($parm['w'],100);
	}

?>
