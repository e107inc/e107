<?php
/*
* Copyright (c) 2014 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Gallery Template
*/

if (!defined('e107_INIT')) { exit; }

/**
 * @param string $parm['category']       category template, default is the 'menu_category' plugin preference
 * @param int    $parm['notablestyle']   1 to render without the tablerender() wrapper
 * @param string $parm['tablestyle']     tablerender() mode, default 'featurebox'
 * @param int    $parm['cols']           number of items per column, default 1
 * @param int    $parm['no_fill_empty']  1 to leave the last column short rather than pad it
 *
 * @example hard-coded {MENU: path=featurebox/featurebox&category=bootstrap_tabs&notablestyle=1}
 * @example hard-coded {PLUGIN=featurebox/featurebox_menu|category=bootstrap_tabs}
 * @example admin assigned - Add via Menu Manager and then configure.
 */

require_once(e_PLUGIN.'featurebox/e_menu.php');
require_once(e_PLUGIN.'featurebox/e_shortcode.php');

$shortcode = featurebox_menu::shortcode(varset($parm), featurebox_shortcodes::defaultCategory());
$text = e107::getParser()->parseTemplate($shortcode);

if(empty($text))
{
	e107::getMessage()->addDebug("DEBUG: There are no featurebox items to render for ".$shortcode);
}

echo $text;
unset($text, $shortcode);
