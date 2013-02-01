<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

if (!defined('e107_INIT')) { exit; }

$template = e107::getCoreTemplate('page','nav');

$text = e107::getParser()->parseTemplate("{PAGE_NAVIGATION}", true);

e107::getRender()->tablerender($template['caption'], $text, 'page-navigation-menu');

?>