<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php,v 1.1 2009-11-25 12:01:25 e107coders Exp $
*
* Banner shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

class featurebox_shortcodes // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	function sc_featurebox($parm)
	{
		require_once(e_PLUGIN."featurebox/featurebox.php");
	}
}
?>