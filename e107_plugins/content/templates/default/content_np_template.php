<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_np_template.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 */

global $sc_style, $content_shortcodes;

// ##### CONTENT NEXT PREV --------------------------------------------------
if(!isset($CONTENT_NP_TABLE)){
	$CONTENT_NP_TABLE = "<div class='nextprev'>{CONTENT_NEXTPREV}</div>";
}
// ##### ----------------------------------------------------------------------

?>