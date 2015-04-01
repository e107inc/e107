<?php
/* $Id$ */

	function setstyle_shortcode($parm)
	{
		global $style;  // BC
		$style = $parm; // BC

		e107::getRender()->setStyle($parm);
	}

?>
