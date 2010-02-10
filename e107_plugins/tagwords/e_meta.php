<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Meta Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/e_meta.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

if (is_readable(THEME."tagwords_css.php"))
{
	$src = THEME_ABS."tagwords_css.php";
	} else {
	$src = e_PLUGIN_ABS."tagwords/tagwords_css.php";
}
echo "<link rel='stylesheet' href='".$src."' type='text/css' />\n";

?>