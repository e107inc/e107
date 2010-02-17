<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/templates/default/content_np_template.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

global $sc_style, $content_shortcodes;

// ##### CONTENT NEXT PREV --------------------------------------------------
if(!isset($CONTENT_NP_TABLE)){
	$CONTENT_NP_TABLE = "<div class='nextprev'>{CONTENT_NEXTPREV}</div>";
}
// ##### ----------------------------------------------------------------------

?>