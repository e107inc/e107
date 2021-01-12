<?php
/* $Id$ */

	function setstyle_shortcode($parm)
	{
		global $style;  // BC
		$style = $parm; // BC

		if(empty($parm))
		{
			return null;
		}

		e107::getRender()->setStyle($parm);
	}

?>
