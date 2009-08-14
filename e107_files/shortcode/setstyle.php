<?php
/* $Id: setstyle.php,v 1.1 2009-08-14 22:31:09 e107coders Exp $ */

	function setstyle_shortcode($parm)
	{
		global $style;  // BC
		$style = $parm; // BC

		e107::getRender()->eSetStyle = $parm;
	}

?>
